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

$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role     = $_POST['role'] ?? '';

$allowedRoles = ['admin', 'kasir', 'pengunjung'];

if ($username === '' || strlen($username) < 3) {
    respond_json(false, 'Username wajib diisi, minimal 3 karakter.');
}
if (!preg_match('/^[a-zA-Z0-9_.]+$/', $username)) {
    respond_json(false, 'Username hanya boleh huruf, angka, titik, dan underscore.');
}
if (strlen($password) < 6) {
    respond_json(false, 'Password minimal 6 karakter.');
}
if (!in_array($role, $allowedRoles, true)) {
    respond_json(false, 'Peran tidak dikenali.');
}
if ($email !== '' && (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/@gmail\.com$/i', $email))) {
    respond_json(false, 'Email harus berupa alamat @gmail.com yang valid.');
}

// Pastikan username belum dipakai
$cekUser = $conn->prepare("SELECT id FROM users WHERE username = ?");
$cekUser->bind_param("s", $username);
$cekUser->execute();
$cekUser->store_result();
if ($cekUser->num_rows > 0) {
    respond_json(false, 'Username sudah dipakai, silakan pilih yang lain.');
}
$cekUser->close();

// Pastikan email (jika diisi) belum dipakai akun lain
if ($email !== '') {
    $cekEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $cekEmail->bind_param("s", $email);
    $cekEmail->execute();
    $cekEmail->store_result();
    if ($cekEmail->num_rows > 0) {
        respond_json(false, 'Email sudah dipakai akun lain.');
    }
    $cekEmail->close();
}

$hashed      = password_hash($password, PASSWORD_DEFAULT);
$emailToSave = $email !== '' ? $email : null;

$ins = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
$ins->bind_param("ssss", $username, $emailToSave, $hashed, $role);

if ($ins->execute()) {
    respond_json(true, 'Akun baru berhasil dibuat.', ['redirect' => 'data_user.php?ok=tambah']);
} else {
    respond_json(false, 'Terjadi kesalahan saat menyimpan, coba lagi.');
}
