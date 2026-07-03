<?php
session_start();
include "../config/koneksi.php";
require_once "../config/mailer.php";

header('Content-Type: application/json');

$email = trim($_POST['email'] ?? '');

function respond_json($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit();
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond_json(false, 'Masukkan alamat email yang valid.');
}

if (!preg_match('/@gmail\.com$/i', $email)) {
    respond_json(false, 'Email harus berupa alamat @gmail.com.');
}

// Cari akun berdasarkan email
$stmt = $conn->prepare("SELECT id, username, email FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    respond_json(false, 'Email tersebut tidak terdaftar di sistem kami.');
}

// Generate OTP 6 digit
$otp        = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expiresAt  = date('Y-m-d H:i:s', time() + (OTP_EXPIRE_MINUTES * 60));

$upd = $conn->prepare("UPDATE users SET reset_otp = ?, reset_otp_expires_at = ? WHERE id = ?");
$upd->bind_param("ssi", $otp, $expiresAt, $user['id']);
$upd->execute();

// Kirim email
$kirim = kirimEmailOtpResetPassword($user['email'], $user['username'], $otp);

if (!$kirim['success']) {
    respond_json(false, $kirim['message']);
}

// Email tersamar untuk ditampilkan di UI, mis. na**@gmail.com
$maskedEmail = preg_replace_callback('/^(.{2}).*(@.*)$/', function ($m) {
    return $m[1] . str_repeat('*', 4) . $m[2];
}, $user['email']);

respond_json(true, 'Kode OTP berhasil dikirim ke email Anda.', ['masked_email' => $maskedEmail]);
