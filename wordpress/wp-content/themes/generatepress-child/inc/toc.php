<?php
/**
 * 글 오른쪽에 붙는 목차.
 *
 * 본문의 h2·h3 에 id 를 달고, 그 목록을 본문 옆에 고정한다.
 * 스크롤에 따라 지금 읽는 절이 강조된다.
 *
 * @package generatepress-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 이 글의 목차 항목. the_content 필터가 채우고 출력부가 읽는다.
 *
 * @var array
 */
global $gpc_toc_items;
$gpc_toc_items = array();

/**
 * 본문 h2·h3 에 id 를 달면서 목차 항목을 모은다.
 *
 * 제목이 한글이라 슬러그 대신 순번을 쓴다. 슬러그로 만들면 주소가
 * 퍼센트 인코딩으로 길어지고, 제목을 고치면 앵커가 끊긴다.
 *
 * @param string $content 본문.
 * @return string
 */
function gpc_collect_headings( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	global $gpc_toc_items;
	$gpc_toc_items = array();
	$index         = 0;

	return preg_replace_callback(
		'/<h([23])([^>]*)>(.*?)<\/h\1>/is',
		function ( $m ) use ( &$index ) {
			global $gpc_toc_items;

			$index++;
			$level = (int) $m[1];
			$attr  = $m[2];
			$inner = $m[3];
			$text  = trim( wp_strip_all_tags( $inner ) );

			if ( '' === $text ) {
				return $m[0];
			}

			if ( preg_match( '/\bid=["\']([^"\']+)["\']/', $attr, $found ) ) {
				$id = $found[1];
			} else {
				$id    = 'gpc-h-' . $index;
				$attr .= ' id="' . esc_attr( $id ) . '"';
			}

			$gpc_toc_items[] = array(
				'id'    => $id,
				'text'  => $text,
				'level' => $level,
			);

			return '<h' . $level . $attr . '>' . $inner . '</h' . $level . '>';
		},
		$content
	);
}
add_filter( 'the_content', 'gpc_collect_headings', 20 );

/**
 * 목차를 출력한다. 자리는 CSS 가 잡으므로 본문 뒤에 두어도 된다.
 * 절이 둘 미만이면 목차가 길잡이 노릇을 못 하므로 내지 않는다.
 */
function gpc_render_toc() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}

	global $gpc_toc_items;

	if ( count( (array) $gpc_toc_items ) < 2 ) {
		return;
	}
	?>
	<nav class="gpc-toc" id="gpc-toc" aria-label="이 글의 목차">
		<ul class="gpc-toc-list">
			<?php foreach ( $gpc_toc_items as $item ) : ?>
				<li class="gpc-toc-item gpc-toc-h<?php echo (int) $item['level']; ?>">
					<a href="#<?php echo esc_attr( $item['id'] ); ?>" data-target="<?php echo esc_attr( $item['id'] ); ?>">
						<?php echo esc_html( $item['text'] ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
	<?php
}
add_action( 'generate_after_entry_content', 'gpc_render_toc', 20 );

/**
 * 목차 스크립트.
 */
function gpc_enqueue_toc_script() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}

	$path = get_stylesheet_directory() . '/js/toc.js';

	wp_enqueue_script(
		'gpc-toc',
		get_stylesheet_directory_uri() . '/js/toc.js',
		array(),
		file_exists( $path ) ? filemtime( $path ) : '1.0.0',
		true
	);
}
add_action( 'wp_enqueue_scripts', 'gpc_enqueue_toc_script' );
