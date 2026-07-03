<?php
session_start();
include "../config/koneksi.php";

$username         = trim($_POST['username'] ?? '');
$email            = trim($_POST['email'] ?? '');
$password         = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Deteksi apakah request datang dari AJAX (fetch) atau submit form biasa
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

function respond_register($is_ajax, $success, $message, $redirect = null, $error_code = '', $username = '', $email = '') {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success'  => $success,
            'message'  => $message,
            'redirect' => $redirect,
        ]);
        exit();
    }

    // Fallback lama (tanpa JS): tetap redirect seperti sebelumnya
    if ($success) {
        header("Location: $redirect");
    } else {
        $qs = 'error=' . urlencode($error_code);
        if ($username !== '') {
            $qs .= '&username=' . urlencode($username);
        }
        if ($email !== '') {
            $qs .= '&email=' . urlencode($email);
        }
        header("Location: register.php?$qs");
    }
    exit();
}

if ($username === '' || $email === '' || $password === '' || $confirm_password === '') {
    respond_register($is_ajax, false, 'Semua kolom wajib diisi.', null, 'empty', $username, $email);
}

// Email wajib format gmail.com, sesuai fitur login/reset password via Gmail
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/@gmail\.com$/i', $email)) {
    respond_register($is_ajax, false, 'Email harus berupa alamat @gmail.com yang valid.', null, 'email', $username, $email);
}

if (strlen($password) < 6) {
    respond_register($is_ajax, false, 'Password minimal 6 karakter.', null, 'short', $username, $email);
}

if ($password !== $confirm_password) {
    respond_register($is_ajax, false, 'Konfirmasi password tidak sama.', null, 'mismatch', $username, $email);
}

// Cek username sudah dipakai atau belum
$cek = $conn->prepare("SELECT id FROM users WHERE username = ?");
$cek->bind_param("s", $username);
$cek->execute();
$cek->store_result();

if ($cek->num_rows > 0) {
    respond_register($is_ajax, false, 'Username sudah dipakai, coba yang lain.', null, 'taken', $username, $email);
}
$cek->close();

// Cek email sudah dipakai akun lain atau belum
$cekEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
$cekEmail->bind_param("s", $email);
$cekEmail->execute();
$cekEmail->store_result();

if ($cekEmail->num_rows > 0) {
    respond_register($is_ajax, false, 'Email sudah dipakai akun lain, coba yang lain.', null, 'email_taken', $username, $email);
}
$cekEmail->close();

// Simpan akun baru. Password di-hash, role default 'kasir'
// (role 'admin' tidak diberikan lewat pendaftaran mandiri).
$hashed = password_hash($password, PASSWORD_DEFAULT);
$role   = 'kasir';

$stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $username, $email, $hashed, $role);

if ($stmt->execute()) {
    respond_register($is_ajax, true, 'Akun berhasil dibuat.', 'login.php?registered=1');
} else {
    respond_register($is_ajax, false, 'Terjadi kesalahan, coba lagi.', null, 'taken', $username, $email);
}
