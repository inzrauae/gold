<?php

namespace App\Providers;

/**
 * Primary gold spot source. Free, no API key, no stated rate limit.
 * GET https://api.gold-api.com/price/XAU -> { price, currency, updatedAt }
 */
class GoldApiCom
{
    public static function read(): array
    {
        $result = HttpClient::getJson('https://api.gold-api.com/price/XAU');

        if (!$result['ok']) {
            return Reading::failure('gold_api_com', $result['error'] ?? 'http_' . $result['status']);
        }

        $body = $result['body'];
        $price = $body['price'] ?? null;
        $currency = $body['currency'] ?? 'USD';
        $updatedAt = $body['updatedAt'] ?? null;

        if (!is_numeric($price)) {
            return Reading::failure('gold_api_com', 'missing_price_field');
        }

        $timestamp = $updatedAt ? strtotime((string) $updatedAt) : time();

        return Reading::success('gold_api_com', (float) $price, (string) $currency, $timestamp ?: time(), $body);
    }
}
