<?php
/**
 * Dipanggil saat user klik "Lanjutkan sebagai ..." di popup konfirmasi
 * remember-me. Baru di titik inilah sesi login benar-benar di-set.
 */
session_start();

if (isset($_SESSION['pending_remember_user'])) {
    $user = $_SESSION['pending_remember_user'];

    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'] ?? 'pengunjung';
    $_SESSION['email']    = $user['email'];
    $_SESSION['user_id']  = $user['id'];

    unset($_SESSION['pending_remember_user']);
}

header('Location: ../index.php');
exit;
