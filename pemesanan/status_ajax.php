<?php
/**
 * status_ajax.php
 * -----------------------------------------------------------------------
 * Endpoint publik buat polling status pesanan secara real-time (dipakai di
 * halaman sukses checkout & halaman lacak.php). Sengaja butuh KOMBINASI
 * id_pemesanan + kode_pesanan (bukan cuma id) supaya orang lain nggak bisa
 * asal tebak ID buat ngintip pesanan orang lain.
 */
session_start();
header('Content-Type: application/json');
include '../config/koneksi.php';

$id   = (int)($_GET['id'] ?? 0);
$kode = trim($_GET['kode'] ?? '');

if ($id <= 0 || $kode === '') {
    http_response_code(400);
    echo json_encode(['error' => 'parameter tidak lengkap']);
    exit;
}

$stmt = $conn->prepare("SELECT status_pesanan, status_pembayaran, nomor_meja, tanggal FROM pemesanan WHERE id_pemesanan = ? AND kode_pesanan = ?");
$stmt->bind_param("is", $id, $kode);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    http_response_code(404);
    echo json_encode(['error' => 'pesanan tidak ditemukan']);
    exit;
}

echo json_encode([
    'status_pesanan'    => $data['status_pesanan'],
    'status_pembayaran' => $data['status_pembayaran'],
    'nomor_meja'        => $data['nomor_meja'],
    'tanggal'           => $data['tanggal'],
]);
