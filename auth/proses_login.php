<?php

session_start();
include "../config/koneksi.php";

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Ambil user berdasarkan username saja, password dicek manual di bawah
// supaya tetap kompatibel dengan akun lama (password polos) maupun
// akun baru dari halaman daftar (password sudah di-hash).
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
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
    header("Location: ../index.php");
    exit();

} else {

    // Redirect kembali ke login dengan pesan error
    header("Location: login.php?error=1");
    exit();

}

?>
