<?php
use App\Core\Csrf;
use App\Core\View;
use App\Services\EnquiryService;
/** @var array $user */
/** @var array $enquiries */
/** @var array|null $latest */
/** @var array $logs */
/** @var array $pending */
/** @var array $sources */
/** @var array $recentLogLines */
?>
<section class="wrap admin-dashboard">
    <div class="admin-bar">
        <h1>Admin Dashboard</h1>
        <form method="post" action="/admin/logout"><?= Csrf::field() ?><button type="submit" class="link-button">Log out (<?= View::e($user['email']) ?>)</button></form>
    </div>

    <?php if (!empty($pending)): ?>
    <section class="alert alert-warn">
        <h2>Held Updates Awaiting Review</h2>
        <?php foreach ($pending as $p): ?>
            <?php $payload = json_decode($p['payload_json'], true) ?: []; ?>
            <div class="pending-item">
                <p><strong><?= View::e($p['reason']) ?></strong> at <?= View::e($p['created_at']) ?></p>
                <?php if (isset($payload['gold_move_percent'])): ?>
                <p>Gold move: <?= View::e((string) $payload['gold_move_percent']) ?>% · FX move: <?= View::e((string) ($payload['fx_move_percent'] ?? '-')) ?>%</p>
                <?php endif; ?>
                <form method="post" action="/admin/accept-change/<?= (int) $p['id'] ?>" onsubmit="return confirm('Publish this price despite the abnormal move?');">
                    <?= Csrf::field() ?>
                    <label><input type="checkbox" required> I confirm this large change is genuine</label>
                    <button type="submit">Accept large change</button>
                </form>
            </div>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <section>
        <h2>Current Price</h2>
        <?php if ($latest): ?>
        <table class="price-table">
            <tr><td>As of</td><td><?= View::e($latest['created_at']) ?></td></tr>
            <tr><td>Verification</td><td><?= View::e($latest['verification_mode']) ?></td></tr>
            <tr><td>22K / g</td><td>LKR <?= number_format((float) $latest['price_22k_gram'], 2) ?></td></tr>
            <tr><td>24K / g</td><td>LKR <?= number_format((float) $latest['price_24k_gram'], 2) ?></td></tr>
            <tr><td>Spot</td><td>US$<?= number_format((float) $latest['spot_usd_per_oz'], 2) ?>/oz</td></tr>
            <tr><td>USD/LKR</td><td><?= number_format((float) $latest['usd_lkr'], 2) ?></td></tr>
        </table>
        <?php else: ?>
        <p>No verified price published yet.</p>
        <?php endif; ?>

        <form method="post" action="/admin/update-now">
            <?= Csrf::field() ?>
            <button type="submit">Update now / Retry</button>
        </form>
    </section>

    <section>
        <h2>Source Status</h2>
        <table class="price-table">
            <thead><tr><th>Slot</th><th>Status</th><th>Value</th><th>Age</th></tr></thead>
            <tbody>
            <?php foreach ($sources as $label => $reading): ?>
                <tr>
                    <td><?= View::e($label) ?></td>
                    <td><?= $reading['ok'] ? '✅ OK' : '❌ ' . View::e($reading['error']) ?></td>
                    <td><?= $reading['ok'] ? number_format((float) $reading['value'], 4) . ' ' . View::e($reading['currency']) : '-' ?></td>
                    <td><?= $reading['ok'] ? (time() - $reading['timestamp']) . 's' : '-' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section>
        <h2>Update Log</h2>
        <table class="price-table">
            <thead><tr><th>Time</th><th>Status</th><th>Reason</th></tr></thead>
            <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= View::e($log['created_at']) ?></td>
                    <td><?= View::e($log['status']) ?></td>
                    <td><?= View::e($log['reason'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section>
        <h2>Advertising Enquiries</h2>
        <?php if (empty($enquiries)): ?>
        <p class="muted">No enquiries yet. They arrive from the form on <a href="/contact">/contact</a>.</p>
        <?php else: ?>
        <div class="table-scroll">
        <table class="price-table">
            <thead><tr><th>Received</th><th>From</th><th>Interest</th><th>Budget</th><th>Message</th></tr></thead>
            <tbody>
            <?php foreach ($enquiries as $q): ?>
                <tr>
                    <td><?= View::e($q['created_at']) ?></td>
                    <td>
                        <strong><?= View::e($q['name']) ?></strong><?= $q['company'] ? ' · ' . View::e($q['company']) : '' ?><br>
                        <a href="mailto:<?= View::e($q['email']) ?>"><?= View::e($q['email']) ?></a>
                        <?= $q['phone'] ? '<br>' . View::e($q['phone']) : '' ?>
                    </td>
                    <td><?= View::e(EnquiryService::TYPES[$q['enquiry_type']] ?? $q['enquiry_type']) ?></td>
                    <td><?= View::e(EnquiryService::BUDGETS[$q['budget'] ?? ''] ?? '-') ?></td>
                    <td style="white-space: pre-line; min-width: 240px"><?= View::e($q['message']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </section>

    <section>
        <h2>Recent Log Lines</h2>
        <pre class="code-block log-lines"><?php foreach ($recentLogLines as $line): ?><?= View::e($line['time'] . ' [' . $line['level'] . '] ' . $line['message']) ?>
<?php endforeach; ?></pre>
    </section>
</section>
