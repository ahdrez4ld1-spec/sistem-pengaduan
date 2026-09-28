<?php
require_once __DIR__ . '/../config/app.php';
require_role('admin');

$query = trim((string) ($_GET['q'] ?? ''));
$sql = 'SELECT f.*, COUNT(p.id_pengaduan) AS jumlah_laporan FROM fasilitas f LEFT JOIN pengaduan p ON p.id_fasilitas = f.id_fasilitas';
$parameters = [];
if ($query !== '') {
    $sql .= ' WHERE f.nama_fasilitas LIKE ? OR f.kategori LIKE ? OR f.lokasi LIKE ?';
    $needle = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $query) . '%';
    $parameters = [$needle, $needle, $needle];
}
$sql .= ' GROUP BY f.id_fasilitas ORDER BY f.nama_fasilitas';
$statement = db()->prepare($sql);
$statement->execute($parameters);
$facilities = $statement->fetchAll();
$pageTitle = 'Fasilitas';
$activeNav = 'fasilitas';
require __DIR__ . '/../includes/header.php';
?>
<section class="section-block section-first">
    <div class="section-heading"><div><span class="eyebrow">DATA INVENTARIS</span><h2>Fasilitas sekolah</h2></div><a class="button button-primary" href="<?= e(app_url('admin/tambah_fasilitas.php')) ?>"><span aria-hidden="true">+</span> Tambah fasilitas</a></div>
    <form class="filter-bar filter-single" method="get"><label class="search-field"><span class="visually-hidden">Cari fasilitas</span><span aria-hidden="true">⌕</span><input type="search" name="q" value="<?= e($query) ?>" placeholder="Cari nama, kategori, atau lokasi"></label><button class="button button-dark" type="submit">Cari</button></form>
    <?php if (!$facilities): ?><div class="empty-state"><span class="empty-icon">⌕</span><h3><?= $query !== '' ? 'Fasilitas tidak ditemukan' : 'Belum ada fasilitas' ?></h3><p><?= $query !== '' ? 'Coba ubah kata kunci pencarian.' : 'Tambahkan fasilitas agar siswa dapat mengirim laporan.' ?></p><?php if ($query === ''): ?><a class="button button-primary" href="<?= e(app_url('admin/tambah_fasilitas.php')) ?>">Tambah fasilitas</a><?php endif; ?></div>
    <?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Nama fasilitas</th><th>Kategori</th><th>Lokasi</th><th>Laporan</th><th>Aksi</th></tr></thead><tbody><?php foreach ($facilities as $facility): ?><tr><td><span class="table-primary"><?= e($facility['nama_fasilitas']) ?></span><?php if ($facility['keterangan']): ?><small class="table-secondary"><?= e($facility['keterangan']) ?></small><?php endif; ?></td><td><?= e($facility['kategori']) ?></td><td><?= e($facility['lokasi']) ?></td><td><span class="count-chip"><?= (int) $facility['jumlah_laporan'] ?></span></td><td><div class="table-actions"><a class="text-link" href="<?= e(app_url('admin/edit_fasilitas.php?id=' . (int) $facility['id_fasilitas'])) ?>">Ubah</a><form method="post" action="<?= e(app_url('admin/hapus_fasilitas.php')) ?>" data-confirm="Hapus fasilitas <?= e($facility['nama_fasilitas']) ?>? Fasilitas dengan riwayat laporan tidak dapat dihapus."><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $facility['id_fasilitas'] ?>"><button class="text-button text-danger" type="submit">Hapus</button></form></div></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>