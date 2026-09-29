<?php

namespace App\Controllers;

use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Response;
use App\Services\PriceRepository;
use App\Services\Seo;

class ApiController
{
    private PriceRepository $repository;

    public function __construct()
    {
        $this->repository = new PriceRepository();
    }

    public function current(Request $request): void
    {
        if (!$this->rateLimit($request)) {
            return;
        }

        $latest = $this->repository->latest();

        if (!$latest) {
            Response::json([
                'status' => 'unavailable',
                'message' => 'No verified price has been published yet.',
            ], 503);
            return;
        }

        Response::json([
            'status' => 'ok',
            'as_of' => $this->toIso($latest['created_at']),
            'verification_mode' => $latest['verification_mode'],
            'currency' => 'LKR',
            'spot_usd_per_oz' => (float) $latest['spot_usd_per_oz'],
            'usd_lkr' => (float) $latest['usd_lkr'],
            'prices' => [
                '24k' => $this->karatBlock($latest, 24),
                '22k' => $this->karatBlock($latest, 22),
                '21k' => $this->karatBlock($latest, 21),
                '18k' => $this->karatBlock($latest, 18),
            ],
            'sources' => [
                'gold_primary' => $latest['gold_source_primary'],
                'gold_secondary' => $latest['gold_source_secondary'],
                'fx_primary' => $latest['fx_source_primary'],
                'fx_secondary' => $latest['fx_source_secondary'],
            ],
            'attribution' => $this->attribution($latest),
            'source_url' => Seo::siteUrl('/data-sources'),
        ]);
    }

    public function history(Request $request): void
    {
        if (!$this->rateLimit($request)) {
            return;
        }

        $range = (string) $request->query('range', '30d');
        $days = match (true) {
            preg_match('/^(\d+)d$/', $range, $m) === 1 => (int) $m[1],
            default => 30,
        };
        $days = max(1, min($days, 3650));

        $from = date('Y-m-d', strtotime("-{$days} days"));
        $to = date('Y-m-d');
        $rows = $this->repository->historyBetween($from, $to);

        Response::json([
            'status' => 'ok',
            'range' => $range,
            'from' => $from,
            'to' => $to,
            'currency' => 'LKR',
            'data' => array_map(fn ($row) => [
                'date' => $row['date'],
                'spot_usd_per_oz' => (float) $row['spot_usd_per_oz'],
                'usd_lkr' => (float) $row['usd_lkr'],
                '24k_per_gram' => (float) $row['price_24k_gram'],
                '22k_per_gram' => (float) $row['price_22k_gram'],
                '21k_per_gram' => (float) $row['price_21k_gram'],
                '18k_per_gram' => (float) $row['price_18k_gram'],
                'imported' => (bool) $row['is_imported'],
            ], $rows),
        ]);
    }

    private function karatBlock(array $latest, int $karat): array
    {
        return [
            'per_gram' => (float) $latest["price_{$karat}k_gram"],
            'per_8g' => (float) $latest["price_{$karat}k_8g"],
            'per_troy_ounce' => (float) $latest["price_{$karat}k_oz"],
        ];
    }

    private function attribution(array $latest): ?string
    {
        if ($latest['fx_source_secondary'] === 'exchangerate_api_open') {
            return \App\Providers\ExchangeRateApiOpen::ATTRIBUTION_TEXT . ' (' . \App\Providers\ExchangeRateApiOpen::ATTRIBUTION_URL . ')';
        }
        return null;
    }

    private function toIso(string $timestamp): string
    {
        $ts = strtotime($timestamp);
        return $ts ? date('c', $ts) : $timestamp;
    }

    private function rateLimit(Request $request): bool
    {
        $allowed = RateLimiter::attempt('api:' . $request->ip(), 60, 60);
        if (!$allowed) {
            Response::json(['status' => 'error', 'message' => 'Rate limit exceeded, try again shortly.'], 429);
        }
        return $allowed;
    }
}
