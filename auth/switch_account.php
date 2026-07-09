<?php
/**
 * Dipanggil saat user klik "Ganti Akun" di popup konfirmasi remember-me.
 * Hapus cookie + token remember-me yang aktif (punya akun yang "diingat"),
 * lalu lempar ke halaman login supaya bisa pilih akun lain / login manual.
 */
session_start();

require_once "../config/koneksi.php";
require_once "../config/remember_me_helper.php";

remember_me_forget($conn);
unset($_SESSION['pending_remember_user']);

header('Location: login.php');
exit;
