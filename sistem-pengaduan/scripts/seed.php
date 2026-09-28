<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../config/database.php';

$pdo = db();
$adminUsername = 'admin.demo';
$studentUsername = 'siswa.demo';
$adminPassword = getenv('SEED_ADMIN_PASSWORD') ?: 'AdminDemo2026!';
$studentPassword = getenv('SEED_USER_PASSWORD') ?: 'SiswaDemo2026!';

if (strlen($adminPassword) < 12 || strlen($studentPassword) < 8) {
    fwrite(STDERR, "Password demo terlalu pendek. Admin minimal 12 karakter, siswa minimal 8 karakter.\n");
    exit(1);
}

$added = ['admin' => 0, 'users' => 0, 'fasilitas' => 0, 'pengaduan' => 0];
$pdo->beginTransaction();

try {
    $statement = $pdo->prepare('SELECT id_admin FROM admin WHERE username = ?');
    $statement->execute([$adminUsername]);
    if (!$statement->fetch()) {
        $statement = $pdo->prepare('INSERT INTO admin (username, password) VALUES (?, ?)');
        $statement->execute([$adminUsername, password_hash($adminPassword, PASSWORD_DEFAULT)]);
        $added['admin']++;
    }

    $statement = $pdo->prepare('SELECT id_user FROM users WHERE username = ?');
    $statement->execute([$studentUsername]);
    $studentId = $statement->fetchColumn();
    if (!$studentId) {
        $statement = $pdo->prepare('INSERT INTO users (nama, username, password, kelas) VALUES (?, ?, ?, ?)');
        $statement->execute(['Nadia Putri', $studentUsername, password_hash($studentPassword, PASSWORD_DEFAULT), 'XI RPL 1']);
        $studentId = (int) $pdo->lastInsertId();
        $added['users']++;
    }

    $facilityRows = [
        ['Lampu kelas', 'Kelistrikan', 'Gedung A, ruang kelas', 'Lampu dan instalasi penerangan ruang kelas'],
        ['Meja dan kursi', 'Perabot', 'Ruang kelas XI RPL 1', 'Meja, kursi, dan perlengkapannya'],
        ['Toilet siswa', 'Sanitasi', 'Gedung B, lantai 1', 'Toilet dan saluran air siswa'],
        ['Proyektor', 'Elektronik', 'Laboratorium komputer', 'Perangkat presentasi dan kabel pendukung'],
    ];
    $findFacility = $pdo->prepare('SELECT id_fasilitas FROM fasilitas WHERE nama_fasilitas = ? AND lokasi = ?');
    $insertFacility = $pdo->prepare('INSERT INTO fasilitas (nama_fasilitas, kategori, lokasi, keterangan) VALUES (?, ?, ?, ?)');
    $facilityIds = [];
    foreach ($facilityRows as [$name, $category, $location, $description]) {
        $findFacility->execute([$name, $location]);
        $facilityId = $findFacility->fetchColumn();
        if (!$facilityId) {
            $insertFacility->execute([$name, $category, $location, $description]);
            $facilityId = $pdo->lastInsertId();
            $added['fasilitas']++;
        }
        $facilityIds[$name] = (int) $facilityId;
    }

    $reportRows = [
        ['Lampu ruang kelas mati', 'Dua lampu di bagian belakang kelas tidak menyala sejak awal minggu.', $facilityIds['Lampu kelas'], 'Menunggu', 0],
        ['Kursi dekat jendela rusak', 'Salah satu kaki kursi longgar dan perlu diperiksa agar aman digunakan.', $facilityIds['Meja dan kursi'], 'Diproses', 2],
        ['Keran toilet bocor', 'Keran di toilet siswa terus menetes setelah ditutup.', $facilityIds['Toilet siswa'], 'Selesai', 5],
    ];
    $findReport = $pdo->prepare('SELECT id_pengaduan FROM pengaduan WHERE id_user = ? AND judul = ?');
    $insertReport = $pdo->prepare('INSERT INTO pengaduan (id_user, id_fasilitas, judul, deskripsi, tanggal, status) VALUES (?, ?, ?, ?, ?, ?)');
    foreach ($reportRows as [$title, $description, $facilityId, $status, $daysAgo]) {
        $findReport->execute([$studentId, $title]);
        if (!$findReport->fetch()) {
            $reportedAt = date('Y-m-d H:i:s', time() - ($daysAgo * 86400));
            $insertReport->execute([$studentId, $facilityId, $title, $description, $reportedAt, $status]);
            $added['pengaduan']++;
        }
    }

    $pdo->commit();
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    throw $exception;
}

fwrite(STDOUT, "Seeder selesai. Data baru: admin {$added['admin']}, user {$added['users']}, fasilitas {$added['fasilitas']}, pengaduan {$added['pengaduan']}.\n");
fwrite(STDOUT, "Login demo: {$adminUsername} / password admin dari SEED_ADMIN_PASSWORD atau default dokumentasi.\n");
fwrite(STDOUT, "Login siswa: {$studentUsername} / password siswa dari SEED_USER_PASSWORD atau default dokumentasi.\n");