<?php
require_once __DIR__ . '/../config/app.php';
require_role('user');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$statement = db()->prepare('SELECT p.*, f.nama_fasilitas, f.kategori, f.lokasi FROM pengaduan p JOIN fasilitas f ON f.id_fasilitas = p.id_fasilitas WHERE p.id_pengaduan = ? AND p.id_user = ?');
$statement->execute([$id ?: 0, current_account_id()]);
$report = $statement->fetch();
if (!$report) {
    http_response_code(404);
    exit('Pengaduan tidak ditemukan.');
}

$pageTitle = 'Detail pengaduan';
$activeNav = 'riwayat';
require __DIR__ . '/../includes/header.php';
?>
<div class="detail-toolbar"><a class="back-link" href="<?= e(app_url('user/riwayat.php')) ?>">← Kembali ke riwayat</a><span class="status status-<?= e(strtolower(str_replace(' ', '-', $report['status']))) ?>"><?= e($report['status']) ?></span></div>
<article class="detail-layout">
    <section class="detail-main"><span class="eyebrow">LAPORAN #<?= str_pad((string) $report['id_pengaduan'], 4, '0', STR_PAD_LEFT) ?></span><h2><?= e($report['judul']) ?></h2><p class="detail-date">Dikirim <?= e(format_date($report['tanggal'], true)) ?></p>
        <div class="detail-section"><h3>Deskripsi masalah</h3><p class="preserve-lines"><?= e($report['deskripsi']) ?></p></div>
        <?php if ($report['foto']): ?><div class="detail-section"><h3>Foto pendukung</h3><a href="<?= e(app_url('assets/uploads/' . rawurlencode($report['foto']))) ?>" target="_blank" rel="noopener"><img class="report-photo" src="<?= e(app_url('assets/uploads/' . rawurlencode($report['foto']))) ?>" alt="Foto pendukung laporan"></a></div><?php endif; ?>
    </section>
    <aside class="detail-aside"><span class="eyebrow">INFORMASI FASILITAS</span><dl class="detail-facts"><div><dt>Fasilitas</dt><dd><?= e($report['nama_fasilitas']) ?></dd></div><div><dt>Kategori</dt><dd><?= e($report['kategori']) ?></dd></div><div><dt>Lokasi</dt><dd><?= e($report['lokasi']) ?></dd></div></dl><div class="status-note"><span class="note-dot"></span><p>Status laporan akan diperbarui oleh administrator setelah ditinjau.</p></div></aside>
</article>
<?php require __DIR__ . '/../includes/footer.php'; ?>