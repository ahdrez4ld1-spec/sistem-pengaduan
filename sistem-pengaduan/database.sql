CREATE DATABASE IF NOT EXISTS pengaduan_sekolah
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE pengaduan_sekolah;

CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user') NOT NULL DEFAULT 'user',
    kelas VARCHAR(50) NOT NULL,
    dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS fasilitas (
    id_fasilitas INT AUTO_INCREMENT PRIMARY KEY,
    nama_fasilitas VARCHAR(100) NOT NULL,
    kategori VARCHAR(100) NOT NULL,
    lokasi VARCHAR(150) NOT NULL,
    keterangan TEXT,
    dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pengaduan (
    id_pengaduan INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_fasilitas INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT NOT NULL,
    foto VARCHAR(255),
    tanggal DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Menunggu', 'Diproses', 'Selesai') NOT NULL DEFAULT 'Menunggu',
    CONSTRAINT fk_pengaduan_user FOREIGN KEY (id_user)
        REFERENCES users(id_user) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pengaduan_fasilitas FOREIGN KEY (id_fasilitas)
        REFERENCES fasilitas(id_fasilitas) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_pengaduan_user_tanggal (id_user, tanggal),
    INDEX idx_pengaduan_status (status),
    INDEX idx_pengaduan_fasilitas (id_fasilitas)
) ENGINE=InnoDB;

INSERT INTO fasilitas (nama_fasilitas, kategori, lokasi, keterangan)
SELECT 'Lampu', 'Kelistrikan', 'Ruang kelas', 'Lampu dan instalasi penerangan'
WHERE NOT EXISTS (SELECT 1 FROM fasilitas WHERE nama_fasilitas = 'Lampu' AND lokasi = 'Ruang kelas');

INSERT INTO fasilitas (nama_fasilitas, kategori, lokasi, keterangan)
SELECT 'Meja dan kursi', 'Perabot', 'Ruang kelas', 'Meja, kursi, dan perlengkapannya'
WHERE NOT EXISTS (SELECT 1 FROM fasilitas WHERE nama_fasilitas = 'Meja dan kursi' AND lokasi = 'Ruang kelas');

INSERT INTO fasilitas (nama_fasilitas, kategori, lokasi, keterangan)
SELECT 'Toilet', 'Sanitasi', 'Area sekolah', 'Fasilitas toilet dan saluran air'
WHERE NOT EXISTS (SELECT 1 FROM fasilitas WHERE nama_fasilitas = 'Toilet' AND lokasi = 'Area sekolah');