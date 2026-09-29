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

    public static function sitemap(): string
    {
        $urls = [
            '/',
            '/gold-price-today-sri-lanka',
            '/gold-price-24k-sri-lanka',
            '/gold-price-22k-sri-lanka',
            '/gold-price-21k-sri-lanka',
            '/gold-price-18k-sri-lanka',
            '/gold-price-per-gram-sri-lanka',
            '/gold-price-per-8-grams-sri-lanka',
            '/gold-price-history',
            '/data-sources',
            '/gold-price-widget',
            '/gold-price-widget/how-to-use',
            '/gold-price-api',
        ];

        $repo = new PriceRepository();
        foreach ($repo->historyYears() as $year) {
            $urls[] = "/gold-price-history/{$year}";
            for ($m = 1; $m <= 12; $m++) {
                $urls[] = sprintf('/gold-price-history/%d/%02d', $year, $m);
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $url) {
            $xml .= '  <url><loc>' . htmlspecialchars(self::siteUrl($url), ENT_XML1) . '</loc></url>' . "\n";
        }
        $xml .= '</urlset>';

        return $xml;
    }

    public static function robotsTxt(): string
    {
        return "User-agent: *\nAllow: /\nDisallow: /admin\nSitemap: " . self::siteUrl('/sitemap.xml') . "\n";
    }
}
