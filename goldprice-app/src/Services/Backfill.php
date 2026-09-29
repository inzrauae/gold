<?php

namespace App\Services;

use App\Core\Env;
use App\Pricing\GoldPriceCalculator;
use App\Providers\HttpClient;

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
