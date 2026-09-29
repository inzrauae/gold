<?php

declare(strict_types=1);

define('APP_DIR', __DIR__);

spl_autoload_register(function (string $class): void {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }
    $relative = substr($class, strlen('App\\'));
    $path = APP_DIR . '/src/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

use App\Core\Env;

Env::load(APP_DIR . '/.env');

date_default_timezone_set('Asia/Colombo');

$debug = Env::bool('APP_DEBUG', false);
ini_set('display_errors', $debug ? '1' : '0');
error_reporting(E_ALL);

set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    \App\Core\Logger::error($message, ['file' => $file, 'line' => $line]);
    return false;
});

set_exception_handler(function (\Throwable $e): void {
    \App\Core\Logger::error($e->getMessage(), [
        'exception' => get_class($e),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ]);

    if (php_sapi_name() !== 'cli') {
        http_response_code(500);
        if (Env::bool('APP_DEBUG', false)) {
            echo '<pre>' . htmlspecialchars((string) $e) . '</pre>';
        } else {
            echo \App\Core\View::render('errors/500');
        }
    } else {
        fwrite(STDERR, $e->getMessage() . PHP_EOL);
    }
});

foreach (['cache', 'logs', 'feeds', 'locks', 'rate-limit'] as $dir) {
    $path = APP_DIR . '/storage/' . $dir;
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}
