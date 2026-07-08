<?php
session_start();
require_once __DIR__.'/../config/staff_guard.php';
require_staff_login();
include '../config/koneksi.php';
require_once '../config/qr_helper.php';

$query = mysqli_query($conn, "SELECT * FROM meja WHERE status != 'Tidak Aktif' ORDER BY nomor_meja ASC");
$base  = base_app_url();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="../assets/css/lucide-icons.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>QR Meja – YOLAZCAKE</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    :root{
      --gold:#D4AF37;--gold-d:#b8860b;--rose:#ee2a7b;
      --glass:rgba(255,255,255,0.05);--muted:rgba(255,255,255,0.5);
    }
    body{min-height:100vh;font-family:'Inter',sans-serif;
      background:linear-gradient(160deg,#1e0e3a 0%,#2d1560 50%,#1a0a2e 100%);
      color:#fff;overflow-x:hidden;}
    body::before{content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
      background:radial-gradient(ellipse at 20% 30%,rgba(212,175,55,.10) 0%,transparent 55%),
                 radial-gradient(ellipse at 80% 70%,rgba(232,160,191,.10) 0%,transparent 55%);}

    .page-hero{position:relative;height:200px;display:flex;flex-direction:column;
      align-items:center;justify-content:center;overflow:hidden;
      background:linear-gradient(135deg,#2b1a11 0%,#4a2c1a 40%,#6d3e26 70%,#3a1f0e 100%);z-index:1;}
    .hero-inner{position:relative;z-index:2;text-align:center;}
    .hero-eyebrow{font-size:.72em;font-weight:500;letter-spacing:5px;text-transform:uppercase;color:var(--gold);margin-bottom:8px;}
    .hero-inner h1{font-family:'Playfair Display',serif;font-size:2.4em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,var(--gold) 60%,#FFE4B5 80%,#fff);
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
    .hero-sub{font-size:.85em;color:rgba(255,255,255,.65);margin-top:8px;}

    .page-wrapper{position:relative;z-index:1;padding:32px 28px 80px;max-width:1200px;margin:0 auto;}

    .btn-back{display:inline-flex;align-items:center;gap:8px;padding:10px 22px;
      background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.3);color:var(--gold);
      font-size:.82em;font-weight:600;letter-spacing:1px;border-radius:999px;text-decoration:none;
      margin-bottom:24px;transition:transform .25s,background .3s;}
    .btn-back:hover{transform:translateX(-3px);background:rgba(212,175,55,.2);}

    .info-box{background:rgba(212,175,55,.08);border:1px solid rgba(212,175,55,.25);
      border-radius:16px;padding:16px 22px;margin-bottom:28px;font-size:.85em;
      color:rgba(255,255,255,.75);line-height:1.7;display:flex;gap:12px;align-items:flex-start;}
    .info-box i{color:var(--gold);flex-shrink:0;margin-top:2px;}

    .qr-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:20px;}
    .qr-card{background:var(--glass);backdrop-filter:blur(20px);
      border:1px solid rgba(255,255,255,.1);border-radius:20px;padding:22px;text-align:center;
      transition:transform .3s,border-color .3s,box-shadow .3s;}
    .qr-card:hover{transform:translateY(-5px);border-color:rgba(212,175,55,.3);box-shadow:0 12px 36px rgba(212,175,55,.15);}
    .qr-card img{width:180px;height:180px;border-radius:12px;background:#fff;padding:8px;margin-bottom:14px;}
    .qr-meja-num{font-family:'Playfair Display',serif;font-size:1.5em;font-weight:700;color:var(--gold);}
    .qr-meja-cap{font-size:.78em;color:var(--muted);margin:4px 0 12px;}
    .qr-actions{display:flex;gap:8px;justify-content:center;flex-wrap:wrap;}
    .btn-act{display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:8px;
      font-size:.72em;font-weight:600;text-decoration:none;border:1px solid transparent;cursor:pointer;
      transition:transform .2s;background:rgba(212,175,55,.18);border-color:rgba(212,175,55,.4);color:var(--gold);}
    .btn-act:hover{transform:translateY(-2px);background:rgba(212,175,55,.32);}
    .qr-link{font-size:.65em;color:rgba(255,255,255,.4);margin-top:10px;word-break:break-all;}

    @media print{
      body::before,.page-hero,.btn-back,.info-box{display:none;}
      body{background:#fff;color:#000;}
      .qr-card{border:2px dashed #999;break-inside:avoid;box-shadow:none;}
      .qr-actions{display:none;}
      .qr-meja-num,.qr-meja-cap{color:#000;}
      .qr-link{color:#333;}
    }
  </style>
</head>
<body>

<div class="page-hero">
  <div class="hero-inner">
    <p class="hero-eyebrow"><i data-lucide="qr-code" class="lucide-ic"></i> YOLAZCAKE Sintang</p>
    <h1>QR Meja — Pesan dari HP</h1>
    <p class="hero-sub">Cetak & tempel di tiap meja supaya pelanggan bisa pesan langsung tanpa panggil pelayan</p>
  </div>
</div>

<div class="page-wrapper">
  <a href="data_meja.php" class="btn-back"><i data-lucide="arrow-left" class="lucide-ic"></i> Kembali ke Data Meja</a>

  <div class="info-box">
    <i data-lucide="info" class="lucide-ic"></i>
    <span>Scan QR di meja akan membuka halaman menu dengan nomor meja otomatis terisi. Pelanggan tinggal pilih menu, checkout, dan bayar QRIS — status meja otomatis berubah jadi <strong>Terisi</strong> saat pesanan masuk. Klik <strong>Cetak Semua</strong> untuk print seluruh QR sekaligus.</span>
  </div>

  <div style="margin-bottom:20px;">
    <button class="btn-act" style="padding:10px 22px;font-size:.8em;" onclick="window.print()">
      <i data-lucide="printer" class="lucide-ic"></i> Cetak Semua QR
    </button>
  </div>

  <div class="qr-grid">
    <?php if(mysqli_num_rows($query) > 0): while($m = mysqli_fetch_assoc($query)):
      $link = $base.'/pemesanan/menuu.php?meja='.urlencode($m['nomor_meja']);
      $qr   = qr_image_url($link, 260);
    ?>
    <div class="qr-card">
      <img src="<?= htmlspecialchars($qr) ?>" alt="QR Meja <?= htmlspecialchars($m['nomor_meja']) ?>" loading="lazy">
      <div class="qr-meja-num"><i data-lucide="armchair" class="lucide-ic"></i> <?= htmlspecialchars($m['nomor_meja']) ?></div>
      <div class="qr-meja-cap">Kapasitas <?= (int)$m['kapasitas'] ?> orang</div>
      <div class="qr-actions">
        <a href="<?= htmlspecialchars($qr) ?>" download="QR_<?= htmlspecialchars($m['nomor_meja']) ?>.png" class="btn-act">
          <i data-lucide="download" class="lucide-ic"></i> Unduh
        </a>
        <a href="<?= htmlspecialchars($link) ?>" target="_blank" class="btn-act">
          <i data-lucide="external-link" class="lucide-ic"></i> Tes Link
        </a>
      </div>
      <div class="qr-link"><?= htmlspecialchars($link) ?></div>
    </div>
    <?php endwhile; else: ?>
    <p style="color:rgba(255,255,255,.5);">Belum ada meja aktif. Tambah meja dulu di halaman Data Meja.</p>
    <?php endif; ?>
  </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide){lucide.createIcons();}</script>
</body>
</html>
