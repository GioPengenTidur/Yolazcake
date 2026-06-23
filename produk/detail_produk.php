<?php
include '../config/koneksi.php';

$id = $_GET['id'];

$query = mysqli_query(
    $conn,
    "SELECT * FROM produk WHERE id_produk='$id'"
);

$produk = mysqli_fetch_assoc($query);

// Stok status
$stok = (int)$produk['stok'];
if($stok <= 0){
    $stok_class = 'stok-habis'; $stok_label = 'Stok Habis'; $stok_icon = '❌';
} elseif($stok <= 5){
    $stok_class = 'stok-low'; $stok_label = $stok.' pcs (Hampir Habis)'; $stok_icon = '⚠️';
} else {
    $stok_class = 'stok-ok'; $stok_label = $stok.' pcs'; $stok_icon = '✅';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detail Produk – <?= htmlspecialchars($produk['nama_produk']); ?> | YOLAZCAKE</title>
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
      content:''; position:fixed; inset:0;
      background:
        radial-gradient(ellipse at 20% 30%,rgba(212,175,55,.10) 0%,transparent 55%),
        radial-gradient(ellipse at 80% 70%,rgba(232,160,191,.10) 0%,transparent 55%);
      pointer-events:none; z-index:0;
    }

    /* ─── HERO ─── */
    .page-hero {
      position:relative; height:260px;
      display:flex; flex-direction:column; align-items:center; justify-content:center;
      overflow:hidden;
      background:linear-gradient(135deg,#2b1a11 0%,#4a2c1a 40%,#6d3e26 70%,#3a1f0e 100%);
      z-index:1;
    }

    .page-hero::before {
      content:''; position:absolute; inset:0;
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
      font-size:.9em; color:rgba(255,255,255,.65); margin-top:10px;
      opacity:0; animation:fadeSlideDown .9s forwards .9s;
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

    /* back link */
    .back-link{
      position:relative;z-index:2;
      display:inline-flex;align-items:center;gap:8px;
      margin:32px auto 0;padding:0 32px;max-width:900px;width:100%;
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

    /* PAGE WRAPPER */
    .page-wrapper{
      position:relative;z-index:1;
      display:flex;flex-direction:column;align-items:center;
      padding:28px 20px 80px;
      max-width:900px;margin:0 auto;
    }

    /* TOP BAR */
    .top-bar{
      width:100%;display:flex;align-items:center;justify-content:space-between;
      margin-bottom:28px;flex-wrap:wrap;gap:14px;
    }

    .section-title{
      font-family:'Playfair Display',serif;font-size:1.6em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,#D4AF37 70%);
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
    }

    .detail-badge{
      display:inline-flex;align-items:center;gap:8px;
      background:linear-gradient(135deg,rgba(100,160,255,.15),rgba(100,160,255,.06));
      border:1px solid rgba(100,160,255,.35);
      color:#7ab3ff;font-size:.78em;font-weight:600;letter-spacing:1.5px;
      padding:6px 18px;border-radius:999px;
    }

    /* ACTION BUTTONS */
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

    .btn-edit{
      background:linear-gradient(135deg,rgba(255,180,50,.9) 0%,rgba(200,120,10,.95) 50%,rgba(255,180,50,.9) 100%);
      background-size:200% 100%;color:#1e0e3a;
      animation:goldSlide 3s linear infinite;
      box-shadow:0 8px 28px rgba(255,180,50,.3),0 0 40px rgba(255,180,50,.12);
    }

    .btn-edit:hover{box-shadow:0 12px 40px rgba(255,180,50,.5),0 0 60px rgba(255,180,50,.25);}

    /* MAIN CARD */
    .main-card{
      width:100%;
      background:rgba(255,255,255,.06);
      backdrop-filter:blur(20px);
      border:1px solid rgba(255,255,255,.1);
      border-radius:28px;
      position:relative;overflow:hidden;
      opacity:0;transform:translateY(40px);
      animation:cardReveal .85s cubic-bezier(.22,.68,0,1.2) forwards .6s;
      transition:border-color .45s,box-shadow .45s;
    }

    .main-card:hover{
      border-color:rgba(212,175,55,.35);
      box-shadow:
        0 25px 65px rgba(0,0,0,.35),
        0 0 35px rgba(212,175,55,.28),
        0 0 70px rgba(212,175,55,.14),
        0 0 110px rgba(212,175,55,.06);
    }

    .main-card::before{
      content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);
      background-size:200% 100%;animation:goldSlide 4s linear infinite;
    }

    @keyframes cardReveal{to{opacity:1;transform:translateY(0);}}

    /* GOLD RULE */
    .gold-rule-h{display:flex;align-items:center;gap:10px;padding:20px 28px 4px;}
    .gold-rule-h::before,.gold-rule-h::after{content:'';flex:1;height:1px;background:linear-gradient(to right,transparent,rgba(212,175,55,.5));}
    .gold-rule-h::after{background:linear-gradient(to left,transparent,rgba(212,175,55,.5));}
    .gold-rule-h span{color:#D4AF37;font-size:.65em;letter-spacing:3px;}

    /* DETAIL LAYOUT */
    .detail-body{
      padding:12px 32px 40px;
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:36px;
      align-items:start;
    }

    /* ─── FOTO COLUMN ─── */
    .foto-col{display:flex;flex-direction:column;gap:16px;}

    .foto-frame{
      position:relative;
      border-radius:20px;
      overflow:hidden;
      border:1px solid rgba(212,175,55,.2);
      background:rgba(0,0,0,.2);
      box-shadow:0 20px 60px rgba(0,0,0,.4);
      transition:box-shadow .4s;
    }

    .foto-frame:hover{
      box-shadow:0 30px 80px rgba(0,0,0,.5),0 0 40px rgba(212,175,55,.25);
    }

    .foto-frame::after{
      content:'';position:absolute;inset:0;
      background:linear-gradient(to bottom,transparent 60%,rgba(0,0,0,.4));
      pointer-events:none;
    }

    .prod-foto{
      width:100%;aspect-ratio:1/1;object-fit:cover;
      display:block;
      transition:transform .5s cubic-bezier(.22,.68,0,1.1);
    }

    .foto-frame:hover .prod-foto{
      transform:scale(1.05);
    }

    /* Corner accents on photo */
    .foto-frame .corner{
      position:absolute;width:20px;height:20px;z-index:2;
    }
    .foto-frame .corner-tl{top:12px;left:12px;border-top:2px solid #D4AF37;border-left:2px solid #D4AF37;border-radius:4px 0 0 0;}
    .foto-frame .corner-tr{top:12px;right:12px;border-top:2px solid #D4AF37;border-right:2px solid #D4AF37;border-radius:0 4px 0 0;}
    .foto-frame .corner-bl{bottom:12px;left:12px;border-bottom:2px solid #D4AF37;border-left:2px solid #D4AF37;border-radius:0 0 0 4px;}
    .foto-frame .corner-br{bottom:12px;right:12px;border-bottom:2px solid #D4AF37;border-right:2px solid #D4AF37;border-radius:0 0 4px 0;}

    .foto-caption{
      text-align:center;
      font-size:.72em;font-weight:500;letter-spacing:2px;text-transform:uppercase;
      color:rgba(212,175,55,.5);
    }

    /* ─── INFO COLUMN ─── */
    .info-col{
      display:flex;flex-direction:column;gap:0;
      padding-top:8px;
    }

    .prod-name-big{
      font-family:'Playfair Display',serif;
      font-size:1.9em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,#D4AF37 70%);
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
      line-height:1.25;
      margin-bottom:10px;
    }

    .divider-gold{
      height:1px;
      background:linear-gradient(to right,rgba(212,175,55,.5),transparent);
      margin:16px 0;
    }

    /* Price display */
    .price-block{
      display:flex;flex-direction:column;gap:4px;
      margin-bottom:4px;
    }

    .price-label{
      font-size:.68em;font-weight:600;letter-spacing:2.5px;text-transform:uppercase;
      color:rgba(212,175,55,.55);
    }

    .price-value{
      font-size:2em;font-weight:700;
      background:linear-gradient(135deg,#D4AF37 0%,#FFE4B5 50%,#D4AF37 100%);
      background-size:200% 100%;
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
      animation:goldSlide 4s linear infinite;
      line-height:1.15;
    }

    /* Info rows */
    .info-row{
      display:flex;flex-direction:column;gap:6px;
      padding:16px 0;
      border-bottom:1px solid rgba(255,255,255,.06);
    }

    .info-row:last-of-type{border-bottom:none;}

    .info-key{
      font-size:.68em;font-weight:600;letter-spacing:2.5px;text-transform:uppercase;
      color:rgba(212,175,55,.55);
    }

    .info-val{
      font-size:.92em;color:rgba(255,255,255,.85);line-height:1.7;
    }

    /* Stok badge */
    .stok-badge{
      display:inline-flex;align-items:center;gap:8px;
      padding:7px 18px;border-radius:999px;font-size:.82em;font-weight:600;
      letter-spacing:1px;width:fit-content;
    }

    .stok-ok{
      background:rgba(99,250,180,.12);
      border:1px solid rgba(99,250,180,.3);
      color:#6efabc;
    }

    .stok-low{
      background:rgba(255,180,50,.12);
      border:1px solid rgba(255,180,50,.3);
      color:#ffb432;
    }

    .stok-habis{
      background:rgba(255,80,80,.12);
      border:1px solid rgba(255,80,80,.3);
      color:#ff6060;
    }

    /* Deskripsi box */
    .desc-box{
      background:rgba(212,175,55,.04);
      border:1px solid rgba(212,175,55,.12);
      border-radius:14px;
      padding:16px 18px;
      font-size:.9em;color:rgba(255,255,255,.75);
      line-height:1.8;
      margin-top:4px;
    }

    /* CTA buttons */
    .cta-row{
      display:flex;gap:12px;margin-top:24px;flex-wrap:wrap;
    }

    .btn-back{
      background:rgba(255,255,255,.06);
      border:1px solid rgba(255,255,255,.15);
      color:rgba(255,255,255,.65);
      padding:13px 24px;
      transition:background .3s,border-color .3s,color .3s,transform .25s;
    }

    .btn-back:hover{
      background:rgba(255,255,255,.1);
      border-color:rgba(255,255,255,.3);
      color:#fff;
      transform:translateY(-2px);
    }

    .btn-hapus-detail{
      background:rgba(255,80,80,.12);
      border:1px solid rgba(255,80,80,.3);
      color:#ff6060;
      padding:13px 24px;
      transition:background .3s,box-shadow .3s,transform .25s;
    }

    .btn-hapus-detail:hover{
      background:rgba(255,80,80,.2);
      box-shadow:0 6px 20px rgba(255,80,80,.25);
      transform:translateY(-2px);
    }

    /* META STRIP at bottom of card */
    .meta-strip{
      grid-column:1/-1;
      border-top:1px solid rgba(255,255,255,.07);
      padding:18px 0 0;
      display:flex;gap:24px;flex-wrap:wrap;
      margin-top:4px;
    }

    .meta-chip{
      display:inline-flex;align-items:center;gap:8px;
      font-size:.72em;color:rgba(255,255,255,.35);letter-spacing:1.2px;
    }

    .meta-chip span{color:rgba(212,175,55,.6);}

    /* PARTICLES */
    .particle{position:fixed;border-radius:50%;pointer-events:none;animation:particleFloat linear infinite;z-index:0;}
    @keyframes particleFloat{
      0%{transform:translateY(100vh) scale(0);opacity:0;}
      10%{opacity:.5;}90%{opacity:.3;}
      100%{transform:translateY(-100px) scale(1);opacity:0;}
    }

    @media(max-width:680px){
      .hero-inner h1{font-size:2em;}
      .detail-body{grid-template-columns:1fr;gap:24px;padding:12px 20px 28px;}
      .cta-row{flex-direction:column;}
      .meta-strip{grid-column:unset;}
    }
  </style>
</head>
<body>

<div id="particles"></div>

<!-- HERO -->
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow">✦ YOLAZCAKE Sintang ✦</p>
    <h1>Detail Produk</h1>
    <p class="hero-sub">Informasi lengkap produk YOLAZCAKE</p>
    <div class="hero-divider">
      <span></span><span class="diamond">✦ ✦ ✦</span><span></span>
    </div>
  </div>
</div>

<div class="back-link">
  <a href="data_produk.php">← Kembali ke Data Produk</a>
</div>

<div class="page-wrapper">

  <div class="top-bar">
    <div>
      <div class="detail-badge">🔍 Detail Produk</div>
      <h2 class="section-title" style="margin-top:10px;">
        <?= htmlspecialchars($produk['nama_produk']); ?>
      </h2>
    </div>
    <a href="edit_produk.php?id=<?= $produk['id_produk']; ?>" class="btn-premium btn-edit">✏️ Edit Produk</a>
  </div>

  <div class="main-card">
    <div class="gold-rule-h"><span>✦ ✦ ✦</span></div>

    <div class="detail-body">

      <!-- FOTO COLUMN -->
      <div class="foto-col">
        <div class="foto-frame">
          <div class="corner corner-tl"></div>
          <div class="corner corner-tr"></div>
          <div class="corner corner-bl"></div>
          <div class="corner corner-br"></div>
          <img
            src="../assets/img/produk/<?= htmlspecialchars($produk['foto']); ?>"
            alt="<?= htmlspecialchars($produk['nama_produk']); ?>"
            class="prod-foto">
        </div>
        <div class="foto-caption">📷 Foto Produk Resmi</div>
      </div>

      <!-- INFO COLUMN -->
      <div class="info-col">

        <h2 class="prod-name-big"><?= htmlspecialchars($produk['nama_produk']); ?></h2>

        <div class="price-block">
          <span class="price-label">Harga</span>
          <span class="price-value">Rp <?= number_format($produk['harga'],0,',','.'); ?></span>
        </div>

        <div class="divider-gold"></div>

        <div class="info-row">
          <span class="info-key">Status Stok</span>
          <span class="stok-badge <?= $stok_class; ?>"><?= $stok_icon; ?> <?= $stok_label; ?></span>
        </div>

        <div class="info-row">
          <span class="info-key">Deskripsi Produk</span>
          <div class="desc-box">
            <?= nl2br(htmlspecialchars($produk['deskripsi'])); ?>
          </div>
        </div>

        <div class="info-row">
          <span class="info-key">ID Produk</span>
          <span class="info-val" style="font-family:monospace;color:rgba(212,175,55,.6);font-size:.85em;">
            #<?= str_pad($produk['id_produk'], 4, '0', STR_PAD_LEFT); ?>
          </span>
        </div>

        <!-- CTA Buttons -->
        <div class="cta-row">
          <a href="data_produk.php" class="btn-premium btn-back">← Kembali</a>
          <a href="edit_produk.php?id=<?= $produk['id_produk']; ?>" class="btn-premium btn-edit">✏️ Edit</a>
          <a href="hapus.php?id=<?= $produk['id_produk']; ?>"
             class="btn-premium btn-hapus-detail"
             onclick="return confirm('Yakin ingin menghapus produk ini?')">🗑 Hapus</a>
        </div>

      </div>

      <!-- META STRIP -->
      <div class="meta-strip">
        <div class="meta-chip">🎂 <span>YOLAZCAKE</span> Sintang</div>
        <div class="meta-chip">📦 Kategori: <span>Produk Toko</span></div>
        <div class="meta-chip">💰 Harga: <span>Rp <?= number_format($produk['harga'],0,',','.'); ?></span></div>
        <div class="meta-chip">📊 Stok: <span><?= $stok; ?> pcs</span></div>
      </div>

    </div>
  </div>

</div>

<!-- FOOTER -->
<div style="position:relative;z-index:1;text-align:center;padding:36px 20px;font-size:.8em;color:rgba(255,255,255,.5);border-top:1px solid rgba(255,255,255,.06);line-height:1.8;">
  © 2026 YOLAZCAKE Sintang • Cafe • Bakery • Boutique<br>
  Jl. Lintas Melawi, Ladang, Sintang, Kalimantan Barat<br>
  WA: 0815-7815-7888
</div>

<script>
  // Hero sparkles
  (function(){
    const hero=document.getElementById('pageHero');
    const colors=['#D4AF37','#FFE4B5','#E8A0BF','#fff','#f9ce34','#b8860b'];
    for(let i=0;i<22;i++){
      const d=document.createElement('div');d.className='sparkle';
      const s=Math.random()*5+2;
      d.style.cssText=`width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;bottom:${Math.random()*30}%;animation-duration:${4+Math.random()*7}s;animation-delay:${Math.random()*5}s;opacity:0;`;
      hero.appendChild(d);
    }
  })();

  // Background particles
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
</script>

</body>
</html>
