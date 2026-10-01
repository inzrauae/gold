<?php

namespace App\Controllers;

use App\Core\Cache;
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

    public function manifest(Request $request): void
    {
        Response::text(json_encode([
            'name' => Seo::siteName(),
            'short_name' => 'Gold Price LK',
            'description' => 'Verified live gold prices in Sri Lanka - 24K, 22K, 21K, 18K.',
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'lang' => 'en-LK',
            'background_color' => '#08070a',
            'theme_color' => '#08070a',
            'icons' => [['src' => '/logo.png', 'sizes' => 'any', 'type' => 'image/png', 'purpose' => 'any']],
        ], JSON_UNESCAPED_SLASHES), 200, 'application/manifest+json');
    }

    public function llms(Request $request): void
    {
        Response::text(Seo::llmsTxt(), 200, 'text/markdown');
    }

    public function ogImage(Request $request): void
    {
        if (!extension_loaded('gd')) {
            Response::notFound();
            return;
        }

        $latest = (new PriceRepository())->latest();
        $cacheKey = 'og-image:' . date('Y-m-d-H') . ':' . ($latest['id'] ?? 'none');

        $png = Cache::remember($cacheKey, 3600, function () use ($latest): string {
            return $this->renderOgImage($latest);
        });

        Response::image($png);
    }

    /** Square brand mark for schema.org Organization.logo - separate from the landscape OG banner. */
    public function logo(Request $request): void
    {
        if (!extension_loaded('gd')) {
            Response::notFound();
            return;
        }

        $png = Cache::remember('logo:v1', 0, function (): string {
            return $this->renderLogo();
        });

        Response::image($png, 'image/png', 86400);
    }

    private function renderLogo(): string
    {
        $size = 512;
        $im = imagecreatetruecolor($size, $size);

        $bg = imagecolorallocate($im, 0x08, 0x07, 0x0a);
        $bgAlt = imagecolorallocate($im, 0x1e, 0x19, 0x11);
        $gold = imagecolorallocate($im, 0xd4, 0xaf, 0x37);

        imagefilledrectangle($im, 0, 0, $size, $size, $bg);
        imagefilledellipse($im, $size / 2, $size / 2, 460, 460, $gold);
        imagefilledellipse($im, $size / 2, $size / 2, 392, 392, $bgAlt);
        imagefilledellipse($im, $size / 2, $size / 2, 320, 320, $gold);
        $this->drawScaledText($im, 'Au', (int) ($size / 2) - 46, (int) ($size / 2) - 38, 5, $bg, $gold);

        ob_start();
        imagepng($im);
        $data = (string) ob_get_clean();
        imagedestroy($im);

        return $data;
    }

    private function renderOgImage(?array $latest): string
    {
        $width = 1200;
        $height = 630;
        $im = imagecreatetruecolor($width, $height);

        $bg = imagecolorallocate($im, 0x08, 0x07, 0x0a);
        $bgAlt = imagecolorallocate($im, 0x1e, 0x19, 0x11);
        $gold = imagecolorallocate($im, 0xd4, 0xaf, 0x37);
        $goldLight = imagecolorallocate($im, 0xf6, 0xd6, 0x75);
        $text = imagecolorallocate($im, 0xf7, 0xf1, 0xe3);
        $textDim = imagecolorallocate($im, 0xbf, 0xb3, 0xa0);

        imagefilledrectangle($im, 0, 0, $width, $height, $bg);
        imagefilledrectangle($im, 0, 0, $width, 10, $gold);
        imagefilledrectangle($im, 60, 500, $width - 60, 501, $gold);

        // Decorative coin motif, top-right.
        imagefilledellipse($im, $width - 170, 170, 220, 220, $gold);
        imagefilledellipse($im, $width - 170, 170, 186, 186, $bgAlt);
        imagefilledellipse($im, $width - 170, 170, 150, 150, $gold);
        $this->drawScaledText($im, 'Au', $width - 170 - 27, 170 - 23, 3, $bg, $gold);

        $this->drawScaledText($im, 'GOLD PRICE TODAY', 60, 90, 4, $gold, $bg);
        $this->drawScaledText($im, 'SRI LANKA', 60, 145, 4, $goldLight, $bg);

        $y = 240;
        if ($latest) {
            foreach ([24, 22, 21, 18] as $k) {
                $val = number_format((float) $latest["price_{$k}k_gram"], 2);
                $this->drawScaledText($im, "{$k}K   LKR {$val} / g", 60, $y, 2, $text, $bg);
                $y += 52;
            }
        } else {
            $this->drawScaledText($im, 'Live, verified rates - updated daily', 60, $y, 2, $text, $bg);
        }

        $this->drawScaledText($im, 'As of ' . date('j M Y, g:i A') . ' (Asia/Colombo) - updated daily', 60, 555, 1, $textDim, $bg);

        ob_start();
        imagepng($im);
        $data = (string) ob_get_clean();
        imagedestroy($im);

        return $data;
    }

    /** Scales GD's built-in bitmap font up (no bundled TTF needed - keeps the site dependency-free). */
    private function drawScaledText($im, string $text, int $x, int $y, int $scale, int $fgColor, int $bgColor): void
    {
        $font = 5;
        $charW = imagefontwidth($font);
        $charH = imagefontheight($font);
        $w = max(1, $charW * strlen($text));
        $h = $charH;

        $tmp = imagecreatetruecolor($w, $h);
        $rgbBg = imagecolorsforindex($im, $bgColor);
        $tmpBg = imagecolorallocate($tmp, $rgbBg['red'], $rgbBg['green'], $rgbBg['blue']);
        imagefilledrectangle($tmp, 0, 0, $w, $h, $tmpBg);

        $rgbFg = imagecolorsforindex($im, $fgColor);
        $tmpFg = imagecolorallocate($tmp, $rgbFg['red'], $rgbFg['green'], $rgbFg['blue']);
        imagestring($tmp, $font, 0, 0, $text, $tmpFg);

        imagecopyresampled($im, $tmp, $x, $y, 0, 0, $w * $scale, $h * $scale, $w, $h);
        imagedestroy($tmp);
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
