<?php

namespace App\Providers;

/**
 * Historical XAU/USD daily closes for the chart's backfill (used for chart display
 * only - never for the live verified price). Free, no API key.
 * GET https://xaus.com/api/v1/history -> { symbol, interval, currency, unit, points: [{d, c, h, l}] }
 * Always returns USD/troy_oz regardless of query params, back to ~2021.
 */
class XausCom
{
    public static function history(): array
    {
        $result = HttpClient::getJson('https://xaus.com/api/v1/history', [], 30);

        if (!$result['ok'] || empty($result['body']['points'])) {
            return ['ok' => false, 'error' => $result['error'] ?? 'no_history_points', 'points' => []];
        }

        return ['ok' => true, 'error' => null, 'points' => $result['body']['points']];
    }
}
