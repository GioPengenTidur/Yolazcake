<?php
session_start();
include '../config/koneksi.php';
require_once '../config/ulasan_helper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}

$rating_makanan = (int)($_POST['rating_makanan'] ?? 0);
$rating_tempat  = (int)($_POST['rating_tempat'] ?? 0);
$komentar       = trim($_POST['komentar'] ?? '');
$id_pemesanan   = isset($_POST['id_pemesanan']) && $_POST['id_pemesanan'] !== '' ? (int)$_POST['id_pemesanan'] : null;

$id_user = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$nama    = $_SESSION['username'] ?? ($_POST['nama'] ?? 'Pelanggan');

$hasil = simpan_ulasan_tempat($conn, $id_user, $id_pemesanan, $nama, $rating_makanan, $rating_tempat, $komentar);

echo json_encode([
    'success' => $hasil['ok'],
    'message' => $hasil['pesan'],
]);
