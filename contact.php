<?php
session_start();
require_once 'config/koneksi.php';

$msg_kontak  = '';
$err_kontak  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kirim_kontak'])) {
    $nama    = trim($_POST['nama']);
    $email   = trim($_POST['email']);
    $no_hp   = trim($_POST['no_hp']);
    $subjek  = trim($_POST['subjek']);
    $pesan   = trim($_POST['pesan']);

    if (!$nama || !$pesan) {
        $err_kontak = 'Nama dan pesan wajib diisi!';
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO kontak (nama, email, no_hp, subjek, pesan)
             VALUES (?,?,?,?,?)"
        );
        $stmt->bind_param("sssss", $nama, $email, $no_hp, $subjek, $pesan);
        $ok = $stmt->execute();
        $stmt->close();
        if ($ok) {
            $msg_kontak = 'Pesan berhasil dikirim! Kami akan segera menghubungi Anda.';
        } else {
            $err_kontak = 'Gagal menyimpan pesan. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact - YOLAZCAKE Sintang</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/responsive.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

  <style>

    /* =============================================
       PREMIUM CONTACT PAGE — scoped styles only
    ============================================= */

    /* --- Hero Banner --- */
    .contact-hero {
      position: relative;
      height: 340px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      background: linear-gradient(135deg, #2b1a11 0%, #4a2c1a 40%, #6d3e26 70%, #3a1f0e 100%);
    }

    .contact-hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background:
        radial-gradient(ellipse at 30% 50%, rgba(212,175,55,0.18) 0%, transparent 60%),
        radial-gradient(ellipse at 75% 40%, rgba(232,160,191,0.15) 0%, transparent 55%);
      animation: heroAurora 8s ease-in-out infinite alternate;
    }

    @keyframes heroAurora {
      0%   { opacity: 0.6; transform: scale(1) translateX(0); }
      100% { opacity: 1;   transform: scale(1.08) translateX(10px); }
    }

    /* floating sparkle dots */
    .contact-hero .sparkle {
      position: absolute;
      border-radius: 50%;
      pointer-events: none;
      animation: floatDot linear infinite;
    }

    @keyframes floatDot {
      0%   { transform: translateY(0)   rotate(0deg);   opacity: 0; }
      20%  { opacity: 1; }
      80%  { opacity: 0.8; }
      100% { transform: translateY(-300px) rotate(360deg); opacity: 0; }
    }

    .contact-hero-inner {
      position: relative;
      z-index: 2;
      text-align: center;
      color: #fff;
    }

    .contact-hero-inner .hero-eyebrow {
      font-family: 'Inter', sans-serif;
      font-size: 0.78em;
      font-weight: 500;
      letter-spacing: 5px;
      text-transform: uppercase;
      color: #D4AF37;
      margin-bottom: 12px;
      opacity: 0;
      animation: fadeSlideDown 0.8s forwards 0.3s;
    }

    .contact-hero-inner h1 {
      font-family: 'Playfair Display', serif;
      font-size: 3.4em;
      font-weight: 700;
      line-height: 1.1;
      background: linear-gradient(135deg, #fff 30%, #D4AF37 60%, #FFE4B5 80%, #fff);
      background-size: 200% 100%;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      animation: shimmerText 4s ease-in-out infinite, fadeSlideDown 0.9s forwards 0.5s;
      opacity: 0;
    }

    .contact-hero-inner .hero-sub {
      font-family: 'Inter', sans-serif;
      font-size: 1em;
      color: rgba(255,255,255,0.75);
      margin-top: 14px;
      opacity: 0;
      animation: fadeSlideDown 0.9s forwards 0.9s;
    }

    .hero-divider {
    position: relative;
    z-index: 2;
    margin-top: 22px;
    display: flex;
    justify-content: center;   /* Tambahkan */
    align-items: center;
    width: 100%;               /* Tambahkan */
    gap: 12px;
    opacity: 0;
    animation: fadeSlideDown 0.9s forwards 1.1s;
}

    .hero-divider span {
      display: block;
      width: 80px;
      height: 1px;
      background: linear-gradient(to right, transparent, #D4AF37);
    }

    .hero-divider span:last-child {
      background: linear-gradient(to left, transparent, #D4AF37);
    }

    .hero-divider .diamond {
      color: #D4AF37;
      font-size: 0.75em;
      letter-spacing: 4px;
    }

    @keyframes fadeSlideDown {
      from { opacity: 0; transform: translateY(-18px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* --- Section wrappers --- */
    #location, #contact {
      padding: 80px 40px;
    }

    #location {
      background: linear-gradient(160deg, #EEF6FF 0%, #DFF0FF 100%);
    }

    #contact {
      background: linear-gradient(160deg, #1e0e3a 0%, #2d1560 50%, #1a0a2e 100%);
      position: relative;
      overflow: hidden;
    }

    #contact::before {
      content: '';
      position: absolute;
      inset: 0;
      background:
        radial-gradient(ellipse at 20% 30%, rgba(212,175,55,0.12) 0%, transparent 55%),
        radial-gradient(ellipse at 80% 70%, rgba(232,160,191,0.1) 0%, transparent 55%);
      pointer-events: none;
    }

    /* Premium section heading */
    .section-label {
      display: flex;
      flex-direction: column;
      align-items: center;
      margin-bottom: 56px;
      gap: 8px;
    }

    .section-label .eyebrow {
      font-family: 'Inter', sans-serif;
      font-size: 0.72em;
      letter-spacing: 5px;
      text-transform: uppercase;
      color: #D4AF37;
      font-weight: 600;
    }

    .section-label h2 {
      font-family: 'Playfair Display', serif;
      font-size: 2.6em;
      font-weight: 700;
      margin: 0;
      text-align: center;
      color: var(--brown);
    }

    #contact .section-label h2 {
      background: linear-gradient(135deg, #fff 30%, #D4AF37 70%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    #contact .section-label .eyebrow {
      color: rgba(212,175,55,0.85);
    }

    .section-label .gold-rule {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-top: 4px;
    }

    .section-label .gold-rule::before,
    .section-label .gold-rule::after {
      content: '';
      display: block;
      width: 50px;
      height: 1px;
      background: linear-gradient(to right, transparent, #D4AF37);
    }

    .section-label .gold-rule::after {
      background: linear-gradient(to left, transparent, #D4AF37);
    }

    .section-label .gold-rule span {
      font-size: 0.6em;
      color: #D4AF37;
      letter-spacing: 3px;
    }

    /* --- Location cards --- */
    .loc-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 28px;
      max-width: 1000px;
      margin: 0 auto;
    }

    .loc-card {
      background: #fff;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 8px 40px rgba(0,0,0,0.09);
      transition: transform 0.4s ease, box-shadow 0.4s ease;
      opacity: 0;
      transform: translateY(30px);
    }

    .loc-card.show {
      animation: cardReveal 0.7s cubic-bezier(.22,.68,0,1.2) forwards;
    }

    @keyframes cardReveal {
      to { opacity: 1; transform: translateY(0); }
    }

    .loc-card:hover {
      transform: translateY(-10px) scale(1.01);
      box-shadow: 0 20px 60px rgba(109,76,65,0.18);
    }

    .loc-card-header {
      background: linear-gradient(135deg, #6D4C41, #4a2c1a);
      padding: 22px 28px 18px;
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .loc-card-icon {
      width: 46px;
      height: 46px;
      border-radius: 50%;
      background: rgba(212,175,55,0.2);
      border: 1.5px solid rgba(212,175,55,0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4em;
      flex-shrink: 0;
    }

    .loc-card-header h3 {
      font-family: 'Playfair Display', serif;
      color: #fff;
      font-size: 1.25em;
      font-weight: 600;
      padding: 0;
    }

    .loc-card-body {
      padding: 24px 28px;
    }

    .loc-card-body iframe {
      border-radius: 14px;
      margin-bottom: 18px;
    }

    .info-row {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      margin-bottom: 14px;
      font-size: 0.95em;
    }

    .info-row .info-icon {
      font-size: 1.1em;
      margin-top: 2px;
      flex-shrink: 0;
    }

    .info-row span {
      color: #555;
      line-height: 1.55;
    }

    .info-row strong {
      color: var(--brown);
    }

    /* Hours badge */
    .hours-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: linear-gradient(135deg, #D4AF37, #FFE4B5);
      color: #4a2c1a;
      font-weight: 700;
      font-size: 0.78em;
      padding: 5px 14px;
      border-radius: 999px;
      margin-bottom: 14px;
      letter-spacing: 0.5px;
    }

    .day-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      margin-top: 8px;
    }

    .day-chip {
      background: linear-gradient(135deg, #FFF3E0, #FFF9E6);
      border: 1px solid #F5E6D3;
      border-radius: 12px;
      padding: 10px 14px;
      text-align: center;
    }

    .day-chip .day-label {
      font-size: 0.72em;
      color: #999;
      text-transform: uppercase;
      letter-spacing: 1px;
      display: block;
    }

    .day-chip .day-time {
      font-size: 0.9em;
      font-weight: 600;
      color: var(--brown);
      display: block;
      margin-top: 4px;
    }

    /* --- Social & Contact section --- */
    .socmed-layout {
      max-width: 1100px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: 1fr 1.4fr;
      gap: 32px;
      align-items: start;
      position: relative;
      z-index: 2;
    }

    /* Instagram premium card */
    .ig-premium-card {
      border-radius: 28px;
      overflow: hidden;
      background: #0f0f0f;
      box-shadow: 0 20px 60px rgba(0,0,0,0.5);
      opacity: 0;
      transform: translateY(40px);
      transition: transform 0.5s ease, box-shadow 0.5s ease;
    }

    .ig-premium-card.show {
      animation: cardReveal 0.8s cubic-bezier(.22,.68,0,1.2) forwards;
    }

    .ig-premium-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 30px 80px rgba(238,42,123,0.35);
    }

    .ig-banner {
      background: linear-gradient(135deg, #f9ce34 0%, #ee2a7b 50%, #6228d7 100%);
      padding: 32px 28px 28px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 14px;
      position: relative;
      overflow: hidden;
    }

    .ig-banner::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(249,206,52,0.3), rgba(238,42,123,0.2), rgba(98,40,215,0.3));
      animation: igGlow 4s ease-in-out infinite alternate;
    }

    @keyframes igGlow {
      0%   { opacity: 0.5; }
      100% { opacity: 1; }
    }

    .ig-avatar-wrap {
      position: relative;
      z-index: 2;
      width: 88px;
      height: 88px;
      border-radius: 50%;
      background: linear-gradient(135deg, #f9ce34, #ee2a7b, #6228d7);
      padding: 3px;
      animation: float 3.5s ease-in-out infinite;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50%       { transform: translateY(-8px); }
    }

    .ig-avatar-inner {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      background: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.4em;
      border: 3px solid #fff;
    }

    .ig-handle {
      position: relative;
      z-index: 2;
      font-family: 'Playfair Display', serif;
      font-size: 1.2em;
      color: #fff;
      font-weight: 700;
      text-align: center;
    }

    .ig-tagline {
      position: relative;
      z-index: 2;
      font-size: 0.78em;
      color: rgba(255,255,255,0.85);
      text-align: center;
      letter-spacing: 0.5px;
    }

    .ig-body {
      padding: 22px 24px;
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    .ig-stat-row {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 10px;
    }

    .ig-stat {
      background: #1a1a1a;
      border-radius: 12px;
      padding: 12px 8px;
      text-align: center;
      border: 1px solid #2e2e2e;
      transition: background 0.3s;
    }

    .ig-stat:hover {
      background: #222;
    }

    .ig-stat .num {
      display: block;
      font-size: 1.2em;
      font-weight: 700;
      color: #fff;
    }

    .ig-stat .lbl {
      display: block;
      font-size: 0.68em;
      color: #999;
      margin-top: 3px;
      letter-spacing: 0.5px;
    }

    .ig-desc {
      font-size: 0.85em;
      color: #aaa;
      line-height: 1.6;
      text-align: center;
    }

    .ig-btn {
      display: block;
      width: 100%;
      padding: 13px;
      border-radius: 12px;
      background: linear-gradient(135deg, #ee2a7b, #6228d7);
      color: #fff;
      font-weight: 700;
      font-size: 0.9em;
      text-align: center;
      text-decoration: none;
      letter-spacing: 0.5px;
      border: none;
      cursor: pointer;
      transition: opacity 0.3s, transform 0.3s;
    }

    .ig-btn:hover {
      opacity: 0.88;
      transform: translateY(-2px);
    }

    /* WA Form premium */
    .wa-premium-card {
      border-radius: 28px;
      overflow: hidden;
      background: rgba(255,255,255,0.06);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255,255,255,0.1);
      box-shadow: 0 20px 60px rgba(0,0,0,0.4);
      opacity: 0;
      transform: translateY(40px);
    }

    .wa-premium-card.show {
      animation: cardReveal 0.8s cubic-bezier(.22,.68,0,1.2) 0.15s forwards;
    }

    .wa-card-header {
      background: linear-gradient(135deg, rgba(37,211,102,0.15), rgba(37,211,102,0.05));
      border-bottom: 1px solid rgba(37,211,102,0.2);
      padding: 24px 30px;
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .wa-icon-badge {
      width: 52px;
      height: 52px;
      border-radius: 50%;
      background: linear-gradient(135deg, #25d366, #128c7e);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.6em;
      box-shadow: 0 6px 20px rgba(37,211,102,0.4);
      animation: waPulse 2.5s ease-in-out infinite;
    }

    @keyframes waPulse {
      0%, 100% { box-shadow: 0 6px 20px rgba(37,211,102,0.4); }
      50%       { box-shadow: 0 6px 30px rgba(37,211,102,0.7), 0 0 40px rgba(37,211,102,0.2); }
    }

    .wa-card-header-text h3 {
      font-family: 'Playfair Display', serif;
      font-size: 1.25em;
      color: #fff;
      font-weight: 600;
      padding: 0;
    }

    .wa-card-header-text p {
      font-size: 0.8em;
      color: rgba(255,255,255,0.6);
      margin-top: 3px;
      padding: 0;
    }

    .wa-form-body {
      padding: 28px 30px;
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    .form-field {
      position: relative;
    }

    .form-field label {
      display: block;
      font-size: 0.72em;
      font-weight: 600;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: rgba(255,255,255,0.5);
      margin-bottom: 7px;
    }

    .form-field input,
    .form-field textarea {
      width: 100%;
      padding: 13px 16px;
      border-radius: 12px;
      border: 1.5px solid rgba(255,255,255,0.12);
      background: rgba(255,255,255,0.07);
      color: #fff;
      font-size: 0.95em;
      font-family: 'Inter', sans-serif;
      outline: none;
      transition: border-color 0.3s, box-shadow 0.3s, background 0.3s;
    }

    .form-field input::placeholder,
    .form-field textarea::placeholder {
      color: rgba(255,255,255,0.3);
    }

    .form-field input:focus,
    .form-field textarea:focus {
      border-color: rgba(212,175,55,0.7);
      background: rgba(255,255,255,0.1);
      box-shadow: 0 0 0 3px rgba(212,175,55,0.12);
    }

    .form-field textarea {
      height: 120px;
      resize: none;
    }

    /* Ripple effect on focus label */
    .form-field input:focus ~ label,
    .form-field textarea:focus ~ label {
      color: #D4AF37;
    }

    .wa-send-btn {
      width: 100%;
      padding: 15px;
      border-radius: 14px;
      border: none;
      background: linear-gradient(135deg, #25d366, #128c7e);
      color: #fff;
      font-size: 1em;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      letter-spacing: 0.5px;
      transition: all 0.35s ease;
      box-shadow: 0 8px 25px rgba(37,211,102,0.35);
      position: relative;
      overflow: hidden;
    }

    .wa-send-btn::before {
      content: '';
      position: absolute;
      top: 0; left: -100%;
      width: 60%;
      height: 100%;
      background: linear-gradient(120deg, transparent, rgba(255,255,255,0.25), transparent);
      transform: skewX(-20deg);
      transition: left 0.6s ease;
    }

    .wa-send-btn:hover::before { left: 160%; }

    .wa-send-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 14px 35px rgba(37,211,102,0.5);
    }

    /* Floating particles in #contact */
    .particle {
      position: absolute;
      border-radius: 50%;
      pointer-events: none;
      animation: particleFloat linear infinite;
    }

    @keyframes particleFloat {
      0%   { transform: translateY(100vh) scale(0); opacity: 0; }
      10%  { opacity: 0.6; }
      90%  { opacity: 0.4; }
      100% { transform: translateY(-100px) scale(1); opacity: 0; }
    }

    /* footer stays untouched */

    /* DARK MODE ADJUSTMENTS */
    body.dark #location {
      background: rgba(20,30,50,0.9) !important;
    }

    body.dark .loc-card {
      background: #1e1e2a;
      box-shadow: 0 8px 40px rgba(0,0,0,0.4);
    }

    body.dark .loc-card-body iframe {
      filter: brightness(0.85) contrast(1.1);
    }

    body.dark .day-chip {
      background: #252535;
      border-color: #3a3a5a;
    }

    body.dark .day-chip .day-time { color: #e0c88a; }
    body.dark .info-row span { color: #aaa; }

    /* RESPONSIVE */
    @media (max-width: 900px) {
      .socmed-layout {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 768px) {
      .contact-hero-inner h1 {
        font-size: 2.2em;
      }
      .contact-hero {
        height: 280px;
      }
      #location, #contact {
        padding: 60px 20px;
      }
      .loc-grid {
        grid-template-columns: 1fr;
      }
      .wa-form-body, .wa-card-header {
        padding: 20px;
      }
      .day-grid {
        grid-template-columns: 1fr;
      }
    }

  </style>
</head>
<body>

<!-- NAVBAR (unchanged) -->
<nav>
  <div class="nav-left">
    <img src="assets/img/Yolazcake.png" alt="YOLAZCAKE Logo">
    <h2>YOLAZCAKE</h2>
  </div>
  <ul class="main-nav">
    <li onclick="window.location.href='index.php'">Home</li>
    <li onclick="window.location.href='produk/menu.php'">Menu</li>
    <li onclick="window.location.href='gallery.php'">Gallery</li>
    <li onclick="window.location.href='about.php'">About</li>
    <li class="active" onclick="window.location.href='contact.php'">Contact</li>
  </ul>
  <div class="nav-right">
<?php if(isset($_SESSION['username'])){ ?>
    <div class="account-dropdown">
      <button class="account-btn">👤 <?php echo htmlspecialchars($_SESSION['username']); ?> ▼</button>
      <div class="account-menu">
        <a href="<?php echo (isset($_SESSION['role']) && $_SESSION['role']==='admin') ? 'dashboard.php' : 'member/member.php'; ?>"><?php echo (isset($_SESSION['role']) && $_SESSION['role']==='admin') ? 'Dashboard' : 'Member'; ?></a>
        <a href="auth/logout.php">Logout</a>
      </div>
    </div>
<?php } else { ?>
    <button class="login-btn" onclick="window.location.href='auth/login.php'">Login</button>
<?php } ?>
    <div class="hamburger" onclick="toggleMenu()" id="hamburger">☰</div>
    <div class="dark-btn" onclick="toggleDark()">🌙</div>
  </div>
  <div class="dropdown" id="dropdown">
    <p onclick="window.location.href='about.php#story'">Back Story</p>
    <p onclick="window.location.href='produk/menu.php#Product'">Featured Product</p>
    <p onclick="window.location.href='produk/menu.php#promo'">Promo</p>
    <p onclick="window.location.href='gallery.php#Rating'">Rating</p>
    <p onclick="window.location.href='gallery.php#gallery'">Gallery</p>
    <p onclick="window.location.href='about.php#team'">Team</p>
    <p onclick="window.location.href='contact.php#location'">Location</p>
    <p onclick="window.location.href='contact.php#contact'">Contact & Sosmed</p>
  </div>
</nav>

<!-- ========== PREMIUM HERO BANNER ========== -->
<div class="contact-hero" id="contactHero">
  <!-- Sparkle dots injected via JS -->
  <div class="contact-hero-inner">
    <p class="hero-eyebrow">✦ YOLAZCAKE Sintang ✦</p>
    <h1>Hubungi Kami</h1>
    <p class="hero-sub">Kami senang mendengar dari Anda — pesan, reservasi, atau sekadar sapa 🎂</p>
    <div class="hero-divider">
      <span></span>
      <span class="diamond">✦ ✦ ✦</span>
      <span></span>
    </div>
  </div>
</div>

<!-- ========== LOCATION ========== -->
<section id="location">

  <div class="section-label fade">
    <span class="eyebrow">✦ Temukan Kami</span>
    <h2>Lokasi &amp; Jam Operasional</h2>
    <div class="gold-rule"><span>✦ ✦ ✦</span></div>
  </div>

  <div class="loc-grid">

    <!-- Alamat card -->
    <div class="loc-card fade">
      <div class="loc-card-header">
        <div class="loc-card-icon">📍</div>
        <h3>Alamat Kami</h3>
      </div>
      <div class="loc-card-body">
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d997.453989154095!2d111.48428296954839!3d0.06236769999606715!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31fe210037bc6e03%3A0x8886e2dfe7f1e1e3!2sYOLAZCAKE%20SINTANG!5e0!3m2!1sen!2sid!4v1777045337846!5m2!1sen!2sid"
          width="100%" height="230" style="border:0;" allowfullscreen="" loading="lazy">
        </iframe>
        <div class="info-row">
          <span class="info-icon">🏠</span>
          <span>Jl. Lintas Melawi, Ladang, Kec. Sintang,<br>Kabupaten Sintang, Kalimantan Barat</span>
        </div>
        <div class="info-row">
          <span class="info-icon">📞</span>
          <span><strong>WA:</strong> 0815-7815-7888</span>
        </div>
      </div>
    </div>

    <!-- Jam Buka card -->
    <div class="loc-card fade">
      <div class="loc-card-header">
        <div class="loc-card-icon">🕐</div>
        <h3>Jam Operasional</h3>
      </div>
      <div class="loc-card-body">
        <div class="hours-badge">🟢 Buka Setiap Hari</div>
        <div class="day-grid">
          <div class="day-chip">
            <span class="day-label">Senin – Minggu</span>
            <span class="day-time">08.00 – 22.00</span>
          </div>
          <div class="day-chip">
            <span class="day-label">Boutique Lt. 2</span>
            <span class="day-time">08.00 – 21.00</span>
          </div>
        </div>
        <div class="info-row" style="margin-top:18px">
          <span class="info-icon">ℹ️</span>
          <span style="font-size:0.88em;color:#888">Boutique Lantai 2 tutup 1 jam lebih awal. Pemesanan kue dapat dilakukan setiap hari.</span>
        </div>
      </div>
    </div>

  </div>

</section>

<!-- ========== CONTACT & SOSMED ========== -->
<section id="contact">

  <!-- Floating particles -->
  <div id="particles"></div>

  <div class="section-label fade">
    <span class="eyebrow">✦ Terhubung Dengan Kami</span>
    <h2>Media Sosial &amp; Kontak</h2>
    <div class="gold-rule"><span>✦ ✦ ✦</span></div>
  </div>

  <div class="socmed-layout">

    <!-- INSTAGRAM PREMIUM -->
    <div class="ig-premium-card fade">
      <div class="ig-banner">
        <div class="ig-avatar-wrap">
          <div class="ig-avatar-inner">📸</div>
        </div>
        <div class="ig-handle">@yolazcake.stg</div>
        <div class="ig-tagline">Cake • Bakery • Boutique — Sintang</div>
      </div>
      <div class="ig-body">
        <div class="ig-stat-row">
          <div class="ig-stat">
            <span class="num">✦</span>
            <span class="lbl">Posts</span>
          </div>
          <div class="ig-stat">
            <span class="num">✦</span>
            <span class="lbl">Followers</span>
          </div>
          <div class="ig-stat">
            <span class="num">✦</span>
            <span class="lbl">Following</span>
          </div>
        </div>
        <p class="ig-desc">
          Ikuti kami untuk update menu terbaru, promo spesial, dan inspirasi kue setiap hari! 🎂
        </p>
        <a href="https://www.instagram.com/yolazcake.stg/" target="_blank" class="ig-btn">
          ✦ &nbsp;Follow di Instagram
        </a>
      </div>
    </div>

    <!-- CONTACT FORM — simpan ke database -->
    <div class="wa-premium-card fade">
      <div class="wa-card-header">
        <div class="wa-icon-badge">✉️</div>
        <div class="wa-card-header-text">
          <h3>Kirim Pesan ke Kami</h3>
          <p>Pesan Anda akan langsung kami terima dan balas ⚡</p>
        </div>
      </div>
      <div class="wa-form-body">

        <?php if ($msg_kontak): ?>
        <div style="background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.35);color:#6ee7b7;padding:14px 18px;border-radius:12px;margin-bottom:18px;font-size:.9em;">
          ✅ <?= htmlspecialchars($msg_kontak) ?>
        </div>
        <?php endif; ?>

        <?php if ($err_kontak): ?>
        <div style="background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);color:#fca5a5;padding:14px 18px;border-radius:12px;margin-bottom:18px;font-size:.9em;">
          ⚠️ <?= htmlspecialchars($err_kontak) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="contact.php">
          <div class="form-field">
            <label>Nama Lengkap <span style="color:#fca5a5;">*</span></label>
            <input type="text" name="nama" placeholder="Contoh: Siti Rahayu" required>
          </div>

          <div class="form-field">
            <label>Email</label>
            <input type="email" name="email" placeholder="email@contoh.com">
          </div>

          <div class="form-field">
            <label>Nomor WhatsApp</label>
            <input type="tel" name="no_hp" placeholder="08xxxxxxxxxx" id="wa_nomor">
          </div>

          <div class="form-field">
            <label>Subjek</label>
            <input type="text" name="subjek" placeholder="Contoh: Tanya menu, Reservasi, dll">
          </div>

          <div class="form-field">
            <label>Pesan <span style="color:#fca5a5;">*</span></label>
            <textarea name="pesan" id="wa_pesan" placeholder="Halo YOLAZCAKE, saya ingin..." required></textarea>
          </div>

          <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" name="kirim_kontak" class="wa-send-btn" style="flex:1;">
              <span>✉️</span>
              <span>Kirim Pesan</span>
            </button>
            <button type="button" class="wa-send-btn" onclick="sendWhatsApp()"
              style="flex:1;background:linear-gradient(135deg,#25D366,#128C7E);">
              <span>💬</span>
              <span>Via WhatsApp</span>
            </button>
          </div>
        </form>

      </div>
    </div>

  </div>

</section>

<!-- FOOTER (unchanged) -->
<div class="footer">
  © 2026 YOLAZCAKE Sintang • Cafe • Bakery • Boutique
  <br>
  Jl. Lintas Melawi, Ladang, Sintang, Kalimantan Barat
  <br>
  WA: 0815-7815-7888
</div>

<script src="js/style.js"></script>
<script>

  /* --- Floating sparkles in hero --- */
  (function(){
    const hero = document.getElementById('contactHero');
    const colors = ['#D4AF37','#FFE4B5','#E8A0BF','#fff','#f9ce34','#ee2a7b'];
    for(let i = 0; i < 22; i++){
      const dot = document.createElement('div');
      dot.classList.add('sparkle');
      const size = Math.random() * 5 + 2;
      dot.style.cssText = `
        width:${size}px; height:${size}px;
        background:${colors[Math.floor(Math.random()*colors.length)]};
        left:${Math.random()*100}%;
        bottom:${Math.random()*30}%;
        animation-duration:${4 + Math.random()*6}s;
        animation-delay:${Math.random()*5}s;
        opacity:0;
      `;
      hero.appendChild(dot);
    }
  })();

  /* --- Floating particles in #contact section --- */
  (function(){
    const container = document.getElementById('particles');
    const colors = ['rgba(212,175,55,0.5)','rgba(232,160,191,0.4)','rgba(255,255,255,0.2)'];
    for(let i = 0; i < 18; i++){
      const p = document.createElement('div');
      p.classList.add('particle');
      const size = Math.random() * 6 + 2;
      p.style.cssText = `
        width:${size}px; height:${size}px;
        background:${colors[Math.floor(Math.random()*colors.length)]};
        left:${Math.random()*100}%;
        animation-duration:${8 + Math.random()*10}s;
        animation-delay:${Math.random()*8}s;
      `;
      container.appendChild(p);
    }
  })();

  /* --- WhatsApp send --- */
  function sendWhatsApp(){
    const nama  = document.getElementById('wa_nama').value.trim();
    const nomor = document.getElementById('wa_nomor').value.trim();
    const pesan = document.getElementById('wa_pesan').value.trim();

    if(!nama || !nomor || !pesan){
      alert('Mohon isi semua kolom terlebih dahulu 😊');
      return;
    }

    const msg = encodeURIComponent(
      `Halo YOLAZCAKE! 👋\n\n*Nama:* ${nama}\n*No. WA:* ${nomor}\n\n*Pesan:*\n${pesan}`
    );
    window.open(`https://wa.me/6281578157888?text=${msg}`, '_blank');
  }

</script>

<?php include 'status_fab.php'; ?>

</body>
</html>
