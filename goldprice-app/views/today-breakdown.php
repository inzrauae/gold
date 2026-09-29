<?php

use App\Core\View;

/** @var array|null $latest */
/** @var array $steps */
/** @var array $logs */
?>
<section class="wrap">
    <h1>Gold Price Today in Sri Lanka - Step by Step</h1>

    <?php if ($latest): ?>
    <p class="as-of">As of <?= date('j M Y, g:i A', strtotime($latest['created_at'])) ?>
        (<?= View::e($latest['verification_mode']) ?>)</p>

    <h2>Live Inputs</h2>
    <table class="price-table">
        <tr><td>Gold spot</td><td>US$<?= number_format((float) $latest['spot_usd_per_oz'], 2) ?> / troy oz
            (<?= View::e($latest['gold_source_primary']) ?><?= $latest['gold_source_secondary'] ? ' + ' . View::e($latest['gold_source_secondary']) : '' ?>)</td></tr>
        <tr><td>USD/LKR</td><td><?= number_format((float) $latest['usd_lkr'], 2) ?>
            (<?= View::e($latest['fx_source_primary']) ?><?= $latest['fx_source_secondary'] ? ' + ' . View::e($latest['fx_source_secondary']) : '' ?>)</td></tr>
        <tr><td>Troy ounce</td><td>31.1034768 grams (fixed)</td></tr>
    </table>

    <h2>Result</h2>
    <table class="price-table">
        <thead><tr><th>Purity</th><th>Per gram</th><th>Per 8g (pawn)</th><th>Per troy oz</th></tr></thead>
        <tbody>
        <?php foreach ([24, 22, 21, 18] as $k): ?>
            <tr>
                <td><?= $k ?>K</td>
                <td>LKR <?= number_format((float) $latest["price_{$k}k_gram"], 2) ?></td>
                <td>LKR <?= number_format((float) $latest["price_{$k}k_8g"], 2) ?></td>
                <td>LKR <?= number_format((float) $latest["price_{$k}k_oz"], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>No verified price has been published yet.</p>
    <?php endif; ?>

    <h2>Method</h2>
    <ol class="method-steps">
        <?php foreach ($steps as $step): ?>
        <li><?= View::e($step) ?></li>
        <?php endforeach; ?>
    </ol>

    <h2>Latest Updates</h2>
    <table class="price-table">
        <thead><tr><th>Time</th><th>Status</th><th>Reason</th></tr></thead>
        <tbody>
        <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= date('j M g:i A', strtotime($log['created_at'])) ?></td>
                <td><?= View::e($log['status']) ?></td>
                <td><?= View::e($log['reason'] ?? '-') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
