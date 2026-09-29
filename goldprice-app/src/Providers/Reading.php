<?php

namespace App\Providers;

/**
 * Standard shape returned by every gold / FX provider so PriceUpdater can treat
 * them identically regardless of which vendor answered.
 */
class Reading
{
    public static function success(string $source, float $value, string $currency, int $timestamp, array $raw = []): array
    {
        return [
            'ok' => true,
            'source' => $source,
            'value' => $value,
            'currency' => $currency,
            'timestamp' => $timestamp,
            'error' => null,
            'raw' => $raw,
        ];
    }

    public static function failure(string $source, string $error): array
    {
        return [
            'ok' => false,
            'source' => $source,
            'value' => null,
            'currency' => null,
            'timestamp' => null,
            'error' => $error,
            'raw' => [],
        ];
    }
}
