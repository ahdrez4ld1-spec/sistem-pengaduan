<?php
require_once __DIR__ . '/../config/app.php';

if (current_role() !== null) {
    redirect(current_role() === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php');
}

$error = '';
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username !== '' && $password !== '') {
        $statement = db()->prepare('SELECT id_admin AS account_id, username, password FROM admin WHERE username = ?');
        $statement->execute([$username]);
        $account = $statement->fetch();
        $role = 'admin';

        if (!$account) {
            $statement = db()->prepare('SELECT id_user AS account_id, nama, username, kelas, password FROM users WHERE username = ?');
            $statement->execute([$username]);
            $account = $statement->fetch();
            $role = 'user';
        }

        if ($account && password_verify($password, $account['password'])) {
            session_regenerate_id(true);
            $_SESSION['account_id'] = (int) $account['account_id'];
            $_SESSION['role'] = $role;
            $_SESSION['name'] = $role === 'admin' ? $account['username'] : $account['nama'];
            $_SESSION['class'] = $role === 'user' ? $account['kelas'] : '';
            redirect($role === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php');
        }
    }

    $error = 'Username atau password tidak sesuai.';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · Ruang Lapor</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(app_url('assets/css/style.css')) ?>">
</head>
<body class="auth-body">
<main class="auth-layout">
    <section class="auth-story">
        <a class="brand brand-light" href="<?= e(app_url('index.php')) ?>"><span class="brand-mark">rl</span><span><strong>ruanglapor</strong><small>Fasilitas sekolah</small></span></a>
        <div class="story-copy"><span class="story-kicker"><span></span> DENGAR. TANGGAP. BENAHI.</span><h1>Ruang belajar<br>yang lebih baik<br><em>dimulai dari sini.</em></h1><p>Laporkan kerusakan fasilitas sekolah dan ikuti perkembangannya, dari laporan pertama sampai selesai diperbaiki.</p></div>
        <div class="story-foot"><span>01 / 03</span><span class="story-line"></span><span>Kolaborasi warga sekolah</span></div>
        <div class="story-shape shape-one"></div><div class="story-shape shape-two"></div>
    </section>
    <section class="auth-panel">
        <div class="auth-box"><span class="eyebrow">SELAMAT DATANG KEMBALI</span><h2>Masuk ke akun</h2><p class="auth-intro">Gunakan akun sekolah untuk melanjutkan.</p>
            <?php if ($error !== ''): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
            <form method="post" class="form-stack">
                <?= csrf_field() ?>
                <label class="field"><span>Username</span><input type="text" name="username" value="<?= e($username) ?>" autocomplete="username" required autofocus></label>
                <label class="field"><span>Password</span><input type="password" name="password" autocomplete="current-password" required></label>
                <button class="button button-primary button-wide" type="submit">Masuk <span aria-hidden="true">→</span></button>
            </form>
            <p class="auth-switch">Belum punya akun? <a href="<?= e(app_url('auth/register.php')) ?>">Daftar sebagai siswa</a></p>
        </div>
        <div class="auth-panel-foot">Akses khusus warga sekolah <span>·</span> <?= e(date('Y')) ?></div>
    </section>
</main>
<script src="<?= e(app_url('assets/js/script.js')) ?>" defer></script>
</body>
</html>