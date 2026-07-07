<?php
session_start();
require_once __DIR__.'/config/staff_guard.php';
require_staff_login('auth/login.php', 'member/member.php');
require_once 'config/koneksi.php';

/* ── LEWATI MODE DASAR JIKA DI SESI INI SUDAH PERNAH MASUK MODE SERIUS ──
   Ditandai di session (bukan database) supaya tiap logout -> login ulang
   dianggap kunjungan baru dan Mode Dasar tampil lagi. Tapi selama masih
   dalam sesi login yang sama (pindah halaman lalu balik ke dashboard),
   Mode Dasar tidak perlu tampil ulang. */
if (!empty($_SESSION['sudah_mode_serius'])) {
  header('Location: dashboard.php');
  exit();
}

/* Statistik ringan saja — bukan panel kelola penuh */
$s_booking = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t FROM booking"));
$s_pesanan = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t FROM pemesanan"));
$s_kontak  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t, SUM(status='Belum Dibaca') AS p FROM kontak"));

$jam = (int) date('H');
if ($jam < 11)      $sapaan = 'Selamat Pagi';
elseif ($jam < 15)   $sapaan = 'Selamat Siang';
elseif ($jam < 18)   $sapaan = 'Selamat Sore';
else                 $sapaan = 'Selamat Malam';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="assets/css/lucide-icons.css">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mode Dasar – YOLAZCAKE</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
:root{
  --gold:#D4AF37; --gold-l:#FFE88A;
  --rose:#EE2A7B; --purple:#8A2BE2;
  --bg1:#0d0520; --bg2:#1a0a3a; --bg3:#150830;
  --glass:rgba(255,255,255,.045); --gb:rgba(255,255,255,.10);
  --text:#fff; --muted:rgba(255,255,255,.5);
}
html,body{height:100%;}
body{
  min-height:100vh;
  font-family:'Inter',sans-serif;
  background:linear-gradient(140deg,var(--bg1) 0%,var(--bg2) 50%,var(--bg3) 100%);
  color:var(--text);
  display:flex;align-items:center;justify-content:center;
  overflow-x:hidden;
  position:relative;
}
body::before{
  content:'';position:fixed;inset:0;pointer-events:none;
  background:
    radial-gradient(ellipse 60% 50% at 15% 20%,rgba(212,175,55,.10) 0%,transparent 55%),
    radial-gradient(ellipse 55% 50% at 85% 80%,rgba(138,43,226,.10) 0%,transparent 55%);
  animation:drift 12s ease-in-out infinite alternate;
}
@keyframes drift{0%{opacity:.7;}100%{opacity:1;transform:scale(1.05);}}

.wrap{
  position:relative;z-index:2;
  width:92%;max-width:620px;
  padding:52px 46px;
  text-align:center;
  background:var(--glass);
  backdrop-filter:blur(22px);
  border:1px solid var(--gb);
  border-radius:30px;
  box-shadow:0 30px 80px rgba(0,0,0,.4);
  opacity:0;transform:translateY(26px);
  animation:cardIn .7s cubic-bezier(.22,.68,0,1.2) forwards .1s;
}
@keyframes cardIn{to{opacity:1;transform:translateY(0);}}

.badge-mode{
  display:inline-flex;align-items:center;gap:8px;
  font-size:.68em;font-weight:700;letter-spacing:3px;text-transform:uppercase;
  color:var(--muted);
  background:rgba(255,255,255,.05);
  border:1px solid rgba(255,255,255,.12);
  border-radius:999px;padding:6px 16px;margin-bottom:22px;
}

h1{
  font-family:'Playfair Display',serif;
  font-size:2.1em;font-weight:700;margin-bottom:8px;
}
h1 span{
  background:linear-gradient(135deg,#fff 0%,var(--gold) 40%,var(--gold-l) 60%,#fff 100%);
  background-size:250% 100%;
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  animation:shimmer 5s ease-in-out infinite;
}
@keyframes shimmer{0%,100%{background-position:0 0;}50%{background-position:100% 0;}}

.sub{color:var(--muted);font-size:.95em;margin-bottom:34px;line-height:1.6;}

.stats-row{
  display:grid;grid-template-columns:repeat(3,1fr);gap:14px;
  margin-bottom:38px;
}
.stat{
  background:rgba(255,255,255,.04);
  border:1px solid var(--gb);
  border-radius:16px;padding:18px 10px;
}
.stat b{
  display:block;font-family:'Playfair Display',serif;
  font-size:1.5em;color:var(--gold);
}
.stat span{font-size:.68em;color:var(--muted);letter-spacing:.5px;text-transform:uppercase;}

.locked-note{
  font-size:.78em;color:var(--muted);
  margin-bottom:26px;
  display:flex;align-items:center;justify-content:center;gap:8px;
}

.btn-serius{
  position:relative;
  display:inline-flex;align-items:center;gap:10px;
  padding:16px 38px;
  font-family:'Inter',sans-serif;font-weight:700;font-size:1em;letter-spacing:.5px;
  color:#fff;
  background:linear-gradient(135deg,var(--purple),var(--rose) 55%,var(--gold) 100%);
  background-size:220% 100%;
  border:none;border-radius:14px;cursor:pointer;
  box-shadow:0 12px 30px rgba(138,43,226,.35);
  transition:transform .25s,box-shadow .25s,background-position .6s;
  overflow:hidden;
}
.btn-serius:hover{
  transform:translateY(-3px);
  background-position:100% 0;
  box-shadow:0 16px 40px rgba(238,42,123,.4);
}
.btn-serius:active{transform:translateY(-1px);}

.footer-line{
  margin-top:30px;
  font-size:.75em;color:rgba(255,255,255,.35);
}
.footer-line a{color:var(--gold);text-decoration:none;}
.footer-line a:hover{text-decoration:underline;}
</style>
</head>
<body>

<div class="wrap">
  <div class="badge-mode"><i data-lucide="lock" class="lucide-ic"></i> Mode Dasar · Fitur Terbatas</div>
  <h1><span><?= $sapaan ?>, <?= htmlspecialchars($_SESSION['username']) ?></span></h1>
  <p class="sub">Kamu sedang berada di panel ringan. Beberapa fitur kelola disembunyikan
     dulu supaya tidak berantakan — cukup untuk lihat ringkasan cepat hari ini.</p>

  <div class="stats-row">
    <div class="stat"><b><?= $s_booking['t'] ?? 0 ?></b><span>Booking</span></div>
    <div class="stat"><b><?= $s_pesanan['t'] ?? 0 ?></b><span>Pesanan</span></div>
    <div class="stat"><b><?= $s_kontak['t'] ?? 0 ?></b><span>Pesan Masuk</span></div>
  </div>

  <div class="locked-note"><i data-lucide="key-round" class="lucide-ic"></i> Fitur kelola penuh (produk, member, promo, dll) masih terkunci.</div>

  <button class="btn-serius" onclick="masukModeSerius()"><i data-lucide="flame" class="lucide-ic"></i> Mode Serius?</button>

  <div class="footer-line">
    atau <a href="auth/logout.php" onclick="return confirm('Yakin ingin keluar?')">keluar dari akun</a>
  </div>
</div>

<script>
function masukModeSerius(){
  window.location.href = 'dashboard.php?serius=1';
}
</script>

<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide){lucide.createIcons();}</script>
</body>
</html>
