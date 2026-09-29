<?php

namespace App\Services;

use App\Core\Cache;
use App\Core\Env;
use App\Core\Logger;

/**
 * Verification-failure e-mails to ADMIN_EMAIL, throttled to at most one per
 * reason per NOTIFY_COOLDOWN_MINUTES. Channel interface for future price alerts
 * (email/WhatsApp/Telegram/push) - disabled until ALERTS_ENABLED=true.
 */
class AlertService
{
    public static function notify(string $reason): void
    {
        $cooldown = Env::int('NOTIFY_COOLDOWN_MINUTES', 60);
        $cacheKey = 'alert_cooldown:' . $reason;

        if (Cache::get($cacheKey) !== null) {
            return;
        }
        Cache::put($cacheKey, time(), $cooldown * 60);

        $to = Env::get('ADMIN_EMAIL');
        $from = Env::get('MAIL_FROM');
        if (!$to || !$from) {
            return;
        }

        $subject = '[' . Env::get('SITE_NAME', 'Gold Price') . '] Verification failed: ' . $reason;
        $body = "Price verification failed.\n\nReason: {$reason}\nTime: " . date('c') . "\n\nCheck /admin for details.";
        $headers = "From: {$from}\r\nContent-Type: text/plain; charset=UTF-8";

        try {
            @mail($to, $subject, $body, $headers);
        } catch (\Throwable $e) {
            Logger::warning('alert_mail_failed', ['error' => $e->getMessage()]);
        }
    }
}
