<?php
require_once __DIR__ . '/../config/app.php';
require_role('admin');

$errors = [];
$values = ['nama_fasilitas' => '', 'kategori' => '', 'lokasi' => '', 'keterangan' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($values as $key => $_value) {
        $values[$key] = trim((string) ($_POST[$key] ?? ''));
    }
    foreach (['nama_fasilitas' => 100, 'kategori' => 100, 'lokasi' => 150] as $field => $maxLength) {
        if ($values[$field] === '' || mb_strlen($values[$field]) > $maxLength) {
            $errors[] = 'Nama fasilitas, kategori, dan lokasi wajib diisi sesuai batas karakter.';
            break;
        }
    }
    if (mb_strlen($values['keterangan']) > 5000) {
        $errors[] = 'Keterangan maksimal 5.000 karakter.';
    }
    if (!$errors) {
        $statement = db()->prepare('INSERT INTO fasilitas (nama_fasilitas, kategori, lokasi, keterangan) VALUES (?, ?, ?, ?)');
        $statement->execute([$values['nama_fasilitas'], $values['kategori'], $values['lokasi'], $values['keterangan'] ?: null]);
        set_flash('success', 'Fasilitas berhasil ditambahkan.');
        redirect('admin/fasilitas.php');
    }
}

$pageTitle = 'Tambah fasilitas';
$activeNav = 'fasilitas';
require __DIR__ . '/../includes/header.php';
?>
<div class="form-intro"><a class="back-link" href="<?= e(app_url('admin/fasilitas.php')) ?>">← Kembali ke fasilitas</a><span class="eyebrow">DATA INVENTARIS</span><h2>Tambahkan fasilitas</h2><p>Fasilitas yang tercatat dapat dipilih siswa saat membuat laporan.</p></div>
<?php if ($errors): ?><div class="alert alert-error" role="alert"><ul class="error-list"><?php foreach ($errors as $message): ?><li><?= e($message) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" class="form-panel"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="form-grid">
    <label class="field"><span>Nama fasilitas <b>*</b></span><input type="text" name="nama_fasilitas" maxlength="100" value="<?= e($values['nama_fasilitas']) ?>" required></label>
    <label class="field"><span>Kategori <b>*</b></span><input type="text" name="kategori" maxlength="100" value="<?= e($values['kategori']) ?>" placeholder="Contoh: Kelistrikan" required></label>
    <label class="field field-full"><span>Lokasi <b>*</b></span><input type="text" name="lokasi" maxlength="150" value="<?= e($values['lokasi']) ?>" placeholder="Contoh: Gedung A, lantai 2" required></label>
    <label class="field field-full"><span>Keterangan</span><textarea name="keterangan" rows="4" maxlength="5000" placeholder="Informasi tambahan, bila diperlukan"><?= e($values['keterangan']) ?></textarea></label>
</div><div class="form-actions"><a class="button button-quiet" href="<?= e(app_url('admin/fasilitas.php')) ?>">Batal</a><button class="button button-primary" type="submit">Simpan fasilitas <span aria-hidden="true">→</span></button></div></form>
<?php require __DIR__ . '/../includes/footer.php'; ?>