<?php

require __DIR__ . '/../bootstrap.php';

use App\Core\Database;
use App\Core\Env;

$driver = Database::driver();
$suffix = $driver === 'mysql' ? 'mysql' : 'sqlite';

if ($driver === 'sqlite') {
    $path = Env::get('DB_SQLITE_PATH', 'storage/database.sqlite');
    if (!preg_match('/^([A-Za-z]:[\\\\\/]|\/)/', $path)) {
        $path = APP_DIR . '/' . $path;
    }
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

$pdo = Database::connection();

$migrationsDir = APP_DIR . '/database/migrations';
$files = glob($migrationsDir . "/*_{$suffix}.sql") ?: [];
sort($files);

if (empty($files)) {
    fwrite(STDERR, "No {$suffix} migrations found in {$migrationsDir}\n");
    exit(1);
}

foreach ($files as $file) {
    $name = basename($file, "_{$suffix}.sql");
    $sql = file_get_contents($file);

    if ($driver === 'sqlite') {
        $pdo->exec($sql);
    } else {
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
            $pdo->exec($statement);
        }
    }

    echo "Applied: {$name}\n";
}

echo "Migration complete.\n";
