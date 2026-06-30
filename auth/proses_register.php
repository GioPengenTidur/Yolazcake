<?php
session_start();
include "../config/koneksi.php";

$username         = trim($_POST['username'] ?? '');
$password         = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

function back_to_register($error, $username = '') {
    $qs = 'error=' . urlencode($error);
    if ($username !== '') {
        $qs .= '&username=' . urlencode($username);
    }
    header("Location: register.php?$qs");
    exit();
}

if ($username === '' || $password === '' || $confirm_password === '') {
    back_to_register('empty', $username);
}

if (strlen($password) < 6) {
    back_to_register('short', $username);
}

if ($password !== $confirm_password) {
    back_to_register('mismatch', $username);
}

// Cek username sudah dipakai atau belum
$cek = $conn->prepare("SELECT id FROM users WHERE username = ?");
$cek->bind_param("s", $username);
$cek->execute();
$cek->store_result();

if ($cek->num_rows > 0) {
    back_to_register('taken', $username);
}
$cek->close();

// Simpan akun baru. Password di-hash, role default 'kasir'
// (role 'admin' tidak diberikan lewat pendaftaran mandiri).
$hashed = password_hash($password, PASSWORD_DEFAULT);
$role   = 'kasir';

$stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $hashed, $role);

if ($stmt->execute()) {
    header("Location: login.php?registered=1");
    exit();
} else {
    back_to_register('taken', $username);
}
