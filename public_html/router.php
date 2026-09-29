<?php

// Local-dev-only router for `php -S`, which (unlike Apache + .htaccess in
// production) does not serve existing static files unless the router script
// explicitly hands them off by returning false. Not used in production.

$path = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$file = __DIR__ . $path;

if ($path !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/index.php';
