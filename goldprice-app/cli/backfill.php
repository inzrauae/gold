<?php

require __DIR__ . '/../bootstrap.php';

use App\Services\Backfill;

$options = getopt('', ['from:', 'to:']);
$from = $options['from'] ?? null;
$to = $options['to'] ?? null;

if (!$from || !$to) {
    fwrite(STDERR, "Usage: php cli/backfill.php --from=YYYY-MM-DD --to=YYYY-MM-DD\n");
    exit(1);
}

$result = (new Backfill())->run($from, $to);

if (!$result['ok']) {
    fwrite(STDERR, $result['error'] . "\n");
    exit(1);
}

echo "Imported {$result['imported']} day(s).\n";
foreach ($result['errors'] as $error) {
    fwrite(STDERR, "Warning: {$error}\n");
}
