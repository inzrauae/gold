<?php

namespace App\Providers;

/**
 * Alternative gold spot source (goldapi_io). Small free tier.
 * GET https://www.goldapi.io/api/XAU/USD -> { price, timestamp }
 */
class GoldApiIo
{
    public static function read(string $apiKey): array
    {
        if ($apiKey === '') {
            return Reading::failure('goldapi_io', 'not_configured');
        }

        $result = HttpClient::getJson('https://www.goldapi.io/api/XAU/USD', [
            'x-access-token' => $apiKey,
        ]);

        if (!$result['ok']) {
            return Reading::failure('goldapi_io', $result['error'] ?? 'http_' . $result['status']);
        }

        $body = $result['body'];
        $price = $body['price'] ?? null;
        $timestamp = $body['timestamp'] ?? time();

        if (!is_numeric($price)) {
            return Reading::failure('goldapi_io', 'missing_price_field');
        }

        return Reading::success('goldapi_io', (float) $price, 'USD', (int) $timestamp, $body);
    }
}
