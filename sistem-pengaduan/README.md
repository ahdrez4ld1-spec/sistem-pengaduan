# Ruang Lapor

Aplikasi pengaduan fasilitas sekolah berbasis PHP 8, MySQL, dan PDO.

## Menjalankan aplikasi

1. Siapkan PHP 8.1+ dengan ekstensi `pdo_mysql` dan `fileinfo`, serta MySQL/MariaDB.
2. Impor `database.sql` melalui phpMyAdmin atau jalankan `mysql -u root -p < database.sql`.
3. Salin `config/database.local.example.php` menjadi `config/database.local.php`, lalu isi username dan password MySQL lokal Anda. File lokal tersebut diabaikan Git. Anda juga dapat memakai environment variable `DB_HOST`, `DB_NAME`, `DB_USER`, dan `DB_PASS`, yang akan diprioritaskan jika diatur.
4. Dari folder `sistem-pengaduan`, isi data contoh dengan `php scripts/seed.php`. Seeder dapat dijalankan berulang dan tidak mengubah akun/data yang sudah ada.
5. Untuk membuat akun admin dengan username/password pilihan sendiri, gunakan `php scripts/create_admin.php`.
6. Jalankan server lokal: `php -S localhost:8000` dari folder proyek.
7. Buka `http://localhost:8000` untuk masuk atau mendaftarkan akun siswa.

## Akun Demo

Setelah seeder dijalankan, gunakan akun berikut pada halaman masuk:

| Peran | Username | Password |
| --- | --- | --- |
| Admin | `admin.demo` | `AdminDemo2026!` |
| Siswa | `siswa.demo` | `SiswaDemo2026!` |

Untuk mengganti password saat seed pertama, atur `SEED_ADMIN_PASSWORD` dan/atau `SEED_USER_PASSWORD` sebelum menjalankan seeder. Akun demo hanya untuk pengembangan lokal; hapus atau ganti password sebelum aplikasi dipakai sungguhan.

Pastikan folder `assets/uploads` dapat ditulis oleh proses PHP. Untuk produksi, gunakan server web dengan HTTPS, blokir eksekusi skrip di folder uploads, dan atur kredensial database lewat environment variable. Proteksi `.htaccess` hanya berlaku pada Apache.

## Fitur

- Registrasi siswa dan login admin/siswa dengan password hash.
- Pengaduan dengan foto opsional (JPG, PNG, WebP; maksimal 3 MB), status tindak lanjut, dan riwayat pencarian.
- Admin dapat mencari laporan, mengubah status, menghapus laporan, serta mengelola data fasilitas.
- Penghapusan fasilitas ditolak jika sudah memiliki riwayat pengaduan.
- Formulir perubahan data dilindungi token CSRF dan query database memakai prepared statement.