<?php
session_start();

header('Content-Type: application/json');

function respond_json($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit();
}

if (!isset($_SESSION['username'])) {
    respond_json(false, 'Sesi login tidak ditemukan, silakan login ulang.');
}
if (($_SESSION['role'] ?? '') !== 'admin') {
    respond_json(false, 'Hanya admin yang bisa melakukan aksi ini.');
}

require_once __DIR__.'/../config/csrf_helper.php';
if (!csrf_is_valid($_POST['csrf'] ?? null)) {
    respond_json(false, 'Token keamanan tidak valid. Silakan muat ulang halaman dan coba lagi.');
}

require_once '../config/koneksi.php';

$id = (int) ($_POST['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$target = $stmt->get_result()->fetch_assoc();

if (!$target) {
    respond_json(false, 'Akun tidak ditemukan.');
}

// Tidak boleh hapus akun sendiri
if ($target['username'] === $_SESSION['username']) {
    respond_json(false, 'Tidak bisa menghapus akun yang sedang kamu pakai sendiri.');
}

// Tidak boleh hapus admin terakhir
if ($target['role'] === 'admin') {
    $cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM users WHERE role='admin'"));
    if (($cek['t'] ?? 0) <= 1) {
        respond_json(false, 'Tidak bisa menghapus admin terakhir. Minimal harus ada 1 admin.');
    }
}

$del = $conn->prepare("DELETE FROM users WHERE id = ?");
$del->bind_param("i", $id);

if ($del->execute()) {
    respond_json(true, 'Akun berhasil dihapus.');
} else {
    respond_json(false, 'Terjadi kesalahan saat menghapus, coba lagi.');
}
