<?php

use App\Core\View;
use App\Services\Seo;

$title = $title ?? Seo::siteName();
$description = $description ?? '';
$canonical = Seo::siteUrl($canonical ?? '/');
$ogImage = $ogImage ?? Seo::ogImageUrl();
$jsonLd = $jsonLd ?? [];
$updatedAtIso = Seo::iso8601($updatedAt ?? null);
$lang = $lang ?? 'en';
$htmlLang = ['en' => 'en-LK', 'si' => 'si-LK', 'ta' => 'ta-LK'][$lang] ?? 'en-LK';
$showAlternates = in_array($canonical, [Seo::siteUrl('/'), Seo::siteUrl('/si'), Seo::siteUrl('/ta')], true);
?>
<!doctype html>
<html lang="<?= View::e($htmlLang) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#08070a">
<meta name="color-scheme" content="dark light">
<script>try{var t=localStorage.getItem("theme");if(t)document.documentElement.setAttribute("data-theme",t)}catch(e){}</script>
<title><?= View::e($title) ?></title>
<meta name="description" content="<?= View::e($description) ?>">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">
<meta name="google-site-verification" content="cMSnr--OIdPpOlJo01EA8EkQE_dJEX6ybZhjW6VvULo">
<link rel="canonical" href="<?= View::e($canonical) ?>">
<?php if ($showAlternates): ?>
<link rel="alternate" hreflang="en-LK" href="<?= View::e(Seo::siteUrl('/')) ?>">
<link rel="alternate" hreflang="si-LK" href="<?= View::e(Seo::siteUrl('/si')) ?>">
<link rel="alternate" hreflang="ta-LK" href="<?= View::e(Seo::siteUrl('/ta')) ?>">
<link rel="alternate" hreflang="x-default" href="<?= View::e(Seo::siteUrl('/')) ?>">
<?php else: ?>
<link rel="alternate" hreflang="en-LK" href="<?= View::e($canonical) ?>">
<link rel="alternate" hreflang="x-default" href="<?= View::e($canonical) ?>">
<?php endif; ?>
<meta name="geo.region" content="LK">
<meta name="geo.placename" content="Sri Lanka">
<meta name="content-language" content="<?= View::e($htmlLang) ?>">
<meta name="application-name" content="<?= View::e(Seo::siteName()) ?>">
<meta name="format-detection" content="telephone=no">
<link rel="manifest" href="/manifest.webmanifest">
<link rel="apple-touch-icon" href="/logo.png">
<link rel="alternate" type="application/rss+xml" title="<?= View::e(Seo::siteName()) ?> RSS" href="<?= View::e(Seo::siteUrl('/rss/gold-price.xml')) ?>">

<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= View::e(Seo::siteName()) ?>">
<meta property="og:title" content="<?= View::e($title) ?>">
<meta property="og:description" content="<?= View::e($description) ?>">
<meta property="og:url" content="<?= View::e($canonical) ?>">
<meta property="og:image" content="<?= View::e($ogImage) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:locale" content="<?= View::e(['en' => 'en_LK', 'si' => 'si_LK', 'ta' => 'ta_LK'][$lang] ?? 'en_LK') ?>">
<?php foreach (['en_LK', 'si_LK', 'ta_LK'] as $altLocale): if ($altLocale !== (['en' => 'en_LK', 'si' => 'si_LK', 'ta' => 'ta_LK'][$lang] ?? 'en_LK')): ?>
<meta property="og:locale:alternate" content="<?= $altLocale ?>">
<?php endif; endforeach; ?>
<?php if ($updatedAtIso): ?>
<meta property="article:modified_time" content="<?= View::e($updatedAtIso) ?>">
<?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= View::e($title) ?>">
<meta name="twitter:description" content="<?= View::e($description) ?>">
<meta name="twitter:image" content="<?= View::e($ogImage) ?>">

<link rel="icon" type="image/png" href="/logo.png">
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 32 32%22><circle cx=%2216%22 cy=%2216%22 r=%2214%22 fill=%22%23d4af37%22/></svg>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800;900&family=Inter:wght@400;500;600;700;800&display=swap">
<?php if ($lang === 'si'): ?>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+Sinhala:wght@400;600;700&display=swap">
<?php elseif ($lang === 'ta'): ?>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+Tamil:wght@400;600;700&display=swap">
<?php endif; ?>
<link rel="preload" href="/assets/css/style.css" as="style">
<link rel="stylesheet" href="/assets/css/style.css?v=<?= (int) @filemtime(dirname(APP_DIR) . '/public_html/assets/css/style.css') ?>">
<?php if (!empty($jsonLd)): ?>
<?= Seo::jsonLdScript($jsonLd) ?>
<?php endif; ?>
</head>
<body>
<header class="site-header">
    <div class="wrap">
        <a class="brand" href="/"><span class="brand-mark" aria-hidden="true"></span><?= View::e(Seo::siteName()) ?></a>
        <nav class="main-nav">
            <a href="/gold-price-today-sri-lanka">Today</a>
            <a href="/gold-price-history">History</a>
            <a href="/gold-price-api">API</a>
            <a href="/gold-price-widget">Widget</a>
            <a href="/data-sources">Sources</a>
        </nav>
        <a class="nav-cta" href="/contact">Advertise</a>
        <nav class="lang-switch" aria-label="Language">
            <a href="/" hreflang="en-LK" lang="en"<?= $lang === 'en' ? ' aria-current="true"' : '' ?>>EN</a>
            <a href="/si" hreflang="si-LK" lang="si"<?= $lang === 'si' ? ' aria-current="true"' : '' ?>>සිං</a>
            <a href="/ta" hreflang="ta-LK" lang="ta"<?= $lang === 'ta' ? ' aria-current="true"' : '' ?>>த</a>
        </nav>
        <button type="button" class="theme-toggle" id="theme-toggle" aria-label="Toggle light/dark theme"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg></button>
    </div>
</header>
<main>
<?= $content ?? '' ?>
</main>
<footer class="site-footer">
    <div class="wrap">
        <div class="footer-grid">
            <div class="footer-about">
                <a class="brand" href="/"><span class="brand-mark" aria-hidden="true"></span><?= View::e(Seo::siteName()) ?></a>
                <p>Verified, indicative gold prices for Sri Lanka - 24K, 22K, 21K and 18K, updated automatically from independent market sources.</p>
            </div>
            <div class="footer-col">
                <h3>Prices</h3>
                <a href="/gold-price-today-sri-lanka">Today's breakdown</a>
                <a href="/gold-price-history">Price history</a>
                <a href="/#calculator">Gold calculator</a>
                <a href="/data-sources">Data sources</a>
            </div>
            <div class="footer-col">
                <h3>Developers</h3>
                <a href="/gold-price-api">Free API</a>
                <a href="/gold-price-widget">Widget</a>
                <a href="/rss/gold-price.xml">RSS feed</a>
                <a href="/sitemap.xml">Sitemap</a>
            </div>
            <div class="footer-col">
                <h3>Company</h3>
                <a href="/about">About</a>
                <a href="/contact">Advertise with us</a>
                <a href="/contact#enquiry">Contact</a>
                <a href="/privacy">Privacy</a>
                <a href="/terms">Terms</a>
            </div>
        </div>
        <p class="footer-note"><strong>Not official retail prices.</strong> Verified, indicative market-rate conversions only.
        See <a href="/data-sources">data sources &amp; methodology</a>.</p>
        <p class="fine-print">
            &copy; <?= date('Y') ?> <?= View::e(Seo::siteName()) ?> ·
            <a href="/si" hreflang="si-LK" lang="si">සිංහල</a> ·
            <a href="/ta" hreflang="ta-LK" lang="ta">தமிழ்</a> ·
            <a href="/admin/login">Admin</a>
        </p>
    </div>
</footer>
<nav class="tabbar" aria-label="Primary">
    <a href="/"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 11l9-8 9 8v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/></svg><span>Home</span></a>
    <a href="/gold-price-history"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18M7 15l4-4 3 3 6-7"/></svg><span>History</span></a>
    <a href="/#calculator"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M8 7h8M8 12h2m4 0h2M8 16h2m4 0h2"/></svg><span>Calc</span></a>
    <a href="/gold-price-api"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 7l-5 5 5 5M16 7l5 5-5 5"/></svg><span>API</span></a>
    <a href="/data-sources"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg><span>Sources</span></a>
</nav>
<script src="/assets/js/app.js"></script>
</body>
</html>
