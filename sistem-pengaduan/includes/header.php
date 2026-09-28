<?php
$role = current_role();
$pageTitle = $pageTitle ?? 'Ruang Lapor';
$activeNav = $activeNav ?? '';
$navItems = $role === 'admin'
    ? [
        ['dashboard', 'Ringkasan', 'admin/dashboard.php', '⌂'],
        ['pengaduan', 'Pengaduan', 'admin/pengaduan.php', '▤'],
        ['fasilitas', 'Fasilitas', 'admin/fasilitas.php', '⌑'],
    ]
    : [
        ['dashboard', 'Beranda', 'user/dashboard.php', '⌂'],
        ['riwayat', 'Riwayat laporan', 'user/riwayat.php', '▤'],
        ['buat', 'Buat pengaduan', 'user/tambah_pengaduan.php', '+'],
    ];
$flash = get_flash();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6f5f0">
    <title><?= e($pageTitle) ?> · Ruang Lapor</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(app_url('assets/css/style.css')) ?>">
</head>
<body class="app-body">
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="<?= e(app_url($role === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php')) ?>">
            <span class="brand-mark">rl</span>
            <span><strong>ruanglapor</strong><small>Fasilitas sekolah</small></span>
        </a>
        <div class="nav-caption">MENU UTAMA</div>
        <nav class="side-nav" aria-label="Navigasi utama">
            <?php foreach ($navItems as [$key, $label, $url, $icon]): ?>
                <a class="nav-link <?= $activeNav === $key ? 'is-active' : '' ?>" href="<?= e(app_url($url)) ?>">
                    <span class="nav-icon" aria-hidden="true"><?= e($icon) ?></span>
                    <span><?= e($label) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-note">
            <span class="note-dot"></span>
            <span>Portal aspirasi<br><strong>sekolah lebih baik</strong></span>
        </div>
        <div class="sidebar-user">
            <span class="avatar"><?= e(strtoupper(substr($_SESSION['name'] ?? 'A', 0, 1))) ?></span>
            <span class="user-meta"><strong><?= e($_SESSION['name'] ?? 'Pengguna') ?></strong><small><?= $role === 'admin' ? 'Administrator' : e($_SESSION['class'] ?? 'Siswa') ?></small></span>
            <form action="<?= e(app_url('auth/logout.php')) ?>" method="post" class="logout-form">
                <?= csrf_field() ?>
                <button class="icon-button" type="submit" aria-label="Keluar" title="Keluar">↗</button>
            </form>
        </div>
    </aside>
    <main class="main-area">
        <header class="topbar">
            <div><span class="eyebrow"><?= $role === 'admin' ? 'PANEL ADMINISTRATOR' : 'PORTAL SEKOLAH' ?></span><h1><?= e($pageTitle) ?></h1></div>
            <div class="topbar-date"><?= e(date('l, d F Y')) ?></div>
            <form class="mobile-logout" action="<?= e(app_url('auth/logout.php')) ?>" method="post">
                <?= csrf_field() ?>
                <button class="icon-button" type="submit" aria-label="Keluar" title="Keluar">↗</button>
            </form>
        </header>
        <div class="page-content">
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
            <?php endif; ?>