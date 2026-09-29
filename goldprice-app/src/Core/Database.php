<?php

namespace App\Core;

use PDO;

class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        $driver = Env::get('DB_CONNECTION', 'sqlite');

        if ($driver === 'sqlite') {
            $path = Env::get('DB_SQLITE_PATH', 'storage/database.sqlite');
            if (!self::isAbsolute($path)) {
                $path = APP_DIR . '/' . $path;
            }
            $dsn = 'sqlite:' . $path;
            $pdo = new PDO($dsn);
            $pdo->exec('PRAGMA foreign_keys = ON');
        } else {
            $host = Env::get('DB_HOST', 'localhost');
            $port = Env::get('DB_PORT', '3306');
            $db = Env::get('DB_DATABASE');
            $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
            $pdo = new PDO($dsn, Env::get('DB_USERNAME'), Env::get('DB_PASSWORD'));
        }

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        self::$connection = $pdo;

        return $pdo;
    }

    public static function driver(): string
    {
        return Env::get('DB_CONNECTION', 'sqlite');
    }

    private static function isAbsolute(string $path): bool
    {
        return $path === '' || $path[0] === '/' || preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1;
    }
}
