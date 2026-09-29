<?php

namespace App\Providers;

/**
 * Alternative gold spot source (goldprice.dev). Free tier works without a key;
 * a key raises the rate limit.
 * GET https://api.goldprice.dev/v1/spot/XAU -> { price, quote_currency, unit, computed_at }
 * price/bid/ask are Decimal-as-string (Pydantic), so cast with (float) rather than assuming a JSON number.
 */
class GoldPriceDev
{
    public static function read(string $apiKey = ''): array
    {
        $headers = $apiKey !== '' ? ['X-API-Key' => $apiKey] : [];

        $result = HttpClient::getJson('https://api.goldprice.dev/v1/spot/XAU', $headers);

        if (!$result['ok']) {
            return Reading::failure('goldprice_dev', $result['error'] ?? 'http_' . $result['status']);
        }

        $body = $result['body'];
        $price = $body['price'] ?? null;
        $currency = $body['quote_currency'] ?? 'USD';
        $unit = $body['unit'] ?? 'troy_ounce';
        $computedAt = $body['computed_at'] ?? null;

        if (!is_numeric($price)) {
            return Reading::failure('goldprice_dev', 'missing_price_field');
        }

        if ($unit !== 'troy_ounce') {
            return Reading::failure('goldprice_dev', 'unexpected_unit_' . (string) $unit);
        }

        if (!empty($body['is_stale'])) {
            return Reading::failure('goldprice_dev', 'stale_reading');
        }

        $timestamp = $computedAt ? strtotime((string) $computedAt) : time();

        return Reading::success('goldprice_dev', (float) $price, (string) $currency, $timestamp ?: time(), $body);
    }
}
