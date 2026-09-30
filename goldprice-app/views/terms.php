<?php

use App\Core\View;

/** @var string $lastUpdated */
?>
<section class="wrap">
    <p class="breadcrumb"><a href="/">Home</a> &rsaquo; Terms of Use</p>
    <h1>Terms of Use</h1>
    <p class="fine-print">Last updated <?= View::e($lastUpdated) ?>.</p>

    <h2>What this site is</h2>
    <p>
        This site publishes a verified, indicative gold price for Sri Lanka, converted from live
        international gold spot data and the USD/LKR exchange rate. It is provided for general reference
        only and is <strong>not</strong> financial, investment or trading advice, and does not represent
        an official retail, wholesale or jeweller's price. See <a href="/data-sources">data sources &amp;
        methodology</a> for exactly how figures are produced and verified.
    </p>

    <h2>Using the site, API, RSS feed and widget</h2>
    <ul>
        <li>All public pages, the <a href="/gold-price-api">JSON API</a>, the
            <a href="/rss/gold-price.xml">RSS feed</a> and the
            <a href="/gold-price-widget">embeddable widget</a> are free to use, including commercially.</li>
        <li>If you need the data programmatically, use the API or RSS feed rather than scraping the HTML
            pages - it is more reliable for you and lighter on the site.</li>
        <li>The API is rate-limited; please keep request volume reasonable. We may throttle or block
            traffic that disrupts the service for others.</li>
        <li>The widget has its own terms - see the <a href="/gold-price-widget/how-to-use">widget
            guide</a> (in short: keep the attribution link, don't imply an official endorsement).</li>
        <li>Do not present this data as an official government, central bank or jeweller price, and do not
            use it to imply an endorsement or partnership that does not exist.</li>
    </ul>

    <h2>No warranty</h2>
    <p>
        Prices are produced by an automated process and are believed accurate at the time of publication,
        but are provided "as is" without warranty of any kind. Source outages, provider errors or
        verification holds can delay an update - see the "as of" timestamp on every price for exactly how
        current a figure is. We are not liable for decisions made based on this data.
    </p>

    <h2>Changes</h2>
    <p>These terms may be updated from time to time; the "last updated" date above will change when they
        are. Continued use of the site after a change means you accept the updated terms.</p>

    <h2>Contact</h2>
    <p>Questions about these terms? See <a href="/contact">Contact</a>.</p>
</section>
