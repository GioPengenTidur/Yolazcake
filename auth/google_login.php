<?php
/**
 * Titik masuk tombol "Google" di login.php.
 * Membuat state anti-CSRF, lalu redirect user ke halaman consent Google.
 */
session_start();
require_once '../config/google_config.php';
require_once '../config/safe_redirect.php';

// Simpan tujuan redirect (mis. balik ke booking.php) supaya tetap terbawa
// setelah proses Google OAuth selesai di google_callback.php.
$carryRedirect = safe_redirect_target($_GET['redirect'] ?? null);
if ($carryRedirect) {
    $_SESSION['google_oauth_redirect'] = $carryRedirect;
} else {
    unset($_SESSION['google_oauth_redirect']);
}

// State token: dicocokkan lagi di google_callback.php supaya request
// callback yang datang bukan hasil forgery pihak luar.
$state = bin2hex(random_bytes(16));
$_SESSION['google_oauth_state'] = $state;

$params = http_build_query([
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope'         => 'openid email profile',
    'state'         => $state,
    'prompt'        => 'select_account',
]);

header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $params);
exit;
