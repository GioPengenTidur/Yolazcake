<?php
session_start();
include '../config/koneksi.php';
require_once '../config/promo_helper.php';

// Redirect jika keranjang kosong
if(empty($_SESSION['keranjang'])){
  header("Location: menuu.php");
  exit();
}

// Hitung total & ambil data produk
$items = [];
$total = 0;
$stmtProduk = $conn->prepare("SELECT * FROM produk WHERE id_produk=?");
foreach($_SESSION['keranjang'] as $id_produk => $jumlah){
  $id_produk_int = (int)$id_produk;
  $stmtProduk->bind_param("i", $id_produk_int);
  $stmtProduk->execute();
  $p = $stmtProduk->get_result()->fetch_assoc();
  $subtotal       = $p['harga'] * $jumlah;
  $total         += $subtotal;
  $items[]        = array_merge($p, ['jumlah'=>$jumlah,'subtotal'=>$subtotal]);
}
$stmtProduk->close();
$ada_booking = isset($_SESSION['id_booking']) && $_SESSION['id_booking'];
$meja_aktif  = $_SESSION['meja_aktif'] ?? null; // dari scan QR meja

// ── KODE PROMO ──
$promo_error = null;

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['terapkan_promo'])){
  $hasil = cek_promo($conn, $_POST['kode_promo'] ?? '', $total);
  if($hasil['ok']){
    $_SESSION['checkout_promo'] = [
      'kode_promo' => $hasil['promo']['kode_promo'],
    ];
  } else {
    $promo_error = $hasil['pesan'];
    unset($_SESSION['checkout_promo']);
  }
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_promo'])){
  unset($_SESSION['checkout_promo']);
}

// Validasi ulang promo yang sedang aktif di session terhadap subtotal
// saat ini (jaga-jaga kalau isi keranjang berubah setelah promo diklaim).
$promo_aktif    = null;
$diskon_nominal = 0;
if(!empty($_SESSION['checkout_promo'])){
  $cek = cek_promo($conn, $_SESSION['checkout_promo']['kode_promo'], $total);
  if($cek['ok']){
    $promo_aktif    = $cek['promo'];
    $diskon_nominal = $cek['diskon_nominal'];
  } else {
    $promo_error = $promo_error ?? $cek['pesan'];
    unset($_SESSION['checkout_promo']);
  }
}
$total_bayar = $total - $diskon_nominal;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="../assets/css/lucide-icons.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout – YOLAZCAKE</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}

    body{
      min-height:100vh;
      font-family:'Inter',sans-serif;
      background:linear-gradient(160deg,#1e0e3a 0%,#2d1560 50%,#1a0a2e 100%);
      position:relative;overflow-x:hidden;
    }
    body::before{
      content:'';position:fixed;inset:0;
      background:
        radial-gradient(ellipse at 20% 30%,rgba(212,175,55,.10) 0%,transparent 55%),
        radial-gradient(ellipse at 80% 70%,rgba(232,160,191,.10) 0%,transparent 55%);
      pointer-events:none;z-index:0;
    }

    /* ── HERO ── */
    .page-hero{
      position:relative;height:240px;
      display:flex;flex-direction:column;align-items:center;justify-content:center;
      overflow:hidden;
      background:linear-gradient(135deg,#2b1a11 0%,#4a2c1a 40%,#6d3e26 70%,#3a1f0e 100%);
      z-index:1;
    }
    .page-hero::before{
      content:'';position:absolute;inset:0;
      background:
        radial-gradient(ellipse at 30% 50%,rgba(212,175,55,.18) 0%,transparent 60%),
        radial-gradient(ellipse at 75% 40%,rgba(232,160,191,.15) 0%,transparent 55%);
      animation:heroAurora 8s ease-in-out infinite alternate;
    }
    @keyframes heroAurora{0%{opacity:.6;transform:scale(1);}100%{opacity:1;transform:scale(1.08) translateX(10px);}}

    .sparkle{position:absolute;border-radius:50%;pointer-events:none;animation:floatDot linear infinite;}
    @keyframes floatDot{0%{transform:translateY(0) rotate(0deg);opacity:0;}20%{opacity:1;}80%{opacity:.8;}100%{transform:translateY(-260px) rotate(360deg);opacity:0;}}

    .hero-inner{position:relative;z-index:2;text-align:center;color:#fff;}
    .hero-eyebrow{
      font-size:.72em;font-weight:500;letter-spacing:5px;text-transform:uppercase;
      color:#D4AF37;margin-bottom:10px;
      opacity:0;animation:fadeSlideDown .8s forwards .3s;
    }
    .hero-inner h1{
      font-family:'Playfair Display',serif;font-size:2.6em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,#D4AF37 60%,#FFE4B5 80%,#fff);
      background-size:200% 100%;
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
      animation:shimmerText 4s ease-in-out infinite,fadeSlideDown .9s forwards .5s;
      opacity:0;
    }
    .hero-inner .hero-sub{
      font-size:.88em;color:rgba(255,255,255,.65);margin-top:10px;
      opacity:0;animation:fadeSlideDown .9s forwards .9s;
    }
    .hero-divider{
      margin-top:14px;display:flex;justify-content:center;align-items:center;gap:12px;
      opacity:0;animation:fadeSlideDown .9s forwards 1.1s;
    }
    .hero-divider span{display:block;width:60px;height:1px;background:linear-gradient(to right,transparent,#D4AF37);}
    .hero-divider span:last-child{background:linear-gradient(to left,transparent,#D4AF37);}
    .hero-divider .diamond{color:#D4AF37;font-size:.75em;letter-spacing:4px;}

    @keyframes shimmerText{0%{background-position:100% 0;}100%{background-position:-100% 0;}}
    @keyframes fadeSlideDown{from{opacity:0;transform:translateY(-18px);}to{opacity:1;transform:translateY(0);}}
    @keyframes goldSlide{0%{background-position:0% 0;}100%{background-position:200% 0;}}

    /* ── BREADCRUMB ── */
    .breadcrumb{
      position:relative;z-index:2;
      display:flex;align-items:center;gap:10px;flex-wrap:wrap;
      max-width:1100px;width:100%;margin:28px auto 0;padding:0 28px;
    }
    .breadcrumb a{
      font-size:.8em;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;
      color:rgba(212,175,55,.8);text-decoration:none;
      border:1px solid rgba(212,175,55,.28);padding:6px 16px;border-radius:999px;
      background:rgba(212,175,55,.06);transition:all .3s;
    }
    .breadcrumb a:hover{
      background:rgba(212,175,55,.15);border-color:rgba(212,175,55,.65);
      box-shadow:0 0 16px rgba(212,175,55,.22);color:#D4AF37;
    }
    .breadcrumb-sep{color:rgba(255,255,255,.25);font-size:.8em;}
    .breadcrumb-cur{
      font-size:.8em;color:rgba(255,255,255,.5);letter-spacing:1px;
    }

    /* ── STEP INDICATOR ── */
    .steps-bar{
      position:relative;z-index:2;
      max-width:1100px;width:100%;margin:24px auto 0;padding:0 28px;
      display:flex;align-items:center;justify-content:center;gap:0;
    }
    .step{
      display:flex;flex-direction:column;align-items:center;gap:6px;
      flex:1;max-width:160px;
    }
    .step-circle{
      width:36px;height:36px;border-radius:50%;
      display:flex;align-items:center;justify-content:center;
      font-size:.75em;font-weight:700;transition:all .3s;
      position:relative;z-index:1;
    }
    .step.done .step-circle{
      background:linear-gradient(135deg,#6efabc,#2dd4a0);
      color:#1e0e3a;
      box-shadow:0 0 16px rgba(99,250,180,.35);
    }
    .step.active .step-circle{
      background:linear-gradient(135deg,#D4AF37,#b8860b);
      color:#1e0e3a;
      box-shadow:0 0 20px rgba(212,175,55,.45),0 0 40px rgba(212,175,55,.2);
      animation:pulseDot 2s ease-in-out infinite;
    }
    .step.pending .step-circle{
      background:rgba(255,255,255,.07);
      border:1px solid rgba(255,255,255,.15);
      color:rgba(255,255,255,.35);
    }
    @keyframes pulseDot{
      0%,100%{box-shadow:0 0 20px rgba(212,175,55,.45),0 0 0 0 rgba(212,175,55,.4);}
      50%{box-shadow:0 0 20px rgba(212,175,55,.45),0 0 0 8px rgba(212,175,55,0);}
    }
    .step-label{
      font-size:.68em;font-weight:500;letter-spacing:1px;text-transform:uppercase;
      color:rgba(255,255,255,.4);text-align:center;
    }
    .step.done  .step-label{color:#6efabc;}
    .step.active .step-label{color:#D4AF37;}
    .step-line{
      flex:1;height:2px;margin-bottom:22px;
      background:rgba(255,255,255,.1);
      position:relative;
    }
    .step-line.done{background:linear-gradient(to right,#6efabc,#D4AF37);}

    /* ── PAGE WRAPPER ── */
    .page-wrapper{
      position:relative;z-index:1;
      max-width:1100px;margin:0 auto;
      padding:32px 28px 100px;
    }

    /* ── TWO-COL LAYOUT ── */
    .checkout-grid{
      display:grid;
      grid-template-columns:1fr 420px;
      gap:28px;
      align-items:start;
    }

    /* ── CARD BASE ── */
    .premium-card{
      background:rgba(255,255,255,.06);
      backdrop-filter:blur(20px);
      border:1px solid rgba(255,255,255,.1);
      border-radius:24px;
      position:relative;overflow:hidden;
      opacity:0;transform:translateY(36px);
      animation:cardReveal .85s cubic-bezier(.22,.68,0,1.2) forwards;
      transition:border-color .4s,box-shadow .4s;
    }
    .premium-card:hover{
      border-color:rgba(212,175,55,.3);
      box-shadow:0 20px 60px rgba(0,0,0,.3),0 0 30px rgba(212,175,55,.18);
    }
    .premium-card::before{
      content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);
      background-size:200% 100%;animation:goldSlide 4s linear infinite;
    }
    @keyframes cardReveal{to{opacity:1;transform:translateY(0);}}

    .card-left {animation-delay:.4s;}
    .card-right{animation-delay:.55s;}

    /* ── CARD HEADER ── */
    .card-header{
      padding:22px 26px 0;
      display:flex;align-items:center;gap:12px;
    }
    .card-header-icon{
      width:40px;height:40px;border-radius:12px;
      background:rgba(212,175,55,.12);
      border:1px solid rgba(212,175,55,.25);
      display:flex;align-items:center;justify-content:center;
      font-size:1.1em;flex-shrink:0;
    }
    .card-title{
      font-family:'Playfair Display',serif;
      font-size:1.15em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,#D4AF37 80%);
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
    }
    .card-subtitle{font-size:.75em;color:rgba(255,255,255,.4);margin-top:3px;}

    /* ── GOLD DIVIDER ── */
    .gold-div{
      height:1px;
      background:linear-gradient(to right,transparent,rgba(212,175,55,.35),transparent);
      margin:18px 26px;
    }

    /* ── ORDER ITEMS ── */
    .order-list{padding:0 26px;}
    .order-item{
      display:flex;align-items:center;gap:14px;
      padding:14px 0;
      border-bottom:1px solid rgba(255,255,255,.06);
      transition:background .2s;
    }
    .order-item:last-child{border-bottom:none;}
    .order-item:hover{background:rgba(212,175,55,.03);margin:0 -10px;padding:14px 10px;border-radius:10px;}

    .order-img{
      width:58px;height:58px;border-radius:10px;object-fit:cover;
      border:1px solid rgba(212,175,55,.2);flex-shrink:0;
    }
    .order-info{flex:1;min-width:0;}
    .order-name{
      font-family:'Playfair Display',serif;
      font-size:.9em;font-weight:600;color:#fff;
      white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
    }
    .order-qty{
      font-size:.75em;color:rgba(255,255,255,.45);margin-top:3px;
    }
    .order-price{
      font-size:.78em;color:rgba(255,255,255,.55);margin-top:2px;
    }
    .order-sub{
      font-size:.9em;font-weight:700;color:#D4AF37;
      white-space:nowrap;flex-shrink:0;
    }

    /* ── PROMO BOX ── */
    .promo-box{margin:0 26px 22px;}
    .promo-form{display:flex;gap:10px;}
    .promo-form input{
      flex:1;
      background:rgba(255,255,255,.06);
      border:1px solid rgba(255,255,255,.14);
      border-radius:12px;
      padding:12px 16px;
      font-family:'Inter',sans-serif;
      font-size:.85em;color:#fff;letter-spacing:1px;
      outline:none;transition:border-color .3s,background .3s;
    }
    .promo-form input::placeholder{color:rgba(255,255,255,.3);letter-spacing:normal;}
    .promo-form input:focus{border-color:rgba(212,175,55,.55);background:rgba(212,175,55,.06);}
    .promo-form input[readonly]{color:#6efabc;border-color:rgba(99,250,180,.35);background:rgba(99,250,180,.06);}
    .promo-form button{
      flex-shrink:0;padding:12px 20px;border:none;border-radius:12px;
      font-family:'Inter',sans-serif;font-size:.78em;font-weight:700;
      letter-spacing:1.5px;text-transform:uppercase;cursor:pointer;
      background:linear-gradient(135deg,#D4AF37,#b8860b);color:#1e0e3a;
      transition:transform .2s,box-shadow .2s;
    }
    .promo-form button:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(212,175,55,.3);}
    .promo-form button.btn-hapus{background:rgba(255,255,255,.08);color:rgba(255,255,255,.6);}
    .promo-msg{
      margin-top:10px;font-size:.78em;line-height:1.5;padding:10px 14px;
      border-radius:10px;
    }
    .promo-msg.error{background:rgba(255,80,80,.1);border:1px solid rgba(255,80,80,.3);color:#ff8080;}
    .promo-msg.success{background:rgba(99,250,180,.1);border:1px solid rgba(99,250,180,.3);color:#6efabc;}

    /* ── TOTAL SECTION ── */
    .total-wrap{
      margin:0 26px 22px;
      background:rgba(212,175,55,.07);
      border:1px solid rgba(212,175,55,.2);
      border-radius:16px;
      padding:18px 20px;
    }
    .total-row{
      display:flex;justify-content:space-between;align-items:center;
      font-size:.83em;color:rgba(255,255,255,.5);
      padding:5px 0;
    }
    .total-row.main{
      border-top:1px solid rgba(212,175,55,.2);
      margin-top:10px;padding-top:14px;
    }
    .total-row.main .label{
      font-family:'Playfair Display',serif;
      font-size:1.1em;font-weight:700;color:#fff;
    }
    .total-row.main .value{
      font-family:'Playfair Display',serif;
      font-size:1.35em;font-weight:700;color:#D4AF37;
      text-shadow:0 0 20px rgba(212,175,55,.4);
    }

    /* ── FORM STYLES ── */
    .form-wrap{padding:0 26px 26px;}
    .form-group{margin-bottom:20px;}
    .form-label{
      display:block;
      font-size:.72em;font-weight:600;letter-spacing:2px;text-transform:uppercase;
      color:rgba(212,175,55,.8);margin-bottom:8px;
    }
    .form-hint{
      font-size:.7em;color:rgba(255,255,255,.3);margin-top:5px;
    }

    /* Input wrapper */
    .input-wrap{
      position:relative;
    }
    .input-icon{
      position:absolute;left:14px;top:50%;transform:translateY(-50%);
      font-size:1em;pointer-events:none;
      color:rgba(212,175,55,.5);
      transition:color .3s;
    }
    .input-wrap:focus-within .input-icon{color:#D4AF37;}

    .form-input{
      width:100%;
      background:rgba(255,255,255,.06);
      border:1px solid rgba(255,255,255,.12);
      border-radius:12px;
      padding:13px 16px 13px 44px;
      font-family:'Inter',sans-serif;
      font-size:.9em;color:#fff;
      outline:none;
      transition:border-color .3s,box-shadow .3s,background .3s;
      -webkit-appearance:none;
    }
    .form-input::placeholder{color:rgba(255,255,255,.25);}
    .form-input:focus{
      border-color:rgba(212,175,55,.55);
      background:rgba(212,175,55,.06);
      box-shadow:0 0 0 3px rgba(212,175,55,.1),0 0 20px rgba(212,175,55,.08);
    }
    .form-input:valid:not(:placeholder-shown){
      border-color:rgba(99,250,180,.35);
    }

    /* Payment method badge */
    .pay-method{
      display:flex;align-items:center;gap:12px;
      background:rgba(255,255,255,.04);
      border:1px solid rgba(212,175,55,.2);
      border-radius:14px;padding:14px 18px;
      cursor:pointer;transition:all .3s;
    }
    .pay-method.selected{
      background:rgba(212,175,55,.1);
      border-color:rgba(212,175,55,.45);
      box-shadow:0 0 20px rgba(212,175,55,.12);
    }
    .pay-method-icon{
      width:44px;height:44px;border-radius:10px;
      background:rgba(212,175,55,.12);border:1px solid rgba(212,175,55,.25);
      display:flex;align-items:center;justify-content:center;font-size:1.3em;
    }
    .pay-method-info .name{
      font-weight:600;color:#fff;font-size:.88em;
    }
    .pay-method-info .desc{
      font-size:.73em;color:rgba(255,255,255,.4);margin-top:2px;
    }
    .pay-method-check{
      margin-left:auto;
      width:22px;height:22px;border-radius:50%;
      border:2px solid rgba(212,175,55,.35);
      display:flex;align-items:center;justify-content:center;
      transition:all .3s;flex-shrink:0;
    }
    .pay-method.selected .pay-method-check{
      background:#D4AF37;border-color:#D4AF37;
      box-shadow:0 0 12px rgba(212,175,55,.4);
    }
    .pay-method.selected .pay-method-check::after{
      content:'<i data-lucide="check" class="lucide-ic"></i>';color:#1e0e3a;font-size:.75em;font-weight:900;
    }

    /* ── SUBMIT BTN ── */
    .btn-submit{
      width:100%;
      position:relative;padding:16px 28px;border:none;border-radius:14px;
      font-family:'Inter',sans-serif;font-size:.88em;font-weight:700;
      letter-spacing:2px;text-transform:uppercase;cursor:pointer;
      overflow:hidden;
      background:linear-gradient(135deg,#D4AF37 0%,#b8860b 50%,#D4AF37 100%);
      background-size:200% 100%;
      animation:goldSlide 3s linear infinite;
      color:#1e0e3a;
      box-shadow:0 10px 35px rgba(212,175,55,.4),0 0 50px rgba(212,175,55,.2);
      transition:transform .25s cubic-bezier(.34,1.56,.64,1),box-shadow .35s;
      display:flex;align-items:center;justify-content:center;gap:10px;
      margin-top:6px;
    }
    .btn-submit::before{
      content:'';position:absolute;inset:0;
      background:linear-gradient(90deg,transparent,rgba(255,255,255,.2),transparent);
      transform:translateX(-100%);transition:transform .55s;
    }
    .btn-submit:hover::before{transform:translateX(100%);}
    .btn-submit:hover{
      transform:translateY(-3px) scale(1.01);
      box-shadow:0 14px 45px rgba(212,175,55,.55),0 0 70px rgba(212,175,55,.3);
    }
    .btn-submit:active{transform:translateY(-1px) scale(.99);}
    .btn-submit .arrow{
      transition:transform .3s;font-size:.9em;
    }
    .btn-submit:hover .arrow{transform:translateX(4px);}

    /* Back link */
    .btn-back{
      display:inline-flex;align-items:center;gap:8px;
      width:100%;
      padding:12px 20px;border-radius:12px;
      border:1px solid rgba(255,255,255,.12);
      background:rgba(255,255,255,.04);
      color:rgba(255,255,255,.55);
      font-size:.8em;font-weight:500;letter-spacing:1.2px;text-transform:uppercase;
      text-decoration:none;cursor:pointer;
      transition:all .3s;justify-content:center;margin-top:10px;
    }
    .btn-back:hover{
      border-color:rgba(255,255,255,.25);
      background:rgba(255,255,255,.08);
      color:rgba(255,255,255,.8);
    }

    /* ── SECURE BADGES ── */
    .secure-row{
      display:flex;align-items:center;justify-content:center;gap:16px;
      padding:14px 26px 20px;flex-wrap:wrap;
    }
    .secure-badge{
      display:flex;align-items:center;gap:5px;
      font-size:.68em;color:rgba(255,255,255,.35);letter-spacing:.5px;
    }
    .secure-badge span{font-size:1em;}

    /* ── ALREADY-BOOKED NOTICE ── */
    .booking-notice{
      display:flex;align-items:flex-start;gap:12px;
      background:rgba(99,250,180,.07);
      border:1px solid rgba(99,250,180,.25);
      border-radius:14px;padding:14px 18px;margin-bottom:20px;
    }
    .booking-notice .icon{font-size:1.2em;flex-shrink:0;margin-top:1px;}
    .booking-notice p{font-size:.8em;color:rgba(255,255,255,.6);line-height:1.5;}
    .booking-notice strong{color:#6efabc;}

    /* ── PARTICLES ── */
    .particle{position:fixed;border-radius:50%;pointer-events:none;animation:particleFloat linear infinite;z-index:0;}
    @keyframes particleFloat{
      0%{transform:translateY(100vh) scale(0);opacity:0;}10%{opacity:.5;}90%{opacity:.3;}
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
    @media(max-width:860px){
      .checkout-grid{grid-template-columns:1fr;}
      .card-right{order:-1;}
      .page-wrapper{padding:24px 18px 80px;}
      .hero-inner h1{font-size:2em;}
    }
    @media(max-width:480px){
      .steps-bar{gap:0;}
      .step-label{display:none;}
    }
  </style>
</head>
<body>

<div id="particles"></div>

<!-- ═══════════════ HERO ═══════════════ -->
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow"><i data-lucide="sparkle" class="lucide-ic"></i> YOLAZCAKE Sintang <i data-lucide="sparkle" class="lucide-ic"></i></p>
    <h1>Checkout</h1>
    <p class="hero-sub">Satu langkah lagi untuk menikmati hidangan kami</p>
    <div class="hero-divider">
      <span></span><span class="diamond"><i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i></span><span></span>
    </div>
  </div>
</div>

<!-- BREADCRUMB -->
<div class="breadcrumb">
  <a href="../index.php">Beranda</a>
  <span class="breadcrumb-sep">›</span>
  <a href="menuu.php">Menu</a>
  <span class="breadcrumb-sep">›</span>
  <a href="keranjang.php">Keranjang</a>
  <span class="breadcrumb-sep">›</span>
  <span class="breadcrumb-cur">Checkout</span>
</div>

<!-- STEP INDICATOR -->
<div class="steps-bar">
  <div class="step done">
    <div class="step-circle"><i data-lucide="check" class="lucide-ic"></i></div>
    <div class="step-label">Menu</div>
  </div>
  <div class="step-line done"></div>
  <div class="step done">
    <div class="step-circle"><i data-lucide="check" class="lucide-ic"></i></div>
    <div class="step-label">Keranjang</div>
  </div>
  <div class="step-line done"></div>
  <div class="step active">
    <div class="step-circle">3</div>
    <div class="step-label">Checkout</div>
  </div>
  <div class="step-line"></div>
  <div class="step pending">
    <div class="step-circle">4</div>
    <div class="step-label">Pembayaran</div>
  </div>
  <div class="step-line"></div>
  <div class="step pending">
    <div class="step-circle">5</div>
    <div class="step-label">Selesai</div>
  </div>
</div>

<!-- ═══════════════ MAIN WRAPPER ═══════════════ -->
<div class="page-wrapper">
  <div class="checkout-grid">

    <!-- ╔══════════════════════════════╗ -->
    <!-- ║   KIRI — RINGKASAN PESANAN  ║ -->
    <!-- ╚══════════════════════════════╝ -->
    <div class="premium-card card-left">

      <div class="card-header">
        <div class="card-header-icon"><i data-lucide="shopping-bag" class="lucide-ic"></i></div>
        <div>
          <div class="card-title">Ringkasan Pesanan</div>
          <div class="card-subtitle"><?= count($items) ?> item dalam pesananmu</div>
        </div>
      </div>
      <div class="gold-div"></div>

      <!-- ITEM LIST -->
      <div class="order-list">
        <?php foreach($items as $it): ?>
        <div class="order-item">
          <img
            class="order-img"
            src="../assets/img/produk/<?= htmlspecialchars($it['foto']); ?>"
            alt="<?= htmlspecialchars($it['nama_produk']); ?>"
            onerror="this.src='../assets/img/no-image.png'">
          <div class="order-info">
            <div class="order-name"><?= htmlspecialchars($it['nama_produk']); ?></div>
            <div class="order-qty">Jumlah: <?= $it['jumlah'] ?> pcs</div>
            <div class="order-price">@ Rp <?= number_format($it['harga'],0,',','.'); ?></div>
          </div>
          <div class="order-sub">Rp <?= number_format($it['subtotal'],0,',','.'); ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="gold-div"></div>

      <!-- KODE PROMO -->
      <div class="promo-box">
        <form method="POST" class="promo-form">
          <input
            type="text"
            name="kode_promo"
            placeholder="Punya kode promo?"
            style="text-transform:uppercase;"
            value="<?= $promo_aktif ? htmlspecialchars($promo_aktif['kode_promo']) : '' ?>"
            <?= $promo_aktif ? 'readonly' : '' ?>
            <?= $promo_aktif ? '' : 'required' ?>>
          <?php if($promo_aktif): ?>
            <button type="submit" name="hapus_promo" value="1" class="btn-hapus"><i data-lucide="x" class="lucide-ic"></i> Hapus</button>
          <?php else: ?>
            <button type="submit" name="terapkan_promo" value="1">Terapkan</button>
          <?php endif; ?>
        </form>
        <?php if($promo_error): ?>
          <div class="promo-msg error"><i data-lucide="alert-triangle" class="lucide-ic"></i> <?= htmlspecialchars($promo_error) ?></div>
        <?php elseif($promo_aktif): ?>
          <div class="promo-msg success"><i data-lucide="check" class="lucide-ic"></i> Kode <strong><?= htmlspecialchars($promo_aktif['kode_promo']) ?></strong> diterapkan — diskon <?= (int)$promo_aktif['diskon_persen'] ?>%.</div>
        <?php endif; ?>
      </div>

      <div class="gold-div"></div>

      <!-- TOTAL -->
      <div class="total-wrap">
        <div class="total-row">
          <span>Subtotal (<?= count($items) ?> item)</span>
          <span>Rp <?= number_format($total,0,',','.'); ?></span>
        </div>
        <?php if($promo_aktif): ?>
        <div class="total-row" style="color:#6efabc;">
          <span>Diskon (<?= htmlspecialchars($promo_aktif['kode_promo']) ?>)</span>
          <span>-Rp <?= number_format($diskon_nominal,0,',','.'); ?></span>
        </div>
        <?php endif; ?>
        <div class="total-row">
          <span>Biaya Layanan</span>
          <span style="color:#6efabc;">Gratis</span>
        </div>
        <div class="total-row main">
          <span class="label">Total Pembayaran</span>
          <span class="value">Rp <?= number_format($total_bayar,0,',','.'); ?></span>
        </div>
      </div>

    </div><!-- / card-left -->


    <!-- ╔══════════════════════════════╗ -->
    <!-- ║   KANAN — FORM & BAYAR      ║ -->
    <!-- ╚══════════════════════════════╝ -->
    <div>

      <!-- FORM CARD -->
      <div class="premium-card card-right" style="margin-bottom:20px;">

        <div class="card-header">
          <div class="card-header-icon"><i data-lucide="user" class="lucide-ic"></i></div>
          <div>
            <div class="card-title">Informasi Pemesan</div>
            <div class="card-subtitle">Lengkapi data dirimu</div>
          </div>
        </div>
        <div class="gold-div"></div>

        <form action="qris.php" method="POST" id="checkoutForm">
          <div class="form-wrap">

            <?php if($meja_aktif): ?>
            <div class="booking-notice" style="border-color:rgba(110,250,188,.35);background:rgba(110,250,188,.08);">
              <div class="icon" style="color:#6efabc;"><i data-lucide="armchair" class="lucide-ic"></i></div>
              <p>Pesanan ini otomatis untuk <strong>Meja <?= htmlspecialchars($meja_aktif) ?></strong> (hasil scan QR). Nggak perlu panggil pelayan, pesanan langsung masuk ke dapur.</p>
            </div>
            <input type="hidden" name="nomor_meja" value="<?= htmlspecialchars($meja_aktif) ?>">
            <?php endif; ?>

            <?php if($ada_booking): ?>
            <!-- SUDAH ADA BOOKING -->
            <div class="booking-notice">
              <div class="icon"><i data-lucide="check-circle" class="lucide-ic"></i></div>
              <p>Data pemesanan sudah terhubung dengan booking <strong>#<?= htmlspecialchars($_SESSION['id_booking']) ?></strong>. Langsung lanjutkan ke pembayaran.</p>
            </div>
            <input type="hidden" name="id_booking" value="<?= htmlspecialchars($_SESSION['id_booking']) ?>">

            <?php else: ?>
            <!-- INPUT NAMA -->
            <div class="form-group">
              <label class="form-label" for="nama"><i data-lucide="sparkle" class="lucide-ic"></i> Nama Lengkap</label>
              <div class="input-wrap">
                <span class="input-icon"><i data-lucide="user" class="lucide-ic"></i></span>
                <input
                  type="text"
                  id="nama"
                  name="nama_pemesan"
                  class="form-input"
                  placeholder="Masukkan nama lengkap kamu"
                  required
                  autocomplete="name">
              </div>
              <div class="form-hint">Nama yang akan tercantum di nota pesanan</div>
            </div>

            <!-- INPUT NO HP -->
            <div class="form-group">
              <label class="form-label" for="hp"><i data-lucide="sparkle" class="lucide-ic"></i> Nomor WhatsApp</label>
              <div class="input-wrap">
                <span class="input-icon"><i data-lucide="smartphone" class="lucide-ic"></i></span>
                <input
                  type="tel"
                  id="hp"
                  name="no_hp"
                  class="form-input"
                  placeholder="Contoh: 0812-3456-7890"
                  required
                  autocomplete="tel">
              </div>
              <div class="form-hint">Kami akan menghubungi kamu melalui WhatsApp</div>
            </div>

            <input type="hidden" name="id_booking" value="">
            <?php endif; ?>

            <!-- METODE PEMBAYARAN -->
            <div class="form-group">
              <label class="form-label"><i data-lucide="sparkle" class="lucide-ic"></i> Metode Pembayaran</label>
              <div class="pay-method selected" onclick="selectPay(this)">
                <div class="pay-method-icon"><i data-lucide="camera" class="lucide-ic"></i></div>
                <div class="pay-method-info">
                  <div class="name">QRIS</div>
                  <div class="desc">Scan QR — semua e-wallet & mobile banking</div>
                </div>
                <div class="pay-method-check"></div>
              </div>
            </div>

          </div><!-- /form-wrap -->

          <div style="padding:0 26px 26px;">
            <!-- SUBMIT -->
            <button type="submit" class="btn-submit" id="submitBtn">
              <span><i data-lucide="sparkle" class="lucide-ic"></i> Lanjut ke Pembayaran</span>
              <span class="arrow"><i data-lucide="arrow-right" class="lucide-ic"></i></span>
            </button>

            <!-- BACK -->
            <a href="keranjang.php" class="btn-back">
              <i data-lucide="arrow-left" class="lucide-ic"></i> Kembali ke Keranjang
            </a>
          </div>

        </form>

      </div><!-- /premium-card -->

      <!-- SECURE CARD -->
      <div class="premium-card" style="animation-delay:.7s;">
        <div class="secure-row">
          <div class="secure-badge"><span><i data-lucide="lock" class="lucide-ic"></i></span> Transaksi Aman</div>
          <div class="secure-badge"><span><i data-lucide="zap" class="lucide-ic"></i></span> Proses Cepat</div>
          <div class="secure-badge"><span><i data-lucide="check-circle" class="lucide-ic"></i></span> Terverifikasi</div>
          <div class="secure-badge"><span><i data-lucide="phone" class="lucide-ic"></i></span> CS Siap Bantu</div>
        </div>
      </div>

    </div><!-- /col-right -->

  </div><!-- /checkout-grid -->
</div>

<!-- ═══════════════ FOOTER ═══════════════ -->
<div class="site-footer">
  © 2026 YOLAZCAKE Sintang • Cafe • Bakery • Boutique<br>
  Jl. Lintas Melawi, Ladang, Sintang, Kalimantan Barat<br>
  WA: 0815-7815-7888
</div>

<!-- ═══════════════ SCRIPTS ═══════════════ -->
<script>
  /* Hero sparkles */
  (function(){
    const hero=document.getElementById('pageHero');
    const colors=['#D4AF37','#FFE4B5','#E8A0BF','#fff','#f9ce34','#b8860b'];
    for(let i=0;i<20;i++){
      const d=document.createElement('div');d.className='sparkle';
      const s=Math.random()*5+2;
      d.style.cssText=`width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;bottom:${Math.random()*30}%;animation-duration:${4+Math.random()*7}s;animation-delay:${Math.random()*5}s;opacity:0;`;
      hero.appendChild(d);
    }
  })();

  /* Background particles */
  (function(){
    const c=document.getElementById('particles');
    const cols=['rgba(212,175,55,.4)','rgba(232,160,191,.3)','rgba(255,255,255,.15)'];
    for(let i=0;i<14;i++){
      const p=document.createElement('div');p.className='particle';
      const s=Math.random()*5+2;
      p.style.cssText=`width:${s}px;height:${s}px;background:${cols[Math.floor(Math.random()*cols.length)]};left:${Math.random()*100}%;animation-duration:${10+Math.random()*12}s;animation-delay:${Math.random()*10}s;`;
      c.appendChild(p);
    }
  })();

  /* Payment method selector */
  function selectPay(el){
    document.querySelectorAll('.pay-method').forEach(m=>m.classList.remove('selected'));
    el.classList.add('selected');
  }

  /* Submit animation */
  document.getElementById('checkoutForm').addEventListener('submit', function(){
    const btn=document.getElementById('submitBtn');
    btn.innerHTML='<span>⏳ Memproses...</span>';
    btn.style.opacity='.75';
    btn.style.pointerEvents='none';
  });
</script>


<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide){lucide.createIcons();}</script>
</body>
</html>
