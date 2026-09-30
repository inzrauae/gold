<?php

use App\Core\View;

/** @var string $lastUpdated */
?>
<section class="wrap">
    <p class="breadcrumb"><a href="/">Home</a> &rsaquo; About</p>
    <h1>About Gold Price Today Sri Lanka</h1>

    <p class="lede">
        This site publishes a verified, indicative gold price for Sri Lanka - 24K, 22K, 21K and 18K, per
        gram, per 8-gram pawn/pound and per troy ounce - calculated automatically from live international
        gold spot data and the USD/LKR exchange rate, and re-checked against independent sources before
        anything is published.
    </p>

    <h2>What makes the price trustworthy</h2>
    <ul>
        <li>No price is ever typed in by hand, estimated, or copied from a jeweller's board.</li>
        <li>Every figure is cross-checked against a second, independent source before publication; if the
            sources disagree beyond a small tolerance, nothing is published and the last verified price
            stays live with its timestamp instead.</li>
        <li>The exact calculation - spot price &times; USD/LKR &divide; 31.1034768g &times; karat/24 - is
            published in full on the <a href="/gold-price-today-sri-lanka">step-by-step breakdown</a>, not
            hidden behind a black box.</li>
        <li>The data providers currently in use are listed, by name, on the
            <a href="/data-sources">data sources &amp; methodology</a> page.</li>
        <li>The same figures are available as a free <a href="/gold-price-api">JSON API</a> and
            <a href="/rss/gold-price.xml">RSS feed</a>, so anyone can verify the site isn't showing one
            number on the page and a different one to machines.</li>
    </ul>

    <h2>What this site is not</h2>
    <p>
        It is not a jeweller, a bullion dealer, or a source of official retail prices - retail prices
        include making charges, taxes and margin that vary by seller and are not part of this calculation.
        It is not financial advice; it is a transparent, automated conversion of public market data, provided
        for reference.
    </p>

    <h2>Corrections and questions</h2>
    <p>Spotted a wrong figure, a broken page, or have a question about the methodology? See
        <a href="/contact">Contact</a>.</p>

    <p class="fine-print">Site code last updated <?= View::e($lastUpdated) ?>.</p>
</section>
