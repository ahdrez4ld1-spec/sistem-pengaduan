<?php
declare(strict_types=1);

function db(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $localConfigPath = __DIR__ . '/database.local.php';
    $localConfig = is_file($localConfigPath) ? require $localConfigPath : [];
    if (!is_array($localConfig)) {
        $localConfig = [];
    }

    $host = getenv('DB_HOST') ?: ($localConfig['host'] ?? '127.0.0.1');
    $name = getenv('DB_NAME') ?: ($localConfig['database'] ?? 'pengaduan_sekolah');
    $user = getenv('DB_USER') ?: ($localConfig['username'] ?? 'root');
    $password = getenv('DB_PASS') ?: ($localConfig['password'] ?? '');
    $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";

    $connection = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $connection;
}