<?php
require_once __DIR__ . '/../config/app.php';
require_role('admin');

$query = trim((string) ($_GET['q'] ?? ''));
$status = (string) ($_GET['status'] ?? '');
$allowedStatuses = ['', 'Menunggu', 'Diproses', 'Selesai'];
if (!in_array($status, $allowedStatuses, true)) {
    $status = '';
}
$conditions = [];
$parameters = [];
if ($query !== '') {
    $conditions[] = '(p.judul LIKE ? OR u.nama LIKE ? OR f.nama_fasilitas LIKE ? OR f.lokasi LIKE ?)';
    $needle = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $query) . '%';
    array_push($parameters, $needle, $needle, $needle, $needle);
}
if ($status !== '') {
    $conditions[] = 'p.status = ?';
    $parameters[] = $status;
}
$sql = 'SELECT p.id_pengaduan, p.judul, p.tanggal, p.status, u.nama, u.kelas, f.nama_fasilitas, f.lokasi FROM pengaduan p JOIN users u ON u.id_user = p.id_user JOIN fasilitas f ON f.id_fasilitas = p.id_fasilitas';
if ($conditions) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}
$sql .= ' ORDER BY p.tanggal DESC';
$statement = db()->prepare($sql);
$statement->execute($parameters);
$reports = $statement->fetchAll();
$pageTitle = 'Pengaduan';
$activeNav = 'pengaduan';
require __DIR__ . '/../includes/header.php';
?>
<section class="section-block section-first">
    <div class="section-heading"><div><span class="eyebrow">PEMANTAUAN LAPORAN</span><h2>Semua pengaduan</h2></div><span class="result-count"><?= count($reports) ?> laporan</span></div>
    <form class="filter-bar" method="get">
        <label class="search-field"><span class="visually-hidden">Cari pengaduan</span><span aria-hidden="true">⌕</span><input type="search" name="q" value="<?= e($query) ?>" placeholder="Cari nama, fasilitas, lokasi, atau judul"></label>
        <label class="visually-hidden" for="status-filter">Filter status</label><select id="status-filter" name="status"><option value="">Semua status</option><?php foreach (['Menunggu', 'Diproses', 'Selesai'] as $option): ?><option value="<?= e($option) ?>" <?= $status === $option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select>
        <button class="button button-dark" type="submit">Cari</button>
    </form>
    <?php if (!$reports): ?><div class="empty-state"><span class="empty-icon">⌕</span><h3><?= $query !== '' || $status !== '' ? 'Tidak ada hasil yang cocok' : 'Belum ada pengaduan' ?></h3><p><?= $query !== '' || $status !== '' ? 'Coba ubah kata kunci atau filter status.' : 'Laporan siswa akan muncul setelah dikirim.' ?></p></div>
    <?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Pelapor</th><th>Judul laporan</th><th>Fasilitas / lokasi</th><th>Tanggal</th><th>Status</th><th></th></tr></thead><tbody><?php foreach ($reports as $report): ?><tr><td><span class="table-primary"><?= e($report['nama']) ?></span><small class="table-secondary"><?= e($report['kelas']) ?></small></td><td><?= e($report['judul']) ?></td><td><span class="table-primary"><?= e($report['nama_fasilitas']) ?></span><small class="table-secondary"><?= e($report['lokasi']) ?></small></td><td><?= e(format_date($report['tanggal'], true)) ?></td><td><span class="status status-<?= e(strtolower(str_replace(' ', '-', $report['status']))) ?>"><?= e($report['status']) ?></span></td><td><a class="row-arrow" href="<?= e(app_url('admin/detail_pengaduan.php?id=' . (int) $report['id_pengaduan'])) ?>" aria-label="Lihat detail">→</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>