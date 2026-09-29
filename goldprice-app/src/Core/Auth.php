<?php

namespace App\Core;

class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $stmt = Database::connection()->prepare('SELECT * FROM admin_users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            RateLimiter::attempt('login:' . $email, 999999, 1);
            return false;
        }

        Session::regenerate();
        Session::put('admin_user_id', $user['id']);
        Session::put('admin_email', $user['email']);
        Session::put('admin_role', $user['role']);

        return true;
    }

    public static function check(): bool
    {
        return Session::get('admin_user_id') !== null;
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        return [
            'id' => Session::get('admin_user_id'),
            'email' => Session::get('admin_email'),
            'role' => Session::get('admin_role'),
        ];
    }

    public static function isAdmin(): bool
    {
        return self::check() && Session::get('admin_role') === 'admin';
    }

    public static function logout(): void
    {
        Session::destroy();
    }
}
