<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location: ../auth/login.php");
    exit();
}
if(($_SESSION['role'] ?? '') !== 'admin'){
    header("Location: ../dashboard.php");
    exit();
}
require_once '../config/koneksi.php';

$id      = (int)($_GET['id'] ?? 0);
$newRole = ($_GET['role'] ?? '') === 'admin' ? 'admin' : 'kasir';

// Ambil data akun yang mau diubah
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$target = $stmt->get_result()->fetch_assoc();

if (!$target) {
    header("Location: data_user.php?err=notfound");
    exit();
}

// Tidak boleh ubah role akun sendiri (cegah kunci diri sendiri keluar)
if ($target['username'] === $_SESSION['username']) {
    header("Location: data_user.php?err=self");
    exit();
}

// Kalau mau turunkan admin jadi kasir, pastikan masih ada admin lain
if ($target['role'] === 'admin' && $newRole === 'kasir') {
    $cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM users WHERE role='admin'"));
    if (($cek['t'] ?? 0) <= 1) {
        header("Location: data_user.php?err=last_admin");
        exit();
    }
}

$upd = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
$upd->bind_param("si", $newRole, $id);
$upd->execute();

header("Location: data_user.php?ok=role");
exit();
