<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About - YOLAZCAKE Sintang</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

  <style>

    /* --- Hero Banner --- */
    .about-hero {
      position: relative;
      height: 360px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      background: linear-gradient(135deg, #2b1a11 0%, #4a2c1a 40%, #6d3e26 70%, #3a1f0e 100%);
    }

    .about-hero::before {
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

    .about-hero .sparkle {
      position: absolute;
      border-radius: 50%;
      pointer-events: none;
      animation: floatDot linear infinite;
    }

    @keyframes floatDot {
      0%   { transform: translateY(0)   rotate(0deg);   opacity: 0; }
      20%  { opacity: 1; }
      80%  { opacity: 0.8; }
      100% { transform: translateY(-360px) rotate(360deg); opacity: 0; }
    }

    .about-hero-inner {
      position: relative;
      z-index: 2;
      text-align: center;
      color: #fff;
    }

    .about-hero-inner .hero-eyebrow {
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

    .about-hero-inner h1 {
      font-family: 'Playfair Display', serif;
      font-size: 3.6em;
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

    @keyframes shimmerText {
      0%   { background-position: 100% 0; }
      100% { background-position: -100% 0; }
    }

    .about-hero-inner .hero-sub {
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
      justify-content: center;
      align-items: center;
      width: 100%;
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

    /* ---- Shared section wrapper ---- */
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

    /* ---- #about section ---- */
    #about {
      padding: 80px 40px;
      background: linear-gradient(160deg, #EEF6FF 0%, #DFF0FF 100%);
      position: relative;
      overflow: hidden;
    }

    #about::before {
      content: '';
      position: absolute;
      top: -80px; right: -80px;
      width: 320px; height: 320px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(212,175,55,0.12) 0%, transparent 70%);
      pointer-events: none;
    }

    .about-intro-grid {
      max-width: 1000px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 28px;
    }

    .about-intro-card {
      background: #fff;
      border-radius: 24px;
      padding: 34px 34px 30px;
      box-shadow: 0 8px 40px rgba(0,0,0,0.08);
      position: relative;
      overflow: hidden;
      opacity: 0;
      transform: translateY(30px);
      transition: transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1),
                  box-shadow 0.45s ease;
    }

    .about-intro-card.show {
      animation: cardReveal 0.7s cubic-bezier(.22,.68,0,1.2) forwards;
    }

    .about-intro-card:hover {
      transform: translateY(-10px) scale(1.03);
      box-shadow:
        0 20px 55px rgba(109,76,65,0.18),
        0 0 30px rgba(212,175,55,0.25),
        0 0 60px rgba(212,175,55,0.12),
        0 0 90px rgba(212,175,55,0.05);
    }

    .about-intro-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 4px;
      background: linear-gradient(90deg, #D4AF37, #6d3e26, #D4AF37);
      background-size: 200% 100%;
      animation: goldSlide 3s linear infinite;
    }

    @keyframes goldSlide {
      0%   { background-position: 0% 0; }
      100% { background-position: 200% 0; }
    }

    @keyframes cardReveal {
      to { opacity: 1; transform: translateY(0); }
    }

    .about-card-icon {
      width: 54px;
      height: 54px;
      border-radius: 50%;
      background: linear-gradient(135deg, rgba(212,175,55,0.2), rgba(212,175,55,0.05));
      border: 1.5px solid rgba(212,175,55,0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.6em;
      margin-bottom: 18px;
    }

    .about-intro-card h3 {
      font-family: 'Playfair Display', serif;
      font-size: 1.3em;
      color: var(--brown);
      margin-bottom: 12px;
      padding: 0;
    }

    .about-intro-card p {
      font-family: 'Inter', sans-serif;
      font-size: 0.93em;
      color: #555;
      line-height: 1.75;
      padding: 0;
    }

    /* ---- #story section ---- */
    #story {
      padding: 80px 40px;
      background: linear-gradient(160deg, #1e0e3a 0%, #2d1560 50%, #1a0a2e 100%);
      position: relative;
      overflow: hidden;
    }

    #story::before {
      content: '';
      position: absolute;
      inset: 0;
      background:
        radial-gradient(ellipse at 20% 30%, rgba(212,175,55,0.12) 0%, transparent 55%),
        radial-gradient(ellipse at 80% 70%, rgba(232,160,191,0.10) 0%, transparent 55%);
      pointer-events: none;
    }

    #story .section-label h2 {
      background: linear-gradient(135deg, #fff 30%, #D4AF37 70%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    #story .section-label .eyebrow {
      color: rgba(212,175,55,0.85);
    }

    .story-card {
      max-width: 800px;
      margin: 0 auto;
      background: rgba(255,255,255,0.06);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 28px;
      padding: 44px 48px;
      position: relative;
      z-index: 2;
      overflow: hidden;
      opacity: 0;
      transform: translateY(40px);
      cursor: default;
      transition: transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1),
                  box-shadow 0.45s ease,
                  border-color 0.45s ease;
    }

    .story-card:hover {
      transform: translateY(-10px) scale(1.02);
      border-color: rgba(212,175,55,0.4);
      box-shadow:
        0 25px 65px rgba(0,0,0,0.35),
        0 0 35px rgba(212,175,55,0.35),
        0 0 70px rgba(212,175,55,0.18),
        0 0 110px rgba(212,175,55,0.08);
    }

    .story-card.show {
      animation: cardReveal 0.8s cubic-bezier(.22,.68,0,1.2) forwards;
    }

    .story-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 3px;
      background: linear-gradient(90deg, #D4AF37, #ee2a7b, #D4AF37);
      background-size: 200% 100%;
      animation: goldSlide 4s linear infinite;
    }

    .story-quote-mark {
      font-family: 'Playfair Display', serif;
      font-size: 5em;
      color: rgba(212,175,55,0.25);
      line-height: 0.6;
      margin-bottom: 20px;
      display: block;
    }

    .story-card p {
      font-family: 'Inter', sans-serif;
      font-size: 1em;
      color: rgba(255,255,255,0.82);
      line-height: 1.85;
      padding: 0;
    }

    .story-badges {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-top: 28px;
    }

    .story-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: linear-gradient(135deg, rgba(212,175,55,0.2), rgba(212,175,55,0.08));
      border: 1px solid rgba(212,175,55,0.4);
      color: #D4AF37;
      font-family: 'Inter', sans-serif;
      font-size: 0.78em;
      font-weight: 600;
      letter-spacing: 1px;
      padding: 8px 16px;
      border-radius: 999px;
      transition: transform 0.3s, box-shadow 0.3s, border-color 0.3s;
    }

    .story-badge:hover {
      transform: translateY(-4px) scale(1.01);
      box-shadow: 0 15px 40px rgba(0,0,0,0.3);
      border-color: rgba(212,175,55,0.3);
    }

    /* ---- #team section ---- */
    #team {
      padding: 80px 40px;
      background: linear-gradient(160deg, #EEF6FF 0%, #DFF0FF 100%);
      position: relative;
      overflow: hidden;
    }

    #team::after {
      content: '';
      position: absolute;
      bottom: -80px; left: -60px;
      width: 280px; height: 280px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(212,175,55,0.1) 0%, transparent 70%);
      pointer-events: none;
    }

    .team-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 28px;
      max-width: 960px;
      margin: 0 auto;
    }

    /* Premium team card */
    .team-card {
      border-radius: 28px;
      overflow: hidden;
      background: #fff;
      box-shadow: 0 10px 40px rgba(0,0,0,0.1);
      opacity: 0;
      transform: translateY(40px);
      transition: transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1),
                  box-shadow 0.45s ease;
      position: relative;
    }

    /* Gold sliding bar on top — sama seperti menu-card */
    .team-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 4px;
      background: linear-gradient(90deg, #D4AF37, #6d3e26, #D4AF37);
      background-size: 200% 100%;
      animation: goldSlide 3s linear infinite;
      z-index: 5;
    }

    .team-card.show {
      animation: cardReveal 0.75s cubic-bezier(.22,.68,0,1.2) forwards;
    }

    .team-card:hover {
      transform: translateY(-10px) scale(1.03);
      box-shadow:
        0 25px 60px rgba(109,76,65,0.25),
        0 0 30px rgba(232,160,191,0.5),
        0 0 60px rgba(212,175,55,0.25),
        0 0 90px rgba(212,175,55,0.1);
    }

.team-card-photo {
    width: 100%;
    height: 320px;
    background-size: cover;
    background-position: center center;
    position: relative;
    overflow: hidden;
}

    /* Shimmer sweep — sama seperti menu-card-photo::before */
    .team-card-photo::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 55%;
      height: 100%;
      background: linear-gradient(
        120deg,
        transparent,
        rgba(255,255,255,0.55),
        transparent
      );
      transform: skewX(-20deg);
      transition: left 0.75s ease;
      pointer-events: none;
      z-index: 3;
    }

    .team-card:hover .team-card-photo::before {
      left: 130%;
    }

    .team-card-photo::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(to bottom, transparent 50%, rgba(43,26,17,0.75) 100%);
    }

    .team-card-photo-overlay {
      position: absolute;
      bottom: 18px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 2;
      display: flex;
      gap: 8px;
    }

    .team-photo-badge {
      background: rgba(212,175,55,0.85);
      color: #2b1a11;
      font-size: 0.65em;
      font-weight: 700;
      letter-spacing: 1.5px;
      padding: 4px 12px;
      border-radius: 999px;
      text-transform: uppercase;
      white-space: nowrap;
      backdrop-filter: blur(4px);
    }

    .team-card-body {
      padding: 26px 28px 28px;
      position: relative;
    }

    .team-card-body::before {
      content: '';
      position: absolute;
      top: 0; left: 28px; right: 28px;
      height: 1.5px;
      background: linear-gradient(to right, transparent, #D4AF37, transparent);
    }

    .team-card-body h3 {
      font-family: 'Playfair Display', serif;
      font-size: 1.25em;
      color: var(--brown);
      margin-bottom: 6px;
      padding: 0;
    }

    .team-card-body p {
      font-family: 'Inter', sans-serif;
      font-size: 0.88em;
      color: #777;
      padding: 0;
      line-height: 1.6;
    }

    .team-tag-row {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      margin-top: 14px;
    }

    .team-tag {
      background: linear-gradient(135deg, #FFF3E0, #FFF9E6);
      border: 1px solid rgba(212,175,55,0.4);
      color: #6d3e26;
      font-size: 0.72em;
      font-weight: 600;
      padding: 4px 12px;
      border-radius: 999px;
      letter-spacing: 0.5px;
      transition: transform 0.3s, box-shadow 0.3s, border-color 0.3s;
    }

    .team-tag:hover {
      transform: translateY(-4px) scale(1.01);
      box-shadow: 0 15px 40px rgba(0,0,0,0.3);
      border-color: rgba(212,175,55,0.3);
    }

    /* ---- Floating particles ---- */
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

    /* DARK MODE */
    body.dark #about,
    body.dark #team {
      background: rgba(18, 25, 40, 0.95) !important;
    }

    body.dark .about-intro-card,
    body.dark .team-card {
      background: #1e1e2a;
      box-shadow: 0 8px 40px rgba(0,0,0,0.4);
    }

    body.dark .about-intro-card h3 { color: #e0c88a; }
    body.dark .about-intro-card p  { color: #aaa; }
    body.dark .team-card-body h3   { color: #e0c88a; }
    body.dark .team-card-body p    { color: #aaa; }
    body.dark .team-tag { background: #252535; border-color: #3a3a5a; color: #D4AF37; }

    /* RESPONSIVE */
    @media (max-width: 860px) {
      .about-intro-grid {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 768px) {
      .about-hero-inner h1 {
        font-size: 2.4em;
      }
      .about-hero {
        height: 300px;
      }
      #about, #story, #team {
        padding: 60px 20px;
      }
      .story-card {
        padding: 30px 24px;
      }
      .team-grid {
        grid-template-columns: 1fr;
      }
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
    <li onclick="window.location.href='index.php'">Home</li>
    <li onclick="window.location.href='produk/menu.php'">Menu</li>
    <li onclick="window.location.href='gallery.php'">Gallery</li>
    <li class="active" onclick="window.location.href='about.php'">About</li>
    <li onclick="window.location.href='contact.php'">Contact</li>
  </ul>
  <div class="nav-right">
<?php if(isset($_SESSION['username'])){ ?>
    <div class="account-dropdown">
      <button class="account-btn">👤 <?php echo htmlspecialchars($_SESSION['username']); ?> ▼</button>
      <div class="account-menu">
        <a href="member/member.php">Member</a>
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
    <p onclick="window.location.href='contact.php#contact'">Contact &amp; Sosmed</p>
  </div>
</nav>

<!-- ========== PREMIUM HERO BANNER ========== -->
<div class="about-hero" id="aboutHero">
  <div class="about-hero-inner">
    <p class="hero-eyebrow">✦ YOLAZCAKE Sintang ✦</p>
    <h1>Tentang Kami</h1>
    <p class="hero-sub">Cafe • Bakery • Boutique — Rasa, Gaya &amp; Cerita di Satu Tempat 🎂</p>
    <div class="hero-divider">
      <span></span>
      <span class="diamond">✦ ✦ ✦</span>
      <span></span>
    </div>
  </div>
</div>

<!-- ========== ABOUT SECTION ========== -->
<section id="about">

  <div class="section-label fade">
    <span class="eyebrow">✦ Siapa Kami</span>
    <h2>Tentang YOLAZCAKE Sintang</h2>
    <div class="gold-rule"><span>✦ ✦ ✦</span></div>
  </div>

  <div class="about-intro-grid">

    <div class="about-intro-card fade">
      <div class="about-card-icon">🎂</div>
      <h3>Kuliner Premium</h3>
      <p>
        YOLAZCAKE Sintang adalah destinasi unik yang menggabungkan pengalaman kuliner premium
        dengan fashion modern. Kami menyajikan kue homemade, dessert lezat, kopi spesial, dan
        minuman segar di lantai 1 — diciptakan dari bahan pilihan terbaik.
      </p>
    </div>

    <div class="about-intro-card fade">
      <div class="about-card-icon">👗</div>
      <h3>Boutique Modern</h3>
      <p>
        Di lantai 2, nikmati boutique pakaian wanita yang stylish dan kurasi koleksi fashion
        terkini. Kami percaya setiap kunjungan harus menjadi pengalaman yang menyenangkan —
        tempat bersantai, bekerja, atau berbelanja dengan nyaman.
      </p>
    </div>

    <div class="about-intro-card fade">
      <div class="about-card-icon">📍</div>
      <h3>Lokasi Strategis</h3>
      <p>
        Terletak di Jl. Lintas Melawi, Ladang, Sintang — kami hadir untuk warga Sintang dan
        sekitarnya. Mudah dijangkau, nyaman, dan selalu menyambut siapa saja dengan kehangatan
        khas YOLAZCAKE.
      </p>
    </div>

    <div class="about-intro-card fade">
      <div class="about-card-icon">✨</div>
      <h3>Pengalaman Berkesan</h3>
      <p>
        Dari cita rasa hingga estetika ruang, kami merawat setiap detail. Setiap sudut dirancang
        untuk memberi kenyamanan, sehingga Anda betah berlama-lama menikmati waktu terbaik
        bersama orang-orang tersayang.
      </p>
    </div>

  </div>

</section>

<!-- ========== STORY SECTION ========== -->
<section id="story">

  <!-- Floating particles -->
  <div id="storyParticles"></div>

  <div class="section-label fade">
    <span class="eyebrow">✦ Perjalanan Kami</span>
    <h2>Cerita di Balik YOLAZCAKE</h2>
    <div class="gold-rule"><span>✦ ✦ ✦</span></div>
  </div>

  <div class="story-card fade">
    <span class="story-quote-mark">"</span>
    <p>
      Dari passion seorang pemilik yang mencintai baking dan fashion, lahirlah YOLAZCAKE Sintang.
      Kami ingin menciptakan ruang di mana orang bisa menikmati rasa enak sekaligus tampil cantik.
      Setiap kue dibuat dengan cinta, setiap kopi diseduh dengan hati, dan setiap pakaian dipilih
      dengan selera tinggi.
    </p>
    <p style="margin-top: 18px;">
      Perjalanan ini dimulai dari dapur kecil yang penuh mimpi — kini tumbuh menjadi tempat yang
      dicintai warga Sintang. Kami terus berinovasi agar setiap kunjungan membawa kenangan indah
      yang tak terlupakan.
    </p>
    <div class="story-badges">
      <span class="story-badge">🎂 Homemade Cake</span>
      <span class="story-badge">☕ Specialty Coffee</span>
      <span class="story-badge">👗 Fashion Boutique</span>
      <span class="story-badge">💛 Made with Love</span>
      <span class="story-badge">📍 Sintang, Kalbar</span>
    </div>
  </div>

</section>

<!-- ========== TEAM SECTION ========== -->
<section id="team">

  <div class="section-label fade">
    <span class="eyebrow">✦ Orang-Orang Hebat</span>
    <h2>Tim YOLAZCAKE</h2>
    <div class="gold-rule"><span>✦ ✦ ✦</span></div>
  </div>

  <div class="team-grid">

    <div class="team-card fade">
      <div class="team-card-photo"
        style="background-image:url('https://lh3.googleusercontent.com/gps-cs/ACgwaOvxKT5lSYSqtGXdOEcolvesgLmUOI5LslddKbpvMrujZolC12At5NaoivmDdUFn5UvXuXT9qDCqkXvjfNuLkqCYc4YMPUurAss2BrIJv1UwIFeNyJE0aix_tHo4cd6T3AsDiWIO1jSvMxPl=w203-h252-k-no')">
        <div class="team-card-photo-overlay">
          <span class="team-photo-badge">✦ Pastry Expert</span>
        </div>
      </div>
      <div class="team-card-body">
        <h3>Chef Baker</h3>
        <p>Spesialis kue homemade premium dengan teknik baking terbaik.</p>
        <div class="team-tag-row">
          <span class="team-tag">🎂 Custom Cake</span>
          <span class="team-tag">🍰 Dessert</span>
          <span class="team-tag">🥐 Pastry</span>
        </div>
      </div>
    </div>

    <div class="team-card fade">
      <div class="team-card-photo"
        style="background-image:url('https://lh3.googleusercontent.com/gps-cs/ACgwaOvhYwysSMlaYth1DKEgkhviQ6lh1kKQitri0i1SOWzsyvlH6WOnQ0hcpnMdyFNbyzMfr6iJKwUl36Xuks9zee3KAVK4pucZQZQSmAu7jFE5icLeGF0ZV1L5fhGUdP6KlvpfapNH7KSlbF0l=w203-h451-k-no')">
        <div class="team-card-photo-overlay">
          <span class="team-photo-badge">✦ Coffee Artisan</span>
        </div>
      </div>
      <div class="team-card-body">
        <h3>Barista</h3>
        <p>Ahli latte art & specialty coffee dengan cita rasa internasional.</p>
        <div class="team-tag-row">
          <span class="team-tag">☕ Latte Art</span>
          <span class="team-tag">🫘 Specialty</span>
          <span class="team-tag">🥤 Minuman</span>
        </div>
      </div>
    </div>

    <div class="team-card fade">
      <div class="team-card-photo"
        style="background-image:url('https://lh3.googleusercontent.com/gps-cs/ACgwaOv7c7BKrobg4hOcBrLVmOkm-mvRw18q1pB2e-xkGSQ6F34AhGeIH9tGr942BsT4llGFdzjtEC2-W6fJvaLb6cuSnQfOhvAmPfF4HYvCHaB-gAOSbdSc74Jo3WyAtaD0Yp04P8U-K0FxbCs=w203-h444-k-no')">
        <div class="team-card-photo-overlay">
          <span class="team-photo-badge">✦ Style Curator</span>
        </div>
      </div>
      <div class="team-card-body">
        <h3>Fashion Stylist</h3>
        <p>Kurator koleksi boutique dengan sentuhan gaya yang selalu trendi.</p>
        <div class="team-tag-row">
          <span class="team-tag">👗 Fashion</span>
          <span class="team-tag">💄 Style</span>
          <span class="team-tag">✨ Boutique</span>
        </div>
      </div>
    </div>

  </div>

</section>

<!-- FOOTER -->
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
    const hero = document.getElementById('aboutHero');
    const colors = ['#D4AF37','#FFE4B5','#E8A0BF','#fff','#f9ce34','#b8860b'];
    for(let i = 0; i < 24; i++){
      const dot = document.createElement('div');
      dot.classList.add('sparkle');
      const size = Math.random() * 5 + 2;
      dot.style.cssText = `
        width:${size}px; height:${size}px;
        background:${colors[Math.floor(Math.random()*colors.length)]};
        left:${Math.random()*100}%;
        bottom:${Math.random()*30}%;
        animation-duration:${4 + Math.random()*7}s;
        animation-delay:${Math.random()*5}s;
        opacity:0;
      `;
      hero.appendChild(dot);
    }
  })();

  /* --- Floating particles in #story --- */
  (function(){
    const container = document.getElementById('storyParticles');
    const colors = ['rgba(212,175,55,0.5)','rgba(232,160,191,0.4)','rgba(255,255,255,0.2)'];
    for(let i = 0; i < 18; i++){
      const p = document.createElement('div');
      p.classList.add('particle');
      const size = Math.random() * 6 + 2;
      p.style.cssText = `
        width:${size}px; height:${size}px;
        background:${colors[Math.floor(Math.random()*colors.length)]};
        left:${Math.random()*100}%;
        animation-duration:${9 + Math.random()*10}s;
        animation-delay:${Math.random()*8}s;
      `;
      container.appendChild(p);
    }
  })();

  /* --- Scroll reveal (IntersectionObserver) --- */
  (function(){
    const targets = document.querySelectorAll('.fade');
    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry, idx) => {
        if(entry.isIntersecting){
          const delay = entry.target.closest('.about-intro-grid, .team-grid')
            ? Array.from(entry.target.parentElement.children).indexOf(entry.target) * 0.12
            : 0;
          entry.target.style.animationDelay = delay + 's';
          entry.target.classList.add('show');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });

    targets.forEach(t => io.observe(t));
  })();

</script>
</body>
</html>

gggg