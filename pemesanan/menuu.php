<?php
session_start();
include '../config/koneksi.php';

$query = mysqli_query($conn, "SELECT * FROM produk ORDER BY id_produk DESC");
if(!$query){ die(mysqli_error($conn)); }

// Hitung total item di keranjang
$total_keranjang = 0;
if(!empty($_SESSION['keranjang'])){
  foreach($_SESSION['keranjang'] as $jml){ $total_keranjang += $jml; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menu Pemesanan – YOLAZCAKE</title>
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
      max-width:1200px;margin:0 auto;
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

    /* ── CART SUMMARY BAR ── */
    .cart-bar{
      width:100%;
      background:rgba(212,175,55,.08);
      border:1px solid rgba(212,175,55,.25);
      border-radius:16px;
      padding:14px 24px;
      display:flex;align-items:center;justify-content:space-between;
      flex-wrap:wrap;gap:12px;
      margin-bottom:32px;
      opacity:0;animation:fadeSlideDown .8s forwards .8s;
    }
    .cart-bar-info{
      font-size:.88em;color:rgba(255,255,255,.7);
    }
    .cart-bar-info strong{color:#D4AF37;}

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

    /* ── PRODUCT GRID ── */
    .product-grid{
      width:100%;
      display:grid;
      grid-template-columns:repeat(3,1fr);
      gap:24px;
    }

    /* ── PRODUCT CARD ── */
    .prod-card{
      background:rgba(255,255,255,.06);
      backdrop-filter:blur(20px);
      border:1px solid rgba(255,255,255,.1);
      border-radius:24px;
      position:relative;overflow:hidden;
      opacity:0;transform:translateY(40px);
      transition:border-color .45s,box-shadow .45s,transform .3s;
      display:flex;flex-direction:column;
    }
    .prod-card.visible{
      opacity:1;transform:translateY(0);
    }
    .prod-card:hover{
      border-color:rgba(212,175,55,.4);
      box-shadow:
        0 20px 55px rgba(0,0,0,.35),
        0 0 30px rgba(212,175,55,.25),
        0 0 65px rgba(212,175,55,.12),
        0 0 100px rgba(212,175,55,.05);
      transform:translateY(-5px);
    }
    .prod-card::before{
      content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);
      background-size:200% 100%;animation:goldSlide 4s linear infinite;
    }

    /* ── CARD IMAGE ── */
    .card-img-wrap{
      position:relative;overflow:hidden;
      height:200px;flex-shrink:0;
    }
    .card-img-wrap img{
      width:100%;height:100%;object-fit:cover;
      transition:transform .5s cubic-bezier(.25,.46,.45,.94);
    }
    .prod-card:hover .card-img-wrap img{
      transform:scale(1.08);
    }
    .card-img-overlay{
      position:absolute;inset:0;
      background:linear-gradient(to top,rgba(30,14,58,.85) 0%,transparent 60%);
    }

    /* stok badge on image */
    .stok-badge{
      position:absolute;top:12px;right:12px;
      display:inline-block;
      padding:4px 14px;border-radius:999px;font-size:.72em;font-weight:600;
      letter-spacing:1px;backdrop-filter:blur(8px);
    }
    .stok-ok{background:rgba(99,250,180,.2);border:1px solid rgba(99,250,180,.4);color:#6efabc;}
    .stok-low{background:rgba(255,180,50,.2);border:1px solid rgba(255,180,50,.4);color:#ffb432;}
    .stok-habis{background:rgba(255,80,80,.2);border:1px solid rgba(255,80,80,.4);color:#ff6060;}

    /* ── CARD BODY ── */
    .card-body{
      padding:20px;
      display:flex;flex-direction:column;flex:1;
      gap:0;
    }
    .prod-name{
      font-family:'Playfair Display',serif;
      font-size:1.05em;font-weight:700;
      color:#fff;line-height:1.3;
      margin-bottom:6px;
    }
    .prod-price{
      color:#D4AF37;font-weight:700;font-size:1.1em;
      letter-spacing:.5px;
      margin-bottom:16px;
    }

    /* Divider */
    .card-divider{
      height:1px;
      background:linear-gradient(to right,transparent,rgba(212,175,55,.35),transparent);
      margin-bottom:16px;
    }

    /* ── QTY + BUTTON ── */
    .card-action{
      display:flex;align-items:center;gap:10px;margin-top:auto;
    }
    .qty-wrap{
      display:flex;align-items:center;
      background:rgba(255,255,255,.06);
      border:1px solid rgba(212,175,55,.25);
      border-radius:10px;overflow:hidden;
      flex-shrink:0;
    }
    .qty-btn{
      width:34px;height:38px;border:none;background:transparent;
      color:#D4AF37;font-size:1.1em;cursor:pointer;
      display:flex;align-items:center;justify-content:center;
      transition:background .2s;user-select:none;
    }
    .qty-btn:hover{background:rgba(212,175,55,.15);}
    .qty-input{
      width:40px;height:38px;border:none;background:transparent;
      color:#fff;font-size:.9em;font-weight:600;font-family:'Inter',sans-serif;
      text-align:center;outline:none;
      -moz-appearance:textfield;
    }
    .qty-input::-webkit-outer-spin-button,
    .qty-input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0;}

    /* Add to cart btn inside card */
    .btn-cart{
      flex:1;position:relative;
      padding:10px 14px;border:none;border-radius:10px;
      font-family:'Inter',sans-serif;font-size:.78em;font-weight:700;
      letter-spacing:1.5px;text-transform:uppercase;cursor:pointer;
      overflow:hidden;transition:transform .25s cubic-bezier(.34,1.56,.64,1),box-shadow .35s;
      display:flex;align-items:center;justify-content:center;gap:6px;
    }
    .btn-cart::before{
      content:'';position:absolute;inset:0;
      background:linear-gradient(90deg,transparent,rgba(255,255,255,.18),transparent);
      transform:translateX(-100%);transition:transform .5s;
    }
    .btn-cart:hover::before{transform:translateX(100%);}
    .btn-cart:hover{transform:translateY(-2px);}
    .btn-cart-gold{
      background:linear-gradient(135deg,#D4AF37 0%,#b8860b 50%,#D4AF37 100%);
      background-size:200% 100%;color:#1e0e3a;
      animation:goldSlide 3s linear infinite;
      box-shadow:0 6px 22px rgba(212,175,55,.3);
    }
    .btn-cart-gold:hover{box-shadow:0 10px 35px rgba(212,175,55,.5);}
    .btn-cart-disabled{
      background:rgba(255,255,255,.06);
      border:1px solid rgba(255,80,80,.25);
      color:rgba(255,80,80,.6);
      cursor:not-allowed;
    }

    /* ── TOAST NOTIF ── */
    .toast{
      position:fixed;bottom:100px;right:28px;z-index:1000;
      background:rgba(30,14,58,.95);
      border:1px solid rgba(212,175,55,.4);
      border-radius:14px;padding:14px 22px;
      font-size:.85em;color:#fff;
      display:flex;align-items:center;gap:10px;
      box-shadow:0 12px 40px rgba(0,0,0,.4),0 0 25px rgba(212,175,55,.2);
      transform:translateX(140%);transition:transform .4s cubic-bezier(.34,1.56,.64,1);
      backdrop-filter:blur(20px);
    }
    .toast.show{transform:translateX(0);}
    .toast .toast-icon{font-size:1.3em;}
    .toast .toast-text strong{display:block;color:#D4AF37;margin-bottom:2px;}

    /* ── FLOATING CART ── */
    .float-cart{
      position:fixed;bottom:32px;right:28px;z-index:999;
      width:62px;height:62px;border-radius:50%;border:none;cursor:pointer;
      background:linear-gradient(135deg,#D4AF37 0%,#b8860b 60%,#D4AF37 100%);
      background-size:200% 200%;animation:goldSlide 3s linear infinite;
      box-shadow:0 8px 30px rgba(212,175,55,.45),0 0 50px rgba(212,175,55,.2);
      display:flex;align-items:center;justify-content:center;
      font-size:1.5em;
      transition:transform .3s cubic-bezier(.34,1.56,.64,1),box-shadow .3s;
      text-decoration:none;
    }
    .float-cart:hover{
      transform:scale(1.15) rotate(-8deg);
      box-shadow:0 14px 45px rgba(212,175,55,.6),0 0 70px rgba(212,175,55,.3);
    }
    .float-badge{
      position:absolute;top:-4px;right:-4px;
      min-width:22px;height:22px;
      background:linear-gradient(135deg,#ee2a7b,#c0175a);
      border-radius:999px;border:2px solid #1e0e3a;
      font-size:.65em;font-weight:700;color:#fff;
      display:flex;align-items:center;justify-content:center;
      padding:0 4px;
      animation:pulseBadge 2s ease-in-out infinite;
    }
    @keyframes pulseBadge{
      0%,100%{box-shadow:0 0 0 0 rgba(238,42,123,.5);}
      50%{box-shadow:0 0 0 7px rgba(238,42,123,0);}
    }

    /* ── EMPTY STATE ── */
    .empty-state{
      text-align:center;padding:80px 20px;
      background:rgba(255,255,255,.04);
      border:1px solid rgba(255,255,255,.08);
      border-radius:24px;width:100%;
    }
    .empty-state .empty-icon{font-size:3.5em;margin-bottom:16px;opacity:.5;}
    .empty-state p{color:rgba(255,255,255,.4);font-size:.92em;}

    /* ── GOLD RULE ── */
    .gold-rule{display:flex;align-items:center;gap:12px;width:100%;margin-bottom:28px;}
    .gold-rule::before,.gold-rule::after{content:'';flex:1;height:1px;background:linear-gradient(to right,transparent,rgba(212,175,55,.4));}
    .gold-rule::after{background:linear-gradient(to left,transparent,rgba(212,175,55,.4));}
    .gold-rule span{color:#D4AF37;font-size:.65em;letter-spacing:3px;white-space:nowrap;}

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
      .product-grid{grid-template-columns:repeat(2,1fr);}
    }
    @media(max-width:580px){
      .hero-inner h1{font-size:2em;}
      .product-grid{grid-template-columns:1fr;}
      .top-bar{flex-direction:column;align-items:flex-start;}
      .float-cart{width:54px;height:54px;font-size:1.3em;bottom:24px;right:20px;}
    }
  </style>
</head>
<body>

<div id="particles"></div>

<!-- ═══════════════ HERO ═══════════════ -->
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow">✦ YOLAZCAKE Sintang ✦</p>
    <h1>Menu Pemesanan</h1>
    <p class="hero-sub">Pilih hidangan favoritmu dan nikmati setiap momen bersama kami</p>
    <div class="hero-divider">
      <span></span><span class="diamond">✦ ✦ ✦</span><span></span>
    </div>
  </div>
</div>

<div class="back-link">
  <a href="../index.php">← Kembali ke Beranda</a>
</div>

<!-- ═══════════════ PAGE WRAPPER ═══════════════ -->
<div class="page-wrapper">

  <!-- TOP BAR -->
  <div class="top-bar">
    <div>
      <div class="new-badge">🎂 Menu Tersedia</div>
      <h2 class="section-title" style="margin-top:10px;">Daftar Menu & Produk</h2>
    </div>
    <a href="keranjang.php" class="btn-premium btn-green">🛒 Lihat Keranjang<?php if($total_keranjang>0): ?> <span style="background:rgba(238,42,123,.8);color:#fff;border-radius:999px;padding:1px 8px;font-size:.8em;"><?= $total_keranjang ?></span><?php endif; ?></a>
  </div>

  <!-- CART BAR -->
  <?php if($total_keranjang > 0): ?>
  <div class="cart-bar">
    <div class="cart-bar-info">
      🛍️ Kamu sudah memilih <strong><?= $total_keranjang ?> item</strong> di keranjang
    </div>
    <a href="keranjang.php" class="btn-premium btn-gold" style="padding:10px 22px;font-size:.78em;">
      ✦ Lanjut ke Keranjang
    </a>
  </div>
  <?php endif; ?>

  <!-- GOLD RULE -->
  <div class="gold-rule"><span>✦ ✦ ✦</span></div>

  <!-- PRODUCT GRID -->
  <?php
  $found = false;
  $rows  = [];
  while($row = mysqli_fetch_assoc($query)){ $rows[] = $row; $found = true; }
  ?>

  <?php if($found): ?>
  <div class="product-grid">
    <?php foreach($rows as $i => $p):
      $stok = (int)$p['stok'];
      if($stok <= 0){ $sc='stok-habis'; $sl='Stok Habis'; }
      elseif($stok <= 5){ $sc='stok-low';  $sl=$stok.' tersisa'; }
      else              { $sc='stok-ok';   $sl='Tersedia'; }
      $habis = ($stok <= 0);
    ?>
    <div class="prod-card" style="animation-delay:<?= ($i * 0.1) ?>s;">

      <!-- IMAGE -->
      <div class="card-img-wrap">
        <img src="../assets/img/produk/<?= htmlspecialchars($p['foto']); ?>"
             alt="<?= htmlspecialchars($p['nama_produk']); ?>"
             onerror="this.src='../assets/img/no-image.png'">
        <div class="card-img-overlay"></div>
        <span class="stok-badge <?= $sc ?>"><?= $sl ?></span>
      </div>

      <!-- BODY -->
      <div class="card-body">
        <div class="prod-name"><?= htmlspecialchars($p['nama_produk']); ?></div>
        <div class="prod-price">Rp <?= number_format($p['harga'],0,',','.'); ?></div>
        <div class="card-divider"></div>

        <!-- ACTION -->
        <?php if(!$habis): ?>
        <form action="keranjang.php" method="POST" class="card-action" onsubmit="onAddCart(event, this, '<?= htmlspecialchars($p['nama_produk']); ?>')">
          <input type="hidden" name="tambah" value="1">
          <input type="hidden" name="id_produk" value="<?= $p['id_produk']; ?>">
          <div class="qty-wrap">
            <button type="button" class="qty-btn" onclick="changeQty(this,-1)">−</button>
            <input type="number" class="qty-input" name="jumlah" value="1" min="1" max="<?= $stok ?>" readonly>
            <button type="button" class="qty-btn" onclick="changeQty(this,1)">+</button>
          </div>
          <button type="submit" class="btn-cart btn-cart-gold">
            🛒 Tambah
          </button>
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

  <?php else: ?>
  <div class="empty-state">
    <div class="empty-icon">🎂</div>
    <p>Belum ada menu yang tersedia. Silakan cek kembali nanti.</p>
  </div>
  <?php endif; ?>

</div>

<!-- ═══════════════ FOOTER ═══════════════ -->
<div class="site-footer">
  © 2026 YOLAZCAKE Sintang • Cafe • Bakery • Boutique<br>
  Jl. Lintas Melawi, Ladang, Sintang, Kalimantan Barat<br>
  WA: 0815-7815-7888
</div>

<!-- ═══════════════ FLOATING CART ═══════════════ -->
<a href="keranjang.php" class="float-cart" title="Lihat Keranjang">
  🛒
  <?php if($total_keranjang > 0): ?>
  <div class="float-badge" id="cartBadge"><?= $total_keranjang ?></div>
  <?php else: ?>
  <div class="float-badge" id="cartBadge" style="display:none;">0</div>
  <?php endif; ?>
</a>

<!-- ═══════════════ TOAST NOTIF ═══════════════ -->
<div class="toast" id="toast">
  <span class="toast-icon">🎂</span>
  <div class="toast-text">
    <strong id="toast-name"></strong>
    <span>Berhasil ditambahkan ke keranjang!</span>
  </div>
</div>

<!-- ═══════════════ SCRIPTS ═══════════════ -->
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
    const cards = document.querySelectorAll('.prod-card');
    const io = new IntersectionObserver(entries=>{
      entries.forEach(e=>{
        if(e.isIntersecting){
          const delay = parseFloat(e.target.style.animationDelay||0)*1000;
          setTimeout(()=>e.target.classList.add('visible'), delay);
          io.unobserve(e.target);
        }
      });
    },{threshold:0.08});
    cards.forEach(c=>io.observe(c));
  })();

  /* Qty control */
  function changeQty(btn, delta){
    const wrap = btn.closest('.qty-wrap');
    const input = wrap.querySelector('.qty-input');
    let v = parseInt(input.value)||1;
    const max = parseInt(input.max)||99;
    v = Math.min(max, Math.max(1, v+delta));
    input.value = v;
  }

  /* Toast + cart badge update */
  let cartCount = <?= $total_keranjang ?>;
  const toast = document.getElementById('toast');
  const toastName = document.getElementById('toast-name');
  const badge = document.getElementById('cartBadge');
  let toastTimer;

  function showToast(name){
    toastName.textContent = name;
    toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(()=>toast.classList.remove('show'), 3000);
  }

  function onAddCart(e, form, name){
    e.preventDefault();
    const qty = parseInt(form.querySelector('.qty-input').value)||1;

    // Update badge instantly
    cartCount += qty;
    badge.textContent = cartCount;
    badge.style.display = 'flex';

    showToast(name);

    // Submit form after short delay (so user sees feedback)
    setTimeout(()=>form.submit(), 600);
  }
</script>

</body>
</html>
