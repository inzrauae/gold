<?php

namespace App\Providers;

/**
 * Secondary gold spot source (paid, documented, independent of Gold-API.com).
 * GET https://api.metalpriceapi.com/v1/latest?base=USD&currencies=XAU,LKR
 * -> rates.USDXAU (troy ounces of XAU per 1 USD, so price = 1/rate), rates.USDLKR, timestamp
 */
class MetalpriceApi
{
    public static function readGold(string $apiKey): array
    {
        if ($apiKey === '') {
            return Reading::failure('metalpriceapi', 'not_configured');
        }

        $result = HttpClient::getJson(
            'https://api.metalpriceapi.com/v1/latest?base=USD&currencies=XAU,LKR',
            ['X-API-KEY' => $apiKey]
        );

        if (!$result['ok']) {
            return Reading::failure('metalpriceapi', $result['error'] ?? 'http_' . $result['status']);
        }

        $body = $result['body'];
        if (($body['success'] ?? true) === false) {
            $message = $body['error']['message'] ?? 'provider_error';
            return Reading::failure('metalpriceapi', (string) $message);
        }

        $rates = $body['rates'] ?? [];
        $rate = $rates['USDXAU'] ?? $rates['XAU'] ?? null;

        if (!is_numeric($rate) || (float) $rate <= 0) {
            return Reading::failure('metalpriceapi', 'missing_gold_rate');
        }

        $pricePerOunce = 1 / (float) $rate;
        $timestamp = (int) ($body['timestamp'] ?? time());

        return Reading::success('metalpriceapi', $pricePerOunce, 'USD', $timestamp, $body);
    }

    public static function readFx(string $apiKey): array
    {
        if ($apiKey === '') {
            return Reading::failure('metalpriceapi', 'not_configured');
        }

        $result = HttpClient::getJson(
            'https://api.metalpriceapi.com/v1/latest?base=USD&currencies=LKR',
            ['X-API-KEY' => $apiKey]
        );

        if (!$result['ok']) {
            return Reading::failure('metalpriceapi', $result['error'] ?? 'http_' . $result['status']);
        }

        $body = $result['body'];
        $rates = $body['rates'] ?? [];
        $rate = $rates['USDLKR'] ?? $rates['LKR'] ?? null;

        if (!is_numeric($rate)) {
            return Reading::failure('metalpriceapi', 'missing_fx_rate');
        }

        $timestamp = (int) ($body['timestamp'] ?? time());

        return Reading::success('metalpriceapi', (float) $rate, 'LKR', $timestamp, $body);
    }
}
