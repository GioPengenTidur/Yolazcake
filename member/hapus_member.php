<?php
include '../config/koneksi.php';
include 'success_overlay.php';

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM member WHERE id_member = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();

$nama = $member['nama'] ?? 'Member';

mysqli_query(
    $conn,
    "DELETE FROM member WHERE id_member='$id'"
);

tampilkan_sukses([
    'proses_judul' => 'Menghapus Member…',
    'proses_sub'   => 'Sedang memproses penghapusan data member',
    'sukses_judul' => 'Member Berhasil Dihapus!',
    'sukses_sub'   => '"'.htmlspecialchars($nama).'" telah dihapus dari data member',
    'redirect'     => 'data_member.php',
    'tombol_label' => 'Lanjutkan ke Data Member',
]);
exit;
?>