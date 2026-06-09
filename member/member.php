<?php
session_start();

if(!isset($_SESSION['username'])){
    header("Location: ../auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Member - YOLAZCAKE</title>
<link rel="stylesheet" href="../css/style.css">
<style>
/* MEMBER PAGE STYLING */
.member-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 120px 40px 60px;
}

.member-topbar {
  margin-bottom: 40px;
}

.back-home {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border-radius: 50px;
  background: rgba(232, 160, 191, 0.15);
  color: var(--brown);
  text-decoration: none;
  transition: 0.3s;
  font-weight: 500;
}

.back-home:hover {
  background: rgba(232, 160, 191, 0.3);
  transform: translateX(-3px);
}

.member-header {
  text-align: center;
  margin-bottom: 60px;
  padding: 50px 40px;
  background: linear-gradient(135deg, rgba(232, 160, 191, 0.1), rgba(212, 175, 55, 0.1));
  border-radius: 30px;
  border: 2px solid rgba(232, 160, 191, 0.3);
}

.member-header h1 {
  font-size: 3em;
  color: var(--brown);
  margin-bottom: 15px;
}

.member-header p {
  font-size: 1.1em;
  color: #666;
}

/* STATUS SECTION */
.member-status {
  background: linear-gradient(135deg, #FFF9E6, #FFF3E0);
  padding: 60px 40px;
  border-radius: 25px;
  margin-bottom: 50px;
  box-shadow: 0 10px 30px rgba(212, 175, 55, 0.15);
}

.member-status h2 {
  text-align: left;
  font-size: 2em;
  margin-bottom: 30px;
  color: var(--brown);
}

.status-content {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 25px;
}

.status-box {
  background: white;
  padding: 25px;
  border-radius: 18px;
  box-shadow: 0 8px 20px rgba(212, 175, 55, 0.1);
  text-align: center;
  border-left: 4px solid var(--gold);
  transition: 0.3s;
}

.status-box:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 35px rgba(212, 175, 55, 0.2);
}

.status-box .icon {
  font-size: 2.5em;
  margin-bottom: 15px;
}

.status-box h3 {
  font-size: 1.3em;
  color: var(--brown);
  margin-bottom: 10px;
}

.status-box p {
  color: #666;
  font-size: 0.95em;
}

/* PROMO SECTION */
.member-promo {
  background: linear-gradient(135deg, #FFE6F0, #FFE0E6);
  padding: 60px 40px;
  border-radius: 25px;
  margin-bottom: 50px;
  box-shadow: 0 10px 30px rgba(232, 160, 191, 0.15);
}

.member-promo h2 {
  text-align: left;
  font-size: 2em;
  margin-bottom: 30px;
  color: var(--brown);
}

.promo-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 20px;
}

.promo-box {
  background: white;
  padding: 30px;
  border-radius: 18px;
  box-shadow: 0 8px 20px rgba(232, 160, 191, 0.1);
  border-top: 3px solid var(--pink);
  text-align: center;
  transition: 0.3s;
}

.promo-box:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 35px rgba(232, 160, 191, 0.2);
}

.promo-box h3 {
  font-size: 1.5em;
  color: var(--pink);
  font-weight: bold;
  margin-bottom: 12px;
}

.promo-box p {
  color: #666;
  font-size: 0.95em;
}

/* POINTS SECTION */
.member-points {
  background: linear-gradient(135deg, #E6F9F7, #E0F5F3);
  padding: 60px 40px;
  border-radius: 25px;
  margin-bottom: 50px;
  box-shadow: 0 10px 30px rgba(78, 205, 196, 0.15);
}

.member-points h2 {
  text-align: left;
  font-size: 2em;
  margin-bottom: 30px;
  color: var(--brown);
}

.points-main {
  background: linear-gradient(135deg, var(--teal), #5DD9D0);
  color: white;
  padding: 40px;
  border-radius: 20px;
  text-align: center;
  margin-bottom: 30px;
  box-shadow: 0 10px 30px rgba(78, 205, 196, 0.3);
}

.points-number {
  font-size: 3.5em;
  font-weight: bold;
  margin-bottom: 10px;
}

.points-main p {
  font-size: 1.1em;
  opacity: 0.95;
}

.reward-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.reward-box {
  background: white;
  padding: 25px;
  border-radius: 16px;
  text-align: center;
  box-shadow: 0 8px 20px rgba(78, 205, 196, 0.1);
  border: 2px solid rgba(78, 205, 196, 0.2);
  transition: 0.3s;
  font-weight: 500;
  color: var(--brown);
}

.reward-box:hover {
  border-color: var(--teal);
  transform: translateY(-5px);
  box-shadow: 0 12px 30px rgba(78, 205, 196, 0.2);
}

.reward-box .icon {
  font-size: 2em;
  margin-bottom: 10px;
}

/* HISTORY SECTION */
.member-history {
  background: linear-gradient(135deg, #F0E6FF, #E6E0FF);
  padding: 60px 40px;
  border-radius: 25px;
  box-shadow: 0 10px 30px rgba(200, 162, 200, 0.15);
}

.member-history h2 {
  text-align: left;
  font-size: 2em;
  margin-bottom: 30px;
  color: var(--brown);
}

.history-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 25px;
}

.history-item {
  background: white;
  padding: 30px;
  border-radius: 18px;
  box-shadow: 0 8px 20px rgba(200, 162, 200, 0.1);
  border-left: 4px solid var(--lavender);
  transition: 0.3s;
}

.history-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 35px rgba(200, 162, 200, 0.2);
}

.history-item h3 {
  color: var(--brown);
  font-size: 1.1em;
  margin-bottom: 12px;
}

.history-item p {
  color: #666;
  font-size: 1.3em;
  font-weight: 600;
  color: var(--lavender);
}

/* FADE ANIMATION */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.fade {
  animation: fadeInUp 0.6s ease-out forwards;
}

body.dark .member-status,
body.dark .member-promo,
body.dark .member-points,
body.dark .member-history {
  background: rgba(40, 40, 40, 0.8);
}

body.dark .status-box,
body.dark .promo-box,
body.dark .reward-box,
body.dark .history-item {
  background: rgba(50, 50, 50, 0.9);
  color: #e0e0e0;
}

body.dark .status-box h3,
body.dark .promo-box h3,
body.dark .history-item h3 {
  color: #e0e0e0;
}

body.dark .status-box p,
body.dark .promo-box p,
body.dark .history-item p {
  color: #b0b0b0;
}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav>
  <div class="nav-left">
    <img src="https://lh3.googleusercontent.com/gps-cs/ACgwaOvrk66Mw6TuaNg3tG6p8G9hJq_wOTUoBmpbb3qtX0t9CN0D6K8ns6HxQUsk_xRrGiRBD__9n78mwhr3RZ7cwM3UINa2Jjzvzx2U1l8S2SP93wZa3ga4xfn1BY446aaj_CJ_6ACQYiN58RQ=w203-h304-k-no">
    <h2>YOLAZCAKE</h2>
  </div>

  <ul class="main-nav">
    <li onclick="window.location.href='../index.php'">Home</li>
    <li onclick="window.location.href='../menu.php'">Menu</li>
    <li onclick="window.location.href='../gallery.php'">Gallery</li>
    <li onclick="window.location.href='../about.php'">About</li>
    <li onclick="window.location.href='../contact.php'">Contact</li>
  </ul>

  <div class="nav-right">
    <div class="hamburger" onclick="toggleMenu()" id="hamburger">☰</div>
    <div class="dark-btn" onclick="toggleDark()">🌙</div>
  </div>

  <div class="dropdown" id="dropdown">
    <p onclick="window.location.href='../index.php'">Home</p>
    <p onclick="window.location.href='../menu.php'">Menu</p>
    <p onclick="window.location.href='../gallery.php'">Gallery</p>
    <p onclick="window.location.href='../about.php'">About</p>
    <p onclick="window.location.href='../contact.php'">Contact</p>
  </div>
</nav>

<!-- MEMBER PAGE -->
<div class="member-container">

<div class="member-topbar">
  <a href="../index.php" class="back-home">
    🏠 Kembali ke Website
  </a>
</div>

<div class="member-header fade">
  <h1>👋 Halo, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
  <p>Selamat datang di Member YOLAZCAKE - Nikmati berbagai keuntungan eksklusif!</p>
</div>

<!-- STATUS SECTION -->
<section class="member-status fade">
  <h2>Status Member Anda</h2>
  <div class="status-content">
    <div class="status-box">
      <div class="icon">✅</div>
      <h3>Status</h3>
      <p>Member Aktif</p>
    </div>
    <div class="status-box">
      <div class="icon">🎯</div>
      <h3>Tier</h3>
      <p>Gold Member</p>
    </div>
    <div class="status-box">
      <div class="icon">📅</div>
      <h3>Bergabung</h3>
      <p>6 Bulan Lalu</p>
    </div>
  </div>
</section>

<!-- PROMO SECTION -->
<section class="member-promo fade">
  <h2>🎁 Promo Khusus Member</h2>
  <div class="promo-grid">
    <div class="promo-box">
      <h3>YOLA10</h3>
      <p>Diskon 10% untuk semua produk bakery</p>
    </div>
    <div class="promo-box">
      <h3>FREECOFFEE</h3>
      <p>Gratis 1 kopi untuk pembelian di atas Rp50.000</p>
    </div>
    <div class="promo-box">
      <h3>BUY2GET1</h3>
      <p>Beli 2 roti premium, gratis 1 roti pilihan</p>
    </div>
  </div>
</section>

<!-- POINTS SECTION -->
<section class="member-points fade">
  <h2>⭐ Poin & Reward Saya</h2>
  <div class="points-main">
    <div class="points-number">250</div>
    <p>Poin Terkumpul</p>
  </div>
  <div class="reward-grid">
    <div class="reward-box">
      <div class="icon">🎁</div>
      100 Poin<br>
      <strong>Diskon 5%</strong>
    </div>
    <div class="reward-box">
      <div class="icon">☕</div>
      200 Poin<br>
      <strong>Gratis Kopi</strong>
    </div>
    <div class="reward-box">
      <div class="icon">🎂</div>
      500 Poin<br>
      <strong>Gratis Cake</strong>
    </div>
  </div>
</section>

<!-- HISTORY SECTION -->
<section class="member-history fade">
  <h2>📅 Riwayat & Data Member</h2>
  <div class="history-grid">
    <div class="history-item">
      <h3>Login Terakhir</h3>
      <p><?php echo date("d F Y"); ?></p>
    </div>
    <div class="history-item">
      <h3>Status Hari Ini</h3>
      <p>Aktif</p>
    </div>
    <div class="history-item">
      <h3>Total Kunjungan</h3>
      <p>15x</p>
    </div>
  </div>
</section>

</div>

<!-- FOOTER -->
<div class="footer">
  © 2026 YOLAZCAKE Sintang • Cafe • Bakery • Boutique
  <br>
  Jl. Lintas Melawi, Ladang, Sintang, Kalimantan Barat
  <br>
  WA: 0815-7815-7888
</div>

<script src="../js/style.js"></script>

</body>
</html>
