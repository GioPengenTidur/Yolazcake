<?php
session_start();
include '../config/koneksi.php';

if(isset($_POST['tambah'])){
    $id_produk = (int)$_POST['id_produk'];
    $jumlah = (int)$_POST['jumlah'];
    $_SESSION['keranjang'][$id_produk] =
        ($_SESSION['keranjang'][$id_produk] ?? 0) + $jumlah;
}

if(isset($_GET['hapus'])){
    $id_produk = (int)$_GET['hapus'];
    unset($_SESSION['keranjang'][$id_produk]);
    header("Location: keranjang.php");
    exit();
}

if(isset($_GET['aksi'])){
    $id_produk = (int)$_GET['id'];
    if($_GET['aksi'] == 'tambah'){
        $_SESSION['keranjang'][$id_produk]++;
    }
    if($_GET['aksi'] == 'kurang'){
        $_SESSION['keranjang'][$id_produk]--;
        if($_SESSION['keranjang'][$id_produk] <= 0){
            unset($_SESSION['keranjang'][$id_produk]);
        }
    }
    header("Location: keranjang.php");
    exit();
}

$total = 0;
$items = [];
if(!empty($_SESSION['keranjang'])){
    $stmtProduk = $conn->prepare("SELECT * FROM produk WHERE id_produk=?");
    foreach($_SESSION['keranjang'] as $id_produk => $jumlah){
        $id_produk = (int)$id_produk;
        $stmtProduk->bind_param("i", $id_produk);
        $stmtProduk->execute();
        $p = $stmtProduk->get_result()->fetch_assoc();
        if($p){
            $subtotal = $p['harga'] * $jumlah;
            $total += $subtotal;
            $items[] = array_merge($p, ['jumlah' => $jumlah, 'subtotal' => $subtotal]);
        }
    }
    $stmtProduk->close();
}

$total_keranjang = array_sum(array_column($items, 'jumlah'));

// Pesan error dari proses_pemesanan.php (misal stok nggak cukup saat
// checkout) ditampilkan sekali lalu dibuang, supaya nggak muncul lagi
// kalau halaman ini di-refresh.
$checkout_error = $_SESSION['checkout_error'] ?? null;
unset($_SESSION['checkout_error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="../assets/css/lucide-icons.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Keranjang Belanja – YOLAZCAKE</title>
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
      margin:32px auto 0;padding:0 32px;max-width:1200px;width:100%;
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
      max-width:900px;margin:0 auto;
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

    .btn-danger{
      background:linear-gradient(135deg,rgba(255,80,80,.2),rgba(255,80,80,.08));
      border:1px solid rgba(255,80,80,.35);
      color:#ff8080;
    }
    .btn-danger:hover{
      background:linear-gradient(135deg,rgba(255,80,80,.3),rgba(255,80,80,.15));
      box-shadow:0 8px 28px rgba(255,80,80,.2);
    }

    /* ── CART ITEM CARD ── */
    .cart-card{
      width:100%;
      background:rgba(255,255,255,.06);
      backdrop-filter:blur(20px);
      border:1px solid rgba(255,255,255,.1);
      border-radius:20px;
      position:relative;overflow:hidden;
      margin-bottom:16px;
      opacity:0;transform:translateY(30px);
      transition:border-color .4s,box-shadow .4s,transform .3s;
    }
    .cart-card.visible{opacity:1;transform:translateY(0);transition:opacity .6s ease,transform .6s ease,border-color .4s,box-shadow .4s;}
    .cart-card:hover{
      border-color:rgba(212,175,55,.35);
      box-shadow:0 16px 50px rgba(0,0,0,.3),0 0 25px rgba(212,175,55,.18);
    }
    .cart-card::before{
      content:'';position:absolute;top:0;left:0;right:0;height:2px;
      background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);
      background-size:200% 100%;animation:goldSlide 4s linear infinite;
    }

    .cart-card-inner{
      display:flex;align-items:center;gap:20px;
      padding:20px 24px;
    }

    .cart-img{
      width:80px;height:80px;border-radius:14px;object-fit:cover;flex-shrink:0;
      border:1px solid rgba(212,175,55,.2);
    }
    .cart-img-placeholder{
      width:80px;height:80px;border-radius:14px;flex-shrink:0;
      background:rgba(212,175,55,.08);border:1px solid rgba(212,175,55,.2);
      display:flex;align-items:center;justify-content:center;font-size:2em;
    }

    .cart-info{flex:1;min-width:0;}
    .cart-name{
      font-family:'Playfair Display',serif;
      font-size:1.08em;font-weight:700;color:#fff;
      margin-bottom:4px;
    }
    .cart-price-unit{
      font-size:.83em;color:rgba(255,255,255,.5);
      margin-bottom:12px;
    }
    .cart-price-unit span{color:#D4AF37;font-weight:600;}

    /* Qty Controls */
    .qty-row{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
    .qty-wrap{
      display:flex;align-items:center;
      background:rgba(255,255,255,.06);
      border:1px solid rgba(212,175,55,.25);
      border-radius:10px;overflow:hidden;
    }
    .qty-btn{
      width:34px;height:36px;border:none;background:transparent;
      color:#D4AF37;font-size:1em;cursor:pointer;
      display:flex;align-items:center;justify-content:center;
      transition:background .2s;text-decoration:none;
    }
    .qty-btn:hover{background:rgba(212,175,55,.15);}
    .qty-num{
      width:36px;height:36px;
      display:flex;align-items:center;justify-content:center;
      color:#fff;font-size:.9em;font-weight:700;font-family:'Inter',sans-serif;
    }
    .del-btn{
      display:inline-flex;align-items:center;gap:5px;
      font-size:.75em;font-weight:600;letter-spacing:1px;text-transform:uppercase;
      color:rgba(255,100,100,.7);text-decoration:none;
      border:1px solid rgba(255,80,80,.2);
      padding:6px 14px;border-radius:8px;
      transition:all .25s;background:rgba(255,80,80,.05);
    }
    .del-btn:hover{color:#ff6060;border-color:rgba(255,80,80,.5);background:rgba(255,80,80,.12);}

    .cart-subtotal{
      font-weight:700;font-size:1.05em;color:#D4AF37;
      white-space:nowrap;flex-shrink:0;text-align:right;
    }
    .cart-subtotal small{display:block;font-size:.65em;color:rgba(255,255,255,.4);font-weight:400;margin-bottom:2px;}

    /* ── ORDER SUMMARY ── */
    .summary-card{
      width:100%;
      background:rgba(212,175,55,.07);
      border:1px solid rgba(212,175,55,.25);
      border-radius:20px;
      padding:28px 32px;
      margin-top:8px;margin-bottom:28px;
      opacity:0;animation:fadeSlideDown .9s forwards .5s;
    }
    .summary-title{
      font-family:'Playfair Display',serif;
      font-size:1.1em;font-weight:700;
      color:#D4AF37;margin-bottom:18px;
      display:flex;align-items:center;gap:10px;
    }
    .summary-row{
      display:flex;justify-content:space-between;align-items:center;
      font-size:.88em;color:rgba(255,255,255,.65);
      padding:6px 0;
      border-bottom:1px solid rgba(255,255,255,.06);
    }
    .summary-row:last-of-type{border-bottom:none;}
    .summary-total{
      display:flex;justify-content:space-between;align-items:center;
      margin-top:16px;padding-top:16px;
      border-top:1px solid rgba(212,175,55,.3);
    }
    .summary-total-label{
      font-family:'Playfair Display',serif;font-size:1.05em;font-weight:700;color:#fff;
    }
    .summary-total-amount{
      font-family:'Playfair Display',serif;font-size:1.5em;font-weight:700;color:#D4AF37;
      text-shadow:0 0 20px rgba(212,175,55,.4);
    }

    /* ── CHECKOUT FORM ── */
    .checkout-card{
      width:100%;
      background:rgba(255,255,255,.05);
      backdrop-filter:blur(20px);
      border:1px solid rgba(255,255,255,.1);
      border-radius:20px;
      padding:32px;
      opacity:0;animation:fadeSlideDown .9s forwards .7s;
    }
    .checkout-card::before{
      content:'';display:block;
      height:2px;width:100%;
      background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);
      background-size:200% 100%;animation:goldSlide 4s linear infinite;
      border-radius:999px;margin-bottom:24px;
    }
    .checkout-title{
      font-family:'Playfair Display',serif;font-size:1.2em;font-weight:700;
      color:#fff;margin-bottom:20px;
      display:flex;align-items:center;gap:10px;
    }
    .form-group{margin-bottom:18px;}
    .form-label{
      display:block;font-size:.78em;font-weight:600;letter-spacing:1.5px;
      text-transform:uppercase;color:rgba(212,175,55,.85);
      margin-bottom:8px;
    }
    .form-input{
      width:100%;padding:13px 18px;
      background:rgba(255,255,255,.06);
      border:1px solid rgba(212,175,55,.25);
      border-radius:12px;
      font-family:'Inter',sans-serif;font-size:.9em;color:#fff;
      outline:none;transition:border-color .3s,box-shadow .3s;
    }
    .form-input::placeholder{color:rgba(255,255,255,.3);}
    .form-input:focus{
      border-color:rgba(212,175,55,.6);
      box-shadow:0 0 0 3px rgba(212,175,55,.12);
    }
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}

    /* ── EMPTY STATE ── */
    .empty-state{
      text-align:center;padding:80px 20px;
      background:rgba(255,255,255,.04);
      border:1px solid rgba(255,255,255,.08);
      border-radius:24px;width:100%;
      opacity:0;animation:fadeSlideDown .8s forwards .3s;
    }
    .empty-state .empty-icon{font-size:3.5em;margin-bottom:16px;opacity:.5;}
    .empty-state p{color:rgba(255,255,255,.4);font-size:.92em;margin-bottom:24px;}

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
    @media(max-width:640px){
      .hero-inner h1{font-size:2em;}
      .cart-card-inner{flex-direction:column;align-items:flex-start;}
      .cart-subtotal{text-align:left;}
      .form-row{grid-template-columns:1fr;}
      .summary-card,.checkout-card{padding:20px;}
    }
  </style>
</head>
<body>

<div id="particles"></div>

<!-- ═══════════════ HERO ═══════════════ -->
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow"><i data-lucide="sparkle" class="lucide-ic"></i> YOLAZCAKE Sintang <i data-lucide="sparkle" class="lucide-ic"></i></p>
    <h1>Keranjang Belanja</h1>
    <p class="hero-sub">Periksa pesananmu sebelum melanjutkan ke pembayaran</p>
    <div class="hero-divider">
      <span></span><span class="diamond"><i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i></span><span></span>
    </div>
  </div>
</div>

<div class="back-link">
  <a href="menuu.php"><i data-lucide="arrow-left" class="lucide-ic"></i> Kembali ke Menu</a>
</div>

<!-- ═══════════════ PAGE WRAPPER ═══════════════ -->
<div class="page-wrapper">

  <?php if(!empty($_SESSION['meja_aktif'])): ?>
  <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;
              background:linear-gradient(135deg,rgba(110,250,188,.14),rgba(110,250,188,.05));
              border:1px solid rgba(110,250,188,.35);border-radius:16px;padding:14px 22px;margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:12px;">
      <span style="width:36px;height:36px;border-radius:50%;background:rgba(110,250,188,.18);
                    display:flex;align-items:center;justify-content:center;color:#6efabc;flex-shrink:0;">
        <i data-lucide="armchair" class="lucide-ic"></i>
      </span>
      <div style="font-size:.85em;">Pesanan ini otomatis untuk <strong>Meja <?= htmlspecialchars($_SESSION['meja_aktif']) ?></strong></div>
    </div>
  </div>
  <?php endif; ?>

  <?php if($checkout_error): ?>
  <div style="background:rgba(255,80,80,.1);border:1px solid rgba(255,80,80,.35);color:#ff8080;padding:14px 20px;border-radius:14px;margin-bottom:20px;font-size:.85em;line-height:1.6;">
    <i data-lucide="alert-triangle" class="lucide-ic"></i> <?= htmlspecialchars($checkout_error) ?>
  </div>
  <?php endif; ?>

  <!-- TOP BAR -->
  <div class="top-bar">
    <div>
      <?php if(!empty($items)): ?>
      <div class="new-badge"><i data-lucide="shopping-cart" class="lucide-ic"></i> <?= $total_keranjang ?> Item</div>
      <?php else: ?>
      <div class="new-badge"><i data-lucide="shopping-cart" class="lucide-ic"></i> Keranjang Kosong</div>
      <?php endif; ?>
      <h2 class="section-title" style="margin-top:10px;">Daftar Pesanan Kamu</h2>
    </div>
    <a href="menuu.php" class="btn-premium btn-green"><i data-lucide="cake" class="lucide-ic"></i> Tambah Menu</a>
  </div>

  <div class="gold-rule"><span><i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i></span></div>

  <?php if(!empty($items)): ?>

  <!-- ─── CART ITEMS ─── -->
  <?php foreach($items as $i => $item): ?>
  <div class="cart-card" style="transition-delay:<?= $i * 0.1 ?>s">
    <div class="cart-card-inner">

      <!-- Image -->
      <div class="cart-img-placeholder"><i data-lucide="cake" class="lucide-ic"></i></div>

      <!-- Info -->
      <div class="cart-info">
        <div class="cart-name"><?= htmlspecialchars($item['nama_produk']) ?></div>
        <div class="cart-price-unit">Harga satuan: <span>Rp <?= number_format($item['harga'],0,',','.') ?></span></div>
        <div class="qty-row">
          <div class="qty-wrap">
            <a href="keranjang.php?aksi=kurang&id=<?= $item['id_produk'] ?>" class="qty-btn">−</a>
            <div class="qty-num"><?= $item['jumlah'] ?></div>
            <a href="keranjang.php?aksi=tambah&id=<?= $item['id_produk'] ?>" class="qty-btn">+</a>
          </div>
          <a href="keranjang.php?hapus=<?= $item['id_produk'] ?>"
             class="del-btn"
             onclick="return confirm('Hapus <?= htmlspecialchars($item['nama_produk']) ?> dari keranjang?')">
            <i data-lucide="x" class="lucide-ic"></i> Hapus
          </a>
        </div>
      </div>

      <!-- Subtotal -->
      <div class="cart-subtotal">
        <small><?= $item['jumlah'] ?>x</small>
        Rp <?= number_format($item['subtotal'],0,',','.') ?>
      </div>

    </div>
  </div>
  <?php endforeach; ?>

  <!-- ─── ORDER SUMMARY ─── -->
  <div class="summary-card">
    <div class="summary-title"><i data-lucide="clipboard-list" class="lucide-ic"></i> Ringkasan Pesanan</div>
    <?php foreach($items as $item): ?>
    <div class="summary-row">
      <span><?= htmlspecialchars($item['nama_produk']) ?> ×<?= $item['jumlah'] ?></span>
      <span>Rp <?= number_format($item['subtotal'],0,',','.') ?></span>
    </div>
    <?php endforeach; ?>
    <div class="summary-total">
      <div class="summary-total-label">Total Pembayaran</div>
      <div class="summary-total-amount">Rp <?= number_format($total,0,',','.') ?></div>
    </div>
  </div>

  <!-- ─── CHECKOUT FORM ─── -->
  <div class="checkout-card">
    <div class="checkout-title"><i data-lucide="sparkle" class="lucide-ic"></i> Informasi Pemesan</div>
    <form action="qris.php" method="POST">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Nama Lengkap</label>
          <input type="text" name="nama_pemesan" class="form-input"
                 placeholder="Masukkan nama kamu"
                 value="<?= htmlspecialchars($_SESSION['nama_pemesan'] ?? '') ?>"
                 required>
        </div>
        <div class="form-group">
          <label class="form-label">Nomor WhatsApp</label>
          <input type="tel" name="no_hp" class="form-input"
                 placeholder="Contoh: 08123456789"
                 value="<?= htmlspecialchars($_SESSION['no_hp'] ?? '') ?>"
                 required>
        </div>
      </div>
      <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;">
        <button type="submit" class="btn-premium btn-gold" style="flex:1;min-width:200px;justify-content:center;">
          <i data-lucide="sparkle" class="lucide-ic"></i> Lanjut ke Pembayaran QRIS
        </button>
        <a href="menuu.php" class="btn-premium btn-green" style="justify-content:center;">
          <i data-lucide="cake" class="lucide-ic"></i> Tambah Menu
        </a>
      </div>
    </form>
  </div>

  <?php else: ?>

  <!-- ─── EMPTY STATE ─── -->
  <div class="empty-state">
    <div class="empty-icon"><i data-lucide="shopping-cart" class="lucide-ic"></i></div>
    <p>Keranjangmu masih kosong. Yuk pilih menu favoritmu!</p>
    <a href="menuu.php" class="btn-premium btn-gold" style="display:inline-flex;justify-content:center;">
      <i data-lucide="cake" class="lucide-ic"></i> Lihat Menu
    </a>
  </div>

  <?php endif; ?>

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

  /* Card reveal on scroll */
  (function(){
    const cards = document.querySelectorAll('.cart-card');
    const io = new IntersectionObserver(entries=>{
      entries.forEach(e=>{
        if(e.isIntersecting){
          e.target.classList.add('visible');
          io.unobserve(e.target);
        }
      });
    },{threshold:0.08});
    cards.forEach(c=>io.observe(c));
  })();
</script>


<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide){lucide.createIcons();}</script>
</body>
</html>
