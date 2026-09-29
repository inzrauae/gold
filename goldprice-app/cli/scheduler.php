<?php

require __DIR__ . '/../bootstrap.php';

use App\Services\Scheduler;

$result = (new Scheduler())->run();
echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
