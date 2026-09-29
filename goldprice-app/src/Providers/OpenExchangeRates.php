<?php

namespace App\Providers;

/**
 * Primary USD/LKR source. Free plan updates hourly, 1,000 requests/month.
 * GET https://openexchangerates.org/api/latest.json?symbols=LKR -> rates.LKR, timestamp
 * Auth: Authorization: Token <app id>
 */
class OpenExchangeRates
{
    public static function read(string $appId): array
    {
        if ($appId === '') {
            return Reading::failure('open_exchange_rates', 'not_configured');
        }

        $result = HttpClient::getJson(
            'https://openexchangerates.org/api/latest.json?symbols=LKR',
            ['Authorization' => 'Token ' . $appId]
        );

        if (!$result['ok']) {
            return Reading::failure('open_exchange_rates', $result['error'] ?? 'http_' . $result['status']);
        }

        $body = $result['body'];
        $rate = $body['rates']['LKR'] ?? null;
        $timestamp = $body['timestamp'] ?? time();

        if (!is_numeric($rate)) {
            return Reading::failure('open_exchange_rates', 'missing_lkr_rate');
        }

        return Reading::success('open_exchange_rates', (float) $rate, 'LKR', (int) $timestamp, $body);
    }
}
