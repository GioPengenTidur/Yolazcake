<?php
/**
 * safe_redirect.php
 * -----------------------------------------------------------------------
 * Helper kecil buat memvalidasi parameter "redirect" yang dikirim lewat
 * form login/register (mis. supaya user yang tadinya mau booking meja
 * otomatis balik ke booking.php setelah login/daftar berhasil).
 *
 * Dipakai bareng oleh auth/proses_login.php dan auth/proses_register.php
 * supaya aturan validasinya konsisten -- HANYA path relatif di dalam
 * project sendiri yang boleh, supaya tidak bisa disalahgunakan buat
 * open-redirect ke situs luar.
 */

function safe_redirect_target(?string $target): ?string
{
    if (!$target) {
        return null;
    }
    // Harus path relatif "../xxx/xxx.php" (boleh ada query string sederhana),
    // tidak boleh mengandung "://", tidak boleh diawali "//" (protocol-relative URL).
    if (preg_match('#^\.\./[A-Za-z0-9_\-/]+\.php(\?[A-Za-z0-9_=&%.\-]*)?$#', $target)) {
        return $target;
    }
    return null;
}

/** Whitelist notice yang boleh ditampilkan di halaman login (bukan free-text). */
function safe_login_notice(?string $notice): ?string
{
    $allowed = ['booking'];
    return in_array($notice, $allowed, true) ? $notice : null;
}
