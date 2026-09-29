<?php
/** @var string $unit */
/** @var array|null $latest */
$suffix = $unit === 'gram' ? '_gram' : '_8g';
$label = $unit === 'gram' ? 'per gram' : 'per 8 grams (pawn)';
?>
<section class="wrap">
    <h1>Gold Price <?= $unit === 'gram' ? 'Per Gram' : 'Per 8 Grams (Pawn)' ?> in Sri Lanka</h1>
    <table class="price-table">
        <thead><tr><th>Purity</th><th>Price <?= $label ?></th></tr></thead>
        <tbody>
        <?php foreach ([24, 22, 21, 18] as $k): ?>
            <tr>
                <td><a href="/gold-price-<?= $k ?>k-sri-lanka"><?= $k ?>K</a></td>
                <td>LKR <?= $latest ? number_format((float) $latest["price_{$k}k{$suffix}"], 2) : '-' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($latest): ?>
    <p class="as-of">As of <?= date('j M Y, g:i A', strtotime($latest['created_at'])) ?></p>
    <?php endif; ?>
</section>
