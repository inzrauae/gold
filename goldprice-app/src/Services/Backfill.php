<?php

namespace App\Services;

use App\Core\Env;
use App\Pricing\GoldPriceCalculator;
use App\Providers\HttpClient;
use App\Providers\XausCom;

/**
 * Imports real daily closes for dates before the site started collecting live
 * data, one MetalpriceAPI /v1/timeframe request per 365-day chunk. Imported
 * days are marked is_imported=1 and never overwrite a live day.
 */
class Backfill
{
    public function run(string $from, string $to): array
    {
        $apiKey = Env::get('GOLD_PRICE_SECONDARY_API_KEY', '');
        if ($apiKey === '') {
            return ['ok' => false, 'error' => 'GOLD_PRICE_SECONDARY_API_KEY (MetalpriceAPI) is required for backfill'];
        }

        $repo = new PriceRepository();
        $imported = 0;
        $errors = [];

        foreach ($this->chunks($from, $to, 365) as [$chunkFrom, $chunkTo]) {
            $url = sprintf(
                'https://api.metalpriceapi.com/v1/timeframe?base=USD&currencies=XAU,LKR&start_date=%s&end_date=%s',
                $chunkFrom,
                $chunkTo
            );
            $result = HttpClient::getJson($url, ['X-API-KEY' => $apiKey], 30);

            if (!$result['ok'] || empty($result['body']['rates'])) {
                $errors[] = "chunk {$chunkFrom}..{$chunkTo}: " . ($result['error'] ?? 'no rates returned');
                continue;
            }

            foreach ($result['body']['rates'] as $date => $rates) {
                $xauRate = $rates['USDXAU'] ?? $rates['XAU'] ?? null;
                $lkrRate = $rates['USDLKR'] ?? $rates['LKR'] ?? null;
                if (!is_numeric($xauRate) || !is_numeric($lkrRate) || (float) $xauRate <= 0) {
                    continue;
                }

                $spot = 1 / (float) $xauRate;
                $usdLkr = (float) $lkrRate;
                $calculated = GoldPriceCalculator::calculateAll($spot, $usdLkr);

                $repo->upsertDailyHistory($date, [
                    'spot_usd_per_oz' => $spot,
                    'usd_lkr' => $usdLkr,
                    'price_24k_gram' => $calculated[24]['per_gram'],
                    'price_22k_gram' => $calculated[22]['per_gram'],
                    'price_21k_gram' => $calculated[21]['per_gram'],
                    'price_18k_gram' => $calculated[18]['per_gram'],
                    'is_imported' => 1,
                ]);
                $imported++;
            }
        }

        return ['ok' => true, 'imported' => $imported, 'errors' => $errors];
    }

    /**
     * Chart-only alternative to run(): free/keyless, no API key required, but xaus.com
     * only gives XAU/USD closes (no historical USD/LKR), so every backfilled day is
     * priced with today's current usd_lkr rate rather than that day's real rate. Never
     * used for the live verified price - only to give the history chart a real XAU/USD
     * curve instead of a straight line before a MetalpriceAPI key is configured.
     */
    public function runFromXaus(string $from, string $to): array
    {
        $repo = new PriceRepository();
        $latest = $repo->latest();
        if (!$latest || !is_numeric($latest['usd_lkr'] ?? null)) {
            return ['ok' => false, 'error' => 'no current usd_lkr rate available - run cli/update-prices.php first'];
        }
        $usdLkr = (float) $latest['usd_lkr'];

        $history = XausCom::history();
        if (!$history['ok']) {
            return ['ok' => false, 'error' => 'xaus.com: ' . $history['error']];
        }

        $liveDates = [];
        foreach ($repo->historyBetween($from, $to) as $row) {
            if ((int) ($row['is_imported'] ?? 0) === 0) {
                $liveDates[$row['date']] = true;
            }
        }

        $imported = 0;
        foreach ($history['points'] as $point) {
            $date = $point['d'] ?? null;
            $spot = $point['c'] ?? null;
            if (!$date || $date < $from || $date > $to || isset($liveDates[$date])) {
                continue;
            }
            if (!is_numeric($spot) || (float) $spot <= 0) {
                continue;
            }

            $calculated = GoldPriceCalculator::calculateAll((float) $spot, $usdLkr);

            $repo->upsertDailyHistory($date, [
                'spot_usd_per_oz' => (float) $spot,
                'usd_lkr' => $usdLkr,
                'price_24k_gram' => $calculated[24]['per_gram'],
                'price_22k_gram' => $calculated[22]['per_gram'],
                'price_21k_gram' => $calculated[21]['per_gram'],
                'price_18k_gram' => $calculated[18]['per_gram'],
                'is_imported' => 1,
            ]);
            $imported++;
        }

        return ['ok' => true, 'imported' => $imported, 'errors' => []];
    }

    private function chunks(string $from, string $to, int $maxDays): iterable
    {
        $start = new \DateTimeImmutable($from);
        $end = new \DateTimeImmutable($to);

        while ($start <= $end) {
            $chunkEnd = $start->modify("+{$maxDays} days");
            if ($chunkEnd > $end) {
                $chunkEnd = $end;
            }
            yield [$start->format('Y-m-d'), $chunkEnd->format('Y-m-d')];
            $start = $chunkEnd->modify('+1 day');
        }
    }
}
