<?php

use App\Core\View;

/** @var int $karat */
/** @var array|null $latest */
/** @var string $date */
?>
<section class="wrap">
    <p class="breadcrumb"><a href="/">Home</a> &rsaquo; <?= $karat ?>K Gold Price</p>
    <h1><?= $karat ?>K Gold Price Today in Sri Lanka - <?= View::e($date) ?></h1>
    <?php if ($latest): ?>
    <p class="lede">
        The <?= $karat ?>K (<?= $karat ?> carat) gold price in Sri Lanka today, <?= View::e($date) ?>, is
        <strong>LKR <?= number_format((float) $latest["price_{$karat}k_gram"], 2) ?> per gram</strong>
        (LKR <?= number_format((float) $latest["price_{$karat}k_8g"], 2) ?> per 8g pawn/pound), live as of
        <?= date('j M Y, g:i A', strtotime($latest['created_at'])) ?>. This is a verified, indicative
        market-rate conversion for Colombo and all of Sri Lanka - updated daily.
    </p>
    <div class="price-grid single">
        <div class="price-card">
            <div class="karat"><?= $karat ?>K</div>
            <div class="lkr">LKR <?= number_format((float) $latest["price_{$karat}k_gram"], 2) ?></div>
            <div class="unit">per gram</div>
        </div>
    </div>
    <table class="price-table">
        <tr><td>Per gram</td><td>LKR <?= number_format((float) $latest["price_{$karat}k_gram"], 2) ?></td></tr>
        <tr><td>Per 8 grams (pawn / pound)</td><td>LKR <?= number_format((float) $latest["price_{$karat}k_8g"], 2) ?></td></tr>
        <tr><td>Per troy ounce</td><td>LKR <?= number_format((float) $latest["price_{$karat}k_oz"], 2) ?></td></tr>
    </table>
    <p class="as-of">As of <?= date('j M Y, g:i A', strtotime($latest['created_at'])) ?> · Updated daily</p>
    <?php else: ?>
    <p>No verified price has been published yet.</p>
    <?php endif; ?>
    <p>
        <a href="/">&larr; All purities and calculator</a> ·
        <a href="/gold-price-per-gram-sri-lanka">Per-gram table</a> ·
        <a href="/gold-price-per-8-grams-sri-lanka">Per-8g (pawn/pound) table</a> ·
        <a href="/gold-price-history">Price history &amp; chart</a>
    </p>
</section>
