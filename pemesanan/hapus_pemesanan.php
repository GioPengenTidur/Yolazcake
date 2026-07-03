<?php
include '../config/koneksi.php';
include 'success_overlay.php';

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM pemesanan WHERE id_pemesanan = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$kode_pesanan = $data['kode_pesanan'] ?? 'Pesanan';

mysqli_query(
$conn,
"DELETE FROM detail_pemesanan
WHERE id_pemesanan='$id'"
);

mysqli_query(
$conn,
"DELETE FROM pemesanan
WHERE id_pemesanan='$id'"
);

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