<?php
require_once __DIR__ . '/../config/app.php';
require_role('admin');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$statement = db()->prepare('SELECT * FROM fasilitas WHERE id_fasilitas = ?');
$statement->execute([$id ?: 0]);
$facility = $statement->fetch();
if (!$facility) {
    http_response_code(404);
    exit('Fasilitas tidak ditemukan.');
}

$errors = [];
$values = ['nama_fasilitas' => $facility['nama_fasilitas'], 'kategori' => $facility['kategori'], 'lokasi' => $facility['lokasi'], 'keterangan' => $facility['keterangan'] ?? ''];
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
        $statement = db()->prepare('UPDATE fasilitas SET nama_fasilitas = ?, kategori = ?, lokasi = ?, keterangan = ? WHERE id_fasilitas = ?');
        $statement->execute([$values['nama_fasilitas'], $values['kategori'], $values['lokasi'], $values['keterangan'] ?: null, $facility['id_fasilitas']]);
        set_flash('success', 'Data fasilitas berhasil diperbarui.');
        redirect('admin/fasilitas.php');
    }
}

$pageTitle = 'Ubah fasilitas';
$activeNav = 'fasilitas';
require __DIR__ . '/../includes/header.php';
?>
<div class="form-intro"><a class="back-link" href="<?= e(app_url('admin/fasilitas.php')) ?>">← Kembali ke fasilitas</a><span class="eyebrow">DATA INVENTARIS</span><h2>Ubah fasilitas</h2><p>Perbarui informasi <?= e($facility['nama_fasilitas']) ?>.</p></div>
<?php if ($errors): ?><div class="alert alert-error" role="alert"><ul class="error-list"><?php foreach ($errors as $message): ?><li><?= e($message) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" class="form-panel"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="form-grid">
    <label class="field"><span>Nama fasilitas <b>*</b></span><input type="text" name="nama_fasilitas" maxlength="100" value="<?= e($values['nama_fasilitas']) ?>" required></label>
    <label class="field"><span>Kategori <b>*</b></span><input type="text" name="kategori" maxlength="100" value="<?= e($values['kategori']) ?>" required></label>
    <label class="field field-full"><span>Lokasi <b>*</b></span><input type="text" name="lokasi" maxlength="150" value="<?= e($values['lokasi']) ?>" required></label>
    <label class="field field-full"><span>Keterangan</span><textarea name="keterangan" rows="4" maxlength="5000"><?= e($values['keterangan']) ?></textarea></label>
</div><div class="form-actions"><a class="button button-quiet" href="<?= e(app_url('admin/fasilitas.php')) ?>">Batal</a><button class="button button-primary" type="submit">Simpan perubahan <span aria-hidden="true">→</span></button></div></form>
<?php require __DIR__ . '/../includes/footer.php'; ?>