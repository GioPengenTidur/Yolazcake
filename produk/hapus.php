<?php
include '../config/koneksi.php';
include 'success_overlay.php';

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM produk WHERE id_produk = ?");

$stmt->bind_param("i", $id);

$stmt->execute();
$result = $stmt->get_result();
$produk = $result->fetch_assoc();

$nama_produk = $produk['nama_produk'] ?? 'Produk';

if(file_exists("../assets/img/produk/".$produk['foto'])){
    unlink("../assets/img/produk/".$produk['foto']);
}

mysqli_query(
    $conn,
    "DELETE FROM produk WHERE id_produk='$id'"
);

tampilkan_sukses([
    'proses_judul' => 'Menghapus Produk…',
    'proses_sub'   => 'Sedang memproses penghapusan data',
    'sukses_judul' => 'Produk Berhasil Dihapus!',
    'sukses_sub'   => '"'.htmlspecialchars($nama_produk).'" telah dihapus dari katalog',
    'redirect'     => 'data_produk.php',
    'tombol_label' => 'Lanjutkan ke Data Produk',
]);
exit;
