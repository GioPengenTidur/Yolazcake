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

if ($valid) {

    $_SESSION['username'] = $username;
    $_SESSION['role'] = $user['role'] ?? 'kasir';
    respond($is_ajax, true, 'Login berhasil.', '../index.php');

} else {

    respond($is_ajax, false, 'Username atau password tidak sesuai.');

}
