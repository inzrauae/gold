<?php

namespace App\Services;

use App\Core\Database;
use App\Providers\HttpClient;

/**
 * Pulls a handful of gold-market headlines for the homepage news panel. Best
 * effort only: a failure here never affects price publication.
 */
class NewsService
{
    private const FEED_URL = 'https://news.google.com/rss/search?q=gold%20price%20sri%20lanka&hl=en-LK&gl=LK&ceid=LK:en';

    public function fetch(): array
    {
        $ch = curl_init(self::FEED_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_USERAGENT => 'GoldPriceTodaySriLanka/1.0',
        ]);
        $body = curl_exec($ch);
        curl_close($ch);

        if (!$body) {
            return ['ok' => false, 'inserted' => 0];
        }

        $prev = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($body);
        libxml_use_internal_errors($prev);

        if ($xml === false || !isset($xml->channel->item)) {
            return ['ok' => false, 'inserted' => 0];
        }

        $pdo = Database::connection();
        $inserted = 0;
        $count = 0;

        foreach ($xml->channel->item as $item) {
            if ($count++ >= 8) {
                break;
            }
            $title = trim((string) $item->title);
            $url = trim((string) $item->link);
            $pubDate = (string) $item->pubDate;
            $publishedAt = $pubDate ? date('Y-m-d H:i:s', strtotime($pubDate)) : null;

            if ($title === '' || $url === '') {
                continue;
            }

            $exists = $pdo->prepare('SELECT id FROM news_items WHERE url = ?');
            $exists->execute([$url]);
            if ($exists->fetch()) {
                continue;
            }

            $stmt = $pdo->prepare('INSERT INTO news_items (title, url, source, published_at) VALUES (?, ?, ?, ?)');
            $stmt->execute([$title, $url, 'Google News', $publishedAt]);
            $inserted++;
        }

        return ['ok' => true, 'inserted' => $inserted];
    }

    public function recent(int $limit = 6): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM news_items ORDER BY COALESCE(published_at, created_at) DESC LIMIT ?');
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
