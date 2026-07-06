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

require_once '../config/koneksi.php';

$id = (int) ($_POST['id'] ?? 0);

$allowedRoles = ['admin', 'kasir', 'pengunjung'];
$newRole = $_POST['role'] ?? '';
if (!in_array($newRole, $allowedRoles, true)) {
    respond_json(false, 'Pangkat tidak dikenali.');
}

// Ambil data akun yang mau diubah
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$target = $stmt->get_result()->fetch_assoc();

if (!$target) {
    respond_json(false, 'Akun tidak ditemukan.');
}

// Tidak boleh ubah pangkat akun sendiri (cegah kunci diri sendiri keluar)
if ($target['username'] === $_SESSION['username']) {
    respond_json(false, 'Tidak bisa mengubah pangkat akun yang sedang kamu pakai sendiri.');
}

// Kalau akun ini admin dan mau diturunkan, pastikan masih ada admin lain
if ($target['role'] === 'admin' && $newRole !== 'admin') {
    $cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM users WHERE role='admin'"));
    if (($cek['t'] ?? 0) <= 1) {
        respond_json(false, 'Tidak bisa menurunkan admin terakhir. Minimal harus ada 1 admin.');
    }
}

if ($target['role'] === $newRole) {
    respond_json(true, 'Pangkat tidak berubah.', ['role' => $newRole]);
}

$upd = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
$upd->bind_param("si", $newRole, $id);

if ($upd->execute()) {
    respond_json(true, 'Pangkat akun berhasil diubah.', ['role' => $newRole]);
} else {
    respond_json(false, 'Terjadi kesalahan saat menyimpan, coba lagi.');
}
