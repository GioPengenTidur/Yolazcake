<?php
session_start();
include '../config/koneksi.php';
require_once '../config/ulasan_helper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}

// Harus login untuk memberi ulasan (mencegah spam ulasan anonim).
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu untuk memberi ulasan.', 'need_login' => true]);
    exit;
}

$id_produk = (int)($_POST['id_produk'] ?? 0);
$rating    = (int)($_POST['rating'] ?? 0);
$komentar  = trim($_POST['komentar'] ?? '');
$nama      = $_SESSION['username'];
$id_user   = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

if ($id_produk <= 0) {
    echo json_encode(['success' => false, 'message' => 'Produk tidak valid.']);
    exit;
}

$hasil = simpan_ulasan_produk($conn, $id_produk, $id_user, $nama, $rating, $komentar);

$ringkasan = get_ringkasan_rating_produk($conn, $id_produk);

echo json_encode([
    'success'   => $hasil['ok'],
    'message'   => $hasil['pesan'],
    'avg'       => $ringkasan['avg'],
    'jumlah'    => $ringkasan['jumlah'],
]);
