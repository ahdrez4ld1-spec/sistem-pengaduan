<?php
require_once __DIR__ . '/../config/app.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/fasilitas.php');
}
verify_csrf();
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id) {
    set_flash('error', 'Fasilitas tidak valid.');
    redirect('admin/fasilitas.php');
}

$statement = db()->prepare('SELECT COUNT(*) FROM pengaduan WHERE id_fasilitas = ?');
$statement->execute([$id]);
if ((int) $statement->fetchColumn() > 0) {
    set_flash('error', 'Fasilitas masih memiliki riwayat pengaduan dan tidak dapat dihapus.');
    redirect('admin/fasilitas.php');
}

$statement = db()->prepare('DELETE FROM fasilitas WHERE id_fasilitas = ?');
$statement->execute([$id]);
set_flash($statement->rowCount() ? 'success' : 'error', $statement->rowCount() ? 'Fasilitas berhasil dihapus.' : 'Fasilitas tidak ditemukan.');
redirect('admin/fasilitas.php');