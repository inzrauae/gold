<?php
use App\Services\Seo;
$scriptUrl = \App\Core\View::e(Seo::siteUrl('/widgets/gold-price.js'));
?>
<section class="wrap">
    <h1>Gold Price Widget - How To Use &amp; Terms</h1>

    <h2>Install</h2>
    <pre class="code-block">&lt;script src="<?= $scriptUrl ?>"&gt;&lt;/script&gt;</pre>

    <h2>What it shows</h2>
    <p>The live 22K and 24K gold price per gram in LKR, refreshed on every page load, with a link back to
    this site. It is intentionally small and dependency-free (no jQuery, no build step).</p>

    <h2>Terms of use</h2>
    <ul>
        <li>Free to embed on any website, personal or commercial.</li>
        <li>Do not modify the script to hide or remove the attribution link.</li>
        <li>Do not use it to imply an official or endorsed relationship with this site.</li>
        <li>We may rate-limit or discontinue the widget endpoint if it is abused.</li>
        <li>Prices shown are indicative only - see <a href="/data-sources">data sources &amp; methodology</a>.</li>
    </ul>

    <h2>Fallback behaviour</h2>
    <p>If the widget script cannot load (blocked by a Content-Security-Policy, ad blocker, or the API is
    unreachable) it shows a plain link to this site instead of a broken box.</p>

    <p><a href="/gold-price-widget">&larr; Back to the widget builder</a></p>
</section>
