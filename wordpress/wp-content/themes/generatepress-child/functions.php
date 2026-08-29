<?php
/**
 * GeneratePress 자식 테마
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_stylesheet_directory() . '/inc/avatars.php';

/**
 * 부모 테마 스타일 뒤에 자식 테마 스타일을 붙인다.
 */
function gpc_enqueue_styles() {
	$child = get_stylesheet_directory() . '/style.css';

	// Pretendard — 한국어 본문 가독성이 가장 좋은 무료 폰트.
	// dynamic-subset 은 화면에 실제로 쓰인 글자만 내려받아 용량이 작다.
	wp_enqueue_style(
		'pretendard',
		'https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable-dynamic-subset.min.css',
		array(),
		'1.3.9'
	);

	wp_enqueue_style(
		'gpc-style',
		get_stylesheet_uri(),
		array( 'generate-style', 'pretendard' ),
		file_exists( $child ) ? filemtime( $child ) : '1.0.0'
	);
}
add_action( 'wp_enqueue_scripts', 'gpc_enqueue_styles', 20 );

/**
 * 폰트 CDN 연결을 미리 열어 첫 화면 렌더링을 앞당긴다.
 */
function gpc_resource_hints( $hints, $relation ) {
	if ( 'preconnect' === $relation ) {
		$hints[] = array( 'href' => 'https://cdn.jsdelivr.net', 'crossorigin' );
	}
	return $hints;
}
add_filter( 'wp_resource_hints', 'gpc_resource_hints', 10, 2 );

/**
 * 목록의 발췌 길이. 한글은 어절 단위라 기본 55 는 너무 길다.
 */
function gpc_excerpt_length( $length ) {
	return 40;
}
add_filter( 'excerpt_length', 'gpc_excerpt_length', 999 );

function gpc_excerpt_more( $more ) {
	return ' …';
}
add_filter( 'excerpt_more', 'gpc_excerpt_more' );

/**
 * GeneratePress 의 ko_KR 번역이 비어 있는 문구를 한글로 바꾼다.
 */
function gpc_translate_theme_strings( $translated, $original, $domain ) {
	if ( 'generatepress' !== $domain ) {
		return $translated;
	}

	$map = array(
		'Read more'          => '계속 읽기',
		'Leave a comment'    => '댓글 남기기',
		'1 Comment'          => '댓글 1개',
		'% Comments'         => '댓글 %개',
		'Continue Reading'   => '계속 읽기',
	);

	return isset( $map[ $original ] ) ? $map[ $original ] : $translated;
}
add_filter( 'gettext', 'gpc_translate_theme_strings', 20, 3 );

/**
 * 발췌 끝의 "Read more" 링크 문구.
 */
function gpc_excerpt_more_link( $output ) {
	return str_replace( 'Read more', '계속 읽기', $output );
}
add_filter( 'generate_excerpt_more_output', 'gpc_excerpt_more_link' );

/**
 * 단일 글에서는 기본 메타 대신 아바타가 붙은 바이라인을 쓴다.
 */
function gpc_swap_post_meta() {
	if ( is_singular( 'post' ) ) {
		remove_action( 'generate_after_entry_title', 'generate_post_meta' );
		add_action( 'generate_after_entry_title', 'gpc_byline' );
	}
}
add_action( 'wp', 'gpc_swap_post_meta' );

/**
 * 아바타 + 작성자 + 날짜.
 */
function gpc_byline() {
	$author_id = get_the_author_meta( 'ID' );
	?>
	<div class="gpc-byline">
		<?php echo get_avatar( $author_id, 88, '', get_the_author(), array( 'class' => 'gpc-avatar' ) ); ?>
		<div class="gpc-byline-text">
			<span class="gpc-author"><?php the_author(); ?></span>
			<time class="gpc-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
				<?php echo esc_html( get_the_date() ); ?>
			</time>
		</div>
	</div>
	<?php
}

/**
 * 스크롤 시 상단에 고정되는 바. 제목이 화면에서 사라지면 나타난다.
 */
function gpc_sticky_bar() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}
	?>
	<div class="gpc-sticky-bar" id="gpc-sticky-bar" aria-hidden="true">
		<div class="gpc-sticky-inner">
			<span class="gpc-sticky-title"><?php the_title(); ?></span>
			<span class="gpc-sticky-meta">
				<?php echo get_avatar( get_the_author_meta( 'ID' ), 56, '', get_the_author(), array( 'class' => 'gpc-sticky-avatar' ) ); ?>
				<span class="gpc-sticky-author"><?php the_author(); ?></span><span class="gpc-sep">·</span><?php echo esc_html( get_the_date() ); ?>
			</span>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'gpc_sticky_bar' );

/**
 * sticky 바 스크립트.
 */
function gpc_enqueue_sticky_script() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}
	$path = get_stylesheet_directory() . '/js/post-sticky.js';
	wp_enqueue_script(
		'gpc-post-sticky',
		get_stylesheet_directory_uri() . '/js/post-sticky.js',
		array(),
		file_exists( $path ) ? filemtime( $path ) : '1.0.0',
		true
	);
}
add_action( 'wp_enqueue_scripts', 'gpc_enqueue_sticky_script' );

/**
 * 댓글 제목을 "댓글 N개" 로. (Medium 의 "Responses (N)")
 */
function gpc_comments_title( $output, $title, $count ) {
	return sprintf(
		'<h2 class="comments-title">댓글 <span class="gpc-comment-count">%s</span></h2>',
		esc_html( number_format_i18n( $count ) )
	);
}
add_filter( 'generate_comments_title_output', 'gpc_comments_title', 10, 3 );

/**
 * 댓글 폼을 간소화한다. 웹사이트 필드는 스팸만 부르므로 뺀다.
 */
function gpc_comment_form_defaults( $defaults ) {
	$defaults['title_reply']         = '댓글 남기기';
	$defaults['title_reply_to']      = '%s 님에게 답글';
	$defaults['cancel_reply_link']   = '취소';
	$defaults['label_submit']        = '남기기';
	$defaults['comment_notes_before'] = '';
	$defaults['comment_notes_after']  = '';
	$defaults['comment_field'] = '<p class="comment-form-comment">'
		. '<label for="comment" class="screen-reader-text">댓글</label>'
		. '<textarea id="comment" name="comment" rows="3" required'
		. ' placeholder="어떻게 생각하시나요?"></textarea></p>';
	return $defaults;
}
// 부모 테마 functions.php 는 자식보다 나중에 로드되므로 priority 를 높여 뒤에 실행시킨다.
add_filter( 'comment_form_defaults', 'gpc_comment_form_defaults', 20 );

function gpc_comment_form_fields( $fields ) {
	// 이름만 남긴다.
	unset( $fields['url'], $fields['email'], $fields['cookies'] );
	return $fields;
}
add_filter( 'comment_form_default_fields', 'gpc_comment_form_fields', 20 );

/**
 * 댓글 아바타는 표시 크기(36px)의 2배로 받아 레티나에서도 또렷하게 한다.
 * 이 필터는 배열이 아니라 정수를 넘긴다.
 */
function gpc_comment_avatar_size( $size ) {
	return 72;
}
add_filter( 'generate_comment_avatar_size', 'gpc_comment_avatar_size' );

/**
 * GeneratePress 의 기본 글 네비게이션을 끄고 썸네일 카드로 대체한다.
 * 단일 글의 네비게이션은 generate_after_loop 가 아니라
 * post meta item 으로 출력되므로 전용 필터로 꺼야 한다.
 */
add_filter( 'generate_show_post_navigation', '__return_false' );
add_action( 'generate_after_entry_content', 'gpc_post_navigation' );

/**
 * 이전 글은 왼쪽, 다음 글은 오른쪽에 썸네일과 함께 배치한다.
 */
function gpc_post_navigation() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}

	$prev = get_previous_post();
	$next = get_next_post();

	if ( ! $prev && ! $next ) {
		return;
	}
	?>
	<nav class="gpc-post-nav" aria-label="이전 글과 다음 글">
		<?php
		gpc_post_nav_card( $prev, 'prev', '이전 글' );
		gpc_post_nav_card( $next, 'next', '다음 글' );
		?>
	</nav>
	<?php
}

/**
 * 네비게이션 카드 하나. 대표 이미지가 없으면 제목 첫 글자를 쓴다.
 *
 * @param WP_Post|null $post  대상 글.
 * @param string       $dir   prev 또는 next.
 * @param string       $label 화면에 보일 라벨.
 */
function gpc_post_nav_card( $post, $dir, $label ) {
	if ( ! $post ) {
		// 한쪽만 있을 때 반대편 칸을 비워 배치를 유지한다.
		printf( '<span class="gpc-nav-empty gpc-nav-%s" aria-hidden="true"></span>', esc_attr( $dir ) );
		return;
	}

	$thumb = get_the_post_thumbnail( $post->ID, 'thumbnail', array( 'class' => 'gpc-nav-thumb', 'loading' => 'lazy' ) );

	if ( ! $thumb ) {
		$initial = mb_substr( wp_strip_all_tags( get_the_title( $post ) ), 0, 1 );
		$thumb   = sprintf(
			'<span class="gpc-nav-thumb gpc-nav-thumb--empty" aria-hidden="true">%s</span>',
			esc_html( $initial )
		);
	}
	?>
	<a class="gpc-nav-card gpc-nav-<?php echo esc_attr( $dir ); ?>" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
		<?php echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<span class="gpc-nav-text">
			<span class="gpc-nav-label"><?php echo esc_html( $label ); ?></span>
			<span class="gpc-nav-title"><?php echo esc_html( get_the_title( $post ) ); ?></span>
		</span>
	</a>
	<?php
}

/**
 * 댓글 폼에 동물 아바타 선택칸을 넣는다. 손님용이므로 로그인 사용자에게는 보이지 않는다.
 *
 * @param array $fields 기존 필드.
 * @return array
 */
function gpc_avatar_field( $fields ) {
	if ( is_user_logged_in() ) {
		return $fields;
	}

	$set     = gpc_avatar_set();
	$chosen  = isset( $_COOKIE['gpc_avatar'] ) ? sanitize_key( wp_unslash( $_COOKIE['gpc_avatar'] ) ) : '';
	$buttons = '';

	foreach ( $set as $key => $animal ) {
		$buttons .= sprintf(
			'<label class="gpc-avatar-option"><input type="radio" name="gpc_avatar" value="%1$s"%4$s>'
			. '<img src="%2$s" alt="%3$s" width="44" height="44" loading="lazy">'
			. '<span class="screen-reader-text">%3$s</span></label>',
			esc_attr( $key ),
			esc_attr( gpc_avatar_data_uri( $key ) ),
			esc_attr( $animal['label'] ),
			checked( $chosen, $key, false )
		);
	}

	$fields['gpc_avatar'] = '<div class="comment-form-avatar">'
		. '<span class="gpc-avatar-legend">아바타 고르기</span>'
		. '<div class="gpc-avatar-grid">' . $buttons . '</div>'
		. '</div>';

	return $fields;
}
add_filter( 'comment_form_default_fields', 'gpc_avatar_field', 30 );

/**
 * 고른 아바타를 댓글에 저장한다.
 *
 * @param int $comment_id 댓글 ID.
 */
function gpc_save_avatar( $comment_id ) {
	if ( empty( $_POST['gpc_avatar'] ) ) {
		return;
	}

	$key = sanitize_key( wp_unslash( $_POST['gpc_avatar'] ) );

	// 목록에 없는 값은 버린다.
	if ( ! array_key_exists( $key, gpc_avatar_set() ) ) {
		return;
	}

	add_comment_meta( $comment_id, 'gpc_avatar', $key, true );

	// 다음 댓글에서도 같은 아바타가 선택되어 있도록 기억한다.
	setcookie( 'gpc_avatar', $key, time() + YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
}
add_action( 'comment_post', 'gpc_save_avatar' );

/**
 * 손님 댓글의 아바타를 동물 스티커로 바꾼다.
 * 고르지 않았다면 이름을 근거로 하나를 배정한다(같은 이름이면 늘 같은 동물).
 *
 * pre_get_avatar_data 로는 안 된다. WordPress 가 src 를 esc_url() 로 거르는데
 * data: 는 허용 프로토콜이 아니라 주소가 통째로 비워지기 때문이다.
 * 그래서 완성된 <img> 태그를 직접 만들어 돌려준다.
 *
 * @param string $avatar      기존 img 태그.
 * @param mixed  $id_or_email 대상.
 * @param int    $size        크기.
 * @param string $default     기본 아바타.
 * @param string $alt         대체 텍스트.
 * @return string
 */
function gpc_filter_avatar_html( $avatar, $id_or_email, $size, $default, $alt ) {
	if ( ! $id_or_email instanceof WP_Comment ) {
		return $avatar;
	}

	// 로그인 사용자가 쓴 댓글은 원래 아바타를 그대로 둔다.
	if ( ! empty( $id_or_email->user_id ) ) {
		return $avatar;
	}

	$set = gpc_avatar_set();
	$key = get_comment_meta( $id_or_email->comment_ID, 'gpc_avatar', true );

	if ( ! $key || ! array_key_exists( $key, $set ) ) {
		$key = gpc_avatar_pick( $id_or_email->comment_author . $id_or_email->comment_author_email );
	}

	$uri = gpc_avatar_data_uri( $key );

	if ( ! $uri ) {
		return $avatar;
	}

	return sprintf(
		'<img src="%1$s" alt="%2$s" class="avatar avatar-%3$d photo gpc-animal-avatar" width="%3$d" height="%3$d" decoding="async" />',
		esc_attr( $uri ),
		esc_attr( $alt ? $alt : $set[ $key ]['label'] ),
		(int) $size
	);
}
add_filter( 'get_avatar', 'gpc_filter_avatar_html', 10, 5 );
