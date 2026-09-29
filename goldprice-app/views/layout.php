<?php

use App\Core\View;
use App\Services\Seo;

$title = $title ?? Seo::siteName();
$description = $description ?? '';
$canonical = Seo::siteUrl($canonical ?? '/');
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= View::e($title) ?></title>
<meta name="description" content="<?= View::e($description) ?>">
<link rel="canonical" href="<?= View::e($canonical) ?>">
<link rel="alternate" type="application/rss+xml" title="<?= View::e(Seo::siteName()) ?> RSS" href="<?= View::e(Seo::siteUrl('/rss/gold-price.xml')) ?>">
<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 32 32%22><circle cx=%2216%22 cy=%2216%22 r=%2214%22 fill=%22%23d4af37%22/></svg>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800;900&family=Inter:wght@400;500;600;700;800&display=swap">
<link rel="stylesheet" href="/assets/css/style.css">
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
