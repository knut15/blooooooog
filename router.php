<?php
// PHP 내장 서버에서 WordPress 퍼머링크를 동작시키는 라우터
$root = __DIR__ . '/wordpress';
$path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
$file = $root . $path;
if ( $path !== '/' && file_exists( $file ) && ! is_dir( $file ) ) {
    return false; // 실제 파일은 그대로 서빙
}

/*
  디렉터리를 요청하면 그 안의 index.php 를 서빙한다.

  이게 없으면 /wp-admin/ 이 아래 fallback 으로 떨어져 블로그 첫 화면이 나온다.
  관리자 화면이 안 열리는 것이 아니라 블로그가 열려서, 원인을 찾기 어렵다.
  Apache 나 Nginx 는 DirectoryIndex 로 이 일을 하는데 PHP 내장 서버는 안 한다.
*/
if ( is_dir( $file ) ) {
    $dir_index = rtrim( $file, '/' ) . '/index.php';
    if ( file_exists( $dir_index ) ) {
        // WordPress 가 관리자 주소를 만들 때 이 값을 본다. 비워 두면 링크가 깨진다.
        $_SERVER['SCRIPT_NAME'] = rtrim( $path, '/' ) . '/index.php';
        require $dir_index;
        return true;
    }
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
require $root . '/index.php';
