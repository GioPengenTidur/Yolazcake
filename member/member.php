<?php
session_start();

if(!isset($_SESSION['username'])){
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/koneksi.php';
require_once '../config/member_helper.php';
require_once '../config/gamifikasi_helper.php';

$member = get_current_member($conn);

// Belum jadi member (belum menyentuh syarat minimal transaksi) -> tampilkan
// halaman progres, bukan dashboard loyalti penuh.
if ($member === null) {
    $id_user_sess = $_SESSION['user_id'] ?? null;
    $progres      = $id_user_sess ? get_visit_count($conn, (int) $id_user_sess) : 0;
    $syarat       = MEMBER_MIN_VISITS;
    $sisa         = max(0, $syarat - $progres);
    $persen       = (int) round(min(100, ($progres / $syarat) * 100));
    include 'belum_member.php';
    exit;
}

$poin   = (int) ($member['poin'] ?? 0);
$tier   = get_member_tier($poin);

// Bulan bergabung dihitung dari tanggal member terdaftar
$bulan_bergabung = 0;
if (!empty($member['created_at'])) {
    $joined = new DateTime($member['created_at']);
    $now    = new DateTime();
    $diff   = $joined->diff($now);
    $bulan_bergabung = ($diff->y * 12) + $diff->m;
}

// Poin masuk/keluar bulan ini (dari riwayat_poin, kalau ada)
$poin_bulan_ini = 0;
if (!empty($member['id_member'])) {
    $stmtR = $conn->prepare(
        "SELECT COALESCE(SUM(CASE WHEN jenis='Masuk' THEN poin ELSE -poin END),0) AS total
         FROM riwayat_poin
         WHERE id_member = ? AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())"
    );
    $stmtR->bind_param("i", $member['id_member']);
    $stmtR->execute();
    $rowR = $stmtR->get_result()->fetch_assoc();
    $stmtR->close();
    $poin_bulan_ini = (int) ($rowR['total'] ?? 0);
}

// Promo aktif untuk ditampilkan di kartu promo member (real dari tabel promo)
$today = date('Y-m-d');
$promo_aktif = [];
$stmtPromo = $conn->prepare("SELECT * FROM promo WHERE status='Aktif' AND (tanggal_selesai IS NULL OR tanggal_selesai >= ?) ORDER BY id_promo DESC LIMIT 3");
$stmtPromo->bind_param("s", $today);
$stmtPromo->execute();
$resPromo = $stmtPromo->get_result();
if ($resPromo) {
    while ($row = mysqli_fetch_assoc($resPromo)) {
        $promo_aktif[] = $row;
    }
}

$reward_milestones = [
    ['poin' => 100, 'icon' => '<i data-lucide="gift" class="lucide-ic"></i>', 'nama' => 'Diskon 5%'],
    ['poin' => 200, 'icon' => '<i data-lucide="coffee" class="lucide-ic"></i>', 'nama' => 'Gratis Kopi'],
    ['poin' => 250, 'icon' => '<i data-lucide="croissant" class="lucide-ic"></i>', 'nama' => 'Gratis Croissant'],
    ['poin' => 500, 'icon' => '<i data-lucide="cake" class="lucide-ic"></i>', 'nama' => 'Gratis Cake'],
];

// Data gamifikasi: streak checkin & notifikasi in-app (kado poin, badge baru).
// Dibungkus try-catch supaya kalau migration_gamifikasi.sql belum diimport,
// dashboard member tetap tampil (fitur gamifikasi saja yang nonaktif sementara).
try {
    $streak_info      = gamif_get_streak_info($conn, $member);
    $notif_belum_baca = gamif_jumlah_notif_belum_dibaca($conn, (int) $member['id_member']);
} catch (Throwable $e) {
    $streak_info      = ['streak_saat_ini' => 0, 'streak_terbaik' => 0, 'sudah_checkin' => false, 'badges' => [], 'tanggal_checkin' => []];
    $notif_belum_baca = 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="../assets/css/lucide-icons.css">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Member Area – YOLAZCAKE</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600;700&family=Cormorant+Garamond:wght@300;400;600&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ═══════════════════════════════════════════
   CSS VARIABLES
═══════════════════════════════════════════ */
:root {
  --gold:        #D4AF37;
  --gold-light:  #FFE88A;
  --gold-dark:   #A07C10;
  --rose:        #EE2A7B;
  --rose-soft:   #FF6B9E;
  --bg1: #0d0520;
  --bg2: #1a0a3a;
  --bg3: #150830;
  --glass:       rgba(255,255,255,0.045);
  --glass-hover: rgba(255,255,255,0.08);
  --glass-border:rgba(255,255,255,0.10);
  --text:        #ffffff;
  --text-muted:  rgba(255,255,255,0.50);
  --nav-bg:      rgba(13,5,32,0.88);
}

body.light {
  --bg1: #f0ebff;
  --bg2: #e8dfff;
  --bg3: #f5f0ff;
  --glass:       rgba(255,255,255,0.70);
  --glass-hover: rgba(255,255,255,0.85);
  --glass-border:rgba(212,175,55,0.25);
  --text:        #1a0a2e;
  --text-muted:  rgba(26,10,46,0.50);
  --nav-bg:      rgba(240,235,255,0.92);
}

/* ═══════════════════════════════════════════
   BASE
═══════════════════════════════════════════ */
html { scroll-behavior: smooth; }

body {
  min-height: 100vh;
  font-family: 'Inter', sans-serif;
  background: linear-gradient(140deg, var(--bg1) 0%, var(--bg2) 50%, var(--bg3) 100%);
  overflow-x: hidden;
  transition: background 0.5s;
  color: var(--text);
}

/* Ambient glow layers */
body::before, body::after {
  content: '';
  position: fixed; inset: 0;
  pointer-events: none; z-index: 0;
}
body::before {
  background:
    radial-gradient(ellipse 60% 50% at 15% 25%, rgba(212,175,55,0.12) 0%, transparent 70%),
    radial-gradient(ellipse 50% 60% at 85% 70%, rgba(232,160,191,0.10) 0%, transparent 70%);
  animation: ambientShift 12s ease-in-out infinite alternate;
}
body::after {
  background:
    radial-gradient(ellipse 40% 40% at 60% 10%, rgba(138,43,226,0.06) 0%, transparent 60%);
  animation: ambientShift 18s ease-in-out infinite alternate-reverse;
}

@keyframes ambientShift {
  0%   { opacity: 0.6; transform: scale(1); }
  100% { opacity: 1;   transform: scale(1.06) translate(10px, -10px); }
}

/* ═══════════════════════════════════════════
   AURORA CANVAS (background top)
═══════════════════════════════════════════ */
#aurora-canvas {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  z-index: 0;
  pointer-events: none;
  opacity: 0.35;
}

/* ═══════════════════════════════════════════
   FLOATING ORBS
═══════════════════════════════════════════ */
.orb {
  position: fixed;
  border-radius: 50%;
  pointer-events: none;
  z-index: 0;
  filter: blur(60px);
  animation: orbFloat linear infinite;
}
@keyframes orbFloat {
  0%   { transform: translate(0,0) scale(1); opacity: 0.4; }
  33%  { transform: translate(30px,-40px) scale(1.08); opacity: 0.55; }
  66%  { transform: translate(-20px,25px) scale(0.95); opacity: 0.35; }
  100% { transform: translate(0,0) scale(1); opacity: 0.4; }
}

/* ═══════════════════════════════════════════
   NAVBAR
═══════════════════════════════════════════ */
nav {
  position: fixed;
  top: 0; left: 0; right: 0; z-index: 200;
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 36px;
  background: var(--nav-bg);
  backdrop-filter: blur(24px) saturate(1.4);
  border-bottom: 1px solid rgba(212,175,55,0.18);
  box-shadow: 0 4px 32px rgba(0,0,0,0.3);
  transition: background 0.4s, border-color 0.4s;
}

.nav-logo {
  display: flex; align-items: center; gap: 12px;
  text-decoration: none;
}
.nav-logo img {
  width: 38px; height: 38px; border-radius: 50%;
  object-fit: cover;
  border: 2px solid rgba(212,175,55,0.5);
  box-shadow: 0 0 16px rgba(212,175,55,0.35);
}
.nav-logo h2 {
  font-family: 'Playfair Display', serif;
  font-size: 1.25em; font-weight: 700;
  background: linear-gradient(135deg, #D4AF37 30%, #FFE88A 70%);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text;
}
body.light .nav-logo h2 {
  background: linear-gradient(135deg, #A07C10 30%, #D4AF37 70%);
  -webkit-background-clip: text; background-clip: text;
}

/* Premium badge in nav */
.nav-premium-badge {
  display: flex; align-items: center; gap: 6px;
  padding: 5px 14px;
  background: linear-gradient(135deg, rgba(212,175,55,0.15), rgba(212,175,55,0.05));
  border: 1px solid rgba(212,175,55,0.35);
  border-radius: 999px;
  font-size: 0.72em; font-weight: 700;
  letter-spacing: 2px; text-transform: uppercase;
  color: var(--gold);
  position: relative; overflow: hidden;
}
.nav-premium-badge::before {
  content: '';
  position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
  background: linear-gradient(90deg, transparent, rgba(212,175,55,0.2), transparent);
  animation: shimmerBadge 2.5s linear infinite;
}
@keyframes shimmerBadge {
  to { left: 200%; }
}

.nav-right {
  display: flex; align-items: center; gap: 10px;
}
.nav-btn {
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.14);
  border-radius: 10px;
  padding: 7px 13px;
  cursor: pointer; font-size: 1em;
  color: rgba(255,255,255,0.85);
  transition: background 0.25s, color 0.25s, border-color 0.25s, transform 0.2s;
  line-height: 1;
}
body.light .nav-btn {
  background: rgba(0,0,0,0.05); border-color: rgba(0,0,0,0.12); color: #333;
}
.nav-btn:hover {
  background: rgba(212,175,55,0.18); color: var(--gold);
  border-color: rgba(212,175,55,0.35); transform: scale(1.08);
}
.nav-btn.active {
  transform: rotate(90deg) scale(1.12);
  background: rgba(212,175,55,0.15); color: var(--gold);
}
#hamburger .icon-close { display: none; }
#hamburger.active .icon-menu { display: none; }
#hamburger.active .icon-close { display: inline-block; }
#darkBtn { display: flex; align-items: center; justify-content: center; }
#darkBtn svg { width: 19px; height: 19px; color: var(--gold); filter: drop-shadow(0 0 3px rgba(212,175,55,0.5)); }
#darkBtn .icon-sun { display: none; }
body.light #darkBtn .icon-sun { display: block; }
body.light #darkBtn .icon-moon { display: none; }

/* Dropdown */
.dropdown {
  position: absolute; top: 68px; right: 24px;
  background: rgba(13,5,32,0.97);
  backdrop-filter: blur(28px) saturate(1.5);
  border: 1px solid rgba(212,175,55,0.22);
  border-radius: 20px; padding: 14px 0;
  min-width: 210px;
  box-shadow: 0 24px 60px rgba(0,0,0,0.5), 0 0 0 1px rgba(212,175,55,0.05);
  z-index: 999;
  opacity: 0; transform: translateY(-16px) scale(0.96); pointer-events: none;
  transition: opacity 0.38s cubic-bezier(.22,.68,0,1.2), transform 0.38s cubic-bezier(.22,.68,0,1.2);
}
body.light .dropdown {
  background: rgba(255,255,255,0.98);
  border-color: rgba(212,175,55,0.28);
  box-shadow: 0 24px 60px rgba(0,0,0,0.15);
}
.dropdown.show { opacity: 1; transform: translateY(0) scale(1); pointer-events: auto; }
.dropdown::before {
  content: '';
  position: absolute; top: 0; left: 14px; right: 14px; height: 2px;
  border-radius: 2px 2px 0 0;
  background: linear-gradient(90deg, #D4AF37, #ee2a7b, #D4AF37);
  background-size: 200% 100%;
  animation: goldSlide 3s linear infinite;
}
.dropdown a, .dropdown p {
  display: block; padding: 11px 24px;
  color: rgba(255,255,255,0.78);
  font-size: 0.88em; font-weight: 500;
  cursor: pointer; text-decoration: none;
  transition: background 0.2s, color 0.2s, padding-left 0.2s;
  opacity: 0; transform: translateY(-8px);
}
body.light .dropdown a, body.light .dropdown p { color: #333; }
.dropdown.show a, .dropdown.show p {
  animation: slideDown 0.38s ease forwards;
}
.dropdown.show a:nth-child(1), .dropdown.show p:nth-child(1) { animation-delay: 0.04s; }
.dropdown.show a:nth-child(2), .dropdown.show p:nth-child(2) { animation-delay: 0.09s; }
.dropdown.show a:nth-child(3), .dropdown.show p:nth-child(3) { animation-delay: 0.14s; }
.dropdown.show a:nth-child(4), .dropdown.show p:nth-child(4) { animation-delay: 0.19s; }
.dropdown.show a:nth-child(5), .dropdown.show p:nth-child(5) { animation-delay: 0.24s; }
.dropdown.show a:nth-child(6), .dropdown.show p:nth-child(6) { animation-delay: 0.29s; }
.dropdown.show a:nth-child(7), .dropdown.show p:nth-child(7) { animation-delay: 0.34s; }
@keyframes slideDown {
  from { opacity: 0; transform: translateY(-8px); }
  to   { opacity: 1; transform: translateY(0); }
}
.dropdown a:hover, .dropdown p:hover {
  background: rgba(212,175,55,0.1); color: var(--gold); padding-left: 30px;
}
.dropdown .dd-divider {
  height: 1px; background: rgba(212,175,55,0.12); margin: 6px 16px;
}
.dropdown .dd-logout { color: #ee2a7b !important; }
.dropdown .dd-logout:hover { background: rgba(238,42,123,0.1) !important; }

/* ═══════════════════════════════════════════
   HERO – PREMIUM CINEMATIC
═══════════════════════════════════════════ */
.hero {
  position: relative;
  height: 380px;
  display: flex; align-items: center; justify-content: center;
  margin-top: 65px;
  overflow: hidden;
  background: linear-gradient(160deg, #1a0533 0%, #2e1060 45%, #1a0c40 70%, #0d0520 100%);
}

.hero-mesh {
  position: absolute; inset: 0;
  background:
    radial-gradient(ellipse 70% 60% at 25% 55%, rgba(212,175,55,0.22) 0%, transparent 55%),
    radial-gradient(ellipse 55% 65% at 78% 38%, rgba(238,42,123,0.18) 0%, transparent 55%),
    radial-gradient(ellipse 40% 50% at 55% 80%, rgba(138,43,226,0.12) 0%, transparent 55%);
  animation: heroMesh 10s ease-in-out infinite alternate;
}
@keyframes heroMesh {
  0%   { transform: scale(1) rotate(0deg); opacity: 0.7; }
  100% { transform: scale(1.08) rotate(2deg); opacity: 1; }
}

/* Hero grid lines */
.hero-grid {
  position: absolute; inset: 0; pointer-events: none;
  background-image:
    linear-gradient(rgba(212,175,55,0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(212,175,55,0.04) 1px, transparent 1px);
  background-size: 60px 60px;
  animation: gridDrift 20s linear infinite;
}
@keyframes gridDrift {
  from { background-position: 0 0; }
  to   { background-position: 60px 60px; }
}

.hero-inner {
  position: relative; z-index: 3; text-align: center; padding: 0 24px;
}

.hero-crown {
  display: inline-flex; align-items: center; gap: 10px;
  padding: 7px 22px;
  background: rgba(212,175,55,0.12);
  border: 1px solid rgba(212,175,55,0.3);
  border-radius: 999px;
  font-size: 0.7em; font-weight: 700; letter-spacing: 4px;
  text-transform: uppercase; color: var(--gold);
  margin-bottom: 20px;
  opacity: 0; animation: fadeUp 0.8s forwards 0.3s;
}

.hero-title {
  font-family: 'Playfair Display', serif;
  font-size: 3.8em; font-weight: 700; line-height: 1;
  margin-bottom: 16px;
  opacity: 0; animation: fadeUp 0.9s forwards 0.55s;
}
.hero-title span {
  display: block;
  background: linear-gradient(135deg, #fff 0%, #D4AF37 35%, #FFE88A 55%, #EE2A7B 80%, #fff 100%);
  background-size: 300% 100%;
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text;
  animation: heroShimmer 5s ease-in-out infinite;
}
@keyframes heroShimmer {
  0%, 100% { background-position: 0% 0; }
  50%       { background-position: 100% 0; }
}

.hero-sub {
  font-size: 0.95em; font-weight: 300;
  color: rgba(255,255,255,0.6); letter-spacing: 0.5px;
  margin-bottom: 24px;
  opacity: 0; animation: fadeUp 0.9s forwards 0.75s;
}

.hero-divider-line {
  display: flex; align-items: center; justify-content: center; gap: 14px;
  opacity: 0; animation: fadeUp 0.8s forwards 0.95s;
}
.hero-divider-line span { display: block; width: 70px; height: 1px; }
.hero-divider-line span:first-child {
  background: linear-gradient(to right, transparent, #D4AF37);
}
.hero-divider-line span:last-child {
  background: linear-gradient(to left, transparent, #D4AF37);
}
.hero-gems { color: var(--gold); font-size: 0.75em; letter-spacing: 5px; }

/* Sparkles in hero */
.hero-sparkle {
  position: absolute; border-radius: 50%;
  pointer-events: none;
  animation: sparkleRise linear infinite;
}
@keyframes sparkleRise {
  0%   { transform: translateY(0) rotate(0deg) scale(0.5); opacity: 0; }
  15%  { opacity: 1; }
  85%  { opacity: 0.7; }
  100% { transform: translateY(-400px) rotate(360deg) scale(1.2); opacity: 0; }
}

/* Hero bottom fade */
.hero::after {
  content: '';
  position: absolute; bottom: 0; left: 0; right: 0;
  height: 80px;
  background: linear-gradient(to bottom, transparent, var(--bg1));
  z-index: 2;
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(22px); }
  to   { opacity: 1; transform: translateY(0); }
}
@keyframes goldSlide {
  0%   { background-position: 0% 0; }
  100% { background-position: 200% 0; }
}

/* ═══════════════════════════════════════════
   MAIN WRAPPER
═══════════════════════════════════════════ */
.page-wrapper {
  position: relative; z-index: 1;
  padding: 48px 28px 100px;
  max-width: 1140px;
  margin: 0 auto;
}

/* ═══════════════════════════════════════════
   TOP BAR
═══════════════════════════════════════════ */
.top-bar {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 36px;
  opacity: 0; animation: fadeUp 0.7s forwards 0.8s;
}
.eyebrow {
  font-size: 0.7em; font-weight: 700; letter-spacing: 4px;
  text-transform: uppercase; color: var(--gold);
  display: flex; align-items: center; gap: 8px;
}
.eyebrow::before {
  content: ''; display: block; width: 24px; height: 1px;
  background: var(--gold);
}

.btn-home {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 10px 24px;
  background: rgba(212,175,55,0.08);
  border: 1px solid rgba(212,175,55,0.3);
  color: var(--gold); font-size: 0.82em; font-weight: 600;
  letter-spacing: 0.5px; border-radius: 999px; text-decoration: none;
  transition: transform 0.25s, box-shadow 0.3s, background 0.3s;
  position: relative; overflow: hidden;
}
.btn-home::before {
  content: '';
  position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
  background: linear-gradient(90deg, transparent, rgba(212,175,55,0.15), transparent);
  transition: left 0.4s;
}
.btn-home:hover::before { left: 100%; }
.btn-home:hover {
  transform: translateX(-3px);
  background: rgba(212,175,55,0.18);
  box-shadow: 0 6px 24px rgba(212,175,55,0.28);
}

/* ═══════════════════════════════════════════
   WELCOME HERO CARD
═══════════════════════════════════════════ */
.welcome-card {
  position: relative;
  background: var(--glass);
  backdrop-filter: blur(28px) saturate(1.3);
  border: 1px solid var(--glass-border);
  border-radius: 28px;
  padding: 48px 52px;
  margin-bottom: 28px;
  overflow: hidden;
  opacity: 0; animation: fadeUp 0.9s forwards 0.95s;
  transition: background 0.4s, border-color 0.4s;
}
.welcome-card::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
  background: linear-gradient(90deg, #D4AF37, #EE2A7B, #8A2BE2, #D4AF37);
  background-size: 300% 100%;
  animation: goldSlide 4s linear infinite;
}
/* Decorative corner orb */
.welcome-card::after {
  content: '';
  position: absolute; top: -80px; right: -80px;
  width: 280px; height: 280px;
  background: radial-gradient(circle, rgba(212,175,55,0.14) 0%, transparent 65%);
  border-radius: 50%;
}

.wc-top {
  display: flex; align-items: flex-start; justify-content: space-between;
  flex-wrap: wrap; gap: 20px;
}
.wc-left { flex: 1; }

.wc-avatar {
  width: 64px; height: 64px; border-radius: 50%;
  background: linear-gradient(135deg, #D4AF37, #EE2A7B, #8A2BE2);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.6em;
  margin-bottom: 18px;
  box-shadow: 0 0 30px rgba(212,175,55,0.4);
  animation: avatarPulse 3s ease-in-out infinite;
}
@keyframes avatarPulse {
  0%, 100% { box-shadow: 0 0 30px rgba(212,175,55,0.4); transform: scale(1); }
  50%       { box-shadow: 0 0 50px rgba(212,175,55,0.6); transform: scale(1.04); }
}

.wc-greeting {
  font-size: 0.75em; letter-spacing: 3px; text-transform: uppercase;
  color: var(--text-muted); margin-bottom: 6px;
}

.wc-name {
  font-family: 'Playfair Display', serif;
  font-size: 2.4em; font-weight: 700;
  background: linear-gradient(135deg, var(--text) 30%, var(--gold) 70%);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text; margin-bottom: 10px; line-height: 1.1;
}

.wc-desc {
  font-size: 0.88em; color: var(--text-muted); line-height: 1.6;
  max-width: 420px;
}

.wc-badges {
  display: flex; flex-direction: column; align-items: flex-end; gap: 10px;
}

.badge-gold {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 10px 20px;
  background: linear-gradient(135deg, rgba(212,175,55,0.22), rgba(212,175,55,0.08));
  border: 1px solid rgba(212,175,55,0.45);
  border-radius: 999px;
  font-size: 0.78em; font-weight: 700; letter-spacing: 2px;
  color: var(--gold); text-transform: uppercase;
  box-shadow: 0 0 20px rgba(212,175,55,0.2);
  position: relative; overflow: hidden;
}
.badge-gold::before {
  content: '';
  position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
  background: linear-gradient(90deg, transparent, rgba(212,175,55,0.3), transparent);
  animation: shimmerBadge 3s linear infinite;
}

.badge-verified {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 7px 16px;
  background: rgba(46,213,115,0.1);
  border: 1px solid rgba(46,213,115,0.3);
  border-radius: 999px;
  font-size: 0.72em; font-weight: 600; color: #2ed573;
}

/* Progress bar to next tier */
.tier-progress {
  margin-top: 28px;
  padding-top: 24px;
  border-top: 1px solid rgba(255,255,255,0.07);
}
.tier-labels {
  display: flex; justify-content: space-between;
  font-size: 0.72em; color: var(--text-muted); margin-bottom: 10px;
}
.tier-labels strong { color: var(--gold); }
.tier-bar-bg {
  width: 100%; height: 8px; border-radius: 99px;
  background: rgba(255,255,255,0.08);
  overflow: hidden; position: relative;
}
.tier-bar-fill {
  height: 100%; border-radius: 99px;
  background: linear-gradient(90deg, #D4AF37, #EE2A7B, #8A2BE2);
  background-size: 200% 100%;
  animation: goldSlide 3s linear infinite;
  width: 0;
  transition: width 1.8s cubic-bezier(0.22, 1, 0.36, 1);
}
.tier-bar-glow {
  position: absolute; top: -3px; right: 0;
  width: 16px; height: 14px; border-radius: 50%;
  background: #FFE88A;
  box-shadow: 0 0 14px 4px rgba(212,175,55,0.7);
  animation: barGlowPulse 1.5s ease-in-out infinite;
}
@keyframes barGlowPulse {
  0%, 100% { opacity: 0.8; transform: scale(1); }
  50%       { opacity: 1;   transform: scale(1.3); }
}

/* ═══════════════════════════════════════════
   STATS ROW
═══════════════════════════════════════════ */
.stats-row {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px;
  margin-bottom: 28px;
  opacity: 0; animation: fadeUp 0.8s forwards 1.1s;
}

.stat-card {
  position: relative;
  background: var(--glass);
  backdrop-filter: blur(20px);
  border: 1px solid var(--glass-border);
  border-radius: 22px; padding: 28px 24px;
  display: flex; flex-direction: column; gap: 6px;
  overflow: hidden;
  transition: border-color 0.35s, box-shadow 0.35s, transform 0.3s, background 0.4s;
  cursor: default;
}
.stat-card::before {
  content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 3px;
  background: var(--stat-color, #D4AF37);
  transform: scaleX(0); transform-origin: left;
  transition: transform 0.4s ease;
}
.stat-card:hover::before { transform: scaleX(1); }
.stat-card:hover {
  background: var(--glass-hover);
  border-color: rgba(212,175,55,0.3);
  box-shadow: 0 12px 40px rgba(0,0,0,0.3), 0 0 30px var(--stat-glow, rgba(212,175,55,0.15));
  transform: translateY(-5px);
}
.stat-card-1 { --stat-color: #D4AF37; --stat-glow: rgba(212,175,55,0.2); }
.stat-card-2 { --stat-color: #EE2A7B; --stat-glow: rgba(238,42,123,0.2); }
.stat-card-3 { --stat-color: #8A2BE2; --stat-glow: rgba(138,43,226,0.2); }

.stat-icon-wrap {
  width: 42px; height: 42px; border-radius: 12px;
  background: var(--stat-icon-bg, rgba(212,175,55,0.12));
  display: flex; align-items: center; justify-content: center;
  font-size: 1.3em; margin-bottom: 8px;
}
.stat-card-1 .stat-icon-wrap { background: rgba(212,175,55,0.12); }
.stat-card-2 .stat-icon-wrap { background: rgba(238,42,123,0.12); }
.stat-card-3 .stat-icon-wrap { background: rgba(138,43,226,0.12); }

.stat-val {
  font-family: 'Playfair Display', serif;
  font-size: 2em; font-weight: 700;
  color: var(--stat-color, #D4AF37); line-height: 1;
  counter-reset: none;
}
.stat-lbl {
  font-size: 0.72em; color: var(--text-muted); letter-spacing: 1px;
  text-transform: uppercase;
}

/* ═══════════════════════════════════════════
   SECTION CARD BASE
═══════════════════════════════════════════ */
.section-card {
  position: relative;
  background: var(--glass);
  backdrop-filter: blur(24px);
  border: 1px solid var(--glass-border);
  border-radius: 28px; padding: 40px 40px 36px;
  margin-bottom: 24px; overflow: hidden;
  opacity: 0;
  transition: background 0.4s, border-color 0.4s;
}
.section-card.d1 { animation: fadeUp 0.8s forwards 1.2s; }
.section-card.d2 { animation: fadeUp 0.8s forwards 1.35s; }
.section-card.d3 { animation: fadeUp 0.8s forwards 1.5s; }
.section-card.d4 { animation: fadeUp 0.8s forwards 1.65s; }
.section-card.d5 { animation: fadeUp 0.8s forwards 1.8s; }

/* ═══════════════════════════════════════════
   GAMIFIKASI SECTION (Kado Poin & Streak)
═══════════════════════════════════════════ */
.gamif-grid {
  display: grid; grid-template-columns: repeat(2, 1fr); gap: 18px;
}
.gamif-card {
  position: relative; border-radius: 20px; padding: 26px 22px;
  border: 1px solid var(--glass-border); background: rgba(255,255,255,0.03);
  text-decoration: none; color: var(--text); display: block;
  transition: transform 0.25s, border-color 0.25s, background 0.25s;
  overflow: hidden;
}
.gamif-card:hover { transform: translateY(-4px); border-color: rgba(212,175,55,0.4); background: rgba(255,255,255,0.06); }
.gamif-card-icon {
  width: 48px; height: 48px; border-radius: 14px; margin-bottom: 14px;
  display: flex; align-items: center; justify-content: center;
}
.gamif-card.kado .gamif-card-icon { background: linear-gradient(135deg, rgba(212,175,55,0.22), rgba(238,42,123,0.14)); color: var(--gold); }
.gamif-card.streak .gamif-card-icon { background: linear-gradient(135deg, rgba(255,122,61,0.22), rgba(212,175,55,0.14)); color: #FF7A3D; }
.gamif-card-title { font-family: 'Playfair Display', serif; font-weight: 700; font-size: 1.05em; margin-bottom: 6px; }
.gamif-card-desc { font-size: 0.82em; color: var(--text-muted); line-height: 1.5; }
.gamif-card-badge {
  display: inline-flex; align-items: center; gap: 6px; margin-top: 14px;
  font-size: 0.75em; font-weight: 700; padding: 5px 12px; border-radius: 999px;
  background: rgba(255,122,61,0.15); color: #FFB088;
}
@media (max-width: 640px) { .gamif-grid { grid-template-columns: 1fr; } }

/* ═══════════════════════════════════════════
   NOTIFICATION BELL
═══════════════════════════════════════════ */
.notif-wrap { position: relative; }
.notif-dot {
  position: absolute; top: 6px; right: 6px; width: 9px; height: 9px; border-radius: 50%;
  background: var(--rose); border: 2px solid var(--bg1); display: none;
}
.notif-dot.show { display: block; }
.notif-dropdown {
  position: absolute; top: 54px; right: 0; width: 320px; max-height: 400px; overflow-y: auto;
  background: rgba(13,5,32,0.97); backdrop-filter: blur(20px);
  border: 1px solid var(--glass-border); border-radius: 18px; padding: 10px;
  opacity: 0; transform: translateY(-10px) scale(0.97); pointer-events: none;
  transition: 0.25s; z-index: 50; box-shadow: 0 20px 50px rgba(0,0,0,0.4);
}
.notif-dropdown.show { opacity: 1; transform: translateY(0) scale(1); pointer-events: auto; }
.notif-item { padding: 12px 14px; border-radius: 12px; margin-bottom: 4px; }
.notif-item:not(.unread) { opacity: 0.6; }
.notif-item.unread { background: rgba(212,175,55,0.08); }
.notif-title { font-size: 0.85em; font-weight: 700; margin-bottom: 3px; }
.notif-msg { font-size: 0.78em; color: var(--text-muted); line-height: 1.4; }
.notif-time { font-size: 0.68em; color: rgba(255,255,255,0.35); margin-top: 4px; }
.notif-empty { padding: 24px 14px; text-align: center; color: var(--text-muted); font-size: 0.85em; }

.section-card::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
  background: linear-gradient(90deg, #D4AF37, #EE2A7B, #8A2BE2, #D4AF37);
  background-size: 300% 100%;
  animation: goldSlide 5s linear infinite;
}

.section-header {
  display: flex; align-items: center; gap: 14px; margin-bottom: 28px;
}
.section-icon {
  width: 46px; height: 46px; border-radius: 14px; flex-shrink: 0;
  background: linear-gradient(135deg, rgba(212,175,55,0.2), rgba(212,175,55,0.06));
  border: 1px solid rgba(212,175,55,0.25);
  display: flex; align-items: center; justify-content: center; font-size: 1.3em;
}
.section-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.3em; font-weight: 700; color: var(--text); flex: 1;
}
.section-title::after {
  content: ''; display: block; margin-top: 4px; height: 1px;
  background: linear-gradient(to right, rgba(212,175,55,0.3), transparent);
}

/* ═══════════════════════════════════════════
   STATUS SECTION
═══════════════════════════════════════════ */
.status-grid {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;
}
.status-box {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 18px; padding: 28px 20px; text-align: center;
  transition: border-color 0.35s, box-shadow 0.35s, transform 0.3s;
  cursor: default;
}
body.light .status-box { background: rgba(0,0,0,0.04); border-color: rgba(0,0,0,0.07); }
.status-box:hover {
  border-color: rgba(212,175,55,0.38);
  box-shadow: 0 0 32px rgba(212,175,55,0.18), 0 8px 24px rgba(0,0,0,0.2);
  transform: translateY(-6px) scale(1.02);
}
.status-box:nth-child(1):hover { border-color: rgba(46,213,115,0.4); box-shadow: 0 0 28px rgba(46,213,115,0.18); }
.status-box:nth-child(2):hover { border-color: rgba(212,175,55,0.4); box-shadow: 0 0 28px rgba(212,175,55,0.2); }
.status-box:nth-child(3):hover { border-color: rgba(138,43,226,0.4); box-shadow: 0 0 28px rgba(138,43,226,0.18); }

.status-box .sb-icon {
  font-size: 2.4em; margin-bottom: 14px; display: block;
  filter: drop-shadow(0 0 8px currentColor);
}
.status-box h3 {
  font-size: 0.68em; letter-spacing: 3px; text-transform: uppercase;
  color: var(--gold); margin-bottom: 8px; font-weight: 700;
}
.status-box p {
  font-family: 'Playfair Display', serif; font-size: 1.05em; color: var(--text);
}

/* ═══════════════════════════════════════════
   PROMO SECTION
═══════════════════════════════════════════ */
.promo-grid {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 18px;
}
.promo-box {
  position: relative; overflow: hidden;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(238,42,123,0.18);
  border-radius: 20px; padding: 32px 26px; text-align: center;
  transition: border-color 0.35s, box-shadow 0.35s, transform 0.3s;
  cursor: default;
}
body.light .promo-box { background: rgba(0,0,0,0.03); border-color: rgba(238,42,123,0.2); }
.promo-box::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
  background: linear-gradient(90deg, #EE2A7B, #D4AF37, #8A2BE2, #EE2A7B);
  background-size: 300% 100%;
  animation: goldSlide 4s linear infinite;
}
.promo-box::after {
  content: '';
  position: absolute; bottom: -30px; right: -30px;
  width: 100px; height: 100px;
  background: radial-gradient(circle, rgba(238,42,123,0.1) 0%, transparent 70%);
  border-radius: 50%;
  transition: transform 0.4s;
}
.promo-box:hover { transform: translateY(-7px) scale(1.02); border-color: rgba(238,42,123,0.4); box-shadow: 0 12px 40px rgba(238,42,123,0.18); }
.promo-box:hover::after { transform: scale(1.5); }

.promo-tag {
  display: inline-block;
  font-size: 0.65em; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
  color: #EE2A7B; background: rgba(238,42,123,0.1);
  border: 1px solid rgba(238,42,123,0.25);
  border-radius: 999px; padding: 4px 12px; margin-bottom: 12px;
}
.promo-code {
  font-family: 'Playfair Display', serif; font-size: 1.5em; font-weight: 700;
  background: linear-gradient(135deg, #EE2A7B, #D4AF37);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text; margin-bottom: 10px;
}
.promo-box p { font-size: 0.83em; color: var(--text-muted); line-height: 1.5; margin-bottom: 16px; }
.promo-copy-btn {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 7px 18px;
  background: rgba(238,42,123,0.1);
  border: 1px solid rgba(238,42,123,0.3);
  border-radius: 999px;
  font-size: 0.72em; font-weight: 600; color: #EE2A7B;
  cursor: pointer; transition: background 0.2s, box-shadow 0.2s;
}
.promo-copy-btn:hover { background: rgba(238,42,123,0.2); box-shadow: 0 0 14px rgba(238,42,123,0.25); }
.promo-copy-btn.copied { color: #2ed573; border-color: rgba(46,213,115,0.4); background: rgba(46,213,115,0.08); }

/* ═══════════════════════════════════════════
   POINTS SECTION
═══════════════════════════════════════════ */
.points-hero {
  background: linear-gradient(135deg, rgba(212,175,55,0.14), rgba(212,175,55,0.04));
  border: 1px solid rgba(212,175,55,0.28);
  border-radius: 22px; padding: 40px;
  text-align: center; margin-bottom: 22px;
  position: relative; overflow: hidden;
  transition: box-shadow 0.35s;
}
.points-hero:hover { box-shadow: 0 0 50px rgba(212,175,55,0.22); }
.points-hero-bg-icon {
  position: absolute; top: -20px; right: 24px;
  font-size: 7em; opacity: 0.05; pointer-events: none; color: #D4AF37;
  animation: starFloat 6s ease-in-out infinite;
}
@keyframes starFloat {
  0%, 100% { transform: translateY(0) rotate(0deg); }
  50%       { transform: translateY(-15px) rotate(20deg); }
}
.points-hero::after {
  content: '';
  position: absolute; bottom: -40px; left: -40px;
  width: 150px; height: 150px;
  background: radial-gradient(circle, rgba(212,175,55,0.14) 0%, transparent 65%);
  border-radius: 50%;
}

.points-number {
  font-family: 'Playfair Display', serif; font-size: 5em; font-weight: 700;
  background: linear-gradient(135deg, #D4AF37 0%, #FFE88A 40%, #D4AF37 60%, #A07C10 100%);
  background-size: 200% 100%;
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text;
  animation: goldSlide 3s ease-in-out infinite;
  line-height: 1; margin-bottom: 6px;
}
.points-lbl {
  font-size: 0.8em; color: var(--text-muted);
  letter-spacing: 3px; text-transform: uppercase; margin-bottom: 20px;
}
.points-mini-bar-bg {
  width: 100%; height: 6px; background: rgba(255,255,255,0.08);
  border-radius: 99px; overflow: hidden; margin: 0 auto; max-width: 320px;
}
.points-mini-bar-fill {
  height: 100%; border-radius: 99px; width: 50%;
  background: linear-gradient(90deg, #D4AF37, #FFE88A, #D4AF37);
  background-size: 200%;
  animation: goldSlide 2s linear infinite;
}

.reward-grid {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 14px;
}
.reward-box {
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(212,175,55,0.18);
  border-radius: 16px; padding: 22px 16px; text-align: center;
  transition: border-color 0.35s, box-shadow 0.35s, transform 0.3s; cursor: default;
  position: relative; overflow: hidden;
}
body.light .reward-box { background: rgba(0,0,0,0.03); }
.reward-box.unlocked { border-color: rgba(212,175,55,0.35); }
.reward-box.locked { opacity: 0.55; }
.reward-box:hover {
  border-color: rgba(212,175,55,0.45);
  box-shadow: 0 0 24px rgba(212,175,55,0.2);
  transform: translateY(-4px);
}
.reward-box .lock-overlay {
  position: absolute; top: 8px; right: 10px;
  font-size: 0.8em; opacity: 0.5;
}
.reward-icon { font-size: 2em; margin-bottom: 10px; }
.reward-poin {
  font-size: 0.68em; color: rgba(212,175,55,0.7);
  letter-spacing: 1px; font-weight: 700; margin-bottom: 4px;
  text-transform: uppercase;
}
.reward-name { font-family: 'Playfair Display', serif; font-size: 0.92em; color: var(--text); }

/* ═══════════════════════════════════════════
   HISTORY SECTION
═══════════════════════════════════════════ */
.history-grid {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 16px;
}
.history-item {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-left: 3px solid var(--gold);
  border-radius: 16px; padding: 24px;
  transition: border-color 0.35s, box-shadow 0.35s, transform 0.3s; cursor: default;
}
body.light .history-item { background: rgba(0,0,0,0.04); border-color: rgba(0,0,0,0.07); border-left-color: var(--gold); }
.history-item:hover {
  border-left-color: #FFE88A;
  box-shadow: 0 0 28px rgba(212,175,55,0.16); transform: translateY(-4px);
}
.history-item h3 {
  font-size: 0.68em; letter-spacing: 2px; text-transform: uppercase;
  color: rgba(212,175,55,0.75); margin-bottom: 10px; font-weight: 700;
}
.history-item p {
  font-family: 'Playfair Display', serif; font-size: 1.05em; color: var(--text);
}

/* ═══════════════════════════════════════════
   EXCLUSIVE BENEFITS SECTION
═══════════════════════════════════════════ */
.benefits-grid {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;
}
.benefit-card {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 18px; padding: 24px 20px;
  display: flex; flex-direction: column; gap: 10px;
  transition: border-color 0.35s, box-shadow 0.35s, transform 0.3s;
  cursor: default;
}
body.light .benefit-card { background: rgba(0,0,0,0.03); border-color: rgba(0,0,0,0.07); }
.benefit-card:hover {
  border-color: rgba(212,175,55,0.32);
  box-shadow: 0 8px 30px rgba(212,175,55,0.14);
  transform: translateY(-5px);
}
.benefit-num {
  font-family: 'Cormorant Garamond', serif;
  font-size: 2.4em; font-weight: 300;
  color: rgba(212,175,55,0.25); line-height: 1;
}
.benefit-title { font-weight: 700; font-size: 0.92em; color: var(--text); }
.benefit-desc { font-size: 0.78em; color: var(--text-muted); line-height: 1.5; }

/* ═══════════════════════════════════════════
   TOAST NOTIFICATION
═══════════════════════════════════════════ */
.toast {
  position: fixed; bottom: 30px; right: 30px; z-index: 9999;
  padding: 14px 24px;
  background: rgba(46,213,115,0.12);
  border: 1px solid rgba(46,213,115,0.4);
  backdrop-filter: blur(16px);
  border-radius: 14px;
  color: #2ed573; font-weight: 600; font-size: 0.85em;
  display: flex; align-items: center; gap: 10px;
  transform: translateY(20px); opacity: 0;
  transition: transform 0.35s ease, opacity 0.35s ease;
  pointer-events: none;
}
.toast.show { transform: translateY(0); opacity: 1; }

/* ═══════════════════════════════════════════
   FOOTER
═══════════════════════════════════════════ */
.footer {
  position: relative; z-index: 1;
  text-align: center; padding: 40px 20px;
  font-size: 0.78em; color: var(--text-muted);
  border-top: 1px solid rgba(255,255,255,0.06);
  line-height: 1.9;
}
body.light .footer { border-top-color: rgba(0,0,0,0.07); }
.footer-brand {
  font-family: 'Playfair Display', serif; font-size: 1.2em;
  background: linear-gradient(135deg, #D4AF37, #FFE88A);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text; margin-bottom: 6px;
}

/* ═══════════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════════ */
@media(max-width: 768px) {
  nav { padding: 12px 18px; }
  .nav-premium-badge { display: none; }
  .hero { height: 300px; }
  .hero-title { font-size: 2.6em; }
  .page-wrapper { padding: 32px 16px 80px; }
  .welcome-card { padding: 32px 24px; }
  .wc-top { flex-direction: column; }
  .wc-badges { align-items: flex-start; }
  .stats-row { grid-template-columns: 1fr; }
  .section-card { padding: 28px 22px 26px; }
  .points-number { font-size: 3.5em; }
  .dropdown { right: 12px; min-width: 190px; }
}
</style>
</head>
<body>

<!-- FLOATING ORBS -->
<div class="orb" style="width:350px;height:350px;top:-80px;left:-100px;background:radial-gradient(circle,rgba(212,175,55,0.12),transparent 60%);animation-duration:20s;"></div>
<div class="orb" style="width:250px;height:250px;bottom:100px;right:-80px;background:radial-gradient(circle,rgba(238,42,123,0.10),transparent 60%);animation-duration:25s;animation-delay:5s;"></div>
<div class="orb" style="width:200px;height:200px;top:40%;left:60%;background:radial-gradient(circle,rgba(138,43,226,0.08),transparent 60%);animation-duration:18s;animation-delay:8s;"></div>

<!-- NAVBAR -->
<nav>
  <a class="nav-logo" href="../index.php">
    <img src="../assets/img/Yolazcake.png" alt="YOLAZCAKE">
    <h2>YOLAZCAKE</h2>
  </a>

  <div class="nav-premium-badge">
    <i data-lucide="crown" class="lucide-ic"></i> <?= htmlspecialchars($tier['name']) ?> Member
  </div>

  <div class="nav-right">
    <div class="notif-wrap">
      <button class="nav-btn" id="notifBtn" onclick="toggleNotif()" title="Notifikasi">
        <i data-lucide="bell" class="lucide-ic"></i>
        <span class="notif-dot <?= $notif_belum_baca > 0 ? 'show' : '' ?>" id="notifDot"></span>
      </button>
      <div class="notif-dropdown" id="notifDropdown">
        <div id="notifList" class="notif-empty">Memuat notifikasi...</div>
      </div>
    </div>
    <button class="nav-btn" id="darkBtn" onclick="toggleDark()" title="Toggle theme"><svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"></circle><line x1="12" y1="2" x2="12" y2="4.5"></line><line x1="12" y1="19.5" x2="12" y2="22"></line><line x1="4.2" y1="4.2" x2="5.9" y2="5.9"></line><line x1="18.1" y1="18.1" x2="19.8" y2="19.8"></line><line x1="2" y1="12" x2="4.5" y2="12"></line><line x1="19.5" y1="12" x2="22" y2="12"></line><line x1="4.2" y1="19.8" x2="5.9" y2="18.1"></line><line x1="18.1" y1="5.9" x2="19.8" y2="4.2"></line></svg><svg class="icon-moon" viewBox="0 0 24 24" fill="currentColor"><path d="M20.84 14.4a9 9 0 0 1-11.24-11.24 1 1 0 0 0-1.3-1.22A10.07 10.07 0 0 0 2 12.1 10 10 0 0 0 12 22a10.07 10.07 0 0 0 9.06-6.3 1 1 0 0 0-1.22-1.3z"></path><circle cx="18.5" cy="5.5" r="1.1"></circle><circle cx="20.5" cy="9" r="0.6"></circle></svg></button>
    <button class="nav-btn" id="hamburger" onclick="toggleMenu()" title="Menu"><i data-lucide="menu" class="lucide-ic icon-menu"></i><i data-lucide="x" class="lucide-ic icon-close"></i></button>
  </div>

  <div class="dropdown" id="dropdown">
    <a onclick="window.location.href='../index.php'"><i data-lucide="home" class="lucide-ic"></i> Home</a>
    <a onclick="window.location.href='../produk/menu.php'"><i data-lucide="coffee" class="lucide-ic"></i> Menu</a>
    <a onclick="window.location.href='../gallery.php'"><i data-lucide="image" class="lucide-ic"></i> Gallery</a>
    <a onclick="window.location.href='../about.php'"><i data-lucide="sparkles" class="lucide-ic"></i> About</a>
    <a onclick="window.location.href='../contact.php'"><i data-lucide="phone" class="lucide-ic"></i> Contact</a>
    <div class="dd-divider"></div>
    <a onclick="window.location.href='../auth/logout.php'" class="dd-logout"><i data-lucide="log-out" class="lucide-ic"></i> Logout</a>
  </div>
</nav>

<!-- HERO -->
<div class="hero" id="heroSection">
  <div class="hero-mesh"></div>
  <div class="hero-grid"></div>
  <div class="hero-inner">
    <div class="hero-crown"><i data-lucide="crown" class="lucide-ic"></i> Exclusive Member Area</div>
    <h1 class="hero-title"><span>Member Dashboard</span></h1>
    <p class="hero-sub">Selamat datang di ruang eksklusif YOLAZCAKE Sintang</p>
    <div class="hero-divider-line">
      <span></span>
      <span class="hero-gems"><i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i></span>
      <span></span>
    </div>
  </div>
</div>

<!-- MAIN CONTENT -->
<div class="page-wrapper">

  <!-- TOP BAR -->
  <div class="top-bar">
    <div class="eyebrow">Member Dashboard</div>
    <a href="../index.php" class="btn-home"><i data-lucide="home" class="lucide-ic"></i> Kembali ke Website</a>
  </div>

  <!-- WELCOME CARD -->
  <div class="welcome-card">
    <div class="wc-top">
      <div class="wc-left">
        <div class="wc-avatar"><i data-lucide="crown" class="lucide-ic"></i></div>
        <div class="wc-greeting">Selamat Datang Kembali</div>
        <div class="wc-name">
          <?php echo htmlspecialchars($_SESSION['username']); ?>
        </div>
        <div class="wc-desc">
          Nikmati berbagai keistimewaan eksklusif yang telah kami siapkan khusus untuk Anda sebagai Gold Member YOLAZCAKE.
        </div>
      </div>
      <div class="wc-badges">
        <div class="badge-gold"><i data-lucide="sparkle" class="lucide-ic"></i> <?= htmlspecialchars($tier['name']) ?> Member <i data-lucide="sparkle" class="lucide-ic"></i></div>
        <div class="badge-verified"><i data-lucide="check" class="lucide-ic"></i> Akun Terverifikasi</div>
      </div>
    </div>

    <div class="tier-progress">
      <div class="tier-labels">
        <span><?= htmlspecialchars($tier['name']) ?> Member · <strong><?= $poin ?> Poin</strong></span>
        <span>
          <?php if ($tier['next_name']): ?>
            <?= $tier['sisa'] ?> poin lagi menuju <strong style="color:var(--rose)"><?= htmlspecialchars($tier['next_name']) ?></strong>
          <?php else: ?>
            <i data-lucide="party-popper" class="lucide-ic"></i> Anda sudah di tier tertinggi!
          <?php endif; ?>
        </span>
      </div>
      <div class="tier-bar-bg">
        <div class="tier-bar-fill" id="tierBarFill"></div>
        <div class="tier-bar-glow" id="tierBarGlow"></div>
      </div>
    </div>
  </div>

  <!-- STATS ROW -->
  <div class="stats-row">
    <div class="stat-card stat-card-1">
      <div class="stat-icon-wrap"><i data-lucide="star" class="lucide-ic lucide-fill"></i></div>
      <div class="stat-val" data-count="<?= $poin ?>"><?= $poin ?></div>
      <div class="stat-lbl">Poin Terkumpul</div>
    </div>
    <div class="stat-card stat-card-2">
      <div class="stat-icon-wrap"><i data-lucide="target" class="lucide-ic"></i></div>
      <div class="stat-val"><?= htmlspecialchars($tier['name']) ?></div>
      <div class="stat-lbl">Level Member</div>
    </div>
    <div class="stat-card stat-card-3">
      <div class="stat-icon-wrap"><i data-lucide="calendar" class="lucide-ic"></i></div>
      <div class="stat-val" data-count="<?= $bulan_bergabung ?>"><?= $bulan_bergabung ?></div>
      <div class="stat-lbl">Bulan Bergabung</div>
    </div>
  </div>

  <!-- STATUS SECTION -->
  <div class="section-card d1">
    <div class="section-header">
      <div class="section-icon"><i data-lucide="check-circle" class="lucide-ic"></i></div>
      <div class="section-title">Status Member Anda</div>
    </div>
    <div class="status-grid">
      <div class="status-box">
        <span class="sb-icon"><i data-lucide="check-circle" class="lucide-ic"></i></span>
        <h3>Status</h3>
        <p>Member Aktif</p>
      </div>
      <div class="status-box">
        <span class="sb-icon"><i data-lucide="target" class="lucide-ic"></i></span>
        <h3>Tier</h3>
        <p><?= htmlspecialchars($tier['name']) ?> Member</p>
      </div>
      <div class="status-box">
        <span class="sb-icon"><i data-lucide="calendar-days" class="lucide-ic"></i></span>
        <h3>Bergabung</h3>
        <p><?= $bulan_bergabung ?> Bulan Lalu</p>
      </div>
    </div>
  </div>

  <!-- BENEFITS SECTION (NEW) -->
  <div class="section-card d2">
    <div class="section-header">
      <div class="section-icon"><i data-lucide="gem" class="lucide-ic"></i></div>
      <div class="section-title">Keistimewaan Gold Member</div>
    </div>
    <div class="benefits-grid">
      <div class="benefit-card">
        <div class="benefit-num">01</div>
        <div class="benefit-title">Priority Order</div>
        <div class="benefit-desc">Pesanan Anda diproses lebih cepat dari pelanggan reguler.</div>
      </div>
      <div class="benefit-card">
        <div class="benefit-num">02</div>
        <div class="benefit-title">Diskon Eksklusif</div>
        <div class="benefit-desc">Diskon khusus hingga 15% setiap transaksi di YOLAZCAKE.</div>
      </div>
      <div class="benefit-card">
        <div class="benefit-num">03</div>
        <div class="benefit-title">Early Access</div>
        <div class="benefit-desc">Akses pertama ke menu baru dan promo spesial sebelum umum.</div>
      </div>
      <div class="benefit-card">
        <div class="benefit-num">04</div>
        <div class="benefit-title">Birthday Reward</div>
        <div class="benefit-desc">Hadiah spesial cake gratis di hari ulang tahun Anda.</div>
      </div>
    </div>
  </div>

  <!-- PROMO SECTION -->
  <div class="section-card d2">
    <div class="section-header">
      <div class="section-icon"><i data-lucide="gift" class="lucide-ic"></i></div>
      <div class="section-title">Promo Khusus Member</div>
    </div>
    <div class="promo-grid">
      <?php if (empty($promo_aktif)): ?>
        <p style="color:var(--text-muted);">Belum ada promo aktif saat ini. Pantau terus halaman ini ya!</p>
      <?php else: foreach ($promo_aktif as $p): ?>
      <div class="promo-box">
        <div class="promo-tag"><?= $p['min_belanja'] > 0 ? 'Min. Rp'.number_format($p['min_belanja'],0,',','.') : 'Aktif Sekarang' ?></div>
        <div class="promo-code"><?= htmlspecialchars($p['kode_promo']) ?></div>
        <p><?= htmlspecialchars($p['deskripsi'] ?: ($p['diskon_persen'].'% diskon untuk pembelian ini.')) ?></p>
        <button class="promo-copy-btn" onclick="copyCode(this, '<?= htmlspecialchars($p['kode_promo'], ENT_QUOTES) ?>')"><i data-lucide="clipboard-list" class="lucide-ic"></i> Salin Kode</button>
      </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <!-- POINTS SECTION -->
  <div class="section-card d3">
    <div class="section-header">
      <div class="section-icon"><i data-lucide="star" class="lucide-ic lucide-fill"></i></div>
      <div class="section-title">Poin & Reward</div>
    </div>
    <div class="points-hero">
      <i data-lucide="star" class="lucide-ic lucide-fill points-hero-bg-icon"></i>
      <div class="points-number"><?= $poin ?></div>
      <div class="points-lbl">Total Poin Terkumpul</div>
      <div class="points-mini-bar-bg">
        <div class="points-mini-bar-fill"></div>
      </div>
    </div>
    <div class="reward-grid">
      <?php foreach ($reward_milestones as $r): $unlocked = $poin >= $r['poin']; ?>
      <div class="reward-box <?= $unlocked ? 'unlocked' : 'locked' ?>">
        <?php if (!$unlocked): ?><span class="lock-overlay"><i data-lucide="lock" class="lucide-ic"></i></span><?php endif; ?>
        <div class="reward-icon"><?= $r['icon'] ?></div>
        <div class="reward-poin"><?= $r['poin'] ?> Poin</div>
        <div class="reward-name"><?= htmlspecialchars($r['nama']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- GAMIFIKASI SECTION -->
  <div class="section-card d5">
    <div class="section-header">
      <div class="section-icon"><i data-lucide="sparkles" class="lucide-ic"></i></div>
      <div class="section-title">Gamifikasi Member</div>
    </div>
    <div class="gamif-grid">
      <a href="kado_poin.php" class="gamif-card kado">
        <div class="gamif-card-icon"><i data-lucide="gift" class="lucide-ic"></i></div>
        <div class="gamif-card-title">Kado Poin</div>
        <div class="gamif-card-desc">Kirim poinmu ke teman sesama member, bikin dia kaget dapat notifikasi kado!</div>
      </a>
      <a href="streak.php" class="gamif-card streak">
        <div class="gamif-card-icon"><i data-lucide="flame" class="lucide-ic"></i></div>
        <div class="gamif-card-title">Streak & Badge</div>
        <div class="gamif-card-desc">Checkin tiap hari biar streak nggak putus dan buka badge eksklusif.</div>
        <div class="gamif-card-badge"><i data-lucide="flame" class="lucide-ic"></i> <?= $streak_info['streak_saat_ini'] ?> hari berturut-turut</div>
      </a>
    </div>
  </div>

  <!-- HISTORY SECTION -->
  <div class="section-card d4">
    <div class="section-header">
      <div class="section-icon"><i data-lucide="clipboard-list" class="lucide-ic"></i></div>
      <div class="section-title">Riwayat & Aktivitas</div>
    </div>
    <div class="history-grid">
      <div class="history-item">
        <h3>Login Terakhir</h3>
        <p><?php echo date("d F Y"); ?></p>
      </div>
      <div class="history-item">
        <h3>Status Hari Ini</h3>
        <p>Aktif <i data-lucide="check-circle" class="lucide-ic"></i></p>
      </div>
      <div class="history-item">
        <h3>Bergabung Sejak</h3>
        <p><?= !empty($member['created_at']) ? date("d F Y", strtotime($member['created_at'])) : '-' ?></p>
      </div>
      <div class="history-item">
        <h3>Poin Didapat Bulan Ini</h3>
        <p><?= $poin_bulan_ini >= 0 ? '+' : '' ?><?= $poin_bulan_ini ?> Poin</p>
      </div>
    </div>
  </div>

</div><!-- /page-wrapper -->

<!-- FOOTER -->
<div class="footer">
  <div class="footer-brand">YOLAZCAKE</div>
  © 2026 YOLAZCAKE Sintang · Cafe · Bakery · Boutique<br>
  Jl. Lintas Melawi, Ladang, Sintang, Kalimantan Barat<br>
  <i data-lucide="smartphone" class="lucide-ic"></i> WA: 0815-7815-7888
</div>

<!-- TOAST -->
<div class="toast" id="toast"><i data-lucide="check-circle" class="lucide-ic"></i> Kode disalin!</div>

<script>
/* ══ HERO SPARKLES ══ */
(function(){
  const hero = document.getElementById('heroSection');
  const colors = ['#D4AF37','#FFE88A','#EE2A7B','#FF6B9E','#ffffff','#8A2BE2'];
  for(let i = 0; i < 30; i++){
    const el = document.createElement('div');
    el.className = 'hero-sparkle';
    const s = Math.random() * 5 + 1.5;
    el.style.cssText = `
      width:${s}px;height:${s}px;
      background:${colors[Math.floor(Math.random()*colors.length)]};
      left:${Math.random()*100}%;
      bottom:${Math.random()*25}%;
      animation-duration:${4 + Math.random()*8}s;
      animation-delay:${Math.random()*6}s;
      opacity:0;
    `;
    hero.appendChild(el);
  }
})();

/* ══ PROGRESS BAR ANIMATE ══ */
window.addEventListener('load', () => {
  setTimeout(() => {
    const fill = document.getElementById('tierBarFill');
    const glow = document.getElementById('tierBarGlow');
    const pct  = <?= (int) $tier['pct'] ?>; /* dihitung dari poin member yang sebenarnya */
    if(fill){ fill.style.width = pct + '%'; }
    if(glow){ glow.style.right = (100 - pct) + '%'; }
  }, 1400);
});

/* ══ COPY PROMO CODE ══ */
/* Helper: ganti isi elemen dengan markup icon lucide lalu render ulang jadi SVG.
   (Sebelumnya pakai textContent sehingga kode <i data-lucide="..."> tampil
   sebagai teks mentah, bukan icon.) */
function setIconHTML(el, html){
  el.innerHTML = html;
  if (window.lucide) lucide.createIcons();
}

function copyCode(btn, code){
  navigator.clipboard.writeText(code).then(() => {
    setIconHTML(btn, '<i data-lucide="check-circle" class="lucide-ic"></i> Tersalin!');
    btn.classList.add('copied');
    showToast('Kode "' + code + '" berhasil disalin!');
    setTimeout(() => {
      setIconHTML(btn, '<i data-lucide="clipboard-list" class="lucide-ic"></i> Salin Kode');
      btn.classList.remove('copied');
    }, 2500);
  }).catch(() => {
    /* fallback */
    setIconHTML(btn, '<i data-lucide="check-circle" class="lucide-ic"></i> Tersalin!');
    btn.classList.add('copied');
    showToast('Kode "' + code + '" berhasil disalin!');
    setTimeout(() => {
      setIconHTML(btn, '<i data-lucide="clipboard-list" class="lucide-ic"></i> Salin Kode');
      btn.classList.remove('copied');
    }, 2500);
  });
}

function showToast(msg){
  const t = document.getElementById('toast');
  setIconHTML(t, '<i data-lucide="check-circle" class="lucide-ic"></i> ' + msg);
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2800);
}

/* ══ NOTIFIKASI (KADO POIN, BADGE BARU) ══ */
function renderNotif(data){
  const list = document.getElementById('notifList');
  const dot  = document.getElementById('notifDot');
  if(!data.success || !data.notifikasi.length){
    list.innerHTML = '<div class="notif-empty"><i data-lucide="bell-off" class="lucide-ic"></i><br>Belum ada notifikasi.</div>';
    if(window.lucide) lucide.createIcons();
    return;
  }
  list.innerHTML = data.notifikasi.map(n => `
    <div class="notif-item ${n.is_read ? '' : 'unread'}">
      <div class="notif-title">${n.judul}</div>
      <div class="notif-msg">${n.pesan}</div>
      <div class="notif-time">${n.waktu}</div>
    </div>
  `).join('');
  dot.classList.toggle('show', data.belum_dibaca > 0);
}

function toggleNotif(){
  const dd = document.getElementById('notifDropdown');
  const opening = !dd.classList.contains('show');
  dd.classList.toggle('show');
  if(opening){
    fetch('notifikasi_ajax.php?action=list').then(r => r.json()).then(data => {
      renderNotif(data);
      if(data.belum_dibaca > 0){
        fetch('notifikasi_ajax.php?action=mark_read').then(() => {
          document.getElementById('notifDot').classList.remove('show');
        });
      }
    });
  }
}

document.addEventListener('click', (e) => {
  const dd  = document.getElementById('notifDropdown');
  const btn = document.getElementById('notifBtn');
  if (dd && !dd.contains(e.target) && !btn.contains(e.target)){
    dd.classList.remove('show');
  }
});

/* ══ NAVBAR HAMBURGER ══ */
function toggleMenu(){
  const dd  = document.getElementById('dropdown');
  const btn = document.getElementById('hamburger');
  const open = dd.classList.toggle('show');
  btn.classList.toggle('active', open);
}

document.querySelectorAll('.dropdown a, .dropdown p').forEach(item => {
  item.addEventListener('click', () => {
    const dd  = document.getElementById('dropdown');
    const btn = document.getElementById('hamburger');
    dd.classList.remove('show');
    btn.classList.remove('active');
  });
});

document.addEventListener('click', (e) => {
  const dd  = document.getElementById('dropdown');
  const btn = document.getElementById('hamburger');
  if (!dd.contains(e.target) && !btn.contains(e.target)){
    dd.classList.remove('show');
    btn.classList.remove('active');
  }
});

/* ══ DARK / LIGHT TOGGLE ══ */
function toggleDark(){
  const isLight = document.body.classList.toggle('light');
  localStorage.setItem('memberTheme', isLight ? 'light' : 'dark');
}

(function(){
  const saved = localStorage.getItem('memberTheme');
  if(saved === 'light'){
    document.body.classList.add('light');
  }
})();

/* ══ SCROLL REVEAL (Intersection Observer) ══ */
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if(entry.isIntersecting){
      entry.target.style.animationPlayState = 'running';
    }
  });
}, { threshold: 0.1 });

document.querySelectorAll('.section-card, .stat-card, .benefit-card, .promo-box, .reward-box').forEach(el => {
  observer.observe(el);
});

/* ══ COUNTER ANIMATION ══ */
function animateCounter(el, target, duration){
  let start = 0;
  const step = target / (duration / 16);
  const tick = () => {
    start = Math.min(start + step, target);
    el.textContent = Math.floor(start);
    if(start < target) requestAnimationFrame(tick);
  };
  requestAnimationFrame(tick);
}

window.addEventListener('load', () => {
  setTimeout(() => {
    document.querySelectorAll('.stat-val[data-count]').forEach(el => {
      animateCounter(el, parseInt(el.dataset.count), 1200);
    });
  }, 1300);
});
</script>

<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide){lucide.createIcons();}</script>
</body>
</html>
