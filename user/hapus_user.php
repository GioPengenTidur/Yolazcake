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

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$target = $stmt->get_result()->fetch_assoc();

if (!$target) {
    header("Location: data_user.php?err=notfound");
    exit();
}

// Tidak boleh hapus akun sendiri
if ($target['username'] === $_SESSION['username']) {
    header("Location: data_user.php?err=self");
    exit();
}

// Tidak boleh hapus admin terakhir
if ($target['role'] === 'admin') {
    $cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM users WHERE role='admin'"));
    if (($cek['t'] ?? 0) <= 1) {
        header("Location: data_user.php?err=last_admin");
        exit();
    }
}

$del = $conn->prepare("DELETE FROM users WHERE id = ?");
$del->bind_param("i", $id);
$del->execute();

header("Location: data_user.php?ok=hapus");
exit();
