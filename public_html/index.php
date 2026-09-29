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

$router = new Router();

// Pages
$router->get('/', [new PageController(), 'home']);
$router->get('/gold-price-today-sri-lanka', [new PageController(), 'todayBreakdown']);
$router->get('/gold-price-{slug}-sri-lanka', function (Request $r, string $slug) {
    (new PageController())->purityOrWeight($r, $slug);
});
$router->get('/data-sources', [new PageController(), 'dataSources']);
$router->get('/gold-price-widget', [new PageController(), 'widgetBuilder']);
$router->get('/gold-price-widget/how-to-use', [new PageController(), 'widgetGuide']);
$router->get('/gold-price-api', [new PageController(), 'apiDocs']);

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
