<?php
session_start();
include "../config/koneksi.php";

header('Content-Type: application/json');

const OTP_MAX_ATTEMPTS = 5;

$email = trim($_POST['email'] ?? '');
$otp   = trim($_POST['otp'] ?? '');

function respond_json($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit();
}

if ($email === '' || $otp === '') {
    respond_json(false, 'Email dan kode OTP wajib diisi.');
}

$stmt = $conn->prepare("SELECT id, reset_otp, reset_otp_expires_at, reset_otp_attempts FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || empty($user['reset_otp'])) {
    respond_json(false, 'Silakan minta kode OTP baru.');
}

if (empty($user['reset_otp_expires_at']) || strtotime($user['reset_otp_expires_at']) < time()) {
    respond_json(false, 'Kode OTP sudah kedaluwarsa. Silakan minta kode baru.');
}

// Rate limit: kalau sudah gagal >= OTP_MAX_ATTEMPTS kali, OTP langsung
// dimatikan (di-null-kan) supaya tidak bisa ditebak terus lewat brute force.
// User harus minta kode baru lewat proses_lupa_password.php.
if ((int) $user['reset_otp_attempts'] >= OTP_MAX_ATTEMPTS) {
    $kill = $conn->prepare("UPDATE users SET reset_otp = NULL, reset_otp_expires_at = NULL, reset_otp_attempts = 0 WHERE id = ?");
    $kill->bind_param("i", $user['id']);
    $kill->execute();
    respond_json(false, 'Terlalu banyak percobaan salah. Kode OTP dimatikan, silakan minta kode baru.');
}

if (!hash_equals((string) $user['reset_otp'], $otp)) {
    $attempts = (int) $user['reset_otp_attempts'] + 1;

    if ($attempts >= OTP_MAX_ATTEMPTS) {
        // Percobaan salah terakhir yang diizinkan: matikan sekalian OTP-nya.
        $upd = $conn->prepare("UPDATE users SET reset_otp = NULL, reset_otp_expires_at = NULL, reset_otp_attempts = 0 WHERE id = ?");
        $upd->bind_param("i", $user['id']);
        $upd->execute();
        respond_json(false, 'Kode OTP salah. Batas percobaan tercapai, kode dimatikan — silakan minta kode baru.');
    }

    $upd = $conn->prepare("UPDATE users SET reset_otp_attempts = ? WHERE id = ?");
    $upd->bind_param("ii", $attempts, $user['id']);
    $upd->execute();

    $sisa = OTP_MAX_ATTEMPTS - $attempts;
    respond_json(false, "Kode OTP salah. Periksa kembali email Anda. Sisa percobaan: {$sisa}.");
}

// OTP benar: reset counter supaya bersih untuk sesi reset password berikutnya.
$reset = $conn->prepare("UPDATE users SET reset_otp_attempts = 0 WHERE id = ?");
$reset->bind_param("i", $user['id']);
$reset->execute();

// OTP valid: tandai sesi sudah terverifikasi supaya user boleh lanjut ke
// halaman set password baru tanpa perlu memasukkan OTP lagi.
$_SESSION['reset_password_email']       = $email;
$_SESSION['reset_password_verified_at'] = time();

respond_json(true, 'Kode OTP berhasil diverifikasi.');
