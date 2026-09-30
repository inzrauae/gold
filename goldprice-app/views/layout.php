<?php

use App\Core\View;
use App\Services\Seo;

$title = $title ?? Seo::siteName();
$description = $description ?? '';
$canonical = Seo::siteUrl($canonical ?? '/');
$ogImage = $ogImage ?? Seo::ogImageUrl();
$jsonLd = $jsonLd ?? [];
$updatedAtIso = Seo::iso8601($updatedAt ?? null);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= View::e($title) ?></title>
<meta name="description" content="<?= View::e($description) ?>">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">
<link rel="canonical" href="<?= View::e($canonical) ?>">
<link rel="alternate" hreflang="en-lk" href="<?= View::e($canonical) ?>">
<link rel="alternate" hreflang="x-default" href="<?= View::e($canonical) ?>">
<link rel="alternate" type="application/rss+xml" title="<?= View::e(Seo::siteName()) ?> RSS" href="<?= View::e(Seo::siteUrl('/rss/gold-price.xml')) ?>">

<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= View::e(Seo::siteName()) ?>">
<meta property="og:title" content="<?= View::e($title) ?>">
<meta property="og:description" content="<?= View::e($description) ?>">
<meta property="og:url" content="<?= View::e($canonical) ?>">
<meta property="og:image" content="<?= View::e($ogImage) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:locale" content="en_LK">
<?php if ($updatedAtIso): ?>
<meta property="article:modified_time" content="<?= View::e($updatedAtIso) ?>">
<?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= View::e($title) ?>">
<meta name="twitter:description" content="<?= View::e($description) ?>">
<meta name="twitter:image" content="<?= View::e($ogImage) ?>">

<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 32 32%22><circle cx=%2216%22 cy=%2216%22 r=%2214%22 fill=%22%23d4af37%22/></svg>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800;900&family=Inter:wght@400;500;600;700;800&display=swap">
<link rel="stylesheet" href="/assets/css/style.css">
<?php if (!empty($jsonLd)): ?>
<?= Seo::jsonLdScript($jsonLd) ?>
<?php endif; ?>
</head>
<body>
<header class="site-header">
    <div class="wrap">
        <a class="brand" href="/">🥇 <?= View::e(Seo::siteName()) ?></a>
        <nav class="main-nav">
            <a href="/gold-price-today-sri-lanka">Today</a>
            <a href="/gold-price-history">History</a>
            <a href="/gold-price-api">API</a>
            <a href="/gold-price-widget">Widget</a>
            <a href="/data-sources">Sources</a>
        </nav>
    </div>
</header>
<main>
<?= $content ?? '' ?>
</main>
<footer class="site-footer">
    <div class="wrap">
        <p><strong>Not official retail prices.</strong> Verified, indicative market-rate conversions only.
        See <a href="/data-sources">data sources &amp; methodology</a>.</p>
        <p class="fine-print">
            &copy; <?= date('Y') ?> <?= View::e(Seo::siteName()) ?> ·
            <a href="/about">About</a> ·
            <a href="/contact">Contact</a> ·
            <a href="/privacy">Privacy</a> ·
            <a href="/terms">Terms</a> ·
            <a href="/gold-price-api">API</a> ·
            <a href="/rss/gold-price.xml">RSS</a> ·
            <a href="/sitemap.xml">Sitemap</a> ·
            <a href="/admin/login">Admin</a>
        </p>
    </div>
</footer>
<script src="/assets/js/app.js"></script>
</body>
</html>
