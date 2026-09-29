<?php

namespace App\Providers;

/**
 * Secondary USD/LKR source, no key required. Updates once a day.
 * GET https://open.er-api.com/v6/latest/USD -> rates.LKR, time_last_update_unix
 * Terms require attribution ("Rates By Exchange Rate API") whenever this source is used.
 */
class ExchangeRateApiOpen
{
    public const ATTRIBUTION_TEXT = 'Rates By Exchange Rate API';
    public const ATTRIBUTION_URL = 'https://www.exchangerate-api.com';

    public static function read(): array
    {
        $result = HttpClient::getJson('https://open.er-api.com/v6/latest/USD');

        if (!$result['ok']) {
            return Reading::failure('exchangerate_api_open', $result['error'] ?? 'http_' . $result['status']);
        }

        $body = $result['body'];
        if (($body['result'] ?? 'success') !== 'success') {
            return Reading::failure('exchangerate_api_open', 'provider_error');
        }

        $rate = $body['rates']['LKR'] ?? null;
        $timestamp = $body['time_last_update_unix'] ?? time();

        if (!is_numeric($rate)) {
            return Reading::failure('exchangerate_api_open', 'missing_lkr_rate');
        }

        return Reading::success('exchangerate_api_open', (float) $rate, 'LKR', (int) $timestamp, $body);
    }
}
