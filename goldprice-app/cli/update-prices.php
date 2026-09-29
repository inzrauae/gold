<?php

require __DIR__ . '/../bootstrap.php';

use App\Services\PriceUpdater;

$result = (new PriceUpdater())->run();

switch ($result['status']) {
    case 'published':
        $row = $result['row'];
        echo "PUBLISHED: 24K LKR {$row['price_24k_gram']}/g, 22K LKR {$row['price_22k_gram']}/g ({$row['verification_mode']})\n";
        exit(0);
    case 'unchanged':
        echo "UNCHANGED: latest verified price still current.\n";
        exit(0);
    case 'held':
        echo "HELD: {$result['reason']} - awaiting admin review at /admin\n";
        exit(1);
    default:
        echo "FAILED: {$result['reason']}\n";
        exit(1);
}
