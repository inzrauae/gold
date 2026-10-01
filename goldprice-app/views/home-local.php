<?php

use App\Core\View;

/** @var array $t */
/** @var array|null $latest */
/** @var string $lang */
$n = static fn ($v) => number_format((float) $v, 2);
?>
<section class="hero wrap">
    <h1><?= View::e($t['h1']) ?></h1>
<?php if ($latest): ?>
    <p class="lede"><?= View::e(sprintf($t['lede'], $n($latest['price_22k_gram']), $n($latest['price_24k_gram']), date('j M Y, g:i A', strtotime($latest['created_at'])))) ?></p>
    <div class="price-grid">
        <?php foreach ([24, 22, 21, 18] as $k): ?>
        <a class="price-card" href="/gold-price-<?= $k ?>k-sri-lanka">
            <div class="karat"><?= View::e(sprintf($t['karat'], $k)) ?></div>
            <div class="lkr">LKR <?= $n($latest["price_{$k}k_gram"]) ?></div>
            <div class="unit"><?= View::e($t['per_gram']) ?></div>
            <div class="sub">LKR <?= $n($latest["price_{$k}k_8g"]) ?> / <?= View::e($t['per_pawn']) ?></div>
        </a>
        <?php endforeach; ?>
    </div>
    <p class="notice-line fine-print"><?= View::e($t['notice']) ?></p>
<?php else: ?>
    <div class="no-data"><p><?= View::e($t['no_data']) ?></p></div>
<?php endif; ?>
</section>

<?php if ($latest): ?>
<section class="wrap">
    <h2><?= View::e($t['table_h']) ?></h2>
    <table class="price-table stack">
        <thead><tr><th><?= View::e($t['purity']) ?></th><th><?= View::e($t['per_gram']) ?></th><th><?= View::e($t['per_pawn']) ?></th><th><?= View::e($t['per_oz']) ?></th></tr></thead>
        <tbody>
        <?php foreach ([24, 22, 21, 18] as $k): ?>
            <tr>
                <td data-label="<?= View::e($t['purity']) ?>"><a href="/gold-price-<?= $k ?>k-sri-lanka"><?= View::e(sprintf($t['karat'], $k)) ?></a></td>
                <td data-label="<?= View::e($t['per_gram']) ?>">LKR <?= $n($latest["price_{$k}k_gram"]) ?></td>
                <td data-label="<?= View::e($t['per_pawn']) ?>">LKR <?= $n($latest["price_{$k}k_8g"]) ?></td>
                <td data-label="<?= View::e($t['per_oz']) ?>">LKR <?= $n($latest["price_{$k}k_oz"]) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="fine-print"><a href="/"><?= View::e($t['full_en']) ?> &rarr;</a></p>
</section>
<?php endif; ?>

<section class="wrap faq">
    <h2><?= View::e($t['faq_h']) ?></h2>
    <?php foreach ($t['faq'] as $item): ?>
    <details>
        <summary><?= View::e($item['q']) ?></summary>
        <p><?= View::e($item['a']) ?></p>
    </details>
    <?php endforeach; ?>
</section>
