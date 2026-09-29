<?php

namespace App\Providers;

/**
 * Alternative keyed USD/LKR source (exchangerate_api). Key is redacted from all logs.
 * GET https://v6.exchangerate-api.com/v6/<key>/latest/USD -> rates.LKR, time_last_update_unix
 */
class ExchangeRateApiKeyed
{
    public static function read(string $apiKey): array
    {
        if ($apiKey === '') {
            return Reading::failure('exchangerate_api', 'not_configured');
        }

        $result = HttpClient::getJson("https://v6.exchangerate-api.com/v6/{$apiKey}/latest/USD");

        if (!$result['ok']) {
            return Reading::failure('exchangerate_api', $result['error'] ?? 'http_' . $result['status']);
        }

        $body = $result['body'];
        $rate = $body['conversion_rates']['LKR'] ?? null;
        $timestamp = $body['time_last_update_unix'] ?? time();

        if (!is_numeric($rate)) {
            return Reading::failure('exchangerate_api', 'missing_lkr_rate');
        }

        return Reading::success('exchangerate_api', (float) $rate, 'LKR', (int) $timestamp, $body);
    }
}
