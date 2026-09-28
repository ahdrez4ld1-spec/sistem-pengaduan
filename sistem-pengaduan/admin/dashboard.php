<?php
require_once __DIR__ . '/../config/app.php';
require_role('admin');

$stats = db()->query("SELECT COUNT(*) AS total, SUM(status = 'Menunggu') AS menunggu, SUM(status = 'Diproses') AS diproses, SUM(status = 'Selesai') AS selesai FROM pengaduan")->fetch();
$reports = db()->query('SELECT p.id_pengaduan, p.judul, p.tanggal, p.status, u.nama, u.kelas, f.nama_fasilitas, f.lokasi FROM pengaduan p JOIN users u ON u.id_user = p.id_user JOIN fasilitas f ON f.id_fasilitas = p.id_fasilitas ORDER BY p.tanggal DESC LIMIT 8')->fetchAll();
$pageTitle = 'Ringkasan';
$activeNav = 'dashboard';
require __DIR__ . '/../includes/header.php';
?>
<section class="admin-welcome"><div><span class="eyebrow">RUANG KENDALI</span><h2>Setiap laporan adalah<br><em>langkah menuju perubahan.</em></h2><p>Berikut kondisi terbaru fasilitas yang dilaporkan warga sekolah.</p></div><div class="admin-stamp"><span>HARI INI</span><strong><?= e(date('d')) ?></strong><small><?= e(date('M Y')) ?></small></div></section>
<section class="stats-grid stats-four" aria-label="Ringkasan semua laporan">
    <article class="stat-card stat-total"><div class="stat-head"><span>Total laporan</span><span class="stat-symbol">▤</span></div><strong><?= (int) ($stats['total'] ?? 0) ?></strong><small>Seluruh laporan masuk</small></article>
    <article class="stat-card stat-waiting"><div class="stat-head"><span>Menunggu</span><span class="stat-symbol">◷</span></div><strong><?= (int) ($stats['menunggu'] ?? 0) ?></strong><small>Perlu ditinjau</small></article>
    <article class="stat-card stat-progress"><div class="stat-head"><span>Diproses</span><span class="stat-symbol">↻</span></div><strong><?= (int) ($stats['diproses'] ?? 0) ?></strong><small>Sedang ditangani</small></article>
    <article class="stat-card stat-done"><div class="stat-head"><span>Selesai</span><span class="stat-symbol">✓</span></div><strong><?= (int) ($stats['selesai'] ?? 0) ?></strong><small>Sudah dituntaskan</small></article>
</section>
<section class="section-block">
    <div class="section-heading"><div><span class="eyebrow">LAPORAN MASUK</span><h2>Perlu perhatian terbaru</h2></div><a class="text-link" href="<?= e(app_url('admin/pengaduan.php')) ?>">Kelola semua <span aria-hidden="true">→</span></a></div>
    <?php if (!$reports): ?><div class="empty-state"><span class="empty-icon">✓</span><h3>Belum ada laporan masuk</h3><p>Laporan baru dari siswa akan tampil di sini.</p></div>
    <?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Pelapor</th><th>Judul laporan</th><th>Fasilitas</th><th>Tanggal</th><th>Status</th><th></th></tr></thead><tbody><?php foreach ($reports as $report): ?><tr><td><span class="table-primary"><?= e($report['nama']) ?></span><small class="table-secondary"><?= e($report['kelas']) ?></small></td><td><?= e($report['judul']) ?></td><td><span class="table-primary"><?= e($report['nama_fasilitas']) ?></span><small class="table-secondary"><?= e($report['lokasi']) ?></small></td><td><?= e(format_date($report['tanggal'])) ?></td><td><span class="status status-<?= e(strtolower(str_replace(' ', '-', $report['status']))) ?>"><?= e($report['status']) ?></span></td><td><a class="row-arrow" href="<?= e(app_url('admin/detail_pengaduan.php?id=' . (int) $report['id_pengaduan'])) ?>" aria-label="Lihat detail">→</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>