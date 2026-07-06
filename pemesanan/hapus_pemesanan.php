<?php
session_start();
require_once __DIR__.'/../config/staff_guard.php';
require_staff_login();
include '../config/koneksi.php';
include 'success_overlay.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM pemesanan WHERE id_pemesanan = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$kode_pesanan = $data['kode_pesanan'] ?? 'Pesanan';

$del1 = $conn->prepare("DELETE FROM detail_pemesanan WHERE id_pemesanan = ?");
$del1->bind_param("i", $id);
$del1->execute();

$del2 = $conn->prepare("DELETE FROM pemesanan WHERE id_pemesanan = ?");
$del2->bind_param("i", $id);
$del2->execute();

tampilkan_sukses([
    'proses_judul' => 'Menghapus Pemesanan…',
    'proses_sub'   => 'Sedang memproses penghapusan data pesanan',
    'sukses_judul' => 'Pemesanan Berhasil Dihapus!',
    'sukses_sub'   => 'Pesanan "'.htmlspecialchars($kode_pesanan).'" telah dihapus dari data',
    'redirect'     => 'data_pemesanan.php',
    'tombol_label' => 'Lanjutkan ke Data Pemesanan',
]);
exit;
?>