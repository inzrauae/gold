<?php

use App\Core\View;

/** @var string $adminEmail */
/** @var string $lastUpdated */
?>
<section class="wrap">
    <p class="breadcrumb"><a href="/">Home</a> &rsaquo; Privacy Policy</p>
    <h1>Privacy Policy</h1>
    <p class="fine-print">Last updated <?= View::e($lastUpdated) ?>.</p>

    <h2>What we collect</h2>
    <p>
        Browsing this site does not require an account and does not collect personal data from you. We do
        not run analytics, advertising or third-party tracking scripts. Your web server / hosting provider
        may keep standard technical logs (IP address, user agent, timestamp, requested URL) as most web
        servers do by default, purely for security and diagnostics - these are not linked to any profile.
    </p>

    <h2>Third-party resources</h2>
    <p>
        Pages load webfonts from Google Fonts (fonts.googleapis.com / fonts.gstatic.com). Loading a font
        makes a request to Google's servers, which - like any web request - includes your IP address.
        See <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer">Google's
        privacy policy</a> for how they handle that. No other third-party scripts are loaded.
    </p>

    <h2>The free API and widget</h2>
    <p>
        Calls to the <a href="/gold-price-api">JSON API</a> are rate-limited by IP address to keep the
        service available to everyone; the IP is used transiently for that rate limit and is not stored
        against a profile or shared.
    </p>

    <h2>Admin area</h2>
    <p>
        The <a href="/admin/login">admin login</a> is restricted to the site operator and uses a session
        cookie strictly to keep that person signed in. It is not set for ordinary visitors browsing the
        public pages.
    </p>

    <h2>Changes to this policy</h2>
    <p>If this policy changes - for example, if analytics or advertising is added in future - this page
        will be updated and the "last updated" date above will change accordingly.</p>

    <h2>Questions</h2>
    <p>Email <a href="mailto:<?= View::e($adminEmail) ?>"><?= View::e($adminEmail) ?></a> - see also
        <a href="/contact">Contact</a>.</p>
</section>
