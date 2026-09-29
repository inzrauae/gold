<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Env;
use App\Core\Logger;

/**
 * Decides what is due and runs it: a price update every PRICE_UPDATE_INTERVAL
 * minutes while markets are open (MARKET_CLOSED_INTERVAL at weekends, faster
 * retry after a failed update), news every NEWS_UPDATE_INTERVAL minutes, and a
 * daily clean-up. A lock file prevents overlapping runs.
 */
class Scheduler
{
    public function run(): array
    {
        $lockFile = APP_DIR . '/storage/locks/scheduler.lock';
        $handle = fopen($lockFile, 'c');
        if ($handle === false || !flock($handle, LOCK_EX | LOCK_NB)) {
            return ['ran' => false, 'reason' => 'already_running'];
        }

        try {
            return $this->tick();
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function tick(): array
    {
        $state = $this->state();
        $ran = [];

        if ($this->priceUpdateDue($state)) {
            $result = (new PriceUpdater())->run();
            $this->touch('last_price_attempt_at');
            if (($result['status'] ?? null) === 'published') {
                $this->touch('last_price_success_at');
            }
            $ran['price_update'] = $result;
        }

        if ($this->newsDue($state)) {
            try {
                $ran['news'] = (new NewsService())->fetch();
            } catch (\Throwable $e) {
                Logger::warning('news_fetch_failed', ['error' => $e->getMessage()]);
            }
            $this->touch('last_news_attempt_at');
        }

        if ($this->cleanupDue($state)) {
            Logger::purgeOld(Env::int('LOG_RETENTION_DAYS', 30));
            $this->touch('last_cleanup_at');
            $ran['cleanup'] = true;
        }

        return ['ran' => true, 'tasks' => $ran];
    }

    private function state(): array
    {
        $stmt = Database::connection()->query('SELECT * FROM scheduler_state WHERE id = 1');
        return $stmt->fetch() ?: [];
    }

    private function touch(string $column): void
    {
        $pdo = Database::connection();
        $now = Database::driver() === 'sqlite' ? "datetime('now')" : 'NOW()';
        $pdo->exec("UPDATE scheduler_state SET {$column} = {$now} WHERE id = 1");
    }

    private function minutesSince(?string $timestamp): float
    {
        if (!$timestamp) {
            return PHP_FLOAT_MAX;
        }
        $ts = strtotime($timestamp . ' UTC') ?: strtotime($timestamp);
        return (time() - $ts) / 60;
    }

    public function isMarketOpen(): bool
    {
        $dayOfWeek = (int) date('N');
        if ($dayOfWeek >= 6) {
            return false;
        }
        $holidays = array_filter(array_map('trim', explode(',', (string) Env::get('GOLD_MARKET_HOLIDAYS', ''))));
        return !in_array(date('Y-m-d'), $holidays, true);
    }

    private function priceUpdateDue(array $state): bool
    {
        $lastAttempt = $state['last_price_attempt_at'] ?? null;
        $lastSuccess = $state['last_price_success_at'] ?? null;

        $interval = $this->isMarketOpen()
            ? Env::int('PRICE_UPDATE_INTERVAL', 15)
            : Env::int('MARKET_CLOSED_INTERVAL', 180);

        $lastAttemptFailed = $lastAttempt !== $lastSuccess;
        if ($lastAttemptFailed && $lastAttempt) {
            return $this->minutesSince($lastAttempt) >= Env::int('RETRY_AFTER_MINUTES', 5);
        }

        return $this->minutesSince($lastAttempt) >= $interval;
    }

    private function newsDue(array $state): bool
    {
        return $this->minutesSince($state['last_news_attempt_at'] ?? null) >= Env::int('NEWS_UPDATE_INTERVAL', 60);
    }

    private function cleanupDue(array $state): bool
    {
        return $this->minutesSince($state['last_cleanup_at'] ?? null) >= 1440;
    }
}
