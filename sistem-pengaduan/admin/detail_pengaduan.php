<?php
require_once __DIR__ . '/../config/app.php';
require_role('admin');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$statement = db()->prepare('SELECT p.*, u.nama, u.username, u.kelas, f.nama_fasilitas, f.kategori, f.lokasi FROM pengaduan p JOIN users u ON u.id_user = p.id_user JOIN fasilitas f ON f.id_fasilitas = p.id_fasilitas WHERE p.id_pengaduan = ?');
$statement->execute([$id ?: 0]);
$report = $statement->fetch();
if (!$report) {
    http_response_code(404);
    exit('Pengaduan tidak ditemukan.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'status') {
        $newStatus = (string) ($_POST['status'] ?? '');
        if (!in_array($newStatus, ['Menunggu', 'Diproses', 'Selesai'], true)) {
            set_flash('error', 'Status yang dipilih tidak valid.');
        } else {
            $statement = db()->prepare('UPDATE pengaduan SET status = ? WHERE id_pengaduan = ?');
            $statement->execute([$newStatus, $report['id_pengaduan']]);
            set_flash('success', 'Status pengaduan diperbarui menjadi ' . $newStatus . '.');
        }
        redirect('admin/detail_pengaduan.php?id=' . (int) $report['id_pengaduan']);
    }
    if ($action === 'delete') {
        $statement = db()->prepare('DELETE FROM pengaduan WHERE id_pengaduan = ?');
        $statement->execute([$report['id_pengaduan']]);
        if ($report['foto']) {
            $photoPath = __DIR__ . '/../assets/uploads/' . basename($report['foto']);
            if (is_file($photoPath)) {
                unlink($photoPath);
            }
        }
        set_flash('success', 'Pengaduan berhasil dihapus.');
        redirect('admin/pengaduan.php');
    }
    set_flash('error', 'Aksi tidak dikenali.');
    redirect('admin/detail_pengaduan.php?id=' . (int) $report['id_pengaduan']);
}

$pageTitle = 'Detail pengaduan';
$activeNav = 'pengaduan';
require __DIR__ . '/../includes/header.php';
?>
<div class="detail-toolbar"><a class="back-link" href="<?= e(app_url('admin/pengaduan.php')) ?>">← Kembali ke pengaduan</a><span class="status status-<?= e(strtolower(str_replace(' ', '-', $report['status']))) ?>"><?= e($report['status']) ?></span></div>
<article class="detail-layout">
    <section class="detail-main"><span class="eyebrow">LAPORAN #<?= str_pad((string) $report['id_pengaduan'], 4, '0', STR_PAD_LEFT) ?></span><h2><?= e($report['judul']) ?></h2><p class="detail-date">Dikirim <?= e(format_date($report['tanggal'], true)) ?></p>
        <div class="detail-section"><h3>Deskripsi masalah</h3><p class="preserve-lines"><?= e($report['deskripsi']) ?></p></div>
        <?php if ($report['foto']): ?><div class="detail-section"><h3>Foto pendukung</h3><a href="<?= e(app_url('assets/uploads/' . rawurlencode($report['foto']))) ?>" target="_blank" rel="noopener"><img class="report-photo" src="<?= e(app_url('assets/uploads/' . rawurlencode($report['foto']))) ?>" alt="Foto pendukung laporan"></a></div><?php endif; ?>
        <form method="post" class="status-editor">
            <?= csrf_field() ?><input type="hidden" name="action" value="status">
            <div><label class="field" for="status">Perbarui status</label><select id="status" name="status"><?php foreach (['Menunggu', 'Diproses', 'Selesai'] as $option): ?><option value="<?= e($option) ?>" <?= $report['status'] === $option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select></div>
            <button class="button button-primary" type="submit">Simpan status</button>
        </form>
        <form method="post" class="delete-report-form" data-confirm="Hapus laporan ini secara permanen?">
            <?= csrf_field() ?><input type="hidden" name="action" value="delete"><button class="button button-danger-quiet" type="submit">Hapus pengaduan</button>
        </form>
    </section>
    <aside class="detail-aside"><span class="eyebrow">PELAPOR</span><dl class="detail-facts"><div><dt>Nama</dt><dd><?= e($report['nama']) ?></dd></div><div><dt>Username</dt><dd><?= e($report['username']) ?></dd></div><div><dt>Kelas</dt><dd><?= e($report['kelas']) ?></dd></div></dl><span class="eyebrow detail-aside-heading">INFORMASI FASILITAS</span><dl class="detail-facts"><div><dt>Fasilitas</dt><dd><?= e($report['nama_fasilitas']) ?></dd></div><div><dt>Kategori</dt><dd><?= e($report['kategori']) ?></dd></div><div><dt>Lokasi</dt><dd><?= e($report['lokasi']) ?></dd></div></dl></aside>
</article>
<?php require __DIR__ . '/../includes/footer.php'; ?>