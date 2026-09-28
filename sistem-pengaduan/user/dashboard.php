<?php
require_once __DIR__ . '/../config/app.php';
require_role('user');

$statement = db()->prepare("SELECT COUNT(*) AS total, SUM(status = 'Menunggu') AS menunggu, SUM(status = 'Diproses') AS diproses, SUM(status = 'Selesai') AS selesai FROM pengaduan WHERE id_user = ?");
$statement->execute([current_account_id()]);
$stats = $statement->fetch();
$statement = db()->prepare('SELECT p.id_pengaduan, p.judul, p.tanggal, p.status, f.nama_fasilitas, f.lokasi FROM pengaduan p JOIN fasilitas f ON f.id_fasilitas = p.id_fasilitas WHERE p.id_user = ? ORDER BY p.tanggal DESC LIMIT 6');
$statement->execute([current_account_id()]);
$reports = $statement->fetchAll();
$pageTitle = 'Beranda';
$activeNav = 'dashboard';
require __DIR__ . '/../includes/header.php';
?>
<section class="welcome-band">
    <div><span class="eyebrow">HALO, <?= e(strtoupper($_SESSION['name'] ?? 'SISWA')) ?></span><h2>Hal kecil yang dilaporkan,<br><em>membuat sekolah lebih baik.</em></h2><p>Pantau kabar terbaru dari laporan fasilitasmu.</p></div>
    <a class="button button-light" href="<?= e(app_url('user/tambah_pengaduan.php')) ?>"><span aria-hidden="true">+</span> Buat pengaduan</a>
    <span class="welcome-number" aria-hidden="true">01</span>
</section>
<section class="stats-grid stats-three" aria-label="Ringkasan laporan">
    <article class="stat-card stat-total"><div class="stat-head"><span>Total laporan</span><span class="stat-symbol">▤</span></div><strong><?= (int) ($stats['total'] ?? 0) ?></strong><small>Semua laporan yang kamu kirim</small></article>
    <article class="stat-card stat-progress"><div class="stat-head"><span>Sedang diproses</span><span class="stat-symbol">↻</span></div><strong><?= (int) ($stats['diproses'] ?? 0) ?></strong><small>Dalam penanganan sekolah</small></article>
    <article class="stat-card stat-done"><div class="stat-head"><span>Selesai</span><span class="stat-symbol">✓</span></div><strong><?= (int) ($stats['selesai'] ?? 0) ?></strong><small>Sudah ditindaklanjuti</small></article>
</section>
<section class="section-block">
    <div class="section-heading"><div><span class="eyebrow">AKTIVITAS TERBARU</span><h2>Riwayat pengaduan</h2></div><a class="text-link" href="<?= e(app_url('user/riwayat.php')) ?>">Lihat semua <span aria-hidden="true">→</span></a></div>
    <?php if (!$reports): ?>
        <div class="empty-state"><span class="empty-icon">▤</span><h3>Belum ada laporan</h3><p>Kalau ada fasilitas yang perlu diperbaiki, sampaikan melalui formulir pengaduan.</p><a class="button button-primary" href="<?= e(app_url('user/tambah_pengaduan.php')) ?>">Buat laporan pertama</a></div>
    <?php else: ?>
        <div class="table-wrap"><table class="data-table"><thead><tr><th>Judul laporan</th><th>Fasilitas</th><th>Tanggal</th><th>Status</th><th></th></tr></thead><tbody>
        <?php foreach ($reports as $report): ?><tr><td><a class="table-primary" href="<?= e(app_url('user/detail_pengaduan.php?id=' . (int) $report['id_pengaduan'])) ?>"><?= e($report['judul']) ?></a><small class="table-secondary"><?= e($report['lokasi']) ?></small></td><td><?= e($report['nama_fasilitas']) ?></td><td><?= e(format_date($report['tanggal'])) ?></td><td><span class="status status-<?= e(strtolower(str_replace(' ', '-', $report['status']))) ?>"><?= e($report['status']) ?></span></td><td><a class="row-arrow" href="<?= e(app_url('user/detail_pengaduan.php?id=' . (int) $report['id_pengaduan'])) ?>" aria-label="Lihat detail">→</a></td></tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>