<?php

use App\Core\View;

/** @var array|null $latest */
/** @var array $chart */
/** @var bool $marketOpen */
/** @var array $news */
/** @var array $faq */
/** @var array|null $trend */
/** @var string $date */
?>
<?php
$prev22 = null;
if ($latest) {
    $latestDay = date('Y-m-d', strtotime($latest['created_at']));
    foreach (array_reverse($chart) as $row) {
        if ($row['date'] < $latestDay) { $prev22 = (float) $row['price_22k_gram']; break; }
    }
}
?>
<section class="hero wrap">
<?php if ($latest): ?>
    <h1>Gold Price Today in Sri Lanka - <?= View::e($date) ?></h1>
    <p class="lede">
        Today's verified gold price in Sri Lanka (Colombo and nationwide) is
        <strong>LKR <?= number_format((float) $latest['price_22k_gram'], 2) ?> per gram for 22K</strong>
        and <strong>LKR <?= number_format((float) $latest['price_24k_gram'], 2) ?> per gram for 24K</strong>,
        live as of <?= date('j M Y, g:i A', strtotime($latest['created_at'])) ?>.
        <?php if ($trend): ?><?= View::e($trend['sentence']) ?><?php endif; ?>
    </p>
    <p class="as-of">
        As of <?= date('j M Y, g:i A', strtotime($latest['created_at'])) ?> ·
        <span class="badge <?= $latest['verification_mode'] === 'dual_source' ? 'badge-ok' : 'badge-warn' ?>">
            <?= $latest['verification_mode'] === 'dual_source' ? 'Verified: two independent sources' : 'Verified: single source' ?>
        </span>
        · Markets <?= $marketOpen ? 'open' : 'closed' ?>
        · Updated daily
    </p>
    <div class="spotlight">
        <div>
            <div class="spot-label">22K gold &middot; per gram</div>
            <div class="spot-price"><small>LKR</small> <?= number_format((float) $latest['price_22k_gram'], 2) ?></div>
        </div>
        <?php if ($prev22 !== null && $prev22 > 0):
            $delta = (float) $latest['price_22k_gram'] - $prev22;
            $pct = $delta / $prev22 * 100;
            $dir = abs($delta) < 0.005 ? 'flat' : ($delta > 0 ? 'up' : 'down'); ?>
        <div class="chg chg-<?= $dir ?>">
            <span class="chg-arrow" aria-hidden="true"><?= $dir === 'up' ? '&#9650;' : ($dir === 'down' ? '&#9660;' : '&#9644;') ?></span>
            <?= $dir === 'flat' ? 'Unchanged' : number_format(abs($delta), 2) . ' (' . number_format(abs($pct), 2) . '%)' ?>
            <span class="chg-note">vs previous day</span>
        </div>
        <?php endif; ?>
    </div>
    <div class="price-grid">
        <?php foreach ([24, 22, 21, 18] as $k): ?>
        <a class="price-card" href="/gold-price-<?= $k ?>k-sri-lanka">
            <div class="karat"><?= $k ?>K</div>
            <div class="lkr">LKR <?= number_format((float) $latest["price_{$k}k_gram"], 2) ?></div>
            <div class="unit">per gram</div>
            <div class="sub">LKR <?= number_format((float) $latest["price_{$k}k_8g"], 2) ?> / 8g pawn</div>
        </a>
        <?php endforeach; ?>
    </div>
    <p class="spot-line">
        Spot: US$<?= number_format((float) $latest['spot_usd_per_oz'], 2) ?> / troy oz ·
        USD/LKR <?= number_format((float) $latest['usd_lkr'], 2) ?>
    </p>
<?php else: ?>
    <div class="no-data">
        <h1>Gold Price Today - Sri Lanka</h1>
        <p>Historical data collection started on <?= date('j M Y') ?>. The first verified price has not
        been published yet.</p>
        <p>Run <code>php cli/update-prices.php</code> (with valid API keys / network access) or wait for
        the scheduler cron job to publish the first verified reading.</p>
    </div>
<?php endif; ?>
</section>

<section class="wrap" id="chart">
    <h2>Gold Price in Sri Lanka - Chart</h2>
    <p>Interactive gold price chart for Sri Lanka showing the last 90 days across all four purities.</p>
    <div class="chart-tools">
        <div class="seg" role="group" aria-label="Chart range">
            <button type="button" class="seg-btn" data-range="7">7D</button>
            <button type="button" class="seg-btn" data-range="30">30D</button>
            <button type="button" class="seg-btn is-active" data-range="90">90D</button>
        </div>
    </div>
    <canvas id="price-chart" width="900" height="320" aria-label="Gold price in Sri Lanka - chart, last 90 days" role="img"></canvas>
    <script id="chart-data" type="application/json"><?= json_encode(array_map(fn ($r) => [
        'date' => $r['date'],
        '24k' => (float) $r['price_24k_gram'],
        '22k' => (float) $r['price_22k_gram'],
        '21k' => (float) $r['price_21k_gram'],
        '18k' => (float) $r['price_18k_gram'],
    ], $chart)) ?></script>
</section>

<section class="wrap calculator-section" id="calc">
    <h2>Gold Price Calculator</h2>
    <div class="calculator" id="calculator"
         data-price-24k="<?= $latest ? (float) $latest['price_24k_gram'] : 0 ?>"
         data-price-22k="<?= $latest ? (float) $latest['price_22k_gram'] : 0 ?>"
         data-price-21k="<?= $latest ? (float) $latest['price_21k_gram'] : 0 ?>"
         data-price-18k="<?= $latest ? (float) $latest['price_18k_gram'] : 0 ?>">
        <label>Weight
            <input type="number" id="calc-weight" value="1" min="0" step="0.01">
            <select id="calc-unit">
                <option value="gram">gram(s)</option>
                <option value="8g">pawn (8g)</option>
                <option value="sovereign">sovereign (8g)</option>
                <option value="ounce">troy ounce</option>
            </select>
        </label>
        <label>Purity
            <select id="calc-karat">
                <option value="24">24K</option>
                <option value="22" selected>22K</option>
                <option value="21">21K</option>
                <option value="18">18K</option>
            </select>
        </label>
        <div class="calc-result">LKR <span id="calc-output">0.00</span></div>
    </div>
</section>

<section class="wrap">
    <h2>Gold Price Table - Sri Lanka Today</h2>
    <table class="price-table stack">
        <thead><tr><th>Purity</th><th>Per gram</th><th>Per 8g (pawn/pound)</th><th>Per troy oz</th></tr></thead>
        <tbody>
        <?php foreach ([24, 22, 21, 18] as $k): ?>
            <tr>
                <td data-label="Purity"><a href="/gold-price-<?= $k ?>k-sri-lanka"><?= $k ?>K (<?= $k ?> carat)</a></td>
                <td data-label="Per gram">LKR <?= $latest ? number_format((float) $latest["price_{$k}k_gram"], 2) : '-' ?></td>
                <td data-label="Per 8g">LKR <?= $latest ? number_format((float) $latest["price_{$k}k_8g"], 2) : '-' ?></td>
                <td data-label="Per troy oz">LKR <?= $latest ? number_format((float) $latest["price_{$k}k_oz"], 2) : '-' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="fine-print">
        Full breakdown by unit: <a href="/gold-price-per-gram-sri-lanka">gold price per gram</a> ·
        <a href="/gold-price-per-8-grams-sri-lanka">gold pawn / pound price (8g)</a> ·
        <a href="/gold-price-history">daily price history &amp; chart</a>.
    </p>
</section>

<section class="wrap">
    <h2>How This Price Is Produced</h2>
    <p><a href="/gold-price-today-sri-lanka">See the full step-by-step breakdown &rarr;</a></p>
</section>

<?php if (!empty($news)): ?>
<section class="wrap">
    <h2>Gold Market News</h2>
    <ul class="news-list">
        <?php foreach ($news as $item): ?>
        <li><a href="<?= View::e($item['url']) ?>" target="_blank" rel="noopener noreferrer"><?= View::e($item['title']) ?></a></li>
        <?php endforeach; ?>
    </ul>
</section>
<?php endif; ?>

<section class="wrap">
    <h2>Embed This Price</h2>
    <p>Add a live, self-updating gold price widget to your own site. <a href="/gold-price-widget">Get the embed code &rarr;</a></p>
</section>

<section class="wrap faq">
    <h2>Frequently Asked Questions</h2>
    <?php foreach ($faq as $item): ?>
    <details>
        <summary><?= View::e($item['q']) ?></summary>
        <p><?= View::e($item['a']) ?></p>
    </details>
    <?php endforeach; ?>
</section>
