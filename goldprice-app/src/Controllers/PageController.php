<?php

namespace App\Controllers;

use App\Core\Env;
use App\Core\Logger;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\Content;
use App\Services\EnquiryService;
use App\Services\Lang;
use App\Services\NewsService;
use App\Services\PriceRepository;
use App\Services\Scheduler;
use App\Services\Seo;

class PageController
{
    private PriceRepository $repository;

    public function __construct()
    {
        $this->repository = new PriceRepository();
    }

    public function home(Request $request): void
    {
        $latest = $this->repository->latest();
        $chart = $this->chartData();
        $date = Seo::todayHuman();
        $shortDate = Seo::todayShort();
        $trend = $this->trend($latest);

        $title = $latest
            ? "Gold Price Today in Sri Lanka ({$shortDate}) | Live Rate"
            : 'Gold Price Today Sri Lanka - Live 24K, 22K, 21K, 18K Rates';

        $description = $latest
            ? sprintf(
                'Gold price in Sri Lanka today: 22K LKR %s/g, 24K LKR %s/g. Live, verified rate for Colombo & Sri Lanka - updated daily.',
                number_format((float) $latest['price_22k_gram'], 2),
                number_format((float) $latest['price_24k_gram'], 2)
            )
            : 'Live, verified gold prices in Sri Lanka for 24K, 22K, 21K and 18K gold, per gram, per 8g pawn and per troy ounce, with history and a calculator.';

        $jsonLd = [
            Seo::organizationSchema(),
            Seo::websiteSchema(),
            Seo::webPageSchema('/', $title, Seo::iso8601($latest['created_at'] ?? null), $description),
            Seo::faqSchema(Content::faq()),
        ];
        if ($latest) {
            $jsonLd[] = Seo::speakableSchema('/');
        }

        Response::html(View::layout('home', [
            'title' => $title,
            'description' => $description,
            'canonical' => '/',
            'updatedAt' => $latest['created_at'] ?? null,
            'jsonLd' => $jsonLd,
            'latest' => $latest,
            'chart' => $chart,
            'marketOpen' => (new Scheduler())->isMarketOpen(),
            'news' => $this->safeNews(),
            'faq' => Content::faq(),
            'trend' => $trend,
            'date' => $date,
        ]));
    }

    public function localizedHome(Request $request, string $lang): void
    {
        if (!Lang::valid($lang)) {
            Response::notFound();
            return;
        }
        $t = Lang::strings($lang);
        $latest = $this->repository->latest();
        $shortDate = Seo::todayShort();
        $path = Lang::path($lang);
        $n = static fn ($v): string => number_format((float) $v, 2);

        $title = sprintf($t['title'], $shortDate);
        $description = $latest
            ? sprintf($t['desc'], $n($latest['price_22k_gram']), $n($latest['price_24k_gram']))
            : $t['name'];

        if ($latest) {
            $t['faq'][0]['a'] = sprintf($t['faq'][0]['a'], $n($latest['price_22k_8g']), $n($latest['price_24k_8g']));
        } else {
            $t['faq'][0]['a'] = str_replace(['රු. %s ක් ද', 'ரூ. %s'], ['', ''], $t['faq'][0]['a']);
        }

        $page = Seo::webPageSchema($path, $title, Seo::iso8601($latest['created_at'] ?? null), $description);
        $page['inLanguage'] = Lang::LOCALES[$lang];
        $jsonLd = [
            Seo::organizationSchema(),
            $page,
            Seo::faqSchema($t['faq']),
            Seo::breadcrumbSchema([['name' => 'Home', 'url' => '/'], ['name' => $t['name'], 'url' => $path]]),
        ];

        Response::html(View::layout('home-local', [
            'title' => $title,
            'description' => $description,
            'canonical' => $path,
            'updatedAt' => $latest['created_at'] ?? null,
            'jsonLd' => $jsonLd,
            'latest' => $latest,
            't' => $t,
            'lang' => $lang,
        ]));
    }

    public function todayBreakdown(Request $request): void
    {
        $latest = $this->repository->latest();
        $date = Seo::todayHuman();
        $shortDate = Seo::todayShort();
        $trend = $this->trend($latest);

        $title = "Gold Price Today in Sri Lanka - Step by Step ({$shortDate})";
        $description = "How today's gold price in Sri Lanka is calculated: live spot price, USD/LKR rate, "
            . "verification checks and the full step-by-step breakdown.";

        $jsonLd = [
            Seo::webPageSchema('/gold-price-today-sri-lanka', $title, Seo::iso8601($latest['created_at'] ?? null), $description),
            Seo::breadcrumbSchema([
                ['name' => 'Home', 'url' => '/'],
                ['name' => 'Today\'s Breakdown', 'url' => '/gold-price-today-sri-lanka'],
            ]),
            Seo::howToSchema('How the Gold Price in Sri Lanka Is Calculated', Content::methodSteps()),
        ];
        if ($latest) {
            $jsonLd[] = Seo::speakableSchema('/gold-price-today-sri-lanka');
        }

        Response::html(View::layout('today-breakdown', [
            'title' => $title,
            'description' => $description,
            'canonical' => '/gold-price-today-sri-lanka',
            'updatedAt' => $latest['created_at'] ?? null,
            'jsonLd' => $jsonLd,
            'latest' => $latest,
            'steps' => Content::methodSteps(),
            'logs' => $this->repository->recentLogs(10),
            'trend' => $trend,
            'date' => $date,
        ]));
    }

    public function purityOrWeight(Request $request, string $slug): void
    {
        $karatMap = ['24k' => 24, '22k' => 22, '21k' => 21, '18k' => 18];
        if (isset($karatMap[$slug])) {
            $this->purity($request, $slug, $karatMap[$slug]);
            return;
        }

        $units = ['per-gram' => 'gram', 'per-8-grams' => '8g'];
        if (isset($units[$slug])) {
            $this->weight($request, $slug, $units[$slug]);
            return;
        }

        Response::notFound();
    }

    private function purity(Request $request, string $karat, int $k): void
    {
        $latest = $this->repository->latest();
        $date = Seo::todayHuman();
        $shortDate = Seo::todayShort();

        $title = "{$k}K Gold Price Today in Sri Lanka ({$shortDate}) | Live Rate";
        $description = $latest
            ? sprintf(
                '%dK gold price in Sri Lanka today: LKR %s/g, LKR %s/8g pawn. Live, verified rate - updated daily.',
                $k,
                number_format((float) $latest["price_{$karat}_gram"], 2),
                number_format((float) $latest["price_{$karat}_8g"], 2)
            )
            : "Live {$k}K gold price in Sri Lanka per gram, per 8g pawn and per troy ounce.";

        $jsonLd = [
            Seo::webPageSchema("/gold-price-{$karat}-sri-lanka", $title, Seo::iso8601($latest['created_at'] ?? null), $description),
            Seo::breadcrumbSchema([
                ['name' => 'Home', 'url' => '/'],
                ['name' => "{$k}K Gold Price", 'url' => "/gold-price-{$karat}-sri-lanka"],
            ]),
        ];
        if ($latest) {
            $specs = Seo::priceSpecifications($latest, $k);
            $jsonLd[] = [
                '@type' => 'ItemList',
                'itemListElement' => array_map(
                    static fn (array $spec, int $i): array => ['@type' => 'ListItem', 'position' => $i + 1, 'item' => $spec],
                    $specs,
                    array_keys($specs)
                ),
            ];
            $jsonLd[] = Seo::speakableSchema("/gold-price-{$karat}-sri-lanka");
        }

        Response::html(View::layout('purity', [
            'title' => $title,
            'description' => $description,
            'canonical' => "/gold-price-{$karat}-sri-lanka",
            'updatedAt' => $latest['created_at'] ?? null,
            'jsonLd' => $jsonLd,
            'karat' => $k,
            'latest' => $latest,
            'date' => $date,
        ]));
    }

    private function weight(Request $request, string $unit, string $u): void
    {
        $latest = $this->repository->latest();
        $date = Seo::todayHuman();
        $shortDate = Seo::todayShort();
        $isPawn = $u !== 'gram';

        $title = $isPawn
            ? "Gold Pawn / Pound Price in Sri Lanka Today (8g) - {$shortDate}"
            : "Gold Price Per Gram in Sri Lanka Today - {$shortDate}";

        $description = $isPawn
            ? 'Gold pawn (also "pound"/paun/sovereign) price in Sri Lanka today, 8g, for 24K, 22K, 21K '
                . '& 18K gold. Verified rate, updated daily.'
            : 'Live gold price per gram in Sri Lanka today for 24K, 22K, 21K and 18K gold. Verified rate, updated daily.';

        $jsonLd = [
            Seo::webPageSchema("/gold-price-{$unit}-sri-lanka", $title, Seo::iso8601($latest['created_at'] ?? null), $description),
            Seo::breadcrumbSchema([
                ['name' => 'Home', 'url' => '/'],
                ['name' => $isPawn ? 'Gold Price Per 8g (Pawn/Pound)' : 'Gold Price Per Gram', 'url' => "/gold-price-{$unit}-sri-lanka"],
            ]),
        ];
        if ($latest) {
            $jsonLd[] = Seo::speakableSchema("/gold-price-{$unit}-sri-lanka");
        }

        Response::html(View::layout('weight', [
            'title' => $title,
            'description' => $description,
            'canonical' => "/gold-price-{$unit}-sri-lanka",
            'updatedAt' => $latest['created_at'] ?? null,
            'jsonLd' => $jsonLd,
            'unit' => $u,
            'latest' => $latest,
            'date' => $date,
        ]));
    }

    public function dataSources(Request $request): void
    {
        $latest = $this->repository->latest();

        Response::html(View::layout('data-sources', [
            'title' => 'Data Sources & Methodology - Gold Price Today Sri Lanka',
            'description' => 'Where our gold price and USD/LKR exchange rate data comes from, and how every price is verified before publication.',
            'canonical' => '/data-sources',
            'jsonLd' => [
                Seo::breadcrumbSchema([
                    ['name' => 'Home', 'url' => '/'],
                    ['name' => 'Data Sources', 'url' => '/data-sources'],
                ]),
            ],
            'latest' => $latest,
            'steps' => Content::methodSteps(),
        ]));
    }

    public function widgetBuilder(Request $request): void
    {
        Response::html(View::layout('widget-builder', [
            'title' => 'Free Gold Price Widget for Your Website',
            'description' => 'Embed a live, self-updating gold price widget on your website in one line of code.',
            'canonical' => '/gold-price-widget',
            'jsonLd' => [
                Seo::breadcrumbSchema([
                    ['name' => 'Home', 'url' => '/'],
                    ['name' => 'Widget', 'url' => '/gold-price-widget'],
                ]),
            ],
        ]));
    }

    public function widgetGuide(Request $request): void
    {
        Response::html(View::layout('widget-guide', [
            'title' => 'Gold Price Widget - How To Use & Terms',
            'description' => 'How to install the gold price widget, customise it, and the terms of use.',
            'canonical' => '/gold-price-widget/how-to-use',
            'jsonLd' => [
                Seo::breadcrumbSchema([
                    ['name' => 'Home', 'url' => '/'],
                    ['name' => 'Widget', 'url' => '/gold-price-widget'],
                    ['name' => 'How To Use', 'url' => '/gold-price-widget/how-to-use'],
                ]),
            ],
        ]));
    }

    public function apiDocs(Request $request): void
    {
        Response::html(View::layout('api-docs', [
            'title' => 'Free Gold Price JSON API - Gold Price Today Sri Lanka',
            'description' => 'Free JSON API for verified Sri Lankan gold prices: current price and history.',
            'canonical' => '/gold-price-api',
            'jsonLd' => [
                Seo::breadcrumbSchema([
                    ['name' => 'Home', 'url' => '/'],
                    ['name' => 'API', 'url' => '/gold-price-api'],
                ]),
            ],
        ]));
    }

    public function about(Request $request): void
    {
        Response::html(View::layout('about', [
            'title' => 'About - Gold Price Today Sri Lanka',
            'description' => 'How Gold Price Today Sri Lanka verifies and publishes its live 24K, 22K, 21K and 18K gold prices, and what it is not.',
            'canonical' => '/about',
            'jsonLd' => [
                Seo::breadcrumbSchema([
                    ['name' => 'Home', 'url' => '/'],
                    ['name' => 'About', 'url' => '/about'],
                ]),
            ],
            'lastUpdated' => Seo::staticLastmodHuman(),
        ]));
    }

    public function contact(Request $request): void
    {
        $form = ['data' => [], 'errors' => []];
        $status = 200;

        if ($request->method === 'POST') {
            header('Cache-Control: no-store');
            $form = $this->handleEnquiry($request);
            if ($form === null) {
                Response::redirect('/contact?sent=1#enquiry', 303);
                return;
            }
            $status = 422;
        }

        Response::html(View::layout('contact', [
            'title' => 'Advertise & Contact - Gold Price Today Sri Lanka',
            'description' => 'Advertise to gold buyers, jewellers and investors across Sri Lanka. Banner, sponsorship and widget packages - or contact us about prices, the API and the widget.',
            'canonical' => '/contact',
            'jsonLd' => [
                Seo::breadcrumbSchema([
                    ['name' => 'Home', 'url' => '/'],
                    ['name' => 'Contact', 'url' => '/contact'],
                ]),
            ],
            'contact' => [
                'email' => Env::get('CONTACT_EMAIL') ?: Env::get('ADMIN_EMAIL', 'contact@example.lk'),
                'phone' => (string) Env::get('CONTACT_PHONE', ''),
                'whatsapp' => preg_replace('/\D+/', '', (string) Env::get('CONTACT_WHATSAPP', '')),
                'location' => (string) Env::get('CONTACT_LOCATION', 'Colombo, Sri Lanka'),
            ],
            'formToken' => EnquiryService::formToken(),
            'old' => $form['data'],
            'errors' => $form['errors'],
            'sent' => $request->method === 'GET' && $request->query('sent') === '1',
            'types' => EnquiryService::TYPES,
            'budgets' => EnquiryService::BUDGETS,
        ]), $status);
    }

    /**
     * Returns null on success, or the submitted data plus errors to re-render the form.
     *
     * @return array{data: array<string, string>, errors: array<string, string>}|null
     */
    private function handleEnquiry(Request $request): ?array
    {
        $input = $request->body;

        // Honeypot or invalid/too-fast token: pretend success so bots learn nothing.
        if (trim((string) ($input['website'] ?? '')) !== '' || !EnquiryService::tokenValid((string) ($input['_t'] ?? ''))) {
            Logger::info('enquiry_rejected_spam', ['ip' => $request->ip()]);
            return null;
        }

        $result = EnquiryService::validate($input);
        if (!empty($result['errors'])) {
            return $result;
        }

        if (!RateLimiter::attempt('enquiry:' . $request->ip(), 5, 3600)) {
            return ['data' => $result['data'], 'errors' => ['form' => 'Too many messages from your connection. Please try again later or email us directly.']];
        }

        EnquiryService::store($result['data'], $request->ip());
        EnquiryService::notifyAdmin($result['data']);
        return null;
    }

    public function privacy(Request $request): void
    {
        Response::html(View::layout('privacy', [
            'title' => 'Privacy Policy - Gold Price Today Sri Lanka',
            'description' => 'What Gold Price Today Sri Lanka does and does not collect: no analytics or ad tracking, no accounts required to browse.',
            'canonical' => '/privacy',
            'jsonLd' => [
                Seo::breadcrumbSchema([
                    ['name' => 'Home', 'url' => '/'],
                    ['name' => 'Privacy Policy', 'url' => '/privacy'],
                ]),
            ],
            'adminEmail' => Env::get('ADMIN_EMAIL', 'contact@example.lk'),
            'lastUpdated' => Seo::staticLastmodHuman(),
        ]));
    }

    public function terms(Request $request): void
    {
        Response::html(View::layout('terms', [
            'title' => 'Terms of Use - Gold Price Today Sri Lanka',
            'description' => 'Terms for using Gold Price Today Sri Lanka, its free API, RSS feed and embeddable widget.',
            'canonical' => '/terms',
            'jsonLd' => [
                Seo::breadcrumbSchema([
                    ['name' => 'Home', 'url' => '/'],
                    ['name' => 'Terms of Use', 'url' => '/terms'],
                ]),
            ],
            'lastUpdated' => Seo::staticLastmodHuman(),
        ]));
    }

    private function chartData(int $days = 90): array
    {
        $to = date('Y-m-d');
        $from = date('Y-m-d', strtotime("-{$days} days"));
        return $this->repository->historyBetween($from, $to);
    }

    private function trend(?array $latest): ?array
    {
        if (!$latest) {
            return null;
        }
        $previous = $this->repository->previousDayHistory(date('Y-m-d'));
        return Content::trendSentence($latest, $previous, 22);
    }

    private function safeNews(): array
    {
        try {
            return (new NewsService())->recent();
        } catch (\Throwable) {
            return [];
        }
    }
}
