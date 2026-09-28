<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

date_default_timezone_set('Asia/Jakarta');

if (session_status() !== PHP_SESSION_ACTIVE) {
    $isSecure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_name('pengaduan_session');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $isSecure,
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

$scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$scriptDirectory = preg_replace('~/(admin|user|auth)$~', '', $scriptDirectory) ?? $scriptDirectory;
define('APP_BASE_PATH', rtrim($scriptDirectory, '/'));

function app_url(string $path = ''): string
{
    return APP_BASE_PATH . '/' . ltrim($path, '/');
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . app_url($path));
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (!is_string($submitted) || !hash_equals(csrf_token(), $submitted)) {
        http_response_code(419);
        exit('Sesi formulir kedaluwarsa. Muat ulang halaman, lalu coba lagi.');
    }
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($flash) ? $flash : null;
}

function require_role(?string $role = null): void
{
    if (empty($_SESSION['role']) || empty($_SESSION['account_id'])) {
        redirect('auth/login.php');
    }

    if ($role !== null && $_SESSION['role'] !== $role) {
        redirect($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php');
    }
}

function current_role(): ?string
{
    return $_SESSION['role'] ?? null;
}

function current_account_id(): int
{
    return (int) ($_SESSION['account_id'] ?? 0);
}

function format_date(?string $date, bool $withTime = false): string
{
    if (!$date) {
        return '-';
    }

    $format = $withTime ? 'd M Y, H:i' : 'd M Y';
    return date($format, strtotime($date));
}