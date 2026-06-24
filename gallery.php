<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gallery - YOLAZCAKE Sintang</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>

    /* ============================================================
       GALLERY PREMIUM — Upgrade Layer
       (Tidak mengubah style.css, semua override di sini)
    ============================================================ */

    /* ---- HERO BANNER ---- */
    .gallery-hero {
      position: relative;
      height: 360px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      overflow: hidden;
      background: linear-gradient(135deg, #2b1a11 0%, #4a2c1a 40%, #6d3e26 70%, #3a1f0e 100%);
    }

    /* floating orbs */
    .gallery-hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background:
        radial-gradient(circle at 20% 40%, rgba(232,160,191,0.35) 0%, transparent 45%),
        radial-gradient(circle at 80% 60%, rgba(212,175,55,0.35) 0%, transparent 45%),
        radial-gradient(circle at 50% 10%, rgba(200,162,200,0.2) 0%, transparent 40%);
      animation: orbFloat 8s ease-in-out infinite alternate;
    }

    @keyframes orbFloat {
      0%   { transform: scale(1) translateY(0); }
      100% { transform: scale(1.08) translateY(-12px); }
    }

    /* particle sparkles */
    .gallery-hero .sparkle-wrap {
      position: absolute;
      inset: 0;
      pointer-events: none;
    }
    .sparkle {
      position: absolute;
      border-radius: 50%;
      background: rgba(255,215,0,0.75);
      animation: sparklePulse 3s ease-in-out infinite;
    }
    @keyframes sparklePulse {
      0%, 100% { opacity: 0; transform: scale(0.4); }
      50%       { opacity: 1; transform: scale(1); }
    }

    .gallery-hero-content {
      position: relative;
      z-index: 2;
    }

    .gallery-hero-eyebrow {
      font-family: 'Inter', sans-serif;
      font-size: 0.78em;
      font-weight: 500;
      letter-spacing: 5px;
      text-transform: uppercase;
      color: #D4AF37;
      margin-bottom: 12px;
      opacity: 0;
      animation: fadeInDown 0.9s forwards 0.3s;
    }

    .gallery-hero h1 {
      font-family: 'Playfair Display', serif;
      font-size: clamp(2.2em, 5vw, 3.6em);
      font-weight: 700;
      line-height: 1.1;
      background: linear-gradient(135deg, #fff 30%, #D4AF37 60%, #FFE4B5 80%, #fff);
      background-size: 200% 100%;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      opacity: 0;
      animation: shimmerText 4s ease-in-out infinite, fadeInDown 0.9s forwards 0.6s;
    }

    .gallery-hero h1 span {
      background: linear-gradient(90deg, #FFD700, #FFE4B5, #D4AF37, #FFF, #D4AF37);
      background-size: 300% 100%;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      animation: shimmerText 3.5s ease-in-out infinite;
    }

    .gallery-hero p {
      color: rgba(255,255,255,0.75);
      margin-top: 14px;
      font-size: 1.05em;
      opacity: 0;
      animation: fadeInDown 0.9s forwards 0.9s;
    }

    /* scroll-down arrow */
    .scroll-hint {
      position: absolute;
      bottom: 22px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 3;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 4px;
      opacity: 0;
      animation: fadeInDown 1s forwards 1.5s;
    }
    
    .scroll-hint span {
      display: block;
      width: 10px;
      height: 10px;
      border-right: 2px solid rgba(255,215,0,0.8);
      border-bottom: 2px solid rgba(255,215,0,0.8);
      transform: rotate(45deg);
      animation: scrollBounce 1.4s infinite;
    }
    .scroll-hint span:nth-child(2) { animation-delay: 0.2s; }
    .scroll-hint span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes scrollBounce {
      0%, 100% { opacity: 0.3; transform: rotate(45deg) translateY(-4px); }
      50%       { opacity: 1;   transform: rotate(45deg) translateY(4px); }
    }

    @keyframes shimmerText {
      0%   { background-position: 100% 0; }
      100% { background-position: -100% 0; }
    }

    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-25px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* ---- Hero Divider (sama seperti about) ---- */
    .gallery-hero-divider {
      position: relative;
      z-index: 2;
      margin-top: 22px;
      display: flex;
      justify-content: center;
      align-items: center;
      width: 100%;
      gap: 12px;
      opacity: 0;
      animation: fadeInDown 0.9s forwards 1.1s;
    }

    .gallery-hero-divider span {
      display: block;
      width: 80px;
      height: 1px;
      background: linear-gradient(to right, transparent, #D4AF37);
    }

    .gallery-hero-divider span:last-child {
      background: linear-gradient(to left, transparent, #D4AF37);
    }

    .gallery-hero-divider .hero-diamond {
      color: #D4AF37;
      font-size: 0.75em;
      letter-spacing: 4px;
      width: auto;
      height: auto;
      background: none;
    }

    /* ---- FILTER TABS ---- */
    .gallery-filter {
      display: flex;
      justify-content: center;
      gap: 12px;
      flex-wrap: wrap;
      padding: 40px 20px 10px;
    }

    .filter-btn {
      padding: 10px 24px;
      border-radius: 50px;
      border: 2px solid transparent;
      background: rgba(255,255,255,0.7);
      color: var(--brown);
      font-weight: 600;
      font-size: 0.9em;
      cursor: pointer;
      transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
      backdrop-filter: blur(8px);
      box-shadow: 0 4px 15px rgba(0,0,0,0.07);
      position: relative;
      overflow: hidden;
    }

    .filter-btn::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, var(--pink), var(--gold));
      opacity: 0;
      transition: opacity 0.3s;
      border-radius: 50px;
      z-index: -1;
    }

    .filter-btn:hover,
    .filter-btn.active {
      color: #fff;
      border-color: transparent;
      transform: translateY(-3px) scale(1.05);
      box-shadow: 0 8px 25px rgba(232,160,191,0.4);
    }

    .filter-btn:hover::after,
    .filter-btn.active::after {
      opacity: 1;
    }

    .body.dark .filter-btn {
      background: rgba(40,40,40,0.8);
      color: #eee;
    }

    /* ---- PREMIUM GALLERY GRID ---- */
    #gallery {
      padding: 30px 40px 80px;
      background: linear-gradient(160deg, #F9E6FF 0%, #F3F0FF 50%, #FFF3E0 100%);
      position: relative;
    }

    #gallery::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 3px;
      background: linear-gradient(90deg, var(--pink), var(--gold), var(--lavender), var(--pink));
      background-size: 300% 100%;
      animation: shimmerLine 4s linear infinite;
    }

    @keyframes shimmerLine {
      0%   { background-position: 0% 0; }
      100% { background-position: 300% 0; }
    }

    #gallery h2 {
      text-align: center;
      font-size: clamp(1.8em, 3.5vw, 2.8em);
      font-weight: 800;
      margin-bottom: 10px;
      background: linear-gradient(135deg, var(--brown), var(--pink), var(--gold));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .gallery-subtitle {
      text-align: center;
      color: #888;
      margin-bottom: 40px;
      font-size: 0.95em;
      letter-spacing: 1px;
    }

    /* Masonry-style grid */
    .gallery-grid {
      columns: 3 280px;
      column-gap: 24px;
    }

    /* ---- PHOTO CARD ---- */
    .photo-card {
      position: relative;
      border-radius: 20px;
      overflow: hidden;
      margin-bottom: 24px;
      break-inside: avoid;
      cursor: zoom-in;
      box-shadow: 0 8px 30px rgba(0,0,0,0.12);
      transition: transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1),
                  box-shadow 0.45s ease;
      /* fade-in starts invisible */
      opacity: 0;
      transform: translateY(50px) scale(0.96);
    }

    .photo-card.show {
      opacity: 1;
      transform: translateY(0) scale(1);
    }

    .photo-card:hover {
      transform: translateY(-10px) scale(1.03);
      box-shadow:
        0 25px 60px rgba(109,76,65,0.25),
        0 0 30px rgba(232,160,191,0.5),
        0 0 60px rgba(212,175,55,0.25),
        0 0 90px rgba(212,175,55,0.1);
      z-index: 5;
    }

    /* shimmer sweep on card */
    .photo-card::after {
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
      transition: 0.8s ease;
      pointer-events: none;
    }

    .photo-card:hover::after {
      left: 130%;
    }

    /* golden border shimmer */
    .photo-card::before {
      content: '';
      position: absolute;
      inset: 0;
      border-radius: 20px;
      padding: 2px;
      background: linear-gradient(
        135deg,
        rgba(232,160,191,0.8),
        rgba(212,175,55,0.8),
        rgba(200,162,200,0.8),
        rgba(232,160,191,0.8)
      );
      background-size: 300% 300%;
      -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
      -webkit-mask-composite: xor;
      mask-composite: exclude;
      opacity: 0;
      transition: opacity 0.4s;
      animation: borderSpin 4s linear infinite;
      z-index: 4;
      pointer-events: none;
    }

    .photo-card:hover::before {
      opacity: 1;
    }

    @keyframes borderSpin {
      0%   { background-position: 0%   50%; }
      50%  { background-position: 100% 50%; }
      100% { background-position: 0%   50%; }
    }

    .photo-card img {
      width: 100%;
      display: block;
      transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    /* zoom on hover */
    .photo-card:hover img {
      transform: scale(1.08);
    }

    .photo-card .card-label {
      position: absolute;
      bottom: 0; left: 0; right: 0;
      background: linear-gradient(to top, rgba(40,20,10,0.85) 0%, transparent 100%);
      padding: 50px 18px 18px;
      transform: translateY(100%);
      transition: transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
      z-index: 3;
    }

    .photo-card:hover .card-label {
      transform: translateY(0);
    }

    .card-label h3 {
      color: #fff;
      font-size: 1.05em;
      font-weight: 700;
      margin-bottom: 4px;
    }

    .card-label p {
      color: rgba(255,255,255,0.75);
      font-size: 0.82em;
    }

    /* ---- LIGHTBOX ---- */
    .lightbox {
      position: fixed;
      inset: 0;
      z-index: 99999;
      display: flex;
      align-items: center;
      justify-content: center;
      pointer-events: none;
      opacity: 0;
      transition: opacity 0.4s;
    }

    .lightbox.active {
      opacity: 1;
      pointer-events: all;
    }

    .lightbox-overlay {
      position: absolute;
      inset: 0;
      background: rgba(10,5,2,0.92);
      backdrop-filter: blur(10px);
    }

    .lightbox-content {
      position: relative;
      z-index: 2;
      max-width: 88vw;
      max-height: 88vh;
      border-radius: 18px;
      overflow: hidden;
      box-shadow:
        0 0 0 2px rgba(212,175,55,0.5),
        0 30px 80px rgba(0,0,0,0.6);
      transform: scale(0.7);
      transition: transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .lightbox.active .lightbox-content {
      transform: scale(1);
    }

    .lightbox-content img {
      max-width: 88vw;
      max-height: 88vh;
      display: block;
      object-fit: contain;
    }

    /* shimmer sweep on lightbox open */
    .lightbox-content::after {
      content: '';
      position: absolute;
      top: 0; left: -100%;
      width: 50%; height: 100%;
      background: linear-gradient(120deg, transparent, rgba(255,255,255,0.25), transparent);
      transform: skewX(-15deg);
      pointer-events: none;
    }

    .lightbox.active .lightbox-content::after {
      animation: lightboxShimmer 0.8s forwards 0.2s;
    }

    @keyframes lightboxShimmer {
      0%   { left: -100%; }
      100% { left: 130%; }
    }

    .lightbox-close {
      position: absolute;
      top: 16px;
      right: 18px;
      z-index: 10;
      background: rgba(255,255,255,0.15);
      border: none;
      color: #fff;
      font-size: 1.5em;
      width: 44px;
      height: 44px;
      border-radius: 50%;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      backdrop-filter: blur(6px);
      transition: background 0.3s, transform 0.3s;
    }

    .lightbox-close:hover {
      background: rgba(232,160,191,0.5);
      transform: rotate(90deg) scale(1.1);
    }

    .lightbox-caption {
      position: absolute;
      bottom: 0; left: 0; right: 0;
      text-align: center;
      padding: 24px;
      background: linear-gradient(to top, rgba(10,5,2,0.9), transparent);
      color: #fff;
      z-index: 3;
    }

    .lightbox-caption h3 {
      font-size: 1.1em;
      font-weight: 700;
    }

    .lightbox-caption p {
      font-size: 0.85em;
      color: rgba(255,255,255,0.7);
      margin-top: 4px;
    }

    /* lightbox nav arrows */
    .lightbox-arrow {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      z-index: 10;
      background: rgba(255,255,255,0.1);
      border: none;
      color: #fff;
      font-size: 1.8em;
      width: 52px;
      height: 52px;
      border-radius: 50%;
      cursor: pointer;
      backdrop-filter: blur(6px);
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.3s, transform 0.3s;
    }

    .lightbox-arrow:hover {
      background: rgba(212,175,55,0.4);
      transform: translateY(-50%) scale(1.12);
    }

    .lightbox-arrow.prev { left: 20px; }
    .lightbox-arrow.next { right: 20px; }

    /* ---- RATING SECTION UPGRADE ---- */
    #Rating {
      padding: 80px 40px;
      background: linear-gradient(135deg, #1a0f0a 0%, #2c1a0e 60%, #3a2518 100%);
      position: relative;
      overflow: hidden;
    }

    #Rating::before {
      content: '';
      position: absolute;
      inset: 0;
      background:
        radial-gradient(circle at 15% 50%, rgba(232,160,191,0.18) 0%, transparent 50%),
        radial-gradient(circle at 85% 30%, rgba(212,175,55,0.18) 0%, transparent 50%);
      pointer-events: none;
    }

    #Rating h2 {
      text-align: center;
      color: #fff;
      font-size: clamp(1.8em, 3.5vw, 2.8em);
      font-weight: 800;
      margin-bottom: 50px;
      position: relative;
      z-index: 1;
    }

    #Rating h2::after {
      content: '';
      display: block;
      width: 60px;
      height: 3px;
      background: linear-gradient(90deg, var(--pink), var(--gold));
      margin: 12px auto 0;
      border-radius: 99px;
    }

    #Ulasan {
      max-width: 850px;
      margin: 0 auto;
      position: relative;
      z-index: 1;
      text-align: center;
    }

    #Stars {
      font-size: clamp(3em, 8vw, 5.5em);
      font-weight: 900;
      color: #fff;
      line-height: 1;
      margin-bottom: 8px;
    }

    .rating-stars {
      color: #FFD700;
      font-size: 1.1em;
      letter-spacing: 8px;
      text-shadow:
        0 0 10px rgba(255,215,0,0.9),
        0 0 25px rgba(255,215,0,0.6),
        0 0 45px rgba(255,215,0,0.4);
      display: inline-block;
      animation: starPulse 2.5s ease-in-out infinite;
    }

    @keyframes starPulse {
      0%, 100% { text-shadow: 0 0 10px rgba(255,215,0,0.9), 0 0 25px rgba(255,215,0,0.6); }
      50%       { text-shadow: 0 0 20px rgba(255,215,0,1),   0 0 50px rgba(255,215,0,0.9), 0 0 80px rgba(255,215,0,0.5); }
    }

    #JumlahUlasan {
      color: rgba(255,255,255,0.55);
      font-size: 0.9em;
      margin-bottom: 6px;
    }

    #JamBuka {
      color: rgba(255,255,255,0.65);
      font-size: 0.9em;
      margin-bottom: 50px;
    }

    #Testimoni {
      background: rgba(255,255,255,0.06) !important;
      border: 1px solid rgba(255,255,255,0.1);
      backdrop-filter: blur(10px);
    }

    #Testimoni h3 {
      color: var(--gold);
      text-align: center;
      margin-bottom: 28px;
      font-size: 1.2em;
      letter-spacing: 0.5px;
    }

    .testimonial {
      background: rgba(255,255,255,0.07) !important;
      border: 1px solid rgba(255,255,255,0.08);
      color: rgba(255,255,255,0.88) !important;
      padding: 28px 30px;
      border-radius: 18px;
      font-style: italic;
      margin-bottom: 16px;
      line-height: 1.8;
      position: relative;
      transition: transform 0.3s, box-shadow 0.3s, border-color 0.3s;
    }

    .testimonial::before {
      content: '"';
      position: absolute;
      top: -12px;
      left: 20px;
      font-size: 4em;
      color: var(--gold);
      opacity: 0.35;
      font-style: normal;
      line-height: 1;
    }

    .testimonial:hover {
      transform: translateY(-4px) scale(1.01);
      box-shadow: 0 15px 40px rgba(0,0,0,0.3);
      border-color: rgba(212,175,55,0.3);
    }

    #Ref {
      color: var(--gold);
      text-align: center;
      margin-top: 28px;
      font-size: 0.85em;
      letter-spacing: 2px;
      opacity: 0.75;
    }

    /* ---- DARK MODE overrides ---- */
    body.dark #gallery {
      background: linear-gradient(160deg, #1a1020 0%, #120f1a 50%, #1a1208 100%) !important;
    }

    body.dark .photo-card {
      box-shadow: 0 8px 30px rgba(0,0,0,0.5);
    }

    body.dark .filter-btn {
      background: rgba(40,30,30,0.85);
      color: #eee;
    }

    /* ---- RESPONSIVE ---- */
    @media (max-width: 768px) {
      .gallery-grid { columns: 2 180px; column-gap: 14px; }
      .photo-card   { margin-bottom: 14px; }
      #gallery      { padding: 20px 18px 60px; }
      #Rating       { padding: 60px 20px; }
      .gallery-hero { height: 300px; }
    }

    @media (max-width: 480px) {
      .gallery-grid { columns: 1; }
    }

  </style>
</head>
<body>

<!-- NAVBAR -->
<nav>

  <!-- LEFT -->
  <div class="nav-left">
    <img src="assets/img/Yolazcake.png" alt="YOLAZCAKE Logo">
    <h2>YOLAZCAKE</h2>
  </div>

  <!-- CENTER -->
  <ul class="main-nav">
    <li onclick="window.location.href='index.php'">Home</li>
    <li onclick="window.location.href='produk/menu.php'">Menu</li>
    <li class="active" onclick="window.location.href='gallery.php'">Gallery</li>
    <li onclick="window.location.href='about.php'">About</li>
    <li onclick="window.location.href='contact.php'">Contact</li>
  </ul>

  <!-- RIGHT -->
  <div class="nav-right">

<?php if(isset($_SESSION['username'])){ ?>
<div class="account-dropdown">
<button class="account-btn">
👤 <?php echo htmlspecialchars($_SESSION['username']); ?> ▼
</button>
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

  <!-- DROPDOWN -->
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

<!-- HERO BANNER -->
<div class="gallery-hero">
  <div class="sparkle-wrap" id="sparkleWrap"></div>
  <div class="gallery-hero-content">
    <p class="gallery-hero-eyebrow">✦ Koleksi Visual Kami ✦</p>
    <h1>Galeri YOLAZCAKE</h1>
    <p style="font-family:'Inter',sans-serif;color:rgba(255,255,255,0.75);margin-top:14px;font-size:1em;opacity:0;animation:fadeInDown 0.9s forwards 0.9s;">Setiap sudut menyimpan cerita rasa &amp; keindahan 📸</p>
    <div class="gallery-hero-divider">
      <span></span>
      <span class="hero-diamond">✦ ✦ ✦</span>
      <span></span>
    </div>
  </div>
</div>

<!-- FILTER TABS -->
<div class="gallery-filter" id="galleryFilter">
  <button class="filter-btn active" data-filter="all">Semua</button>
  <button class="filter-btn" data-filter="interior">Interior</button>
  <button class="filter-btn" data-filter="kue">Kue & Pastry</button>
  <button class="filter-btn" data-filter="coffee">Coffee</button>
  <button class="filter-btn" data-filter="boutique">Boutique</button>
</div>

<!-- GALLERY -->
<section id="gallery">

  <h2>Galeri YOLAZCAKE</h2>
  <p class="gallery-subtitle">— Sintang, Kalimantan Barat —</p>

  <div class="gallery-grid" id="galleryGrid">

    <div class="photo-card fade" data-category="interior"
         data-title="Interior Cafe" data-desc="Pemandangan di dalam cafe">
      <img
        src="https://lh3.googleusercontent.com/gps-cs-s/APNQkAFR4IeKh-0U3sMc-EUUfcP8EpwWGFHLIf7yh1xk1yAVAzQ0JU5TQKeePE2otwORHFzeGWxkmtG8CPSOfOQkivphbY7hcKY74l7msyGPoPIjfG99lBCtqSsOzQ3sjyeQB_0P81ohEinkkXXF=s570-k-no"
        alt="Interior Cafe"
        loading="lazy">
      <div class="card-label">
        <h3>Interior Cafe</h3>
        <p>Pemandangan di dalam cafe</p>
      </div>
    </div>

    <div class="photo-card fade" data-category="kue"
         data-title="Display Kue" data-desc="Pameran kue yang baru keluar dari oven">
      <img
        src="https://lh3.googleusercontent.com/gps-cs-s/APNQkAGULyO-bH8yxvqm3EfE71B39vSe7V8Xb8fBIp9IU_1aVfMUeGgS_v9NzNukQD8zM2b1IDyQq-_H52pdhLgq1sbiB6q0J2t0nUL2ATxZVdSensb_6GeBlNZ_Bw0beZsouVCmYf7-ip9jX2o=w203-h270-k-no"
        alt="Display Kue"
        loading="lazy">
      <div class="card-label">
        <h3>Display Kue</h3>
        <p>Pameran kue yang baru keluar dari oven</p>
      </div>
    </div>

    <div class="photo-card fade" data-category="coffee"
         data-title="Coffee Corner" data-desc="Tempat pembuatan coffee">
      <img
        src="https://lh3.googleusercontent.com/p/AF1QipNmBvfaHIm0maTFr1WgBzWfszGuZ9CtDE2JHUjg=w203-h270-k-no"
        alt="Coffee Corner"
        loading="lazy">
      <div class="card-label">
        <h3>Coffee Corner</h3>
        <p>Tempat pembuatan coffee</p>
      </div>
    </div>

    <div class="photo-card fade" data-category="boutique"
         data-title="Boutique Lantai 2" data-desc="Pakaian wanita modern & aesthetic">
      <img
        src="https://lh3.googleusercontent.com/gps-cs-s/APNQkAFyRTFJVt37RS1l6KvoZhmJdSlG8i-WzMMZAB4PxtxURDK3gl9hqcKBOT0FzNdhkIUVQU8RWZX4fI5zi_bdI5Xp0di4w9kdSVzn3panNK2_2AW2SZ7O7YF6INyWaDusZAefHgpe0WppNMPw=s846-k-no"
        alt="Boutique Lantai 2"
        loading="lazy">
      <div class="card-label">
        <h3>Boutique Lantai 2</h3>
        <p>Pakaian wanita modern & aesthetic</p>
      </div>
    </div>

  </div>

</section>

<!-- RATING -->
<section id="Rating">

  <h2>Rating & Testimoni</h2>

  <div id="Ulasan">

    <h3 id="Stars">
      4,8 <span class="rating-stars">★★★★★</span>
    </h3>

    <p id="JumlahUlasan">(26 ulasan)</p>

    <p><strong style="color:var(--gold)">Toko Roti • Rp 25.000 – 50.000</strong></p>

    <p id="JamBuka">Buka setiap hari • Tutup pukul 22.00 WIB</p>

    <div class="card fade" id="Testimoni">

      <h3>Apa kata pelanggan kami?</h3>

      <div class="testimonial fade">
        "Kuenya mantap banget, harganya murah meriah, dan matcha-nya enak!"
      </div>

      <div class="testimonial fade">
        "Tempatnya cozy banget! Kuenya enak, kopinya mantap, dan bisa langsung belanja baju di atas. Recomended!"
      </div>

      <div class="testimonial fade">
        "Dessertnya selalu fresh. Boutique-nya juga aesthetic, cocok buat cewek-cewek yang suka foto-foto."
      </div>

      <div class="testimonial fade">
        "Satu-satunya tempat di Sintang yang bisa makan enak sambil belanja fashion. Pelayanannya ramah!"
      </div>

      <p id="Ref">— Dari Google Review</p>

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

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox">
  <div class="lightbox-overlay" onclick="closeLightbox()"></div>
  <button class="lightbox-arrow prev" onclick="prevPhoto()">&#8592;</button>
  <div class="lightbox-content">
    <img id="lightboxImg" src="" alt="">
    <div class="lightbox-caption">
      <h3 id="lightboxTitle"></h3>
      <p id="lightboxDesc"></p>
    </div>
  </div>
  <button class="lightbox-arrow next" onclick="nextPhoto()">&#8594;</button>
  <button class="lightbox-close" onclick="closeLightbox()">✕</button>
</div>

<script src="js/style.js"></script>
<script>
  /* ---- SPARKLES IN HERO ---- */
  (function(){
    const wrap = document.getElementById('sparkleWrap');
    for(let i = 0; i < 22; i++){
      const s = document.createElement('div');
      s.className = 'sparkle';
      const size = Math.random() * 6 + 3;
      s.style.cssText = `
        width:${size}px; height:${size}px;
        top:${Math.random()*100}%;
        left:${Math.random()*100}%;
        animation-delay:${Math.random()*4}s;
        animation-duration:${2 + Math.random()*3}s;
      `;
      wrap.appendChild(s);
    }
  })();

  /* ---- FILTER TABS ---- */
  const filterBtns = document.querySelectorAll('.filter-btn');
  const photoCards = document.querySelectorAll('.photo-card');

  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const filter = btn.dataset.filter;
      photoCards.forEach(card => {
        const show = filter === 'all' || card.dataset.category === filter;
        card.style.transition = 'opacity 0.4s, transform 0.4s';
        if(show){
          card.style.opacity = '1';
          card.style.transform = 'scale(1)';
          card.style.pointerEvents = '';
        } else {
          card.style.opacity = '0';
          card.style.transform = 'scale(0.9)';
          card.style.pointerEvents = 'none';
        }
      });
    });
  });

  /* ---- LIGHTBOX ---- */
  let currentIndex = 0;
  let visibleCards = [];

  function getVisibleCards(){
    return Array.from(photoCards).filter(c => c.style.pointerEvents !== 'none');
  }

  function openLightbox(card){
    visibleCards = getVisibleCards();
    currentIndex = visibleCards.indexOf(card);
    showPhoto(currentIndex);
    document.getElementById('lightbox').classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function showPhoto(idx){
    const card = visibleCards[idx];
    if(!card) return;
    const img   = card.querySelector('img');
    const title = card.dataset.title;
    const desc  = card.dataset.desc;
    document.getElementById('lightboxImg').src   = img.src;
    document.getElementById('lightboxTitle').textContent = title;
    document.getElementById('lightboxDesc').textContent  = desc;
    currentIndex = idx;
  }

  function closeLightbox(){
    document.getElementById('lightbox').classList.remove('active');
    document.body.style.overflow = '';
  }

  function prevPhoto(){
    const newIdx = (currentIndex - 1 + visibleCards.length) % visibleCards.length;
    showPhoto(newIdx);
  }

  function nextPhoto(){
    const newIdx = (currentIndex + 1) % visibleCards.length;
    showPhoto(newIdx);
  }

  photoCards.forEach(card => {
    card.addEventListener('click', () => openLightbox(card));
  });

  /* keyboard navigation */
  document.addEventListener('keydown', e => {
    if(!document.getElementById('lightbox').classList.contains('active')) return;
    if(e.key === 'Escape')     closeLightbox();
    if(e.key === 'ArrowLeft')  prevPhoto();
    if(e.key === 'ArrowRight') nextPhoto();
  });

  /* ---- INTERSECTION OBSERVER (reuse style.js .fade → .show) ---- */
  /* style.js already handles .fade → .show; photo-card uses the same class */

</script>

</body>
</html>
