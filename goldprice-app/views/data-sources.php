<?php

use App\Core\View;

/** @var array|null $latest */
/** @var array $steps */
?>
<section class="wrap">
    <h1>Data Sources &amp; Methodology</h1>
    <p>We publish <strong>verified, indicative</strong> gold prices - never typed in by hand, estimated,
    interpolated, or scraped from jeweller sites.</p>

    <h2>Gold spot price (XAU/USD)</h2>
    <ul>
        <li><strong>Primary:</strong> Gold-API.com - real-time, no key required.</li>
        <li><strong>Secondary (independent check):</strong> MetalpriceAPI, or GoldAPI.io, when configured.</li>
    </ul>

    <h2>USD/LKR exchange rate</h2>
    <ul>
        <li><strong>Primary:</strong> Open Exchange Rates - hourly.</li>
        <li><strong>Secondary:</strong> ExchangeRate-API open access (Rates By Exchange Rate API), or a keyed
        provider when configured.</li>
    </ul>

    <p>The Central Bank of Sri Lanka publishes an official indicative USD/LKR spot rate each business day at
    <a href="https://www.cbsl.gov.lk" target="_blank" rel="noopener noreferrer">cbsl.gov.lk</a> &rarr;
    Rates and Indicators &rarr; Exchange Rates. It has no public API, so it is referenced here rather than
    used automatically. Market mid rates normally sit very close to it.</p>

    <h2>Verification</h2>
    <ol class="method-steps">
        <?php foreach ($steps as $step): ?>
        <li><?= View::e($step) ?></li>
        <?php endforeach; ?>
    </ol>

    <?php if ($latest): ?>
    <h2>Current Sources In Use</h2>
    <table class="price-table">
        <tr><td>Gold primary</td><td><?= View::e($latest['gold_source_primary']) ?></td></tr>
        <tr><td>Gold secondary</td><td><?= View::e($latest['gold_source_secondary'] ?? 'not configured') ?></td></tr>
        <tr><td>FX primary</td><td><?= View::e($latest['fx_source_primary']) ?></td></tr>
        <tr><td>FX secondary</td><td><?= View::e($latest['fx_source_secondary'] ?? 'not configured') ?></td></tr>
        <tr><td>Verification mode</td><td><?= View::e($latest['verification_mode']) ?></td></tr>
    </table>
    <?php endif; ?>

    <h2>Limitations</h2>
    <p>These are indicative market-rate conversions, not a jeweller's retail selling price, which includes
    making charges, taxes and margins. Treat this as a reference, not a quote.</p>
</section>
