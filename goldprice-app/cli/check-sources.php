<?php

require __DIR__ . '/../bootstrap.php';

use App\Services\ProviderGateway;

$gateway = new ProviderGateway();

$checks = [
    'Gold primary' => $gateway->goldPrimary(),
    'Gold secondary' => $gateway->goldSecondary(),
    'FX primary' => $gateway->fxPrimary(),
    'FX secondary' => $gateway->fxSecondary(),
];

$allOk = true;
foreach ($checks as $label => $reading) {
    if ($reading['ok']) {
        printf("%-16s OK   %s = %s %s (age %ds)\n", $label, $reading['source'], $reading['value'], $reading['currency'], time() - $reading['timestamp']);
    } else {
        $allOk = false;
        printf("%-16s FAIL %s: %s\n", $label, $reading['source'], $reading['error']);
    }
}

exit($allOk ? 0 : 1);
