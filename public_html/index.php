<?php

declare(strict_types=1);

// Change this if goldprice-app lives somewhere other than right next to public_html
// (e.g. an addon domain with its own document root).
$appDir = dirname(__DIR__) . '/goldprice-app';

require $appDir . '/bootstrap.php';

use App\Controllers\AdminController;
use App\Controllers\ApiController;
use App\Controllers\FeedController;
use App\Controllers\HistoryController;
use App\Controllers\PageController;
use App\Core\Env;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

if (Env::bool('FORCE_HTTPS', false)) {
    $request = new Request();
    if (!$request->isSecure()) {
        $target = 'https://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '/');
        Response::redirect($target, 301);
        exit;
    }
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// Baseline security + caching headers for every public response (admin stays uncached).
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
$reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (strncmp($reqPath, '/admin', 6) !== 0 && strncmp($reqPath, '/cron', 5) !== 0) {
    header('Cache-Control: public, max-age=120, stale-while-revalidate=600');
} else {
    header('Cache-Control: no-store');
}

$router = new Router();

// Pages
$router->get('/', [new PageController(), 'home']);
$router->get('/si', function (Request $r) {
    (new PageController())->localizedHome($r, 'si');
});
$router->get('/ta', function (Request $r) {
    (new PageController())->localizedHome($r, 'ta');
});
$router->get('/gold-price-today-sri-lanka', [new PageController(), 'todayBreakdown']);
$router->get('/gold-price-{slug}-sri-lanka', function (Request $r, string $slug) {
    (new PageController())->purityOrWeight($r, $slug);
});
$router->get('/data-sources', [new PageController(), 'dataSources']);
$router->get('/gold-price-widget', [new PageController(), 'widgetBuilder']);
$router->get('/gold-price-widget/how-to-use', [new PageController(), 'widgetGuide']);
$router->get('/gold-price-api', [new PageController(), 'apiDocs']);
$router->get('/about', [new PageController(), 'about']);
$router->get('/contact', [new PageController(), 'contact']);
$router->post('/contact', [new PageController(), 'contact']);
$router->get('/advertise', fn () => Response::redirect('/contact', 301));
$router->get('/privacy', [new PageController(), 'privacy']);
$router->get('/terms', [new PageController(), 'terms']);

// History
$router->get('/gold-price-history', [new HistoryController(), 'index']);
$router->get('/gold-price-history/{year}', [new HistoryController(), 'year']);
$router->get('/gold-price-history/{year}/{month}', [new HistoryController(), 'month']);

// API
$router->get('/api/v1/gold-price', [new ApiController(), 'current']);
$router->get('/api/v1/gold-price/history', [new ApiController(), 'history']);

// Feeds / widget / SEO / monitoring
$router->get('/rss/gold-price.xml', [new FeedController(), 'rss']);
$router->get('/feed.xml', [new FeedController(), 'rss']);
$router->get('/widgets/gold-price.js', [new FeedController(), 'widgetScript']);
$router->get('/sitemap.xml', [new FeedController(), 'sitemap']);
$router->get('/robots.txt', [new FeedController(), 'robots']);
$router->get('/llms.txt', [new FeedController(), 'llms']);
$router->get('/og-image.png', [new FeedController(), 'ogImage']);
$router->get('/favicon.ico', [new FeedController(), 'logo']);
$router->get('/manifest.webmanifest', [new FeedController(), 'manifest']);
$router->get('/logo.png', [new FeedController(), 'logo']);
$router->get('/healthz', [new FeedController(), 'healthz']);
$router->get('/cron/run', [new FeedController(), 'cronRun']);
$router->post('/cron/run', [new FeedController(), 'cronRun']);

// Admin
$router->get('/admin/login', [new AdminController(), 'login']);
$router->post('/admin/login', [new AdminController(), 'login']);
$router->post('/admin/logout', [new AdminController(), 'logout']);
$router->get('/admin', [new AdminController(), 'dashboard']);
$router->post('/admin/update-now', [new AdminController(), 'updateNow']);
$router->post('/admin/accept-change/{id}', function (Request $r, string $id) {
    (new AdminController())->acceptChange($r, $id);
});

$router->dispatch(new Request());
