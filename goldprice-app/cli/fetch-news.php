<?php

require __DIR__ . '/../bootstrap.php';

use App\Services\NewsService;

$result = (new NewsService())->fetch();
echo $result['ok'] ? "Fetched news: {$result['inserted']} new item(s).\n" : "News fetch failed.\n";
