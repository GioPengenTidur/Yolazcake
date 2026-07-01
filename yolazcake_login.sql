-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 30 Jun 2026 pada 19.56
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `yolazcake_login`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `booking`
--

CREATE TABLE `booking` (
  `id_booking` int(11) NOT NULL,
  `id_member` int(11) DEFAULT NULL,
  `nama_pemesan` varchar(100) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `tanggal_booking` date NOT NULL,
  `jam_booking` time NOT NULL,
  `jumlah_orang` int(11) NOT NULL,
  `catatan` text DEFAULT NULL,
  `status` enum('Pending','Dikonfirmasi','Selesai','Dibatalkan') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_meja` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `booking`
--

INSERT INTO `booking` (`id_booking`, `id_member`, `nama_pemesan`, `no_hp`, `tanggal_booking`, `jam_booking`, `jumlah_orang`, `catatan`, `status`, `created_at`, `id_meja`) VALUES
(1, NULL, 'yoonskyy', '0898866755', '2026-06-01', '04:08:00', 1, 'Weenakk poll', 'Dikonfirmasi', '2026-06-16 19:06:54', NULL),
(2, NULL, 'yoyon', '0844278423', '2026-06-16', '02:21:00', 1, 'g ada', 'Dibatalkan', '2026-06-16 19:19:49', NULL),
(3, NULL, 'tes', '12423423', '2026-06-16', '01:58:00', 2, '222', 'Dibatalkan', '2026-06-16 19:22:19', NULL),
(4, NULL, 'd', '12', '2026-06-17', '10:03:00', 1, '11', 'Pending', '2026-06-16 19:32:02', NULL),
(5, NULL, '12', '23', '2026-06-17', '10:36:00', 1, '', 'Pending', '2026-06-16 19:32:33', NULL),
(6, NULL, 'eeee', '456', '2026-06-17', '14:32:00', 1, '', 'Pending', '2026-06-16 19:32:53', NULL),
(7, NULL, 'df', '865644', '2026-06-17', '19:36:00', 1, '', 'Dikonfirmasi', '2026-06-16 19:33:11', NULL),
(9, NULL, 'lol', '456777', '2026-06-17', '20:36:00', 1, '', 'Dibatalkan', '2026-06-16 19:33:47', NULL),
(13, NULL, 'GIOOO', '45456', '2026-06-30', '12:24:00', 2, '', 'Pending', '2026-06-28 19:23:37', 1),
(14, NULL, 'anonim', '989898', '2026-06-30', '11:22:00', 2, 'tes aja', 'Pending', '2026-06-29 16:17:14', 2),
(15, NULL, 'anonim', '989898', '2026-06-30', '11:22:00', 2, 'tes aja', 'Pending', '2026-06-29 16:17:48', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_pemesanan`
--

CREATE TABLE `detail_pemesanan` (
  `id_detail` int(11) NOT NULL,
  `id_pemesanan` int(11) DEFAULT NULL,
  `id_produk` int(11) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_pemesanan`
--

INSERT INTO `detail_pemesanan` (`id_detail`, `id_pemesanan`, `id_produk`, `jumlah`, `subtotal`) VALUES
(3, 4, 4, 3, 54000.00),
(4, 4, 5, 1, 25000.00),
(5, 5, 4, 1, 18000.00),
(6, 6, 4, 1, 18000.00),
(7, 7, 4, 2, 36000.00),
(8, 8, 4, 1, 18000.00),
(9, 9, 4, 2, 36000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori` 
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `deskripsi`, `icon`) VALUES
(1, 'Minuman', 'Kopi, teh, dan minuman segar lainnya', '☕'),
(2, 'Makanan Ringan', 'Croffle, snack, dan camilan', '🥐'),
(3, 'Kue & Cake', 'Aneka kue dan cake', '🍰'),
(4, 'Lainnya', 'Produk lain-lain', '🍽️');

-- --------------------------------------------------------

--
-- Struktur dari tabel `meja` 
--

CREATE TABLE `meja` (
  `id_meja` int(11) NOT NULL,
  `nomor_meja` varchar(10) NOT NULL,
  `kapasitas` int(11) NOT NULL DEFAULT 2,
  `status` enum('Tersedia','Terisi','Dipesan') DEFAULT 'Tersedia',
  `keterangan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `meja`
--

INSERT INTO `meja` (`id_meja`, `nomor_meja`, `kapasitas`, `status`, `keterangan`) VALUES
(1, 'M1', 2, 'Tersedia', NULL),
(2, 'M2', 4, 'Tersedia', NULL),
(3, 'M3', 4, 'Tersedia', NULL),
(4, 'M4', 6, 'Tersedia', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `galeri` 
--

CREATE TABLE `galeri` (
  `id_galeri` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `promo` 
--

CREATE TABLE `promo` (
  `id_promo` int(11) NOT NULL,
  `kode_promo` varchar(50) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `diskon_persen` int(11) DEFAULT 0,
  `min_belanja` int(11) DEFAULT 0,
  `poin_bonus` int(11) DEFAULT 0,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `status` enum('Aktif','Nonaktif') DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kontak` 
--

CREATE TABLE `kontak` (
  `id_kontak` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `subjek` varchar(150) DEFAULT NULL,
  `pesan` text NOT NULL,
  `status` enum('Belum Dibaca','Sudah Dibaca') DEFAULT 'Belum Dibaca',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_poin` 
--

CREATE TABLE `riwayat_poin` (
  `id_riwayat` int(11) NOT NULL,
  `id_member` int(11) NOT NULL,
  `jenis` enum('Masuk','Keluar') DEFAULT 'Masuk',
  `poin` int(11) NOT NULL DEFAULT 0,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `member`
--

CREATE TABLE `member` (
  `id_member` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `poin` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `member`
--

INSERT INTO `member` (`id_member`, `nama`, `email`, `no_hp`, `alamat`, `poin`, `created_at`) VALUES
(2, 'Amba', 'kangkung@gmail.com', '089129817656', 'ngawi', 9, '2026-06-09 15:38:36'),
(3, 'tes', 'gionardoalenski@itbss.ac.id', '0891298263633', 'ITBSS', 10, '2026-06-14 17:06:14');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pemesanan`
--

CREATE TABLE `pemesanan` (
  `id_pemesanan` int(11) NOT NULL,
  `kode_pesanan` varchar(20) DEFAULT NULL,
  `id_member` int(11) DEFAULT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `total_harga` decimal(10,2) DEFAULT NULL,
  `id_booking` int(11) DEFAULT NULL,
  `metode_pembayaran` varchar(20) DEFAULT NULL,
  `status_pembayaran` enum('Menunggu','Lunas','Gagal') DEFAULT 'Menunggu',
  `status_pesanan` enum('Menunggu','Diproses','Siap Diambil','Selesai','Dibatalkan') DEFAULT 'Menunggu',
  `nama_pemesan` varchar(100) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `nomor_meja` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pemesanan`
--

INSERT INTO `pemesanan` (`id_pemesanan`, `kode_pesanan`, `id_member`, `tanggal`, `total_harga`, `id_booking`, `metode_pembayaran`, `status_pembayaran`, `status_pesanan`, `nama_pemesan`, `no_hp`, `nomor_meja`) VALUES
(4, 'ORD20260617190835', NULL, '2026-06-17 19:08:35', 79000.00, NULL, 'QRIS', 'Lunas', 'Menunggu', '12', '0898866755', NULL),
(5, 'ORD20260617192710', NULL, '2026-06-17 19:27:10', 18000.00, NULL, 'QRIS', 'Lunas', 'Menunggu', 'yoyon', '23', NULL),
(6, 'ORD20260618050138', NULL, '2026-06-18 05:01:38', 18000.00, NULL, 'QRIS', 'Lunas', 'Menunggu', 'sasxadsad', '0891298263633', NULL),
(7, 'ORD20260618053018', NULL, '2026-06-18 05:30:18', 36000.00, NULL, 'QRIS', 'Lunas', 'Menunggu', 'tes', '123', NULL),
(8, 'ORD20260629195149', NULL, '2026-06-29 19:51:49', 18000.00, 15, 'QRIS', 'Lunas', 'Menunggu', 'yoyon', '0898866755', NULL),
(9, 'ORD20260629195300', NULL, '2026-06-29 19:53:00', 36000.00, 15, 'QRIS', 'Lunas', 'Menunggu', 'yoyon', '0898866755', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `stok` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_kategori` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id_produk`, `nama_produk`, `harga`, `deskripsi`, `foto`, `stok`, `created_at`, `id_kategori`) VALUES
(4, 'Americano', 18000.00, 'Kopi Americano', NULL, 100, '2026-06-17 17:06:21', 1),
(5, 'Matcha Latte', 25000.00, 'Minuman Matcha', NULL, 100, '2026-06-17 17:06:21', 1),
(6, 'Croffle', 22000.00, 'Croissant Waffle', NULL, 50, '2026-06-17 17:06:21', 2),
(7, 'French Fries', 18000.00, 'Kentang Goreng', NULL, 50, '2026-06-17 17:06:21', 4);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','kasir','pengunjung') DEFAULT 'pengunjung'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '12345', 'admin'),
(2, 'admin2', '12345', 'admin'),
(3, 'kasir1', '12345', 'kasir'),
(4, 'kasir2', '12345', 'kasir'),
(5, 'pengunjung1', '12345', 'pengunjung'),
(6, 'pengunjung2', '12345', 'pengunjung');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`id_booking`),
  ADD KEY `id_member` (`id_member`),
  ADD KEY `id_meja` (`id_meja`);

--
-- Indeks untuk tabel `detail_pemesanan`
--
ALTER TABLE `detail_pemesanan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `fk_pemesanan` (`id_pemesanan`),
  ADD KEY `fk_produk` (`id_produk`);

--
-- Indeks untuk tabel `galeri`
--
ALTER TABLE `galeri`
  ADD PRIMARY KEY (`id_galeri`);

--
-- Indeks untuk tabel `promo`
--
ALTER TABLE `promo`
  ADD PRIMARY KEY (`id_promo`);

--
-- Indeks untuk tabel `kontak`
--
ALTER TABLE `kontak`
  ADD PRIMARY KEY (`id_kontak`);

--
-- Indeks untuk tabel `riwayat_poin`
--
ALTER TABLE `riwayat_poin`
  ADD PRIMARY KEY (`id_riwayat`),
  ADD KEY `id_member` (`id_member`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indeks untuk tabel `meja`
--
ALTER TABLE `meja`
  ADD PRIMARY KEY (`id_meja`);

--
-- Indeks untuk tabel `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`id_member`);

--
-- Indeks untuk tabel `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD PRIMARY KEY (`id_pemesanan`),
  ADD UNIQUE KEY `kode_pesanan` (`kode_pesanan`),
  ADD KEY `fk_member` (`id_member`),
  ADD KEY `fk_pemesanan_booking` (`id_booking`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD KEY `fk_produk_kategori` (`id_kategori`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `booking`
--
ALTER TABLE `booking`
  MODIFY `id_booking` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `detail_pemesanan`
--
ALTER TABLE `detail_pemesanan`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `galeri`
--
ALTER TABLE `galeri`
  MODIFY `id_galeri` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT untuk tabel `promo`
--
ALTER TABLE `promo`
  MODIFY `id_promo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT untuk tabel `kontak`
--
ALTER TABLE `kontak`
  MODIFY `id_kontak` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT untuk tabel `riwayat_poin`
--
ALTER TABLE `riwayat_poin`
  MODIFY `id_riwayat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `meja`
--
ALTER TABLE `meja`
  MODIFY `id_meja` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `member`
--
ALTER TABLE `member`
  MODIFY `id_member` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `pemesanan`
--
ALTER TABLE `pemesanan`
  MODIFY `id_pemesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`id_member`) REFERENCES `member` (`id_member`) ON DELETE SET NULL,
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`id_meja`) REFERENCES `meja` (`id_meja`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `detail_pemesanan`
--
ALTER TABLE `detail_pemesanan`
  ADD CONSTRAINT `detail_pemesanan_ibfk_1` FOREIGN KEY (`id_pemesanan`) REFERENCES `pemesanan` (`id_pemesanan`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_pemesanan_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD CONSTRAINT `fk_member` FOREIGN KEY (`id_member`) REFERENCES `member` (`id_member`),
  ADD CONSTRAINT `fk_pemesanan_booking` FOREIGN KEY (`id_booking`) REFERENCES `booking` (`id_booking`);

--
-- Ketidakleluasaan untuk tabel `riwayat_poin`
--
ALTER TABLE `riwayat_poin`
  ADD CONSTRAINT `riwayat_poin_ibfk_1` FOREIGN KEY (`id_member`) REFERENCES `member` (`id_member`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
