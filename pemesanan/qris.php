<?php
session_start();
include '../config/koneksi.php';
require_once '../config/promo_helper.php';

if(isset($_POST['nama_pemesan'])){
    $_SESSION['nama_pemesan'] = $_POST['nama_pemesan'];
}
if(isset($_POST['no_hp'])){
    $_SESSION['no_hp'] = $_POST['no_hp'];
}
$_SESSION['id_booking'] = $_POST['id_booking'] ?? ($_SESSION['id_booking'] ?? null);

$nama    = $_SESSION['nama_pemesan'] ?? '';
$no_hp   = $_SESSION['no_hp'] ?? '';

// Hitung total harga sebenarnya dari keranjang (bukan cuma jumlah pcs),
// lalu terapkan kode promo yang sudah divalidasi di halaman checkout.
$subtotal_keranjang = 0;
if(!empty($_SESSION['keranjang'])){
    $stmtP = $conn->prepare("SELECT harga FROM produk WHERE id_produk=?");
    foreach($_SESSION['keranjang'] as $id_produk => $jml){
        $id_produk_int = (int)$id_produk;
        $stmtP->bind_param("i", $id_produk_int);
        $stmtP->execute();
        $p = $stmtP->get_result()->fetch_assoc();
        if($p){ $subtotal_keranjang += $p['harga'] * $jml; }
    }
    $stmtP->close();
}

$diskon_nominal = 0;
$kode_promo_aktif = null;
if(!empty($_SESSION['checkout_promo'])){
    $cek = cek_promo($conn, $_SESSION['checkout_promo']['kode_promo'], $subtotal_keranjang);
    if($cek['ok']){
        $diskon_nominal   = $cek['diskon_nominal'];
        $kode_promo_aktif = $cek['promo']['kode_promo'];
    }
}
$total_keranjang = $subtotal_keranjang - $diskon_nominal;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pembayaran QRIS – YOLAZCAKE</title>
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
      display:inline-flex;align-items:center;gap:8px;
      margin:32px auto 0;padding:0 32px;max-width:800px;width:100%;
    }
    .back-link a{
      font-size:.82em;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;
      color:rgba(212,175,55,.85);text-decoration:none;
      border:1px solid rgba(212,175,55,.3);padding:7px 18px;border-radius:999px;
      transition:all .3s;background:rgba(212,175,55,.06);
    }
    .back-link a:hover{
      background:rgba(212,175,55,.16);border-color:rgba(212,175,55,.7);
      box-shadow:0 0 18px rgba(212,175,55,.25);color:#D4AF37;
    }

    /* ── PAGE WRAPPER ── */
    .page-wrapper{
      position:relative;z-index:1;
      display:flex;flex-direction:column;align-items:center;
      padding:28px 20px 120px;
      max-width:800px;margin:0 auto;
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
    .status-badge{
      display:inline-flex;align-items:center;gap:8px;
      background:linear-gradient(135deg,rgba(99,250,180,.15),rgba(99,250,180,.06));
      border:1px solid rgba(99,250,180,.35);
      color:#6efabc;font-size:.78em;font-weight:600;letter-spacing:1.5px;
      padding:6px 18px;border-radius:999px;
    }
    .status-badge .dot{
      width:7px;height:7px;border-radius:50%;background:#6efabc;
      animation:pulseDot 1.8s ease-in-out infinite;
    }
    @keyframes pulseDot{0%,100%{opacity:1;transform:scale(1);}50%{opacity:.4;transform:scale(.7);}}

    /* ── GOLD RULE ── */
    .gold-rule{display:flex;align-items:center;gap:12px;width:100%;margin-bottom:28px;}
    .gold-rule::before,.gold-rule::after{content:'';flex:1;height:1px;background:linear-gradient(to right,transparent,rgba(212,175,55,.4));}
    .gold-rule::after{background:linear-gradient(to left,transparent,rgba(212,175,55,.4));}
    .gold-rule span{color:#D4AF37;font-size:.65em;letter-spacing:3px;white-space:nowrap;}

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
      background:linear-gradient(135deg,rgba(99,250,180,.25),rgba(99,250,180,.12));
      border:1px solid rgba(99,250,180,.4);
      color:#6efabc;
    }
    .btn-green:hover{
      background:linear-gradient(135deg,rgba(99,250,180,.35),rgba(99,250,180,.2));
      box-shadow:0 8px 28px rgba(99,250,180,.2);
    }

    /* ── PEMESAN INFO CARD ── */
    .info-card{
      width:100%;
      background:rgba(212,175,55,.07);
      border:1px solid rgba(212,175,55,.22);
      border-radius:20px;padding:22px 28px;
      display:flex;align-items:center;gap:20px;flex-wrap:wrap;
      margin-bottom:24px;
      opacity:0;animation:fadeSlideDown .8s forwards .4s;
    }
    .info-icon{font-size:2em;flex-shrink:0;}
    .info-text{flex:1;}
    .info-text .info-label{font-size:.72em;letter-spacing:1.5px;text-transform:uppercase;color:#D4AF37;margin-bottom:4px;}
    .info-text .info-name{font-family:'Playfair Display',serif;font-size:1.1em;font-weight:700;color:#fff;}
    .info-text .info-hp{font-size:.85em;color:rgba(255,255,255,.55);margin-top:2px;}
    .info-divider{width:1px;height:40px;background:rgba(212,175,55,.25);flex-shrink:0;}

    /* ── STEPS ── */
    .steps-row{
      width:100%;display:grid;grid-template-columns:repeat(3,1fr);gap:14px;
      margin-bottom:28px;
    }
    .step-card{
      background:rgba(255,255,255,.05);
      border:1px solid rgba(255,255,255,.09);
      border-radius:16px;padding:18px 16px;text-align:center;
      opacity:0;transform:translateY(20px);
      transition:border-color .4s,box-shadow .4s;
    }
    .step-card.visible{opacity:1;transform:translateY(0);}
    .step-card:hover{
      border-color:rgba(212,175,55,.3);
      box-shadow:0 10px 30px rgba(0,0,0,.25),0 0 20px rgba(212,175,55,.12);
    }
    .step-num{
      width:36px;height:36px;border-radius:50%;
      background:linear-gradient(135deg,#D4AF37,#b8860b);
      color:#1e0e3a;font-weight:800;font-size:.85em;
      display:flex;align-items:center;justify-content:center;
      margin:0 auto 10px;
      box-shadow:0 4px 16px rgba(212,175,55,.35);
    }
    .step-icon{font-size:1.6em;margin-bottom:8px;}
    .step-label{font-size:.78em;color:rgba(255,255,255,.6);line-height:1.4;}
    .step-label strong{display:block;color:#fff;margin-bottom:2px;font-size:1.05em;}

    /* ── QRIS CARD ── */
    .qris-card{
      width:100%;
      background:rgba(255,255,255,.06);
      backdrop-filter:blur(20px);
      border:1px solid rgba(255,255,255,.1);
      border-radius:24px;
      position:relative;overflow:hidden;
      padding:36px;
      display:flex;flex-direction:column;align-items:center;
      opacity:0;animation:fadeSlideDown .9s forwards .6s;
      margin-bottom:24px;
    }
    .qris-card::before{
      content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);
      background-size:200% 100%;animation:goldSlide 4s linear infinite;
    }
    .qris-card::after{
      content:'';position:absolute;inset:0;
      background:radial-gradient(ellipse at 50% 0%,rgba(212,175,55,.06) 0%,transparent 65%);
      pointer-events:none;
    }

    .qris-label{
      font-family:'Playfair Display',serif;font-size:1.3em;font-weight:700;
      color:#fff;margin-bottom:6px;text-align:center;
    }
    .qris-sub{font-size:.83em;color:rgba(255,255,255,.5);margin-bottom:28px;text-align:center;}

    /* QR Frame */
    .qr-frame{
      position:relative;
      width:280px;height:280px;
      display:flex;align-items:center;justify-content:center;
      margin-bottom:28px;
    }
    .qr-frame::before,.qr-frame::after{
      content:'';position:absolute;
      width:30px;height:30px;
      border-color:#D4AF37;border-style:solid;
    }
    .qr-frame::before{top:-4px;left:-4px;border-width:3px 0 0 3px;border-radius:4px 0 0 0;}
    .qr-frame::after{top:-4px;right:-4px;border-width:3px 3px 0 0;border-radius:0 4px 0 0;}
    .qr-corner-bl,.qr-corner-br{
      position:absolute;width:30px;height:30px;
      border-color:#D4AF37;border-style:solid;
    }
    .qr-corner-bl{bottom:-4px;left:-4px;border-width:0 0 3px 3px;border-radius:0 0 0 4px;}
    .qr-corner-br{bottom:-4px;right:-4px;border-width:0 3px 3px 0;border-radius:0 0 4px 0;}

    .qr-frame img{
      width:260px;height:260px;object-fit:contain;
      border-radius:16px;
      border:2px solid rgba(212,175,55,.2);
      background:#fff;padding:10px;
    }

    /* Scan animation ring */
    .qr-scan-line{
      position:absolute;left:6px;right:6px;height:2px;
      background:linear-gradient(to right,transparent,#D4AF37,transparent);
      animation:scanLine 3s ease-in-out infinite;
      box-shadow:0 0 10px rgba(212,175,55,.6);
    }
    @keyframes scanLine{
      0%{top:10px;opacity:0;}
      10%{opacity:1;}
      90%{opacity:1;}
      100%{top:265px;opacity:0;}
    }

    .qris-note{
      font-size:.8em;color:rgba(255,255,255,.45);text-align:center;
      background:rgba(255,255,255,.04);
      border:1px solid rgba(255,255,255,.07);
      border-radius:10px;padding:10px 20px;
      margin-bottom:28px;line-height:1.6;
    }
    .qris-note span{color:#D4AF37;}

    /* ── CONFIRM CARD ── */
    .confirm-card{
      width:100%;
      background:linear-gradient(135deg,rgba(99,250,180,.07),rgba(99,250,180,.02));
      border:1px solid rgba(99,250,180,.2);
      border-radius:20px;padding:24px 28px;
      opacity:0;animation:fadeSlideDown .9s forwards .85s;
    }
    .confirm-title{
      font-family:'Playfair Display',serif;font-size:1em;font-weight:700;
      color:#6efabc;margin-bottom:14px;
      display:flex;align-items:center;gap:8px;
    }
    .confirm-checklist{
      display:flex;flex-direction:column;gap:8px;margin-bottom:20px;
    }
    .confirm-check{
      display:flex;align-items:flex-start;gap:10px;
      font-size:.85em;color:rgba(255,255,255,.65);line-height:1.4;
    }
    .check-icon{color:#6efabc;flex-shrink:0;margin-top:1px;}

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
    @media(max-width:600px){
      .hero-inner h1{font-size:2em;}
      .steps-row{grid-template-columns:1fr;}
      .qris-card{padding:24px 18px;}
      .qr-frame{width:240px;height:240px;}
      .qr-frame img{width:220px;height:220px;}
      .info-divider{display:none;}
    }
  </style>
</head>
<body>

<div id="particles"></div>

<!-- ═══════════════ HERO ═══════════════ -->
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow">✦ YOLAZCAKE Sintang ✦</p>
    <h1>Pembayaran QRIS</h1>
    <p class="hero-sub">Scan kode QR dan selesaikan pembayaranmu dengan mudah</p>
    <div class="hero-divider">
      <span></span><span class="diamond">✦ ✦ ✦</span><span></span>
    </div>
  </div>
</div>

<div class="back-link">
  <a href="keranjang.php">← Kembali ke Keranjang</a>
</div>

<!-- ═══════════════ PAGE WRAPPER ═══════════════ -->
<div class="page-wrapper">

  <!-- TOP BAR -->
  <div class="top-bar">
    <div>
      <div class="status-badge"><span class="dot"></span> Menunggu Pembayaran</div>
      <h2 class="section-title" style="margin-top:10px;">Selesaikan Pembayaran</h2>
    </div>
  </div>

  <!-- INFO PEMESAN -->
  <?php if($nama || $no_hp): ?>
  <div class="info-card">
    <div class="info-icon">👤</div>
    <div class="info-text">
      <div class="info-label">Nama Pemesan</div>
      <div class="info-name"><?= htmlspecialchars($nama ?: '–') ?></div>
    </div>
    <?php if($no_hp): ?>
    <div class="info-divider"></div>
    <div class="info-text">
      <div class="info-label">Nomor WhatsApp</div>
      <div class="info-name"><?= htmlspecialchars($no_hp) ?></div>
      <div class="info-hp">Konfirmasi pesanan via WA</div>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <div class="gold-rule"><span>✦ ✦ ✦</span></div>

  <!-- ─── STEPS ─── -->
  <div class="steps-row">
    <div class="step-card">
      <div class="step-num">1</div>
      <div class="step-icon">📱</div>
      <div class="step-label"><strong>Buka Aplikasi</strong>Buka aplikasi e-wallet atau mobile banking kamu</div>
    </div>
    <div class="step-card">
      <div class="step-num">2</div>
      <div class="step-icon">📷</div>
      <div class="step-label"><strong>Scan QRIS</strong>Arahkan kamera ke kode QR di bawah ini</div>
    </div>
    <div class="step-card">
      <div class="step-num">3</div>
      <div class="step-icon">✅</div>
      <div class="step-label"><strong>Konfirmasi</strong>Klik tombol "Saya Sudah Bayar" setelah berhasil</div>
    </div>
  </div>

  <!-- ─── QRIS CARD ─── -->
  <div class="qris-card">
    <div class="qris-label">✦ Kode Pembayaran QRIS</div>
    <div class="qris-sub">Berlaku untuk semua e-wallet & mobile banking</div>

    <div class="qr-frame">
      <div class="qr-corner-bl"></div>
      <div class="qr-corner-br"></div>
      <div class="qr-scan-line"></div>
      <img src="../assets/img/image.png"
           alt="QRIS YOLAZCAKE"
           onerror="this.style.padding='24px';this.style.fontSize='5em';this.outerHTML='<div style=\'width:260px;height:260px;background:rgba(255,255,255,.1);border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:5em;\'>📱</div>'">
    </div>

    <div class="qris-note">
      Scan QRIS ini menggunakan <span>GoPay, OVO, Dana, ShopeePay, BCA, BNI, BRI,</span> atau aplikasi e-wallet lainnya<br>
      yang mendukung pembayaran QRIS
    </div>

    <div style="margin-top:20px;padding:16px 20px;background:rgba(212,175,55,.08);border:1px solid rgba(212,175,55,.25);border-radius:14px;text-align:center;">
      <div style="font-size:.72em;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.5);">Total yang Harus Dibayar</div>
      <div style="font-family:'Playfair Display',serif;font-size:1.6em;font-weight:700;color:#D4AF37;margin-top:4px;">Rp <?= number_format($total_keranjang,0,',','.') ?></div>
      <?php if($kode_promo_aktif): ?>
        <div style="font-size:.75em;color:#6efabc;margin-top:4px;">✓ Sudah termasuk diskon kode <?= htmlspecialchars($kode_promo_aktif) ?> (-Rp <?= number_format($diskon_nominal,0,',','.') ?>)</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ─── CONFIRM CARD ─── -->
  <div class="confirm-card">
    <div class="confirm-title">✦ Checklist Sebelum Konfirmasi</div>
    <div class="confirm-checklist">
      <div class="confirm-check">
        <span class="check-icon">✓</span>
        <span>Pastikan nominal pembayaran sudah benar sesuai total pesananmu</span>
      </div>
      <div class="confirm-check">
        <span class="check-icon">✓</span>
        <span>Simpan bukti pembayaran dari aplikasimu sebagai referensi</span>
      </div>
      <div class="confirm-check">
        <span class="check-icon">✓</span>
        <span>Tekan tombol di bawah setelah pembayaran berhasil dikonfirmasi</span>
      </div>
    </div>
    <form action="proses_pemesanan.php" method="POST">
      <button type="submit" name="bayar" class="btn-premium btn-gold"
              style="width:100%;justify-content:center;padding:16px 28px;font-size:.9em;">
        ✓ &nbsp;Saya Sudah Bayar
      </button>
    </form>
  </div>

</div>

<!-- ═══════════════ FOOTER ═══════════════ -->
<div class="site-footer">
  © 2026 YOLAZCAKE Sintang • Cafe • Bakery • Boutique<br>
  Jl. Lintas Melawi, Ladang, Sintang, Kalimantan Barat<br>
  WA: 0815-7815-7888
</div>

<script>
  /* Hero sparkles */
  (function(){
    const hero = document.getElementById('pageHero');
    const colors = ['#D4AF37','#FFE4B5','#E8A0BF','#fff','#f9ce34','#b8860b'];
    for(let i=0;i<22;i++){
      const d = document.createElement('div'); d.className='sparkle';
      const s = Math.random()*5+2;
      d.style.cssText=`width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;bottom:${Math.random()*30}%;animation-duration:${4+Math.random()*7}s;animation-delay:${Math.random()*5}s;opacity:0;`;
      hero.appendChild(d);
    }
  })();

  /* Background particles */
  (function(){
    const c = document.getElementById('particles');
    const colors=['rgba(212,175,55,.4)','rgba(232,160,191,.3)','rgba(255,255,255,.15)'];
    for(let i=0;i<16;i++){
      const p=document.createElement('div');p.className='particle';
      const s=Math.random()*5+2;
      p.style.cssText=`width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;animation-duration:${10+Math.random()*12}s;animation-delay:${Math.random()*10}s;`;
      c.appendChild(p);
    }
  })();

  /* Step cards reveal */
  (function(){
    const cards = document.querySelectorAll('.step-card');
    const io = new IntersectionObserver(entries=>{
      entries.forEach((e,i)=>{
        if(e.isIntersecting){
          setTimeout(()=>e.target.classList.add('visible'), i*120);
          io.unobserve(e.target);
        }
      });
    },{threshold:0.1});
    cards.forEach(c=>io.observe(c));
  })();

  /* Confirm button feedback */
  document.querySelector('form button[name="bayar"]').addEventListener('click', function(){
    this.textContent = '⏳  Memproses Pesanan…';
    this.style.opacity = '.7';
    this.style.pointerEvents = 'none';
  });
</script>

</body>
</html>
