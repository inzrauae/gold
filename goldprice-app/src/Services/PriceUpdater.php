<?php

namespace App\Services;

use App\Core\Cache;
use App\Core\Env;
use App\Pricing\GoldPriceCalculator;

/**
 * The verification pipeline described in the README:
 *
 *   fetch gold (primary + secondary) -> fetch USD/LKR (primary + secondary)
 *   -> validate each reading (format, currency, positive, sanity, freshness)
 *   -> cross-check sources (never averaged - primary's value is what gets used)
 *   -> compare with the last verified price (reject abnormal moves)
 *   -> calculate -> store (skip if unchanged) -> roll up -> RSS -> clear caches
 *
 * Any failed check leaves the last verified price live and logs why.
 */
class PriceUpdater
{
    private const GOLD_SANITY_MIN = 300.0;
    private const GOLD_SANITY_MAX = 10000.0;
    private const FX_SANITY_MIN = 50.0;
    private const FX_SANITY_MAX = 1000.0;

    public function __construct(
        private ProviderGateway $gateway = new ProviderGateway(),
        private PriceRepository $repository = new PriceRepository(),
    ) {
    }

    public function run(): array
    {
        $goldPrimary = $this->gateway->goldPrimary();
        $goldSecondary = $this->gateway->goldSecondary();
        $fxPrimary = $this->gateway->fxPrimary();
        $fxSecondary = $this->gateway->fxSecondary();

        $requireSecondary = Env::bool('REQUIRE_SECONDARY_SOURCE', false);
        $maxSourceDiff = Env::float('MAX_SOURCE_DIFFERENCE_PERCENT', 1);
        $maxGoldMove = Env::float('MAX_GOLD_MOVE_PERCENT', 5);
        $maxFxMove = Env::float('MAX_FX_MOVE_PERCENT', 3);

        $check = $this->validateReading($goldPrimary, 'USD', self::GOLD_SANITY_MIN, self::GOLD_SANITY_MAX, Env::int('MAX_GOLD_READING_AGE_MINUTES', 90));
        if ($check !== null) {
            return $this->fail('gold_primary_' . $check, compact('goldPrimary'));
        }

        $goldSecondaryOk = $goldSecondary['ok']
            && $this->validateReading($goldSecondary, 'USD', self::GOLD_SANITY_MIN, self::GOLD_SANITY_MAX, Env::int('MAX_GOLD_READING_AGE_MINUTES', 90)) === null;

        if ($requireSecondary && !$goldSecondaryOk) {
            return $this->fail('gold_secondary_unavailable', compact('goldSecondary'));
        }

        if ($goldSecondaryOk) {
            $diff = GoldPriceCalculator::percentDifference($goldPrimary['value'], $goldSecondary['value']);
            if ($diff > $maxSourceDiff) {
                return $this->fail('gold_sources_disagree', ['diff_percent' => round($diff, 3), 'primary' => $goldPrimary, 'secondary' => $goldSecondary]);
            }
        }

        $check = $this->validateReading($fxPrimary, 'LKR', self::FX_SANITY_MIN, self::FX_SANITY_MAX, Env::int('MAX_FX_READING_AGE_MINUTES', 180));
        if ($check !== null) {
            return $this->fail('fx_primary_' . $check, compact('fxPrimary'));
        }

        $fxSecondaryOk = $fxSecondary['ok']
            && $this->validateReading($fxSecondary, 'LKR', self::FX_SANITY_MIN, self::FX_SANITY_MAX, Env::int('MAX_FX_READING_AGE_MINUTES', 180)) === null;

        if ($requireSecondary && !$fxSecondaryOk) {
            return $this->fail('fx_secondary_unavailable', compact('fxSecondary'));
        }

        if ($fxSecondaryOk) {
            $diff = GoldPriceCalculator::percentDifference($fxPrimary['value'], $fxSecondary['value']);
            if ($diff > $maxSourceDiff) {
                return $this->fail('fx_sources_disagree', ['diff_percent' => round($diff, 3), 'primary' => $fxPrimary, 'secondary' => $fxSecondary]);
            }
        }

        $verificationMode = ($goldSecondaryOk && $fxSecondaryOk) ? 'dual_source' : 'single_source';

        $goldValue = (float) $goldPrimary['value'];
        $fxValue = (float) $fxPrimary['value'];

        $last = $this->repository->latest();

        if ($last) {
            $goldMove = GoldPriceCalculator::percentDifference($goldValue, (float) $last['spot_usd_per_oz']);
            $fxMove = GoldPriceCalculator::percentDifference($fxValue, (float) $last['usd_lkr']);

            if ($goldMove > $maxGoldMove || $fxMove > $maxFxMove) {
                $payload = [
                    'gold_primary' => $goldPrimary,
                    'gold_secondary' => $goldSecondary,
                    'fx_primary' => $fxPrimary,
                    'fx_secondary' => $fxSecondary,
                    'verification_mode' => $verificationMode,
                    'gold_move_percent' => round($goldMove, 3),
                    'fx_move_percent' => round($fxMove, 3),
                ];
                $reason = $goldMove > $maxGoldMove ? 'abnormal_gold_move' : 'abnormal_fx_move';
                $this->repository->createPendingChange($reason, $payload);
                return $this->fail($reason, $payload, 'held');
            }
        }

        return $this->publish($goldPrimary, $goldSecondary, $fxPrimary, $fxSecondary, $verificationMode, $goldValue, $fxValue);
    }

    /**
     * Force-publish a reading that was held for an abnormal move. Still requires
     * both sources to have agreed at the time it was captured.
     */
    public function acceptPendingChange(array $pendingPayload): array
    {
        if (($pendingPayload['verification_mode'] ?? null) !== 'dual_source') {
            return $this->fail('accept_requires_dual_source', $pendingPayload);
        }

        return $this->publish(
            $pendingPayload['gold_primary'],
            $pendingPayload['gold_secondary'],
            $pendingPayload['fx_primary'],
            $pendingPayload['fx_secondary'],
            $pendingPayload['verification_mode'],
            (float) $pendingPayload['gold_primary']['value'],
            (float) $pendingPayload['fx_primary']['value'],
        );
    }

    private function publish(array $goldPrimary, array $goldSecondary, array $fxPrimary, array $fxSecondary, string $verificationMode, float $goldValue, float $fxValue): array
    {
        $calculated = GoldPriceCalculator::calculateAll($goldValue, $fxValue);
        $last = $this->repository->latest();

        if ($last && $this->isUnchanged($last, $calculated)) {
            $this->repository->log('unchanged');
            return ['status' => 'unchanged', 'reason' => null];
        }

        $row = [
            'spot_usd_per_oz' => $goldValue,
            'usd_lkr' => $fxValue,
            'price_24k_gram' => $calculated[24]['per_gram'],
            'price_22k_gram' => $calculated[22]['per_gram'],
            'price_21k_gram' => $calculated[21]['per_gram'],
            'price_18k_gram' => $calculated[18]['per_gram'],
            'price_24k_8g' => $calculated[24]['per_8g'],
            'price_22k_8g' => $calculated[22]['per_8g'],
            'price_21k_8g' => $calculated[21]['per_8g'],
            'price_18k_8g' => $calculated[18]['per_8g'],
            'price_24k_oz' => $calculated[24]['per_ounce'],
            'price_22k_oz' => $calculated[22]['per_ounce'],
            'price_21k_oz' => $calculated[21]['per_ounce'],
            'price_18k_oz' => $calculated[18]['per_ounce'],
            'gold_source_primary' => $goldPrimary['source'],
            'gold_source_secondary' => $goldSecondary['ok'] ? $goldSecondary['source'] : null,
            'fx_source_primary' => $fxPrimary['source'],
            'fx_source_secondary' => $fxSecondary['ok'] ? $fxSecondary['source'] : null,
            'verification_mode' => $verificationMode,
            'gold_reading_timestamp' => $goldPrimary['timestamp'],
            'fx_reading_timestamp' => $fxPrimary['timestamp'],
        ];

        $this->repository->insert($row);
        $this->repository->log('published', null, ['verification_mode' => $verificationMode]);

        $this->repository->upsertDailyHistory(date('Y-m-d'), [
            'spot_usd_per_oz' => $goldValue,
            'usd_lkr' => $fxValue,
            'price_24k_gram' => $calculated[24]['per_gram'],
            'price_22k_gram' => $calculated[22]['per_gram'],
            'price_21k_gram' => $calculated[21]['per_gram'],
            'price_18k_gram' => $calculated[18]['per_gram'],
            'is_imported' => 0,
        ]);

        (new RssFeed())->regenerate();
        Cache::forget('latest_price');
        Cache::forget('home_page_data');

        return ['status' => 'published', 'reason' => null, 'row' => $row];
    }

    private function isUnchanged(array $last, array $calculated): bool
    {
        foreach ([24, 22, 21, 18] as $karat) {
            if ((float) $last["price_{$karat}k_gram"] !== $calculated[$karat]['per_gram']) {
                return false;
            }
        }
        return true;
    }

    private function fail(string $reason, array $context = [], string $status = 'failed'): array
    {
        $this->repository->log($status, $reason, $this->redact($context));
        AlertService::notify($reason);
        return ['status' => $status, 'reason' => $reason];
    }

    /**
     * Never let a raw provider payload (which could echo back a key in an error
     * message) reach the log file.
     */
    private function redact(array $context): array
    {
        array_walk_recursive($context, function (&$value, $key) {
            if (is_string($key) && preg_match('/key|token|auth/i', $key)) {
                $value = '[redacted]';
            }
        });
        return $context;
    }

    private function validateReading(array $reading, string $expectedCurrency, float $min, float $max, int $maxAgeMinutes): ?string
    {
        if (!$reading['ok']) {
            return 'unavailable';
        }
        if (!is_numeric($reading['value']) || $reading['value'] <= 0) {
            return 'invalid_value';
        }
        if ($reading['currency'] !== $expectedCurrency) {
            return 'wrong_currency';
        }
        if ($reading['value'] < $min || $reading['value'] > $max) {
            return 'out_of_bounds';
        }
        $ageMinutes = (time() - $reading['timestamp']) / 60;
        if ($ageMinutes > $maxAgeMinutes) {
            return 'stale';
        }
        return null;
    }
}
