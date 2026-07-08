<?php
/**
 * Helper CSRF sederhana berbasis session.
 * Wajib dipanggil session_start() dulu sebelum include file ini.
 *
 * Cara pakai:
 * - Di halaman yang menampilkan link/form aksi (hapus, ubah status, dll):
 *     require_once __DIR__.'/../config/csrf_helper.php';
 *     $csrf = csrf_token();
 *   lalu tempel &csrf=<?= urlencode($csrf) ?> di query string link,
 *   atau tempel sebagai field/param tersembunyi untuk request POST/fetch.
 *
 * - Di file proses yang menjalankan aksinya:
 *     require_once __DIR__.'/../config/csrf_helper.php';
 *     csrf_verify_or_die($_GET['csrf'] ?? null, 'admin_booking.php');   // untuk aksi via link (redirect balik)
 *     csrf_verify_json($_POST['csrf'] ?? null);                        // untuk endpoint JSON/AJAX
 */

// Token dibuat sekali per sesi login dan dipakai ulang untuk semua aksi
// (bukan per-form) supaya tidak perlu reload token tiap buka halaman baru.
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_is_valid($token) {
    return !empty($_SESSION['csrf_token']) && !empty($token) && hash_equals($_SESSION['csrf_token'], (string) $token);
}

// Untuk aksi yang dipicu lewat <a href="...&csrf=...">, lalu file proses
// biasanya redirect balik ke halaman asal. Kalau token tidak valid, hentikan
// proses dan tampilkan pesan singkat (bukan redirect diam-diam, supaya jelas
// kenapa aksinya gagal).
function csrf_verify_or_die($token, $redirect_on_fail = null) {
    if (!csrf_is_valid($token)) {
        http_response_code(403);
        echo 'Permintaan ditolak: token keamanan tidak valid atau sudah kedaluwarsa. Silakan muat ulang halaman dan coba lagi.';
        if ($redirect_on_fail) {
            echo '<script>setTimeout(function(){ window.location.href = '.json_encode($redirect_on_fail).'; }, 1800);</script>';
        }
        exit;
    }
}

// Untuk endpoint yang dipanggil lewat fetch()/AJAX dan membalas JSON.
function csrf_verify_json($token) {
    if (!csrf_is_valid($token)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'csrf_invalid', 'message' => 'Token keamanan tidak valid. Silakan muat ulang halaman.']);
        exit;
    }
}
