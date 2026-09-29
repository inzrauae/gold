<?php

namespace App\Services;

use App\Core\Cache;
use App\Core\Env;
use App\Providers\ExchangeRateApiKeyed;
use App\Providers\ExchangeRateApiOpen;
use App\Providers\GoldApiCom;
use App\Providers\GoldApiIo;
use App\Providers\GoldPriceDev;
use App\Providers\MetalpriceApi;
use App\Providers\OpenExchangeRates;
use App\Providers\Reading;

/**
 * Picks the configured provider for each of the four slots (gold primary/secondary,
 * FX primary/secondary) and applies the *_MIN_INTERVAL reuse policy: within the
 * interval the last successful reading is replayed (with its original timestamp,
 * so it still ages towards the freshness limit) instead of making a fresh call.
 */
class ProviderGateway
{
    public function goldPrimary(): array
    {
        $provider = Env::get('GOLD_PRICE_PRIMARY_PROVIDER', 'gold_api_com');

        return match ($provider) {
            'goldapi_io' => GoldApiIo::read(Env::get('GOLDAPI_IO_KEY', '')),
            'goldprice_dev' => GoldPriceDev::read(Env::get('GOLDPRICE_DEV_API_KEY', '')),
            default => GoldApiCom::read(),
        };
    }

    public function goldSecondary(): array
    {
        $provider = Env::get('GOLD_PRICE_SECONDARY_PROVIDER', 'none');

        if ($provider === 'none' || $provider === '') {
            return Reading::failure('none', 'not_configured');
        }

        $minInterval = Env::int('GOLD_SECONDARY_MIN_INTERVAL', 45);

        return $this->cached('gold_secondary', $minInterval, function () use ($provider) {
            return match ($provider) {
                'goldapi_io' => GoldApiIo::read(Env::get('GOLDAPI_IO_KEY', '')),
                'goldprice_dev' => GoldPriceDev::read(Env::get('GOLDPRICE_DEV_API_KEY', '')),
                default => MetalpriceApi::readGold(Env::get('GOLD_PRICE_SECONDARY_API_KEY', '')),
            };
        });
    }

    public function fxPrimary(): array
    {
        $provider = Env::get('FX_PRIMARY_PROVIDER', 'open_exchange_rates');
        $minInterval = Env::int('FX_PRIMARY_MIN_INTERVAL', 60);

        return $this->cached('fx_primary', $minInterval, function () use ($provider) {
            return match ($provider) {
                'exchangerate_api_open' => ExchangeRateApiOpen::read(),
                'exchangerate_api' => ExchangeRateApiKeyed::read(Env::get('EXCHANGERATE_API_KEY', '')),
                'metalpriceapi' => MetalpriceApi::readFx(Env::get('GOLD_PRICE_SECONDARY_API_KEY', '')),
                default => OpenExchangeRates::read(Env::get('EXCHANGE_RATE_API_KEY', '')),
            };
        });
    }

    public function fxSecondary(): array
    {
        $provider = Env::get('FX_SECONDARY_PROVIDER', 'exchangerate_api_open');
        $minInterval = Env::int('FX_SECONDARY_MIN_INTERVAL', 360);

        return $this->cached('fx_secondary', $minInterval, function () use ($provider) {
            return match ($provider) {
                'exchangerate_api' => ExchangeRateApiKeyed::read(Env::get('EXCHANGERATE_API_KEY', '')),
                'metalpriceapi' => MetalpriceApi::readFx(Env::get('GOLD_PRICE_SECONDARY_API_KEY', '')),
                default => ExchangeRateApiOpen::read(),
            };
        });
    }

    private function cached(string $key, int $minIntervalMinutes, callable $fetch): array
    {
        if ($minIntervalMinutes <= 0) {
            return $fetch();
        }

        $cacheKey = 'provider_reading:' . $key;
        $cached = Cache::get($cacheKey);
        if (is_array($cached) && ($cached['ok'] ?? false)) {
            return $cached;
        }

        $reading = $fetch();
        if ($reading['ok']) {
            Cache::put($cacheKey, $reading, $minIntervalMinutes * 60);
        }

        return $reading;
    }
}
