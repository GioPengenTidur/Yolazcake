<?php
session_start();
require_once __DIR__.'/config/staff_guard.php';
require_staff_login('auth/login.php', 'member/member.php');
require_once 'config/koneksi.php';

/* ── TANDAI SUDAH MASUK MODE SERIUS (di sesi ini saja) ──
   Sekali admin/kasir sampai di dashboard penuh ini (baik lewat tombol
   "Mode Serius?" maupun link langsung), tandai di session supaya
   kunjungan berikutnya SELAMA SESI LOGIN INI MASIH AKTIF tidak lagi
   diarahkan ke dashboard_awal.php (Mode Dasar). Begitu logout (session
   hancur) dan login lagi, flag ini otomatis hilang dan Mode Dasar
   tampil lagi dari awal. */
$_SESSION['sudah_mode_serius'] = 1;

/* ── STATS ── */
$s_booking   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t, SUM(status='Pending') AS p FROM booking"));
$s_pesanan   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t, SUM(status_pesanan='Menunggu') AS p FROM pemesanan"));
$s_produk    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t, SUM(stok<=0) AS habis, SUM(stok>0 AND stok<=5) AS menipis FROM produk"));
$s_member    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t, SUM(created_at >= NOW() - INTERVAL 1 DAY) AS p FROM member"));
$s_kontak    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t, SUM(status='Belum Dibaca') AS p FROM kontak"));

/* Ulasan Produk & Ulasan Tempat butuh kolom `dibaca` (lihat
   database/tambah_kolom_dibaca_ulasan.sql). Query dibungkus try/catch
   ringan via @ supaya tidak fatal error kalau migrasi belum dijalankan. */
$q_up = mysqli_query($conn,"SELECT COUNT(*) AS t, SUM(dibaca=0) AS p FROM ulasan_produk");
$s_ulasan_produk = $q_up ? mysqli_fetch_assoc($q_up) : ['t'=>0,'p'=>0];
$q_ut = mysqli_query($conn,"SELECT COUNT(*) AS t, SUM(dibaca=0) AS p FROM ulasan_tempat");
$s_ulasan_tempat = $q_ut ? mysqli_fetch_assoc($q_ut) : ['t'=>0,'p'=>0];

/* Total produk yang butuh perhatian (habis + menipis) */
$s_stok_bermasalah = (int)($s_produk['habis'] ?? 0) + (int)($s_produk['menipis'] ?? 0);

/* ── RECENT BOOKING (5) ── */
$q_booking = mysqli_query($conn,"SELECT * FROM booking ORDER BY created_at DESC LIMIT 5");

/* ── RECENT PEMESANAN (5) ── */
$q_pesanan = mysqli_query($conn,"SELECT * FROM pemesanan ORDER BY tanggal DESC LIMIT 5");

/* ── RECENT PESAN KONTAK (5) ── */
$q_kontak = mysqli_query($conn,"SELECT * FROM kontak ORDER BY created_at DESC LIMIT 5");

/* ── GRAFIK: PENJUALAN HARIAN (7 hari terakhir) ── */
$chart_harian_label = [];
$chart_harian_data  = [];
$q_harian = mysqli_query($conn,"
  SELECT DATE(tanggal) AS d, SUM(total_harga) AS total
  FROM pemesanan
  WHERE status_pembayaran='Lunas' AND tanggal >= (CURDATE() - INTERVAL 6 DAY)
  GROUP BY DATE(tanggal)
");
$harian_map = [];
while ($row = mysqli_fetch_assoc($q_harian)) { $harian_map[$row['d']] = (float)$row['total']; }
$hari_singkat = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
for ($i = 6; $i >= 0; $i--) {
  $d = date('Y-m-d', strtotime("-$i day"));
  $chart_harian_label[] = $hari_singkat[date('w', strtotime($d))] . ' ' . date('d/m', strtotime($d));
  $chart_harian_data[]  = $harian_map[$d] ?? 0;
}

/* ── GRAFIK: PENJUALAN MINGGUAN (8 minggu terakhir) ── */
$chart_mingguan_label = [];
$chart_mingguan_data  = [];
$q_mingguan = mysqli_query($conn,"
  SELECT YEARWEEK(tanggal,3) AS yw, MIN(DATE(tanggal)) AS awal, SUM(total_harga) AS total
  FROM pemesanan
  WHERE status_pembayaran='Lunas' AND tanggal >= (CURDATE() - INTERVAL 7 WEEK)
  GROUP BY YEARWEEK(tanggal,3)
");
$mingguan_map = [];
while ($row = mysqli_fetch_assoc($q_mingguan)) { $mingguan_map[$row['yw']] = ['awal'=>$row['awal'],'total'=>(float)$row['total']]; }
for ($i = 7; $i >= 0; $i--) {
  $ts = strtotime("-$i week");
  $yw = date('oW', $ts); // ISO year+week, cocok dgn mode 3 di YEARWEEK
  if (isset($mingguan_map[$yw])) {
    $chart_mingguan_label[] = 'Mgg ' . date('d/m', strtotime($mingguan_map[$yw]['awal']));
    $chart_mingguan_data[]  = $mingguan_map[$yw]['total'];
  } else {
    $chart_mingguan_label[] = 'Mgg ' . date('d/m', strtotime('monday this week', $ts));
    $chart_mingguan_data[]  = 0;
  }
}

/* ── GRAFIK: PRODUK TERLARIS (top 5, all-time dari pesanan Lunas) ── */
$chart_produk_label = [];
$chart_produk_data  = [];
$q_produk_terlaris = mysqli_query($conn,"
  SELECT p.nama_produk, SUM(dp.jumlah) AS qty
  FROM detail_pemesanan dp
  JOIN produk p ON p.id_produk = dp.id_produk
  JOIN pemesanan pm ON pm.id_pemesanan = dp.id_pemesanan
  WHERE pm.status_pembayaran='Lunas'
  GROUP BY dp.id_produk
  ORDER BY qty DESC
  LIMIT 5
");
while ($row = mysqli_fetch_assoc($q_produk_terlaris)) {
  $chart_produk_label[] = $row['nama_produk'];
  $chart_produk_data[]  = (int)$row['qty'];
}

/* ── HERO BACKGROUND VIDEO & MUSIK "MODE SERIUS" ──
   Taruh file video di assets/video/ (mp4/webm/mov) dan musik di
   assets/audio/ (mp3/ogg/wav) — otomatis terpakai.
   Sekarang mendukung 2 video terpisah untuk 2 mode panel:
     - hero-full.mp4  -> dipakai saat mode "Wallpaper Penuh"
     - hero-half.mp4  -> dipakai saat mode "Panel Kiri"
   Kalau file dengan nama itu tidak ada, sistem otomatis fallback
   ke file pertama/kedua yang ditemukan di folder (urutan abjad),
   supaya tetap jalan meski baru ada 1 video atau namanya beda. */
$hero_video_full = null;
$hero_video_half = null;
$video_files = glob(__DIR__ . '/assets/video/*.{mp4,webm,mov,MP4,WEBM,MOV}', GLOB_BRACE);
if (!empty($video_files)) {
  sort($video_files);
  foreach ($video_files as $vf) {
    $vname = strtolower(basename($vf));
    if ($hero_video_full === null && (strpos($vname, 'full') !== false || strpos($vname, 'penuh') !== false)) {
      $hero_video_full = 'assets/video/' . basename($vf);
    }
    if ($hero_video_half === null && (strpos($vname, 'half') !== false || strpos($vname, 'panel') !== false || strpos($vname, 'kiri') !== false)) {
      $hero_video_half = 'assets/video/' . basename($vf);
    }
  }
  // Fallback: kalau penamaan eksplisit tidak ditemukan, pakai urutan file
  if ($hero_video_full === null) $hero_video_full = 'assets/video/' . basename($video_files[0]);
  if ($hero_video_half === null) $hero_video_half = 'assets/video/' . basename($video_files[count($video_files) > 1 ? 1 : 0]);
}
// Dipakai di tempat lain (mis. render tombol toggle) sebagai penanda "ada video sama sekali"
$hero_video = $hero_video_full;

$hero_audio = null;
$audio_name = 'Musik Latar';

// ─── Spotify premium player — eksklusif untuk email tertentu ───
// Widget Spotify hanya dirender kalau email akun yang sedang login cocok.
// Admin lain (email berbeda) tetap dapat music-player lokal biasa di bawah.
$spotify_owner_email   = 'yoonskyy63@gmail.com';
$is_spotify_owner      = (($_SESSION['email'] ?? '') === $spotify_owner_email);
$spotify_playlist_id   = '7F9a1MU6RVDJSp8ygPcPew';
$spotify_playlist_meta = null;

if ($is_spotify_owner) {
    $oembed_url = 'https://open.spotify.com/oembed?url=' . urlencode('https://open.spotify.com/playlist/' . $spotify_playlist_id);
    $ctx = stream_context_create(['http' => ['timeout' => 2]]);
    $oembed_raw = @file_get_contents($oembed_url, false, $ctx);
    if ($oembed_raw !== false) {
        $decoded = json_decode($oembed_raw, true);
        if (is_array($decoded)) {
            $spotify_playlist_meta = $decoded; // berisi title, thumbnail_url, dll
        }
    }
}
$audio_files = glob(__DIR__ . '/assets/audio/*.{mp3,ogg,wav,MP3,OGG,WAV}', GLOB_BRACE);
if (!empty($audio_files)) {
  sort($audio_files);
  $hero_audio = 'assets/audio/' . basename($audio_files[0]);
  $audio_name = pathinfo($audio_files[0], PATHINFO_FILENAME);
}

$masuk_mode_serius = isset($_GET['serius']) && $_GET['serius'] === '1';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="assets/css/lucide-icons.css">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin – YOLAZCAKE</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<style>
/* ════════════════════════════════════════════
   RESET & ROOT
════════════════════════════════════════════ */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
:root{
  --gold:#D4AF37;
  --gold-l:#FFE88A;
  --gold-d:#A07C10;
  --rose:#EE2A7B;
  --purple:#8A2BE2;
  --bg1:#0d0520;
  --bg2:#1a0a3a;
  --bg3:#150830;
  --glass:rgba(255,255,255,0.045);
  --glass-h:rgba(255,255,255,0.08);
  --gb:rgba(255,255,255,0.10);
  --text:#ffffff;
  --muted:rgba(255,255,255,0.50);
  --sidebar-w:260px;
}

html{scroll-behavior:smooth;}

body{
  min-height:100vh;
  font-family:'Inter',sans-serif;
  background:linear-gradient(140deg,var(--bg1) 0%,var(--bg2) 50%,var(--bg3) 100%);
  color:var(--text);
  overflow-x:hidden;
}

/* ── AMBIENT GLOW ── */
body::before{
  content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
  background:
    radial-gradient(ellipse 60% 50% at 15% 25%,rgba(212,175,55,.1) 0%,transparent 70%),
    radial-gradient(ellipse 50% 60% at 85% 70%,rgba(138,43,226,.08) 0%,transparent 70%),
    radial-gradient(ellipse 40% 40% at 60% 10%,rgba(238,42,123,.06) 0%,transparent 60%);
  animation:ambientShift 14s ease-in-out infinite alternate;
}
@keyframes ambientShift{0%{opacity:.6;transform:scale(1);}100%{opacity:1;transform:scale(1.05) translate(8px,-8px);}}

/* ════════════════════════════════════════════
   LAYOUT WRAPPER
════════════════════════════════════════════ */
.admin-layout{
  display:flex;
  min-height:100vh;
  position:relative;
  z-index:1;
}

/* ════════════════════════════════════════════
   SIDEBAR
════════════════════════════════════════════ */
.sidebar{
  width:var(--sidebar-w);
  flex-shrink:0;
  position:fixed;
  top:0;left:0;
  height:100vh;
  display:flex;
  flex-direction:column;
  background:rgba(13,5,32,0.92);
  backdrop-filter:blur(28px) saturate(1.4);
  border-right:1px solid rgba(212,175,55,0.15);
  z-index:100;
  overflow:hidden;
  transition:transform .35s cubic-bezier(.22,.68,0,1.2);
}

/* top glow line */
.sidebar::before{
  content:'';position:absolute;top:0;left:0;right:0;height:3px;
  background:linear-gradient(90deg,#D4AF37,#EE2A7B,#8A2BE2,#D4AF37);
  background-size:300% 100%;
  animation:goldSlide 5s linear infinite;
}

/* ambient orb inside sidebar */
.sidebar::after{
  content:'';
  position:absolute;bottom:-80px;left:-80px;
  width:260px;height:260px;
  background:radial-gradient(circle,rgba(212,175,55,.07) 0%,transparent 65%);
  border-radius:50%;pointer-events:none;
}

/* brand */
.sb-brand{
  padding:28px 24px 22px;
  border-bottom:1px solid rgba(255,255,255,.07);
  position:relative;
}

.sb-label{
  font-size:.62em;font-weight:700;letter-spacing:4px;text-transform:uppercase;
  color:rgba(212,175,55,.6);margin-bottom:12px;
}

.sb-logo{
  display:flex;align-items:center;gap:12px;text-decoration:none;
}
.sb-logo img{
  width:42px;height:42px;border-radius:50%;object-fit:cover;
  border:2px solid rgba(212,175,55,.45);
  box-shadow:0 0 18px rgba(212,175,55,.35);
}
.sb-logo-text{
  font-family:'Playfair Display',serif;
  font-size:1.2em;font-weight:700;
  background:linear-gradient(135deg,#D4AF37 30%,#FFE88A 70%);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}

.sb-badge{
  margin-top:10px;
  display:inline-flex;align-items:center;gap:6px;
  padding:4px 14px;
  background:linear-gradient(135deg,rgba(212,175,55,.18),rgba(212,175,55,.06));
  border:1px solid rgba(212,175,55,.35);
  border-radius:999px;
  font-size:.68em;font-weight:700;letter-spacing:2px;text-transform:uppercase;
  color:var(--gold);
  position:relative;overflow:hidden;
}
.sb-badge::before{
  content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;
  background:linear-gradient(90deg,transparent,rgba(212,175,55,.25),transparent);
  animation:shimmer 2.5s linear infinite;
}
@keyframes shimmer{to{left:200%;}}

/* nav scroll area */
.sb-nav{
  flex:1;overflow-y:auto;padding:20px 0;
  scrollbar-width:none;
}
.sb-nav::-webkit-scrollbar{display:none;}

.sb-section-label{
  font-size:.60em;font-weight:700;letter-spacing:3px;text-transform:uppercase;
  color:rgba(255,255,255,.28);
  padding:14px 24px 6px;
  display:flex;align-items:center;gap:8px;
}
.sb-section-label::after{
  content:'';flex:1;height:1px;
  background:linear-gradient(to right,rgba(255,255,255,.1),transparent);
}

.sb-link{
  display:flex;align-items:center;gap:12px;
  padding:11px 24px;
  color:rgba(255,255,255,.65);
  font-size:.875em;font-weight:500;
  text-decoration:none;
  border-left:3px solid transparent;
  transition:background .25s,color .25s,border-color .25s,padding-left .2s;
  position:relative;
}
.sb-link:hover{
  background:rgba(212,175,55,.08);
  color:#fff;
  border-left-color:rgba(212,175,55,.4);
  padding-left:28px;
}
.sb-link.active{
  background:rgba(212,175,55,.12);
  color:var(--gold);
  border-left-color:var(--gold);
  font-weight:600;
}
.sb-link.active .sb-link-icon{
  filter:drop-shadow(0 0 6px rgba(212,175,55,.6));
}

.sb-link-icon{font-size:1.1em;width:20px;text-align:center;flex-shrink:0;}

.sb-link-badge{
  margin-left:auto;
  background:linear-gradient(135deg,#EE2A7B,#D4AF37);
  color:#fff;font-size:.65em;font-weight:700;
  padding:2px 8px;border-radius:999px;min-width:20px;text-align:center;
  box-shadow:0 2px 10px rgba(238,42,123,.4);
}
.sb-link-badge.sb-link-badge-warn{
  background:linear-gradient(135deg,#ff6060,#ffb432);
  box-shadow:0 2px 10px rgba(255,140,50,.4);
}

.sb-link-new{
  margin-left:auto;
  background:rgba(46,213,115,.15);
  border:1px solid rgba(46,213,115,.35);
  color:#2ed573;font-size:.62em;font-weight:700;
  padding:2px 8px;border-radius:999px;
}

/* add-type nav links */
.sb-link.add-link{
  color:rgba(212,175,55,.75);
}
.sb-link.add-link:hover{
  color:var(--gold);
  background:rgba(212,175,55,.1);
}

/* footer of sidebar */
.sb-footer{
  padding:16px 16px 24px;
  border-top:1px solid rgba(255,255,255,.07);
}

.sb-logout{
  display:flex;align-items:center;justify-content:center;gap:10px;
  padding:12px;
  background:rgba(238,42,123,.1);
  border:1px solid rgba(238,42,123,.28);
  border-radius:14px;
  color:#EE2A7B;
  font-size:.84em;font-weight:600;
  text-decoration:none;
  transition:background .25s,box-shadow .3s,transform .2s;
}
.sb-logout:hover{
  background:rgba(238,42,123,.2);
  box-shadow:0 6px 20px rgba(238,42,123,.25);
  transform:translateY(-2px);
}

/* ════════════════════════════════════════════
   MAIN CONTENT
════════════════════════════════════════════ */
.main-content{
  flex:1;
  margin-left:var(--sidebar-w);
  min-height:100vh;
  display:flex;
  flex-direction:column;
}

/* ── TOPBAR ── */
.topbar{
  position:sticky;top:0;z-index:50;
  display:flex;align-items:center;justify-content:space-between;
  padding:16px 32px;
  background:rgba(13,5,32,.88);
  backdrop-filter:blur(24px) saturate(1.3);
  border-bottom:1px solid rgba(212,175,55,.12);
  box-shadow:0 4px 30px rgba(0,0,0,.35);
}

.topbar-left{
  display:flex;align-items:center;gap:16px;
}

.hamburger-btn{
  display:none;
  background:rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.12);
  border-radius:10px;padding:8px 10px;
  cursor:pointer;font-size:1.1em;color:rgba(255,255,255,.8);
  transition:background .2s;
}
.hamburger-btn:hover{background:rgba(212,175,55,.15);color:var(--gold);}

.topbar-title{
  font-family:'Playfair Display',serif;
  font-size:1.15em;font-weight:700;
  background:linear-gradient(135deg,#fff 40%,#D4AF37 80%);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}

.topbar-right{
  display:flex;align-items:center;gap:12px;
}

.topbar-time{
  font-size:.78em;color:var(--muted);letter-spacing:.5px;
}

.topbar-user{
  display:flex;align-items:center;gap:10px;
  padding:8px 16px;
  background:rgba(212,175,55,.08);
  border:1px solid rgba(212,175,55,.22);
  border-radius:999px;
}
.topbar-avatar{
  width:30px;height:30px;border-radius:50%;
  background:linear-gradient(135deg,#D4AF37,#EE2A7B);
  display:flex;align-items:center;justify-content:center;
  font-size:.8em;font-weight:700;flex-shrink:0;
}
.topbar-username{
  font-size:.82em;font-weight:600;color:var(--gold);
}

.btn-website{
  display:inline-flex;align-items:center;gap:8px;
  padding:9px 18px;
  background:rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.14);
  border-radius:10px;
  color:rgba(255,255,255,.8);
  font-size:.8em;font-weight:600;
  text-decoration:none;
  transition:background .25s,color .25s,border-color .25s,transform .2s;
}
.btn-website:hover{
  background:rgba(212,175,55,.14);
  color:var(--gold);border-color:rgba(212,175,55,.35);
  transform:translateY(-1px);
}

/* ── PAGE BODY ── */
.page-body{
  flex:1;
  padding:36px 32px 60px;
}

/* ── HERO BANNER ── */
.dash-hero{
  position:relative;
  border-radius:28px;
  padding:44px 48px;
  margin-bottom:32px;
  overflow:hidden;
  background:linear-gradient(135deg,#1a0533 0%,#2e1060 40%,#1e0a44 70%,#0d0520 100%);
  border:1px solid rgba(212,175,55,.15);
  animation:fadeUp .8s forwards .1s;opacity:0;
}
/* Mode Wallpaper Penuh butuh tinggi lebih lega supaya video latar tidak
   ter-crop terlalu agresif oleh object-fit:cover (rasio lebar-vs-tinggi
   panel jadi tidak terlalu ekstrem dibanding rasio asli video). */
.dash-hero.media-full{min-height:340px;padding-top:56px;padding-bottom:56px;}
.dash-hero::before{
  content:'';position:absolute;inset:0;
  background:
    radial-gradient(ellipse 70% 60% at 20% 60%,rgba(212,175,55,.18) 0%,transparent 55%),
    radial-gradient(ellipse 50% 65% at 80% 30%,rgba(238,42,123,.16) 0%,transparent 55%),
    radial-gradient(ellipse 40% 50% at 55% 90%,rgba(138,43,226,.1) 0%,transparent 55%);
  animation:heroMesh 10s ease-in-out infinite alternate;
}
@keyframes heroMesh{0%{opacity:.7;transform:scale(1);}100%{opacity:1;transform:scale(1.08) rotate(1deg);}}

.dash-hero-grid{
  position:absolute;inset:0;pointer-events:none;
  background-image:
    linear-gradient(rgba(212,175,55,.03) 1px,transparent 1px),
    linear-gradient(90deg,rgba(212,175,55,.03) 1px,transparent 1px);
  background-size:50px 50px;
  animation:gridDrift 18s linear infinite;
}
@keyframes gridDrift{from{background-position:0 0;}to{background-position:50px 50px;}}

.dash-hero-inner{position:relative;z-index:2;}

.dash-eyebrow{
  display:inline-flex;align-items:center;gap:8px;
  font-size:.68em;font-weight:700;letter-spacing:4px;text-transform:uppercase;
  color:var(--gold);margin-bottom:14px;
}
.dash-eyebrow::before{content:'✨';}

.dash-hero h1{
  font-family:'Playfair Display',serif;
  font-size:2.4em;font-weight:700;line-height:1.15;
  margin-bottom:10px;
}
.dash-hero h1 span{
  background:linear-gradient(135deg,#fff 0%,#D4AF37 35%,#FFE88A 55%,#EE2A7B 80%,#fff 100%);
  background-size:300% 100%;
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  animation:heroShimmer 5s ease-in-out infinite;
}
@keyframes heroShimmer{0%,100%{background-position:0% 0;}50%{background-position:100% 0;}}

.dash-hero-sub{
  font-size:.9em;color:rgba(255,255,255,.55);
  display:flex;align-items:center;gap:10px;
}
.dash-hero-sub span{
  display:inline-flex;align-items:center;gap:5px;
  background:rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.1);
  border-radius:999px;padding:4px 14px;
  font-size:.9em;
}

/* decorative number / logo watermark */
.dash-hero-deco{
  position:absolute;right:48px;top:50%;transform:translateY(-50%);
  font-family:'Playfair Display',serif;
  font-size:9em;font-weight:700;line-height:1;
  background:linear-gradient(135deg,rgba(212,175,55,.12),rgba(212,175,55,.03));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  pointer-events:none;user-select:none;
  z-index:1;
}

/* versi gambar (logo/desain sendiri, background transparan) */
.dash-hero-deco-img{
  position:absolute;right:48px;top:50%;transform:translateY(-50%);
  max-height:450px;width:auto;
  opacity:.75;
  filter:drop-shadow(0 0 30px rgba(212,175,55,.4));
  pointer-events:none;user-select:none;
  z-index:1;
}

/* ── STATS GRID ── */
.stats-grid{
  display:grid;
  grid-template-columns:repeat(4,1fr);
  gap:18px;
  margin-bottom:32px;
}

.stat-card{
  position:relative;
  background:var(--glass);
  backdrop-filter:blur(20px);
  border:1px solid var(--gb);
  border-radius:22px;padding:26px 22px;
  display:flex;flex-direction:column;gap:8px;
  overflow:hidden;
  opacity:0;
  transition:border-color .35s,box-shadow .35s,transform .3s;
  cursor:default;
}
.stat-card.s1{animation:fadeUp .7s forwards .25s;--sc:#D4AF37;--sg:rgba(212,175,55,.2);}
.stat-card.s2{animation:fadeUp .7s forwards .35s;--sc:#EE2A7B;--sg:rgba(238,42,123,.2);}
.stat-card.s3{animation:fadeUp .7s forwards .45s;--sc:#8A2BE2;--sg:rgba(138,43,226,.2);}
.stat-card.s4{animation:fadeUp .7s forwards .55s;--sc:#2ed573;--sg:rgba(46,213,115,.2);}

.stat-card::before{
  content:'';position:absolute;bottom:0;left:0;right:0;height:3px;
  background:var(--sc,#D4AF37);
  transform:scaleX(0);transform-origin:left;
  transition:transform .4s ease;
}
.stat-card:hover::before{transform:scaleX(1);}
.stat-card:hover{
  background:var(--glass-h);
  border-color:rgba(212,175,55,.25);
  box-shadow:0 12px 40px rgba(0,0,0,.3),0 0 30px var(--sg,rgba(212,175,55,.15));
  transform:translateY(-4px);
}

.stat-icon-wrap{
  width:44px;height:44px;border-radius:13px;flex-shrink:0;
  background:rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.1);
  display:flex;align-items:center;justify-content:center;font-size:1.3em;
  margin-bottom:4px;
}

.stat-val{
  font-family:'Playfair Display',serif;
  font-size:2.2em;font-weight:700;
  color:var(--sc,#D4AF37);line-height:1;
}
.stat-lbl{font-size:.72em;color:var(--muted);letter-spacing:1px;text-transform:uppercase;}

.stat-badge{
  margin-top:4px;
  display:inline-flex;align-items:center;gap:4px;
  font-size:.68em;font-weight:700;color:#EE2A7B;
  background:rgba(238,42,123,.12);
  border:1px solid rgba(238,42,123,.3);
  border-radius:999px;padding:2px 10px;
  width:fit-content;
}
.stat-badge.ok{color:#2ed573;background:rgba(46,213,115,.1);border-color:rgba(46,213,115,.3);}

/* ── "API" GLOW — mode serius flair (subtle, bukan norak) ── */
.flame-text{animation:flameText 4.5s ease-in-out infinite;}
@keyframes flameText{
  0%,100%{text-shadow:0 0 10px rgba(138,43,226,.5),0 0 18px rgba(138,43,226,.2);}
  33%    {text-shadow:0 0 10px rgba(75,110,255,.5),0 0 18px rgba(75,110,255,.2);}
  66%    {text-shadow:0 0 10px rgba(238,42,123,.5),0 0 18px rgba(238,42,123,.2);}
}
.flame-card{animation:flameCard 6s ease-in-out infinite;}
@keyframes flameCard{
  0%,100%{box-shadow:0 12px 40px rgba(0,0,0,.3),0 0 22px rgba(138,43,226,.16);}
  33%    {box-shadow:0 12px 40px rgba(0,0,0,.3),0 0 22px rgba(75,110,255,.16);}
  66%    {box-shadow:0 12px 40px rgba(0,0,0,.3),0 0 22px rgba(238,42,123,.16);}
}

/* ── HERO MEDIA (video latar) — kini 2 video terpisah per mode ── */
.dash-hero{transition:padding .4s ease;}
.dash-hero.media-half{display:flex;align-items:center;gap:30px;}
.hero-media{border-radius:20px;overflow:hidden;display:none;}
.hero-media-placeholder{
  position:absolute;inset:0;display:flex;align-items:center;justify-content:center;text-align:center;
  font-size:.78em;color:rgba(255,255,255,.4);padding:14px;
  border:1px dashed rgba(255,255,255,.15);border-radius:inherit;background:rgba(255,255,255,.02);
}

/* Video khusus mode "Wallpaper Penuh" */
.dash-hero.media-full .hero-media-full{
  display:block;position:absolute;inset:0;z-index:0;border-radius:inherit;
}
.dash-hero.media-full .hero-media-full video{
  width:100%;height:100%;object-fit:cover;object-position:center 50%;opacity:.75;
}
.dash-hero.media-full .hero-media-full .hero-media-overlay{
  position:absolute;inset:0;
  background:linear-gradient(120deg,rgba(13,5,32,.78) 0%,rgba(26,10,58,.55) 55%,rgba(13,5,32,.82) 100%);
}

/* Video khusus mode "Panel Kiri" */
.dash-hero.media-half .hero-media-half{
  display:block;position:relative;flex:0 0 300px;max-width:38%;min-height:230px;align-self:stretch;
}
.dash-hero.media-half .hero-media-half video{width:100%;height:100%;object-fit:cover;display:block;}
.dash-hero.media-half .hero-media-half .hero-media-overlay{
  position:absolute;inset:0;background:linear-gradient(0deg,rgba(13,5,32,.35),transparent 40%);
}

.dash-hero.media-half .dash-hero-inner{flex:1;}

.hero-media-toggle{
  position:relative;z-index:3;display:flex;gap:8px;margin-top:18px;
}
.dash-hero.media-half .hero-media-toggle,
.dash-hero.media-full .hero-media-toggle{position:absolute;right:24px;bottom:20px;margin-top:0;}
.hmt-btn{
  font-family:'Inter',sans-serif;font-size:.72em;font-weight:600;
  padding:7px 13px;border-radius:9px;cursor:pointer;
  background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.14);
  color:rgba(255,255,255,.65);transition:.2s;
}
.hmt-btn:hover{background:rgba(255,255,255,.12);color:#fff;}
.hmt-btn.active{background:rgba(212,175,55,.18);border-color:rgba(212,175,55,.4);color:var(--gold);}

/* ── MUSIC PLAYER (gaya Spotify) ── */
.music-player{
  display:flex;align-items:center;gap:16px;
  background:var(--glass);backdrop-filter:blur(20px);
  border:1px solid var(--gb);border-radius:18px;
  padding:14px 20px;margin-bottom:32px;
  opacity:0;animation:fadeUp .7s forwards .65s;
}
.mp-art{
  width:46px;height:46px;border-radius:12px;flex-shrink:0;
  background:linear-gradient(135deg,var(--purple),var(--rose));
  display:flex;align-items:center;justify-content:center;font-size:1.2em;
  animation:mpSpin 8s linear infinite;
}
@keyframes mpSpin{from{transform:rotate(0)}to{transform:rotate(360deg)}}
.music-player.playing .mp-art{animation-play-state:running;}
.music-player:not(.playing) .mp-art{animation-play-state:paused;}
.mp-info{flex:1;min-width:0;}
.mp-title{font-size:.85em;font-weight:600;margin-bottom:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.mp-progress-wrap{position:relative;height:4px;border-radius:99px;background:rgba(255,255,255,.1);margin-bottom:5px;}
.mp-progress{position:absolute;left:0;top:0;height:100%;width:0;background:linear-gradient(90deg,var(--gold),var(--rose));border-radius:99px;pointer-events:none;}
.mp-seek{
  position:absolute;left:0;top:50%;transform:translateY(-50%);
  width:100%;height:16px;margin:0;
  -webkit-appearance:none;appearance:none;
  background:transparent;cursor:pointer;
}
.mp-seek::-webkit-slider-runnable-track{background:transparent;height:16px;}
.mp-seek::-webkit-slider-thumb{
  -webkit-appearance:none;appearance:none;
  width:12px;height:12px;border-radius:50%;margin-top:2px;
  background:#fff;box-shadow:0 0 0 2px rgba(0,0,0,.3),0 0 8px rgba(212,175,55,.65);
  cursor:pointer;transition:transform .15s;
}
.mp-seek:hover::-webkit-slider-thumb{transform:scale(1.15);}
.mp-seek::-moz-range-track{background:transparent;height:16px;border:none;}
.mp-seek::-moz-range-thumb{
  width:12px;height:12px;border-radius:50%;border:none;
  background:#fff;box-shadow:0 0 0 2px rgba(0,0,0,.3),0 0 8px rgba(212,175,55,.65);
  cursor:pointer;
}
.mp-time{font-size:.65em;color:var(--muted);}
.mp-playbtn{
  flex-shrink:0;width:42px;height:42px;border-radius:50%;border:none;cursor:pointer;
  background:linear-gradient(135deg,var(--gold),var(--rose));color:#1a0533;font-size:1em;font-weight:700;
  display:flex;align-items:center;justify-content:center;transition:transform .2s;
}
.mp-playbtn:hover{transform:scale(1.08);}
.mp-placeholder{font-size:.78em;color:var(--muted);}

.mp-autoplay{
  flex-shrink:0;display:flex;align-items:center;gap:8px;
  cursor:pointer;user-select:none;
}
.mp-autoplay input{position:absolute;opacity:0;width:0;height:0;}
.mp-autoplay-track{
  position:relative;width:34px;height:19px;border-radius:99px;flex-shrink:0;
  background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.18);
  transition:background .2s,border-color .2s;
}
.mp-autoplay-thumb{
  position:absolute;top:1px;left:1px;width:15px;height:15px;border-radius:50%;
  background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.4);
  transition:transform .2s;
}
.mp-autoplay input:checked + .mp-autoplay-track{
  background:linear-gradient(135deg,var(--gold),var(--rose));border-color:transparent;
}
.mp-autoplay input:checked + .mp-autoplay-track .mp-autoplay-thumb{transform:translateX(15px);}
.mp-autoplay-label{font-size:.68em;color:var(--muted);letter-spacing:.3px;white-space:nowrap;}

/* ── SPOTIFY PREMIUM PLAYER (eksklusif) ── */
.spotify-premium{
  position:relative;overflow:hidden;
  background:var(--glass);backdrop-filter:blur(20px);
  border:1px solid rgba(212,175,55,.35);border-radius:18px;
  padding:16px 18px;margin-bottom:32px;
  opacity:0;animation:fadeUp .7s forwards .65s;
  box-shadow:0 0 0 1px rgba(212,175,55,.06) inset,0 8px 30px rgba(0,0,0,.25);
}
.spotify-premium::before{
  content:'';position:absolute;inset:-40%;z-index:0;pointer-events:none;
  background:radial-gradient(circle at 15% 20%,rgba(212,175,55,.16),transparent 55%),
             radial-gradient(circle at 85% 80%,rgba(157,78,221,.18),transparent 55%);
  animation:spGlow 9s ease-in-out infinite alternate;
}
@keyframes spGlow{from{transform:rotate(0deg) scale(1);}to{transform:rotate(8deg) scale(1.08);}}
.sp-head{position:relative;z-index:1;display:flex;align-items:center;gap:14px;margin-bottom:14px;}
.sp-art{
  width:56px;height:56px;border-radius:12px;flex-shrink:0;overflow:hidden;
  background:linear-gradient(135deg,var(--purple),var(--rose));
  display:flex;align-items:center;justify-content:center;color:#fff;
  box-shadow:0 4px 14px rgba(0,0,0,.35),0 0 0 1px rgba(212,175,55,.3);
}
.sp-art img{width:100%;height:100%;object-fit:cover;display:block;}
.sp-meta{flex:1;min-width:0;}
.sp-badge{
  display:inline-flex;align-items:center;gap:5px;font-size:.62em;font-weight:700;
  letter-spacing:.4px;text-transform:uppercase;color:var(--gold);
  background:rgba(212,175,55,.12);border:1px solid rgba(212,175,55,.3);
  border-radius:99px;padding:3px 9px;margin-bottom:6px;
}
.sp-badge .lucide-ic{width:11px;height:11px;}
.sp-title{font-size:.92em;font-weight:700;margin-bottom:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.sp-sub{display:flex;align-items:center;gap:5px;font-size:.68em;color:var(--muted);}
.sp-sub .lucide-ic{width:12px;height:12px;}
.sp-embed-wrap{
  position:relative;z-index:1;border-radius:14px;overflow:hidden;
  border:1px solid rgba(255,255,255,.08);
}
.sp-embed-wrap iframe{display:block;border-radius:14px;}

/* ── MODAL "MODE SERIUS" ── */
.serius-modal-overlay{
  position:fixed;inset:0;z-index:9999;
  background:rgba(5,2,15,.75);backdrop-filter:blur(6px);
  display:flex;align-items:center;justify-content:center;
  opacity:0;pointer-events:none;transition:opacity .35s;
}
.serius-modal-overlay.show{opacity:1;pointer-events:auto;}
.serius-modal{
  width:90%;max-width:420px;text-align:center;padding:42px 34px;
  background:rgba(255,255,255,.06);backdrop-filter:blur(24px);
  border:1px solid rgba(255,255,255,.12);border-radius:24px;
  box-shadow:0 30px 70px rgba(0,0,0,.45);
  transform:translateY(24px) scale(.94);transition:transform .4s cubic-bezier(.22,.68,0,1.2);
}
.serius-modal-overlay.show .serius-modal{transform:translateY(0) scale(1);}
.serius-modal-icon{font-size:2.6em;margin-bottom:14px;}
.serius-modal h2{
  font-family:'Playfair Display',serif;font-size:1.35em;margin-bottom:12px;
  background:linear-gradient(135deg,#fff,var(--gold) 35%,var(--rose) 70%,var(--purple));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.serius-modal p{color:rgba(255,255,255,.62);font-size:.92em;line-height:1.6;margin-bottom:24px;}
.serius-modal button{
  padding:12px 28px;border:none;border-radius:12px;cursor:pointer;
  font-weight:700;font-size:.9em;color:#fff;
  background:linear-gradient(135deg,var(--purple),var(--rose) 60%,var(--gold));
  box-shadow:0 10px 26px rgba(138,43,226,.35);transition:transform .2s;
}
.serius-modal button:hover{transform:translateY(-2px);}

/* ── SECTION TITLE ── */
.section-hd{
  display:flex;align-items:center;gap:12px;
  margin-bottom:20px;
}
.section-hd-line{
  flex:1;height:1px;
  background:linear-gradient(to right,rgba(212,175,55,.25),transparent);
}
.section-hd h2{
  font-family:'Playfair Display',serif;
  font-size:1.15em;font-weight:700;color:var(--text);white-space:nowrap;
}
.section-hd .sh-icon{
  width:36px;height:36px;border-radius:10px;
  background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.25);
  display:flex;align-items:center;justify-content:center;font-size:1em;
}

/* ── MANAGEMENT CARDS GRID ── */
.mgmt-grid{
  display:grid;
  grid-template-columns:repeat(2,1fr);
  gap:20px;
  margin-bottom:32px;
}

.mgmt-card{
  position:relative;
  background:var(--glass);
  backdrop-filter:blur(22px);
  border:1px solid var(--gb);
  border-radius:24px;
  padding:32px 30px;
  overflow:hidden;
  opacity:0;
  transition:border-color .35s,box-shadow .35s,transform .3s,background .3s;
}
.mgmt-card.m1{animation:fadeUp .7s forwards .35s;}
.mgmt-card.m2{animation:fadeUp .7s forwards .45s;}
.mgmt-card.m3{animation:fadeUp .7s forwards .55s;}
.mgmt-card.m4{animation:fadeUp .7s forwards .65s;}

/* animated top border */
.mgmt-card::before{
  content:'';position:absolute;top:0;left:0;right:0;height:3px;
  background:linear-gradient(90deg,#D4AF37,#EE2A7B,#8A2BE2,#D4AF37);
  background-size:300% 100%;
  animation:goldSlide 5s linear infinite;
}

/* subtle corner glow */
.mgmt-card::after{
  content:'';
  position:absolute;top:-60px;right:-60px;
  width:180px;height:180px;
  background:radial-gradient(circle,var(--mc-glow,rgba(212,175,55,.08)) 0%,transparent 65%);
  border-radius:50%;pointer-events:none;
  transition:transform .4s;
}
.mgmt-card:hover::after{transform:scale(1.3);}

.mgmt-card:hover{
  background:var(--glass-h);
  border-color:rgba(212,175,55,.25);
  box-shadow:0 16px 48px rgba(0,0,0,.35),0 0 40px rgba(212,175,55,.1);
  transform:translateY(-5px);
}

.mgmt-card.booking{--mc-glow:rgba(212,175,55,.1);}
.mgmt-card.pemesanan{--mc-glow:rgba(238,42,123,.08);}
.mgmt-card.produk{--mc-glow:rgba(138,43,226,.08);}
.mgmt-card.member{--mc-glow:rgba(46,213,115,.08);}

.mc-top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:18px;}

.mc-icon{
  width:52px;height:52px;border-radius:16px;
  display:flex;align-items:center;justify-content:center;font-size:1.5em;
  background:rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.1);
  box-shadow:0 4px 20px rgba(0,0,0,.2);
}

.mc-count{
  font-family:'Playfair Display',serif;
  font-size:2.6em;font-weight:700;
  background:linear-gradient(135deg,#D4AF37,#FFE88A);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  line-height:1;
}

.mc-title{
  font-family:'Playfair Display',serif;
  font-size:1.25em;font-weight:700;margin-bottom:8px;
}

.mc-desc{font-size:.82em;color:var(--muted);line-height:1.6;margin-bottom:22px;}

.mc-actions{display:flex;flex-wrap:wrap;gap:10px;}

.btn-primary{
  display:inline-flex;align-items:center;gap:7px;
  padding:10px 20px;
  background:rgba(212,175,55,.12);
  border:1px solid rgba(212,175,55,.35);
  border-radius:10px;
  color:var(--gold);
  font-size:.8em;font-weight:600;
  text-decoration:none;
  transition:background .25s,box-shadow .3s,transform .2s;
  position:relative;overflow:hidden;
}
.btn-primary::before{
  content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;
  background:linear-gradient(90deg,transparent,rgba(212,175,55,.15),transparent);
  transition:left .35s;
}
.btn-primary:hover::before{left:100%;}
.btn-primary:hover{
  background:rgba(212,175,55,.22);
  box-shadow:0 6px 22px rgba(212,175,55,.3);
  transform:translateY(-2px);
}

.btn-secondary{
  display:inline-flex;align-items:center;gap:7px;
  padding:10px 20px;
  background:rgba(255,255,255,.05);
  border:1px solid rgba(255,255,255,.12);
  border-radius:10px;
  color:rgba(255,255,255,.75);
  font-size:.8em;font-weight:600;
  text-decoration:none;
  transition:background .25s,color .25s,border-color .25s,transform .2s;
}
.btn-secondary:hover{
  background:rgba(255,255,255,.1);
  color:#fff;
  border-color:rgba(255,255,255,.25);
  transform:translateY(-2px);
}

.btn-accent{
  display:inline-flex;align-items:center;gap:7px;
  padding:10px 20px;
  background:rgba(238,42,123,.1);
  border:1px solid rgba(238,42,123,.3);
  border-radius:10px;
  color:#EE2A7B;
  font-size:.8em;font-weight:600;
  text-decoration:none;
  transition:background .25s,box-shadow .3s,transform .2s;
}
.btn-accent:hover{
  background:rgba(238,42,123,.2);
  box-shadow:0 6px 22px rgba(238,42,123,.28);
  transform:translateY(-2px);
}

.btn-add{
  display:inline-flex;align-items:center;gap:7px;
  padding:10px 20px;
  background:rgba(46,213,115,.08);
  border:1px solid rgba(46,213,115,.28);
  border-radius:10px;
  color:#2ed573;
  font-size:.8em;font-weight:600;
  text-decoration:none;
  transition:background .25s,box-shadow .3s,transform .2s;
}
.btn-add:hover{
  background:rgba(46,213,115,.18);
  box-shadow:0 6px 22px rgba(46,213,115,.25);
  transform:translateY(-2px);
}

.pending-tag{
  display:inline-flex;align-items:center;gap:5px;
  padding:3px 10px;
  background:rgba(238,42,123,.15);
  border:1px solid rgba(238,42,123,.35);
  border-radius:999px;
  font-size:.68em;font-weight:700;color:#EE2A7B;
  margin-top:4px;
}

/* ── ACTIVITY SECTION ── */
.activity-grid{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(340px,1fr));
  gap:20px;
  margin-bottom:32px;
}

.activity-card{
  position:relative;
  background:var(--glass);
  backdrop-filter:blur(22px);
  border:1px solid var(--gb);
  border-radius:24px;
  overflow:hidden;
  opacity:0;
}
.activity-card.a1{animation:fadeUp .7s forwards .6s;}
.activity-card.a2{animation:fadeUp .7s forwards .7s;}
.activity-card.a3{animation:fadeUp .7s forwards .8s;}

.activity-card::before{
  content:'';position:absolute;top:0;left:0;right:0;height:3px;
  background:linear-gradient(90deg,#D4AF37,#EE2A7B,#8A2BE2,#D4AF37);
  background-size:300% 100%;
  animation:goldSlide 5s linear infinite;
}

.ac-head{
  display:flex;align-items:center;justify-content:space-between;
  padding:22px 26px 16px;
  border-bottom:1px solid rgba(255,255,255,.06);
}
.ac-head-left{display:flex;align-items:center;gap:10px;}
.ac-head-icon{font-size:1.1em;}
.ac-head-title{font-family:'Playfair Display',serif;font-size:.98em;font-weight:700;}
.ac-head-link{
  font-size:.75em;font-weight:600;color:var(--gold);
  text-decoration:none;
  display:inline-flex;align-items:center;gap:4px;
  opacity:.8;transition:opacity .2s;
}
.ac-head-link:hover{opacity:1;}

.ac-table{width:100%;border-collapse:collapse;}
.ac-table thead th{
  padding:10px 16px;
  font-size:.63em;font-weight:700;letter-spacing:2px;text-transform:uppercase;
  color:rgba(212,175,55,.7);text-align:left;
  background:rgba(212,175,55,.05);
  border-bottom:1px solid rgba(212,175,55,.1);
}
.ac-table tbody tr{border-bottom:1px solid rgba(255,255,255,.05);}
.ac-table tbody tr:last-child{border-bottom:none;}
.ac-table tbody tr:hover{background:rgba(212,175,55,.04);}
.ac-table tbody td{padding:11px 16px;font-size:.82em;color:rgba(255,255,255,.75);}
.td-name{font-weight:600;color:#fff !important;}

.s-badge{
  display:inline-flex;align-items:center;gap:4px;
  padding:3px 10px;border-radius:999px;
  font-size:.7em;font-weight:700;white-space:nowrap;
}
.s-pending{background:rgba(212,175,55,.15);border:1px solid rgba(212,175,55,.4);color:#D4AF37;}
.s-ok{background:rgba(99,250,180,.15);border:1px solid rgba(99,250,180,.4);color:#6efabc;}
.s-batal{background:rgba(239,68,68,.14);border:1px solid rgba(239,68,68,.35);color:#fca5a5;}
.s-lunas{background:rgba(46,213,115,.12);border:1px solid rgba(46,213,115,.3);color:#2ed573;}
.s-menunggu{background:rgba(212,175,55,.12);border:1px solid rgba(212,175,55,.35);color:#D4AF37;}
.s-akun{background:rgba(238,42,123,.14);border:1px solid rgba(238,42,123,.35);color:#ff8ab5;}
.s-umum{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.18);color:rgba(255,255,255,.6);}

/* ── GRAFIK PENJUALAN ── */
.charts-grid{
  display:grid;
  grid-template-columns:1.4fr 1fr;
  gap:20px;
  margin-bottom:32px;
}
@media(max-width:1000px){ .charts-grid{grid-template-columns:1fr;} }

.chart-card{
  position:relative;
  background:var(--glass);
  backdrop-filter:blur(22px);
  border:1px solid var(--gb);
  border-radius:24px;
  overflow:hidden;
  opacity:0;
  animation:fadeUp .7s forwards .5s;
}
.chart-card::before{
  content:'';position:absolute;top:0;left:0;right:0;height:3px;
  background:linear-gradient(90deg,#D4AF37,#EE2A7B,#8A2BE2,#D4AF37);
  background-size:300% 100%;
  animation:goldSlide 5s linear infinite;
}
.chart-body{padding:18px 22px 24px;}
.chart-canvas-wrap{position:relative;height:260px;}

.chart-toggle{
  display:flex;gap:6px;
  background:rgba(255,255,255,.05);
  border:1px solid rgba(255,255,255,.1);
  border-radius:999px;padding:3px;
}
.chart-toggle button{
  border:none;background:transparent;color:rgba(255,255,255,.55);
  font-size:.72em;font-weight:700;letter-spacing:.5px;
  padding:6px 14px;border-radius:999px;cursor:pointer;
  transition:background .2s,color .2s;
}
.chart-toggle button.active{
  background:linear-gradient(135deg,rgba(212,175,55,.9),rgba(238,42,123,.85));
  color:#fff;
}
.chart-empty{
  display:flex;align-items:center;justify-content:center;
  height:260px;color:rgba(255,255,255,.35);font-size:.85em;text-align:center;
}

.ac-empty{
  text-align:center;padding:30px;color:var(--muted);font-size:.85em;
}

/* ── QUICK LINKS STRIP ── */
.quick-links{
  display:grid;
  grid-template-columns:repeat(4,1fr);
  gap:14px;
  margin-bottom:32px;
  opacity:0;
  animation:fadeUp .7s forwards .75s;
}
.ql-item{
  display:flex;flex-direction:column;align-items:center;gap:10px;
  padding:22px 16px;
  background:var(--glass);
  backdrop-filter:blur(20px);
  border:1px solid var(--gb);
  border-radius:18px;
  text-decoration:none;
  color:rgba(255,255,255,.7);
  font-size:.8em;font-weight:600;
  text-align:center;
  transition:background .25s,border-color .25s,color .25s,transform .25s,box-shadow .3s;
  position:relative;overflow:hidden;
}
.ql-item::before{
  content:'';position:absolute;top:0;left:0;right:0;height:2px;
  background:var(--ql-color,#D4AF37);
  transform:scaleX(0);transform-origin:left;
  transition:transform .35s;
}
.ql-item:hover::before{transform:scaleX(1);}
.ql-item:hover{
  background:var(--glass-h);
  border-color:rgba(212,175,55,.25);
  color:#fff;
  transform:translateY(-4px);
  box-shadow:0 12px 32px rgba(0,0,0,.3);
}
.ql-icon{font-size:1.7em;}
.ql-item:nth-child(1){--ql-color:#D4AF37;}
.ql-item:nth-child(2){--ql-color:#EE2A7B;}
.ql-item:nth-child(3){--ql-color:#8A2BE2;}
.ql-item:nth-child(4){--ql-color:#2ed573;}

/* ── FOOTER ── */
.dash-footer{
  text-align:center;padding:24px;
  font-size:.75em;color:var(--muted);
  border-top:1px solid rgba(255,255,255,.05);
  line-height:1.9;
}
.dash-footer-brand{
  font-family:'Playfair Display',serif;font-size:1.1em;
  background:linear-gradient(135deg,#D4AF37,#FFE88A);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  margin-bottom:4px;
}

/* ── PARTICLES ── */
.particle{position:fixed;border-radius:50%;pointer-events:none;animation:pFloat linear infinite;z-index:0;}
@keyframes pFloat{
  0%{transform:translateY(100vh) scale(0);opacity:0;}
  10%{opacity:.45;}90%{opacity:.25;}
  100%{transform:translateY(-120px) scale(1);opacity:0;}
}

/* ── OVERLAY (mobile) ── */
.sidebar-overlay{
  display:none;
  position:fixed;inset:0;background:rgba(0,0,0,.7);
  backdrop-filter:blur(4px);z-index:90;
}

/* ════════════════════════════════════════════
   ANIMATIONS
════════════════════════════════════════════ */
@keyframes fadeUp{
  from{opacity:0;transform:translateY(20px);}
  to{opacity:1;transform:translateY(0);}
}
@keyframes goldSlide{
  0%{background-position:0% 0;}100%{background-position:200% 0;}
}

/* ════════════════════════════════════════════
   RESPONSIVE
════════════════════════════════════════════ */
@media(max-width:1100px){
  .stats-grid{grid-template-columns:repeat(2,1fr);}
  .quick-links{grid-template-columns:repeat(2,1fr);}
}
@media(max-width:900px){
  .activity-grid{grid-template-columns:1fr;}
  .mgmt-grid{grid-template-columns:1fr;}
  .dash-hero-deco{display:none;}
  .dash-hero-deco-img{display:none;}
}
@media(max-width:768px){
  :root{--sidebar-w:260px;}
  .sidebar{transform:translateX(-100%);}
  .sidebar.open{transform:translateX(0);}
  .sidebar-overlay.open{display:block;}
  .main-content{margin-left:0;}
  .hamburger-btn{display:flex;}
  .page-body{padding:24px 16px 48px;}
  .dash-hero{padding:28px 22px;}
  .dash-hero h1{font-size:1.7em;}
  .topbar{padding:14px 18px;}
  .topbar-time{display:none;}
  .stats-grid{grid-template-columns:1fr 1fr;gap:12px;}
  .quick-links{grid-template-columns:repeat(2,1fr);}
}
@media(max-width:480px){
  .stats-grid{grid-template-columns:1fr;}
  .mc-actions{flex-direction:column;}
  .mc-actions a{width:100%;justify-content:center;}
}
</style>
</head>
<body>

<!-- MODAL "MODE SERIUS" -->
<div class="serius-modal-overlay" id="seriusModalOverlay">
  <div class="serius-modal">
    <div class="serius-modal-icon"><i data-lucide="flame" class="lucide-ic"></i></div>
    <h2>Ngalamin masalah serius ya?</h2>
    <p>Oke silahkan laksanakan, yang mulia.</p>
    <button type="button" onclick="document.getElementById('seriusModalOverlay').classList.remove('show')">Lanjutkan</button>
  </div>
</div>

<!-- PARTICLES -->
<div id="particles-wrap"></div>

<!-- SIDEBAR OVERLAY (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- ═══════════════════════════════════
     LAYOUT
══════════════════════════════════════ -->
<div class="admin-layout">

  <!-- ────────── SIDEBAR ────────── -->
  <aside class="sidebar" id="sidebar">

    <!-- Brand -->
    <div class="sb-brand">
      <div class="sb-label">Admin Panel</div>
      <a class="sb-logo" href="index.php">
        <img src="assets/img/Yolazcake.png" alt="YOLAZCAKE">
        <span class="sb-logo-text">YOLAZCAKE</span>
      </a>
      <div class="sb-badge"><i data-lucide="crown" class="lucide-ic"></i> Admin</div>
    </div>

    <!-- Nav -->
    <nav class="sb-nav">

      <!-- UTAMA -->
      <div class="sb-section-label">Utama</div>
      <a class="sb-link active" href="dashboard.php">
        <span class="sb-link-icon"><i data-lucide="home" class="lucide-ic"></i></span> Dashboard
      </a>
      <a class="sb-link" href="index.php" target="_blank">
        <span class="sb-link-icon"><i data-lucide="globe" class="lucide-ic"></i></span> Lihat Website
      </a>

      <!-- KELOLA DATA -->
      <div class="sb-section-label">Kelola Data</div>
      <a class="sb-link" href="booking/admin_booking.php">
        <span class="sb-link-icon"><i data-lucide="clipboard-list" class="lucide-ic"></i></span> Booking
        <?php if(($s_booking['p'] ?? 0) > 0): ?>
          <span class="sb-link-badge"><?= $s_booking['p'] ?></span>
        <?php endif; ?>
      </a>
      <a class="sb-link" href="pemesanan/data_pemesanan.php">
        <span class="sb-link-icon"><i data-lucide="shopping-bag" class="lucide-ic"></i></span> Pemesanan
        <?php if(($s_pesanan['p'] ?? 0) > 0): ?>
          <span class="sb-link-badge"><?= $s_pesanan['p'] ?></span>
        <?php endif; ?>
      </a>
      <a class="sb-link" href="produk/data_produk.php">
        <span class="sb-link-icon"><i data-lucide="cake-slice" class="lucide-ic"></i></span> Produk
        <?php if($s_stok_bermasalah > 0): ?>
          <span class="sb-link-badge sb-link-badge-warn" title="<?= (int)($s_produk['habis'] ?? 0) ?> habis, <?= (int)($s_produk['menipis'] ?? 0) ?> menipis"><i data-lucide="alert-triangle" class="lucide-ic"></i> <?= $s_stok_bermasalah ?></span>
        <?php endif; ?>
      </a>
      <a class="sb-link" href="member/data_member.php">
        <span class="sb-link-icon"><i data-lucide="users" class="lucide-ic"></i></span> Member
        <?php if(($s_member['p'] ?? 0) > 0): ?>
          <span class="sb-link-badge"><?= $s_member['p'] ?></span>
        <?php endif; ?>
      </a>
      <a class="sb-link" href="meja/data_meja.php">
        <span class="sb-link-icon"><i data-lucide="armchair" class="lucide-ic"></i></span> Meja
      </a>
      <a class="sb-link" href="kategori/data_kategori.php">
        <span class="sb-link-icon"><i data-lucide="tag" class="lucide-ic"></i></span> Kategori Produk
      </a>
      <a class="sb-link" href="riwayat_poin/riwayat_poin.php">
        <span class="sb-link-icon"><i data-lucide="star" class="lucide-ic lucide-fill"></i></span> Riwayat Poin
      </a>
      <a class="sb-link" href="kontak/data_kontak.php">
        <span class="sb-link-icon"><i data-lucide="mail" class="lucide-ic"></i></span> Pesan Kontak
        <?php if(($s_kontak['p'] ?? 0) > 0): ?>
          <span class="sb-link-badge"><?= $s_kontak['p'] ?></span>
        <?php endif; ?>
      </a>
      <a class="sb-link" href="galeri/data_galeri.php">
        <span class="sb-link-icon"><i data-lucide="image" class="lucide-ic"></i></span> Galeri
      </a>
      <a class="sb-link" href="menu_foto/data_menu_foto.php">
        <span class="sb-link-icon"><i data-lucide="sparkles" class="lucide-ic"></i></span> Foto Menu & Highlight
      </a>
      <a class="sb-link" href="promo/data_promo.php">
        <span class="sb-link-icon"><i data-lucide="ticket" class="lucide-ic"></i></span> Promo
      </a>
      <a class="sb-link" href="instagram/ig_stats.php">
        <span class="sb-link-icon"><i data-lucide="camera" class="lucide-ic"></i></span> Statistik Instagram
      </a>
      <a class="sb-link" href="ulasan/data_ulasan_produk.php">
        <span class="sb-link-icon"><i data-lucide="star" class="lucide-ic lucide-fill"></i></span> Ulasan Produk
        <?php if(($s_ulasan_produk['p'] ?? 0) > 0): ?>
          <span class="sb-link-badge"><?= $s_ulasan_produk['p'] ?></span>
        <?php endif; ?>
      </a>
      <a class="sb-link" href="ulasan/data_ulasan_tempat.php">
        <span class="sb-link-icon"><i data-lucide="home" class="lucide-ic"></i></span> Ulasan Tempat & Makanan
        <?php if(($s_ulasan_tempat['p'] ?? 0) > 0): ?>
          <span class="sb-link-badge"><?= $s_ulasan_tempat['p'] ?></span>
        <?php endif; ?>
      </a>

      <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
      <!-- ADMIN -->
      <div class="sb-section-label">Admin</div>
      <a class="sb-link" href="user/data_user.php">
        <span class="sb-link-icon"><i data-lucide="lock-keyhole" class="lucide-ic"></i></span> Kelola Akun
      </a>
      <?php endif; ?>

      <!-- TAMBAH DATA -->
      <div class="sb-section-label">Tambah Data</div>
      <a class="sb-link add-link" href="produk/tambah_produk.php">
        <span class="sb-link-icon"><i data-lucide="plus" class="lucide-ic"></i></span> Tambah Produk
        <span class="sb-link-new">Baru</span>
      </a>
      <a class="sb-link add-link" href="member/tambah_member.php">
        <span class="sb-link-icon"><i data-lucide="plus" class="lucide-ic"></i></span> Tambah Member
        <span class="sb-link-new">Baru</span>
      </a>
      <a class="sb-link add-link" href="meja/tambah_meja.php">
        <span class="sb-link-icon"><i data-lucide="plus" class="lucide-ic"></i></span> Tambah Meja
        <span class="sb-link-new">Baru</span>
      </a>
      <a class="sb-link add-link" href="galeri/tambah_galeri.php">
        <span class="sb-link-icon"><i data-lucide="plus" class="lucide-ic"></i></span> Tambah Foto Galeri
        <span class="sb-link-new">Baru</span>
      </a>

    </nav>

    <!-- Footer / Logout -->
    <div class="sb-footer">
      <a class="sb-logout" href="auth/logout.php" onclick="return confirm('Yakin ingin keluar?')">
        <i data-lucide="log-out" class="lucide-ic"></i> <span>Keluar</span>
      </a>
    </div>

  </aside>
  <!-- end sidebar -->

  <!-- ────────── MAIN ────────── -->
  <div class="main-content">

    <!-- TOPBAR -->
    <div class="topbar">
      <div class="topbar-left">
        <button class="hamburger-btn" id="hamburgerBtn" onclick="toggleSidebar()"><i data-lucide="menu" class="lucide-ic"></i></button>
        <div class="topbar-title">Dashboard Admin</div>
      </div>
      <div class="topbar-right">
        <span class="topbar-time" id="topbarTime"></span>
        <a class="btn-website" href="index.php" target="_blank"><i data-lucide="globe" class="lucide-ic"></i> Lihat Website</a>
        <div class="topbar-user">
          <div class="topbar-avatar">
            <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
          </div>
          <span class="topbar-username"><?= htmlspecialchars($_SESSION['username']) ?></span>
        </div>
      </div>
    </div>

    <!-- PAGE BODY -->
    <div class="page-body">

      <!-- ─── HERO BANNER ─── -->
      <div class="dash-hero" id="dashHero">
        <div class="dash-hero-grid"></div>

        <!-- Video latar "Mode Serius" — 2 video terpisah untuk 2 mode tampilan
             (wallpaper penuh / panel kiri). Hanya dirender kalau ada file video
             di assets/video/, supaya tampilan hero tidak berubah sebelum videonya
             ditaruh. -->
        <?php if ($hero_video_full): ?>
        <div class="hero-media hero-media-full" id="heroMediaFull">
          <video id="heroVideoFull" src="<?= htmlspecialchars($hero_video_full) ?>" autoplay muted loop playsinline></video>
          <div class="hero-media-overlay"></div>
        </div>
        <?php endif; ?>
        <?php if ($hero_video_half): ?>
        <div class="hero-media hero-media-half" id="heroMediaHalf">
          <video id="heroVideoHalf" src="<?= htmlspecialchars($hero_video_half) ?>" autoplay muted loop playsinline></video>
          <div class="hero-media-overlay"></div>
        </div>
        <?php endif; ?>

        <div class="dash-hero-inner">
          <div class="dash-eyebrow flame-text">Panel Kontrol YOLAZCAKE</div>
          <h1><span>Selamat Datang, <?= htmlspecialchars($_SESSION['username']) ?>!</span></h1>
          <div class="dash-hero-sub">
            <span><i data-lucide="calendar" class="lucide-ic"></i> <?= date("d F Y") ?></span>
            <span>⏰ <span id="heroTime"></span></span>
            <span><i data-lucide="sparkle" class="lucide-ic"></i> Sintang, Kalimantan Barat</span>
          </div>
        </div>
        <?php
          // Logo/desain custom diletakkan di assets/img/logo/.
          // Tidak perlu nama file tertentu — otomatis pakai gambar pertama
          // (jpg/jpeg/png/webp) yang ditemukan di folder ini.
          $deco_logo = null;
          $logo_files = glob(__DIR__ . '/assets/img/logo/*.{png,jpg,jpeg,webp,PNG,JPG,JPEG,WEBP}', GLOB_BRACE);
          if (!empty($logo_files)) {
            sort($logo_files);
            $deco_logo = 'assets/img/logo/' . basename($logo_files[0]);
          }
          if ($deco_logo):
        ?>
          <img src="<?= $deco_logo ?>" alt="" class="dash-hero-deco-img">
        <?php else: ?>
          <div class="dash-hero-deco">YZ</div>
        <?php endif; ?>

        <?php if ($hero_video): ?>
        <div class="hero-media-toggle">
          <button type="button" class="hmt-btn" data-mode="full"><i data-lucide="image" class="lucide-ic"></i> Wallpaper Penuh</button>
          <button type="button" class="hmt-btn" data-mode="half"><i data-lucide="tv" class="lucide-ic"></i> Panel Kiri</button>
        </div>
        <?php endif; ?>
      </div>

      <?php if ($is_spotify_owner): ?>
      <!-- ─── SPOTIFY PREMIUM PLAYER (eksklusif — hanya untuk akun ini) ─── -->
      <div class="spotify-premium" id="spotifyPremium">
        <div class="sp-head">
          <div class="sp-art">
            <?php if (!empty($spotify_playlist_meta['thumbnail_url'])): ?>
              <img src="<?= htmlspecialchars($spotify_playlist_meta['thumbnail_url']) ?>" alt="Cover playlist">
            <?php else: ?>
              <i data-lucide="disc-3" class="lucide-ic"></i>
            <?php endif; ?>
          </div>
          <div class="sp-meta">
            <div class="sp-badge"><i data-lucide="lock" class="lucide-ic"></i> Private &mdash; hanya kamu</div>
            <div class="sp-title"><?= htmlspecialchars($spotify_playlist_meta['title'] ?? 'Discover Weekly') ?></div>
            <div class="sp-sub"><i data-lucide="shuffle" class="lucide-ic"></i> Shuffle otomatis dari Spotify</div>
          </div>
        </div>
        <div class="sp-embed-wrap">
          <iframe
            src="https://open.spotify.com/embed/playlist/<?= htmlspecialchars($spotify_playlist_id) ?>?utm_source=generator&theme=0"
            width="100%" height="152" frameborder="0"
            allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
            loading="lazy"></iframe>
        </div>
      </div>
      <?php else: ?>
      <!-- ─── MUSIC PLAYER (gaya Spotify) ─── -->
      <div class="music-player" id="musicPlayer">
        <?php if ($hero_audio): ?>
          <audio id="heroAudio" src="<?= htmlspecialchars($hero_audio) ?>" loop></audio>
          <div class="mp-art"><i data-lucide="music" class="lucide-ic"></i></div>
          <div class="mp-info">
            <div class="mp-title"><?= htmlspecialchars($audio_name) ?></div>
            <div class="mp-progress-wrap">
              <div class="mp-progress" id="mpProgress"></div>
              <input type="range" class="mp-seek" id="mpSeek" min="0" max="100" step="0.1" value="0">
            </div>
            <div class="mp-time"><span id="mpCur">0:00</span> / <span id="mpDur">0:00</span></div>
          </div>
          <button type="button" class="mp-playbtn" id="mpPlayBtn" onclick="toggleMusic()">▶</button>
          <label class="mp-autoplay" title="Putar otomatis musik saat kembali ke dashboard">
            <input type="checkbox" id="mpAutoplayToggle">
            <span class="mp-autoplay-track"><span class="mp-autoplay-thumb"></span></span>
            <span class="mp-autoplay-label">Auto-play</span>
          </label>
        <?php else: ?>
          <div class="mp-art"><i data-lucide="music" class="lucide-ic"></i></div>
          <div class="mp-placeholder">Taruh file musik di <code>assets/audio/</code> untuk mengaktifkan pemutar ini.</div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <!-- ─── STATS ─── -->
      <div class="stats-grid">
        <div class="stat-card s1 flame-card">
          <div class="stat-icon-wrap"><i data-lucide="clipboard-list" class="lucide-ic"></i></div>
          <div class="stat-val" data-count="<?= $s_booking['t'] ?? 0 ?>"><?= $s_booking['t'] ?? 0 ?></div>
          <div class="stat-lbl">Total Booking</div>
          <?php if(($s_booking['p'] ?? 0) > 0): ?>
            <div class="stat-badge">⏳ <?= $s_booking['p'] ?> Pending</div>
          <?php endif; ?>
        </div>
        <div class="stat-card s2 flame-card">
          <div class="stat-icon-wrap"><i data-lucide="shopping-bag" class="lucide-ic"></i></div>
          <div class="stat-val" data-count="<?= $s_pesanan['t'] ?? 0 ?>"><?= $s_pesanan['t'] ?? 0 ?></div>
          <div class="stat-lbl">Total Pemesanan</div>
          <?php if(($s_pesanan['p'] ?? 0) > 0): ?>
            <div class="stat-badge">⏳ <?= $s_pesanan['p'] ?> Menunggu</div>
          <?php endif; ?>
        </div>
        <div class="stat-card s3 flame-card">
          <div class="stat-icon-wrap"><i data-lucide="cake-slice" class="lucide-ic"></i></div>
          <div class="stat-val" data-count="<?= $s_produk['t'] ?? 0 ?>"><?= $s_produk['t'] ?? 0 ?></div>
          <div class="stat-lbl">Total Produk</div>
          <?php if($s_stok_bermasalah > 0): ?>
            <div class="stat-badge"><i data-lucide="alert-triangle" class="lucide-ic"></i> <?= $s_stok_bermasalah ?> Stok Habis/Menipis</div>
          <?php else: ?>
            <div class="stat-badge ok"><i data-lucide="check" class="lucide-ic"></i> Aktif</div>
          <?php endif; ?>
        </div>
        <div class="stat-card s4 flame-card">
          <div class="stat-icon-wrap"><i data-lucide="users" class="lucide-ic"></i></div>
          <div class="stat-val" data-count="<?= $s_member['t'] ?? 0 ?>"><?= $s_member['t'] ?? 0 ?></div>
          <div class="stat-lbl">Total Member</div>
          <div class="stat-badge ok"><i data-lucide="check" class="lucide-ic"></i> Terdaftar</div>
        </div>
      </div>

      <!-- ─── GRAFIK PENJUALAN ─── -->
      <div class="section-hd">
        <div class="sh-icon"><i data-lucide="bar-chart-3" class="lucide-ic"></i></div>
        <h2>Grafik Penjualan</h2>
        <div class="section-hd-line"></div>
      </div>

      <div class="charts-grid">

        <!-- Grafik Penjualan Harian/Mingguan -->
        <div class="chart-card">
          <div class="ac-head">
            <div class="ac-head-left">
              <div class="ac-head-icon"><i data-lucide="trending-up" class="lucide-ic"></i></div>
              <div class="ac-head-title">Tren Penjualan</div>
            </div>
            <div class="chart-toggle" id="salesToggle">
              <button type="button" class="active" data-mode="harian">Harian</button>
              <button type="button" data-mode="mingguan">Mingguan</button>
            </div>
          </div>
          <div class="chart-body">
            <?php
              $ada_data_harian = array_sum($chart_harian_data) > 0;
              $ada_data_mingguan = array_sum($chart_mingguan_data) > 0;
            ?>
            <?php if ($ada_data_harian || $ada_data_mingguan): ?>
              <div class="chart-canvas-wrap"><canvas id="salesChart"></canvas></div>
            <?php else: ?>
              <div class="chart-empty">Belum ada transaksi lunas untuk ditampilkan.</div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Grafik Produk Terlaris -->
        <div class="chart-card">
          <div class="ac-head">
            <div class="ac-head-left">
              <div class="ac-head-icon"><i data-lucide="trophy" class="lucide-ic"></i></div>
              <div class="ac-head-title">Produk Terlaris</div>
            </div>
          </div>
          <div class="chart-body">
            <?php if (!empty($chart_produk_data)): ?>
              <div class="chart-canvas-wrap"><canvas id="topProdukChart"></canvas></div>
            <?php else: ?>
              <div class="chart-empty">Belum ada produk terjual untuk ditampilkan.</div>
            <?php endif; ?>
          </div>
        </div>

      </div>
      <!-- end charts-grid -->

      <!-- ─── MANAGEMENT CARDS ─── -->
      <div class="section-hd">
        <div class="sh-icon"><i data-lucide="settings" class="lucide-ic"></i></div>
        <h2>Manajemen Data</h2>
        <div class="section-hd-line"></div>
      </div>

      <div class="mgmt-grid">

        <!-- Kelola Booking -->
        <div class="mgmt-card booking m1">
          <div class="mc-top">
            <div class="mc-icon"><i data-lucide="clipboard-list" class="lucide-ic"></i></div>
            <div class="mc-count"><?= $s_booking['t'] ?? 0 ?></div>
          </div>
          <div class="mc-title">Kelola Booking</div>
          <div class="mc-desc">
            Konfirmasi, batalkan, atau hapus reservasi meja pelanggan. Pantau status booking secara real-time.
          </div>
          <?php if(($s_booking['p'] ?? 0) > 0): ?>
            <div class="pending-tag">⏳ <?= $s_booking['p'] ?> booking menunggu konfirmasi</div>
          <?php endif; ?>
          <div class="mc-actions" style="margin-top:18px;">
            <a class="btn-primary" href="booking/admin_booking.php"><i data-lucide="clipboard-list" class="lucide-ic"></i> Lihat Semua</a>
          </div>
        </div>

        <!-- Kelola Pemesanan -->
        <div class="mgmt-card pemesanan m2">
          <div class="mc-top">
            <div class="mc-icon"><i data-lucide="shopping-bag" class="lucide-ic"></i></div>
            <div class="mc-count"><?= $s_pesanan['t'] ?? 0 ?></div>
          </div>
          <div class="mc-title">Kelola Pemesanan</div>
          <div class="mc-desc">
            Monitor seluruh transaksi pesanan, update status pembayaran dan pengiriman produk ke meja.
          </div>
          <?php if(($s_pesanan['p'] ?? 0) > 0): ?>
            <div class="pending-tag">⏳ <?= $s_pesanan['p'] ?> pesanan menunggu diproses</div>
          <?php endif; ?>
          <div class="mc-actions" style="margin-top:18px;">
            <a class="btn-primary" href="pemesanan/data_pemesanan.php"><i data-lucide="shopping-bag" class="lucide-ic"></i> Lihat Semua</a>
          </div>
        </div>

        <!-- Kelola Produk -->
        <div class="mgmt-card produk m3">
          <div class="mc-top">
            <div class="mc-icon"><i data-lucide="cake-slice" class="lucide-ic"></i></div>
            <div class="mc-count"><?= $s_produk['t'] ?? 0 ?></div>
          </div>
          <div class="mc-title">Kelola Produk</div>
          <div class="mc-desc">
            Tambah, edit, atau hapus menu produk kafe. Pantau stok dan kelola harga produk dengan mudah.
          </div>
          <?php if($s_stok_bermasalah > 0): ?>
            <div class="pending-tag"><i data-lucide="alert-triangle" class="lucide-ic"></i> <?= $s_stok_bermasalah ?> produk stok habis/menipis</div>
          <?php endif; ?>
          <div class="mc-actions" style="margin-top:18px;">
            <a class="btn-primary" href="produk/data_produk.php"><i data-lucide="cake-slice" class="lucide-ic"></i> Lihat Semua</a>
            <a class="btn-add" href="produk/tambah_produk.php"><i data-lucide="plus" class="lucide-ic"></i> Tambah Produk</a>
          </div>
        </div>

        <!-- Kelola Member -->
        <div class="mgmt-card member m4">
          <div class="mc-top">
            <div class="mc-icon"><i data-lucide="users" class="lucide-ic"></i></div>
            <div class="mc-count"><?= $s_member['t'] ?? 0 ?></div>
          </div>
          <div class="mc-title">Kelola Member</div>
          <div class="mc-desc">
            Kelola data pelanggan terdaftar, edit informasi, pantau poin loyalitas, dan tambah member baru.
          </div>
          <div class="mc-actions" style="margin-top:26px;">
            <a class="btn-primary" href="member/data_member.php"><i data-lucide="users" class="lucide-ic"></i> Lihat Semua</a>
            <a class="btn-add" href="member/tambah_member.php"><i data-lucide="plus" class="lucide-ic"></i> Tambah Member</a>
          </div>
        </div>

      </div>
      <!-- end mgmt-grid -->

      <!-- ─── QUICK ACCESS ─── -->
      <div class="section-hd">
        <div class="sh-icon"><i data-lucide="zap" class="lucide-ic"></i></div>
        <h2>Akses Cepat</h2>
        <div class="section-hd-line"></div>
      </div>

      <div class="quick-links">
        <a class="ql-item" href="index.php" target="_blank">
          <span class="ql-icon"><i data-lucide="home" class="lucide-ic"></i></span>
          <span>Beranda Website</span>
        </a>
        <a class="ql-item" href="pemesanan/menuu.php" target="_blank">
          <span class="ql-icon"><i data-lucide="coffee" class="lucide-ic"></i></span>
          <span>Menu Kafe</span>
        </a>
        <a class="ql-item" href="booking/booking.php" target="_blank">
          <span class="ql-icon"><i data-lucide="calendar-days" class="lucide-ic"></i></span>
          <span>Form Booking</span>
        </a>
        <a class="ql-item" href="auth/logout.php" onclick="return confirm('Yakin ingin keluar?')">
          <span class="ql-icon"><i data-lucide="log-out" class="lucide-ic"></i></span>
          <span>Logout</span>
        </a>
      </div>

      <!-- ─── RECENT ACTIVITY ─── -->
      <div class="section-hd">
        <div class="sh-icon"><i data-lucide="clock" class="lucide-ic"></i></div>
        <h2>Aktivitas Terbaru</h2>
        <div class="section-hd-line"></div>
      </div>

      <div class="activity-grid">

        <!-- Booking Terbaru -->
        <div class="activity-card a1">
          <div class="ac-head">
            <div class="ac-head-left">
              <span class="ac-head-icon"><i data-lucide="clipboard-list" class="lucide-ic"></i></span>
              <span class="ac-head-title">Booking Terbaru</span>
            </div>
            <a class="ac-head-link" href="booking/admin_booking.php">Lihat Semua <i data-lucide="arrow-right" class="lucide-ic"></i></a>
          </div>
          <table class="ac-table">
            <thead>
              <tr>
                <th>Nama</th>
                <th>Tanggal</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $rows = mysqli_num_rows($q_booking);
              if($rows > 0):
                while($r = mysqli_fetch_assoc($q_booking)):
                  $sc = $r['status']==='Dikonfirmasi' ? 's-ok' : ($r['status']==='Dibatalkan' ? 's-batal' : 's-pending');
                  $si = $r['status']==='Dikonfirmasi' ? '<i data-lucide="check-circle" class="lucide-ic"></i>' : ($r['status']==='Dibatalkan' ? '<i data-lucide="ban" class="lucide-ic"></i>' : '⏳');
              ?>
              <tr>
                <td class="td-name"><?= htmlspecialchars($r['nama_pemesan']) ?></td>
                <td><?= htmlspecialchars($r['tanggal_booking']) ?></td>
                <td><span class="s-badge <?= $sc ?>"><?= $si ?> <?= htmlspecialchars($r['status']) ?></span></td>
              </tr>
              <?php endwhile; else: ?>
              <tr><td colspan="3" class="ac-empty">Belum ada data booking</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Pemesanan Terbaru -->
        <div class="activity-card a2">
          <div class="ac-head">
            <div class="ac-head-left">
              <span class="ac-head-icon"><i data-lucide="shopping-bag" class="lucide-ic"></i></span>
              <span class="ac-head-title">Pemesanan Terbaru</span>
            </div>
            <a class="ac-head-link" href="pemesanan/data_pemesanan.php">Lihat Semua <i data-lucide="arrow-right" class="lucide-ic"></i></a>
          </div>
          <table class="ac-table">
            <thead>
              <tr>
                <th>Pemesan</th>
                <th>Total</th>
                <th>Bayar</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $rows2 = mysqli_num_rows($q_pesanan);
              if($rows2 > 0):
                while($r2 = mysqli_fetch_assoc($q_pesanan)):
                  $pc = $r2['status_pembayaran']==='Lunas' ? 's-lunas' : 's-menunggu';
                  $pi = $r2['status_pembayaran']==='Lunas' ? '<i data-lucide="check-circle" class="lucide-ic"></i>' : '⏳';
              ?>
              <tr>
                <td class="td-name"><?= htmlspecialchars($r2['nama_pemesan']) ?></td>
                <td>Rp <?= number_format($r2['total_harga'],0,',','.') ?></td>
                <td><span class="s-badge <?= $pc ?>"><?= $pi ?> <?= htmlspecialchars($r2['status_pembayaran']) ?></span></td>
              </tr>
              <?php endwhile; else: ?>
              <tr><td colspan="3" class="ac-empty">Belum ada data pemesanan</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Pesan Masuk -->
        <div class="activity-card a3">
          <div class="ac-head">
            <div class="ac-head-left">
              <span class="ac-head-icon"><i data-lucide="mail" class="lucide-ic"></i></span>
              <span class="ac-head-title">Pesan Masuk</span>
              <?php if(($s_kontak['p'] ?? 0) > 0): ?>
                <span class="sb-link-badge" style="position:relative;top:0;margin-left:6px;"><?= $s_kontak['p'] ?></span>
              <?php endif; ?>
            </div>
            <a class="ac-head-link" href="kontak/data_kontak.php">Lihat Semua <i data-lucide="arrow-right" class="lucide-ic"></i></a>
          </div>
          <table class="ac-table">
            <thead>
              <tr>
                <th>Nama</th>
                <th>Kategori</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $rows3 = mysqli_num_rows($q_kontak);
              if($rows3 > 0):
                while($r3 = mysqli_fetch_assoc($q_kontak)):
                  $kc = ($r3['kategori'] ?? 'Umum') === 'Bantuan Akun' ? 's-akun' : 's-umum';
                  $ki = ($r3['kategori'] ?? 'Umum') === 'Bantuan Akun' ? '<i data-lucide="key" class="lucide-ic"></i> Bantuan Akun' : '<i data-lucide="message-circle" class="lucide-ic"></i> Umum';
                  $sc3 = $r3['status']==='Dibalas' ? 's-lunas' : ($r3['status']==='Sudah Dibaca' ? 's-umum' : 's-pending');
                  $si3 = $r3['status']==='Dibalas' ? '<i data-lucide="check-circle" class="lucide-ic"></i>' : ($r3['status']==='Sudah Dibaca' ? '<i data-lucide="circle" class="lucide-ic lucide-fill" style="color:#a78bfa"></i>' : '<i data-lucide="circle" class="lucide-ic lucide-fill" style="color:#D4AF37"></i>');
              ?>
              <tr>
                <td class="td-name"><?= htmlspecialchars($r3['nama']) ?></td>
                <td><span class="s-badge <?= $kc ?>"><?= $ki ?></span></td>
                <td><span class="s-badge <?= $sc3 ?>"><?= $si3 ?> <?= htmlspecialchars($r3['status']) ?></span></td>
              </tr>
              <?php endwhile; else: ?>
              <tr><td colspan="3" class="ac-empty">Belum ada pesan masuk</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

      </div>
      <!-- end activity-grid -->

    </div>
    <!-- end page-body -->

    <!-- FOOTER -->
    <div class="dash-footer">
      <div class="dash-footer-brand">YOLAZCAKE</div>
      © <?= date('Y') ?> YOLAZCAKE Sintang · Admin Panel · All rights reserved
    </div>

  </div>
  <!-- end main-content -->

</div>
<!-- end admin-layout -->


<script>
/* ── PARTICLES ── */
(function(){
  const wrap = document.getElementById('particles-wrap');
  const colors = ['rgba(212,175,55,.35)','rgba(232,160,191,.25)','rgba(138,43,226,.2)','rgba(255,255,255,.12)'];
  for(let i=0;i<18;i++){
    const p = document.createElement('div');
    p.className = 'particle';
    const s = Math.random()*5+2;
    p.style.cssText=`width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;animation-duration:${12+Math.random()*14}s;animation-delay:${Math.random()*12}s;`;
    wrap.appendChild(p);
  }
})();

/* ── CLOCK ── */
function updateClock(){
  const now = new Date();
  const h = String(now.getHours()).padStart(2,'0');
  const m = String(now.getMinutes()).padStart(2,'0');
  const s = String(now.getSeconds()).padStart(2,'0');
  const t = `${h}:${m}:${s}`;
  const el1 = document.getElementById('heroTime');
  const el2 = document.getElementById('topbarTime');
  if(el1) el1.textContent = t;
  if(el2) el2.textContent = t;
}
updateClock();
setInterval(updateClock, 1000);

/* ── COUNTER ANIMATION ── */
function animateCounter(el, target, dur){
  let start = 0;
  const step = target / (dur / 16);
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
      animateCounter(el, parseInt(el.dataset.count), 1000);
    });
  }, 400);
});

/* ── SIDEBAR TOGGLE (mobile) ── */
function toggleSidebar(){
  const sb = document.getElementById('sidebar');
  const ov = document.getElementById('sidebarOverlay');
  const btn = document.getElementById('hamburgerBtn');
  const open = sb.classList.toggle('open');
  ov.classList.toggle('open', open);
  btn.innerHTML = open ? '<i data-lucide="x" class="lucide-ic"></i>' : '<i data-lucide="menu" class="lucide-ic"></i>';
  if (window.lucide) lucide.createIcons();
}
function closeSidebar(){
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebarOverlay').classList.remove('open');
  document.getElementById('hamburgerBtn').innerHTML = '<i data-lucide="menu" class="lucide-ic"></i>';
  if (window.lucide) lucide.createIcons();
}

/* ── HERO MEDIA LAYOUT (wallpaper penuh / panel kiri) ── */
(function(){
  const hero = document.getElementById('dashHero');
  const btns = document.querySelectorAll('.hmt-btn');
  const videoFull = document.getElementById('heroVideoFull');
  const videoHalf = document.getElementById('heroVideoHalf');
  if (!hero || (!videoFull && !videoHalf)) return; // belum ada video -> lewati, tampilan hero default

  function setHeroLayout(mode){
    hero.classList.remove('media-full','media-half');
    hero.classList.add('media-' + mode);
    btns.forEach(b => b.classList.toggle('active', b.dataset.mode === mode));
    localStorage.setItem('yolaz_hero_layout', mode);

    // Mainkan hanya video yang sedang terlihat, jeda yang satunya
    // (hemat CPU/RAM — penting untuk perangkat dengan spek terbatas).
    if (mode === 'full') {
      if (videoHalf) videoHalf.pause();
      if (videoFull) videoFull.play().catch(()=>{});
    } else {
      if (videoFull) videoFull.pause();
      if (videoHalf) videoHalf.play().catch(()=>{});
    }
  }

  btns.forEach(b => b.addEventListener('click', () => setHeroLayout(b.dataset.mode)));
  setHeroLayout(localStorage.getItem('yolaz_hero_layout') || 'full');

  // Fallback: sebagian browser kadang mengabaikan atribut autoplay
  // (mis. tab sempat di-background), jadi coba play() manual juga —
  // tapi hanya untuk video yang sedang aktif/terlihat.
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) return;
    const activeVideo = hero.classList.contains('media-full') ? videoFull : videoHalf;
    if (activeVideo && activeVideo.paused) activeVideo.play().catch(()=>{});
  });
})();

/* ── MUSIC PLAYER (gaya Spotify) ── */
const heroAudioEl = document.getElementById('heroAudio');
const mpPlayBtn   = document.getElementById('mpPlayBtn');
const mpPlayerEl  = document.getElementById('musicPlayer');

function fmtTime(t){
  if (!isFinite(t)) return '0:00';
  const m = Math.floor(t / 60);
  const s = Math.floor(t % 60).toString().padStart(2, '0');
  return `${m}:${s}`;
}

function toggleMusic(){
  if (!heroAudioEl) return;
  if (heroAudioEl.paused) heroAudioEl.play().catch(()=>{});
  else heroAudioEl.pause();
}

if (heroAudioEl){
  heroAudioEl.addEventListener('play', () => {
    mpPlayBtn.textContent = '⏸';
    mpPlayerEl.classList.add('playing');
  });
  heroAudioEl.addEventListener('pause', () => {
    mpPlayBtn.textContent = '▶';
    mpPlayerEl.classList.remove('playing');
  });
  let isSeeking = false;
  const mpSeek = document.getElementById('mpSeek');

  heroAudioEl.addEventListener('timeupdate', () => {
    if (isSeeking) return;
    const pct = (heroAudioEl.currentTime / heroAudioEl.duration) * 100 || 0;
    const bar = document.getElementById('mpProgress');
    if (bar) bar.style.width = pct + '%';
    if (mpSeek) mpSeek.value = pct;
    const cur = document.getElementById('mpCur');
    if (cur) cur.textContent = fmtTime(heroAudioEl.currentTime);
  });

  if (mpSeek) {
    mpSeek.addEventListener('input', () => {
      isSeeking = true;
      const pct = parseFloat(mpSeek.value);
      const bar = document.getElementById('mpProgress');
      if (bar) bar.style.width = pct + '%';
      const cur = document.getElementById('mpCur');
      if (cur) cur.textContent = fmtTime((pct / 100) * (heroAudioEl.duration || 0));
    });
    mpSeek.addEventListener('change', () => {
      const pct = parseFloat(mpSeek.value);
      heroAudioEl.currentTime = (pct / 100) * (heroAudioEl.duration || 0);
      isSeeking = false;
    });
  }

  heroAudioEl.addEventListener('loadedmetadata', () => {
    const dur = document.getElementById('mpDur');
    if (dur) dur.textContent = fmtTime(heroAudioEl.duration);
  });

  /* ── AUTO-PLAY MUSIK SAAT KEMBALI KE DASHBOARD ──
     Tersimpan per-browser lewat localStorage. Kalau dinyalakan, musik
     otomatis terputar tiap kali dashboard ini dibuka lagi. Kalau
     dimatikan, musik tetap diam (paused) sampai ditekan manual. */
  const AUTOPLAY_KEY = 'yolaz_music_autoplay';
  const autoplayToggle = document.getElementById('mpAutoplayToggle');
  const isAutoplayOn = () => localStorage.getItem(AUTOPLAY_KEY) === '1';

  if (autoplayToggle) {
    autoplayToggle.checked = isAutoplayOn();

    autoplayToggle.addEventListener('change', () => {
      if (autoplayToggle.checked) {
        localStorage.setItem(AUTOPLAY_KEY, '1');
        heroAudioEl.play().catch(()=>{});
      } else {
        localStorage.setItem(AUTOPLAY_KEY, '0');
        heroAudioEl.pause();
      }
    });
  }

  // Kalau auto-play aktif, coba putar begitu dashboard dibuka.
  if (isAutoplayOn()) {
    heroAudioEl.play().catch(()=>{});
  }
}

/* ── GRAFIK PENJUALAN (Chart.js) ── */
(function(){
  if (typeof Chart === 'undefined') return;

  const goldColor  = '#D4AF37';
  const roseColor  = '#EE2A7B';
  const purpleColor= '#8A2BE2';

  Chart.defaults.color = 'rgba(255,255,255,.55)';
  Chart.defaults.font.family = "'Inter',sans-serif";

  const fmtRupiah = (v) => 'Rp' + Number(v).toLocaleString('id-ID');

  /* ── Tren Penjualan (Harian / Mingguan) ── */
  const salesCanvas = document.getElementById('salesChart');
  if (salesCanvas) {
    const dataSets = {
      harian:   { labels: <?= json_encode($chart_harian_label) ?>,   data: <?= json_encode($chart_harian_data) ?> },
      mingguan: { labels: <?= json_encode($chart_mingguan_label) ?>, data: <?= json_encode($chart_mingguan_data) ?> }
    };

    const gradient = salesCanvas.getContext('2d').createLinearGradient(0,0,0,260);
    gradient.addColorStop(0, 'rgba(212,175,55,.35)');
    gradient.addColorStop(1, 'rgba(212,175,55,0)');

    const salesChart = new Chart(salesCanvas, {
      type: 'line',
      data: {
        labels: dataSets.harian.labels,
        datasets: [{
          label: 'Penjualan',
          data: dataSets.harian.data,
          borderColor: goldColor,
          backgroundColor: gradient,
          borderWidth: 2.5,
          pointBackgroundColor: goldColor,
          pointBorderColor: '#0d0520',
          pointBorderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6,
          tension: .35,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: 'rgba(13,5,32,.95)',
            borderColor: 'rgba(212,175,55,.35)',
            borderWidth: 1,
            padding: 10,
            titleColor: '#fff',
            bodyColor: 'rgba(255,255,255,.85)',
            callbacks: { label: (ctx) => fmtRupiah(ctx.parsed.y) }
          }
        },
        scales: {
          x: { grid: { color: 'rgba(255,255,255,.06)' }, ticks: { font: { size: 11 } } },
          y: {
            grid: { color: 'rgba(255,255,255,.06)' },
            ticks: { font: { size: 11 }, callback: (v) => fmtRupiah(v) }
          }
        }
      }
    });

    const toggleWrap = document.getElementById('salesToggle');
    if (toggleWrap) {
      toggleWrap.querySelectorAll('button').forEach(btn => {
        btn.addEventListener('click', () => {
          toggleWrap.querySelectorAll('button').forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          const mode = btn.dataset.mode;
          salesChart.data.labels = dataSets[mode].labels;
          salesChart.data.datasets[0].data = dataSets[mode].data;
          salesChart.update();
        });
      });
    }
  }

  /* ── Produk Terlaris ── */
  const produkCanvas = document.getElementById('topProdukChart');
  if (produkCanvas) {
    new Chart(produkCanvas, {
      type: 'bar',
      data: {
        labels: <?= json_encode($chart_produk_label) ?>,
        datasets: [{
          label: 'Terjual',
          data: <?= json_encode($chart_produk_data) ?>,
          backgroundColor: [goldColor, roseColor, purpleColor, 'rgba(212,175,55,.6)', 'rgba(238,42,123,.6)'],
          borderRadius: 8,
          maxBarThickness: 38
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: 'rgba(13,5,32,.95)',
            borderColor: 'rgba(212,175,55,.35)',
            borderWidth: 1,
            padding: 10,
            callbacks: { label: (ctx) => ctx.parsed.x + ' terjual' }
          }
        },
        scales: {
          x: { grid: { color: 'rgba(255,255,255,.06)' }, ticks: { precision: 0 } },
          y: { grid: { display: false } }
        }
      }
    });
  }
})();

/* ── MODE SERIUS: popup + auto-play video & musik ── */
(function(){
  const params = new URLSearchParams(window.location.search);
  if (params.get('serius') !== '1') return;

  const modal = document.getElementById('seriusModalOverlay');
  setTimeout(() => { if (modal) modal.classList.add('show'); }, 500);

  // Mainkan video sesuai mode panel yang sedang aktif (full/half)
  const heroEl = document.getElementById('dashHero');
  const heroVideoFullEl = document.getElementById('heroVideoFull');
  const heroVideoHalfEl = document.getElementById('heroVideoHalf');
  const activeHeroVideo = (heroEl && heroEl.classList.contains('media-half')) ? heroVideoHalfEl : heroVideoFullEl;
  if (activeHeroVideo) activeHeroVideo.play().catch(()=>{});
  if (heroAudioEl) {
    heroAudioEl.play().catch(()=>{});
    // Tandai auto-play aktif supaya kunjungan berikutnya musik langsung
    // terputar juga, kecuali admin mematikannya lewat toggle.
    localStorage.setItem('yolaz_music_autoplay', '1');
    const autoplayToggleEl = document.getElementById('mpAutoplayToggle');
    if (autoplayToggleEl) autoplayToggleEl.checked = true;
  }

  // Bersihkan parameter URL supaya popup tidak muncul lagi saat refresh
  window.history.replaceState({}, document.title, window.location.pathname);
})();
</script>


<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide){lucide.createIcons();}</script>
</body>
</html>
