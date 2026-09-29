<?php

namespace App\Core;

class Cache
{
    private static function dir(): string
    {
        $dir = APP_DIR . '/storage/cache';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    private static function path(string $key): string
    {
        return self::dir() . '/' . sha1($key) . '.cache';
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $file = self::path($key);
        if (!is_file($file)) {
            return $default;
        }
        $raw = file_get_contents($file);
        $data = @unserialize($raw);
        if (!is_array($data) || !array_key_exists('expires', $data)) {
            return $default;
        }
        if ($data['expires'] !== 0 && $data['expires'] < time()) {
            @unlink($file);
            return $default;
        }
        return $data['value'];
    }

    public static function put(string $key, mixed $value, int $ttlSeconds = 0): void
    {
        $expires = $ttlSeconds > 0 ? time() + $ttlSeconds : 0;
        file_put_contents(self::path($key), serialize(['expires' => $expires, 'value' => $value]), LOCK_EX);
    }

    public static function remember(string $key, int $ttlSeconds, callable $callback): mixed
    {
        $value = self::get($key, '__MISS__');
        if ($value !== '__MISS__') {
            return $value;
        }
        $value = $callback();
        self::put($key, $value, $ttlSeconds);
        return $value;
    }

    public static function forget(string $key): void
    {
        @unlink(self::path($key));
    }

    public static function clear(): void
    {
        foreach (glob(self::dir() . '/*.cache') ?: [] as $file) {
            @unlink($file);
        }
    }
}
