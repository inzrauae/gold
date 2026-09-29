<?php
use App\Services\Seo;
$e = fn ($s) => \App\Core\View::e($s);
?>
<section class="wrap">
    <h1>Free Gold Price JSON API</h1>
    <p>No API key required. Rate limit: 60 requests/minute per IP.</p>

    <h2>Current price</h2>
    <pre class="code-block">GET <?= $e(Seo::siteUrl('/api/v1/gold-price')) ?></pre>
    <pre class="code-block">{
  "status": "ok",
  "as_of": "2026-09-27T10:15:00+05:30",
  "verification_mode": "dual_source",
  "currency": "LKR",
  "spot_usd_per_oz": 2650.12,
  "usd_lkr": 302.5,
  "prices": {
    "24k": { "per_gram": 25781.40, "per_8g": 206251.20, "per_troy_ounce": 801747.20 },
    "22k": { "per_gram": 23632.28, "per_8g": 189058.24, "per_troy_ounce": 735268.60 },
    "21k": { "per_gram": 22558.73, "per_8g": 180469.80, "per_troy_ounce": 701528.30 },
    "18k": { "per_gram": 19336.05, "per_8g": 154688.40, "per_troy_ounce": 601310.40 }
  },
  "sources": {
    "gold_primary": "gold_api_com",
    "gold_secondary": "metalpriceapi",
    "fx_primary": "open_exchange_rates",
    "fx_secondary": "exchangerate_api_open"
  }
}</pre>

    <h2>History</h2>
    <pre class="code-block">GET <?= $e(Seo::siteUrl('/api/v1/gold-price/history?range=30d')) ?></pre>
    <p><code>range</code> accepts any <code>{n}d</code> value, e.g. <code>7d</code>, <code>90d</code>, <code>365d</code>. Default is <code>30d</code>.</p>

    <h2>Other feeds</h2>
    <ul>
        <li>RSS: <a href="/rss/gold-price.xml">/rss/gold-price.xml</a> (also <a href="/feed.xml">/feed.xml</a>)</li>
        <li>Widget: <a href="/gold-price-widget">/gold-price-widget</a></li>
        <li>Sitemap: <a href="/sitemap.xml">/sitemap.xml</a></li>
    </ul>

    <p>See <a href="/data-sources">data sources &amp; methodology</a> for how these numbers are produced and verified.</p>
</section>
