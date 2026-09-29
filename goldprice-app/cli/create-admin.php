<?php

require __DIR__ . '/../bootstrap.php';

use App\Core\Database;

$options = getopt('', ['email:', 'role::']);
$email = $options['email'] ?? null;
$role = $options['role'] ?? 'admin';

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Usage: php cli/create-admin.php --email=you@example.lk [--role=admin|viewer]\n");
    exit(1);
}

if (!in_array($role, ['admin', 'viewer'], true)) {
    fwrite(STDERR, "Role must be 'admin' or 'viewer'\n");
    exit(1);
}

$password = getenv('ADMIN_PASSWORD') ?: null;

if (!$password) {
    echo 'Password (min 12 chars): ';
    $password = trim((string) fgets(STDIN));
}

if (strlen($password) < 12) {
    fwrite(STDERR, "Password must be at least 12 characters.\n");
    exit(1);
}

$pdo = Database::connection();
$hash = password_hash($password, PASSWORD_DEFAULT);

$existing = $pdo->prepare('SELECT id FROM admin_users WHERE email = ?');
$existing->execute([$email]);

if ($existing->fetch()) {
    $stmt = $pdo->prepare('UPDATE admin_users SET password_hash = ?, role = ? WHERE email = ?');
    $stmt->execute([$hash, $role, $email]);
    echo "Updated existing admin account: {$email} ({$role})\n";
} else {
    $stmt = $pdo->prepare('INSERT INTO admin_users (email, password_hash, role) VALUES (?, ?, ?)');
    $stmt->execute([$email, $hash, $role]);
    echo "Created admin account: {$email} ({$role})\n";
}
