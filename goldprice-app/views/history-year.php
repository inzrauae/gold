<?php
use App\Core\View;
/** @var int $year */
/** @var array $rows */

$byMonth = [];
foreach ($rows as $row) {
    $m = (int) date('n', strtotime($row['date']));
    $byMonth[$m][] = $row;
}
?>
<section class="wrap">
    <h1>Gold Price History <?= $year ?></h1>
    <p><a href="/gold-price-history">&larr; All years</a></p>

    <table class="price-table">
        <thead><tr><th>Month</th><th>Days recorded</th><th>Avg 22K / g</th></tr></thead>
        <tbody>
        <?php for ($m = 1; $m <= 12; $m++): ?>
            <?php $monthRows = $byMonth[$m] ?? []; ?>
            <?php if (empty($monthRows)) continue; ?>
            <?php $avg = array_sum(array_column($monthRows, 'price_22k_gram')) / count($monthRows); ?>
            <tr>
                <td><a href="/gold-price-history/<?= $year ?>/<?= sprintf('%02d', $m) ?>"><?= date('F', mktime(0, 0, 0, $m, 1)) ?></a></td>
                <td><?= count($monthRows) ?></td>
                <td>LKR <?= number_format($avg, 2) ?></td>
            </tr>
        <?php endfor; ?>
        </tbody>
    </table>
</section>
