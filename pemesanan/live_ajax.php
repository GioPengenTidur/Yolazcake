<?php
session_start();
header('Content-Type: application/json');
include '../config/koneksi.php';

// Staff-only, tapi jangan redirect ke halaman login (ini endpoint JSON) --
// cukup balikin 403 kalau bukan staff.
$role = $_SESSION['role'] ?? 'pengunjung';
if (!isset($_SESSION['username']) || !in_array($role, ['admin', 'kasir'], true)) {
    http_response_code(403);
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$pesanan = mysqli_query($conn, "
    SELECT id_pemesanan, kode_pesanan, nama_pemesan, nomor_meja, total_harga,
           status_pesanan, status_pembayaran, tanggal
    FROM pemesanan
    WHERE status_pesanan IN ('Menunggu','Diproses','Siap Diambil')
    ORDER BY tanggal ASC
");
$list_pesanan = [];
while ($row = mysqli_fetch_assoc($pesanan)) {
    $list_pesanan[] = $row;
}

$meja = mysqli_query($conn, "SELECT id_meja, nomor_meja, kapasitas, status FROM meja ORDER BY nomor_meja ASC");
$list_meja = [];
while ($row = mysqli_fetch_assoc($meja)) {
    $list_meja[] = $row;
}

echo json_encode([
    'pesanan' => $list_pesanan,
    'meja'    => $list_meja,
    'waktu'   => date('H:i:s'),
]);
