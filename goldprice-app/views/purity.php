<?php
/** @var int $karat */
/** @var array|null $latest */
?>
<section class="wrap">
    <h1><?= $karat ?>K Gold Price Today in Sri Lanka</h1>
    <?php if ($latest): ?>
    <div class="price-grid single">
        <div class="price-card">
            <div class="karat"><?= $karat ?>K</div>
            <div class="lkr">LKR <?= number_format((float) $latest["price_{$karat}k_gram"], 2) ?></div>
            <div class="unit">per gram</div>
        </div>
    </div>
    <table class="price-table">
        <tr><td>Per gram</td><td>LKR <?= number_format((float) $latest["price_{$karat}k_gram"], 2) ?></td></tr>
        <tr><td>Per 8 grams (pawn)</td><td>LKR <?= number_format((float) $latest["price_{$karat}k_8g"], 2) ?></td></tr>
        <tr><td>Per troy ounce</td><td>LKR <?= number_format((float) $latest["price_{$karat}k_oz"], 2) ?></td></tr>
    </table>
    <p class="as-of">As of <?= date('j M Y, g:i A', strtotime($latest['created_at'])) ?></p>
    <?php else: ?>
    <p>No verified price has been published yet.</p>
    <?php endif; ?>
    <p><a href="/">&larr; All purities and calculator</a></p>
</section>
