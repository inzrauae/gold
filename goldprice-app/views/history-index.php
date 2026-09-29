<?php
/** @var array $years */
/** @var array $recent */
/** @var string|null $firstDate */
?>
<section class="wrap">
    <h1>Gold Price History - Sri Lanka</h1>
    <?php if ($firstDate): ?>
    <p>Historical data collection started on <?= date('j M Y', strtotime($firstDate)) ?>.</p>
    <?php else: ?>
    <p>No history yet - check back after the first verified price is published.</p>
    <?php endif; ?>

    <?php if (!empty($years)): ?>
    <h2>Browse by Year</h2>
    <p>
    <?php foreach ($years as $y): ?>
        <a class="year-link" href="/gold-price-history/<?= $y ?>"><?= $y ?></a>
    <?php endforeach; ?>
    </p>
    <?php endif; ?>

    <h2>Last 90 Days</h2>
    <table class="price-table">
        <thead><tr><th>Date</th><th>24K / g</th><th>22K / g</th><th>21K / g</th><th>18K / g</th></tr></thead>
        <tbody>
        <?php foreach ($recent as $row): ?>
            <tr>
                <td><?= \App\Core\View::e($row['date']) ?><?= $row['is_imported'] ? ' *' : '' ?></td>
                <td>LKR <?= number_format((float) $row['price_24k_gram'], 2) ?></td>
                <td>LKR <?= number_format((float) $row['price_22k_gram'], 2) ?></td>
                <td>LKR <?= number_format((float) $row['price_21k_gram'], 2) ?></td>
                <td>LKR <?= number_format((float) $row['price_18k_gram'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="fine-print">* imported historical close, not a live verified reading.</p>
</section>
