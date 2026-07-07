<?php

session_start();
include "../config/koneksi.php";

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Deteksi apakah request datang dari AJAX (fetch) atau submit form biasa
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

function respond($is_ajax, $success, $message, $redirect = null) {
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
        header("Location: login.php?error=1");
    }
    exit();
}

// Ambil user berdasarkan username ATAU email (gmail), password dicek manual
// di bawah supaya tetap kompatibel dengan akun lama (password polos) maupun
// akun baru dari halaman daftar (password sudah di-hash).
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$result = $stmt->get_result();

$valid = false;
$user  = null;

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $valid = password_verify($password, $user['password']) || $password === $user['password'];
}

require_once '../config/safe_redirect.php';

// Redirect tujuan opsional (mis. balik ke booking.php setelah login berhasil).
// Divalidasi ketat lewat safe_redirect_target() supaya tidak bisa
// disalahgunakan untuk open-redirect ke domain luar.
$redirectTarget = safe_redirect_target($_POST['redirect'] ?? null);

if ($valid) {

    $_SESSION['username'] = $username;
    $_SESSION['role'] = $user['role'] ?? 'pengunjung';
    $_SESSION['email'] = $user['email'] ?? null;
    $_SESSION['user_id'] = $user['id'] ?? null;

    // Semua role (termasuk admin/kasir) mendarat di halaman utama dulu
    // setelah login, sama seperti pengunjung biasa. Untuk masuk ke panel,
    // staff tinggal klik dropdown akun di navbar -> "Dashboard", yang akan
    // mengarah ke dashboard_awal.php (Mode Dasar) seperti biasa dari sana.
    $defaultTarget = '../index.php';

    respond($is_ajax, true, 'Login berhasil.', $redirectTarget ?? $defaultTarget);

} else {

    respond($is_ajax, false, 'Username atau password tidak sesuai.');

}
