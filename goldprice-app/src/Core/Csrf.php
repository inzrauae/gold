<?php

namespace App\Core;

class Csrf
{
    public static function token(): string
    {
        $token = Session::get('_csrf_token');
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::put('_csrf_token', $token);
        }
        return $token;
    }

    public static function field(): string
    {
        $token = self::e(self::token());
        return "<input type=\"hidden\" name=\"_csrf\" value=\"{$token}\">";
    }

    public static function verify(?string $token): bool
    {
        $expected = Session::get('_csrf_token');
        return is_string($token) && is_string($expected) && hash_equals($expected, $token);
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
