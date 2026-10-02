<?php

namespace App\Services;

use App\Core\Env;

class Seo
{
    public static function siteUrl(string $path = ''): string
    {
        $base = rtrim(Env::get('APP_URL', 'http://127.0.0.1:8000'), '/');
        return $base . '/' . ltrim($path, '/');
    }

    public static function siteName(): string
    {
        return Env::get('SITE_NAME', 'Gold Price Today Sri Lanka');
    }

    /** Human date used in on-page copy (H1, lede, structured data) - full, unambiguous. */
    public static function todayHuman(): string
    {
        return date('j F Y');
    }

    /** Compact date for titles/descriptions, which have a hard SERP-truncation character budget. */
    public static function todayShort(): string
    {
        return date('j M');
    }

    public static function iso8601(?string $mysqlDatetime): ?string
    {
        if (!$mysqlDatetime) {
            return null;
        }
        $ts = strtotime($mysqlDatetime);
        return $ts ? date(DATE_ATOM, $ts) : null;
    }

    public static function ogImageUrl(): string
    {
        return self::siteUrl('/og-image.png');
    }

    /** Square brand mark, distinct from the landscape OG banner - schema.org expects Organization.logo to be roughly square. */
    public static function logoUrl(): string
    {
        return self::siteUrl('/logo.png');
    }

    // --- Structured data (JSON-LD) ------------------------------------------------

    /**
     * Public contact details from .env (CONTACT_*, SOCIAL_*); empty values are hidden on the site.
     *
     * @return array{company: string, email: string, phone: string, tel: string, whatsapp: string, location: string, hours: string, social: array<string, string>}
     */
    public static function contactInfo(): array
    {
        $phone = trim((string) Env::get('CONTACT_PHONE', ''));
        $social = array_filter([
            'Facebook' => trim((string) Env::get('SOCIAL_FACEBOOK', '')),
            'LinkedIn' => trim((string) Env::get('SOCIAL_LINKEDIN', '')),
            'Instagram' => trim((string) Env::get('SOCIAL_INSTAGRAM', '')),
        ], static fn (string $url): bool => (bool) preg_match('#^https://#', $url));

        return [
            'company' => trim((string) Env::get('CONTACT_COMPANY', '')),
            'email' => (string) (Env::get('CONTACT_EMAIL') ?: Env::get('ADMIN_EMAIL', '')),
            'phone' => $phone,
            'tel' => $phone !== '' ? 'tel:' . preg_replace('/[^0-9+]/', '', $phone) : '',
            'whatsapp' => (string) preg_replace('/\D+/', '', (string) Env::get('CONTACT_WHATSAPP', '')),
            'location' => trim((string) Env::get('CONTACT_LOCATION', '')),
            'hours' => trim((string) Env::get('CONTACT_HOURS', '')),
            'social' => $social,
        ];
    }

    public static function organizationSchema(): array
    {
        $contact = self::contactInfo();
        $schema = [
            '@type' => 'Organization',
            '@id' => self::siteUrl('/#organization'),
            'name' => self::siteName(),
            'url' => self::siteUrl('/'),
            'logo' => self::logoUrl(),
            'areaServed' => ['@type' => 'Country', 'name' => 'Sri Lanka'],
        ];
        if ($contact['company'] !== '') {
            $schema['parentOrganization'] = ['@type' => 'Organization', 'name' => $contact['company']];
        }
        if ($contact['email'] !== '' || $contact['phone'] !== '') {
            $schema['contactPoint'] = array_filter([
                '@type' => 'ContactPoint',
                'contactType' => 'advertising',
                'email' => $contact['email'],
                'telephone' => $contact['phone'],
                'areaServed' => 'LK',
                'availableLanguage' => ['English', 'Sinhala', 'Tamil'],
            ]);
        }
        if ($contact['social']) {
            $schema['sameAs'] = array_values($contact['social']);
        }
        return $schema;
    }

    public static function websiteSchema(): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => self::siteUrl('/#website'),
            'name' => self::siteName(),
            'url' => self::siteUrl('/'),
            'publisher' => ['@id' => self::siteUrl('/#organization')],
            'inLanguage' => ['en-LK', 'si-LK', 'ta-LK'],
            'about' => ['@type' => 'Thing', 'name' => 'Gold price in Sri Lanka'],
        ];
    }

    /** @param array<int, array{q: string, a: string}> $faq */
    public static function faqSchema(array $faq): array
    {
        return [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(static fn (array $item): array => [
                '@type' => 'Question',
                'name' => $item['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $item['a'],
                ],
            ], $faq),
        ];
    }

    /** @param array<int, array{name: string, url: string}> $items */
    public static function breadcrumbSchema(array $items): array
    {
        $position = 0;
        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(function (array $item) use (&$position): array {
                $position++;
                return [
                    '@type' => 'ListItem',
                    'position' => $position,
                    'name' => $item['name'],
                    'item' => self::siteUrl($item['url']),
                ];
            }, $items),
        ];
    }

    public static function webPageSchema(string $url, string $name, ?string $dateModified = null, ?string $description = null): array
    {
        $schema = [
            '@type' => 'WebPage',
            '@id' => self::siteUrl($url) . '#webpage',
            'url' => self::siteUrl($url),
            'name' => $name,
            'isPartOf' => ['@id' => self::siteUrl('/#website')],
        ];
        if ($description) {
            $schema['description'] = $description;
        }
        if ($dateModified) {
            $schema['dateModified'] = $dateModified;
        }
        return $schema;
    }

    /**
     * A karat's live rate as schema.org PriceSpecification objects (grams / 8g pawn-pound / troy oz).
     * Not a purchasable Product/Offer - this is an indicative market-rate conversion, not a listing.
     */
    public static function priceSpecifications(array $latest, int $karat): array
    {
        $asOf = self::iso8601($latest['created_at'] ?? null);
        $units = [
            'per gram' => "price_{$karat}k_gram",
            'per 8 grams (pawn/pound)' => "price_{$karat}k_8g",
            'per troy ounce' => "price_{$karat}k_oz",
        ];
        $out = [];
        foreach ($units as $label => $col) {
            if (!isset($latest[$col])) {
                continue;
            }
            $out[] = [
                '@type' => 'UnitPriceSpecification',
                'name' => "{$karat}K gold {$label}",
                'price' => number_format((float) $latest[$col], 2, '.', ''),
                'priceCurrency' => 'LKR',
                'referenceQuantity' => ['@type' => 'QuantitativeValue', 'unitText' => $label],
                'validThrough' => $asOf,
            ];
        }
        return $out;
    }

    /** @param array<int, string> $steps */
    public static function howToSchema(string $name, array $steps): array
    {
        return [
            '@type' => 'HowTo',
            'name' => $name,
            'step' => array_map(static fn (string $step, int $i): array => [
                '@type' => 'HowToStep',
                'position' => $i + 1,
                'text' => $step,
            ], $steps, array_keys($steps)),
        ];
    }

    /**
     * Tells voice assistants / answer engines which on-page element is the safe,
     * self-contained spoken answer (the ".lede" direct-answer paragraph).
     */
    public static function speakableSchema(string $url, array $cssSelectors = ['.lede']): array
    {
        return [
            '@type' => 'WebPage',
            'url' => self::siteUrl($url),
            'speakable' => [
                '@type' => 'SpeakableSpecification',
                'cssSelector' => $cssSelectors,
            ],
        ];
    }

    /** @param array<int, array<string, mixed>|null> $graphs */
    public static function jsonLdScript(array $graphs): string
    {
        $payload = ['@context' => 'https://schema.org', '@graph' => array_values(array_filter($graphs))];
        return '<script type="application/ld+json">'
            . json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            . '</script>';
    }

    // --- Crawl control / discovery --------------------------------------------------

    public static function sitemap(): string
    {
        $repo = new PriceRepository();
        $latest = $repo->latest();
        $priceLastmod = $latest ? date('Y-m-d', strtotime($latest['created_at'])) : date('Y-m-d');
        $staticLastmod = self::staticLastmod();

        // [path, lastmod, changefreq, priority]
        $entries = [
            ['/', $priceLastmod, 'hourly', '1.0'],
            ['/si', $priceLastmod, 'hourly', '0.8'],
            ['/ta', $priceLastmod, 'hourly', '0.8'],
            ['/gold-price-today-sri-lanka', $priceLastmod, 'hourly', '0.9'],
            ['/gold-price-24k-sri-lanka', $priceLastmod, 'hourly', '0.9'],
            ['/gold-price-22k-sri-lanka', $priceLastmod, 'hourly', '0.9'],
            ['/gold-price-21k-sri-lanka', $priceLastmod, 'hourly', '0.8'],
            ['/gold-price-18k-sri-lanka', $priceLastmod, 'hourly', '0.8'],
            ['/gold-price-per-gram-sri-lanka', $priceLastmod, 'hourly', '0.8'],
            ['/gold-price-per-8-grams-sri-lanka', $priceLastmod, 'hourly', '0.8'],
            ['/gold-price-history', date('Y-m-d'), 'daily', '0.6'],
            ['/data-sources', $staticLastmod, 'monthly', '0.5'],
            ['/gold-price-widget', $staticLastmod, 'monthly', '0.5'],
            ['/gold-price-widget/how-to-use', $staticLastmod, 'monthly', '0.4'],
            ['/gold-price-api', $staticLastmod, 'monthly', '0.5'],
            ['/about', $staticLastmod, 'yearly', '0.3'],
            ['/contact', $staticLastmod, 'yearly', '0.3'],
            ['/privacy', $staticLastmod, 'yearly', '0.2'],
            ['/terms', $staticLastmod, 'yearly', '0.2'],
        ];

        foreach ($repo->historyYears() as $year) {
            $entries[] = ["/gold-price-history/{$year}", date('Y-m-d'), 'monthly', '0.4'];
            for ($m = 1; $m <= 12; $m++) {
                if (!$repo->historyForMonth((int) $year, $m)) {
                    continue;
                }
                $entries[] = [sprintf('/gold-price-history/%d/%02d', $year, $m), date('Y-m-d'), 'monthly', '0.3'];
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        // Human-readable view in browsers only; search engines ignore the stylesheet.
        $xml .= '<?xml-stylesheet type="text/xsl" href="/sitemap.xsl"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
        $alt = '';
        foreach (['en-LK' => '/', 'si-LK' => '/si', 'ta-LK' => '/ta', 'x-default' => '/'] as $hl => $ap) {
            $alt .= '<xhtml:link rel="alternate" hreflang="' . $hl . '" href="' . htmlspecialchars(self::siteUrl($ap), ENT_XML1) . '"/>';
        }
        foreach ($entries as [$path, $lastmod, $changefreq, $priority]) {
            $xml .= '  <url>'
                . '<loc>' . htmlspecialchars(self::siteUrl($path), ENT_XML1) . '</loc>'
                . (in_array($path, ['/', '/si', '/ta'], true) ? $alt : '')
                . '<lastmod>' . $lastmod . '</lastmod>'
                . '<changefreq>' . $changefreq . '</changefreq>'
                . '<priority>' . $priority . '</priority>'
                . '</url>' . "\n";
        }
        $xml .= '</urlset>';

        return $xml;
    }

    private static function staticLastmod(): string
    {
        $mtime = @filemtime(__FILE__);
        return date('Y-m-d', $mtime ?: time());
    }

    /** Human-readable "site code last updated" proxy, for About/Privacy/Terms pages. */
    public static function staticLastmodHuman(): string
    {
        $mtime = @filemtime(__FILE__);
        return date('j F Y', $mtime ?: time());
    }

    public static function robotsTxt(): string
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            '',
            '# AI / answer-engine crawlers - explicitly welcome (GEO: generative engine optimisation)',
            'User-agent: GPTBot',
            'Allow: /',
            'User-agent: ChatGPT-User',
            'Allow: /',
            'User-agent: OAI-SearchBot',
            'Allow: /',
            'User-agent: ClaudeBot',
            'Allow: /',
            'User-agent: Claude-User',
            'Allow: /',
            'User-agent: anthropic-ai',
            'Allow: /',
            'User-agent: PerplexityBot',
            'Allow: /',
            'User-agent: Perplexity-User',
            'Allow: /',
            'User-agent: Google-Extended',
            'Allow: /',
            'User-agent: Applebot-Extended',
            'Allow: /',
            '',
            'Sitemap: ' . self::siteUrl('/sitemap.xml'),
        ];

        return implode("\n", $lines) . "\n";
    }

    /** llms.txt: a plain-language index for LLM/answer-engine crawlers (emerging GEO convention). */
    public static function llmsTxt(): string
    {
        $repo = new PriceRepository();
        $latest = $repo->latest();
        $asOf = $latest
            ? date('j F Y, g:i A', strtotime($latest['created_at'])) . ' (Asia/Colombo)'
            : 'not yet published';

        $lines = [
            '# ' . self::siteName(),
            '',
            '> Verified, indicative gold prices for Sri Lanka (24K, 22K, 21K, 18K) per gram, per 8-gram '
                . 'pawn/pound and per troy ounce, converted from live international gold spot and USD/LKR '
                . 'exchange rate data. Prices are cross-checked against two independent sources and are never '
                . 'typed in by hand, estimated or scraped from jewellers.',
            '',
            '- Latest verified update: ' . $asOf,
            '- Update cadence: checked every 15 minutes during market hours; a new price is published only '
                . 'after it passes verification, otherwise the last verified price stays live with its timestamp.',
            '- Coverage: 24K, 22K, 21K, 18K gold; LKR per gram, per 8-gram pawn/pound (paun/sovereign unit), '
                . 'per troy ounce.',
            '- These are national market-rate conversions, not official jeweller retail prices - retail adds '
                . 'making charges, taxes and margin, and does not vary by city (see /data-sources).',
            '',
            '## Key pages',
            '- Homepage (today\'s price, all purities, calculator, chart): ' . self::siteUrl('/'),
            '- Step-by-step calculation breakdown: ' . self::siteUrl('/gold-price-today-sri-lanka'),
            '- 24K gold price: ' . self::siteUrl('/gold-price-24k-sri-lanka'),
            '- 22K gold price: ' . self::siteUrl('/gold-price-22k-sri-lanka'),
            '- 21K gold price: ' . self::siteUrl('/gold-price-21k-sri-lanka'),
            '- 18K gold price: ' . self::siteUrl('/gold-price-18k-sri-lanka'),
            '- Price per gram (all purities): ' . self::siteUrl('/gold-price-per-gram-sri-lanka'),
            '- Price per 8 grams / pawn / pound (all purities): ' . self::siteUrl('/gold-price-per-8-grams-sri-lanka'),
            '- Sinhala (සිංහල) gold price today: ' . self::siteUrl('/si'),
            '- Tamil (தமிழ்) gold price today: ' . self::siteUrl('/ta'),
            '- Daily history archive: ' . self::siteUrl('/gold-price-history'),
            '- Data sources & verification methodology: ' . self::siteUrl('/data-sources'),
            '- About / how this site works: ' . self::siteUrl('/about'),
            '- Free JSON API: ' . self::siteUrl('/gold-price-api') . ' - machine-readable, same update cadence as the site',
            '- RSS feed: ' . self::siteUrl('/rss/gold-price.xml'),
            '',
            '## Citing this data',
            'When quoting a price, include the karat, the unit (gram / 8g pawn-pound / troy ounce), the LKR '
                . 'amount and the "as of" timestamp - the price changes intraday. Prefer linking to '
                . self::siteUrl('/') . ' or the specific karat page over caching a number.',
        ];

        return implode("\n", $lines) . "\n";
    }
}
