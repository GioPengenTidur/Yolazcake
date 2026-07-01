<?php
include '../config/koneksi.php';
include 'success_overlay.php';

$id = $_GET['id'];

$data = mysqli_query(
    $conn,
    "SELECT * FROM produk WHERE id_produk='$id'"
);

$produk = mysqli_fetch_assoc($data);
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
