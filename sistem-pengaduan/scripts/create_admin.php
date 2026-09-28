<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../config/database.php';

$username = trim((string) readline('Username admin (3-50 karakter): '));
if (!preg_match('/^[a-zA-Z0-9._-]{3,50}$/', $username)) {
    fwrite(STDERR, "Username tidak valid.\n");
    exit(1);
}

$password = (string) readline('Password admin (minimal 12 karakter): ');
$confirmation = (string) readline('Ulangi password: ');
if (strlen($password) < 12 || !hash_equals($password, $confirmation)) {
    fwrite(STDERR, "Password minimal 12 karakter dan harus sama dengan konfirmasi.\n");
    exit(1);
}

try {
    $statement = db()->prepare('INSERT INTO admin (username, password) VALUES (?, ?)');
    $statement->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
    fwrite(STDOUT, "Akun admin berhasil dibuat.\n");
} catch (PDOException $exception) {
    if ($exception->getCode() === '23000') {
        fwrite(STDERR, "Username admin tersebut sudah digunakan.\n");
        exit(1);
    }
    throw $exception;
}