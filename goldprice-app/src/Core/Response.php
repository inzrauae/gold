<?php

namespace App\Core;

class Response
{
    public static function html(string $content, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        echo $content;
    }

    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public static function xml(string $content, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/xml; charset=utf-8');
        echo $content;
    }

    public static function text(string $content, int $status = 200, string $contentType = 'text/plain'): void
    {
        http_response_code($status);
        header("Content-Type: {$contentType}; charset=utf-8");
        echo $content;
    }

    public static function javascript(string $content, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/javascript; charset=utf-8');
        echo $content;
    }

    public static function image(string $binary, string $contentType = 'image/png', int $cacheSeconds = 3600): void
    {
        http_response_code(200);
        header("Content-Type: {$contentType}");
        header("Cache-Control: public, max-age={$cacheSeconds}");
        echo $binary;
    }

    public static function redirect(string $to, int $status = 302): void
    {
        http_response_code($status);
        header("Location: {$to}");
    }

    public static function notFound(): void
    {
        http_response_code(404);
        header('Content-Type: text/html; charset=utf-8');
        echo View::render('errors/404');
    }
}
