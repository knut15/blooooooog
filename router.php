<?php
// PHP 내장 서버에서 WordPress 퍼머링크를 동작시키는 라우터
$root = __DIR__ . '/wordpress';
$path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
$file = $root . $path;
if ( $path !== '/' && file_exists( $file ) && ! is_dir( $file ) ) {
    return false; // 실제 파일은 그대로 서빙
}
$_SERVER['SCRIPT_NAME'] = '/index.php';
require $root . '/index.php';
