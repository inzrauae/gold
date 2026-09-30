<?php

use App\Core\View;

/** @var string $adminEmail */
?>
<section class="wrap">
    <p class="breadcrumb"><a href="/">Home</a> &rsaquo; Contact</p>
    <h1>Contact</h1>

    <p class="lede">
        For price corrections, data-source questions, API/widget support, or privacy and legal enquiries,
        email <a href="mailto:<?= View::e($adminEmail) ?>"><?= View::e($adminEmail) ?></a>.
    </p>

    <h2>Before you write in</h2>
    <ul>
        <li>Reporting a price that looks wrong? Include the page URL and the time you viewed it - prices
            change intraday, so the exact timestamp matters. See <a href="/data-sources">how prices are
            verified</a> first; most "wrong" prices are the last verified reading held during a source
            disagreement, which is expected behaviour, not a bug.</li>
        <li>API or widget not working? See the <a href="/gold-price-api">API docs</a> or
            <a href="/gold-price-widget/how-to-use">widget guide</a> - most issues are covered there.</li>
        <li>This is not a jewellery retailer - we cannot quote a selling price, buy, or sell gold.</li>
    </ul>
</section>
