<?php
session_start();
require_once 'config/koneksi.php';

// Ambil kode promo dari GET atau tampilkan promo aktif pertama
$kode_req = isset($_GET['kode']) ? strtoupper(trim($_GET['kode'])) : '';

if ($kode_req) {
    $stmt = $conn->prepare("SELECT * FROM promo WHERE kode_promo=? AND status='Aktif' LIMIT 1");
    $stmt->bind_param("s", $kode_req);
    $stmt->execute();
    $promo = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} else {
    // Tampilkan promo aktif pertama yang masih berlaku
    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT * FROM promo WHERE status='Aktif'
         AND (tanggal_selesai IS NULL OR tanggal_selesai >= ?)
         ORDER BY id_promo DESC LIMIT 1");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $promo = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Fallback jika tidak ada promo
$promo_is_real = (bool) $promo;
if (!$promo) {
    $promo = [
        'kode_promo'     => 'YOLA25',
        'judul'          => 'Promo Spesial YOLAZCAKE',
        'deskripsi'      => 'Dapatkan diskon eksklusif dari YOLAZCAKE Sintang.',
        'diskon_persen'  => 25,
        'min_belanja'    => 150000,
        'poin_bonus'     => 50,
        'tanggal_selesai'=> null,
    ];
}

// ─────────────────────────────────────────────────────────────
// VALIDASI SYARAT PROMO (baru): promo hanya boleh "diambil" kalau
// syaratnya benar-benar terpenuhi, bukan otomatis lolos.
// ─────────────────────────────────────────────────────────────
require_once __DIR__.'/config/member_helper.php';

$is_logged_in      = isset($_SESSION['username']);
$min_belanja       = (float) ($promo['min_belanja'] ?? 0);
$cart_total        = $is_logged_in ? get_cart_total($conn) : 0;
$last_order_total  = $is_logged_in ? (float) ($_SESSION['riwayat_belanja_total'] ?? 0) : 0;
// Ambil yang terbesar: bisa dari keranjang yang lagi diisi, ATAU dari
// pesanan yang baru saja selesai dibuat (keranjang otomatis kosong
// setelah checkout, jadi total pesanan terakhir tetap dihitung).
$belanja_relevan   = max($cart_total, $last_order_total);
$alasan_gagal      = [];

// Cek status member lebih dulu (bisa null kalau belum menyentuh syarat
// minimal transaksi -> lihat config/member_helper.php)
$member = $is_logged_in ? get_current_member($conn) : null;

if (!$promo_is_real) {
    $alasan_gagal[] = 'Promo dengan kode tersebut tidak ditemukan atau sudah tidak aktif.';
}
if (!$is_logged_in) {
    $alasan_gagal[] = 'Kamu harus login terlebih dahulu supaya poin bonus bisa masuk ke akun member kamu.';
} elseif ($member === null) {
    $alasan_gagal[] = 'Poin bonus promo cuma buat member. Booking/pesan online dulu sampai total '.MEMBER_MIN_VISITS.'x transaksi supaya otomatis jadi member.';
}
if ($is_logged_in && $min_belanja > 0 && $belanja_relevan < $min_belanja) {
    $alasan_gagal[] = 'Belanja kamu baru Rp'.number_format($belanja_relevan,0,',','.').
                       ', minimal belanja untuk promo ini adalah Rp'.number_format($min_belanja,0,',','.').'.';
}

$syarat_terpenuhi = empty($alasan_gagal);
$sudah_diklaim    = false;

if ($syarat_terpenuhi && $is_logged_in && $promo_is_real && $member) {
    $stmtCek = $conn->prepare("SELECT id_klaim FROM promo_klaim WHERE id_promo = ? AND id_member = ? LIMIT 1");
    $stmtCek->bind_param("ii", $promo['id_promo'], $member['id_member']);
    $stmtCek->execute();
    $sudah_diklaim = (bool) $stmtCek->get_result()->fetch_assoc();
    $stmtCek->close();

    if (!$sudah_diklaim) {
        // Catat klaim, tambahkan poin bonus, dan catat di riwayat poin
        $stmtKlaim = $conn->prepare("INSERT INTO promo_klaim (id_promo, id_member) VALUES (?, ?)");
        $stmtKlaim->bind_param("ii", $promo['id_promo'], $member['id_member']);

        if ($stmtKlaim->execute()) {
            $bonus = (int) ($promo['poin_bonus'] ?? 0);
            if ($bonus > 0) {
                $stmtPoin = $conn->prepare("UPDATE member SET poin = poin + ? WHERE id_member = ?");
                $stmtPoin->bind_param("ii", $bonus, $member['id_member']);
                $stmtPoin->execute();
                $stmtPoin->close();

                $ket = "Klaim promo ".$promo['kode_promo'];
                $stmtRiwayat = $conn->prepare("INSERT INTO riwayat_poin (id_member, jenis, poin, keterangan) VALUES (?, 'Masuk', ?, ?)");
                $stmtRiwayat->bind_param("iis", $member['id_member'], $bonus, $ket);
                $stmtRiwayat->execute();
                $stmtRiwayat->close();
            }
            // refresh data member supaya poin yang ditampilkan sudah terbaru
            $member['poin'] = ($member['poin'] ?? 0) + $bonus;
        }
        $stmtKlaim->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="assets/css/lucide-icons.css">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Promo Berhasil – YOLAZCAKE Sintang</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  /* ─── CSS VARIABLES ─── */
  :root {
    --bg: linear-gradient(160deg, #1e0e3a 0%, #2d1560 50%, #1a0a2e 100%);
    --nav-bg: rgba(30,14,58,0.85);
    --nav-border: rgba(212,175,55,0.18);
    --drop-bg: rgba(22,10,44,0.97);
    --drop-border: rgba(212,175,55,0.25);
    --drop-item: rgba(255,255,255,0.78);
    --drop-hover-bg: rgba(212,175,55,0.12);
    --drop-hover-col: #D4AF37;
    --text: #ffffff;
    --muted: rgba(255,255,255,0.55);
    --card-bg: rgba(255,255,255,0.05);
    --card-border: rgba(255,255,255,0.1);
  }

  body.light {
    --bg: linear-gradient(160deg, #f5f0ff 0%, #ede4ff 50%, #f8f5ff 100%);
    --nav-bg: rgba(255,255,255,0.9);
    --nav-border: rgba(212,175,55,0.3);
    --drop-bg: rgba(255,255,255,0.98);
    --drop-border: rgba(212,175,55,0.3);
    --drop-item: #333;
    --drop-hover-bg: rgba(212,175,55,0.1);
    --drop-hover-col: #b8860b;
    --text: #1a0a2e;
    --muted: rgba(30,14,58,0.5);
    --card-bg: rgba(255,255,255,0.7);
    --card-border: rgba(0,0,0,0.08);
  }

  body {
    min-height: 100vh;
    font-family: 'Inter', sans-serif;
    background: var(--bg);
    position: relative;
    overflow-x: hidden;
    transition: background 0.4s;
  }

  body::before {
    content: '';
    position: fixed; inset: 0;
    background:
      radial-gradient(ellipse at 20% 20%, rgba(212,175,55,0.12) 0%, transparent 55%),
      radial-gradient(ellipse at 80% 80%, rgba(232,160,191,0.12) 0%, transparent 55%);
    pointer-events: none; z-index: 0;
  }

  /* ─── NAVBAR ─── */
  nav {
    position: fixed; top: 0; left: 0; right: 0; z-index: 100;
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 36px;
    background: var(--nav-bg);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid var(--nav-border);
    box-shadow: 0 4px 28px rgba(0,0,0,0.25);
    transition: background 0.4s, border-color 0.4s;
  }

  .nav-left { display:flex; align-items:center; gap:12px; }

  .nav-left img {
    width:38px; height:38px; border-radius:50%; object-fit:cover;
    border:2px solid rgba(212,175,55,0.5);
    box-shadow:0 0 12px rgba(212,175,55,0.3);
  }

  .nav-left h2 {
    font-family:'Playfair Display',serif; font-size:1.25em; font-weight:700;
    background:linear-gradient(135deg,#D4AF37 30%,#FFE4B5 70%);
    -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
  }

  .nav-right { display:flex; align-items:center; gap:12px; }

  .hamburger, .dark-btn {
    background:rgba(255,255,255,0.07);
    border:1px solid rgba(255,255,255,0.15);
    border-radius:10px; padding:7px 13px;
    cursor:pointer; font-size:1.05em;
    color:rgba(255,255,255,0.85);
    transition:background 0.25s, color 0.25s, transform 0.4s;
    line-height:1; user-select:none;
  }

  body.light .hamburger, body.light .dark-btn {
    background:rgba(0,0,0,0.06); border-color:rgba(0,0,0,0.12); color:#333;
  }

  .hamburger:hover, .dark-btn:hover {
    background:rgba(212,175,55,0.2); color:#D4AF37; border-color:rgba(212,175,55,0.3);
  }

  .dark-btn { display:flex; align-items:center; justify-content:center; }
  .dark-btn svg { width:19px; height:19px; color:#D4AF37; filter:drop-shadow(0 0 3px rgba(212,175,55,0.5)); }
  .dark-btn .icon-sun { display:none; }
  body.light .dark-btn .icon-sun { display:block; }
  body.light .dark-btn .icon-moon { display:none; }

  .hamburger.active {
    transform:rotate(180deg) scale(1.15);
    background:rgba(212,175,55,0.18); color:#D4AF37;
  }
  #hamburger .icon-close { display:none; }
  #hamburger.active .icon-menu { display:none; }
  #hamburger.active .icon-close { display:inline-block; }

  /* ─── DROPDOWN ─── */
  .dropdown {
    position:absolute; top:70px; right:24px;
    background:var(--drop-bg);
    backdrop-filter:blur(20px);
    border:1px solid var(--drop-border);
    border-radius:18px; padding:14px 0; min-width:210px;
    box-shadow:0 16px 48px rgba(0,0,0,0.35);
    z-index:999;
    opacity:0; transform:translateY(-16px) scale(0.97);
    pointer-events:none;
    transition:opacity 0.38s cubic-bezier(.22,.68,0,1.2), transform 0.38s cubic-bezier(.22,.68,0,1.2);
  }

  .dropdown::before {
    content:''; position:absolute; top:0; left:14px; right:14px; height:2px;
    border-radius:2px 2px 0 0;
    background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);
    background-size:200% 100%; animation:goldSlide 3s linear infinite;
  }

  .dropdown.show { opacity:1; transform:translateY(0) scale(1); pointer-events:auto; }

  .dropdown p {
    padding:11px 24px; color:var(--drop-item);
    font-size:0.9em; font-weight:500; cursor:pointer;
    transition:background 0.2s, color 0.2s, padding-left 0.2s;
    opacity:0; transform:translateY(-8px);
  }

  .dropdown.show p { animation:slideDown 0.38s ease forwards; }
  .dropdown.show p:nth-child(1){animation-delay:0.04s;}
  .dropdown.show p:nth-child(2){animation-delay:0.09s;}
  .dropdown.show p:nth-child(3){animation-delay:0.14s;}
  .dropdown.show p:nth-child(4){animation-delay:0.19s;}
  .dropdown.show p:nth-child(5){animation-delay:0.24s;}
  .dropdown.show p:nth-child(6){animation-delay:0.29s;}
  .dropdown.show p:nth-child(7){animation-delay:0.34s;}
  .dropdown.show p:nth-child(8){animation-delay:0.39s;}

  .dropdown p:hover { background:var(--drop-hover-bg); color:var(--drop-hover-col); padding-left:30px; }

  .drop-divider { height:1px; background:rgba(212,175,55,0.12); margin:6px 16px; }

  @keyframes slideDown {
    from { opacity:0; transform:translateY(-8px); }
    to   { opacity:1; transform:translateY(0); }
  }

  /* ─── ACCOUNT DROPDOWN ─── */
  .account-dropdown { position:relative; }

  .account-btn {
    background:rgba(212,175,55,0.15); border:1px solid rgba(212,175,55,0.35);
    border-radius:999px; padding:8px 18px;
    color:#D4AF37; font-family:'Inter',sans-serif; font-size:0.84em;
    font-weight:600; cursor:pointer;
    transition:background 0.25s, box-shadow 0.25s;
  }

  .account-btn:hover { background:rgba(212,175,55,0.25); box-shadow:0 4px 16px rgba(212,175,55,0.2); }

  .account-menu {
    position:absolute; top:calc(100% + 10px); right:0;
    background:var(--drop-bg); border:1px solid var(--drop-border);
    border-radius:14px; padding:8px 0; min-width:160px;
    box-shadow:0 12px 36px rgba(0,0,0,0.3); z-index:200;
    opacity:0; transform:translateY(-8px); pointer-events:none;
    transition:opacity 0.28s, transform 0.28s;
  }

  .account-dropdown:hover .account-menu,
  .account-menu.show { opacity:1; transform:translateY(0); pointer-events:auto; }

  .account-menu a {
    display:block; padding:10px 20px; color:var(--drop-item);
    text-decoration:none; font-size:0.88em;
    transition:background 0.2s, color 0.2s;
  }

  .account-menu a:hover { background:var(--drop-hover-bg); color:var(--drop-hover-col); }

  .login-btn {
    background:linear-gradient(135deg,#D4AF37,#b8860b);
    border:none; border-radius:999px; padding:9px 22px;
    color:#fff; font-weight:600; font-size:0.85em; cursor:pointer;
    transition:box-shadow 0.25s, transform 0.2s;
  }

  .login-btn:hover { box-shadow:0 6px 20px rgba(212,175,55,0.4); transform:translateY(-1px); }

  /* ─── HERO ─── */
  .page-hero {
    position:relative; margin-top:65px;
    height:320px;
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    overflow:hidden;
    background:linear-gradient(135deg,#2b1a11 0%,#4a2c1a 40%,#6d3e26 70%,#3a1f0e 100%);
    z-index:1;
  }

  .page-hero::before {
    content:''; position:absolute; inset:0;
    background:
      radial-gradient(ellipse at 30% 50%, rgba(212,175,55,0.2) 0%, transparent 60%),
      radial-gradient(ellipse at 75% 40%, rgba(232,160,191,0.18) 0%, transparent 55%);
    animation:heroAurora 8s ease-in-out infinite alternate;
  }

  @keyframes heroAurora {
    0%   { opacity:0.6; transform:scale(1); }
    100% { opacity:1; transform:scale(1.08) translateX(10px); }
  }

  .sparkle {
    position:absolute; border-radius:50%; pointer-events:none;
    animation:floatDot linear infinite;
  }

  @keyframes floatDot {
    0%   { transform:translateY(0) rotate(0deg); opacity:0; }
    20%  { opacity:1; }
    80%  { opacity:0.8; }
    100% { transform:translateY(-340px) rotate(360deg); opacity:0; }
  }

  .hero-inner { position:relative; z-index:2; text-align:center; color:#fff; }

  .hero-eyebrow {
    font-size:0.72em; font-weight:500; letter-spacing:5px; text-transform:uppercase;
    color:#D4AF37; margin-bottom:12px;
    opacity:0; animation:fadeUp 0.8s forwards 0.3s;
  }

  .hero-inner h1 {
    font-family:'Playfair Display',serif; font-size:2.9em; font-weight:700;
    background:linear-gradient(135deg,#fff 30%,#D4AF37 60%,#FFE4B5 80%,#fff);
    background-size:200% 100%;
    -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
    animation:shimmer 4s ease-in-out infinite, fadeUp 0.9s forwards 0.5s;
    opacity:0;
  }

  .hero-sub {
    font-size:0.9em; color:rgba(255,255,255,0.65); margin-top:10px;
    opacity:0; animation:fadeUp 0.9s forwards 0.9s;
  }

  .hero-divider {
    margin-top:16px; display:flex; justify-content:center; align-items:center; gap:12px;
    opacity:0; animation:fadeUp 0.9s forwards 1.1s;
  }

  .hero-divider span { display:block; width:60px; height:1px; background:linear-gradient(to right,transparent,#D4AF37); }
  .hero-divider span:last-child { background:linear-gradient(to left,transparent,#D4AF37); }
  .hero-divider .dmd { color:#D4AF37; font-size:0.75em; letter-spacing:4px; }

  @keyframes shimmer { 0%{background-position:100% 0;} 100%{background-position:-100% 0;} }
  @keyframes fadeUp { from{opacity:0;transform:translateY(20px);} to{opacity:1;transform:translateY(0);} }
  @keyframes goldSlide { 0%{background-position:0% 0;} 100%{background-position:200% 0;} }

  /* ─── CONFETTI BURST ANIMATION ─── */
  .confetti-piece {
    position:fixed; pointer-events:none; z-index:50; border-radius:3px;
    animation:confettiFall linear forwards;
  }

  @keyframes confettiFall {
    0%   { transform:translateY(-10px) rotate(0deg); opacity:1; }
    100% { transform:translateY(100vh) rotate(720deg); opacity:0; }
  }

  /* ─── MAIN WRAPPER ─── */
  .page-wrapper {
    position:relative; z-index:1;
    max-width:860px; margin:0 auto;
    padding:50px 24px 100px;
  }

  @keyframes cardReveal {
    from { opacity:0; transform:translateY(24px); }
    to   { opacity:1; transform:translateY(0); }
  }

  /* ─── SUCCESS BADGE ─── */
  .badge-wrap {
    text-align:center; margin-bottom:32px;
    opacity:0; animation:cardReveal 0.7s forwards 0.4s;
  }

  .badge-icon {
    display:inline-flex; align-items:center; justify-content:center;
    width:100px; height:100px; border-radius:50%;
    background:linear-gradient(135deg,rgba(212,175,55,0.2),rgba(212,175,55,0.05));
    border:2px solid rgba(212,175,55,0.4);
    font-size:3em;
    box-shadow:0 0 40px rgba(212,175,55,0.3), 0 0 80px rgba(212,175,55,0.1);
    animation:pulseBadge 2.5s ease-in-out infinite;
  }

  @keyframes pulseBadge {
    0%,100% { box-shadow:0 0 40px rgba(212,175,55,0.3), 0 0 80px rgba(212,175,55,0.1); transform:scale(1); }
    50%      { box-shadow:0 0 60px rgba(212,175,55,0.5), 0 0 100px rgba(212,175,55,0.2); transform:scale(1.05); }
  }

  /* ─── HEADLINE CARD ─── */
  .headline-card {
    background:var(--card-bg);
    backdrop-filter:blur(24px);
    border:1px solid var(--card-border);
    border-radius:28px; padding:40px 44px 36px;
    text-align:center; position:relative; overflow:hidden;
    margin-bottom:24px;
    opacity:0; animation:cardReveal 0.8s forwards 0.6s;
    transition:background 0.4s, border-color 0.4s;
  }

  .headline-card::before {
    content:''; position:absolute; top:0; left:0; right:0; height:3px;
    background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);
    background-size:200% 100%; animation:goldSlide 4s linear infinite;
  }

  .headline-card::after {
    content:''; position:absolute; top:-80px; right:-80px;
    width:240px; height:240px; border-radius:50%;
    background:radial-gradient(circle,rgba(212,175,55,0.1) 0%,transparent 70%);
    pointer-events:none;
  }

  .headline-card h1 {
    font-family:'Playfair Display',serif; font-size:2.4em; font-weight:700;
    background:linear-gradient(135deg,var(--text,#fff) 30%,#D4AF37 70%);
    -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
    margin-bottom:14px; line-height:1.2;
  }

  .headline-card .sub-text {
    font-size:0.97em; color:var(--muted); line-height:1.75; max-width:540px; margin:0 auto;
  }

  /* ─── PROMO CODE CARD ─── */
  .code-card {
    background:linear-gradient(135deg,rgba(212,175,55,0.16),rgba(184,134,11,0.07));
    border:1px solid rgba(212,175,55,0.35);
    border-radius:24px; padding:40px 36px;
    text-align:center; position:relative; overflow:hidden;
    margin-bottom:24px;
    opacity:0; animation:cardReveal 0.8s forwards 0.85s;
    transition:box-shadow 0.3s;
  }

  .code-card:hover {
    box-shadow:0 0 50px rgba(212,175,55,0.22), 0 12px 40px rgba(0,0,0,0.15);
  }

  .code-card::before {
    content:''; position:absolute; top:0; left:0; right:0; height:3px;
    background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);
    background-size:200% 100%; animation:goldSlide 3.5s linear infinite;
  }

  .code-card-bg-icon {
    position:absolute; bottom:-10px; right:20px;
    font-size:7em; opacity:0.05; pointer-events:none; color:#D4AF37;
  }

  .code-label {
    font-size:0.72em; font-weight:600; letter-spacing:5px; text-transform:uppercase;
    color:rgba(212,175,55,0.75); margin-bottom:20px;
  }

  .promo-code-display {
    display:inline-block;
    font-family:'Playfair Display',serif; font-size:3em; font-weight:700; letter-spacing:6px;
    background:linear-gradient(135deg,#D4AF37,#FFE4B5,#D4AF37);
    background-size:200% 100%;
    -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
    animation:shimmer 3s ease-in-out infinite;
    padding:0 12px; margin-bottom:24px; cursor:pointer;
    position:relative;
  }

  .copy-hint {
    font-size:0.72em; color:rgba(212,175,55,0.6); margin-top:-14px; margin-bottom:20px;
    letter-spacing:1px;
  }

  .code-copied {
    display:none; font-size:0.8em; font-weight:600; color:#4ade80;
    letter-spacing:1px; margin-bottom:12px;
  }

  .code-meta {
    display:flex; flex-wrap:wrap; justify-content:center; gap:12px; margin-top:8px;
  }

  .meta-pill {
    display:inline-flex; align-items:center; gap:6px;
    padding:8px 18px;
    background:rgba(255,255,255,0.06); border:1px solid rgba(212,175,55,0.2);
    border-radius:999px; font-size:0.78em; color:var(--muted);
    transition:border-color 0.25s;
  }

  .meta-pill:hover { border-color:rgba(212,175,55,0.45); }
  .meta-pill span { color:#D4AF37; font-weight:600; }

  /* ─── BENEFITS ROW ─── */
  .benefits-row {
    display:grid; grid-template-columns:repeat(3,1fr); gap:16px;
    margin-bottom:24px;
    opacity:0; animation:cardReveal 0.8s forwards 1.05s;
  }

  .benefit-card {
    background:var(--card-bg);
    backdrop-filter:blur(16px);
    border:1px solid var(--card-border);
    border-radius:20px; padding:26px 20px; text-align:center;
    position:relative; overflow:hidden;
    transition:border-color 0.3s, box-shadow 0.3s, transform 0.3s, background 0.4s;
    cursor:default;
  }

  .benefit-card::before {
    content:''; position:absolute; top:0; left:0; right:0; height:2px;
    background:linear-gradient(90deg,#ee2a7b,#D4AF37,#ee2a7b);
    background-size:200% 100%; animation:goldSlide 3s linear infinite;
  }

  .benefit-card:hover {
    border-color:rgba(212,175,55,0.4);
    box-shadow:0 0 30px rgba(212,175,55,0.18), 0 8px 24px rgba(0,0,0,0.15);
    transform:translateY(-6px) scale(1.02);
  }

  .benefit-icon { font-size:2.2em; margin-bottom:12px; }

  .benefit-title {
    font-size:0.72em; font-weight:600; letter-spacing:2px; text-transform:uppercase;
    color:rgba(212,175,55,0.8); margin-bottom:8px;
  }

  .benefit-val {
    font-family:'Playfair Display',serif; font-size:1.1em; font-weight:700;
    color:var(--text,#fff);
  }

  /* ─── STEPS CARD ─── */
  .steps-card {
    background:var(--card-bg); backdrop-filter:blur(20px);
    border:1px solid var(--card-border);
    border-radius:24px; padding:36px;
    margin-bottom:24px; position:relative; overflow:hidden;
    opacity:0; animation:cardReveal 0.8s forwards 1.2s;
    transition:background 0.4s, border-color 0.4s;
  }

  .steps-card::before {
    content:''; position:absolute; top:0; left:0; right:0; height:3px;
    background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);
    background-size:200% 100%; animation:goldSlide 4s linear infinite;
  }

  .section-title {
    font-family:'Playfair Display',serif; font-size:1.3em; font-weight:700;
    color:var(--text,#fff); margin-bottom:28px;
    display:flex; align-items:center; gap:10px;
  }

  .section-title::after {
    content:''; flex:1; height:1px;
    background:linear-gradient(to right,rgba(212,175,55,0.3),transparent);
  }

  .steps-list { display:flex; flex-direction:column; gap:18px; }

  .step-item {
    display:flex; align-items:flex-start; gap:18px;
    padding:18px 20px;
    background:rgba(255,255,255,0.04);
    border:1px solid rgba(255,255,255,0.07);
    border-radius:16px;
    transition:border-color 0.3s, background 0.3s, transform 0.3s;
    cursor:default;
  }

  body.light .step-item { background:rgba(0,0,0,0.03); border-color:rgba(0,0,0,0.06); }

  .step-item:hover {
    border-color:rgba(212,175,55,0.3); background:rgba(212,175,55,0.05);
    transform:translateX(6px);
  }

  .step-num {
    flex-shrink:0; width:38px; height:38px; border-radius:50%;
    background:linear-gradient(135deg,#D4AF37,#b8860b);
    display:flex; align-items:center; justify-content:center;
    font-weight:700; font-size:0.88em; color:#fff;
    box-shadow:0 4px 12px rgba(212,175,55,0.35);
  }

  .step-text h4 {
    font-size:0.9em; font-weight:600; color:var(--text,#fff); margin-bottom:4px;
  }

  .step-text p { font-size:0.82em; color:var(--muted); line-height:1.55; }

  /* ─── CTA BUTTONS ─── */
  .cta-row {
    display:flex; gap:14px; justify-content:center; flex-wrap:wrap;
    opacity:0; animation:cardReveal 0.8s forwards 1.35s;
    margin-top:4px;
  }

  .btn-primary {
    display:inline-flex; align-items:center; gap:8px;
    padding:14px 32px;
    background:linear-gradient(135deg,#D4AF37,#b8860b);
    border:none; border-radius:999px;
    color:#fff; font-family:'Inter',sans-serif;
    font-size:0.9em; font-weight:700; letter-spacing:0.5px;
    cursor:pointer; text-decoration:none;
    transition:transform 0.25s, box-shadow 0.3s;
    box-shadow:0 6px 24px rgba(212,175,55,0.35);
  }

  .btn-primary:hover {
    transform:translateY(-3px);
    box-shadow:0 12px 32px rgba(212,175,55,0.5);
  }

  .btn-secondary {
    display:inline-flex; align-items:center; gap:8px;
    padding:14px 32px;
    background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.18);
    border-radius:999px;
    color:var(--text,#fff); font-family:'Inter',sans-serif;
    font-size:0.9em; font-weight:600; letter-spacing:0.5px;
    cursor:pointer; text-decoration:none;
    transition:background 0.25s, border-color 0.25s, transform 0.25s;
  }

  body.light .btn-secondary { background:rgba(0,0,0,0.05); border-color:rgba(0,0,0,0.15); }

  .btn-secondary:hover {
    background:rgba(212,175,55,0.12); border-color:rgba(212,175,55,0.4);
    color:#D4AF37; transform:translateY(-3px);
  }

  /* ─── PARTICLES ─── */
  .particle { position:fixed; border-radius:50%; pointer-events:none; z-index:0; animation:partFloat linear infinite; }
  @keyframes partFloat {
    0%   { transform:translateY(100vh) scale(0); opacity:0; }
    10%  { opacity:0.5; } 90% { opacity:0.3; }
    100% { transform:translateY(-120px) scale(1); opacity:0; }
  }

  /* ─── FOOTER ─── */
  .footer {
    position:relative; z-index:1; text-align:center;
    padding:36px 20px; font-size:0.8em; color:var(--muted);
    border-top:1px solid rgba(255,255,255,0.06); line-height:1.8;
  }

  body.light .footer { border-top-color:rgba(0,0,0,0.08); }

  /* ─── RESPONSIVE ─── */
  @media(max-width:768px) {
    nav { padding:12px 18px; }
    .page-hero { height:260px; }
    .hero-inner h1 { font-size:2em; }
    .page-wrapper { padding:32px 16px 70px; }
    .headline-card { padding:30px 22px; }
    .headline-card h1 { font-size:1.8em; }
    .promo-code-display { font-size:2.2em; letter-spacing:4px; }
    .benefits-row { grid-template-columns:1fr; }
    .code-card { padding:30px 22px; }
    .steps-card { padding:26px 20px; }
    .dropdown { right:12px; min-width:185px; }
  }
</style>
</head>
<body>

<div id="particles"></div>

<!-- NAVBAR -->
<nav>
  <div class="nav-left">
    <img src="assets/img/Yolazcake.png" alt="YOLAZCAKE Logo">
    <h2>YOLAZCAKE</h2>
  </div>

  <ul class="main-nav" style="display:none;"></ul>

  <div class="nav-right">
    <?php if(isset($_SESSION['username'])): ?>
    <div class="account-dropdown">
      <button class="account-btn" id="accountBtn" onclick="toggleAccountMenu(event)"><i data-lucide="user" class="lucide-ic"></i> <?php echo htmlspecialchars($_SESSION['username']); ?> ▼</button>
      <div class="account-menu" id="accountMenu">
        <a href="<?php echo (isset($_SESSION['role']) && $_SESSION['role']==='admin') ? 'dashboard.php' : 'member/member.php'; ?>"><?php echo (isset($_SESSION['role']) && $_SESSION['role']==='admin') ? 'Dashboard' : 'Member Area'; ?></a>
        <a href="auth/logout.php">Logout</a>
      </div>
    </div>
    <?php else: ?>
    <button class="login-btn" onclick="window.location.href='auth/login.php'">Login</button>
    <?php endif; ?>
    <div class="hamburger" onclick="toggleMenu()" id="hamburger"><i data-lucide="menu" class="lucide-ic icon-menu"></i><i data-lucide="x" class="lucide-ic icon-close"></i></div>
    <div class="dark-btn" id="darkBtn" onclick="toggleDark()" title="Toggle theme"><svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"></circle><line x1="12" y1="2" x2="12" y2="4.5"></line><line x1="12" y1="19.5" x2="12" y2="22"></line><line x1="4.2" y1="4.2" x2="5.9" y2="5.9"></line><line x1="18.1" y1="18.1" x2="19.8" y2="19.8"></line><line x1="2" y1="12" x2="4.5" y2="12"></line><line x1="19.5" y1="12" x2="22" y2="12"></line><line x1="4.2" y1="19.8" x2="5.9" y2="18.1"></line><line x1="18.1" y1="5.9" x2="19.8" y2="4.2"></line></svg><svg class="icon-moon" viewBox="0 0 24 24" fill="currentColor"><path d="M20.84 14.4a9 9 0 0 1-11.24-11.24 1 1 0 0 0-1.3-1.22A10.07 10.07 0 0 0 2 12.1 10 10 0 0 0 12 22a10.07 10.07 0 0 0 9.06-6.3 1 1 0 0 0-1.22-1.3z"></path><circle cx="18.5" cy="5.5" r="1.1"></circle><circle cx="20.5" cy="9" r="0.6"></circle></svg></div>
  </div>

  <div class="dropdown" id="dropdown">
    <p onclick="window.location.href='index.php'"><i data-lucide="home" class="lucide-ic"></i> Home</p>
    <p onclick="window.location.href='produk/menu.php'"><i data-lucide="coffee" class="lucide-ic"></i> Menu</p>
    <p onclick="window.location.href='gallery.php'"><i data-lucide="image" class="lucide-ic"></i> Gallery</p>
    <p onclick="window.location.href='about.php'"><i data-lucide="sparkles" class="lucide-ic"></i> About</p>
    <p onclick="window.location.href='contact.php'"><i data-lucide="phone" class="lucide-ic"></i> Contact</p>
    <div class="drop-divider"></div>
    <p onclick="window.location.href='produk/menu.php#promo'"><i data-lucide="tag" class="lucide-ic"></i> Promo Lainnya</p>
  </div>
</nav>

<!-- HERO -->
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow"><i data-lucide="sparkle" class="lucide-ic"></i> YOLAZCAKE Sintang <i data-lucide="sparkle" class="lucide-ic"></i></p>
    <?php if ($syarat_terpenuhi): ?>
      <h1>Promo Berhasil!</h1>
      <p class="hero-sub">Kode eksklusif Anda sudah siap digunakan <i data-lucide="party-popper" class="lucide-ic"></i></p>
    <?php else: ?>
      <h1>Syarat Belum Terpenuhi</h1>
      <p class="hero-sub">Lengkapi dulu syaratnya untuk mengambil promo ini</p>
    <?php endif; ?>
    <div class="hero-divider">
      <span></span><span class="dmd"><i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i></span><span></span>
    </div>
  </div>
</div>

<div class="page-wrapper">

<?php if (!$syarat_terpenuhi): ?>

  <!-- SYARAT BELUM TERPENUHI -->
  <div class="badge-wrap">
    <div class="badge-icon" id="badgeIcon"><i data-lucide="alert-triangle" class="lucide-ic"></i></div>
  </div>

  <div class="headline-card">
    <h1>Belum Bisa Diambil</h1>
    <p class="sub-text">
      Promo ini punya syarat yang harus dipenuhi dulu sebelum bisa kamu ambil:
    </p>
  </div>

  <div class="steps-card">
    <div class="section-title"><i data-lucide="clipboard-list" class="lucide-ic"></i> Yang Perlu Dilengkapi</div>
    <div class="steps-list">
      <?php foreach ($alasan_gagal as $i => $alasan): ?>
      <div class="step-item">
        <div class="step-num"><?= $i + 1 ?></div>
        <div class="step-text">
          <p><?= htmlspecialchars($alasan) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="cta-row">
    <a href="produk/menu.php#Product" class="btn-primary"><i data-lucide="shopping-bag" class="lucide-ic"></i> Lihat Produk</a>
    <?php if(!$is_logged_in): ?>
    <a href="auth/login.php" class="btn-secondary"><i data-lucide="key" class="lucide-ic"></i> Login Dulu</a>
    <?php else: ?>
    <a href="pemesanan/keranjang.php" class="btn-secondary"><i data-lucide="shopping-cart" class="lucide-ic"></i> Ke Keranjang</a>
    <?php endif; ?>
    <a href="index.php" class="btn-secondary"><i data-lucide="home" class="lucide-ic"></i> Kembali ke Home</a>
  </div>

<?php else: ?>

  <!-- ICON BADGE -->
  <div class="badge-wrap">
    <div class="badge-icon" id="badgeIcon"><i data-lucide="party-popper" class="lucide-ic"></i></div>
  </div>

  <!-- HEADLINE CARD -->
  <div class="headline-card">
    <h1>Selamat, Kamu Berhasil!</h1>
    <p class="sub-text">
      Kamu telah mendapatkan promo spesial dari YOLAZCAKE Sintang.
      Tunjukkan kode berikut kepada kasir saat pembayaran dan nikmati diskonnya!
      <?php if ($sudah_diklaim): ?>
        <br><em>(Kamu sudah pernah mengambil promo ini sebelumnya, jadi poin bonus tidak ditambahkan lagi.)</em>
      <?php endif; ?>
    </p>
  </div>

  <!-- PROMO CODE CARD -->
  <div class="code-card">
    <i data-lucide="tag" class="lucide-ic code-card-bg-icon"></i>
    <div class="code-label"><i data-lucide="sparkle" class="lucide-ic"></i> Kode Promo Eksklusif Anda <i data-lucide="sparkle" class="lucide-ic"></i></div>
    <div class="promo-code-display" id="promoCode" onclick="copyCode()" title="Klik untuk salin"><?= htmlspecialchars($promo['kode_promo']) ?></div>
    <div class="copy-hint"><i data-lucide="arrow-up" class="lucide-ic"></i> Klik kode untuk menyalin</div>
    <div class="code-copied" id="codeCopied"><i data-lucide="check-circle" class="lucide-ic"></i> Kode tersalin!</div>
    <div class="code-meta">
      <div class="meta-pill"><i data-lucide="wallet" class="lucide-ic"></i> Diskon <span><?= $promo['diskon_persen'] ?>%</span></div>
      <div class="meta-pill"><i data-lucide="shopping-cart" class="lucide-ic"></i> Min. belanja <span>Rp<?= number_format($promo['min_belanja'],0,',','.') ?></span></div>
      <div class="meta-pill"><i data-lucide="calendar" class="lucide-ic"></i> Berlaku <span><?= $promo['tanggal_selesai'] ? 'S/d '.date('d M Y',strtotime($promo['tanggal_selesai'])) : 'Tanpa batas' ?></span></div>
      <div class="meta-pill"><i data-lucide="star" class="lucide-ic lucide-fill"></i> Poin Bonus <span>+<?= $promo['poin_bonus'] ?></span></div>
    </div>
  </div>

  <!-- BENEFIT CARDS -->
  <div class="benefits-row">
    <div class="benefit-card">
      <div class="benefit-icon"><i data-lucide="tag" class="lucide-ic"></i></div>
      <div class="benefit-title">Judul Promo</div>
      <div class="benefit-val"><?= htmlspecialchars($promo['judul']) ?></div>
    </div>
    <div class="benefit-card">
      <div class="benefit-icon"><i data-lucide="banknote" class="lucide-ic"></i></div>
      <div class="benefit-title">Diskon</div>
      <div class="benefit-val"><?= $promo['diskon_persen'] ?>% dari total belanja</div>
    </div>
    <div class="benefit-card">
      <div class="benefit-icon"><i data-lucide="star" class="lucide-ic lucide-fill"></i></div>
      <div class="benefit-title">Poin Bonus</div>
      <div class="benefit-val"><?= $sudah_diklaim ? 'Sudah pernah diklaim' : '+'.$promo['poin_bonus'].' Poin Member (sudah ditambahkan)' ?></div>
    </div>
  </div>

  <!-- STEPS CARD -->
  <div class="steps-card">
    <div class="section-title"><i data-lucide="clipboard-list" class="lucide-ic"></i> Cara Menggunakan Promo</div>
    <div class="steps-list">
      <div class="step-item">
        <div class="step-num">1</div>
        <div class="step-text">
          <h4>Pilih Produk Favorit</h4>
          <p>Kunjungi YOLAZCAKE dan pilih produk favoritmu dengan total minimal Rp<?= number_format($promo['min_belanja'],0,',','.') ?>.</p>
        </div>
      </div>
      <div class="step-item">
        <div class="step-num">2</div>
        <div class="step-text">
          <h4>Tunjukkan Kode ke Kasir</h4>
          <p>Tunjukkan kode <strong style="color:#D4AF37;"><?= htmlspecialchars($promo['kode_promo']) ?></strong> kepada kasir sebelum pembayaran dilakukan.</p>
        </div>
      </div>
      <div class="step-item">
        <div class="step-num">3</div>
        <div class="step-text">
          <h4>Diskon Langsung Terapkan</h4>
          <p>Kasir akan langsung memotong <?= $promo['diskon_persen'] ?>% dari total belanjamu.<?= $promo['deskripsi'] ? ' '.$promo['deskripsi'] : '' ?></p>
        </div>
      </div>
      <div class="step-item">
        <div class="step-num">4</div>
        <div class="step-text">
          <h4>Kumpulkan Poin Member</h4>
          <p>Poin bonus <?= $promo['poin_bonus'] ?> sudah otomatis masuk ke akun member kamu, cek di halaman Member Area.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- CTA BUTTONS -->
  <div class="cta-row">
    <a href="produk/menu.php#Product" class="btn-primary"><i data-lucide="shopping-bag" class="lucide-ic"></i> Lihat Produk</a>
    <a href="index.php" class="btn-secondary"><i data-lucide="home" class="lucide-ic"></i> Kembali ke Home</a>
    <?php if(isset($_SESSION['username'])): ?>
    <a href="member/member.php" class="btn-secondary"><i data-lucide="user" class="lucide-ic"></i> Member Area</a>
    <?php endif; ?>
  </div>

<?php endif; ?>

</div>

<!-- FOOTER -->
<div class="footer">
  © 2026 YOLAZCAKE Sintang • Cafe • Bakery • Boutique<br>
  Jl. Lintas Melawi, Ladang, Sintang, Kalimantan Barat<br>
  WA: 0815-7815-7888
</div>

<script>
  /* ── Sparkles hero ── */
  (function(){
    const hero = document.getElementById('pageHero');
    const cols = ['#D4AF37','#FFE4B5','#E8A0BF','#fff','#f9ce34','#b8860b'];
    for(let i=0;i<26;i++){
      const d=document.createElement('div'); d.className='sparkle';
      const s=Math.random()*5+2;
      d.style.cssText=`width:${s}px;height:${s}px;background:${cols[Math.floor(Math.random()*cols.length)]};left:${Math.random()*100}%;bottom:${Math.random()*35}%;animation-duration:${4+Math.random()*7}s;animation-delay:${Math.random()*5}s;opacity:0;`;
      hero.appendChild(d);
    }
  })();

  /* ── Confetti burst on load ── */
  (function(){
    const colors=['#D4AF37','#ee2a7b','#FFE4B5','#E8A0BF','#fff','#b8860b','#f9ce34'];
    for(let i=0;i<60;i++){
      setTimeout(()=>{
        const c=document.createElement('div'); c.className='confetti-piece';
        const s=Math.random()*8+5;
        const dur=2+Math.random()*2;
        c.style.cssText=`
          width:${s}px;height:${s}px;
          background:${colors[Math.floor(Math.random()*colors.length)]};
          left:${Math.random()*100}vw; top:-10px;
          border-radius:${Math.random()>0.5?'50%':'2px'};
          animation-duration:${dur}s;
          animation-delay:0s;`;
        document.body.appendChild(c);
        setTimeout(()=>c.remove(), dur*1000+100);
      }, i*40);
    }
  })();

  /* ── Background particles ── */
  (function(){
    const c=document.getElementById('particles');
    const cols=['rgba(212,175,55,0.4)','rgba(232,160,191,0.3)','rgba(255,255,255,0.15)'];
    for(let i=0;i<18;i++){
      const p=document.createElement('div'); p.className='particle';
      const s=Math.random()*5+2;
      p.style.cssText=`width:${s}px;height:${s}px;background:${cols[Math.floor(Math.random()*cols.length)]};left:${Math.random()*100}%;animation-duration:${10+Math.random()*12}s;animation-delay:${Math.random()*10}s;`;
      c.appendChild(p);
    }
  })();

  /* ── Copy promo code ── */
  function copyCode(){
    const code = document.getElementById('promoCode').textContent.trim();
    navigator.clipboard.writeText(code).then(()=>{
      const el=document.getElementById('codeCopied');
      el.style.display='block';
      document.getElementById('promoCode').style.transform='scale(1.06)';
      setTimeout(()=>{
        el.style.display='none';
        document.getElementById('promoCode').style.transform='';
      }, 2200);
    }).catch(()=>{
      /* fallback */
      const ta=document.createElement('textarea');
      ta.value=code; document.body.appendChild(ta);
      ta.select(); document.execCommand('copy'); ta.remove();
      const el=document.getElementById('codeCopied');
      el.style.display='block';
      setTimeout(()=>el.style.display='none', 2200);
    });
  }

  /* ── Account Dropdown ──
     Sebelumnya dropdown ini hanya mengandalkan CSS :hover, yang di layar
     sentuh butuh tap pertama untuk memicu "hover" dan tap kedua baru
     benar-benar mengklik (tampak "tidak respon, harus berkali-kali tekan").
     Sekarang pakai toggle class via klik supaya konsisten di HP maupun desktop. */
  function toggleAccountMenu(e){
    if (e) e.stopPropagation();
    const menu = document.getElementById('accountMenu');
    if (menu) menu.classList.toggle('show');
  }

  document.addEventListener('click', (e) => {
    const menu = document.getElementById('accountMenu');
    const btn  = document.getElementById('accountBtn');
    if (menu && menu.classList.contains('show') && !menu.contains(e.target) && (!btn || !btn.contains(e.target))){
      menu.classList.remove('show');
    }
  });

  /* ── Hamburger / Dropdown ── */
  function toggleMenu(){
    const drop=document.getElementById('dropdown');
    const burg=document.getElementById('hamburger');
    drop.classList.toggle('show');
    burg.classList.toggle('active');
  }

  document.querySelectorAll('.dropdown p').forEach(item=>{
    item.addEventListener('click',()=>{
      const drop=document.getElementById('dropdown');
      const burg=document.getElementById('hamburger');
      drop.classList.remove('show'); burg.classList.remove('active');
    });
  });

  document.addEventListener('click',(e)=>{
    const drop=document.getElementById('dropdown');
    const burg=document.getElementById('hamburger');
    if(!drop.contains(e.target)&&!burg.contains(e.target)){
      drop.classList.remove('show'); burg.classList.remove('active');
    }
  });

  /* ── Dark / Light Mode ── */
  function toggleDark(){
    const isLight=document.body.classList.toggle('light');
    localStorage.setItem('promoTheme',isLight?'light':'dark');
  }

  (function(){
    const saved=localStorage.getItem('promoTheme');
    if(saved==='light'){
      document.body.classList.add('light');
    }
  })();
</script>

<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide){lucide.createIcons();}</script>
</body>
</html>
