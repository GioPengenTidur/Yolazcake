<?php
session_start();
require_once __DIR__.'/../config/staff_guard.php';
require_staff_login();
include '../config/koneksi.php';

$query = mysqli_query($conn,
    "SELECT p.*, COALESCE(k.nama_kategori, 'Lainnya') AS nama_kategori, k.icon AS kategori_icon
     FROM produk p
     LEFT JOIN kategori k ON k.id_kategori = p.id_kategori
     ORDER BY p.id_produk DESC"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Produk – YOLAZCAKE</title>
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

    /* HERO */
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

    /* PAGE WRAPPER */
    .page-wrapper{
      position:relative;z-index:1;
      display:flex;flex-direction:column;align-items:center;
      padding:28px 20px 80px;
      max-width:1100px;margin:0 auto;
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

    .new-badge{
      display:inline-flex;align-items:center;gap:8px;
      background:linear-gradient(135deg,rgba(99,250,180,.15),rgba(99,250,180,.06));
      border:1px solid rgba(99,250,180,.35);
      color:#6efabc;font-size:.78em;font-weight:600;letter-spacing:1.5px;
      padding:6px 18px;border-radius:999px;
    }

    /* TAMBAH BUTTON */
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

    .btn-add{
      background:linear-gradient(135deg,#D4AF37 0%,#b8860b 50%,#D4AF37 100%);
      background-size:200% 100%;color:#1e0e3a;
      animation:goldSlide 3s linear infinite;
      box-shadow:0 8px 28px rgba(212,175,55,.35),0 0 40px rgba(212,175,55,.18);
    }

    .btn-add:hover{box-shadow:0 12px 40px rgba(212,175,55,.55),0 0 60px rgba(212,175,55,.3);}

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

    /* TABLE */
    .table-wrap{overflow-x:auto;padding:0 0 8px;}

    table{
      width:100%;border-collapse:collapse;
    }

    thead tr{
      background:rgba(212,175,55,.08);
      border-bottom:1px solid rgba(212,175,55,.25);
    }

    thead th{
      padding:16px 20px;
      font-size:.72em;font-weight:600;letter-spacing:2.5px;text-transform:uppercase;
      color:rgba(212,175,55,.9);text-align:left;white-space:nowrap;
    }

    thead th:first-child{padding-left:28px;text-align:center;width:60px;}
    thead th:nth-child(2){text-align:center;}
    thead th:last-child{text-align:center;}

    tbody tr{
      border-bottom:1px solid rgba(255,255,255,.05);
      transition:background .25s;
    }

    tbody tr:last-child{border-bottom:none;}

    tbody tr:hover{
      background:rgba(212,175,55,.05);
    }

    tbody td{
      padding:16px 20px;
      font-size:.9em;color:rgba(255,255,255,.8);
      vertical-align:middle;
    }

    tbody td:first-child{padding-left:28px;text-align:center;color:rgba(212,175,55,.6);font-size:.8em;font-weight:600;}
    tbody td:nth-child(2){text-align:center;}
    tbody td:last-child{text-align:center;}

    .prod-img{
      width:80px;height:80px;object-fit:cover;
      border-radius:12px;
      border:1px solid rgba(212,175,55,.2);
      transition:transform .3s,box-shadow .3s;
    }

    .prod-img:hover{
      transform:scale(1.08);
      box-shadow:0 0 20px rgba(212,175,55,.3);
    }

    .prod-name{
      font-weight:600;color:#fff;
      font-family:'Playfair Display',serif;font-size:1em;
    }

    .prod-price{
      color:#D4AF37;font-weight:600;font-size:.92em;
      white-space:nowrap;
    }

    .stok-badge{
      display:inline-block;
      padding:4px 14px;border-radius:999px;font-size:.75em;font-weight:600;
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

    /* ACTION BUTTONS */
    .action-wrap{display:flex;gap:8px;justify-content:center;flex-wrap:wrap;}

    .btn-act{
      position:relative;overflow:hidden;
      padding:8px 16px;border-radius:10px;border:none;
      font-family:'Inter',sans-serif;font-size:.75em;font-weight:600;
      letter-spacing:1.2px;text-transform:uppercase;
      text-decoration:none;cursor:pointer;
      transition:transform .25s,box-shadow .3s;
      white-space:nowrap;
    }

    .btn-act::before{
      content:'';position:absolute;inset:0;
      background:linear-gradient(90deg,transparent,rgba(255,255,255,.18),transparent);
      transform:translateX(-100%);transition:transform .45s;
    }

    .btn-act:hover::before{transform:translateX(100%);}
    .btn-act:hover{transform:translateY(-2px);}

    .btn-detail{
      background:rgba(100,160,255,.15);
      border:1px solid rgba(100,160,255,.35);
      color:#7ab3ff;
    }
    .btn-detail:hover{box-shadow:0 6px 20px rgba(100,160,255,.25);}

    .btn-edit{
      background:rgba(255,180,50,.15);
      border:1px solid rgba(255,180,50,.35);
      color:#ffb432;
    }
    .btn-edit:hover{box-shadow:0 6px 20px rgba(255,180,50,.25);}

    .btn-hapus{
      background:rgba(255,80,80,.15);
      border:1px solid rgba(255,80,80,.35);
      color:#ff6060;
    }
    .btn-hapus:hover{box-shadow:0 6px 20px rgba(255,80,80,.25);}

    /* EMPTY STATE */
    .empty-state{
      text-align:center;padding:70px 20px;
    }
    .empty-state .empty-icon{font-size:3.5em;margin-bottom:16px;opacity:.5;}
    .empty-state p{color:rgba(255,255,255,.4);font-size:.92em;}

    /* GOLD RULE */
    .gold-rule-h{display:flex;align-items:center;gap:10px;padding:20px 28px 4px;}
    .gold-rule-h::before,.gold-rule-h::after{content:'';flex:1;height:1px;background:linear-gradient(to right,transparent,rgba(212,175,55,.5));}
    .gold-rule-h::after{background:linear-gradient(to left,transparent,rgba(212,175,55,.5));}
    .gold-rule-h span{color:#D4AF37;font-size:.65em;letter-spacing:3px;}

    /* PARTICLES */
    .particle{position:fixed;border-radius:50%;pointer-events:none;animation:particleFloat linear infinite;z-index:0;}
    @keyframes particleFloat{
      0%{transform:translateY(100vh) scale(0);opacity:0;}
      10%{opacity:.5;}90%{opacity:.3;}
      100%{transform:translateY(-100px) scale(1);opacity:0;}
    }

    @media(max-width:768px){
      .hero-inner h1{font-size:2em;}
      .top-bar{flex-direction:column;align-items:flex-start;}
      tbody td:nth-child(4), tbody td:nth-child(6){display:none;}
      thead th:nth-child(4), thead th:nth-child(6){display:none;}
    }
  </style>
</head>
<body>

<div id="particles"></div>

<!-- HERO -->
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow">✦ YOLAZCAKE Sintang ✦</p>
    <h1>Data Produk</h1>
    <p class="hero-sub">Kelola seluruh menu dan produk YOLAZCAKE dengan mudah</p>
    <div class="hero-divider">
      <span></span><span class="diamond">✦ ✦ ✦</span><span></span>
    </div>
  </div>
</div>

<div class="page-wrapper">

  <div class="back-link">
    <a href="../dashboard.php">← Dashboard</a>
  </div>

  <div class="top-bar">
    <div>
      <div class="new-badge">🎂 Manajemen Produk</div>
      <h2 class="section-title" style="margin-top:10px;">Daftar Semua Produk</h2>
    </div>
    <a href="tambah_produk.php" class="btn-premium btn-add">✦ Tambah Produk</a>
  </div>

  <div class="main-card">
    <div class="gold-rule-h"><span>✦ ✦ ✦</span></div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>No</th>
            <th>Foto</th>
            <th>Nama Produk</th>
            <th>Kategori</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $no = 1;
          $found = false;
          while($data = mysqli_fetch_assoc($query)):
            $found = true;
            $stok = (int)$data['stok'];
            if($stok <= 0){
              $stok_class = 'stok-habis'; $stok_label = 'Habis';
            } elseif($stok <= 5){
              $stok_class = 'stok-low'; $stok_label = $stok.' pcs';
            } else {
              $stok_class = 'stok-ok'; $stok_label = $stok.' pcs';
            }
          ?>
          <tr>
            <td><?= $no++; ?></td>
            <td>
              <img
                src="../assets/img/produk/<?= htmlspecialchars($data['foto']); ?>"
                alt="<?= htmlspecialchars($data['nama_produk']); ?>"
                class="prod-img">
            </td>
            <td><span class="prod-name"><?= htmlspecialchars($data['nama_produk']); ?></span></td>
            <td>
              <span style="display:inline-flex;align-items:center;gap:5px;background:rgba(212,175,55,.12);border:1px solid rgba(212,175,55,.3);color:#D4AF37;padding:4px 12px;border-radius:999px;font-size:.78em;white-space:nowrap;">
                <?= htmlspecialchars($data['kategori_icon'] ?? '🍽️'); ?> <?= htmlspecialchars($data['nama_kategori']); ?>
              </span>
            </td>
            <td><span class="prod-price">Rp <?= number_format($data['harga'],0,',','.'); ?></span></td>
            <td><span class="stok-badge <?= $stok_class; ?>"><?= $stok_label; ?></span></td>
            <td>
              <div class="action-wrap">
                <a href="detail_produk.php?id=<?= $data['id_produk']; ?>" class="btn-act btn-detail">🔍 Detail</a>
                <a href="edit_produk.php?id=<?= $data['id_produk']; ?>" class="btn-act btn-edit">✏️ Edit</a>
                <a href="hapus.php?id=<?= $data['id_produk']; ?>"
                   class="btn-act btn-hapus"
                   onclick="return confirm('Yakin ingin menghapus produk ini?')">🗑 Hapus</a>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>

          <?php if(!$found): ?>
          <tr>
            <td colspan="7">
              <div class="empty-state">
                <div class="empty-icon">🎂</div>
                <p>Belum ada produk. Tambahkan produk pertama Anda!</p>
              </div>
            </td>
          </tr>
          <?php endif; ?>

        </tbody>
      </table>
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
