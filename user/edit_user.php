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

$id                = (int) ($_POST['id'] ?? 0);
$email             = trim($_POST['email'] ?? '');
$password          = $_POST['password'] ?? '';
$confirm_password  = $_POST['confirm_password'] ?? '';

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$target = $stmt->get_result()->fetch_assoc();

if (!$target) {
    respond_json(false, 'Akun tidak ditemukan.');
}

// Validasi email jika diisi
if ($email !== '') {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/@gmail\.com$/i', $email)) {
        respond_json(false, 'Email harus berupa alamat @gmail.com yang valid.');
    }

    // Pastikan email tidak dipakai akun lain
    $cekEmail = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $cekEmail->bind_param("si", $email, $id);
    $cekEmail->execute();
    $cekEmail->store_result();
    if ($cekEmail->num_rows > 0) {
        respond_json(false, 'Email sudah dipakai akun lain.');
    }
    $cekEmail->close();
}

// Validasi password baru jika diisi (opsional — admin bisa reset tanpa ubah password)
$updatePassword = false;
if ($password !== '' || $confirm_password !== '') {
    if (strlen($password) < 6) {
        respond_json(false, 'Password baru minimal 6 karakter.');
    }
    if ($password !== $confirm_password) {
        respond_json(false, 'Konfirmasi password baru tidak sama.');
    }
    $updatePassword = true;
}

$emailToSave = $email !== '' ? $email : null;

if ($updatePassword) {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $upd = $conn->prepare("UPDATE users SET email = ?, password = ?, reset_otp = NULL, reset_otp_expires_at = NULL WHERE id = ?");
    $upd->bind_param("ssi", $emailToSave, $hashed, $id);
} else {
    $upd = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
    $upd->bind_param("si", $emailToSave, $id);
}

if ($upd->execute()) {
    respond_json(true, 'Data akun berhasil diperbarui.', ['redirect' => 'data_user.php?ok=edit']);
} else {
    respond_json(false, 'Terjadi kesalahan saat menyimpan, coba lagi.');
}
