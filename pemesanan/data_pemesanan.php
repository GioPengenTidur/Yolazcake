<?php
session_start();
require_once __DIR__.'/../config/staff_guard.php';
require_staff_login();
include '../config/koneksi.php';
require_once __DIR__.'/../config/csrf_helper.php';
$csrf = csrf_token();

$query = mysqli_query($conn,
"SELECT * FROM pemesanan
ORDER BY id_pemesanan DESC");
if(!$query){ die(mysqli_error($conn)); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Pemesanan – YOLAZCAKE</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}

    body {
      min-height:100vh;
      font-family:'Inter',sans-serif;
      background:linear-gradient(160deg,#1e0e3a 0%,#2d1560 50%,#1a0a2e 100%);
      position:relative;
      overflow-x:hidden;
    }

    body::before {
      content:'';position:fixed;inset:0;
      background:
        radial-gradient(ellipse at 20% 30%,rgba(212,175,55,.10) 0%,transparent 55%),
        radial-gradient(ellipse at 80% 70%,rgba(232,160,191,.10) 0%,transparent 55%);
      pointer-events:none;z-index:0;
    }

    /* ── HERO ── */
    .page-hero {
      position:relative;height:260px;
      display:flex;flex-direction:column;align-items:center;justify-content:center;
      overflow:hidden;
      background:linear-gradient(135deg,#2b1a11 0%,#4a2c1a 40%,#6d3e26 70%,#3a1f0e 100%);
      z-index:1;
    }
    .page-hero::before {
      content:'';position:absolute;inset:0;
      background:
        radial-gradient(ellipse at 30% 50%,rgba(212,175,55,.18) 0%,transparent 60%),
        radial-gradient(ellipse at 75% 40%,rgba(232,160,191,.15) 0%,transparent 55%);
      animation:heroAurora 8s ease-in-out infinite alternate;
    }
    @keyframes heroAurora{0%{opacity:.6;transform:scale(1);}100%{opacity:1;transform:scale(1.08) translateX(10px);}}

    .sparkle{position:absolute;border-radius:50%;pointer-events:none;animation:floatDot linear infinite;}
    @keyframes floatDot{0%{transform:translateY(0) rotate(0deg);opacity:0;}20%{opacity:1;}80%{opacity:.8;}100%{transform:translateY(-280px) rotate(360deg);opacity:0;}}

    .hero-inner{position:relative;z-index:2;text-align:center;color:#fff;}

    .hero-eyebrow{
      font-size:.72em;font-weight:500;letter-spacing:5px;text-transform:uppercase;
      color:#D4AF37;margin-bottom:10px;
      opacity:0;animation:fadeSlideDown .8s forwards .3s;
    }
    .hero-inner h1{
      font-family:'Playfair Display',serif;font-size:2.8em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,#D4AF37 60%,#FFE4B5 80%,#fff);
      background-size:200% 100%;
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
      animation:shimmerText 4s ease-in-out infinite,fadeSlideDown .9s forwards .5s;
      opacity:0;
    }
    .hero-inner .hero-sub{
      font-size:.9em;color:rgba(255,255,255,.65);margin-top:10px;
      opacity:0;animation:fadeSlideDown .9s forwards .9s;
    }
    .hero-divider{
      margin-top:16px;display:flex;justify-content:center;align-items:center;gap:12px;
      opacity:0;animation:fadeSlideDown .9s forwards 1.1s;
    }
    .hero-divider span{display:block;width:60px;height:1px;background:linear-gradient(to right,transparent,#D4AF37);}
    .hero-divider span:last-child{background:linear-gradient(to left,transparent,#D4AF37);}
    .hero-divider .diamond{color:#D4AF37;font-size:.75em;letter-spacing:4px;}

    @keyframes shimmerText{0%{background-position:100% 0;}100%{background-position:-100% 0;}}
    @keyframes fadeSlideDown{from{opacity:0;transform:translateY(-18px);}to{opacity:1;transform:translateY(0);}}
    @keyframes goldSlide{0%{background-position:0% 0;}100%{background-position:200% 0;}}

    /* ── BACK LINK ── */
    .back-link{
      position:relative;z-index:2;
      display:flex;justify-content:flex-start;
      width:100%;margin-bottom:20px;
    }
    .back-link a{
      display:inline-flex;align-items:center;gap:8px;
      font-size:.82em;font-weight:600;letter-spacing:1px;
      color:#D4AF37;text-decoration:none;
      border:1px solid rgba(212,175,55,.3);padding:10px 22px;border-radius:999px;
      transition:transform .25s,box-shadow .3s,background .3s;background:rgba(212,175,55,.1);
    }
    .back-link a:hover{
      transform:translateX(-3px);
      background:rgba(212,175,55,.2);
      box-shadow:0 6px 20px rgba(212,175,55,.25);
    }

    /* ── PAGE WRAPPER ── */
    .page-wrapper{
      position:relative;z-index:1;
      display:flex;flex-direction:column;align-items:center;
      padding:28px 20px 120px;
      max-width:1300px;margin:0 auto;
    }

    /* ── TOP BAR ── */
    .top-bar{
      width:100%;display:flex;align-items:center;justify-content:space-between;
      margin-bottom:32px;flex-wrap:wrap;gap:14px;
    }
    .section-title{
      font-family:'Playfair Display',serif;font-size:1.6em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,#D4AF37 70%);
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
    }
    .new-badge{
      display:inline-flex;align-items:center;gap:8px;
      background:linear-gradient(135deg,rgba(99,250,180,.15),rgba(99,250,180,.06));
      border:1px solid rgba(99,250,180,.35);
      color:#6efabc;font-size:.78em;font-weight:600;letter-spacing:1.5px;
      padding:6px 18px;border-radius:999px;
    }

    /* ── BTN PREMIUM ── */
    .btn-premium{
      position:relative;padding:13px 28px;border:none;border-radius:14px;
      font-family:'Inter',sans-serif;font-size:.85em;font-weight:700;
      letter-spacing:2px;text-transform:uppercase;cursor:pointer;
      overflow:hidden;transition:transform .25s cubic-bezier(.34,1.56,.64,1),box-shadow .35s;
      text-decoration:none;display:inline-flex;align-items:center;gap:8px;
    }
    .btn-premium::before{
      content:'';position:absolute;inset:0;
      background:linear-gradient(90deg,transparent,rgba(255,255,255,.15),transparent);
      transform:translateX(-100%);transition:transform .5s;
    }
    .btn-premium:hover::before{transform:translateX(100%);}
    .btn-premium:hover{transform:translateY(-3px) scale(1.02);}

    .btn-gold{
      background:linear-gradient(135deg,#D4AF37 0%,#b8860b 50%,#D4AF37 100%);
      background-size:200% 100%;color:#1e0e3a;
      animation:goldSlide 3s linear infinite;
      box-shadow:0 8px 28px rgba(212,175,55,.35),0 0 40px rgba(212,175,55,.18);
    }
    .btn-gold:hover{box-shadow:0 12px 40px rgba(212,175,55,.55),0 0 60px rgba(212,175,55,.3);}

    .btn-green{
      background:linear-gradient(135deg,#2ee6a0 0%,#1aa878 50%,#2ee6a0 100%);
      background-size:200% 100%;color:#0b2e22;
      animation:goldSlide 3s linear infinite;
      box-shadow:0 8px 28px rgba(46,230,160,.3),0 0 40px rgba(46,230,160,.15);
    }
    .btn-green:hover{box-shadow:0 12px 40px rgba(46,230,160,.5),0 0 60px rgba(46,230,160,.25);}

    /* ── EXPORT PANEL ── */
    .export-panel{
      width:100%;background:rgba(46,230,160,.05);
      border:1px solid rgba(46,230,160,.25);border-radius:18px;
      padding:20px 22px;margin-bottom:28px;
      opacity:0;animation:fadeSlideDown .8s forwards .3s;
    }
    .export-panel-label{
      font-size:.85em;font-weight:700;letter-spacing:1px;color:#2ee6a0;margin-bottom:14px;
    }
    .export-panel-fields{
      display:flex;gap:14px;align-items:flex-end;flex-wrap:wrap;
    }
    .export-field{display:flex;flex-direction:column;gap:6px;}
    .export-field label{
      font-size:.7em;color:rgba(255,255,255,.5);letter-spacing:1px;text-transform:uppercase;
    }
    .export-field input,.export-field select{
      background:rgba(255,255,255,.06);
      border:1px solid rgba(212,175,55,.25);
      border-radius:10px;padding:9px 14px;
      color:#fff;font-family:'Inter',sans-serif;font-size:.85em;
      outline:none;cursor:pointer;
      transition:border-color .3s;
      color-scheme:dark;
    }
    .export-field input:focus,.export-field select:focus{border-color:rgba(212,175,55,.6);}
    .export-field select option{background:#2d1560;color:#fff;}
    .export-panel-hint{
      margin-top:12px;font-size:.75em;color:rgba(255,255,255,.4);
    }
    @media(max-width:580px){
      .export-panel-fields{flex-direction:column;align-items:stretch;}
    }

    /* ── GOLD RULE ── */
    .gold-rule{display:flex;align-items:center;gap:12px;width:100%;margin-bottom:28px;}
    .gold-rule::before,.gold-rule::after{content:'';flex:1;height:1px;background:linear-gradient(to right,transparent,rgba(212,175,55,.4));}
    .gold-rule::after{background:linear-gradient(to left,transparent,rgba(212,175,55,.4));}
    .gold-rule span{color:#D4AF37;font-size:.65em;letter-spacing:3px;white-space:nowrap;}

    /* ── STATS ROW ── */
    .stats-row{
      width:100%;display:grid;grid-template-columns:repeat(4,1fr);gap:16px;
      margin-bottom:32px;
    }
    .stat-card{
      background:rgba(255,255,255,.06);
      backdrop-filter:blur(20px);
      border:1px solid rgba(255,255,255,.1);
      border-radius:16px;padding:18px 20px;
      display:flex;flex-direction:column;gap:6px;
      opacity:0;animation:fadeSlideDown .7s forwards;
    }
    .stat-card:nth-child(1){animation-delay:.1s;}
    .stat-card:nth-child(2){animation-delay:.2s;}
    .stat-card:nth-child(3){animation-delay:.3s;}
    .stat-card:nth-child(4){animation-delay:.4s;}
    .stat-card::before{
      content:'';position:absolute;top:0;left:0;right:0;height:2px;
      background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);
      background-size:200% 100%;animation:goldSlide 4s linear infinite;
      border-radius:16px 16px 0 0;
    }
    .stat-card{position:relative;overflow:hidden;}
    .stat-icon{font-size:1.5em;margin-bottom:4px;}
    .stat-label{font-size:.72em;color:rgba(255,255,255,.5);letter-spacing:1px;text-transform:uppercase;}
    .stat-value{font-family:'Playfair Display',serif;font-size:1.5em;font-weight:700;color:#fff;}
    .stat-value.gold{color:#D4AF37;}

    /* ── SEARCH BAR ── */
    .search-bar{
      width:100%;margin-bottom:24px;
      display:flex;gap:12px;align-items:center;flex-wrap:wrap;
    }
    .search-wrap{
      flex:1;min-width:220px;
      display:flex;align-items:center;gap:10px;
      background:rgba(255,255,255,.06);
      border:1px solid rgba(212,175,55,.25);
      border-radius:12px;padding:10px 16px;
      transition:border-color .3s,box-shadow .3s;
    }
    .search-wrap:focus-within{
      border-color:rgba(212,175,55,.6);
      box-shadow:0 0 20px rgba(212,175,55,.15);
    }
    .search-wrap input{
      background:transparent;border:none;outline:none;
      color:#fff;font-family:'Inter',sans-serif;font-size:.9em;width:100%;
    }
    .search-wrap input::placeholder{color:rgba(255,255,255,.3);}
    .search-icon{color:rgba(212,175,55,.6);font-size:1em;}

    .filter-select{
      background:rgba(255,255,255,.06);
      border:1px solid rgba(212,175,55,.25);
      border-radius:12px;padding:10px 16px;
      color:#fff;font-family:'Inter',sans-serif;font-size:.85em;
      outline:none;cursor:pointer;
      transition:border-color .3s;
    }
    .filter-select:focus{border-color:rgba(212,175,55,.6);}
    .filter-select option{background:#2d1560;color:#fff;}

    /* ── TABLE WRAP ── */
    .table-wrap{
      width:100%;
      background:rgba(255,255,255,.04);
      backdrop-filter:blur(20px);
      border:1px solid rgba(255,255,255,.1);
      border-radius:24px;
      overflow:hidden;
      opacity:0;animation:fadeSlideDown .9s forwards .5s;
    }

    table{
      border-collapse:collapse;
      width:100%;
    }

    thead tr{
      background:linear-gradient(135deg,rgba(212,175,55,.15) 0%,rgba(212,175,55,.06) 100%);
      border-bottom:1px solid rgba(212,175,55,.3);
    }

    thead th{
      padding:16px 20px;
      font-size:.75em;font-weight:600;letter-spacing:2px;text-transform:uppercase;
      color:#D4AF37;text-align:left;white-space:nowrap;
    }
    thead th:first-child{text-align:center;}

    tbody tr{
      border-bottom:1px solid rgba(255,255,255,.06);
      transition:background .25s;
    }
    tbody tr:last-child{border-bottom:none;}
    tbody tr:hover{
      background:rgba(212,175,55,.06);
    }

    tbody td{
      padding:15px 20px;
      font-size:.88em;color:rgba(255,255,255,.85);
      vertical-align:middle;
    }
    tbody td:first-child{text-align:center;color:rgba(255,255,255,.4);font-size:.8em;}

    /* kode pesanan */
    .kode-badge{
      display:inline-flex;align-items:center;
      background:rgba(212,175,55,.1);
      border:1px solid rgba(212,175,55,.3);
      color:#D4AF37;font-size:.82em;font-weight:600;letter-spacing:1px;
      padding:4px 12px;border-radius:999px;
    }

    /* nama */
    .nama-cell{font-weight:600;color:#fff;}

    /* harga */
    .harga-cell{color:#6efabc;font-weight:600;}

    /* metode pembayaran */
    .metode-badge{
      display:inline-block;
      padding:4px 12px;border-radius:999px;font-size:.78em;font-weight:600;
      background:rgba(150,130,255,.15);border:1px solid rgba(150,130,255,.3);color:#b8a5ff;
    }

    /* ── STATUS BADGES ── */
    .status-badge{
      display:inline-flex;align-items:center;gap:6px;
      padding:5px 14px;border-radius:999px;font-size:.78em;font-weight:600;letter-spacing:.5px;
    }
    .status-badge::before{content:'';width:6px;height:6px;border-radius:50%;display:block;}

    .status-menunggu{
      background:rgba(255,180,50,.15);border:1px solid rgba(255,180,50,.35);color:#ffb432;
    }
    .status-menunggu::before{background:#ffb432;box-shadow:0 0 6px #ffb432;}

    .status-diproses{
      background:rgba(99,149,250,.15);border:1px solid rgba(99,149,250,.35);color:#6395fa;
    }
    .status-diproses::before{background:#6395fa;box-shadow:0 0 6px #6395fa;}

    .status-siap{
      background:rgba(180,99,250,.15);border:1px solid rgba(180,99,250,.35);color:#c06efa;
    }
    .status-siap::before{background:#c06efa;box-shadow:0 0 6px #c06efa;}

    .status-selesai{
      background:rgba(99,250,180,.15);border:1px solid rgba(99,250,180,.35);color:#6efabc;
    }
    .status-selesai::before{background:#6efabc;box-shadow:0 0 6px #6efabc;}

    .status-batal{
      background:rgba(255,80,80,.15);border:1px solid rgba(255,80,80,.35);color:#ff6060;
    }
    .status-batal::before{background:#ff6060;box-shadow:0 0 6px #ff6060;}

    /* ── ACTION BUTTONS ── */
    .btn-aksi{
      display:inline-flex;align-items:center;gap:6px;
      padding:7px 14px;border-radius:10px;font-size:.78em;font-weight:600;
      text-decoration:none;cursor:pointer;border:none;
      transition:transform .25s cubic-bezier(.34,1.56,.64,1),box-shadow .3s;
      letter-spacing:.5px;
    }
    .btn-aksi:hover{transform:translateY(-2px);}

    .btn-detail{
      background:linear-gradient(135deg,rgba(99,149,250,.25),rgba(99,149,250,.12));
      border:1px solid rgba(99,149,250,.4);color:#6395fa;
    }
    .btn-detail:hover{
      background:linear-gradient(135deg,rgba(99,149,250,.35),rgba(99,149,250,.2));
      box-shadow:0 6px 20px rgba(99,149,250,.3);
    }

    .btn-invoice{
      background:linear-gradient(135deg,rgba(212,175,55,.25),rgba(238,42,123,.12));
      border:1px solid rgba(212,175,55,.4);color:#D4AF37;
    }
    .btn-invoice:hover{
      background:linear-gradient(135deg,rgba(212,175,55,.35),rgba(238,42,123,.2));
      box-shadow:0 6px 20px rgba(212,175,55,.3);
    }

    .btn-hapus{
      background:linear-gradient(135deg,rgba(255,80,80,.2),rgba(255,80,80,.08));
      border:1px solid rgba(255,80,80,.35);color:#ff6060;
    }
    .btn-hapus:hover{
      background:linear-gradient(135deg,rgba(255,80,80,.3),rgba(255,80,80,.15));
      box-shadow:0 6px 20px rgba(255,80,80,.3);
    }

    .btn-edit{
      background:linear-gradient(135deg,rgba(212,175,55,.25),rgba(212,175,55,.12));
      border:1px solid rgba(212,175,55,.4);color:#D4AF37;
    }
    .btn-edit:hover{
      background:linear-gradient(135deg,rgba(212,175,55,.35),rgba(212,175,55,.2));
      box-shadow:0 6px 20px rgba(212,175,55,.3);
    }

    .aksi-cell{display:flex;gap:8px;flex-wrap:wrap;}

    /* ── EMPTY STATE ── */
    .empty-state{
      text-align:center;padding:80px 20px;
    }
    .empty-state .empty-icon{font-size:3.5em;margin-bottom:16px;opacity:.5;}
    .empty-state p{color:rgba(255,255,255,.4);font-size:.92em;}

    /* ── PARTICLES ── */
    .particle{position:fixed;border-radius:50%;pointer-events:none;animation:particleFloat linear infinite;z-index:0;}
    @keyframes particleFloat{
      0%{transform:translateY(100vh) scale(0);opacity:0;}
      10%{opacity:.5;}90%{opacity:.3;}
      100%{transform:translateY(-100px) scale(1);opacity:0;}
    }

    /* ── FOOTER ── */
    .site-footer{
      position:relative;z-index:1;text-align:center;
      padding:36px 20px;font-size:.8em;
      color:rgba(255,255,255,.5);
      border-top:1px solid rgba(255,255,255,.06);
      line-height:1.8;
    }

    /* ── RESPONSIVE ── */
    @media(max-width:960px){
      .stats-row{grid-template-columns:repeat(2,1fr);}
      .table-wrap{overflow-x:auto;}
      table{min-width:700px;}
    }
    @media(max-width:580px){
      .hero-inner h1{font-size:2em;}
      .stats-row{grid-template-columns:1fr 1fr;}
      .top-bar{flex-direction:column;align-items:flex-start;}
    }
  </style>
</head>
<body>

<div id="particles"></div>

<!-- HERO -->
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow">✦ YOLAZCAKE Sintang ✦</p>
    <h1>Data Pemesanan</h1>
    <p class="hero-sub">Kelola dan pantau semua pesanan dengan mudah</p>
    <div class="hero-divider">
      <span></span><span class="diamond">✦ ✦ ✦</span><span></span>
    </div>
  </div>
</div>

<!-- PAGE WRAPPER -->
<div class="page-wrapper">

  <div class="back-link">
    <a href="../dashboard.php">← Dashboard</a>
  </div>

  <!-- TOP BAR -->
  <div class="top-bar">
    <div>
      <div class="new-badge">📋 Manajemen Pesanan</div>
      <h2 class="section-title" style="margin-top:10px;">Daftar Semua Pesanan</h2>
    </div>
    <a href="menuu.php" class="btn-premium btn-gold">🎂 Menu Pemesanan</a>
  </div>

  <!-- EXPORT PANEL -->
  <form class="export-panel" method="get" action="export_excel.php" target="_blank">
    <div class="export-panel-label">📊 Export Laporan Penjualan ke Excel</div>
    <div class="export-panel-fields">
      <div class="export-field">
        <label>Dari Tanggal</label>
        <input type="date" name="tanggal_mulai">
      </div>
      <div class="export-field">
        <label>Sampai Tanggal</label>
        <input type="date" name="tanggal_akhir">
      </div>
      <div class="export-field">
        <label>Status Pesanan</label>
        <select name="status">
          <option value="">Semua Status</option>
          <option value="Menunggu">Menunggu</option>
          <option value="Diproses">Diproses</option>
          <option value="Siap Diambil">Siap Diambil</option>
          <option value="Selesai">Selesai</option>
          <option value="Dibatalkan">Dibatalkan</option>
        </select>
      </div>
      <button type="submit" class="btn-premium btn-green">📥 Export ke Excel</button>
    </div>
    <p class="export-panel-hint">Kosongkan tanggal untuk mengekspor seluruh data yang ada.</p>
  </form>

  <!-- GOLD RULE -->
  <div class="gold-rule"><span>✦ ✦ ✦</span></div>

  <?php
  // Ambil semua data ke array untuk statistik
  $semua = [];
  while($row = mysqli_fetch_assoc($query)){ $semua[] = $row; }

  $total_pesanan   = count($semua);
  $total_revenue   = array_sum(array_column($semua,'total_harga'));
  $menunggu_count  = count(array_filter($semua, fn($r)=>$r['status_pesanan']==='Menunggu'));
  $selesai_count   = count(array_filter($semua, fn($r)=>$r['status_pesanan']==='Selesai'));
  ?>

  <!-- STATS ROW -->
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-icon">📦</div>
      <div class="stat-label">Total Pesanan</div>
      <div class="stat-value gold"><?= $total_pesanan ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">💰</div>
      <div class="stat-label">Total Pendapatan</div>
      <div class="stat-value" style="font-size:1.1em;">Rp <?= number_format($total_revenue,0,',','.') ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">⏳</div>
      <div class="stat-label">Menunggu Proses</div>
      <div class="stat-value gold"><?= $menunggu_count ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">✅</div>
      <div class="stat-label">Pesanan Selesai</div>
      <div class="stat-value gold"><?= $selesai_count ?></div>
    </div>
  </div>

  <!-- SEARCH + FILTER -->
  <div class="search-bar">
    <div class="search-wrap">
      <span class="search-icon">🔍</span>
      <input type="text" id="searchInput" placeholder="Cari nama pemesan atau kode pesanan..." oninput="filterTable()">
    </div>
    <select class="filter-select" id="filterStatus" onchange="filterTable()">
      <option value="">Semua Status</option>
      <option value="Menunggu">Menunggu</option>
      <option value="Diproses">Diproses</option>
      <option value="Siap Diambil">Siap Diambil</option>
      <option value="Selesai">Selesai</option>
      <option value="Dibatalkan">Dibatalkan</option>
    </select>
  </div>

  <!-- TABLE -->
  <div class="table-wrap">
    <?php if(!empty($semua)): ?>
    <table id="mainTable">
      <thead>
        <tr>
          <th>No</th>
          <th>Kode Pesanan</th>
          <th>Nama Pemesan</th>
          <th>Tanggal</th>
          <th>Total Harga</th>
          <th>Pembayaran</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($semua as $i => $data): ?>
        <tr data-nama="<?= strtolower($data['nama_pemesan']) ?>" data-kode="<?= strtolower($data['kode_pesanan']) ?>" data-status="<?= htmlspecialchars($data['status_pesanan']) ?>">
          <td><?= $i + 1 ?></td>
          <td><span class="kode-badge"><?= htmlspecialchars($data['kode_pesanan']) ?></span></td>
          <td class="nama-cell"><?= htmlspecialchars($data['nama_pemesan']) ?></td>
          <td><?= htmlspecialchars($data['tanggal']) ?></td>
          <td class="harga-cell">
            Rp <?= number_format($data['total_harga'],0,',','.') ?>
            <?php if(!empty($data['kode_promo'])): ?>
              <br><span style="font-size:.7em;color:#6efabc;">🏷️ <?= htmlspecialchars($data['kode_promo']) ?> (-Rp<?= number_format($data['diskon_nominal'] ?? 0,0,',','.') ?>)</span>
            <?php endif; ?>
          </td>
          <td><span class="metode-badge"><?= htmlspecialchars($data['metode_pembayaran']) ?></span></td>
          <td>
            <?php
            $s = $data['status_pesanan'];
            if($s==='Menunggu')       echo "<span class='status-badge status-menunggu'>Menunggu</span>";
            elseif($s==='Diproses')   echo "<span class='status-badge status-diproses'>Diproses</span>";
            elseif($s==='Siap Diambil') echo "<span class='status-badge status-siap'>Siap Diambil</span>";
            elseif($s==='Selesai')    echo "<span class='status-badge status-selesai'>Selesai</span>";
            else                      echo "<span class='status-badge status-batal'>Dibatalkan</span>";
            ?>
          </td>
          <td>
            <div class="aksi-cell">
              <a class="btn-aksi btn-detail" href="detail_pemesanan.php?id=<?= $data['id_pemesanan'] ?>">🔍 Detail</a>
              <a class="btn-aksi btn-invoice" href="invoice_pdf.php?id=<?= $data['id_pemesanan'] ?>" target="_blank">🧾 Invoice</a>
              <a class="btn-aksi btn-edit" href="edit_pemesanan.php?id=<?= $data['id_pemesanan'] ?>">✏️ Edit</a>
              <a class="btn-aksi btn-hapus" href="hapus_pemesanan.php?id=<?= $data['id_pemesanan'] ?>&csrf=<?= urlencode($csrf) ?>" onclick="return confirm('Yakin ingin hapus pesanan ini?')">🗑️ Hapus</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
    <div class="empty-state">
      <div class="empty-icon">📭</div>
      <p>Belum ada data pemesanan yang masuk.</p>
    </div>
    <?php endif; ?>
  </div>

</div>

<!-- FOOTER -->
<div class="site-footer">
  © 2026 YOLAZCAKE Sintang • Cafe • Bakery • Boutique<br>
  Jl. Lintas Melawi, Ladang, Sintang, Kalimantan Barat<br>
  WA: 0815-7815-7888
</div>

<script>
  /* Hero sparkles */
  (function(){
    const hero = document.getElementById('pageHero');
    const colors=['#D4AF37','#FFE4B5','#E8A0BF','#fff','#f9ce34','#b8860b'];
    for(let i=0;i<22;i++){
      const d=document.createElement('div');d.className='sparkle';
      const s=Math.random()*5+2;
      d.style.cssText=`width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;bottom:${Math.random()*30}%;animation-duration:${4+Math.random()*7}s;animation-delay:${Math.random()*5}s;opacity:0;`;
      hero.appendChild(d);
    }
  })();

  /* Background particles */
  (function(){
    const c=document.getElementById('particles');
    const colors=['rgba(212,175,55,.4)','rgba(232,160,191,.3)','rgba(255,255,255,.15)'];
    for(let i=0;i<16;i++){
      const p=document.createElement('div');p.className='particle';
      const s=Math.random()*5+2;
      p.style.cssText=`width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;animation-duration:${10+Math.random()*12}s;animation-delay:${Math.random()*10}s;`;
      c.appendChild(p);
    }
  })();

  /* Search + Filter */
  function filterTable(){
    const keyword = document.getElementById('searchInput').value.toLowerCase();
    const status  = document.getElementById('filterStatus').value.toLowerCase();
    const rows    = document.querySelectorAll('#mainTable tbody tr');
    rows.forEach(row=>{
      const nama  = row.dataset.nama  || '';
      const kode  = row.dataset.kode  || '';
      const st    = (row.dataset.status || '').toLowerCase();
      const matchSearch = nama.includes(keyword) || kode.includes(keyword);
      const matchStatus = !status || st === status;
      row.style.display = (matchSearch && matchStatus) ? '' : 'none';
    });
  }
</script>

</body>
</html>
