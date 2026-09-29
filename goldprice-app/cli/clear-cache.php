<?php

require __DIR__ . '/../bootstrap.php';

use App\Core\Cache;

Cache::clear();
echo "Cache cleared.\n";
