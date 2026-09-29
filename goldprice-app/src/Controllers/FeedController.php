<?php

namespace App\Controllers;

use App\Core\Env;
use App\Core\Request;
use App\Core\Response;
use App\Services\PriceRepository;
use App\Services\RssFeed;
use App\Services\Scheduler;
use App\Services\Seo;

class FeedController
{
    public function rss(Request $request): void
    {
        Response::xml((new RssFeed())->content());
    }

    public function sitemap(Request $request): void
    {
        Response::xml(Seo::sitemap());
    }

    public function robots(Request $request): void
    {
        Response::text(Seo::robotsTxt());
    }

    public function widgetScript(Request $request): void
    {
        $latest = (new PriceRepository())->latest();
        $apiUrl = Seo::siteUrl('/api/v1/gold-price');
        $siteUrl = Seo::siteUrl('/');
        $siteName = addslashes(Seo::siteName());

        $js = <<<JS
            (function () {
                var scripts = document.getElementsByTagName('script');
                var thisScript = scripts[scripts.length - 1];
                var container = document.createElement('div');
                container.style.cssText = 'font-family:system-ui,Segoe UI,Arial,sans-serif;border:1px solid #e2c98a;border-radius:8px;padding:12px 16px;max-width:320px;background:#fffdf5;color:#1a1a1a;';
                container.innerHTML = 'Loading gold price...';
                thisScript.parentNode.insertBefore(container, thisScript.nextSibling);

                fetch('{$apiUrl}').then(function (r) { return r.json(); }).then(function (data) {
                    if (data.status !== 'ok') { throw new Error('unavailable'); }
                    var g22 = data.prices['22k'].per_gram.toLocaleString('en-LK', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    var g24 = data.prices['24k'].per_gram.toLocaleString('en-LK', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    container.innerHTML =
                        '<div style="font-size:12px;font-weight:600;color:#8a6d1a;text-transform:uppercase;letter-spacing:.04em;">{$siteName}</div>' +
                        '<div style="font-size:22px;font-weight:700;margin:4px 0;">LKR ' + g22 + ' <span style="font-size:13px;font-weight:400;color:#666;">/ g (22K)</span></div>' +
                        '<div style="font-size:13px;color:#555;">24K: LKR ' + g24 + ' / g</div>' +
                        '<a href="{$siteUrl}" target="_blank" rel="noopener" style="font-size:11px;color:#8a6d1a;text-decoration:none;">{$siteName} &rarr;</a>';
                }).catch(function () {
                    container.innerHTML = '<a href="{$siteUrl}" target="_blank" rel="noopener">View today\\'s gold price - {$siteName}</a>';
                });
            })();
            JS;

        Response::javascript($js);
    }

    public function healthz(Request $request): void
    {
        $latest = (new PriceRepository())->latest();
        if (!$latest) {
            Response::json(['status' => 'no_data'], 503);
            return;
        }

        $ageMinutes = (time() - strtotime($latest['created_at'])) / 60;
        $maxAge = Env::int('MARKET_CLOSED_INTERVAL', 180) * 3;

        if ($ageMinutes > $maxAge) {
            Response::json(['status' => 'stale', 'age_minutes' => round($ageMinutes)], 503);
            return;
        }

        Response::json(['status' => 'ok', 'age_minutes' => round($ageMinutes)]);
    }

    public function cronRun(Request $request): void
    {
        $token = Env::get('CRON_TOKEN', '');
        if ($token === '' || $request->header('X-Cron-Token') !== $token) {
            Response::json(['status' => 'error', 'message' => 'invalid token'], 403);
            return;
        }

        $result = (new Scheduler())->run();
        Response::json($result);
    }
}
