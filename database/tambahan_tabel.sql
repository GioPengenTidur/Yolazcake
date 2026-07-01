-- Tabel tambahan untuk yolazcake_login
-- JALANKAN FILE INI DI phpMyAdmin (Import) SETELAH import yolazcake_login.sql
-- Jika tabel sudah ada, bisa pakai CREATE TABLE IF NOT EXISTS

-- 1. TABEL MEJA
CREATE TABLE IF NOT EXISTS `meja` (
  `id_meja` int(11) NOT NULL AUTO_INCREMENT,
  `nomor_meja` varchar(10) NOT NULL,
  `kapasitas` int(11) NOT NULL DEFAULT 1,
  `status` enum('Tersedia','Terisi','Dipesan','Tidak Aktif') DEFAULT 'Tersedia',
  `keterangan` text DEFAULT NULL,
  PRIMARY KEY (`id_meja`),
  UNIQUE KEY `nomor_meja` (`nomor_meja`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. TABEL KATEGORI
CREATE TABLE IF NOT EXISTS `kategori` (
  `id_kategori` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `icon` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. TABEL KONTAK (dengan kolom subjek)
CREATE TABLE IF NOT EXISTS `kontak` (
  `id_kontak` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `subjek` varchar(150) DEFAULT NULL,
  `pesan` text NOT NULL,
  `status` enum('Belum Dibaca','Sudah Dibaca','Dibalas') DEFAULT 'Belum Dibaca',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_kontak`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. TABEL GALERI
CREATE TABLE IF NOT EXISTS `galeri` (
  `id_galeri` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `kategori` enum('interior','kue','coffee','boutique') NOT NULL DEFAULT 'interior',
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_galeri`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. TABEL PROMO
CREATE TABLE IF NOT EXISTS `promo` (
  `id_promo` int(11) NOT NULL AUTO_INCREMENT,
  `kode_promo` varchar(20) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `diskon_persen` int(11) NOT NULL DEFAULT 0,
  `min_belanja` int(11) DEFAULT 0,
  `poin_bonus` int(11) DEFAULT 0,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `status` enum('Aktif','Nonaktif') DEFAULT 'Aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_promo`),
  UNIQUE KEY `kode_promo` (`kode_promo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6. TABEL RIWAYAT POIN
CREATE TABLE IF NOT EXISTS `riwayat_poin` (
  `id_riwayat` int(11) NOT NULL AUTO_INCREMENT,
  `id_member` int(11) NOT NULL,
  `jenis` enum('Masuk','Keluar') NOT NULL,
  `poin` int(11) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_riwayat`),
  KEY `fk_riwayat_member` (`id_member`),
  CONSTRAINT `fk_riwayat_member` FOREIGN KEY (`id_member`) REFERENCES `member` (`id_member`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 7. Tambah FK produk -> kategori (HANYA jalankan jika FK belum ada)
-- Cek dulu di phpMyAdmin: kalau sudah ada constraint fk_produk_kategori, SKIP baris ini
-- ALTER TABLE `produk` ADD CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE SET NULL;

-- 8. Data contoh kategori (opsional)
INSERT IGNORE INTO `kategori` (`nama_kategori`, `deskripsi`, `icon`) VALUES
('Minuman', 'Kopi, teh, dan minuman segar', '🥤'),
('Kue & Pastry', 'Kue, croissant, dan dessert', '🍰'),
('Makanan Berat', 'Nasi, pasta, dan makanan utama', '🍜'),
('Snack', 'Camilan dan makanan ringan', '🍟');

-- 9. Data contoh meja (opsional)
INSERT IGNORE INTO `meja` (`nomor_meja`, `kapasitas`, `status`, `keterangan`) VALUES
('M01', 2, 'Tersedia', 'Meja dekat jendela'),
('M02', 4, 'Tersedia', 'Meja tengah ruangan'),
('M03', 4, 'Tersedia', NULL),
('M04', 6, 'Tersedia', 'Meja besar untuk grup'),
('M05', 2, 'Tersedia', 'Meja sudut cozy');
