<?php
/**
 * 댓글용 동물 아바타 세트.
 *
 * 외부 서비스(Gravatar)에 의존하지 않도록 SVG 를 직접 그려 넣는다.
 * 각 SVG 는 64x64 viewBox 를 쓰고, data URI 로 <img> 에 실린다.
 *
 * @package generatepress-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 아바타 목록. 키는 DB 에 저장되는 값이므로 바꾸지 말 것.
 *
 * @return array
 */
function gpc_avatar_set() {
	static $set = null;

	if ( null !== $set ) {
		return $set;
	}

	$set = array(
		'cat' => array(
			'label' => '고양이',
			'svg'   => '<path d="M0 0h64v64H0z" fill="#FBF1E4"/> <path d="M12 24 L10 7 L27 17 Z" fill="#E9A05C"/><path d="M12 24 L10 7 L19 12 Z" fill="#D08644"/> <path d="M52 24 L54 7 L37 17 Z" fill="#E9A05C"/><path d="M52 24 L54 7 L45 12 Z" fill="#D08644"/> <path d="M32 52 L11 22 L53 22 Z" fill="#F2B173"/> <path d="M32 52 L11 22 L32 22 Z" fill="#E09A54"/> <path d="M24 31 l3 4 -3 4 -3 -4 Z" fill="#3E2A1E"/><path d="M40 31 l3 4 -3 4 -3 -4 Z" fill="#3E2A1E"/> <path d="M32 41 l4 3 -4 4 -4 -4 Z" fill="#C4553F"/> <path d="M16 36 l5 2 -5 3 Z" fill="#F0847A" opacity=".55"/><path d="M48 36 l-5 2 5 3 Z" fill="#F0847A" opacity=".55"/>',
		),
		'fox' => array(
			'label' => '여우',
			'svg'   => '<path d="M0 0h64v64H0z" fill="#FDF0E7"/> <path d="M10 22 L13 5 L29 16 Z" fill="#E2703A"/><path d="M10 22 L13 5 L20 11 Z" fill="#C2552A"/> <path d="M54 22 L51 5 L35 16 Z" fill="#E2703A"/><path d="M54 22 L51 5 L44 11 Z" fill="#C2552A"/> <path d="M32 54 L9 21 L55 21 Z" fill="#EE8348"/> <path d="M32 54 L9 21 L32 21 Z" fill="#D6672F"/> <path d="M32 54 L20 37 L44 37 Z" fill="#FBF3EC"/> <path d="M32 54 L20 37 L32 37 Z" fill="#E8DDD3"/> <path d="M23 29 l3.5 4 -3.5 4 -3.5 -4 Z" fill="#38241A"/><path d="M41 29 l3.5 4 -3.5 4 -3.5 -4 Z" fill="#38241A"/> <path d="M32 45 l4.5 3.5 -4.5 5 -4.5 -5 Z" fill="#33221A"/>',
		),
		'bear' => array(
			'label' => '곰',
			'svg'   => '<path d="M0 0h64v64H0z" fill="#F5EDE3"/> <path d="M14 22 L8 12 L22 10 Z" fill="#A9784E"/><path d="M14 22 L8 12 L15 11 Z" fill="#8C6039"/> <path d="M50 22 L56 12 L42 10 Z" fill="#A9784E"/><path d="M50 22 L56 12 L49 11 Z" fill="#8C6039"/> <path d="M32 54 L10 38 L18 17 L46 17 L54 38 Z" fill="#BE8A5C"/> <path d="M32 54 L10 38 L18 17 L32 17 Z" fill="#A3714A"/> <path d="M32 52 L20 41 L26 33 L38 33 L44 41 Z" fill="#EBDBC7"/> <path d="M24 29 l3.5 4 -3.5 4 -3.5 -4 Z" fill="#3A2718"/><path d="M40 29 l3.5 4 -3.5 4 -3.5 -4 Z" fill="#3A2718"/> <path d="M32 38 l5 3.5 -5 4.5 -5 -4.5 Z" fill="#33221A"/>',
		),
		'rabbit' => array(
			'label' => '토끼',
			'svg'   => '<path d="M0 0h64v64H0z" fill="#FCF1F3"/> <path d="M24 30 L19 4 L30 20 Z" fill="#F3F1EE"/><path d="M24 30 L19 4 L24 18 Z" fill="#DCD6D0"/> <path d="M40 30 L45 4 L34 20 Z" fill="#F3F1EE"/><path d="M40 30 L45 4 L40 18 Z" fill="#DCD6D0"/> <path d="M23 24 L20 9 L26 20 Z" fill="#F2A8B8"/><path d="M41 24 L44 9 L38 20 Z" fill="#F2A8B8"/> <path d="M32 55 L13 36 L20 21 L44 21 L51 36 Z" fill="#F7F5F2"/> <path d="M32 55 L13 36 L20 21 L32 21 Z" fill="#E3DDD6"/> <path d="M24 33 l3.5 4 -3.5 4 -3.5 -4 Z" fill="#3B3038"/><path d="M40 33 l3.5 4 -3.5 4 -3.5 -4 Z" fill="#3B3038"/> <path d="M32 42 l4 3 -4 4.5 -4 -4.5 Z" fill="#EC8FA5"/> <path d="M15 40 l5 2 -5 3 Z" fill="#F0879C" opacity=".5"/><path d="M49 40 l-5 2 5 3 Z" fill="#F0879C" opacity=".5"/>',
		),
		'panda' => array(
			'label' => '판다',
			'svg'   => '<path d="M0 0h64v64H0z" fill="#F1F1F3"/> <path d="M13 21 L9 9 L24 12 Z" fill="#39393F"/><path d="M13 21 L9 9 L16 10.5 Z" fill="#26262C"/> <path d="M51 21 L55 9 L40 12 Z" fill="#39393F"/><path d="M51 21 L55 9 L48 10.5 Z" fill="#26262C"/> <path d="M32 54 L11 37 L18 17 L46 17 L53 37 Z" fill="#FCFCFD"/> <path d="M32 54 L11 37 L18 17 L32 17 Z" fill="#E5E5E9"/> <path d="M23 27 L30 31 L25 39 L18 34 Z" fill="#39393F"/> <path d="M41 27 L34 31 L39 39 L46 34 Z" fill="#39393F"/> <path d="M24 32 l2.5 2.5 -2.5 3 -2.5 -3 Z" fill="#FCFCFD"/><path d="M40 32 l2.5 2.5 -2.5 3 -2.5 -3 Z" fill="#FCFCFD"/> <path d="M32 40 l5 3.5 -5 4.5 -5 -4.5 Z" fill="#33333A"/>',
		),
		'dog' => array(
			'label' => '강아지',
			'svg'   => '<path d="M0 0h64v64H0z" fill="#F6F0E7"/> <path d="M17 15 L4 30 L10 47 L20 38 Z" fill="#9C6C3F"/> <path d="M17 15 L4 30 L12 33 Z" fill="#805531"/> <path d="M47 15 L60 30 L54 47 L44 38 Z" fill="#9C6C3F"/> <path d="M47 15 L60 30 L52 33 Z" fill="#805531"/> <path d="M32 52 L17 38 L20 16 L44 16 L47 38 Z" fill="#E0AE72"/> <path d="M32 52 L17 38 L20 16 L32 16 Z" fill="#C79154"/> <path d="M32 54 L20 43 L26 32 L38 32 L44 43 Z" fill="#FAF0E0"/> <path d="M32 54 L20 43 L26 32 L32 32 Z" fill="#EBDCC6"/> <path d="M24 26 l3.5 4 -3.5 4 -3.5 -4 Z" fill="#3B2A1B"/> <path d="M40 26 l3.5 4 -3.5 4 -3.5 -4 Z" fill="#3B2A1B"/> <path d="M32 38 L37 42 L32 47 L27 42 Z" fill="#33231A"/>',
		),
		'frog' => array(
			'label' => '개구리',
			'svg'   => '<path d="M0 0h64v64H0z" fill="#EDF7EE"/> <path d="M32 56 L8 42 L15 24 L49 24 L56 42 Z" fill="#8CD293"/> <path d="M32 56 L8 42 L15 24 L32 24 Z" fill="#6FB477"/> <path d="M20 24 L11 10 L29 16 Z" fill="#79BE80"/> <path d="M44 24 L53 10 L35 16 Z" fill="#79BE80"/> <path d="M20 8 L31 17 L20 26 L9 17 Z" fill="#FBFCFB"/> <path d="M20 8 L9 17 L20 26 Z" fill="#E4EDE5"/> <path d="M44 8 L55 17 L44 26 L33 17 Z" fill="#FBFCFB"/> <path d="M44 8 L33 17 L44 26 Z" fill="#E4EDE5"/> <path d="M20 13 l4 4 -4 4 -4 -4 Z" fill="#2C3A2E"/> <path d="M44 13 l4 4 -4 4 -4 -4 Z" fill="#2C3A2E"/> <path d="M20 41 L32 50 L44 41 Z" fill="#3F6B46"/> <path d="M14 36 l5 2 -5 3 Z" fill="#F0879C" opacity=".45"/> <path d="M50 36 l-5 2 5 3 Z" fill="#F0879C" opacity=".45"/>',
		),
		'penguin' => array(
			'label' => '펭귄',
			'svg'   => '<path d="M0 0h64v64H0z" fill="#EAF2F8"/> <path d="M32 38 L48 48 L32 60 L16 48 Z" fill="#3A4661"/> <path d="M32 38 L16 48 L32 60 Z" fill="#2B3550"/> <path d="M32 42 L41 50 L32 57 L23 50 Z" fill="#FBFCFD"/> <path d="M32 10 L52 28 L32 46 L12 28 Z" fill="#48556F"/> <path d="M32 10 L12 28 L32 46 Z" fill="#343F58"/> <path d="M24 22 L28.5 26.5 L24 31 L19.5 26.5 Z" fill="#FBFCFD"/> <path d="M40 22 L44.5 26.5 L40 31 L35.5 26.5 Z" fill="#FBFCFD"/> <path d="M24 24 l2.2 2.5 -2.2 2.5 -2.2 -2.5 Z" fill="#242C3E"/> <path d="M40 24 l2.2 2.5 -2.2 2.5 -2.2 -2.5 Z" fill="#242C3E"/> <path d="M32 30 L39 35 L32 40 L25 35 Z" fill="#F2A93F"/> <path d="M32 30 L25 35 L32 40 Z" fill="#D68A26"/>',
		),
	);

	return $set;
}

/**
 * 아바타 SVG 를 data URI 로 만든다.
 *
 * @param string $key  아바타 키.
 * @param int    $size 픽셀 크기.
 * @return string
 */
function gpc_avatar_data_uri( $key ) {
	$set = gpc_avatar_set();

	if ( ! isset( $set[ $key ] ) ) {
		return '';
	}

	$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="64" height="64">'
		. $set[ $key ]['svg'] . '</svg>';

	return 'data:image/svg+xml;base64,' . base64_encode( $svg );
}

/**
 * 아바타를 고르지 않았을 때 이름을 근거로 하나를 배정한다.
 * 같은 이름이면 늘 같은 동물이 나온다.
 *
 * @param string $seed 이름이나 이메일.
 * @return string
 */
function gpc_avatar_pick( $seed ) {
	$keys = array_keys( gpc_avatar_set() );
	$index = hexdec( substr( md5( (string) $seed ), 0, 8 ) ) % count( $keys );

	return $keys[ $index ];
}
