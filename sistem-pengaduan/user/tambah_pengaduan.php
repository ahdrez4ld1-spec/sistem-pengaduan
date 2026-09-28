<?php
require_once __DIR__ . '/../config/app.php';
require_role('user');

$errors = [];
$values = ['id_fasilitas' => '', 'judul' => '', 'deskripsi' => ''];
$facilities = db()->query('SELECT id_fasilitas, nama_fasilitas, kategori, lokasi FROM fasilitas ORDER BY kategori, nama_fasilitas')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($values as $key => $_value) {
        $values[$key] = trim((string) ($_POST[$key] ?? ''));
    }
    $facilityId = filter_var($values['id_fasilitas'], FILTER_VALIDATE_INT);
    $statement = db()->prepare('SELECT id_fasilitas FROM fasilitas WHERE id_fasilitas = ?');
    $statement->execute([$facilityId ?: 0]);
    if (!$statement->fetch()) {
        $errors[] = 'Pilih fasilitas yang tersedia.';
    }
    if ($values['judul'] === '' || mb_strlen($values['judul']) > 200) {
        $errors[] = 'Judul wajib diisi dan maksimal 200 karakter.';
    }
    if ($values['deskripsi'] === '' || mb_strlen($values['deskripsi']) > 10000) {
        $errors[] = 'Deskripsi wajib diisi dan maksimal 10.000 karakter.';
    }

    $photoName = null;
    $file = $_FILES['foto'] ?? null;
    if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Foto gagal diunggah. Coba pilih file lain.';
        } elseif ($file['size'] > 3 * 1024 * 1024) {
            $errors[] = 'Ukuran foto maksimal 3 MB.';
        } else {
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
            $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            if (!isset($allowedTypes[$mime]) || @getimagesize($file['tmp_name']) === false) {
                $errors[] = 'Foto harus berupa JPG, PNG, atau WebP yang valid.';
            } else {
                $photoName = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mime];
            }
        }
    }

    if (!$errors) {
        if ($photoName !== null && !move_uploaded_file($file['tmp_name'], __DIR__ . '/../assets/uploads/' . $photoName)) {
            $errors[] = 'Foto tidak dapat disimpan. Pastikan folder uploads dapat ditulis.';
        } else {
            $statement = db()->prepare('INSERT INTO pengaduan (id_user, id_fasilitas, judul, deskripsi, foto) VALUES (?, ?, ?, ?, ?)');
            $statement->execute([current_account_id(), (int) $facilityId, $values['judul'], $values['deskripsi'], $photoName]);
            set_flash('success', 'Pengaduan berhasil dikirim. Terima kasih sudah menyampaikan.');
            redirect('user/riwayat.php');
        }
    }
}

$pageTitle = 'Buat pengaduan';
$activeNav = 'buat';
require __DIR__ . '/../includes/header.php';
?>
<div class="form-intro"><span class="eyebrow">FORMULIR LAPORAN</span><h2>Ceritakan apa yang perlu diperbaiki.</h2><p>Berikan informasi yang cukup agar petugas dapat menemukan dan menangani masalahnya.</p></div>
<?php if ($errors): ?><div class="alert alert-error" role="alert"><ul class="error-list"><?php foreach ($errors as $message): ?><li><?= e($message) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php if (!$facilities): ?><div class="empty-state"><h3>Fasilitas belum tersedia</h3><p>Silakan hubungi administrator sekolah sebelum membuat laporan.</p></div><?php else: ?>
<form method="post" enctype="multipart/form-data" class="form-panel">
    <?= csrf_field() ?>
    <div class="form-grid">
        <label class="field field-full"><span>Fasilitas yang dilaporkan <b>*</b></span><select name="id_fasilitas" required><option value="">Pilih fasilitas</option><?php foreach ($facilities as $facility): ?><option value="<?= (int) $facility['id_fasilitas'] ?>" <?= $values['id_fasilitas'] === (string) $facility['id_fasilitas'] ? 'selected' : '' ?>><?= e($facility['nama_fasilitas']) ?> · <?= e($facility['lokasi']) ?> (<?= e($facility['kategori']) ?>)</option><?php endforeach; ?></select></label>
        <label class="field field-full"><span>Judul laporan <b>*</b></span><input type="text" name="judul" maxlength="200" value="<?= e($values['judul']) ?>" placeholder="Contoh: Lampu kelas XI mati" required></label>
        <label class="field field-full"><span>Deskripsi masalah <b>*</b></span><textarea name="deskripsi" rows="6" maxlength="10000" placeholder="Jelaskan kondisi, lokasi yang lebih spesifik, dan kapan masalah mulai terlihat..." required><?= e($values['deskripsi']) ?></textarea><small>Jelaskan secara singkat dan jelas. Maksimal 10.000 karakter.</small></label>
        <label class="field field-full"><span>Foto pendukung <small>(opsional)</small></span><input class="file-input" type="file" name="foto" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG, atau WebP. Maksimal 3 MB.</small></label>
    </div>
    <div class="form-actions"><a class="button button-quiet" href="<?= e(app_url('user/riwayat.php')) ?>">Batal</a><button class="button button-primary" type="submit">Kirim pengaduan <span aria-hidden="true">→</span></button></div>
</form>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>