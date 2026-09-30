<?php

use App\Core\View;

/** @var string $unit */
/** @var array|null $latest */
/** @var string $date */
$suffix = $unit === 'gram' ? '_gram' : '_8g';
$label = $unit === 'gram' ? 'per gram' : 'per 8 grams (pawn/pound)';
$isPawn = $unit !== 'gram';
?>
<section class="wrap">
    <p class="breadcrumb"><a href="/">Home</a> &rsaquo; <?= $isPawn ? 'Per 8g (Pawn/Pound)' : 'Per Gram' ?></p>
    <h1><?= $isPawn ? 'Gold Pawn / Pound Price in Sri Lanka Today (8g)' : 'Gold Price Per Gram in Sri Lanka Today' ?> - <?= View::e($date) ?></h1>
    <?php if ($isPawn): ?>
    <p class="lede">
        "Pawn", "pound" (paun) and "sovereign" all refer to the same 8-gram gold unit widely used in
        Sri Lanka's jewellery and pawning market. The verified price below is the same for every name.
    </p>
    <?php else: ?>
    <p class="lede">Live gold price per gram in Sri Lanka today, <?= View::e($date) ?>, for every purity sold locally.</p>
    <?php endif; ?>
    <table class="price-table">
        <thead><tr><th>Purity</th><th>Price <?= $label ?></th></tr></thead>
        <tbody>
        <?php foreach ([24, 22, 21, 18] as $k): ?>
            <tr>
                <td><a href="/gold-price-<?= $k ?>k-sri-lanka"><?= $k ?>K (<?= $k ?> carat)</a></td>
                <td>LKR <?= $latest ? number_format((float) $latest["price_{$k}k{$suffix}"], 2) : '-' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($latest): ?>
    <p class="as-of">As of <?= date('j M Y, g:i A', strtotime($latest['created_at'])) ?> · Updated daily</p>
    <?php endif; ?>
    <p>
        <a href="/">&larr; All purities and calculator</a> ·
        <a href="<?= $isPawn ? '/gold-price-per-gram-sri-lanka' : '/gold-price-per-8-grams-sri-lanka' ?>">
            <?= $isPawn ? 'Per-gram table' : 'Per-8g (pawn/pound) table' ?>
        </a> ·
        <a href="/gold-price-history">Price history &amp; chart</a>
    </p>
</section>
