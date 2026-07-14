<?php
/**
 * staff_guard.php
 * Halaman back-office (kelola produk, kategori, meja, booking, member,
 * pemesanan, promo, galeri, kontak, dsb) sebelumnya HANYA mengecek
 * `isset($_SESSION['username'])`, tanpa mengecek kolom `role` di tabel
 * `users`. Akibatnya akun 'pengunjung' (pelanggan biasa yang daftar
 * lewat register.php) bisa membuka semua halaman admin lewat URL
 * langsung, walau tidak ada tombol/menu ke sana.
 *
 * Fungsi ini menegakkan bahwa hanya role 'admin' dan 'kasir' (staff)
 * yang boleh mengakses halaman back-office. Pengunjung yang login akan
 * diarahkan ke halaman member miliknya sendiri.
 */
function require_staff_login(string $login_path = '../auth/login.php', string $member_path = '../member/member.php'): void {
    if (!isset($_SESSION['username'])) {
        header("Location: $login_path");
        exit();
    }

    $role = $_SESSION['role'] ?? 'pengunjung';

    if (!in_array($role, ['admin', 'kasir'], true)) {
        // Login valid, tapi bukan staff -> bukan halaman untuk dia.
        header("Location: $member_path");
        exit();
    }
}
