<?php

namespace App\Core;

class RateLimiter
{
    private static function dir(): string
    {
        $dir = APP_DIR . '/storage/rate-limit';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Returns true if the request is allowed, false if the limit was exceeded.
     */
    public static function attempt(string $key, int $maxAttempts, int $windowSeconds): bool
    {
        $file = self::dir() . '/' . sha1($key) . '.json';
        $now = time();

        $handle = fopen($file, 'c+');
        if ($handle === false) {
            return true;
        }
        flock($handle, LOCK_EX);
        $raw = stream_get_contents($handle);
        $data = json_decode((string) $raw, true);
        if (!is_array($data) || ($data['window_start'] ?? 0) < $now - $windowSeconds) {
            $data = ['window_start' => $now, 'count' => 0];
        }
        $data['count']++;
        $allowed = $data['count'] <= $maxAttempts;

        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, json_encode($data));
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);

        return $allowed;
    }
}
