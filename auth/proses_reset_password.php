<?php
session_start();
include "../config/koneksi.php";

header('Content-Type: application/json');

$email             = trim($_POST['email'] ?? '');
$password          = $_POST['password'] ?? '';
$confirm_password  = $_POST['confirm_password'] ?? '';

function respond_json($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit();
}

// Berlaku hanya 10 menit sejak OTP diverifikasi, dan email harus cocok
// dengan sesi yang sudah lolos verifikasi OTP (proses_verifikasi_otp.php).
$sessionEmail    = $_SESSION['reset_password_email'] ?? null;
$verifiedAt      = $_SESSION['reset_password_verified_at'] ?? 0;
$sessionExpired  = (time() - $verifiedAt) > (10 * 60);

if (!$sessionEmail || $sessionEmail !== $email || $sessionExpired) {
    respond_json(false, 'Sesi verifikasi tidak valid atau sudah kedaluwarsa. Silakan ulangi dari awal.');
}

if ($password === '' || $confirm_password === '') {
    respond_json(false, 'Semua kolom wajib diisi.');
}

if (strlen($password) < 6) {
    respond_json(false, 'Password minimal 6 karakter.');
}

if ($password !== $confirm_password) {
    respond_json(false, 'Konfirmasi password tidak sama.');
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

$upd = $conn->prepare("UPDATE users SET password = ?, reset_otp = NULL, reset_otp_expires_at = NULL WHERE email = ?");
$upd->bind_param("ss", $hashed, $email);

if ($upd->execute()) {
    // Bersihkan sesi verifikasi supaya tidak bisa dipakai ulang
    unset($_SESSION['reset_password_email'], $_SESSION['reset_password_verified_at']);
    respond_json(true, 'Password berhasil diperbarui.', ['redirect' => 'login.php']);
} else {
    respond_json(false, 'Terjadi kesalahan, coba lagi.');
}
