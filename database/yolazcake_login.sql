-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 07 Jul 2026 pada 22.29
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
  `id_user` int(11) DEFAULT NULL,
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

INSERT INTO `booking` (`id_booking`, `id_member`, `id_user`, `nama_pemesan`, `no_hp`, `tanggal_booking`, `jam_booking`, `jumlah_orang`, `catatan`, `status`, `created_at`, `id_meja`) VALUES
(1, NULL, NULL, 'yoonskyy', '0898866755', '2026-06-01', '04:08:00', 1, 'Weenakk poll', 'Dikonfirmasi', '2026-06-16 19:06:54', NULL),
(2, NULL, NULL, 'yoyon', '0844278423', '2026-06-16', '02:21:00', 1, 'g ada', 'Dibatalkan', '2026-06-16 19:19:49', NULL),
(3, NULL, NULL, 'tes', '12423423', '2026-06-16', '01:58:00', 2, '222', 'Dibatalkan', '2026-06-16 19:22:19', NULL),
(4, NULL, NULL, 'd', '12', '2026-06-17', '10:03:00', 1, '11', 'Pending', '2026-06-16 19:32:02', NULL),
(5, NULL, NULL, '12', '23', '2026-06-17', '10:36:00', 1, '', 'Dibatalkan', '2026-06-16 19:32:33', NULL),
(7, NULL, NULL, 'df', '865644', '2026-06-17', '19:36:00', 1, '', 'Dikonfirmasi', '2026-06-16 19:33:11', NULL),
(9, NULL, NULL, 'lol', '456777', '2026-06-17', '20:36:00', 1, '', 'Dibatalkan', '2026-06-16 19:33:47', NULL),
(13, NULL, NULL, 'GIOOO', '45456', '2026-06-30', '12:24:00', 2, '', 'Dikonfirmasi', '2026-06-28 19:23:37', 1),
(14, NULL, NULL, 'anonim', '989898', '2026-06-30', '11:22:00', 2, 'tes aja', 'Dibatalkan', '2026-06-29 16:17:14', 2),
(15, NULL, NULL, 'anonim', '989898', '2026-06-30', '11:22:00', 2, 'tes aja', 'Dikonfirmasi', '2026-06-29 16:17:48', NULL),
(18, NULL, NULL, 'anonim', '23', '2026-07-05', '19:24:00', 2, '', 'Dikonfirmasi', '2026-07-04 11:24:11', NULL),
(19, NULL, NULL, 'yoyon', '111111', '2026-07-06', '12:06:00', 2, '', 'Pending', '2026-07-04 19:04:03', NULL),
(20, NULL, NULL, 'yoyon', '0898866755', '2026-07-06', '18:16:00', 1, '', 'Pending', '2026-07-04 19:16:08', NULL),
(21, NULL, 5, 'yoyon', '12345555', '2026-07-08', '12:39:00', 1, '', 'Pending', '2026-07-07 04:39:38', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `chat_memori`
--

CREATE TABLE `chat_memori` (
  `id_memori` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `guest_token` varchar(64) DEFAULT NULL,
  `ringkasan` text NOT NULL,
  `diperbarui_pada` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `chat_memori`
--

INSERT INTO `chat_memori` (`id_memori`, `id_user`, `guest_token`, `ringkasan`, `diperbarui_pada`) VALUES
(1, 1, NULL, '*   Nama pengguna/ID: y', '2026-07-08 03:18:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `chat_pesan`
--

CREATE TABLE `chat_pesan` (
  `id_pesan` int(11) NOT NULL,
  `id_sesi` int(11) NOT NULL,
  `peran` enum('user','bot') NOT NULL,
  `isi` text NOT NULL,
  `dibuat_pada` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `chat_sesi`
--

CREATE TABLE `chat_sesi` (
  `id_sesi` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `guest_token` varchar(64) DEFAULT NULL,
  `judul` varchar(150) NOT NULL DEFAULT 'Obrolan baru',
  `dibuat_pada` datetime NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(11, 11, 12, 4, 60000.00),
(12, 12, 12, 8, 120000.00),
(13, 13, 12, 7, 105000.00),
(14, 14, 12, 9, 135000.00),
(15, 15, 12, 4, 60000.00),
(16, 16, 12, 3, 45000.00),
(17, 17, 12, 2, 30000.00),
(18, 18, 12, 2, 30000.00),
(19, 19, 12, 2, 30000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `galeri`
--

CREATE TABLE `galeri` (
  `id_galeri` int(11) NOT NULL,
  `judul` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `kategori` enum('interior','kue','coffee','boutique') NOT NULL DEFAULT 'interior',
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ig_stats`
--

CREATE TABLE `ig_stats` (
  `id` int(11) NOT NULL,
  `followers` int(11) NOT NULL DEFAULT 0,
  `following` int(11) NOT NULL DEFAULT 0,
  `posts` int(11) NOT NULL DEFAULT 0,
  `updated_by` varchar(100) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `ig_stats`
--

INSERT INTO `ig_stats` (`id`, `followers`, `following`, `posts`, `updated_by`, `updated_at`) VALUES
(1, 5773, 74, 15, 'yoonskyy63@gmail.com', '2026-07-07 01:03:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `icon` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `deskripsi`, `icon`) VALUES
(1, 'Minuman', 'Kopi, teh, dan minuman segar', '🥤'),
(2, 'Kue & Pastry', 'Kue, croissant, dan dessert', '🍰'),
(3, 'Makanan Berat', 'Nasi, pasta, dan makanan utama', '🍜'),
(4, 'Snack', 'Camilan dan makanan ringan', '🍟');

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
  `kategori` enum('Umum','Bantuan Akun') NOT NULL DEFAULT 'Umum',
  `username_terkait` varchar(50) DEFAULT NULL,
  `pesan` text NOT NULL,
  `status` enum('Belum Dibaca','Sudah Dibaca','Dibalas') DEFAULT 'Belum Dibaca',
  `balasan` text DEFAULT NULL,
  `dibalas_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kontak`
--

INSERT INTO `kontak` (`id_kontak`, `nama`, `email`, `no_hp`, `subjek`, `kategori`, `username_terkait`, `pesan`, `status`, `balasan`, `dibalas_at`, `created_at`) VALUES
(1, 'Gionardo Alenski', 'twendexo85@gmail.com', '1234567890', 'Lupa Password', 'Bantuan Akun', 'GioNA', 'tes', 'Dibalas', 'tes', '2026-07-05 00:39:57', '2026-07-04 17:38:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `meja`
--

CREATE TABLE `meja` (
  `id_meja` int(11) NOT NULL,
  `nomor_meja` varchar(10) NOT NULL,
  `kapasitas` int(11) NOT NULL,
  `status` enum('Tersedia','Terisi','Dipesan','Tidak Aktif') DEFAULT 'Tersedia',
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `meja`
--

INSERT INTO `meja` (`id_meja`, `nomor_meja`, `kapasitas`, `status`, `keterangan`) VALUES
(1, 'M01', 4, 'Tersedia', 'Data placeholder, silakan sesuaikan'),
(2, 'M02', 4, 'Tersedia', 'Data placeholder, silakan sesuaikan'),
(3, 'M03', 4, 'Tersedia', NULL),
(4, 'M04', 6, 'Tersedia', 'Meja besar untuk grup'),
(5, 'M05', 2, 'Tersedia', 'Meja sudut cozy');

-- --------------------------------------------------------

--
-- Struktur dari tabel `member`
--

CREATE TABLE `member` (
  `id_member` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
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

INSERT INTO `member` (`id_member`, `id_user`, `nama`, `email`, `no_hp`, `alamat`, `poin`, `created_at`) VALUES
(2, NULL, 'Amba', 'kangkung@gmail.com', '089129817656', 'ngawi', 9, '2026-06-09 15:38:36'),
(3, NULL, 'tes', 'gionardoalenski@itbss.ac.id', '0891298263633', 'ITBSS', 10, '2026-06-14 17:06:14'),
(5, 9, 'GioNa', 'twendexo85@gmail.com', '11111111111', 'Sintang', 100, '2026-07-04 15:19:42'),
(6, NULL, 'gionardoalenskii@gmail.com', 'gionardoalenskii@gmail.com', '', '', 0, '2026-07-04 19:21:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `menu_highlight_foto`
--

CREATE TABLE `menu_highlight_foto` (
  `id_foto` int(11) NOT NULL,
  `section` varchar(30) NOT NULL,
  `card_index` int(11) NOT NULL,
  `slide_index` int(11) NOT NULL,
  `nama_kartu` varchar(100) DEFAULT NULL,
  `label_slide` varchar(100) DEFAULT NULL,
  `foto_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `menu_highlight_foto`
--

INSERT INTO `menu_highlight_foto` (`id_foto`, `section`, `card_index`, `slide_index`, `nama_kartu`, `label_slide`, `foto_path`) VALUES
(1, 'highlight', 0, 0, 'Donut', 'Slide 1', 'assets/img/produk/Doughnut.jpeg'),
(2, 'highlight', 0, 1, 'Donut', 'Slide 2', 'assets/img/produk/Tiramisu_Doughnut.jpeg'),
(3, 'highlight', 0, 2, 'Donut', 'Slide 3', 'assets/img/produk/Matcha_Doughnut.jpeg'),
(4, 'highlight', 1, 0, 'Specialty Coffee', 'Slide 1', 'assets/img/produk/Iced Palm Sugar Coffee Latte.jpeg'),
(5, 'highlight', 1, 1, 'Specialty Coffee', 'Slide 2', 'assets/img/produk/Coffee_Latte.jpeg'),
(6, 'highlight', 1, 2, 'Specialty Coffee', 'Slide 3', 'assets/img/produk/Coffee_Latte_2.jpeg'),
(7, 'highlight', 2, 0, 'Dessert & Minuman', 'Slide 1', 'assets/img/produk/Iced_Chocolate.jpeg'),
(8, 'highlight', 2, 1, 'Dessert & Minuman', 'Slide 2', 'assets/img/produk/Matcha_Latte.jpeg'),
(9, 'highlight', 2, 2, 'Dessert & Minuman', 'Slide 3', 'assets/img/produk/Lychee_Iced_Tea.jpeg'),
(10, 'highlight', 3, 0, 'Boutique Lantai 2', 'Slide 1', 'assets/img/produk/Boutique_Lantai_2.jpeg'),
(11, 'highlight', 3, 1, 'Boutique Lantai 2', 'Slide 2', 'assets/img/produk/Boutique_Lantai_2_1.jpeg'),
(12, 'highlight', 3, 2, 'Boutique Lantai 2', 'Slide 3', 'assets/img/produk/Boutique_Lantai_2_2.jpeg'),
(13, 'unggulan', 0, 0, 'Trio Cake', 'Slide 1', 'assets/img/produk/Chocolate_Indulgence.jpeg'),
(14, 'unggulan', 0, 1, 'Trio Cake', 'Slide 2', 'assets/img/produk/Klepon_Cake.jpeg'),
(15, 'unggulan', 0, 2, 'Trio Cake', 'Slide 3', 'assets/img/produk/Red_Velvet_Cake.jpeg'),
(16, 'unggulan', 1, 0, 'Signature Latte', 'Slide 1', 'assets/img/produk/Iced_Palm_Sugar_Coffee_Latte.jpeg'),
(17, 'unggulan', 1, 1, 'Signature Latte', 'Slide 2', 'assets/img/produk/Iced_Brown_Sugar_Coffee_Latte.jpeg'),
(18, 'unggulan', 1, 2, 'Signature Latte', 'Slide 3', 'assets/img/produk/Matcha_Latte.jpeg'),
(19, 'unggulan', 2, 0, 'Donat Premium', 'Slide 1', 'assets/img/produk/Strawberry_Doughnut.jpeg'),
(20, 'unggulan', 2, 1, 'Donat Premium', 'Slide 2', 'assets/img/produk/Classic_Chocolate_Doughnut.jpeg'),
(21, 'unggulan', 2, 2, 'Donat Premium', 'Slide 3', 'assets/img/produk/Red_Velvet_Doughnut.jpeg'),
(22, 'unggulan', 3, 0, 'Boutique Collection', 'Slide 1', 'assets/img/produk/Boutique_Collection.jpeg'),
(23, 'unggulan', 3, 1, 'Boutique Collection', 'Slide 2', 'assets/img/produk/Baju.jpeg'),
(24, 'unggulan', 3, 2, 'Boutique Collection', 'Slide 3', 'assets/img/produk/Celana.jpeg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pemesanan`
--

CREATE TABLE `pemesanan` (
  `id_pemesanan` int(11) NOT NULL,
  `kode_pesanan` varchar(20) DEFAULT NULL,
  `id_member` int(11) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `total_harga` decimal(10,2) DEFAULT NULL,
  `kode_promo` varchar(30) DEFAULT NULL,
  `diskon_nominal` decimal(10,2) DEFAULT 0.00,
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

INSERT INTO `pemesanan` (`id_pemesanan`, `kode_pesanan`, `id_member`, `id_user`, `tanggal`, `total_harga`, `kode_promo`, `diskon_nominal`, `id_booking`, `metode_pembayaran`, `status_pembayaran`, `status_pesanan`, `nama_pemesan`, `no_hp`, `nomor_meja`) VALUES
(4, 'ORD20260617190835', NULL, NULL, '2026-06-17 19:08:35', 79000.00, NULL, 0.00, NULL, 'QRIS', 'Lunas', 'Menunggu', '12', '0898866755', NULL),
(5, 'ORD20260617192710', NULL, NULL, '2026-06-17 19:27:10', 18000.00, NULL, 0.00, NULL, 'QRIS', 'Lunas', 'Menunggu', 'yoyon', '23', NULL),
(6, 'ORD20260618050138', NULL, NULL, '2026-06-18 05:01:38', 18000.00, NULL, 0.00, NULL, 'QRIS', 'Lunas', 'Menunggu', 'sasxadsad', '0891298263633', NULL),
(11, 'ORD20260704172724', NULL, NULL, '2026-07-04 17:27:24', 60000.00, NULL, 0.00, NULL, 'QRIS', 'Lunas', 'Menunggu', 'GioNA', '12345555', NULL),
(12, 'ORD20260704173003', NULL, NULL, '2026-07-04 17:30:03', 120000.00, NULL, 0.00, NULL, 'QRIS', 'Lunas', 'Selesai', 'GioNA', '12345555', NULL),
(13, 'ORD20260704174054', NULL, NULL, '2026-07-04 17:40:54', 105000.00, NULL, 0.00, NULL, 'QRIS', 'Lunas', 'Menunggu', 'GioNA', '12345555', NULL),
(14, 'ORD20260704210432', NULL, NULL, '2026-07-04 21:04:32', 135000.00, NULL, 0.00, 19, 'QRIS', 'Lunas', 'Menunggu', 'yoyon', '111111', NULL),
(15, 'ORD20260704211532', NULL, NULL, '2026-07-04 21:15:32', 60000.00, NULL, 0.00, 19, 'QRIS', 'Lunas', 'Menunggu', 'yoyon', '111111', NULL),
(16, 'ORD20260704211646', NULL, NULL, '2026-07-04 21:16:46', 45000.00, NULL, 0.00, 20, 'QRIS', 'Lunas', 'Menunggu', 'yoyon', '0898866755', NULL),
(17, 'ORD20260704211718', NULL, NULL, '2026-07-04 21:17:18', 30000.00, NULL, 0.00, 20, 'QRIS', 'Lunas', 'Menunggu', 'yoyon', '0898866755', NULL),
(18, 'ORD20260704212119', 6, NULL, '2026-07-04 21:21:19', 30000.00, NULL, 0.00, 20, 'QRIS', 'Lunas', 'Menunggu', 'yoyon', '0898866755', NULL),
(19, 'ORD20260707064013', NULL, 5, '2026-07-07 06:40:13', 30000.00, NULL, 0.00, 21, 'QRIS', 'Lunas', 'Menunggu', 'yoyon', '12345555', NULL);

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
(12, 'Ayam Goreng', 15000.00, 'enak', 'img_6a48f54bd0c1b9.51715188.png', 48, '2026-07-04 11:58:03', 4);

-- --------------------------------------------------------

--
-- Struktur dari tabel `promo`
--

CREATE TABLE `promo` (
  `id_promo` int(11) NOT NULL,
  `kode_promo` varchar(30) NOT NULL,
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
-- Struktur dari tabel `promo_klaim`
--

CREATE TABLE `promo_klaim` (
  `id_klaim` int(11) NOT NULL,
  `id_promo` int(11) NOT NULL,
  `id_member` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_poin`
--

CREATE TABLE `riwayat_poin` (
  `id_riwayat` int(11) NOT NULL,
  `id_member` int(11) NOT NULL,
  `jenis` enum('Masuk','Keluar') NOT NULL,
  `poin` int(11) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ulasan_produk`
--

CREATE TABLE `ulasan_produk` (
  `id_ulasan` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `nama_reviewer` varchar(100) NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `komentar` text DEFAULT NULL,
  `dibaca` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ulasan_tempat`
--

CREATE TABLE `ulasan_tempat` (
  `id_ulasan_tempat` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_pemesanan` int(11) DEFAULT NULL,
  `nama_reviewer` varchar(100) NOT NULL,
  `rating_makanan` tinyint(1) NOT NULL,
  `rating_tempat` tinyint(1) NOT NULL,
  `komentar` text DEFAULT NULL,
  `dibaca` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data untuk tabel `ulasan_tempat`
--

INSERT INTO `ulasan_tempat` (`id_ulasan_tempat`, `id_user`, `id_pemesanan`, `nama_reviewer`, `rating_makanan`, `rating_tempat`, `komentar`, `dibaca`, `created_at`) VALUES
(1, NULL, NULL, 'wrkntnkintin@gmail.com', 5, 5, 'kerenn', 1, '2026-07-06 22:56:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','kasir','pengunjung') DEFAULT 'pengunjung',
  `reset_otp` varchar(6) DEFAULT NULL,
  `reset_otp_expires_at` datetime DEFAULT NULL,
  `sudah_mode_serius` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `reset_otp`, `reset_otp_expires_at`, `sudah_mode_serius`) VALUES
(1, 'admin', 'yoonskyy63@gmail.com', '$2y$10$yt3h70JgHXAuj76M2xjmCe3dJkkIMVY7Y90zJCwvYwQm6YnIYrfpK', 'admin', NULL, NULL, 1),
(2, 'admin2', NULL, '12345', 'admin', NULL, NULL, 0),
(3, 'kasir1', NULL, '12345', 'kasir', NULL, NULL, 0),
(4, 'kasir2', NULL, '12345', 'kasir', NULL, NULL, 0),
(5, 'pengunjung1', NULL, '12345', 'pengunjung', NULL, NULL, 0),
(6, 'pengunjung2', NULL, '$2y$10$sxI0Hrs3vB8PsYnT05lUjeqGcoYXIAqnjC5uT.uEDCUyCiWH1ROZO', 'pengunjung', NULL, NULL, 0),
(9, 'Gio', 'twendexo85@gmail.com', '$2y$10$54LKGHtR2c/ktfTXrmYuRutIDv3cV9NtmQM3eFI7xPhKhJYdfCD8C', 'pengunjung', NULL, NULL, 0);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`id_booking`),
  ADD KEY `id_member` (`id_member`),
  ADD KEY `id_meja` (`id_meja`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `chat_memori`
--
ALTER TABLE `chat_memori`
  ADD PRIMARY KEY (`id_memori`),
  ADD UNIQUE KEY `unik_user` (`id_user`),
  ADD UNIQUE KEY `unik_guest` (`guest_token`);

--
-- Indeks untuk tabel `chat_pesan`
--
ALTER TABLE `chat_pesan`
  ADD PRIMARY KEY (`id_pesan`),
  ADD KEY `id_sesi` (`id_sesi`);

--
-- Indeks untuk tabel `chat_sesi`
--
ALTER TABLE `chat_sesi`
  ADD PRIMARY KEY (`id_sesi`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `guest_token` (`guest_token`);

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
-- Indeks untuk tabel `ig_stats`
--
ALTER TABLE `ig_stats`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indeks untuk tabel `kontak`
--
ALTER TABLE `kontak`
  ADD PRIMARY KEY (`id_kontak`);

--
-- Indeks untuk tabel `meja`
--
ALTER TABLE `meja`
  ADD PRIMARY KEY (`id_meja`),
  ADD UNIQUE KEY `nomor_meja` (`nomor_meja`);

--
-- Indeks untuk tabel `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`id_member`),
  ADD UNIQUE KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `menu_highlight_foto`
--
ALTER TABLE `menu_highlight_foto`
  ADD PRIMARY KEY (`id_foto`);

--
-- Indeks untuk tabel `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD PRIMARY KEY (`id_pemesanan`),
  ADD UNIQUE KEY `kode_pesanan` (`kode_pesanan`),
  ADD KEY `fk_member` (`id_member`),
  ADD KEY `fk_pemesanan_booking` (`id_booking`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD KEY `fk_produk_kategori` (`id_kategori`);

--
-- Indeks untuk tabel `promo`
--
ALTER TABLE `promo`
  ADD PRIMARY KEY (`id_promo`),
  ADD UNIQUE KEY `kode_promo` (`kode_promo`);

--
-- Indeks untuk tabel `promo_klaim`
--
ALTER TABLE `promo_klaim`
  ADD PRIMARY KEY (`id_klaim`),
  ADD UNIQUE KEY `unik_promo_member` (`id_promo`,`id_member`),
  ADD KEY `fk_klaim_promo` (`id_promo`),
  ADD KEY `fk_klaim_member` (`id_member`);

--
-- Indeks untuk tabel `riwayat_poin`
--
ALTER TABLE `riwayat_poin`
  ADD PRIMARY KEY (`id_riwayat`),
  ADD KEY `fk_riwayat_member` (`id_member`);

--
-- Indeks untuk tabel `ulasan_produk`
--
ALTER TABLE `ulasan_produk`
  ADD PRIMARY KEY (`id_ulasan`),
  ADD KEY `fk_ulasanproduk_produk` (`id_produk`),
  ADD KEY `fk_ulasanproduk_user` (`id_user`);

--
-- Indeks untuk tabel `ulasan_tempat`
--
ALTER TABLE `ulasan_tempat`
  ADD PRIMARY KEY (`id_ulasan_tempat`),
  ADD KEY `fk_ulasantempat_user` (`id_user`),
  ADD KEY `fk_ulasantempat_pemesanan` (`id_pemesanan`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `booking`
--
ALTER TABLE `booking`
  MODIFY `id_booking` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `chat_memori`
--
ALTER TABLE `chat_memori`
  MODIFY `id_memori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `chat_pesan`
--
ALTER TABLE `chat_pesan`
  MODIFY `id_pesan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `chat_sesi`
--
ALTER TABLE `chat_sesi`
  MODIFY `id_sesi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `detail_pemesanan`
--
ALTER TABLE `detail_pemesanan`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `galeri`
--
ALTER TABLE `galeri`
  MODIFY `id_galeri` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ig_stats`
--
ALTER TABLE `ig_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `kontak`
--
ALTER TABLE `kontak`
  MODIFY `id_kontak` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `meja`
--
ALTER TABLE `meja`
  MODIFY `id_meja` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `member`
--
ALTER TABLE `member`
  MODIFY `id_member` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `menu_highlight_foto`
--
ALTER TABLE `menu_highlight_foto`
  MODIFY `id_foto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT untuk tabel `pemesanan`
--
ALTER TABLE `pemesanan`
  MODIFY `id_pemesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `promo`
--
ALTER TABLE `promo`
  MODIFY `id_promo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `promo_klaim`
--
ALTER TABLE `promo_klaim`
  MODIFY `id_klaim` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `riwayat_poin`
--
ALTER TABLE `riwayat_poin`
  MODIFY `id_riwayat` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ulasan_produk`
--
ALTER TABLE `ulasan_produk`
  MODIFY `id_ulasan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ulasan_tempat`
--
ALTER TABLE `ulasan_tempat`
  MODIFY `id_ulasan_tempat` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`id_member`) REFERENCES `member` (`id_member`) ON DELETE SET NULL,
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`id_meja`) REFERENCES `meja` (`id_meja`) ON DELETE SET NULL,
  ADD CONSTRAINT `booking_ibfk_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `detail_pemesanan`
--
ALTER TABLE `detail_pemesanan`
  ADD CONSTRAINT `detail_pemesanan_ibfk_1` FOREIGN KEY (`id_pemesanan`) REFERENCES `pemesanan` (`id_pemesanan`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_pemesanan_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `member`
--
ALTER TABLE `member`
  ADD CONSTRAINT `fk_member_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD CONSTRAINT `fk_member` FOREIGN KEY (`id_member`) REFERENCES `member` (`id_member`),
  ADD CONSTRAINT `fk_pemesanan_booking` FOREIGN KEY (`id_booking`) REFERENCES `booking` (`id_booking`),
  ADD CONSTRAINT `fk_pemesanan_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `promo_klaim`
--
ALTER TABLE `promo_klaim`
  ADD CONSTRAINT `fk_klaim_member` FOREIGN KEY (`id_member`) REFERENCES `member` (`id_member`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_klaim_promo` FOREIGN KEY (`id_promo`) REFERENCES `promo` (`id_promo`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `riwayat_poin`
--
ALTER TABLE `riwayat_poin`
  ADD CONSTRAINT `fk_riwayat_member` FOREIGN KEY (`id_member`) REFERENCES `member` (`id_member`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `ulasan_produk`
--
ALTER TABLE `ulasan_produk`
  ADD CONSTRAINT `fk_ulasanproduk_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ulasanproduk_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `ulasan_tempat`
--
ALTER TABLE `ulasan_tempat`
  ADD CONSTRAINT `fk_ulasantempat_pemesanan` FOREIGN KEY (`id_pemesanan`) REFERENCES `pemesanan` (`id_pemesanan`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ulasantempat_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
