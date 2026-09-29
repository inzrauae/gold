<?php

namespace App\Pricing;

/**
 * Single source of truth for all gold price maths.
 *
 * price per gram (24K) = spot(USD/oz) * USD/LKR / TROY_OUNCE_GRAMS
 * price per gram (kK)  = price per gram (24K) * kK / 24
 * Every other figure (per 8g "pawn", per troy ounce, other purities) is derived
 * from that one number. Rounding happens exactly once, at the very end.
 */
class GoldPriceCalculator
{
    public const TROY_OUNCE_GRAMS = 31.1034768;
    public const PAWN_GRAMS = 8;

    /** @var array<int, string> */
    public const PURITIES = [24, 22, 21, 18];

    public static function pricePerGram(float $spotUsdPerOz, float $usdLkr, int $karat): float
    {
        $per24kGram = $spotUsdPerOz * $usdLkr / self::TROY_OUNCE_GRAMS;
        $perKaratGram = $per24kGram * $karat / 24;

        return self::round($perKaratGram);
    }

    public static function pricePerEightGrams(float $spotUsdPerOz, float $usdLkr, int $karat): float
    {
        $per24kGram = $spotUsdPerOz * $usdLkr / self::TROY_OUNCE_GRAMS;
        $perKaratGram = $per24kGram * $karat / 24;

        return self::round($perKaratGram * self::PAWN_GRAMS);
    }

    public static function pricePerOunce(float $spotUsdPerOz, float $usdLkr, int $karat): float
    {
        $per24kOunce = $spotUsdPerOz * $usdLkr;
        $perKaratOunce = $per24kOunce * $karat / 24;

        return self::round($perKaratOunce);
    }

    /**
     * Full breakdown for every supported purity, keyed by karat.
     *
     * @return array<int, array{karat:int, per_gram:float, per_8g:float, per_ounce:float}>
     */
    public static function calculateAll(float $spotUsdPerOz, float $usdLkr): array
    {
        $out = [];
        foreach (self::PURITIES as $karat) {
            $out[$karat] = [
                'karat' => $karat,
                'per_gram' => self::pricePerGram($spotUsdPerOz, $usdLkr, $karat),
                'per_8g' => self::pricePerEightGrams($spotUsdPerOz, $usdLkr, $karat),
                'per_ounce' => self::pricePerOunce($spotUsdPerOz, $usdLkr, $karat),
            ];
        }
        return $out;
    }

    public static function percentDifference(float $a, float $b): float
    {
        if ($a == 0.0 && $b == 0.0) {
            return 0.0;
        }
        $base = max(abs($a), abs($b));
        if ($base == 0.0) {
            return 0.0;
        }
        return abs($a - $b) / $base * 100;
    }

    private static function round(float $value): float
    {
        return round($value, 2);
    }
}
