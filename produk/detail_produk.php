<?php
session_start();
require_once __DIR__.'/../config/staff_guard.php';
require_staff_login();
include '../config/koneksi.php';
require_once '../config/ulasan_helper.php';

// Moderasi: admin bisa menghapus ulasan langsung dari halaman ini
if (isset($_GET['hapus_ulasan'])) {
    $idUlasan = (int)$_GET['hapus_ulasan'];
    $stmtHapus = $conn->prepare("DELETE FROM ulasan_produk WHERE id_ulasan=?");
    $stmtHapus->bind_param("i", $idUlasan);
    $stmtHapus->execute();
    $stmtHapus->close();
    header("Location: detail_produk.php?id=".(int)($_GET['id'] ?? 0));
    exit();
}

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare(
    "SELECT p.*, COALESCE(k.nama_kategori, 'Lainnya') AS nama_kategori, k.icon AS kategori_icon
     FROM produk p
     LEFT JOIN kategori k ON k.id_kategori = p.id_kategori
     WHERE p.id_produk = ?"
);

$stmt->bind_param("i", $id);

$stmt->execute();
$result = $stmt->get_result();
$produk = $result->fetch_assoc();

if(!$produk){
    header("Location: data_produk.php");
    exit();
}

$ringkasan_rating = get_ringkasan_rating_produk($conn, $id);
$daftar_ulasan     = get_ulasan_produk($conn, $id);

$stok = (int)$produk['stok'];
if($stok <= 0){
    $stok_class = 'stok-habis'; $stok_label = 'Habis';
} elseif($stok <= 5){
    $stok_class = 'stok-low'; $stok_label = $stok.' pcs tersisa';
} else {
    $stok_class = 'stok-ok'; $stok_label = $stok.' pcs tersedia';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="../assets/css/lucide-icons.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detail Produk – YOLAZCAKE</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}

    body{
      min-height:100vh;
      font-family:'Inter',sans-serif;
      background:linear-gradient(160deg,#1e0e3a 0%,#2d1560 50%,#1a0a2e 100%);
      position:relative;
      overflow-x:hidden;
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
      position:relative;height:260px;
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
    @keyframes floatDot{
      0%{transform:translateY(0) rotate(0deg);opacity:0;}
      20%{opacity:1;}80%{opacity:.8;}
      100%{transform:translateY(-280px) rotate(360deg);opacity:0;}
    }

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
      margin:32px auto 0;padding:0 32px;max-width:820px;width:100%;
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
      padding:28px 20px 80px;
      max-width:820px;margin:0 auto;
    }

    /* ── TOP BAR ── */
    .top-bar{
      width:100%;display:flex;align-items:center;justify-content:space-between;
      margin-bottom:28px;flex-wrap:wrap;gap:14px;
    }
    .section-title{
      font-family:'Playfair Display',serif;font-size:1.6em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,#D4AF37 70%);
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
    }
    .new-badge{
      display:inline-flex;align-items:center;gap:8px;
      background:linear-gradient(135deg,rgba(100,160,255,.15),rgba(100,160,255,.06));
      border:1px solid rgba(100,160,255,.35);
      color:#7ab3ff;font-size:.78em;font-weight:600;letter-spacing:1.5px;
      padding:6px 18px;border-radius:999px;
    }

    /* ── MAIN CARD ── */
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

    /* ── GOLD RULE ── */
    .gold-rule-h{display:flex;align-items:center;gap:10px;padding:20px 28px 4px;}
    .gold-rule-h::before,.gold-rule-h::after{content:'';flex:1;height:1px;background:linear-gradient(to right,transparent,rgba(212,175,55,.5));}
    .gold-rule-h::after{background:linear-gradient(to left,transparent,rgba(212,175,55,.5));}
    .gold-rule-h span{color:#D4AF37;font-size:.65em;letter-spacing:3px;}

    /* ── DETAIL BODY ── */
    .detail-body{
      padding:20px 32px 36px;
      display:grid;
      grid-template-columns:280px 1fr;
      gap:32px;
    }
    @media(max-width:640px){
      .detail-body{grid-template-columns:1fr;padding:16px 20px 28px;}
    }

    /* ── PHOTO ── */
    .detail-photo-wrap{
      position:relative;
      border-radius:20px;
      overflow:hidden;
      opacity:0;transform:translateX(-20px);
      animation:slideInLeft .6s forwards .75s;
      border:1px solid rgba(212,175,55,.25);
      box-shadow:0 12px 35px rgba(0,0,0,.3);
      height:fit-content;
    }
    @keyframes slideInLeft{to{opacity:1;transform:translateX(0);}}

    .detail-photo-wrap img{
      width:100%;
      height:280px;
      object-fit:cover;
      display:block;
      transition:transform .6s cubic-bezier(.25,.46,.45,.94);
    }
    .detail-photo-wrap:hover img{
      transform:scale(1.08);
    }

    /* ── INFO ── */
    .detail-info{
      display:flex;flex-direction:column;gap:18px;
      opacity:0;transform:translateY(20px);
      animation:fadeSlideDown .7s forwards .95s;
    }

    .detail-name{
      font-family:'Playfair Display',serif;
      font-size:1.9em;font-weight:700;
      color:#fff;line-height:1.25;
    }

    .detail-price{
      font-size:1.6em;font-weight:700;
      background:linear-gradient(135deg,#D4AF37 0%,#FFE4B5 50%,#D4AF37 100%);
      background-size:200% 100%;
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
      animation:goldSlide 4s linear infinite;
      width:fit-content;
    }

    .stok-badge{
      display:inline-flex;align-items:center;gap:6px;
      width:fit-content;
      padding:6px 16px;border-radius:999px;font-size:.78em;font-weight:600;
      letter-spacing:1px;
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

    .detail-divider{
      height:1px;
      background:linear-gradient(to right,rgba(212,175,55,.4),transparent);
      margin:2px 0;
    }

    .detail-desc-label{
      font-size:.76em;font-weight:600;letter-spacing:2px;text-transform:uppercase;
      color:rgba(212,175,55,.9);
      display:flex;align-items:center;gap:8px;
    }

    .detail-desc-text{
      font-size:.95em;line-height:1.75;
      color:rgba(255,255,255,.75);
      background:rgba(255,255,255,.04);
      border:1px solid rgba(255,255,255,.08);
      border-radius:14px;
      padding:16px 18px;
      white-space:pre-line;
    }

    /* ── ACTION BUTTONS ── */
    .detail-actions{
      display:flex;gap:14px;flex-wrap:wrap;margin-top:6px;
      opacity:0;animation:fadeSlideDown .6s forwards 1.2s;
    }

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
      background:linear-gradient(135deg,#D4AF37 0%,#b8860b 50%,#D4AF37 100%);
      background-size:200% 100%;color:#1e0e3a;
      animation:goldSlide 3s linear infinite;
      box-shadow:0 8px 28px rgba(212,175,55,.35),0 0 40px rgba(212,175,55,.18);
    }
    .btn-edit:hover{box-shadow:0 12px 40px rgba(212,175,55,.55),0 0 60px rgba(212,175,55,.3);}

    .btn-hapus{
      background:rgba(255,80,80,.15);
      border:1px solid rgba(255,80,80,.35);
      color:#ff6060;
    }
    .btn-hapus:hover{box-shadow:0 6px 20px rgba(255,80,80,.25);}

    .btn-back{
      background:rgba(255,255,255,.07);
      border:1px solid rgba(255,255,255,.15);
      color:rgba(255,255,255,.7);
    }
    .btn-back:hover{
      background:rgba(255,255,255,.12);
      border-color:rgba(255,255,255,.3);
      color:#fff;
    }

    /* ── PARTICLES ── */
    .particle{position:fixed;border-radius:50%;pointer-events:none;animation:particleFloat linear infinite;z-index:0;}
    @keyframes particleFloat{
      0%{transform:translateY(100vh) scale(0);opacity:0;}
      10%{opacity:.5;}90%{opacity:.3;}
      100%{transform:translateY(-100px) scale(1);opacity:0;}
    }

    @media(max-width:768px){
      .hero-inner h1{font-size:2em;}
      .detail-name{font-size:1.5em;}
      .detail-price{font-size:1.3em;}
    }

    /* ── ULASAN PRODUK (MODERASI) ── */
    .review-card{
      width:100%;margin-top:24px;
      background:rgba(255,255,255,.05);backdrop-filter:blur(20px);
      border:1px solid rgba(255,255,255,.1);border-radius:24px;
      padding:24px 28px;position:relative;overflow:hidden;
    }
    .review-card::before{
      content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);
      background-size:200% 100%;animation:goldSlide 4s linear infinite;
    }
    .review-card h3{
      font-family:'Playfair Display',serif;color:#D4AF37;font-size:1.2em;
      margin-bottom:6px;display:flex;align-items:center;gap:8px;
    }
    .review-summary{font-size:.85em;color:rgba(255,255,255,.55);margin-bottom:18px;}
    .review-item{
      background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);
      border-radius:14px;padding:14px 16px;margin-bottom:10px;
    }
    .review-item-head{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;}
    .review-item-name{font-weight:700;color:#fff;font-size:.9em;}
    .review-item-date{font-size:.72em;color:rgba(255,255,255,.4);}
    .review-item-stars{margin:4px 0;font-size:.85em;}
    .review-item-text{font-size:.85em;color:rgba(255,255,255,.7);line-height:1.6;margin-top:4px;}
    .review-item-del{
      display:inline-flex;align-items:center;gap:4px;margin-top:8px;padding:4px 10px;border-radius:7px;
      font-size:.72em;font-weight:600;text-decoration:none;
      background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);color:#fca5a5;
    }
    .review-item-del:hover{background:rgba(239,68,68,.25);}
    .review-empty{text-align:center;padding:24px;color:rgba(255,255,255,.4);font-size:.88em;}
  </style>
</head>
<body>

<div id="particles"></div>

<!-- HERO -->
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow"><i data-lucide="sparkle" class="lucide-ic"></i> YOLAZCAKE Sintang <i data-lucide="sparkle" class="lucide-ic"></i></p>
    <h1>Detail Produk</h1>
    <p class="hero-sub">Informasi lengkap mengenai produk pilihan Anda</p>
    <div class="hero-divider">
      <span></span><span class="diamond"><i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i></span><span></span>
    </div>
  </div>
</div>

<div class="back-link">
  <a href="data_produk.php"><i data-lucide="arrow-left" class="lucide-ic"></i> Kembali ke Data Produk</a>
</div>

<div class="page-wrapper">

  <div class="top-bar">
    <div>
      <div class="new-badge"><i data-lucide="search" class="lucide-ic"></i> Manajemen Produk</div>
      <h2 class="section-title" style="margin-top:10px;">Informasi Produk</h2>
    </div>
  </div>

  <div class="main-card">
    <div class="gold-rule-h"><span><i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i></span></div>

    <div class="detail-body">

      <div class="detail-photo-wrap">
        <img
          src="../assets/img/produk/<?= htmlspecialchars($produk['foto']); ?>"
          alt="<?= htmlspecialchars($produk['nama_produk']); ?>">
      </div>

      <div class="detail-info">

        <div class="detail-name"><?= htmlspecialchars($produk['nama_produk']); ?></div>

        <div class="detail-price">Rp <?= number_format($produk['harga'],0,',','.'); ?></div>

        <div style="display:flex;gap:10px;flex-wrap:wrap;">
          <div class="stok-badge <?= $stok_class; ?>"><i data-lucide="package" class="lucide-ic"></i> <?= $stok_label; ?></div>
          <div class="stok-badge" style="background:rgba(212,175,55,.12);border:1px solid rgba(212,175,55,.3);color:#D4AF37;">
            <?= htmlspecialchars($produk['kategori_icon'] ?? '<i data-lucide="utensils" class="lucide-ic"></i>'); ?> <?= htmlspecialchars($produk['nama_kategori']); ?>
          </div>
        </div>

        <div class="detail-divider"></div>

        <div>
          <div class="detail-desc-label"><span><i data-lucide="file-text" class="lucide-ic"></i></span> Deskripsi</div>
          <div class="detail-desc-text" style="margin-top:8px;">
            <?= nl2br(htmlspecialchars($produk['deskripsi'])); ?>
          </div>
        </div>

        <div class="detail-actions">
          <a href="edit_produk.php?id=<?= $produk['id_produk']; ?>" class="btn-premium btn-edit"><i data-lucide="pencil" class="lucide-ic"></i> Edit Produk</a>
          <a href="hapus.php?id=<?= $produk['id_produk']; ?>"
             class="btn-premium btn-hapus"
             onclick="return confirm('Yakin ingin menghapus produk ini?')"><i data-lucide="trash-2" class="lucide-ic"></i> Hapus</a>
          <a href="data_produk.php" class="btn-premium btn-back"><i data-lucide="arrow-left" class="lucide-ic"></i> Kembali</a>
        </div>

      </div>

    </div>

  </div><!-- /.main-card -->

  <!-- ULASAN PRODUK (MODERASI) -->
  <div class="review-card">
    <h3><i data-lucide="star" class="lucide-ic lucide-fill"></i> Ulasan Pelanggan</h3>
    <div class="review-summary">
      Rata-rata <strong style="color:#D4AF37;"><?= number_format($ringkasan_rating['avg'],1) ?></strong> / 5
      dari <?= $ringkasan_rating['jumlah'] ?> ulasan.
      Ulasan baru masuk otomatis lewat halaman menu / detail produk publik pelanggan.
    </div>

    <?php if (!empty($daftar_ulasan)): foreach ($daftar_ulasan as $u): ?>
      <div class="review-item">
        <div class="review-item-head">
          <span class="review-item-name"><i data-lucide="user" class="lucide-ic"></i> <?= htmlspecialchars($u['nama_reviewer']) ?></span>
          <span class="review-item-date"><?= date('d M Y H:i', strtotime($u['created_at'])) ?></span>
        </div>
        <div class="review-item-stars">
          <?php for($s=1;$s<=5;$s++): ?>
            <span style="color:<?= $s <= $u['rating'] ? '#D4AF37' : 'rgba(255,255,255,.2)' ?>;"><i data-lucide="star" class="lucide-ic lucide-fill"></i></span>
          <?php endfor; ?>
        </div>
        <?php if (!empty($u['komentar'])): ?>
          <div class="review-item-text"><?= nl2br(htmlspecialchars($u['komentar'])) ?></div>
        <?php endif; ?>
        <a href="?id=<?= $id ?>&hapus_ulasan=<?= $u['id_ulasan'] ?>" class="review-item-del"
           onclick="return confirm('Hapus ulasan ini?')"><i data-lucide="trash-2" class="lucide-ic"></i> Hapus Ulasan</a>
      </div>
    <?php endforeach; else: ?>
      <div class="review-empty"><i data-lucide="inbox" class="lucide-ic"></i> Belum ada ulasan untuk produk ini.</div>
    <?php endif; ?>
  </div>

</div><!-- /.page-wrapper -->

<!-- FOOTER -->
<div style="position:relative;z-index:1;text-align:center;padding:36px 20px;font-size:.8em;color:rgba(255,255,255,.5);border-top:1px solid rgba(255,255,255,.06);line-height:1.8;">
  © 2026 YOLAZCAKE Sintang • Cafe • Bakery • Boutique<br>
  Jl. Lintas Melawi, Ladang, Sintang, Kalimantan Barat<br>
  WA: 0815-7815-7888
</div>

<script>
  /* Hero sparkles */
  (function(){
    const hero = document.getElementById('pageHero');
    const colors = ['#D4AF37','#FFE4B5','#E8A0BF','#fff','#f9ce34','#b8860b'];
    for(let i = 0; i < 22; i++){
      const d = document.createElement('div'); d.className = 'sparkle';
      const s = Math.random() * 5 + 2;
      d.style.cssText = `width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;bottom:${Math.random()*30}%;animation-duration:${4+Math.random()*7}s;animation-delay:${Math.random()*5}s;opacity:0;`;
      hero.appendChild(d);
    }
  })();

  /* Background particles */
  (function(){
    const c = document.getElementById('particles');
    const colors = ['rgba(212,175,55,.4)','rgba(232,160,191,.3)','rgba(255,255,255,.15)'];
    for(let i = 0; i < 16; i++){
      const p = document.createElement('div'); p.className = 'particle';
      const s = Math.random() * 5 + 2;
      p.style.cssText = `width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;animation-duration:${10+Math.random()*12}s;animation-delay:${Math.random()*10}s;`;
      c.appendChild(p);
    }
  })();
</script>


<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide){lucide.createIcons();}</script>
</body>
</html>
