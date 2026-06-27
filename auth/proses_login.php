<?php

session_start();
include "../config/koneksi.php";

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Gunakan prepared statement untuk keamanan
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $_SESSION['username'] = $username;
    $_SESSION['role']     = $user['role'] ?? 'kasir';

    // Admin → dashboard, kasir → index
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../dashboard.php");
    } else {
        header("Location: ../index.php");
    }
    exit();

} else {

    // Redirect kembali ke login dengan pesan error
    header("Location: login.php?error=1");
    exit();

}

?>
