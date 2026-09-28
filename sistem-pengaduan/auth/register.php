<?php
require_once __DIR__ . '/../config/app.php';

if (current_role() !== null) {
    redirect(current_role() === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php');
}

$errors = [];
$values = ['nama' => '', 'username' => '', 'kelas' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($values as $key => $_value) {
        $values[$key] = trim((string) ($_POST[$key] ?? ''));
    }
    $password = (string) ($_POST['password'] ?? '');
    $confirmation = (string) ($_POST['password_confirmation'] ?? '');

    if ($values['nama'] === '' || mb_strlen($values['nama']) > 100) {
        $errors[] = 'Nama wajib diisi (maksimal 100 karakter).';
    }
    if (!preg_match('/^[a-zA-Z0-9._-]{3,50}$/', $values['username'])) {
        $errors[] = 'Username harus 3-50 karakter dan hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.';
    }
    if ($values['kelas'] === '' || mb_strlen($values['kelas']) > 50) {
        $errors[] = 'Kelas wajib diisi (maksimal 50 karakter).';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    }
    if ($password !== $confirmation) {
        $errors[] = 'Konfirmasi password tidak sama.';
    }

    if (!$errors) {
        try {
            $statement = db()->prepare('INSERT INTO users (nama, username, password, kelas) VALUES (?, ?, ?, ?)');
            $statement->execute([$values['nama'], $values['username'], password_hash($password, PASSWORD_DEFAULT), $values['kelas']]);
            set_flash('success', 'Akun berhasil dibuat. Silakan masuk.');
            redirect('auth/login.php');
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $errors[] = 'Username tersebut sudah digunakan.';
            } else {
                throw $exception;
            }
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar · Ruang Lapor</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(app_url('assets/css/style.css')) ?>">
</head>
<body class="auth-body">
<main class="auth-layout">
    <section class="auth-story">
        <a class="brand brand-light" href="<?= e(app_url('index.php')) ?>"><span class="brand-mark">rl</span><span><strong>ruanglapor</strong><small>Fasilitas sekolah</small></span></a>
        <div class="story-copy"><span class="story-kicker"><span></span> SATU LAPORAN BERARTI</span><h1>Perubahan baik<br>butuh <em>suara kita.</em></h1><p>Buat akun sekolah untuk menyampaikan masalah fasilitas dengan jelas dan memantau tindak lanjutnya.</p></div>
        <div class="story-foot"><span>02 / 03</span><span class="story-line"></span><span>Kolaborasi warga sekolah</span></div>
        <div class="story-shape shape-one"></div><div class="story-shape shape-two"></div>
    </section>
    <section class="auth-panel"><div class="auth-box auth-box-wide"><span class="eyebrow">AKUN SISWA</span><h2>Buat akun baru</h2><p class="auth-intro">Isi identitas sekolahmu untuk mulai melapor.</p>
        <?php if ($errors): ?><div class="alert alert-error" role="alert"><ul class="error-list"><?php foreach ($errors as $message): ?><li><?= e($message) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <form method="post" class="form-stack">
            <?= csrf_field() ?>
            <label class="field"><span>Nama lengkap</span><input type="text" name="nama" value="<?= e($values['nama']) ?>" maxlength="100" autocomplete="name" required></label>
            <div class="field-row"><label class="field"><span>Username</span><input type="text" name="username" value="<?= e($values['username']) ?>" maxlength="50" autocomplete="username" required></label><label class="field"><span>Kelas</span><input type="text" name="kelas" value="<?= e($values['kelas']) ?>" maxlength="50" placeholder="Contoh: XI RPL 1" required></label></div>
            <label class="field"><span>Password</span><input type="password" name="password" minlength="8" autocomplete="new-password" required><small>Gunakan minimal 8 karakter.</small></label>
            <label class="field"><span>Ulangi password</span><input type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required></label>
            <button class="button button-primary button-wide" type="submit">Buat akun <span aria-hidden="true">→</span></button>
        </form>
        <p class="auth-switch">Sudah punya akun? <a href="<?= e(app_url('auth/login.php')) ?>">Masuk</a></p>
    </div><div class="auth-panel-foot">Akses khusus warga sekolah <span>·</span> <?= e(date('Y')) ?></div></section>
</main>
<script src="<?= e(app_url('assets/js/script.js')) ?>" defer></script>
</body>
</html>