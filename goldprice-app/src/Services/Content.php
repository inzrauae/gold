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
                'a' => 'A common Sri Lankan unit equal to 8 grams, used heavily in the pawning (jewellery-backed lending) market.',
            ],
            [
                'q' => 'Is there a free API?',
                'a' => 'Yes - see /gold-price-api for the JSON endpoints, and /gold-price-widget for an embeddable price widget.',
            ],
        ];
    }
}
