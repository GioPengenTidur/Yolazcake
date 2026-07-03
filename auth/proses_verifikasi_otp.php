<?php
session_start();
include "../config/koneksi.php";

header('Content-Type: application/json');

$email = trim($_POST['email'] ?? '');
$otp   = trim($_POST['otp'] ?? '');

function respond_json($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit();
}

if ($email === '' || $otp === '') {
    respond_json(false, 'Email dan kode OTP wajib diisi.');
}

$stmt = $conn->prepare("SELECT id, reset_otp, reset_otp_expires_at FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || empty($user['reset_otp'])) {
    respond_json(false, 'Silakan minta kode OTP baru.');
}

if (empty($user['reset_otp_expires_at']) || strtotime($user['reset_otp_expires_at']) < time()) {
    respond_json(false, 'Kode OTP sudah kedaluwarsa. Silakan minta kode baru.');
}

if (!hash_equals((string) $user['reset_otp'], $otp)) {
    respond_json(false, 'Kode OTP salah. Periksa kembali email Anda.');
}

// OTP valid: tandai sesi sudah terverifikasi supaya user boleh lanjut ke
// halaman set password baru tanpa perlu memasukkan OTP lagi.
$_SESSION['reset_password_email']       = $email;
$_SESSION['reset_password_verified_at'] = time();

respond_json(true, 'Kode OTP berhasil diverifikasi.');
