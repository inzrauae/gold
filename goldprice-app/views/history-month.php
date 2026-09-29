<?php
use App\Core\View;
/** @var int $year */
/** @var int $month */
/** @var string $monthName */
/** @var array $rows */
?>
<section class="wrap">
    <h1>Gold Price History - <?= View::e($monthName) ?> <?= $year ?></h1>
    <p><a href="/gold-price-history/<?= $year ?>">&larr; <?= $year ?></a> · <a href="/gold-price-history">All years</a></p>

    <?php if (empty($rows)): ?>
    <p>No verified prices recorded for this month.</p>
    <?php else: ?>
    <table class="price-table">
        <thead><tr><th>Date</th><th>24K / g</th><th>22K / g</th><th>21K / g</th><th>18K / g</th><th>Spot US$/oz</th><th>USD/LKR</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= View::e($row['date']) ?><?= $row['is_imported'] ? ' *' : '' ?></td>
                <td>LKR <?= number_format((float) $row['price_24k_gram'], 2) ?></td>
                <td>LKR <?= number_format((float) $row['price_22k_gram'], 2) ?></td>
                <td>LKR <?= number_format((float) $row['price_21k_gram'], 2) ?></td>
                <td>LKR <?= number_format((float) $row['price_18k_gram'], 2) ?></td>
                <td><?= number_format((float) $row['spot_usd_per_oz'], 2) ?></td>
                <td><?= number_format((float) $row['usd_lkr'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="fine-print">* imported historical close, drawn dashed on the chart, never overwrites a live day.</p>
    <?php endif; ?>
</section>
