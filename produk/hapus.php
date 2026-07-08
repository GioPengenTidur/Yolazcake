<?php
session_start();
require_once __DIR__.'/../config/staff_guard.php';
require_staff_login();
require_once __DIR__.'/../config/csrf_helper.php';
csrf_verify_or_die($_GET['csrf'] ?? null, 'data_produk.php');
include '../config/koneksi.php';
include 'success_overlay.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM produk WHERE id_produk = ?");

$stmt->bind_param("i", $id);

$stmt->execute();
$result = $stmt->get_result();
$produk = $result->fetch_assoc();

$nama_produk = $produk['nama_produk'] ?? 'Produk';

if(!empty($produk['foto']) && file_exists("../assets/img/produk/".$produk['foto'])){
    unlink("../assets/img/produk/".$produk['foto']);
}

$del = $conn->prepare("DELETE FROM produk WHERE id_produk = ?");
$del->bind_param("i", $id);
$del->execute();

tampilkan_sukses([
    'proses_judul' => 'Menghapus Produk…',
    'proses_sub'   => 'Sedang memproses penghapusan data',
    'sukses_judul' => 'Produk Berhasil Dihapus!',
    'sukses_sub'   => '"'.htmlspecialchars($nama_produk).'" telah dihapus dari katalog',
    'redirect'     => 'data_produk.php',
    'tombol_label' => 'Lanjutkan ke Data Produk',
]);
exit;
