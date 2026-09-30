<?php

namespace App\Services;

/**
 * Static editorial copy shared across pages (method explanation, FAQ). Kept in
 * one place so every page quoting "how prices are produced" stays in sync.
 */
class Content
{
    public static function methodSteps(): array
    {
        return [
            'Fetch the international gold spot price (US$ per troy ounce) from a primary source, and an independent secondary source when configured.',
            'Fetch the USD/LKR exchange rate the same way: primary plus an independent secondary source.',
            'Validate every reading: correct format, correct currency, a positive number, within a sane range, and fresh enough.',
            'Cross-check the primary and secondary reading for each figure - they must agree within a small tolerance. The primary reading is used for the calculation; readings are never averaged.',
            'Compare the result with the last verified price. An abnormally large jump is held for a human to confirm rather than published automatically.',
            'Calculate: spot price x USD/LKR rate, divided by 31.1034768 (grams per troy ounce), multiplied by karat/24. Rounding happens once, at the end.',
            'Store the new price only if it changed, roll it into the daily history, refresh the RSS feed, and clear cached pages.',
        ];
    }

    public static function faq(): array
    {
        return [
            [
                'q' => 'Where do these prices come from?',
                'a' => 'From live international gold market data and the USD/LKR exchange rate, converted with a fixed, published formula. See /data-sources for the exact providers in use right now.',
            ],
            [
                'q' => 'Are these official retail prices?',
                'a' => 'No. They are verified, indicative market-rate conversions, not a jeweller\'s selling price. Retail prices include making charges, taxes and margins that vary by seller.',
            ],
            [
                'q' => 'Why does the price sometimes not update for a while?',
                'a' => 'If a source is unavailable, disagrees with its counterpart, or looks like an abnormal jump, nothing is published until it can be verified. The last verified price stays live with its timestamp.',
            ],
            [
                'q' => 'What is a "pawn"?',
                'a' => 'A common Sri Lankan unit equal to 8 grams, used heavily in the pawning (jewellery-backed lending) market. It is also commonly called a "pound" (paun) or "sovereign" - all three names refer to the same 8-gram weight and the same price shown on our per-8-gram page.',
            ],
            [
                'q' => 'Why does gold price change so often in Sri Lanka?',
                'a' => 'Two moving parts drive it: the international gold spot price (set globally, in US dollars) and the USD/LKR exchange rate. Local factors also shape demand - and therefore what jewellers charge over this base rate - including import duty on gold, seasonal buying around the Sinhala and Tamil New Year (Avurudu) and the wedding season, and general currency conditions. Our figure tracks only the first two, verified, internationally sourced inputs.',
            ],
            [
                'q' => 'Is the gold price in Sri Lanka going up or down today?',
                'a' => 'Check the "as of" line at the top of the homepage for today\'s live direction and percentage change versus the previous verified price. Gold price moves with the international spot price and the USD/LKR exchange rate, so it can change several times a day.',
            ],
            [
                'q' => 'Does the gold price differ in Colombo versus the rest of Sri Lanka?',
                'a' => 'No. This is a national market-rate conversion based on the international gold spot price and the USD/LKR exchange rate, not a city-specific quote, so the same verified price applies in Colombo and everywhere else in Sri Lanka. Retail jewellers may still vary their selling price by making charges and location.',
            ],
            [
                'q' => 'What about gold biscuits, bars or sovereigns?',
                'a' => 'A gold biscuit or bar is simply gold sold at a specific weight (commonly 1 gram to 100 grams), and a sovereign is another name for the 8-gram unit also called a pawn or pound. Use the calculator on the homepage to price any weight at today\'s verified rate.',
            ],
            [
                'q' => 'Is there a free API?',
                'a' => 'Yes - see /gold-price-api for the JSON endpoints, and /gold-price-widget for an embeddable price widget.',
            ],
        ];
    }

    /**
     * A single, self-contained, quotable sentence comparing the latest verified price
     * to the previous day's close - drives the "gold price increase/decrease" content
     * and doubles as an AI-Overview-friendly direct answer.
     *
     * @return array{direction: string, percent: float, diff: float, sentence: string}|null
     */
    public static function trendSentence(?array $latest, ?array $previous, int $karat = 22): ?array
    {
        if (!$latest || !$previous) {
            return null;
        }

        $todayVal = (float) ($latest["price_{$karat}k_gram"] ?? 0);
        $prevVal = (float) ($previous["price_{$karat}k_gram"] ?? 0);
        if ($todayVal <= 0 || $prevVal <= 0) {
            return null;
        }

        $diff = $todayVal - $prevVal;
        $percent = ($diff / $prevVal) * 100;
        $direction = abs($percent) < 0.005 ? 'unchanged' : ($diff > 0 ? 'up' : 'down');
        $verb = $direction === 'up' ? 'increased' : ($direction === 'down' ? 'decreased' : 'stayed flat');

        $sentence = $direction === 'unchanged'
            ? sprintf(
                'Gold price in Sri Lanka today is unchanged for %dK gold per gram, holding at LKR %s compared to the previous verified update.',
                $karat,
                number_format($todayVal, 2)
            )
            : sprintf(
                'Gold price in Sri Lanka today has %s by %s%% (LKR %s) for %dK gold per gram, from LKR %s to LKR %s, compared to the previous verified update.',
                $verb,
                number_format(abs($percent), 2),
                number_format(abs($diff), 2),
                $karat,
                number_format($prevVal, 2),
                number_format($todayVal, 2)
            );

        return [
            'direction' => $direction,
            'percent' => round($percent, 2),
            'diff' => round($diff, 2),
            'sentence' => $sentence,
        ];
    }
}
