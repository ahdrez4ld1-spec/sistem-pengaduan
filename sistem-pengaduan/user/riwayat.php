<?php
require_once __DIR__ . '/../config/app.php';
require_role('user');

$query = trim((string) ($_GET['q'] ?? ''));
$status = (string) ($_GET['status'] ?? '');
$allowedStatuses = ['', 'Menunggu', 'Diproses', 'Selesai'];
if (!in_array($status, $allowedStatuses, true)) {
    $status = '';
}
$conditions = ['p.id_user = ?'];
$parameters = [current_account_id()];
if ($query !== '') {
    $conditions[] = '(p.judul LIKE ? OR f.nama_fasilitas LIKE ? OR f.lokasi LIKE ?)';
    $needle = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $query) . '%';
    array_push($parameters, $needle, $needle, $needle);
}
if ($status !== '') {
    $conditions[] = 'p.status = ?';
    $parameters[] = $status;
}
$sql = 'SELECT p.id_pengaduan, p.judul, p.tanggal, p.status, f.nama_fasilitas, f.lokasi FROM pengaduan p JOIN fasilitas f ON f.id_fasilitas = p.id_fasilitas WHERE ' . implode(' AND ', $conditions) . ' ORDER BY p.tanggal DESC';
$statement = db()->prepare($sql);
$statement->execute($parameters);
$reports = $statement->fetchAll();
$pageTitle = 'Riwayat laporan';
$activeNav = 'riwayat';
require __DIR__ . '/../includes/header.php';
?>
<section class="section-block section-first">
    <div class="section-heading"><div><span class="eyebrow">SEMUA AKTIVITASMU</span><h2>Perjalanan setiap laporan</h2></div><a class="button button-primary" href="<?= e(app_url('user/tambah_pengaduan.php')) ?>"><span aria-hidden="true">+</span> Buat pengaduan</a></div>
    <form class="filter-bar" method="get">
        <label class="search-field"><span class="visually-hidden">Cari laporan</span><span aria-hidden="true">⌕</span><input type="search" name="q" value="<?= e($query) ?>" placeholder="Cari judul, fasilitas, atau lokasi"></label>
        <label class="visually-hidden" for="status-filter">Filter status</label><select id="status-filter" name="status"><option value="">Semua status</option><?php foreach (['Menunggu', 'Diproses', 'Selesai'] as $option): ?><option value="<?= e($option) ?>" <?= $status === $option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select>
        <button class="button button-dark" type="submit">Cari</button>
    </form>
    <?php if (!$reports): ?><div class="empty-state"><span class="empty-icon">⌕</span><h3><?= $query !== '' || $status !== '' ? 'Tidak ada hasil yang cocok' : 'Belum ada laporan' ?></h3><p><?= $query !== '' || $status !== '' ? 'Coba ubah kata kunci atau filter status.' : 'Laporan yang kamu kirim akan muncul di halaman ini.' ?></p></div>
    <?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Judul laporan</th><th>Fasilitas</th><th>Tanggal</th><th>Status</th><th></th></tr></thead><tbody><?php foreach ($reports as $report): ?><tr><td><a class="table-primary" href="<?= e(app_url('user/detail_pengaduan.php?id=' . (int) $report['id_pengaduan'])) ?>"><?= e($report['judul']) ?></a><small class="table-secondary"><?= e($report['lokasi']) ?></small></td><td><?= e($report['nama_fasilitas']) ?></td><td><?= e(format_date($report['tanggal'], true)) ?></td><td><span class="status status-<?= e(strtolower(str_replace(' ', '-', $report['status']))) ?>"><?= e($report['status']) ?></span></td><td><a class="row-arrow" href="<?= e(app_url('user/detail_pengaduan.php?id=' . (int) $report['id_pengaduan'])) ?>" aria-label="Lihat detail">→</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>