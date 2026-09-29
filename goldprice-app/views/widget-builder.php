<?php

use App\Core\View;
use App\Services\Seo;

$scriptUrl = Seo::siteUrl('/widgets/gold-price.js');
?>
<section class="wrap">
    <h1>Free Gold Price Widget</h1>
    <p>Paste this one line before the closing <code>&lt;/body&gt;</code> tag of your site:</p>
    <pre class="code-block">&lt;script src="<?= View::e($scriptUrl) ?>"&gt;&lt;/script&gt;</pre>
    <p>It renders a small, self-updating card with the live 24K and 22K gold price and a link back here.
    If a site's Content-Security-Policy blocks external scripts, a plain text link is shown instead.</p>

    <h2>Preview</h2>
    <script src="<?= View::e($scriptUrl) ?>"></script>

    <p><a href="/gold-price-widget/how-to-use">Full usage guide &amp; terms &rarr;</a></p>
</section>
