<?php
session_start();
include '../config/koneksi.php';

// Query produk dengan JOIN ke kategori
$query = mysqli_query($conn,
    "SELECT p.*, COALESCE(k.nama_kategori, 'Lainnya') as nama_kategori, k.icon as kategori_icon
     FROM produk p
     LEFT JOIN kategori k ON k.id_kategori = p.id_kategori
     ORDER BY k.nama_kategori ASC, p.nama_produk ASC");

if(!$query){ die(mysqli_error($conn)); }

// Ambil semua produk ke array
$produk_list = [];
while ($row = mysqli_fetch_assoc($query)) {
    $produk_list[] = $row;
}

// Ambil daftar kategori untuk filter
$kat_query = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");

// Hitung total item di keranjang
$total_keranjang = 0;
if(!empty($_SESSION['keranjang'])){
  foreach($_SESSION['keranjang'] as $jml){ $total_keranjang += $jml; }
}

// Filter aktif
$filter_kat = isset($_GET['kat']) ? (int)$_GET['kat'] : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menu Pemesanan – YOLAZCAKE</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* ══════════════════════════════════════════
       RESET & BASE
    ══════════════════════════════════════════ */
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}

    :root {
      --gold:        #D4AF37;
      --gold-light:  #FFE87C;
      --gold-dark:   #b8860b;
      --gold-pale:   #fff3c4;
      --pink:        #ee2a7b;
      --pink-dark:   #c0175a;
      --mint:        #6efabc;
      --purple-deep: #1e0e3a;
      --purple-mid:  #2d1560;
      --purple-dark: #1a0a2e;
      --glass-bg:    rgba(255,255,255,.065);
      --glass-brd:   rgba(255,255,255,.11);
      --gold-glow:   rgba(212,175,55,.28);
    }

    html { scroll-behavior:smooth; }

    body {
      min-height:100vh;
      font-family:'Inter',sans-serif;
      background:linear-gradient(160deg,var(--purple-deep) 0%,var(--purple-mid) 50%,var(--purple-dark) 100%);
      position:relative; overflow-x:hidden; color:#fff;
    }

    /* Ambient triple-gradient overlay */
    body::before {
      content:''; position:fixed; inset:0; pointer-events:none; z-index:0;
      background:
        radial-gradient(ellipse at 15% 20%, rgba(212,175,55,.13) 0%, transparent 52%),
        radial-gradient(ellipse at 85% 75%, rgba(232,160,191,.13) 0%, transparent 52%),
        radial-gradient(ellipse at 50% 100%, rgba(99,250,180,.07) 0%, transparent 40%);
    }

    /* ══════════════════════════════════════════
       KEYFRAMES
    ══════════════════════════════════════════ */
    @keyframes goldSlide      { 0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%} }
    @keyframes shimmerText    { 0%{background-position:200% center} 100%{background-position:-200% center} }
    @keyframes fadeSlideDown  { from{opacity:0;transform:translateY(-22px)} to{opacity:1;transform:translateY(0)} }
    @keyframes fadeSlideUp    { from{opacity:0;transform:translateY(32px)}  to{opacity:1;transform:translateY(0)} }
    @keyframes fadeIn         { from{opacity:0} to{opacity:1} }
    @keyframes heroAurora     { 0%{opacity:.55;transform:scale(1)} 100%{opacity:1;transform:scale(1.1) translateX(14px)} }
    @keyframes floatDot       { 0%{transform:translateY(0) rotate(0deg);opacity:0} 20%{opacity:1} 80%{opacity:.7} 100%{transform:translateY(-300px) rotate(360deg);opacity:0} }
    @keyframes particleFloat  { 0%{transform:translateY(100vh) scale(0);opacity:0} 10%{opacity:.45} 90%{opacity:.25} 100%{transform:translateY(-120px) scale(1);opacity:0} }
    @keyframes pulseBadge     { 0%,100%{box-shadow:0 0 0 0 rgba(238,42,123,.55)} 50%{box-shadow:0 0 0 9px rgba(238,42,123,0)} }
    @keyframes pulseGold      { 0%,100%{box-shadow:0 0 0 0 rgba(212,175,55,.45)} 50%{box-shadow:0 0 0 9px rgba(212,175,55,0)} }
    @keyframes orbPulse       { 0%,100%{transform:scale(1);opacity:.45} 50%{transform:scale(1.35);opacity:.75} }
    @keyframes filterGlow     { 0%,100%{box-shadow:0 0 14px var(--gold-glow)} 50%{box-shadow:0 0 32px rgba(212,175,55,.6),0 0 60px rgba(212,175,55,.22)} }
    @keyframes crownPulse     { 0%,100%{text-shadow:0 0 6px var(--gold),0 0 14px rgba(212,175,55,.4)} 50%{text-shadow:0 0 20px var(--gold),0 0 45px rgba(212,175,55,.75),0 0 80px rgba(212,175,55,.3)} }
    @keyframes glowLine       { 0%,100%{opacity:.5;transform:scaleX(.8)} 50%{opacity:1;transform:scaleX(1)} }
    @keyframes toastIn        { from{transform:translateX(130%)} to{transform:translateX(0)} }
    @keyframes toastOut       { from{transform:translateX(0)} to{transform:translateX(130%)} }
    @keyframes borderRainbow  { 0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%} }
    @keyframes cardReveal     { from{opacity:0;transform:translateY(50px) scale(.96)} to{opacity:1;transform:translateY(0) scale(1)} }
    @keyframes floatBob       { 0%,100%{transform:translateY(0) rotate(0deg)} 50%{transform:translateY(-6px) rotate(3deg)} }
    @keyframes priceShimmer   { 0%{background-position:0% 50%} 100%{background-position:200% 50%} }
    @keyframes ribbonSlide    { 0%{transform:translateX(-100%) skewX(-15deg)} 100%{transform:translateX(300%) skewX(-15deg)} }
    @keyframes searchGlow     { 0%,100%{box-shadow:0 0 0 0 rgba(212,175,55,0)} 50%{box-shadow:0 0 0 4px rgba(212,175,55,.22)} }
    @keyframes countUp        { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
    @keyframes liveBlip       { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.6;transform:scale(.8)} }
    @keyframes rotateHalo     { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }

    /* ══════════════════════════════════════════
       PARTICLES
    ══════════════════════════════════════════ */
    #particles { position:fixed; inset:0; z-index:0; pointer-events:none; }
    .particle  { position:absolute; border-radius:50%; pointer-events:none; animation:particleFloat linear infinite; }

    /* ══════════════════════════════════════════
       HERO
    ══════════════════════════════════════════ */
    .page-hero {
      position:relative; height:300px;
      display:flex; flex-direction:column; align-items:center; justify-content:center;
      overflow:hidden; z-index:1;
      background:linear-gradient(135deg,#2b1a11 0%,#4a2c1a 40%,#6d3e26 70%,#3a1f0e 100%);
    }

    /* Aurora shimmer */
    .page-hero::before {
      content:''; position:absolute; inset:0; pointer-events:none;
      background:
        radial-gradient(ellipse at 28% 55%, rgba(212,175,55,.24) 0%, transparent 58%),
        radial-gradient(ellipse at 78% 35%, rgba(232,160,191,.20) 0%, transparent 55%),
        radial-gradient(ellipse at 55% 85%, rgba(99,250,180,.11) 0%, transparent 45%);
      animation:heroAurora 9s ease-in-out infinite alternate;
    }

    /* Bottom fade into page */
    .page-hero::after {
      content:''; position:absolute; bottom:0; left:0; right:0; height:80px; pointer-events:none;
      background:linear-gradient(to bottom, transparent, var(--purple-deep));
    }

    /* Decorative orbs */
    .hero-orb {
      position:absolute; border-radius:50%; pointer-events:none;
      background:radial-gradient(circle, rgba(212,175,55,.22), transparent 70%);
      animation:orbPulse ease-in-out infinite;
    }

    .hero-inner { position:relative; z-index:2; text-align:center; padding:0 20px; }

    .hero-eyebrow {
      font-size:.72em; font-weight:600; letter-spacing:6px; text-transform:uppercase;
      color:var(--gold); margin-bottom:14px;
      animation:crownPulse 3s ease-in-out infinite, fadeSlideDown .8s forwards .3s;
      opacity:0;
    }

    .hero-inner h1 {
      font-family:'Playfair Display',serif; font-size:3.3em; font-weight:900;
      background:linear-gradient(270deg,#fff 0%,var(--gold-light) 25%,var(--gold) 50%,#fff 75%,var(--gold-light) 100%);
      background-size:300% 100%;
      -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
      animation:shimmerText 5s linear infinite, fadeSlideDown .9s forwards .5s;
      opacity:0; line-height:1.1; letter-spacing:-1px;
    }

    .hero-sub {
      font-size:.89em; color:rgba(255,255,255,.62); margin-top:13px;
      opacity:0; animation:fadeSlideDown .9s forwards .9s; letter-spacing:.5px;
    }

    .hero-divider {
      margin-top:20px; display:flex; justify-content:center; align-items:center; gap:14px;
      opacity:0; animation:fadeSlideDown .9s forwards 1.1s;
    }
    .hero-divider .line {
      display:block; width:75px; height:1px;
      background:linear-gradient(to right, transparent, var(--gold));
      animation:glowLine 3s ease-in-out infinite;
    }
    .hero-divider .line:last-child { background:linear-gradient(to left, transparent, var(--gold)); }
    .hero-divider .diamond { color:var(--gold); font-size:.75em; letter-spacing:5px; }

    /* Sparkles inside hero */
    .sparkle { position:absolute; border-radius:50%; pointer-events:none; animation:floatDot linear infinite; }

    /* ══════════════════════════════════════════
       BACK LINK
    ══════════════════════════════════════════ */
    .back-link {
      position:relative; z-index:2;
      display:flex; align-items:center;
      padding:26px 32px 0; max-width:1280px; width:100%; margin:0 auto;
    }
    .back-link a {
      font-size:.77em; font-weight:600; letter-spacing:2px; text-transform:uppercase;
      color:rgba(212,175,55,.8); text-decoration:none;
      border:1px solid rgba(212,175,55,.3); padding:8px 20px; border-radius:999px;
      transition:all .3s; background:rgba(212,175,55,.06);
      display:inline-flex; align-items:center; gap:6px;
    }
    .back-link a:hover {
      background:rgba(212,175,55,.16); border-color:rgba(212,175,55,.7);
      box-shadow:0 0 24px rgba(212,175,55,.3); color:var(--gold);
      transform:translateX(-4px);
    }

    /* ══════════════════════════════════════════
       PAGE WRAPPER
    ══════════════════════════════════════════ */
    .page-wrapper {
      position:relative; z-index:1;
      display:flex; flex-direction:column; align-items:center;
      padding:28px 24px 140px;
      max-width:1280px; margin:0 auto;
    }

    /* ══════════════════════════════════════════
       TOP BAR
    ══════════════════════════════════════════ */
    .top-bar {
      width:100%; display:flex; align-items:center; justify-content:space-between;
      margin-bottom:28px; flex-wrap:wrap; gap:14px;
    }
    .section-label {
      font-size:.67em; font-weight:600; letter-spacing:4.5px; text-transform:uppercase;
      color:var(--gold); margin-bottom:6px; opacity:.85;
    }
    .section-title {
      font-family:'Playfair Display',serif; font-size:1.85em; font-weight:700;
      background:linear-gradient(135deg,#fff 30%,var(--gold) 70%);
      -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
    }

    /* Live badge */
    .badge-live {
      display:inline-flex; align-items:center; gap:8px;
      background:linear-gradient(135deg,rgba(99,250,180,.15),rgba(99,250,180,.06));
      border:1px solid rgba(99,250,180,.38); color:var(--mint);
      font-size:.76em; font-weight:600; letter-spacing:1.5px;
      padding:7px 18px; border-radius:999px;
    }
    .badge-live .blip {
      width:7px; height:7px; border-radius:50%; background:var(--mint);
      box-shadow:0 0 0 3px rgba(99,250,180,.3);
      animation:liveBlip 1.8s ease-in-out infinite;
    }

    /* ══════════════════════════════════════════
       STATS BAR  (new)
    ══════════════════════════════════════════ */
    .stats-bar {
      width:100%; display:flex; gap:16px; flex-wrap:wrap; margin-bottom:28px;
      opacity:0; animation:fadeSlideUp .7s forwards .3s;
    }
    .stat-pill {
      flex:1; min-width:140px;
      background:rgba(255,255,255,.055);
      border:1px solid rgba(255,255,255,.09);
      border-radius:16px; padding:14px 20px;
      display:flex; align-items:center; gap:12px;
      backdrop-filter:blur(14px);
      transition:border-color .3s, box-shadow .3s;
    }
    .stat-pill:hover {
      border-color:rgba(212,175,55,.35);
      box-shadow:0 8px 28px rgba(212,175,55,.12);
    }
    .stat-icon { font-size:1.6em; flex-shrink:0; }
    .stat-val  { font-family:'Playfair Display',serif; font-size:1.4em; font-weight:700; color:var(--gold); line-height:1; }
    .stat-lbl  { font-size:.72em; color:rgba(255,255,255,.45); letter-spacing:1px; margin-top:3px; }

    /* ══════════════════════════════════════════
       CART SUMMARY BAR
    ══════════════════════════════════════════ */
    .cart-bar {
      width:100%;
      background:linear-gradient(135deg,rgba(212,175,55,.11),rgba(212,175,55,.04));
      border:1px solid rgba(212,175,55,.3); border-radius:18px;
      padding:16px 26px;
      display:flex; align-items:center; justify-content:space-between;
      flex-wrap:wrap; gap:12px; margin-bottom:28px;
      box-shadow:0 0 40px rgba(212,175,55,.12), inset 0 1px 0 rgba(212,175,55,.18);
      opacity:0; animation:fadeSlideUp .7s forwards .5s;
      position:relative; overflow:hidden;
    }
    /* Ribbon shimmer on cart bar */
    .cart-bar::before {
      content:''; position:absolute; top:0; left:0; width:60px; height:100%;
      background:linear-gradient(105deg,transparent,rgba(255,255,255,.06),transparent);
      animation:ribbonSlide 3s ease-in-out infinite;
    }
    .cart-bar-info { font-size:.87em; color:rgba(255,255,255,.7); }
    .cart-bar-info strong { color:var(--gold); }

    /* ══════════════════════════════════════════
       SEARCH BAR  (new)
    ══════════════════════════════════════════ */
    .search-wrap {
      width:100%; margin-bottom:28px; position:relative;
      opacity:0; animation:fadeSlideUp .7s forwards .35s;
    }
    .search-wrap .search-icon {
      position:absolute; left:18px; top:50%; transform:translateY(-50%);
      font-size:1.05em; pointer-events:none; color:rgba(212,175,55,.6);
    }
    .search-input {
      width:100%; padding:14px 20px 14px 50px;
      background:rgba(255,255,255,.065); backdrop-filter:blur(18px);
      border:1px solid rgba(255,255,255,.12); border-radius:16px;
      color:#fff; font-family:'Inter',sans-serif; font-size:.9em; font-weight:500;
      outline:none; transition:border-color .3s, box-shadow .3s;
    }
    .search-input::placeholder { color:rgba(255,255,255,.3); }
    .search-input:focus {
      border-color:rgba(212,175,55,.55);
      box-shadow:0 0 0 3px rgba(212,175,55,.15), 0 8px 28px rgba(0,0,0,.25);
      animation:searchGlow 2s ease-in-out infinite;
    }
    .search-clear {
      position:absolute; right:16px; top:50%; transform:translateY(-50%);
      background:rgba(255,255,255,.12); border:none; border-radius:50%;
      width:28px; height:28px; cursor:pointer; color:rgba(255,255,255,.6);
      font-size:.85em; display:none; align-items:center; justify-content:center;
      transition:background .2s;
    }
    .search-clear:hover { background:rgba(255,80,80,.25); color:#ff6060; }
    .search-clear.visible { display:flex; }

    /* ══════════════════════════════════════════
       CATEGORY FILTER BAR
    ══════════════════════════════════════════ */
    .filter-section {
      width:100%; margin-bottom:36px;
      opacity:0; animation:fadeSlideUp .7s forwards .4s;
    }
    .filter-label {
      font-size:.67em; font-weight:600; letter-spacing:4px; text-transform:uppercase;
      color:rgba(212,175,55,.65); margin-bottom:16px; text-align:center;
    }
    .filter-track {
      display:flex; align-items:center; justify-content:center;
      flex-wrap:wrap; gap:10px;
    }

    /* Filter pill */
    .filter-pill {
      position:relative; display:inline-flex; align-items:center; gap:7px;
      padding:10px 22px; border-radius:999px; cursor:pointer;
      font-family:'Inter',sans-serif; font-size:.79em; font-weight:600;
      letter-spacing:1.5px; text-transform:uppercase; text-decoration:none;
      border:1px solid rgba(255,255,255,.12);
      background:rgba(255,255,255,.055); color:rgba(255,255,255,.65);
      backdrop-filter:blur(14px);
      transition:all .32s cubic-bezier(.34,1.56,.64,1);
      overflow:hidden;
    }
    .filter-pill::before {
      content:''; position:absolute; inset:0;
      background:linear-gradient(90deg,transparent,rgba(255,255,255,.1),transparent);
      transform:translateX(-100%); transition:transform .55s; border-radius:999px;
    }
    .filter-pill:hover::before { transform:translateX(100%); }
    .filter-pill:hover {
      border-color:rgba(212,175,55,.5); background:rgba(212,175,55,.12); color:#fff;
      transform:translateY(-3px) scale(1.04);
      box-shadow:0 8px 30px rgba(212,175,55,.22), 0 0 44px rgba(212,175,55,.1);
    }
    .filter-pill.active {
      background:linear-gradient(135deg,var(--gold) 0%,var(--gold-dark) 50%,var(--gold) 100%);
      background-size:200% 100%;
      border-color:var(--gold); color:var(--purple-deep); font-weight:700;
      animation:goldSlide 3s ease infinite, filterGlow 2.5s ease-in-out infinite;
      box-shadow:0 8px 34px rgba(212,175,55,.48), 0 0 58px rgba(212,175,55,.22), inset 0 1px 0 rgba(255,255,255,.28);
      transform:translateY(-2px);
    }
    .filter-pill .pill-icon { font-size:1.1em; line-height:1; }
    .pill-count {
      background:rgba(0,0,0,.25); border-radius:999px;
      padding:1px 8px; font-size:.74em; font-weight:700;
      min-width:22px; text-align:center;
    }
    .filter-pill.active .pill-count { background:rgba(30,14,58,.3); }

    /* ══════════════════════════════════════════
       GOLD RULE
    ══════════════════════════════════════════ */
    .gold-rule {
      display:flex; align-items:center; gap:14px; width:100%; margin-bottom:32px;
    }
    .gold-rule::before,.gold-rule::after {
      content:''; flex:1; height:1px;
      background:linear-gradient(to right,transparent,rgba(212,175,55,.48));
    }
    .gold-rule::after { background:linear-gradient(to left,transparent,rgba(212,175,55,.48)); }
    .gold-rule span { color:var(--gold); font-size:.65em; letter-spacing:4px; white-space:nowrap; }

    /* ══════════════════════════════════════════
       ACTIVE CATEGORY HEADER
    ══════════════════════════════════════════ */
    .cat-header {
      width:100%; display:flex; align-items:center; gap:16px;
      margin-bottom:24px; padding:18px 26px;
      background:linear-gradient(135deg,rgba(212,175,55,.11),rgba(212,175,55,.04));
      border:1px solid rgba(212,175,55,.24); border-radius:18px;
      opacity:0; animation:fadeSlideUp .5s forwards;
      position:relative; overflow:hidden;
    }
    .cat-header::after {
      content:''; position:absolute; inset:0;
      background:linear-gradient(90deg,transparent,rgba(212,175,55,.04),transparent);
      animation:ribbonSlide 4s ease-in-out infinite;
    }
    .cat-header-icon { font-size:2em; flex-shrink:0; filter:drop-shadow(0 0 10px rgba(212,175,55,.5)); }
    .cat-header-text h3 {
      font-family:'Playfair Display',serif; font-size:1.15em; font-weight:700; color:#fff;
    }
    .cat-header-text p { font-size:.78em; color:rgba(255,255,255,.45); margin-top:3px; }

    /* Results info */
    .results-info {
      width:100%; margin-bottom:20px;
      font-size:.78em; color:rgba(255,255,255,.38);
      letter-spacing:1px; display:flex; align-items:center; justify-content:space-between;
    }
    .results-info strong { color:rgba(212,175,55,.8); }
    .results-info #no-results-msg { color:rgba(255,130,130,.7); font-size:.9em; display:none; }

    /* ══════════════════════════════════════════
       PRODUCT GRID
    ══════════════════════════════════════════ */
    .product-grid {
      width:100%; display:grid;
      grid-template-columns:repeat(3,1fr);
      gap:28px;
    }

    /* ══════════════════════════════════════════
       PRODUCT CARD
    ══════════════════════════════════════════ */
    .prod-card {
      background:var(--glass-bg); backdrop-filter:blur(22px);
      border:1px solid var(--glass-brd); border-radius:26px;
      position:relative; overflow:hidden;
      display:flex; flex-direction:column;
      opacity:0; transform:translateY(50px) scale(.96);
      transition:border-color .45s, box-shadow .45s, transform .38s cubic-bezier(.34,1.56,.64,1);
    }

    /* Animated rainbow top bar */
    .prod-card::before {
      content:''; position:absolute; top:0; left:0; right:0; height:3px; z-index:3;
      background:linear-gradient(90deg,var(--gold),var(--pink),var(--gold-light),var(--mint),var(--gold));
      background-size:400% 100%;
      animation:borderRainbow 5s linear infinite;
    }

    /* Corner glow on hover */
    .prod-card::after {
      content:''; position:absolute; inset:0; border-radius:26px; pointer-events:none; z-index:1;
      background:radial-gradient(ellipse at 0% 0%,rgba(212,175,55,.1),transparent 65%);
      opacity:0; transition:opacity .4s;
    }
    .prod-card:hover::after { opacity:1; }

    .prod-card.visible {
      opacity:1; transform:translateY(0) scale(1);
    }
    .prod-card.hidden-by-search {
      display:none;
    }
    .prod-card:hover {
      border-color:rgba(212,175,55,.5);
      box-shadow:
        0 24px 65px rgba(0,0,0,.4),
        0 0 35px rgba(212,175,55,.3),
        0 0 75px rgba(212,175,55,.15),
        0 0 120px rgba(212,175,55,.06);
      transform:translateY(-8px) scale(1.015);
    }

    /* ── CARD IMAGE ── */
    .card-img-wrap {
      position:relative; overflow:hidden; height:215px; flex-shrink:0;
    }
    .card-img-wrap img {
      width:100%; height:100%; object-fit:cover;
      transition:transform .58s cubic-bezier(.25,.46,.45,.94), filter .4s;
    }
    .prod-card:hover .card-img-wrap img {
      transform:scale(1.11);
      filter:brightness(1.08) saturate(1.1);
    }
    .card-img-overlay {
      position:absolute; inset:0;
      background:linear-gradient(to top,rgba(30,14,58,.92) 0%,rgba(30,14,58,.18) 50%,transparent 100%);
    }

    /* Category tag inside image */
    .card-cat-tag {
      position:absolute; bottom:12px; left:12px; z-index:2;
      display:inline-flex; align-items:center; gap:5px;
      background:rgba(20,10,45,.75); backdrop-filter:blur(12px);
      border:1px solid rgba(255,255,255,.13);
      color:rgba(255,255,255,.78); font-size:.68em; font-weight:600;
      letter-spacing:1.5px; text-transform:uppercase;
      padding:4px 12px; border-radius:999px;
    }

    /* Stock badge */
    .stok-badge {
      position:absolute; top:12px; right:12px; z-index:2;
      display:inline-block; padding:5px 14px; border-radius:999px;
      font-size:.7em; font-weight:700; letter-spacing:1px;
      backdrop-filter:blur(12px); text-transform:uppercase;
    }
    .stok-ok    { background:rgba(99,250,180,.2);  border:1px solid rgba(99,250,180,.5);  color:#6efabc; }
    .stok-low   { background:rgba(255,180,50,.2);  border:1px solid rgba(255,180,50,.5);  color:#ffb432; }
    .stok-habis { background:rgba(255,80,80,.2);   border:1px solid rgba(255,80,80,.5);   color:#ff6060; }

    /* NEW badge ribbon (opsional, bisa ditambah via PHP) */
    .new-ribbon {
      position:absolute; top:18px; left:-24px; z-index:4;
      background:linear-gradient(135deg,var(--pink),var(--pink-dark));
      color:#fff; font-size:.62em; font-weight:800; letter-spacing:2px;
      padding:5px 32px; transform:rotate(-45deg);
      box-shadow:0 4px 14px rgba(238,42,123,.4);
    }

    /* ── CARD BODY ── */
    .card-body {
      padding:20px 22px 22px;
      display:flex; flex-direction:column; flex:1; gap:0; position:relative; z-index:2;
    }

    /* Prod name */
    .prod-name {
      font-family:'Playfair Display',serif;
      font-size:1.07em; font-weight:700; color:#fff; line-height:1.3; margin-bottom:6px;
      transition:color .3s;
    }
    .prod-card:hover .prod-name { color:var(--gold-pale); }

    /* Price with shimmer */
    .prod-price {
      font-size:1.18em; font-weight:700; letter-spacing:.5px;
      background:linear-gradient(90deg,var(--gold),var(--gold-light),var(--gold));
      background-size:200% 100%;
      -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
      animation:priceShimmer 4s linear infinite;
      margin-bottom:16px;
    }

    .card-divider {
      height:1px; margin-bottom:16px;
      background:linear-gradient(to right,transparent,rgba(212,175,55,.4),transparent);
    }

    /* ── QTY CONTROL ── */
    .card-action { display:flex; align-items:center; gap:10px; margin-top:auto; }

    .qty-wrap {
      display:flex; align-items:center;
      background:rgba(255,255,255,.065);
      border:1px solid rgba(212,175,55,.3); border-radius:12px; overflow:hidden; flex-shrink:0;
    }
    .qty-btn {
      width:36px; height:40px; border:none; background:transparent;
      color:var(--gold); font-size:1.15em; cursor:pointer;
      display:flex; align-items:center; justify-content:center;
      transition:background .2s, color .2s; user-select:none;
    }
    .qty-btn:hover { background:rgba(212,175,55,.2); color:var(--gold-light); }
    .qty-input {
      width:42px; height:40px; border:none; background:transparent;
      color:#fff; font-size:.9em; font-weight:700; font-family:'Inter',sans-serif;
      text-align:center; outline:none; -moz-appearance:textfield;
    }
    .qty-input::-webkit-outer-spin-button,
    .qty-input::-webkit-inner-spin-button { -webkit-appearance:none; margin:0; }

    /* ── ADD-TO-CART BTN ── */
    .btn-cart {
      flex:1; position:relative; padding:11px 14px; border:none; border-radius:12px;
      font-family:'Inter',sans-serif; font-size:.78em; font-weight:700;
      letter-spacing:1.5px; text-transform:uppercase; cursor:pointer;
      overflow:hidden; transition:transform .28s cubic-bezier(.34,1.56,.64,1), box-shadow .35s;
      display:flex; align-items:center; justify-content:center; gap:7px;
    }
    .btn-cart::before {
      content:''; position:absolute; inset:0;
      background:linear-gradient(90deg,transparent,rgba(255,255,255,.22),transparent);
      transform:translateX(-100%); transition:transform .5s;
    }
    .btn-cart:hover::before { transform:translateX(100%); }
    .btn-cart:hover { transform:translateY(-2px); }

    .btn-cart-gold {
      background:linear-gradient(135deg,var(--gold) 0%,var(--gold-dark) 50%,var(--gold) 100%);
      background-size:200% 100%; color:var(--purple-deep);
      animation:goldSlide 3s linear infinite;
      box-shadow:0 6px 24px rgba(212,175,55,.34);
    }
    .btn-cart-gold:hover { box-shadow:0 12px 40px rgba(212,175,55,.58); }

    .btn-cart-disabled {
      background:rgba(255,255,255,.05); border:1px solid rgba(255,80,80,.3);
      color:rgba(255,80,80,.55); cursor:not-allowed;
    }

    /* ══════════════════════════════════════════
       GLOBAL PREMIUM BUTTONS
    ══════════════════════════════════════════ */
    .btn-premium {
      position:relative; padding:13px 28px; border:none; border-radius:14px;
      font-family:'Inter',sans-serif; font-size:.84em; font-weight:700;
      letter-spacing:2px; text-transform:uppercase; cursor:pointer;
      overflow:hidden; transition:transform .28s cubic-bezier(.34,1.56,.64,1), box-shadow .35s;
      text-decoration:none; display:inline-flex; align-items:center; gap:8px;
    }
    .btn-premium::before {
      content:''; position:absolute; inset:0;
      background:linear-gradient(90deg,transparent,rgba(255,255,255,.16),transparent);
      transform:translateX(-100%); transition:transform .55s;
    }
    .btn-premium:hover::before { transform:translateX(100%); }
    .btn-premium:hover { transform:translateY(-3px) scale(1.02); }

    .btn-gold {
      background:linear-gradient(135deg,var(--gold) 0%,var(--gold-dark) 50%,var(--gold) 100%);
      background-size:200% 100%; color:var(--purple-deep);
      animation:goldSlide 3s linear infinite;
      box-shadow:0 8px 32px rgba(212,175,55,.4), 0 0 50px rgba(212,175,55,.2);
    }
    .btn-gold:hover { box-shadow:0 14px 48px rgba(212,175,55,.65), 0 0 70px rgba(212,175,55,.32); }

    .btn-green {
      background:linear-gradient(135deg,rgba(99,250,180,.22),rgba(99,250,180,.1));
      border:1px solid rgba(99,250,180,.44); color:var(--mint);
    }
    .btn-green:hover {
      background:linear-gradient(135deg,rgba(99,250,180,.35),rgba(99,250,180,.18));
      box-shadow:0 8px 30px rgba(99,250,180,.24);
    }

    /* ══════════════════════════════════════════
       TOAST NOTIFICATION
    ══════════════════════════════════════════ */
    .toast {
      position:fixed; bottom:115px; right:28px; z-index:1001;
      background:rgba(22,10,48,.97); backdrop-filter:blur(26px);
      border:1px solid rgba(212,175,55,.45); border-radius:18px;
      padding:16px 26px;
      display:flex; align-items:center; gap:14px;
      box-shadow:0 16px 55px rgba(0,0,0,.5), 0 0 35px rgba(212,175,55,.25);
      transform:translateX(150%); pointer-events:none; max-width:300px;
    }
    .toast.show    { animation:toastIn  .45s cubic-bezier(.34,1.56,.64,1) forwards; pointer-events:auto; }
    .toast.hide    { animation:toastOut .35s ease-in forwards; }
    .toast-icon    { font-size:1.5em; animation:floatBob 2s ease-in-out infinite; }
    .toast-text strong { display:block; color:var(--gold); margin-bottom:3px; font-size:.9em; line-height:1.2; }
    .toast-text span   { font-size:.8em; color:rgba(255,255,255,.65); }

    /* ══════════════════════════════════════════
       FLOATING CART
    ══════════════════════════════════════════ */
    .float-cart {
      position:fixed; bottom:34px; right:28px; z-index:1000;
      width:66px; height:66px; border-radius:50%; border:none; cursor:pointer;
      background:linear-gradient(135deg,var(--gold) 0%,var(--gold-dark) 60%,var(--gold) 100%);
      background-size:200% 200%; animation:goldSlide 3s linear infinite;
      box-shadow:0 8px 36px rgba(212,175,55,.55), 0 0 60px rgba(212,175,55,.25);
      display:flex; align-items:center; justify-content:center; font-size:1.6em;
      transition:transform .3s cubic-bezier(.34,1.56,.64,1), box-shadow .3s;
      text-decoration:none;
    }
    .float-cart:hover {
      transform:scale(1.2) rotate(-12deg);
      box-shadow:0 16px 55px rgba(212,175,55,.7), 0 0 90px rgba(212,175,55,.35);
    }
    .float-badge {
      position:absolute; top:-6px; right:-6px;
      min-width:26px; height:26px;
      background:linear-gradient(135deg,var(--pink),var(--pink-dark));
      border-radius:999px; border:2.5px solid var(--purple-deep);
      font-size:.63em; font-weight:700; color:#fff;
      display:flex; align-items:center; justify-content:center; padding:0 4px;
      animation:pulseBadge 2s ease-in-out infinite;
    }

    /* ══════════════════════════════════════════
       EMPTY STATE
    ══════════════════════════════════════════ */
    .empty-state {
      text-align:center; padding:90px 20px; width:100%;
      background:var(--glass-bg); border:1px solid var(--glass-brd); border-radius:28px;
    }
    .empty-icon    { font-size:4.2em; margin-bottom:18px; opacity:.45; }
    .empty-state h3 { color:rgba(255,255,255,.55); font-family:'Playfair Display',serif; font-size:1.35em; }
    .empty-state p  { color:rgba(255,255,255,.35); font-size:.88em; margin-top:10px; }

    /* Dynamic search no-result box */
    .no-search-result {
      display:none; width:100%; text-align:center; padding:60px 20px;
      background:var(--glass-bg); border:1px solid rgba(255,255,255,.08); border-radius:24px;
    }
    .no-search-result .ns-icon { font-size:3em; opacity:.4; margin-bottom:14px; }
    .no-search-result p { color:rgba(255,255,255,.38); font-size:.9em; }

    /* ══════════════════════════════════════════
       FOOTER
    ══════════════════════════════════════════ */
    .site-footer {
      position:relative; z-index:1; text-align:center;
      padding:40px 20px; font-size:.8em;
      color:rgba(255,255,255,.4);
      border-top:1px solid rgba(255,255,255,.07); line-height:2;
    }
    .footer-brand {
      font-family:'Playfair Display',serif; font-size:1.08em; font-weight:700;
      background:linear-gradient(90deg,var(--gold),var(--gold-light),var(--gold));
      background-size:200% 100%; animation:shimmerText 5s linear infinite;
      -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
      display:block; margin-bottom:8px;
    }
    .footer-dots { color:rgba(212,175,55,.4); letter-spacing:4px; }

    /* ══════════════════════════════════════════
       RESPONSIVE
    ══════════════════════════════════════════ */
    @media(max-width:1024px) { .product-grid { grid-template-columns:repeat(2,1fr); } }
    @media(max-width:620px) {
      .hero-inner h1 { font-size:2.2em; }
      .page-hero { height:250px; }
      .product-grid { grid-template-columns:1fr; }
      .top-bar { flex-direction:column; align-items:flex-start; }
      .float-cart { width:58px; height:58px; font-size:1.38em; bottom:24px; right:18px; }
      .filter-track { justify-content:flex-start; overflow-x:auto; flex-wrap:nowrap; padding-bottom:6px; }
      .stats-bar { gap:10px; }
      .stat-pill { min-width:120px; }
    }
  </style>
</head>

<style>
    body{
        background-color: #2d1560;
        font-family: 'Inter', sans-serif;
    }

    h2{
        font-family: 'Playfair Display', Georgia, serif;
        font-size:2em;
        color: #6D4C41;
    }

    div{
        background-color: rgba(255, 255, 255, .06);
        border-radius: 50px;
        padding: 50px;
        color: rgba(255, 255, 255, .5);
    }

    input{
        background: rgba(255, 255, 255, .07);
        border-radius: 50px;
        color: rgba(255, 255, 255, .5);
        border: 1px solid;
    }

    button{
        background: rgba(255, 255, 255, .07);
        border-radius: 50px;
        color: rgba(255, 255, 255, .5);
        border: 1px solid;
    }

</style>

<body>

<div id="particles"></div>

<!-- ═══════════ HERO ═══════════ -->
<div class="page-hero" id="pageHero">
  <div class="hero-orb" style="width:320px;height:320px;top:-90px;left:-90px;animation-duration:7s;animation-delay:0s;"></div>
  <div class="hero-orb" style="width:220px;height:220px;bottom:-55px;right:-45px;animation-duration:9s;animation-delay:2s;background:radial-gradient(circle,rgba(232,160,191,.22),transparent 70%);"></div>
  <div class="hero-orb" style="width:140px;height:140px;top:30px;right:20%;animation-duration:11s;animation-delay:1.5s;background:radial-gradient(circle,rgba(99,250,180,.14),transparent 70%);"></div>

  <div class="hero-inner">
    <p class="hero-eyebrow">✦ YOLAZCAKE Sintang ✦</p>
    <h1>Menu Pemesanan</h1>
    <p class="hero-sub">Pilih hidangan favoritmu &amp; nikmati setiap momen bersama kami</p>
    <div class="hero-divider">
      <span class="line"></span>
      <span class="diamond">✦ ✦ ✦</span>
      <span class="line"></span>
    </div>
  </div>
</div>

<div class="back-link">
  <a href="../index.php">← Kembali ke Beranda</a>
</div>

<!-- ═══════════ PAGE WRAPPER ═══════════ -->
<div class="page-wrapper">

  <!-- TOP BAR -->
  <div class="top-bar">
    <div>
      <div class="section-label">Pilihan Kami</div>
      <h2 class="section-title">Daftar Menu &amp; Produk</h2>
    </div>
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
      <span class="badge-live"><span class="blip"></span>Menu Tersedia</span>
      <a href="keranjang.php" class="btn-premium btn-green">
        🛒 Keranjang
        <?php if($total_keranjang > 0): ?>
        <span style="background:rgba(238,42,123,.88);color:#fff;border-radius:999px;padding:1px 9px;font-size:.78em;"><?= $total_keranjang ?></span>
        <?php endif; ?>
      </a>
    </div>
  </div>

  <?php
  // Hitung statistik
  $total_produk   = count($produk_list);
  $total_tersedia = 0;
  $total_kategori_unik = 0;
  $kat_ids = [];
  foreach($produk_list as $p){
    if((int)$p['stok'] > 0) $total_tersedia++;
    $kid = $p['id_kategori'] ?? null;
    if($kid && !in_array($kid, $kat_ids)){ $kat_ids[] = $kid; $total_kategori_unik++; }
  }
  ?>

  <!-- STATS BAR -->
  <div class="stats-bar">
    <div class="stat-pill">
      <span class="stat-icon">🎂</span>
      <div>
        <div class="stat-val"><?= $total_produk ?></div>
        <div class="stat-lbl">Total Menu</div>
      </div>
    </div>
    <div class="stat-pill">
      <span class="stat-icon">✅</span>
      <div>
        <div class="stat-val"><?= $total_tersedia ?></div>
        <div class="stat-lbl">Tersedia</div>
      </div>
    </div>
    <div class="stat-pill">
      <span class="stat-icon">🏷️</span>
      <div>
        <div class="stat-val"><?= $total_kategori_unik ?></div>
        <div class="stat-lbl">Kategori</div>
      </div>
    </div>
    <?php if($total_keranjang > 0): ?>
    <div class="stat-pill">
      <span class="stat-icon">🛒</span>
      <div>
        <div class="stat-val"><?= $total_keranjang ?></div>
        <div class="stat-lbl">Di Keranjang</div>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- CART SUMMARY BAR -->
  <?php if($total_keranjang > 0): ?>
  <div class="cart-bar">
    <div class="cart-bar-info">
      🛍️ Kamu sudah memilih <strong><?= $total_keranjang ?> item</strong> di keranjang
    </div>
    <a href="keranjang.php" class="btn-premium btn-gold" style="padding:10px 24px;font-size:.78em;">
      ✦ Lanjut ke Keranjang
    </a>
  </div>
  <?php endif; ?>

  <!-- SEARCH BAR -->
  <div class="search-wrap">
    <span class="search-icon">🔍</span>
    <input type="text" class="search-input" id="searchInput"
           placeholder="Cari menu atau produk favorit kamu…"
           autocomplete="off">
    <button class="search-clear" id="searchClear" title="Hapus pencarian">✕</button>
  </div>

  <!-- CATEGORY FILTER -->
  <?php
  if($kat_query && mysqli_num_rows($kat_query) > 0):
    $kat_counts = [];
    foreach($produk_list as $p){
      $kid = (int)($p['id_kategori'] ?? 0);
      $kat_counts[$kid] = ($kat_counts[$kid] ?? 0) + 1;
    }
    $total_all = count($produk_list);
  ?>
  <div class="filter-section">
    <div class="filter-label">✦ Filter Kategori ✦</div>
    <div class="filter-track">

      <!-- Semua -->
      <a href="menuu.php" class="filter-pill <?= ($filter_kat === 0) ? 'active' : '' ?>">
        <span class="pill-icon">🍽️</span>
        Semua
        <span class="pill-count"><?= $total_all ?></span>
      </a>

      <?php mysqli_data_seek($kat_query, 0); while($k = mysqli_fetch_assoc($kat_query)):
        $kid   = (int)$k['id_kategori'];
        $cnt   = $kat_counts[$kid] ?? 0;
        $isAct = ($filter_kat === $kid);
      ?>
      <a href="?kat=<?= $kid ?>" class="filter-pill <?= $isAct ? 'active' : '' ?>">
        <span class="pill-icon"><?= htmlspecialchars($k['icon'] ?? '🍰') ?></span>
        <?= htmlspecialchars($k['nama_kategori']) ?>
        <span class="pill-count"><?= $cnt ?></span>
      </a>
      <?php endwhile; ?>

    </div>
  </div>
  <?php endif; ?>

  <!-- GOLD RULE -->
  <div class="gold-rule"><span>✦ ✦ ✦</span></div>

  <?php
  // Filter produk berdasar kategori
  $filtered = [];
  foreach($produk_list as $p){
    if($filter_kat && (int)($p['id_kategori'] ?? 0) !== $filter_kat) continue;
    $filtered[] = $p;
  }

  // Nama & icon kategori aktif
  $active_cat_name = 'Semua Menu';
  $active_cat_icon = '🍽️';
  if($filter_kat && $kat_query){
    mysqli_data_seek($kat_query,0);
    while($k = mysqli_fetch_assoc($kat_query)){
      if((int)$k['id_kategori'] === $filter_kat){
        $active_cat_name = htmlspecialchars($k['nama_kategori']);
        $active_cat_icon = htmlspecialchars($k['icon'] ?? '🍰');
        break;
      }
    }
  }
  ?>

  <!-- ACTIVE CATEGORY HEADER -->
  <div class="cat-header">
    <span class="cat-header-icon"><?= $active_cat_icon ?></span>
    <div class="cat-header-text">
      <h3><?= $active_cat_name ?></h3>
      <p id="prodCountText"><?= count($filtered) ?> produk ditemukan</p>
    </div>
  </div>

  <!-- RESULTS INFO -->
  <div class="results-info">
    <span>Menampilkan <strong id="visibleCount"><?= count($filtered) ?></strong> dari <strong><?= count($filtered) ?></strong> produk</span>
    <span id="searchQueryLabel"></span>
  </div>

  <!-- PRODUCT GRID -->
  <?php if(!empty($filtered)): ?>
  <div class="product-grid" id="productGrid">
    <?php foreach($filtered as $i => $p):
      $stok = (int)$p['stok'];
      if($stok <= 0)     { $sc='stok-habis'; $sl='Stok Habis'; }
      elseif($stok <= 5) { $sc='stok-low';   $sl=$stok.' Tersisa'; }
      else               { $sc='stok-ok';    $sl='Tersedia'; }
      $habis = ($stok <= 0);
    ?>
    <div class="prod-card"
         data-delay="<?= $i ?>"
         data-name="<?= strtolower(htmlspecialchars($p['nama_produk'])) ?>"
         data-kat="<?= strtolower(htmlspecialchars($p['nama_kategori'])) ?>">

      <!-- IMAGE -->
      <div class="card-img-wrap">
        <img src="../assets/img/produk/<?= htmlspecialchars($p['foto']); ?>"
             alt="<?= htmlspecialchars($p['nama_produk']); ?>"
             onerror="this.src='../assets/img/no-image.png'"
             loading="lazy">
        <div class="card-img-overlay"></div>
        <span class="stok-badge <?= $sc ?>"><?= $sl ?></span>
        <?php if(!empty($p['nama_kategori'])): ?>
        <div class="card-cat-tag">
          <?= htmlspecialchars($p['kategori_icon'] ?? '') ?>
          <?= htmlspecialchars($p['nama_kategori']) ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- BODY -->
      <div class="card-body">
        <div class="prod-name"><?= htmlspecialchars($p['nama_produk']); ?></div>
        <div class="prod-price">Rp <?= number_format($p['harga'],0,',','.'); ?></div>
        <div class="card-divider"></div>

        <!-- ACTION -->
        <?php if(!$habis): ?>
        <form action="keranjang.php" method="POST" class="card-action"
              onsubmit="onAddCart(event,this,'<?= htmlspecialchars($p['nama_produk'],ENT_QUOTES); ?>')">
          <input type="hidden" name="tambah"    value="1">
          <input type="hidden" name="id_produk" value="<?= $p['id_produk']; ?>">
          <div class="qty-wrap">
            <button type="button" class="qty-btn" onclick="changeQty(this,-1)">−</button>
            <input  type="number" class="qty-input" name="jumlah" value="1" min="1" max="<?= $stok ?>" readonly>
            <button type="button" class="qty-btn" onclick="changeQty(this,1)">+</button>
          </div>
          <button type="submit" class="btn-cart btn-cart-gold">🛒 Tambah</button>
        </form>
        <?php else: ?>
        <div class="card-action">
          <button class="btn-cart btn-cart-disabled" disabled>❌ Stok Habis</button>
        </div>
        <?php endif; ?>
      </div>

    </div>
    <?php endforeach; ?>
  </div>

  <!-- No search result box (hidden by default) -->
  <div class="no-search-result" id="noSearchResult">
    <div class="ns-icon">🔍</div>
    <p>Tidak ada produk yang cocok dengan pencarianmu.<br>Coba kata kunci lain.</p>
  </div>

  <?php else: ?>
  <div class="empty-state">
    <div class="empty-icon">🎂</div>
    <h3>Belum ada produk</h3>
    <p>Tidak ada menu yang tersedia di kategori ini.<br>Coba pilih kategori lain.</p>
  </div>
  <?php endif; ?>

</div><!-- /page-wrapper -->

<!-- ═══════════ FOOTER ═══════════ -->
<div class="site-footer">
  <span class="footer-brand">✦ YOLAZCAKE Sintang ✦</span>
  <span class="footer-dots">· · ·</span><br>
  Cafe &bull; Bakery &bull; Boutique<br>
  Jl. Lintas Melawi, Ladang, Sintang, Kalimantan Barat<br>
  WA: 0815-7815-7888
</div>

<!-- ═══════════ FLOATING CART ═══════════ -->
<a href="keranjang.php" class="float-cart" title="Lihat Keranjang">
  🛒
  <?php if($total_keranjang > 0): ?>
  <div class="float-badge" id="cartBadge"><?= $total_keranjang ?></div>
  <?php else: ?>
  <div class="float-badge" id="cartBadge" style="display:none;">0</div>
  <?php endif; ?>
</a>

<!-- ═══════════ TOAST ═══════════ -->
<div class="toast" id="toast">
  <span class="toast-icon">🎂</span>
  <div class="toast-text">
    <strong id="toast-name"></strong>
    <span>Berhasil ditambahkan ke keranjang!</span>
  </div>
</div>

<!-- ═══════════ SCRIPTS ═══════════ -->
<script>
/* ── Hero sparkles ── */
(function(){
  const hero   = document.getElementById('pageHero');
  const colors = ['#D4AF37','#FFE87C','#E8A0BF','#fff','#f9ce34','#b8860b','#6efabc','#ee2a7b'];
  for(let i=0;i<32;i++){
    const d = document.createElement('div'); d.className='sparkle';
    const s = Math.random()*7+2;
    d.style.cssText =
      `width:${s}px;height:${s}px;` +
      `background:${colors[Math.floor(Math.random()*colors.length)]};` +
      `left:${Math.random()*100}%;bottom:${Math.random()*40}%;` +
      `animation-duration:${4+Math.random()*9}s;animation-delay:${Math.random()*7}s;` +
      `opacity:0;border-radius:50%;position:absolute;pointer-events:none;` +
      `animation-name:floatDot;animation-timing-function:linear;animation-iteration-count:infinite;`;
    hero.appendChild(d);
  }
})();

/* ── Background particles ── */
(function(){
  const c    = document.getElementById('particles');
  const cols = ['rgba(212,175,55,.32)','rgba(232,160,191,.26)','rgba(255,255,255,.1)','rgba(99,250,180,.18)'];
  for(let i=0;i<22;i++){
    const p=document.createElement('div'); p.className='particle';
    const s=Math.random()*7+2;
    p.style.cssText =
      `width:${s}px;height:${s}px;` +
      `background:${cols[Math.floor(Math.random()*cols.length)]};` +
      `left:${Math.random()*100}%;` +
      `animation-duration:${12+Math.random()*16}s;animation-delay:${Math.random()*14}s;`;
    c.appendChild(p);
  }
})();

/* ── Card reveal with stagger ── */
(function(){
  const cards = document.querySelectorAll('.prod-card');
  const io = new IntersectionObserver(entries=>{
    entries.forEach(e=>{
      if(e.isIntersecting){
        const idx   = parseInt(e.target.dataset.delay||0);
        const delay = Math.min(idx,9) * 75;
        setTimeout(()=>{
          e.target.style.transition =
            `opacity .5s ease ${delay}ms, transform .55s cubic-bezier(.34,1.56,.64,1) ${delay}ms, border-color .45s, box-shadow .45s`;
          e.target.classList.add('visible');
        },10);
        io.unobserve(e.target);
      }
    });
  },{threshold:0.05});
  cards.forEach(c=>io.observe(c));
})();

/* ── Qty control ── */
function changeQty(btn, delta){
  const wrap  = btn.closest('.qty-wrap');
  const input = wrap.querySelector('.qty-input');
  let v = parseInt(input.value)||1;
  const max = parseInt(input.max)||99;
  v = Math.min(max, Math.max(1, v+delta));
  input.value = v;
}

/* ── Live search ── */
(function(){
  const inp     = document.getElementById('searchInput');
  const clrBtn  = document.getElementById('searchClear');
  const grid    = document.getElementById('productGrid');
  const noRes   = document.getElementById('noSearchResult');
  const countEl = document.getElementById('visibleCount');
  const qLabel  = document.getElementById('searchQueryLabel');
  const allCards= document.querySelectorAll('.prod-card');
  const totalFixed = allCards.length;

  function doFilter(){
    const q = inp.value.trim().toLowerCase();
    clrBtn.classList.toggle('visible', q.length > 0);

    let visible = 0;
    allCards.forEach(card=>{
      const name = card.dataset.name || '';
      const kat  = card.dataset.kat  || '';
      const match = !q || name.includes(q) || kat.includes(q);
      card.classList.toggle('hidden-by-search', !match);
      if(match) visible++;
    });

    if(countEl) countEl.textContent = visible;
    if(qLabel)  qLabel.textContent  = q ? `"${inp.value.trim()}"` : '';

    const isEmpty = (visible === 0 && totalFixed > 0);
    if(grid)  grid.style.display = isEmpty ? 'none' : 'grid';
    if(noRes) noRes.style.display = isEmpty ? 'block' : 'none';
  }

  if(inp){
    inp.addEventListener('input', doFilter);
    clrBtn.addEventListener('click',()=>{ inp.value=''; inp.focus(); doFilter(); });
  }
})();

/* ── Toast & cart badge ── */
let cartCount = <?= $total_keranjang ?>;
const toast     = document.getElementById('toast');
const toastName = document.getElementById('toast-name');
const badge     = document.getElementById('cartBadge');
let toastTimer;

function showToast(name){
  toastName.textContent = name;
  toast.classList.remove('hide');
  void toast.offsetWidth; // reflow
  toast.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(()=>{
    toast.classList.remove('show');
    toast.classList.add('hide');
    setTimeout(()=>toast.classList.remove('hide'), 400);
  }, 3400);
}

function onAddCart(e, form, name){
  e.preventDefault();
  const qty = parseInt(form.querySelector('.qty-input').value)||1;
  cartCount += qty;
  badge.textContent = cartCount;
  badge.style.display = 'flex';
  showToast(name);
  setTimeout(()=>form.submit(), 680);
}
</script>

</body>
</html>
