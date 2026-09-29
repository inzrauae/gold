<?php

namespace App\Core;

class Logger
{
    public static function log(string $level, string $message, array $context = []): void
    {
        $dir = APP_DIR . '/storage/logs';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file = $dir . '/' . date('Y-m-d') . '.log';
        $line = json_encode([
            'time' => date('c'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ], JSON_UNESCAPED_SLASHES);

        file_put_contents($file, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('info', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('warning', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('error', $message, $context);
    }

    /** @return array<int, array<string, mixed>> */
    public static function tail(int $lines = 200, ?string $date = null): array
    {
        $date ??= date('Y-m-d');
        $file = APP_DIR . '/storage/logs/' . $date . '.log';
        if (!is_file($file)) {
            return [];
        }
        $all = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $slice = array_slice($all, -$lines);
        $out = [];
        foreach (array_reverse($slice) as $raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $out[] = $decoded;
            }
        }
        return $out;
    }

    public static function purgeOld(int $retentionDays): void
    {
        $dir = APP_DIR . '/storage/logs';
        if (!is_dir($dir)) {
            return;
        }
        $cutoff = time() - $retentionDays * 86400;
        foreach (glob($dir . '/*.log') ?: [] as $file) {
            if (filemtime($file) < $cutoff) {
                @unlink($file);
            }
        }
    }
}
