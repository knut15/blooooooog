<?php
/**
 * 대표 이미지가 없는 글의 목록 썸네일.
 *
 * 글마다 이미지를 구하는 부담을 없애기 위해 제목에서 색과 무늬를 만들어낸다.
 * 같은 글은 늘 같은 그림이 나오고, 시리즈 글은 시리즈명으로 색을 정해
 * 목록에서 같은 계열로 묶인다. 아바타와 같은 종이접기 결로 맞췄다.
 *
 * @package generatepress-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 색 팔레트. base 는 밝은 면, shade 는 접혀 그늘진 면, bg 는 바탕.
 *
 * @return array
 */
function gpc_thumb_palette() {
	return array(
		array( 'base' => '#F0A868', 'shade' => '#D18B4E', 'bg' => '#FBF1E4' ),
		array( 'base' => '#6E9EC7', 'shade' => '#4E7BA3', 'bg' => '#EAF1F8' ),
		array( 'base' => '#8CC194', 'shade' => '#6AA173', 'bg' => '#EDF7EE' ),
		array( 'base' => '#A490C4', 'shade' => '#8272A6', 'bg' => '#F2EFF8' ),
		array( 'base' => '#7FBFB5', 'shade' => '#5E9E94', 'bg' => '#EAF6F4' ),
		array( 'base' => '#E8A38C', 'shade' => '#C8806A', 'bg' => '#FBEFEA' ),
		array( 'base' => '#5F7391', 'shade' => '#465873', 'bg' => '#EDF0F4' ),
		array( 'base' => '#E3C169', 'shade' => '#C4A24B', 'bg' => '#FBF5E3' ),
	);
}

/**
 * 무늬 6종. 모두 직선 다각형이라 종이를 접은 면처럼 보인다.
 * viewBox 는 160x100 (16:10).
 *
 * @param array $c 색 조합.
 * @return array
 */
function gpc_thumb_patterns( $c ) {
	$b = $c['base'];
	$s = $c['shade'];

	return array(
		// 대각선 접힘
		'<path d="M0 0h160v100H0z" fill="' . $b . '"/>'
			. '<path d="M0 100 L160 0 L160 100 Z" fill="' . $s . '"/>',
		// 산 모양
		'<path d="M0 0h160v100H0z" fill="' . $b . '"/>'
			. '<path d="M0 100 L48 34 L96 100 Z" fill="' . $s . '"/>'
			. '<path d="M72 100 L118 44 L160 100 Z" fill="' . $s . '" opacity=".62"/>',
		// 계단 접힘
		'<path d="M0 0h160v100H0z" fill="' . $b . '"/>'
			. '<path d="M0 100 L0 62 L54 62 L54 100 Z" fill="' . $s . '"/>'
			. '<path d="M54 100 L54 40 L108 40 L108 100 Z" fill="' . $s . '" opacity=".7"/>'
			. '<path d="M108 100 L108 18 L160 18 L160 100 Z" fill="' . $s . '" opacity=".45"/>',
		// 부채꼴 접힘
		'<path d="M0 0h160v100H0z" fill="' . $b . '"/>'
			. '<path d="M80 100 L0 40 L0 100 Z" fill="' . $s . '"/>'
			. '<path d="M80 100 L44 10 L80 34 Z" fill="' . $s . '" opacity=".68"/>'
			. '<path d="M80 100 L116 10 L80 34 Z" fill="' . $s . '" opacity=".5"/>'
			. '<path d="M80 100 L160 40 L160 100 Z" fill="' . $s . '" opacity=".78"/>',
		// 마름모
		'<path d="M0 0h160v100H0z" fill="' . $b . '"/>'
			. '<path d="M80 12 L136 50 L80 88 L24 50 Z" fill="' . $s . '"/>'
			. '<path d="M80 12 L24 50 L80 88 Z" fill="' . $s . '" opacity=".55"/>',
		// 지그재그
		'<path d="M0 0h160v100H0z" fill="' . $b . '"/>'
			. '<path d="M0 100 L0 70 L32 44 L64 70 L96 44 L128 70 L160 44 L160 100 Z" fill="' . $s . '"/>'
			. '<path d="M0 100 L32 74 L64 100 Z" fill="' . $s . '" opacity=".55"/>'
			. '<path d="M96 100 L128 74 L160 100 Z" fill="' . $s . '" opacity=".55"/>',
	);
}

/**
 * 색과 무늬를 정하는 씨앗.
 * 제목이 "<시리즈명> - <소제목> (NN)" 형식이면 시리즈명만 쓴다.
 * 그래야 한 시리즈가 목록에서 같은 색으로 묶인다.
 *
 * @param string $title 글 제목.
 * @return string
 */
function gpc_thumb_seed( $title ) {
	// WordPress 가 하이픈을 en dash 로 바꿔 저장하거나 출력하는 경우가 있어 둘 다 본다.
	// em dash(—)는 단독 글의 부제 구분자라 여기서 다루지 않는다.
	foreach ( array( ' - ', ' – ' ) as $sep ) {
		$pos = mb_strpos( $title, $sep );

		if ( false !== $pos && $pos > 0 ) {
			return mb_substr( $title, 0, $pos );
		}
	}

	return $title;
}

/**
 * 폴백 썸네일 SVG 를 data URI 로 만든다.
 *
 * @param int $post_id 글 ID.
 * @return string
 */
function gpc_fallback_thumb_uri( $post_id ) {
	// get_the_title() 은 wptexturize 를 타서 하이픈이 &#8211; 로 바뀐다.
	// 시리즈 구분자를 놓치므로 필터를 거치지 않은 원본을 쓴다.
	$title   = wp_strip_all_tags( get_post_field( 'post_title', $post_id ) );
	$seed    = gpc_thumb_seed( $title );
	$hash    = md5( $seed );

	$palette = gpc_thumb_palette();
	$color   = $palette[ hexdec( substr( $hash, 0, 6 ) ) % count( $palette ) ];

	$patterns = gpc_thumb_patterns( $color );
	// 무늬는 제목 전체로 고른다 — 같은 시리즈여도 편마다 무늬가 달라진다.
	$pattern = $patterns[ hexdec( substr( md5( $title ), 6, 6 ) ) % count( $patterns ) ];

	$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 100" width="160" height="100">'
		. '<path d="M0 0h160v100H0z" fill="' . $color['bg'] . '"/>'
		. $pattern
		. '</svg>';

	return 'data:image/svg+xml;base64,' . base64_encode( $svg );
}

/**
 * 글에 실려 있는 첫 이미지를 찾는다. 본문에 넣은 것을 먼저 보고, 없으면 첨부 미디어를 본다.
 *
 * @param int    $post_id 글 ID.
 * @param string $size    이미지 크기.
 * @return string 없으면 빈 문자열.
 */
function gpc_first_image_url( $post_id, $size = 'gpc-list-thumb' ) {
	$content = get_post_field( 'post_content', $post_id );

	if ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $m ) ) {
		$url = $m[1];

		// 이 사이트의 미디어면 잘라 둔 크기를 쓴다. 원본을 그대로 내보내면
		// 목록 한 화면에 수 MB 가 실린다.
		if ( 0 === strpos( $url, home_url() ) ) {
			$attachment_id = attachment_url_to_postid( $url );

			if ( $attachment_id ) {
				$sized = wp_get_attachment_image_url( $attachment_id, $size );

				if ( $sized ) {
					return $sized;
				}
			}
		}

		return $url;
	}

	$media = get_attached_media( 'image', $post_id );

	if ( $media ) {
		$first = reset( $media );
		$sized = wp_get_attachment_image_url( $first->ID, $size );

		if ( $sized ) {
			return $sized;
		}
	}

	return '';
}

/**
 * 목록 썸네일. 순서는 대표 이미지 → 글에 실린 이미지 → 만들어 낸 그림이다.
 *
 * 대표 이미지는 GeneratePress 의 generate_post_image 가 이미 출력하므로
 * 여기서는 그것이 없을 때만 나선다.
 */
function gpc_list_thumb() {
	if ( is_singular() || has_post_thumbnail() ) {
		return;
	}

	$post_id = get_the_ID();
	$found   = gpc_first_image_url( $post_id );

	if ( $found ) {
		printf(
			'<div class="post-image gpc-content-image">'
				. '<a href="%1$s" aria-hidden="true" tabindex="-1">'
				. '<img src="%2$s" alt="" loading="lazy" decoding="async" />'
				. '</a></div>',
			esc_url( get_permalink() ),
			esc_url( $found )
		);
		return;
	}

	printf(
		'<div class="post-image gpc-fallback-image">'
			. '<a href="%1$s" aria-hidden="true" tabindex="-1">'
			. '<img src="%2$s" alt="" width="160" height="100" loading="lazy" decoding="async" />'
			. '</a></div>',
		esc_url( get_permalink() ),
		esc_attr( gpc_fallback_thumb_uri( $post_id ) )
	);
}
add_action( 'generate_after_entry_header', 'gpc_list_thumb', 11 );
