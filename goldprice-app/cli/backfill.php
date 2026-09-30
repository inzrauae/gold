<?php

require __DIR__ . '/../bootstrap.php';

use App\Services\Backfill;

$options = getopt('', ['from:', 'to:', 'source::']);
$from = $options['from'] ?? null;
$to = $options['to'] ?? null;
$source = $options['source'] ?? 'metalpriceapi';

if (!$from || !$to) {
    fwrite(STDERR, "Usage: php cli/backfill.php --from=YYYY-MM-DD --to=YYYY-MM-DD [--source=metalpriceapi|xaus_com]\n");
    exit(1);
}

$backfill = new Backfill();
$result = $source === 'xaus_com' ? $backfill->runFromXaus($from, $to) : $backfill->run($from, $to);

if (!$result['ok']) {
    fwrite(STDERR, $result['error'] . "\n");
    exit(1);
}

echo "Imported {$result['imported']} day(s).\n";
foreach ($result['errors'] as $error) {
    fwrite(STDERR, "Warning: {$error}\n");
}
