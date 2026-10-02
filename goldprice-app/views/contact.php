<?php

use App\Core\View;

/** @var array{company: string, email: string, phone: string, tel: string, whatsapp: string, location: string, hours: string, social: array<string, string>} $contact */
/** @var string $formToken */
/** @var array<string, string> $old */
/** @var array<string, string> $errors */
/** @var bool $sent */
/** @var array<string, string> $types */
/** @var array<string, string> $budgets */

$old = $old ?? [];
$errors = $errors ?? [];
$val = static fn (string $key): string => View::e($old[$key] ?? '');
$err = static fn (string $key): string => isset($errors[$key])
    ? '<span class="field-error" id="err-' . $key . '">' . View::e($errors[$key]) . '</span>'
    : '';
$invalid = static fn (string $key): string => isset($errors[$key]) ? ' aria-invalid="true" aria-describedby="err-' . $key . '"' : '';
$waLink = $contact['whatsapp'] !== ''
    ? 'https://wa.me/' . $contact['whatsapp'] . '?text=' . rawurlencode('Hi, I would like to advertise on ' . \App\Services\Seo::siteName() . '.')
    : '';
$telLink = $contact['tel'];
$selectedType = $old['enquiry_type'] ?? '';
?>
<section class="ad-hero">
    <div class="wrap ad-hero-grid">
        <div class="ad-hero-copy">
            <p class="breadcrumb"><a href="/">Home</a> &rsaquo; Advertise &amp; Contact</p>
            <span class="eyebrow"><span class="eyebrow-dot" aria-hidden="true"></span>Advertise with us</span>
            <h1>Put your brand in front of <span class="gold-text">Sri Lanka's gold buyers</span></h1>
            <p class="lede">
                Jewellers, pawn centres, banks and bullion dealers reach people at the exact moment they check
                today's gold rate. Tell us what you would like to promote and we will come back with placement
                options and pricing - usually within one business day.
            </p>
            <div class="cta-row">
                <a class="btn btn-gold" href="#enquiry">
                    Send an enquiry
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
                <?php if ($waLink): ?>
                <a class="btn btn-ghost" href="<?= View::e($waLink) ?>" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 3.5A11 11 0 0 0 3.4 17.3L2 22l4.8-1.3A11 11 0 0 0 20.5 3.5zM12 20a8 8 0 0 1-4.1-1.1l-.3-.2-2.8.8.8-2.8-.2-.3A8 8 0 1 1 12 20zm4.4-6c-.2-.1-1.4-.7-1.6-.8-.2-.1-.4-.1-.5.1l-.7.9c-.1.2-.3.2-.5.1a6.6 6.6 0 0 1-3.3-2.9c-.2-.4.2-.4.7-1.2.1-.2 0-.3 0-.4l-.7-1.7c-.2-.5-.4-.4-.5-.4h-.5a.9.9 0 0 0-.7.3 2.8 2.8 0 0 0-.9 2.1 4.9 4.9 0 0 0 1 2.6 11.2 11.2 0 0 0 4.3 3.8c1.6.7 2.2.7 3 .6a2.6 2.6 0 0 0 1.7-1.2 2.1 2.1 0 0 0 .1-1.2c0-.1-.2-.2-.4-.3z" fill="currentColor" stroke="none"/></svg>
                    Chat on WhatsApp
                </a>
                <?php elseif ($telLink): ?>
                <a class="btn btn-ghost" href="<?= View::e($telLink) ?>">Call <?= View::e($contact['phone']) ?></a>
                <?php endif; ?>
            </div>
        </div>

        <aside class="ad-hero-panel" aria-label="Why advertise here">
            <div class="panel-glow" aria-hidden="true"></div>
            <h2 class="panel-title">Why brands choose us</h2>
            <ul class="stat-list">
                <li><strong>15&nbsp;min</strong><span>Verified price updates through market hours - a reason to return daily</span></li>
                <li><strong>3</strong><span>Languages: English, සිංහල and தமிழ்</span></li>
                <li><strong>4</strong><span>Karats covered - 24K, 22K, 21K and 18K, per gram, pawn and ounce</span></li>
                <li><strong>Free</strong><span>API, RSS and embeddable widget that carry the brand onto other sites</span></li>
            </ul>
        </aside>
    </div>
</section>

<section class="wrap">
    <div class="contact-cards">
        <?php if ($contact['location'] !== ''): ?>
        <div class="contact-card">
            <span class="icon-badge"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s7-6.1 7-12a7 7 0 0 0-14 0c0 5.9 7 12 7 12z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
            <span class="card-label">Visit us</span>
            <span class="card-value"><?= implode('<br>', array_map([View::class, 'e'], array_map('trim', explode(',', $contact['location'])))) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($telLink): ?>
        <a class="contact-card" href="<?= View::e($telLink) ?>">
            <span class="icon-badge"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg></span>
            <span class="card-label">Call us</span>
            <span class="card-value"><?= View::e($contact['phone']) ?></span>
            <?php if ($contact['hours'] !== ''): ?>
            <span class="card-note"><?= View::e($contact['hours']) ?></span>
            <?php endif; ?>
        </a>
        <?php endif; ?>
        <a class="contact-card" href="mailto:<?= View::e($contact['email']) ?>">
            <span class="icon-badge"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg></span>
            <span class="card-label">Email us</span>
            <span class="card-value"><?= str_replace('@', '<wbr>@', View::e($contact['email'])) ?></span>
        </a>
        <?php if ($waLink): ?>
        <a class="contact-card" href="<?= View::e($waLink) ?>" target="_blank" rel="noopener">
            <span class="icon-badge"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 11.5a8.4 8.4 0 0 1-12.4 7.4L3 21l2.1-5.4A8.5 8.5 0 1 1 21 11.5z"/></svg></span>
            <span class="card-label">WhatsApp</span>
            <span class="card-value"><?= View::e(preg_match('/^94(\d{2})(\d{3})(\d{4})$/', $contact['whatsapp'], $m) ? "+94 {$m[1]} {$m[2]} {$m[3]}" : '+' . $contact['whatsapp']) ?></span>
        </a>
        <?php endif; ?>
    </div>

    <?php if ($contact['social'] || $contact['company'] !== ''): ?>
    <div class="contact-meta">
        <?php if ($contact['company'] !== ''): ?>
        <span class="meta-item">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 9h1M14 9h1M9 13h1M14 13h1M9 17h1M14 17h1"/></svg>
            Operated by <?= View::e($contact['company']) ?>
        </span>
        <?php endif; ?>
        <?php if ($contact['social']): ?>
        <span class="social-links">
            <?php foreach ($contact['social'] as $network => $url): ?>
            <?= View::render('partials/social-icon', ['network' => $network, 'url' => $url]) ?>
            <?php endforeach; ?>
        </span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="section-head">
        <span class="eyebrow">Placements</span>
        <h2>Advertising options</h2>
        <p class="section-sub">Every placement is clearly labelled as advertising and sits beside - never inside - the verified price data.</p>
    </div>

    <div class="package-grid">
        <article class="package-card">
            <span class="icon-badge"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18M8 14h8"/></svg></span>
            <h3>Display banners</h3>
            <p>Leaderboard and in-content banners on the home page, today's breakdown and history pages.</p>
            <ul class="check-list">
                <li>Desktop and mobile sizes</li>
                <li>Monthly or campaign booking</li>
                <li>Click and impression report</li>
            </ul>
            <a class="package-link" href="#enquiry" data-type="banner">Ask about banners &rarr;</a>
        </article>
        <article class="package-card is-featured">
            <span class="ribbon">Most popular</span>
            <span class="icon-badge"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3 6.3 6.9 1-5 4.8 1.2 6.9L12 17.8 5.9 21l1.2-6.9-5-4.8 6.9-1z"/></svg></span>
            <h3>Price-card sponsorship</h3>
            <p>"Presented by" branding next to the live price cards and calculator - the most viewed part of the site.</p>
            <ul class="check-list">
                <li>Logo + short message + link</li>
                <li>Exclusive per category</li>
                <li>English, Sinhala and Tamil pages</li>
            </ul>
            <a class="package-link" href="#enquiry" data-type="sponsor">Ask about sponsorship &rarr;</a>
        </article>
        <article class="package-card">
            <span class="icon-badge"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 7l-5 5 5 5M16 7l5 5-5 5"/></svg></span>
            <h3>Widget &amp; API sponsor</h3>
            <p>Your name on the free gold-price widget and API docs, seen on every partner site that embeds it.</p>
            <ul class="check-list">
                <li>Reach beyond this site</li>
                <li>Developer and publisher audience</li>
                <li>Long-term placements</li>
            </ul>
            <a class="package-link" href="#enquiry" data-type="widget">Ask about the widget &rarr;</a>
        </article>
        <article class="package-card">
            <span class="icon-badge"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v16H4z"/><path d="M8 8h8M8 12h8M8 16h5"/></svg></span>
            <h3>Sponsored content</h3>
            <p>Labelled articles or listings: new collections, pawning rates, savings schemes or store openings.</p>
            <ul class="check-list">
                <li>Written with you, marked "Sponsored"</li>
                <li>Shared through RSS</li>
                <li>Stays live and searchable</li>
            </ul>
            <a class="package-link" href="#enquiry" data-type="content">Ask about content &rarr;</a>
        </article>
    </div>

    <div class="audience-strip">
        <span class="audience-title">Ideal for</span>
        <span class="chip">Jewellers</span>
        <span class="chip">Pawning &amp; gold-loan providers</span>
        <span class="chip">Banks &amp; finance companies</span>
        <span class="chip">Bullion &amp; coin dealers</span>
        <span class="chip">Wedding &amp; lifestyle brands</span>
        <span class="chip">Remittance services</span>
    </div>

    <div class="enquiry-layout" id="enquiry">
        <div class="enquiry-card">
            <span class="eyebrow">Get in touch</span>
            <h2>Write us a message</h2>

            <?php if ($sent): ?>
            <div class="form-success" role="status">
                <span class="icon-badge"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12l5 5L20 7"/></svg></span>
                <div>
                    <strong>Thank you - your enquiry has been sent.</strong>
                    <p>We will reply to your email, usually within one business day.</p>
                </div>
            </div>
            <?php endif; ?>

            <?php if (isset($errors['form'])): ?>
            <div class="form-alert" role="alert"><?= View::e($errors['form']) ?></div>
            <?php elseif (!empty($errors)): ?>
            <div class="form-alert" role="alert">Please check the highlighted fields below.</div>
            <?php endif; ?>

            <form class="enquiry-form" method="post" action="/contact#enquiry" novalidate>
                <input type="hidden" name="_t" value="<?= View::e($formToken) ?>">
                <div class="hp-field" aria-hidden="true">
                    <label>Leave this empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>

                <fieldset class="type-picker">
                    <legend>I'm interested in <span class="req">*</span></legend>
                    <div class="type-options">
                    <?php foreach ($types as $key => $label): ?>
                        <label class="type-option">
                            <input type="radio" name="enquiry_type" value="<?= View::e($key) ?>"<?= $selectedType === $key ? ' checked' : '' ?> required>
                            <span><?= View::e($label) ?></span>
                        </label>
                    <?php endforeach; ?>
                    </div>
                    <?= $err('enquiry_type') ?>
                </fieldset>

                <div class="form-grid">
                    <label class="field">
                        <span>Your name <span class="req">*</span></span>
                        <input type="text" name="name" value="<?= $val('name') ?>" autocomplete="name" maxlength="120" required<?= $invalid('name') ?>>
                        <?= $err('name') ?>
                    </label>
                    <label class="field">
                        <span>Company / brand</span>
                        <input type="text" name="company" value="<?= $val('company') ?>" autocomplete="organization" maxlength="160">
                    </label>
                    <label class="field">
                        <span>Email <span class="req">*</span></span>
                        <input type="email" name="email" value="<?= $val('email') ?>" autocomplete="email" maxlength="190" required<?= $invalid('email') ?>>
                        <?= $err('email') ?>
                    </label>
                    <label class="field">
                        <span>Phone / WhatsApp</span>
                        <input type="tel" name="phone" value="<?= $val('phone') ?>" autocomplete="tel" maxlength="40" placeholder="+94 7X XXX XXXX"<?= $invalid('phone') ?>>
                        <?= $err('phone') ?>
                    </label>
                    <label class="field field-wide">
                        <span>Monthly budget</span>
                        <select name="budget">
                        <?php foreach ($budgets as $key => $label): ?>
                            <option value="<?= View::e($key) ?>"<?= ($old['budget'] ?? '') === $key ? ' selected' : '' ?>><?= View::e($label) ?></option>
                        <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="field field-wide">
                        <span>Message <span class="req">*</span></span>
                        <textarea name="message" rows="5" maxlength="3000" required placeholder="What would you like to promote, when, and to whom?"<?= $invalid('message') ?>><?= $val('message') ?></textarea>
                        <?= $err('message') ?>
                    </label>
                </div>

                <div class="form-foot">
                    <button type="submit" class="btn-submit">
                        Send enquiry
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4z"/></svg>
                    </button>
                    <p class="fine">We only use your details to reply to this enquiry. See our <a href="/privacy">privacy policy</a>.</p>
                </div>
            </form>
        </div>

        <aside class="enquiry-side">
            <div class="side-card">
                <h3>How it works</h3>
                <ol class="steps">
                    <li><strong>Tell us your goal</strong><span>Send the form or message us on WhatsApp.</span></li>
                    <li><strong>Get a proposal</strong><span>Placements, dates and pricing within one business day.</span></li>
                    <li><strong>Go live</strong><span>Send your artwork; we publish and share a report.</span></li>
                </ol>
            </div>
            <div class="side-card side-card-muted">
                <h3>Our ad standards</h3>
                <p>To protect readers' trust we do not accept ads that quote their own "gold rate" as if it were ours,
                    promise guaranteed returns, or imitate this site's price cards. Ads never change the published price.</p>
            </div>
        </aside>
    </div>

    <div class="section-head">
        <span class="eyebrow">Support</span>
        <h2>Not about advertising?</h2>
    </div>
    <div class="help-grid">
        <div class="help-item">
            <h3>Price looks wrong?</h3>
            <p>Email us with the page URL and the exact time you viewed it - prices change intraday. Read
                <a href="/data-sources">how prices are verified</a> first: most "wrong" prices are the last verified
                reading held during a source disagreement, which is expected behaviour.</p>
        </div>
        <div class="help-item">
            <h3>API or widget help</h3>
            <p>Most issues are covered in the <a href="/gold-price-api">API docs</a> and the
                <a href="/gold-price-widget/how-to-use">widget guide</a>.</p>
        </div>
        <div class="help-item">
            <h3>Buying or selling gold?</h3>
            <p>We are not a jewellery retailer and cannot quote a selling price, buy or sell gold. Please contact a jeweller directly.</p>
        </div>
    </div>

    <div class="section-head">
        <span class="eyebrow">FAQ</span>
        <h2>Advertising questions</h2>
    </div>
    <div class="faq">
        <details>
            <summary>How much does advertising cost?</summary>
            <p>Pricing depends on the placement, the pages and the length of the booking. Send an enquiry with your
                budget range and we will propose the best fit.</p>
        </details>
        <details>
            <summary>Can I target Sinhala or Tamil readers only?</summary>
            <p>Yes. The site has separate English, Sinhala and Tamil pages, so placements can be booked per language.</p>
        </details>
        <details>
            <summary>Will an ad change the gold price shown?</summary>
            <p>Never. Prices come only from verified market sources and are calculated automatically. Advertising is
                labelled and kept visually separate from the price data.</p>
        </details>
        <details>
            <summary>What artwork do you need?</summary>
            <p>For banners, a PNG, JPG or WebP for each size plus your landing-page link. We can share exact sizes
                with your proposal.</p>
        </details>
    </div>
</section>

<script>
(function () {
    // Package "Ask about ..." links pre-select the matching interest in the form.
    document.querySelectorAll('.package-link[data-type]').forEach(function (link) {
        link.addEventListener('click', function () {
            var radio = document.querySelector('input[name="enquiry_type"][value="' + link.dataset.type + '"]');
            if (radio) { radio.checked = true; }
        });
    });
})();
</script>
