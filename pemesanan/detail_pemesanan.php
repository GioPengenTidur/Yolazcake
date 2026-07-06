<?php
session_start();
require_once __DIR__.'/../config/staff_guard.php';
require_staff_login();
include '../config/koneksi.php';

$id_pemesanan = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM pemesanan WHERE id_pemesanan = ?");
$stmt->bind_param("i", $id_pemesanan);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if(!$data){
    die("Data pemesanan tidak ditemukan");
}

// Ambil rincian item pesanan dari tabel detail_pemesanan (join ke produk
// untuk nama & foto produk). Sebelumnya halaman ini tidak pernah
// menampilkan isi pesanan sama sekali walau tabelnya sudah ada.
$stmtItems = $conn->prepare(
    "SELECT dp.*, p.nama_produk, p.foto
     FROM detail_pemesanan dp
     LEFT JOIN produk p ON p.id_produk = dp.id_produk
     WHERE dp.id_pemesanan = ?"
);
$stmtItems->bind_param("i", $id_pemesanan);
$stmtItems->execute();
$items = $stmtItems->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtItems->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detail Pemesanan – YOLAZCAKE</title>
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
      background:linear-gradient(135deg,rgba(99,149,250,.15),rgba(99,149,250,.06));
      border:1px solid rgba(99,149,250,.35);
      color:#6395fa;font-size:.78em;font-weight:600;letter-spacing:1.5px;
      padding:6px 18px;border-radius:999px;
    }

    /* ── GOLD RULE ── */
    .gold-rule{display:flex;align-items:center;gap:12px;width:100%;margin-bottom:28px;}
    .gold-rule::before,.gold-rule::after{content:'';flex:1;height:1px;background:linear-gradient(to right,transparent,rgba(212,175,55,.4));}
    .gold-rule::after{background:linear-gradient(to left,transparent,rgba(212,175,55,.4));}
    .gold-rule span{color:#D4AF37;font-size:.65em;letter-spacing:3px;white-space:nowrap;}

    /* ── KODE PESANAN BANNER ── */
    .kode-banner{
      width:100%;
      background:linear-gradient(135deg,rgba(212,175,55,.12) 0%,rgba(212,175,55,.05) 100%);
      border:1px solid rgba(212,175,55,.35);
      border-radius:20px;padding:20px 28px;
      display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;
      margin-bottom:28px;
      opacity:0;animation:fadeSlideDown .7s forwards .3s;
    }
    .kode-banner-left{display:flex;flex-direction:column;gap:4px;}
    .kode-label{font-size:.72em;color:rgba(212,175,55,.7);letter-spacing:2px;text-transform:uppercase;}
    .kode-value{
      font-family:'Playfair Display',serif;font-size:1.5em;font-weight:700;color:#D4AF37;
      letter-spacing:2px;
    }

    /* ── STATUS BADGES ── */
    .status-badge{
      display:inline-flex;align-items:center;gap:6px;
      padding:7px 18px;border-radius:999px;font-size:.85em;font-weight:600;letter-spacing:.5px;
    }
    .status-badge::before{content:'';width:8px;height:8px;border-radius:50%;display:block;}

    .status-menunggu{background:rgba(255,180,50,.15);border:1px solid rgba(255,180,50,.35);color:#ffb432;}
    .status-menunggu::before{background:#ffb432;box-shadow:0 0 8px #ffb432;}

    .status-diproses{background:rgba(99,149,250,.15);border:1px solid rgba(99,149,250,.35);color:#6395fa;}
    .status-diproses::before{background:#6395fa;box-shadow:0 0 8px #6395fa;}

    .status-siap{background:rgba(180,99,250,.15);border:1px solid rgba(180,99,250,.35);color:#c06efa;}
    .status-siap::before{background:#c06efa;box-shadow:0 0 8px #c06efa;}

    .status-selesai{background:rgba(99,250,180,.15);border:1px solid rgba(99,250,180,.35);color:#6efabc;}
    .status-selesai::before{background:#6efabc;box-shadow:0 0 8px #6efabc;}

    .status-batal{background:rgba(255,80,80,.15);border:1px solid rgba(255,80,80,.35);color:#ff6060;}
    .status-batal::before{background:#ff6060;box-shadow:0 0 8px #ff6060;}

    /* ── DETAIL CARD ── */
    .detail-card{
      width:100%;
      background:rgba(255,255,255,.05);
      backdrop-filter:blur(20px);
      border:1px solid rgba(255,255,255,.1);
      border-radius:24px;
      overflow:hidden;
      position:relative;
      opacity:0;animation:fadeSlideDown .9s forwards .5s;
    }
    .detail-card::before{
      content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);
      background-size:200% 100%;animation:goldSlide 4s linear infinite;
    }

    .detail-card-header{
      padding:22px 28px;
      background:linear-gradient(135deg,rgba(212,175,55,.1),rgba(212,175,55,.04));
      border-bottom:1px solid rgba(212,175,55,.2);
      display:flex;align-items:center;gap:12px;
    }
    .detail-card-header h3{
      font-family:'Playfair Display',serif;font-size:1.1em;font-weight:700;
      background:linear-gradient(135deg,#fff,#D4AF37);
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
    }

    /* ── DETAIL ROWS ── */
    .detail-row{
      display:flex;align-items:stretch;
      border-bottom:1px solid rgba(255,255,255,.06);
      transition:background .25s;
    }
    .detail-row:last-child{border-bottom:none;}
    .detail-row:hover{background:rgba(212,175,55,.04);}

    .detail-label{
      width:240px;flex-shrink:0;
      padding:16px 28px;
      font-size:.82em;font-weight:600;letter-spacing:.5px;
      color:rgba(212,175,55,.8);text-transform:uppercase;
      background:rgba(212,175,55,.05);
      border-right:1px solid rgba(212,175,55,.15);
      display:flex;align-items:center;gap:10px;
    }
    .detail-label .row-icon{font-size:1.1em;width:20px;text-align:center;}

    .detail-val{
      flex:1;padding:16px 28px;
      font-size:.92em;color:rgba(255,255,255,.9);
      display:flex;align-items:center;
    }

    .detail-val.highlight{
      color:#6efabc;font-weight:700;font-size:1.05em;
    }
    .detail-val.gold-text{
      color:#D4AF37;font-weight:600;
    }

    /* ── SUMMARY BOX ── */
    .summary-box{
      width:100%;margin-top:28px;
      background:linear-gradient(135deg,rgba(99,250,180,.08),rgba(99,250,180,.03));
      border:1px solid rgba(99,250,180,.25);
      border-radius:20px;padding:24px 28px;
      display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;
      opacity:0;animation:fadeSlideDown .9s forwards .7s;
    }
    .summary-label{
      font-size:.8em;color:rgba(255,255,255,.5);letter-spacing:1.5px;text-transform:uppercase;margin-bottom:4px;
    }
    .summary-amount{
      font-family:'Playfair Display',serif;font-size:2em;font-weight:700;
      background:linear-gradient(135deg,#6efabc,#D4AF37);
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
    }

    /* ── ACTION BUTTONS ── */
    .actions{
      width:100%;margin-top:28px;
      display:flex;gap:14px;flex-wrap:wrap;
      opacity:0;animation:fadeSlideDown .9s forwards .9s;
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

    .btn-back{
      background:linear-gradient(135deg,rgba(212,175,55,.2),rgba(212,175,55,.08));
      border:1px solid rgba(212,175,55,.4);color:#D4AF37;
    }
    .btn-back:hover{
      background:linear-gradient(135deg,rgba(212,175,55,.3),rgba(212,175,55,.15));
      box-shadow:0 8px 28px rgba(212,175,55,.3);
    }

    .btn-home{
      background:linear-gradient(135deg,rgba(99,250,180,.2),rgba(99,250,180,.08));
      border:1px solid rgba(99,250,180,.4);color:#6efabc;
    }
    .btn-home:hover{
      background:linear-gradient(135deg,rgba(99,250,180,.3),rgba(99,250,180,.15));
      box-shadow:0 8px 28px rgba(99,250,180,.25);
    }

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

    @media(max-width:600px){
      .hero-inner h1{font-size:2em;}
      .detail-label{width:160px;padding:14px 16px;}
      .detail-val{padding:14px 16px;}
      .kode-value{font-size:1.2em;}
      .summary-amount{font-size:1.5em;}
    }
  </style>
</head>
<body>

<div id="particles"></div>

<!-- HERO -->
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow">✦ YOLAZCAKE Sintang ✦</p>
    <h1>Detail Pemesanan</h1>
    <p class="hero-sub">Informasi lengkap pesanan pelanggan</p>
    <div class="hero-divider">
      <span></span><span class="diamond">✦ ✦ ✦</span><span></span>
    </div>
  </div>
</div>



<!-- PAGE WRAPPER -->
<div class="page-wrapper">

  <!-- TOP BAR -->
  <div class="top-bar">
    <div>
      <div class="new-badge">🔍 Rincian Pesanan</div>
      <h2 class="section-title" style="margin-top:10px;">Detail Pemesanan</h2>
    </div>
    <?php
    $s = $data['status_pesanan'];
    if($s==='Menunggu')         echo "<span class='status-badge status-menunggu'>Menunggu</span>";
    elseif($s==='Diproses')     echo "<span class='status-badge status-diproses'>Diproses</span>";
    elseif($s==='Siap Diambil') echo "<span class='status-badge status-siap'>Siap Diambil</span>";
    elseif($s==='Selesai')      echo "<span class='status-badge status-selesai'>Selesai</span>";
    else                        echo "<span class='status-badge status-batal'>Dibatalkan</span>";
    ?>
  </div>

  <!-- GOLD RULE -->
  <div class="gold-rule"><span>✦ ✦ ✦</span></div>

  <!-- KODE BANNER -->
  <div class="kode-banner">
    <div class="kode-banner-left">
      <div class="kode-label">Kode Pesanan</div>
      <div class="kode-value"><?= htmlspecialchars($data['kode_pesanan']) ?></div>
    </div>
    <div style="font-size:2em;opacity:.4;">📋</div>
  </div>

  <!-- DETAIL CARD -->
  <div class="detail-card">
    <div class="detail-card-header">
      <span style="font-size:1.3em;">👤</span>
      <h3>Informasi Pemesan</h3>
    </div>

    <div class="detail-row">
      <div class="detail-label"><span class="row-icon">👤</span>Nama Pemesan</div>
      <div class="detail-val"><?= htmlspecialchars($data['nama_pemesan']) ?></div>
    </div>

    <div class="detail-row">
      <div class="detail-label"><span class="row-icon">📱</span>No. HP</div>
      <div class="detail-val"><?= htmlspecialchars($data['no_hp']) ?></div>
    </div>

    <?php if(!empty($data['nomor_meja'])): ?>
    <div class="detail-row">
      <div class="detail-label"><span class="row-icon">🪑</span>Nomor Meja</div>
      <div class="detail-val gold-text"><?= htmlspecialchars($data['nomor_meja']) ?></div>
    </div>
    <?php endif; ?>

    <div class="detail-row">
      <div class="detail-label"><span class="row-icon">📅</span>Tanggal Pemesanan</div>
      <div class="detail-val"><?= htmlspecialchars($data['tanggal']) ?></div>
    </div>

    <div class="detail-row">
      <div class="detail-label"><span class="row-icon">💳</span>Metode Pembayaran</div>
      <div class="detail-val gold-text"><?= htmlspecialchars($data['metode_pembayaran']) ?></div>
    </div>

    <div class="detail-row">
      <div class="detail-label"><span class="row-icon">🔖</span>Status Pembayaran</div>
      <div class="detail-val">
        <?php
        $sp = $data['status_pembayaran'] ?? '-';
        $spClass = ($sp === 'Lunas') ? 'color:#6efabc;font-weight:600;' : 'color:#ffb432;font-weight:600;';
        echo "<span style='$spClass'>$sp</span>";
        ?>
      </div>
    </div>

    <div class="detail-row">
      <div class="detail-label"><span class="row-icon">📦</span>Status Pesanan</div>
      <div class="detail-val">
        <?php
        if($s==='Menunggu')         echo "<span class='status-badge status-menunggu'>Menunggu</span>";
        elseif($s==='Diproses')     echo "<span class='status-badge status-diproses'>Diproses</span>";
        elseif($s==='Siap Diambil') echo "<span class='status-badge status-siap'>Siap Diambil</span>";
        elseif($s==='Selesai')      echo "<span class='status-badge status-selesai'>Selesai</span>";
        else                        echo "<span class='status-badge status-batal'>Dibatalkan</span>";
        ?>
      </div>
    </div>
  </div>

  <!-- ITEM PESANAN -->
  <?php if(!empty($items)): ?>
  <div class="detail-card" style="margin-top:20px;">
    <div class="gold-rule"><span>✦ Item Pesanan ✦</span></div>
    <table style="width:100%;border-collapse:collapse;color:#fff;">
      <thead>
        <tr style="border-bottom:1px solid rgba(212,175,55,.3);">
          <th style="text-align:left;padding:8px;font-size:.85em;opacity:.7;">Produk</th>
          <th style="text-align:center;padding:8px;font-size:.85em;opacity:.7;">Qty</th>
          <th style="text-align:right;padding:8px;font-size:.85em;opacity:.7;">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($items as $item): ?>
        <tr style="border-bottom:1px solid rgba(255,255,255,.08);">
          <td style="padding:8px;"><?= htmlspecialchars($item['nama_produk'] ?? 'Produk telah dihapus') ?></td>
          <td style="text-align:center;padding:8px;"><?= (int)$item['jumlah'] ?></td>
          <td style="text-align:right;padding:8px;">Rp <?= number_format($item['subtotal'],0,',','.') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <?php if(!empty($data['kode_promo']) && (float)($data['diskon_nominal'] ?? 0) > 0): ?>
  <div class="detail-card" style="margin-top:20px;">
    <div class="detail-row">
      <div class="detail-label"><span class="row-icon">🏷️</span>Kode Promo</div>
      <div class="detail-val" style="color:#6efabc;"><?= htmlspecialchars($data['kode_promo']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-label"><span class="row-icon">💸</span>Diskon</div>
      <div class="detail-val" style="color:#6efabc;">-Rp <?= number_format($data['diskon_nominal'],0,',','.') ?></div>
    </div>
  </div>
  <?php endif; ?>

  <!-- SUMMARY BOX -->
  <div class="summary-box">
    <div>
      <div class="summary-label">💰 Total Pembayaran</div>
      <div class="summary-amount">Rp <?= number_format($data['total_harga'],0,',','.') ?></div>
    </div>
    <div style="opacity:.3;font-size:3em;">✦</div>
  </div>

  <!-- ACTIONS -->
  <div class="actions">
    <a href="../index.php" class="btn-premium btn-home">🏠 Kembali ke Beranda</a>
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
</script>

</body>
</html>
