<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Env;

class RssFeed
{
    public function path(): string
    {
        return APP_DIR . '/storage/feeds/gold-price.xml';
    }

    public function regenerate(): void
    {
        $stmt = Database::connection()->query('SELECT * FROM prices ORDER BY id DESC LIMIT 30');
        $rows = $stmt->fetchAll();

        $siteUrl = rtrim(Env::get('APP_URL', ''), '/');
        $siteName = Env::get('SITE_NAME', 'Gold Price Today Sri Lanka');

        $items = '';
        foreach ($rows as $row) {
            $pubDate = date(DATE_RSS, strtotime($row['created_at']));
            $title = sprintf(
                '22K gold: LKR %s / g - 24K: LKR %s / g',
                number_format((float) $row['price_22k_gram'], 2),
                number_format((float) $row['price_24k_gram'], 2)
            );
            $link = $siteUrl . '/gold-price-today-sri-lanka';
            $guid = $link . '#' . $row['id'];
            $description = htmlspecialchars(sprintf(
                '24K: LKR %s | 22K: LKR %s | 21K: LKR %s | 18K: LKR %s per gram. Spot US$%s/oz, USD/LKR %s. (%s)',
                number_format((float) $row['price_24k_gram'], 2),
                number_format((float) $row['price_22k_gram'], 2),
                number_format((float) $row['price_21k_gram'], 2),
                number_format((float) $row['price_18k_gram'], 2),
                number_format((float) $row['spot_usd_per_oz'], 2),
                number_format((float) $row['usd_lkr'], 2),
                $row['verification_mode']
            ), ENT_QUOTES | ENT_XML1, 'UTF-8');

            $items .= <<<XML
                <item>
                    <title>{$this->esc($title)}</title>
                    <link>{$this->esc($link)}</link>
                    <guid isPermaLink="false">{$this->esc($guid)}</guid>
                    <pubDate>{$pubDate}</pubDate>
                    <description>{$description}</description>
                </item>

                XML;
        }

        $xml = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <rss version="2.0">
            <channel>
                <title>{$this->esc($siteName)}</title>
                <link>{$this->esc($siteUrl)}</link>
                <description>Verified, indicative gold prices for Sri Lanka (24K, 22K, 21K, 18K)</description>
                <language>en-lk</language>
                <lastBuildDate>{$this->esc(date(DATE_RSS))}</lastBuildDate>
            {$items}</channel>
            </rss>

            XML;

        $dir = dirname($this->path());
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($this->path(), $xml, LOCK_EX);
    }

    public function content(): string
    {
        if (!is_file($this->path())) {
            $this->regenerate();
        }
        return file_get_contents($this->path()) ?: '';
    }

    private function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
