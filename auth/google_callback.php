<?php
/**
 * Callback yang dipanggil Google setelah user menyetujui/menolak login.
 * Tugas: tukar "code" jadi access token, ambil profil, cari/buat user,
 * lalu login-kan dan redirect balik ke index.php (atau tujuan semula).
 */
session_start();
require_once '../config/google_config.php';
require_once '../config/koneksi.php';
require_once '../config/remember_me_helper.php';

/** Halaman error singkat, senada tema login (gold/glassmorphism), pakai ikon Lucide. */
function google_login_fail(string $message): void {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Login Google Gagal – YOLAZCAKE Sintang</title>
      <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
      <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
          min-height:100vh; display:flex; align-items:center; justify-content:center;
          font-family:'Inter',sans-serif;
          background: linear-gradient(135deg, #2b1a11 0%, #4a2c1a 40%, #3a1f0e 70%, #1e0e3a 100%);
        }
        .box {
          width: 100%; max-width: 400px; margin: 20px;
          background: rgba(255,255,255,0.06);
          backdrop-filter: blur(24px);
          border: 1px solid rgba(255,255,255,0.12);
          border-radius: 24px;
          padding: 40px 32px;
          text-align: center;
          box-shadow: 0 30px 80px rgba(0,0,0,0.45), 0 0 40px rgba(212,175,55,0.12);
        }
        .icon-wrap {
          width: 64px; height: 64px; margin: 0 auto 18px;
          border-radius: 50%;
          background: rgba(238,42,123,0.15);
          border: 1px solid rgba(238,42,123,0.35);
          display: flex; align-items: center; justify-content: center;
          color: #ff8ab5;
        }
        .icon-wrap svg { width: 30px; height: 30px; }
        h1 {
          font-family:'Playfair Display', serif; font-size: 1.3em; color:#fff; margin-bottom: 10px;
        }
        p { color: rgba(255,255,255,0.6); font-size:0.9em; line-height:1.6; margin-bottom:26px; }
        a.btn {
          display: inline-flex; align-items:center; gap:8px; justify-content:center;
          padding: 12px 24px; border-radius: 12px; text-decoration:none;
          background: linear-gradient(135deg, #D4AF37, #b8922a);
          color: #1e0e0a; font-weight:700; font-family:'Playfair Display', serif;
        }
        a.btn svg { width:16px; height:16px; }
      </style>
    </head>
    <body>
      <div class="box">
        <div class="icon-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m10.24 3.957-8.196 13.5A1.914 1.914 0 0 0 3.734 20.5h16.532a1.914 1.914 0 0 0 1.69-3.043l-8.196-13.5a1.914 1.914 0 0 0-3.516 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
        </div>
        <h1>Login Google Gagal</h1>
        <p><?= htmlspecialchars($message, ENT_QUOTES) ?></p>
        <a class="btn" href="login.php">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
          Kembali ke Login
        </a>
      </div>
    </body>
    </html>
    <?php
    exit;
}

// User menolak izin akses di layar consent Google.
if (isset($_GET['error'])) {
    google_login_fail('Kamu membatalkan proses login dengan Google. Silakan coba lagi atau gunakan username/password.');
}

// Validasi state anti-CSRF.
$state = $_GET['state'] ?? '';
if (empty($_SESSION['google_oauth_state']) || !hash_equals($_SESSION['google_oauth_state'], $state)) {
    google_login_fail('Sesi login Google sudah kedaluwarsa. Silakan coba lagi dari halaman login.');
}
unset($_SESSION['google_oauth_state']);

$code = $_GET['code'] ?? '';
if (empty($code)) {
    google_login_fail('Kode otorisasi dari Google tidak ditemukan. Silakan coba lagi.');
}

// ── Tukar authorization code -> access token ──
$tokenPayload = http_build_query([
    'code'          => $code,
    'client_id'     => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'grant_type'    => 'authorization_code',
]);

$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $tokenPayload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
]);
$tokenResponse = curl_exec($ch);
$tokenHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$tokenData = json_decode((string) $tokenResponse, true);

if ($tokenHttpCode !== 200 || empty($tokenData['access_token'])) {
    google_login_fail('Gagal terhubung ke Google. Pastikan koneksi internet stabil dan Client ID/Secret sudah dikonfigurasi dengan benar di config/google_config.php.');
}

// ── Ambil profil user dari Google ──
$ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $tokenData['access_token']],
]);
$profileResponse = curl_exec($ch);
$profileHttpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$profile = json_decode((string) $profileResponse, true);

if ($profileHttpCode !== 200 || empty($profile['sub']) || empty($profile['email'])) {
    google_login_fail('Gagal mengambil data profil dari akun Google kamu. Silakan coba lagi.');
}

$googleId = $profile['sub'];
$email    = $profile['email'];
$name     = $profile['name'] ?? explode('@', $email)[0];
$picture  = $profile['picture'] ?? null;

// ── Cari user: 1) berdasarkan google_id, 2) berdasarkan email (akun lama) ──
$stmt = $conn->prepare("SELECT * FROM users WHERE google_id = ? LIMIT 1");
$stmt->bind_param("s", $googleId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user) {
        // Akun lama dengan email sama sudah ada -> tautkan google_id-nya.
        $stmt = $conn->prepare("UPDATE users SET google_id = ?, foto_profil = COALESCE(foto_profil, ?) WHERE id = ?");
        $stmt->bind_param("ssi", $googleId, $picture, $user['id']);
        $stmt->execute();
        $stmt->close();
    }
}

if (!$user) {
    // Belum ada akun sama sekali -> daftarkan otomatis, role default pengunjung.
    $baseUsername = preg_replace('/[^a-zA-Z0-9_]/', '', explode('@', $email)[0]);
    if ($baseUsername === '') $baseUsername = 'user';
    $username = $baseUsername;
    $suffix = 1;
    while (true) {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();
        $taken = $check->num_rows > 0;
        $check->close();
        if (!$taken) break;
        $username = $baseUsername . $suffix;
        $suffix++;
    }

    $role = 'pengunjung';
    $stmt = $conn->prepare("INSERT INTO users (username, email, google_id, foto_profil, role, password) VALUES (?, ?, ?, ?, ?, NULL)");
    $stmt->bind_param("sssss", $username, $email, $googleId, $picture, $role);
    $stmt->execute();
    $newId = $stmt->insert_id;
    $stmt->close();

    $user = ['id' => $newId, 'username' => $username, 'email' => $email, 'role' => $role];
}

// ── Login-kan user ──
$_SESSION['username'] = $user['username'];
$_SESSION['role']     = $user['role'] ?? 'pengunjung';
$_SESSION['email']    = $user['email'] ?? $email;
$_SESSION['user_id']  = $user['id'];

// Login via Google dianggap seperti mencentang "Ingat saya" secara default,
// supaya user tidak perlu login ulang tiap buka browser.
remember_me_create($conn, (int) $user['id']);

$redirectTarget = $_SESSION['google_oauth_redirect'] ?? '../index.php';
unset($_SESSION['google_oauth_redirect']);

header('Location: ' . $redirectTarget);
exit;
