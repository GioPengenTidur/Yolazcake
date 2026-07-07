<?php
session_start();
require_once 'config/koneksi.php';

$no_hp     = '';
$do_search = false;

if (isset($_GET['no_hp']) && trim($_GET['no_hp']) !== '') {
    $no_hp     = trim($_GET['no_hp']);
    $do_search = true;
} elseif (!empty($_SESSION['no_hp'])) {
    $no_hp = $_SESSION['no_hp'];
    $do_search = true; // auto-cari jika datang dari sesi booking/pesanan sebelumnya
}

$bookings   = [];
$pemesanans = [];

if ($do_search && $no_hp !== '') {
    $stmtB = $conn->prepare("
        SELECT b.*, m.nomor_meja AS nomor_meja_terdaftar
        FROM booking b
        LEFT JOIN meja m ON m.id_meja = b.id_meja
        WHERE b.no_hp = ?
        ORDER BY b.created_at DESC
    ");
    $stmtB->bind_param("s", $no_hp);
    $stmtB->execute();
    $qb = $stmtB->get_result();
    if ($qb) { while ($row = mysqli_fetch_assoc($qb)) { $bookings[] = $row; } }
    $stmtB->close();

    $stmtP = $conn->prepare("
        SELECT *
        FROM pemesanan
        WHERE no_hp = ?
        ORDER BY tanggal DESC
    ");
    $stmtP->bind_param("s", $no_hp);
    $stmtP->execute();
    $qp = $stmtP->get_result();
    if ($qp) { while ($row = mysqli_fetch_assoc($qp)) { $pemesanans[] = $row; } }
    $stmtP->close();
}

function badge_booking($status) {
    switch ($status) {
        case 'Pending':       return ['status-menunggu', '⏳ Pending'];
        case 'Dikonfirmasi':  return ['status-diproses', '<i data-lucide="check-circle" class="lucide-ic"></i> Dikonfirmasi'];
        case 'Selesai':       return ['status-selesai',  '<i data-lucide="party-popper" class="lucide-ic"></i> Selesai'];
        case 'Dibatalkan':    return ['status-batal',    '<i data-lucide="x" class="lucide-ic"></i> Dibatalkan'];
        default:              return ['status-menunggu', htmlspecialchars($status)];
    }
}

function badge_pesanan($status) {
    switch ($status) {
        case 'Menunggu':     return ['status-menunggu', '⏳ Menunggu'];
        case 'Diproses':     return ['status-diproses', '<i data-lucide="user-round" class="lucide-ic"></i>‍<i data-lucide="chef-hat" class="lucide-ic"></i> Diproses'];
        case 'Siap Diambil': return ['status-siap',      '<i data-lucide="package" class="lucide-ic"></i> Siap Diambil'];
        case 'Selesai':      return ['status-selesai',   '<i data-lucide="party-popper" class="lucide-ic"></i> Selesai'];
        case 'Dibatalkan':   return ['status-batal',     '<i data-lucide="x" class="lucide-ic"></i> Dibatalkan'];
        default:             return ['status-menunggu', htmlspecialchars($status)];
    }
}

function badge_bayar($status) {
    switch ($status) {
        case 'Lunas':    return ['pay-lunas',    '<i data-lucide="check" class="lucide-ic"></i> Lunas'];
        case 'Gagal':    return ['pay-gagal',    '<i data-lucide="x" class="lucide-ic"></i> Gagal'];
        default:         return ['pay-menunggu', '⏳ Menunggu'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="assets/css/lucide-icons.css">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cek Status Booking & Pesanan – YOLAZCAKE</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}

  :root {
    --gold:#D4AF37; --gold-light:#FFE4B5; --gold-dark:#b8860b;
    --pink:#E8A0BF; --bg-deep:#1e0e3a; --bg-mid:#2d1560; --bg-dark:#1a0a2e;
    --glass:rgba(255,255,255,.06); --glass-border:rgba(255,255,255,.1);
  }

  body {
    min-height:100vh;
    font-family:'Inter',sans-serif;
    background:linear-gradient(160deg,var(--bg-deep) 0%,var(--bg-mid) 50%,var(--bg-dark) 100%);
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

  /* ===== ORBS ===== */
  .orb{position:fixed;border-radius:50%;pointer-events:none;z-index:0;filter:blur(70px);opacity:.32;}
  .orb-1{width:420px;height:420px;background:radial-gradient(circle,rgba(212,175,55,.55),rgba(212,175,55,.05));top:-80px;left:-80px;animation:orbFloat1 18s ease-in-out infinite;}
  .orb-2{width:350px;height:350px;background:radial-gradient(circle,rgba(232,160,191,.55),rgba(232,160,191,.05));bottom:-60px;right:-60px;animation:orbFloat2 22s ease-in-out infinite;}
  .orb-3{width:280px;height:280px;background:radial-gradient(circle,rgba(120,80,255,.45),rgba(120,80,255,.05));top:40%;left:60%;animation:orbFloat3 15s ease-in-out infinite;}
  @keyframes orbFloat1{0%,100%{transform:translate(0,0) scale(1);}33%{transform:translate(80px,50px) scale(1.1);}66%{transform:translate(-30px,80px) scale(.95);}}
  @keyframes orbFloat2{0%,100%{transform:translate(0,0) scale(1);}40%{transform:translate(-70px,-60px) scale(1.08);}70%{transform:translate(40px,-30px) scale(.92);}}
  @keyframes orbFloat3{0%,100%{transform:translate(0,0) scale(1);}50%{transform:translate(-100px,60px) scale(1.15);}}

  .particle{position:fixed;border-radius:50%;pointer-events:none;animation:particleFloat linear infinite;z-index:0;}
  @keyframes particleFloat{0%{transform:translateY(100vh) scale(0);opacity:0;}10%{opacity:.5;}90%{opacity:.3;}100%{transform:translateY(-100px) scale(1);opacity:0;}}

  @keyframes goldSlide{0%{background-position:0% 0;}100%{background-position:200% 0;}}
  @keyframes shimmerText{0%{background-position:100% 0;}100%{background-position:-100% 0;}}
  @keyframes fadeSlideDown{from{opacity:0;transform:translateY(-18px);}to{opacity:1;transform:translateY(0);}}
  @keyframes fadeSlideUp{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
  @keyframes heroAurora{0%{opacity:.6;transform:scale(1);}100%{opacity:1;transform:scale(1.08) translateX(10px);}}
  @keyframes floatDot{0%{transform:translateY(0) rotate(0deg);opacity:0;}20%{opacity:1;}80%{opacity:.8;}100%{transform:translateY(-280px) rotate(360deg);opacity:0;}}

  /* ===== HERO ===== */
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
  .sparkle{position:absolute;border-radius:50%;pointer-events:none;animation:floatDot linear infinite;}
  .hero-inner{position:relative;z-index:2;text-align:center;color:#fff;}
  .hero-eyebrow{font-size:.72em;font-weight:500;letter-spacing:5px;text-transform:uppercase;color:#D4AF37;margin-bottom:10px;opacity:0;animation:fadeSlideDown .8s forwards .3s;}
  .hero-inner h1{
    font-family:'Playfair Display',serif;font-size:2.6em;font-weight:700;
    background:linear-gradient(135deg,#fff 30%,#D4AF37 60%,#FFE4B5 80%,#fff);
    background-size:200% 100%;
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
    animation:shimmerText 4s ease-in-out infinite,fadeSlideDown .9s forwards .5s;
    opacity:0;
  }
  .hero-inner .hero-sub{font-size:.9em;color:rgba(255,255,255,.65);margin-top:10px;opacity:0;animation:fadeSlideDown .9s forwards .9s;}
  .hero-divider{margin-top:16px;display:flex;justify-content:center;align-items:center;gap:12px;opacity:0;animation:fadeSlideDown .9s forwards 1.1s;}
  .hero-divider span{display:block;width:60px;height:1px;background:linear-gradient(to right,transparent,#D4AF37);}
  .hero-divider span:last-child{background:linear-gradient(to left,transparent,#D4AF37);}
  .hero-divider .diamond{color:#D4AF37;font-size:.75em;letter-spacing:4px;}

  /* ===== WRAPPER ===== */
  .page-wrapper{position:relative;z-index:1;display:flex;flex-direction:column;align-items:center;padding:28px 20px 120px;max-width:900px;margin:0 auto;}

  /* ===== SEARCH CARD ===== */
  .search-card{
    width:100%;
    background:rgba(255,255,255,.06);backdrop-filter:blur(20px);
    border:1px solid rgba(255,255,255,.1);border-radius:24px;
    padding:30px 30px;position:relative;overflow:hidden;
    margin-bottom:32px;
    opacity:0;animation:fadeSlideUp .8s forwards .2s;
  }
  .search-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--gold),var(--pink),var(--gold));background-size:200% 100%;animation:goldSlide 4s linear infinite;}
  .search-title{font-family:'Playfair Display',serif;font-size:1.15em;font-weight:700;color:#fff;margin-bottom:6px;display:flex;align-items:center;gap:10px;}
  .search-desc{font-size:.85em;color:rgba(255,255,255,.55);margin-bottom:20px;line-height:1.6;}
  .search-form{display:flex;gap:12px;flex-wrap:wrap;}
  .search-form input{
    flex:1;min-width:200px;
    background:rgba(255,255,255,.05);border:1px solid rgba(212,175,55,.3);
    border-radius:14px;padding:14px 18px;color:#fff;font-size:.92em;font-family:'Inter',sans-serif;
    outline:none;transition:border-color .25s, box-shadow .25s;
  }
  .search-form input::placeholder{color:rgba(255,255,255,.35);}
  .search-form input:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(212,175,55,.15);}
  .btn-cari{
    position:relative;padding:14px 28px;border:none;border-radius:14px;
    font-family:'Inter',sans-serif;font-size:.85em;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;
    cursor:pointer;overflow:hidden;
    background:linear-gradient(135deg,var(--gold) 0%,var(--gold-dark) 50%,var(--gold) 100%);
    background-size:200% 100%;color:var(--bg-deep);
    animation:goldSlide 3s linear infinite;
    box-shadow:0 8px 28px rgba(212,175,55,.3);
    transition:transform .25s cubic-bezier(.34,1.56,.64,1), box-shadow .35s;
  }
  .btn-cari:hover{transform:translateY(-2px) scale(1.02);box-shadow:0 12px 36px rgba(212,175,55,.45);}

  /* ===== SECTION TITLE ===== */
  .result-section{width:100%;margin-bottom:30px;opacity:0;animation:fadeSlideUp .8s forwards .35s;}
  .result-section-title{
    font-family:'Playfair Display',serif;font-size:1.3em;font-weight:700;color:#fff;
    margin-bottom:16px;display:flex;align-items:center;gap:10px;
  }
  .result-section-title span.count{
    font-family:'Inter',sans-serif;font-size:.55em;font-weight:700;letter-spacing:1px;
    background:rgba(212,175,55,.18);border:1px solid rgba(212,175,55,.4);color:var(--gold);
    padding:4px 12px;border-radius:999px;
  }

  /* ===== STATUS BADGES (reused convention) ===== */
  .status-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 16px;border-radius:999px;font-size:.78em;font-weight:600;letter-spacing:.5px;white-space:nowrap;}
  .status-menunggu{background:rgba(255,180,50,.15);border:1px solid rgba(255,180,50,.35);color:#ffb432;}
  .status-diproses{background:rgba(99,149,250,.15);border:1px solid rgba(99,149,250,.35);color:#6395fa;}
  .status-siap{background:rgba(180,99,250,.15);border:1px solid rgba(180,99,250,.35);color:#c06efa;}
  .status-selesai{background:rgba(99,250,180,.15);border:1px solid rgba(99,250,180,.35);color:#6efabc;}
  .status-batal{background:rgba(255,80,80,.15);border:1px solid rgba(255,80,80,.35);color:#ff6060;}

  .pay-tag{display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:999px;font-size:.72em;font-weight:600;}
  .pay-lunas{background:rgba(99,250,180,.13);border:1px solid rgba(99,250,180,.3);color:#6efabc;}
  .pay-menunggu{background:rgba(255,180,50,.13);border:1px solid rgba(255,180,50,.3);color:#ffb432;}
  .pay-gagal{background:rgba(255,80,80,.13);border:1px solid rgba(255,80,80,.3);color:#ff6060;}

  /* ===== ITEM CARD ===== */
  .item-card{
    width:100%;background:rgba(255,255,255,.045);backdrop-filter:blur(16px);
    border:1px solid rgba(255,255,255,.09);border-radius:18px;
    padding:18px 22px;margin-bottom:14px;position:relative;overflow:hidden;
    transition:border-color .25s, transform .25s;
  }
  .item-card:hover{border-color:rgba(212,175,55,.4);transform:translateY(-2px);}
  .item-card::before{content:'';position:absolute;top:0;left:0;bottom:0;width:3px;background:linear-gradient(180deg,var(--gold),var(--pink));}
  .item-top{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:10px;}
  .item-id{font-family:'Playfair Display',serif;font-size:1.05em;font-weight:700;color:var(--gold);}
  .item-meta{display:flex;flex-wrap:wrap;gap:14px;font-size:.82em;color:rgba(255,255,255,.65);margin-bottom:10px;}
  .item-meta b{color:rgba(255,255,255,.9);font-weight:600;}
  .item-bottom{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;}
  .item-total{font-size:.95em;color:#6efabc;font-weight:700;}
  .item-link{
    font-size:.78em;font-weight:600;letter-spacing:.5px;color:var(--gold);text-decoration:none;
    border:1px solid rgba(212,175,55,.4);padding:7px 16px;border-radius:999px;
    transition:all .25s;background:rgba(212,175,55,.06);
  }
  .item-link:hover{background:rgba(212,175,55,.18);box-shadow:0 0 14px rgba(212,175,55,.25);}

  /* ===== EMPTY STATE ===== */
  .empty-state{
    width:100%;text-align:center;padding:60px 24px;
    background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:22px;
    opacity:0;animation:fadeSlideUp .8s forwards .35s;
  }
  .empty-state .e-icon{font-size:3em;margin-bottom:14px;opacity:.5;}
  .empty-state h3{font-family:'Playfair Display',serif;color:rgba(255,255,255,.7);font-size:1.15em;margin-bottom:8px;}
  .empty-state p{color:rgba(255,255,255,.4);font-size:.85em;}

  .hint-state{
    width:100%;text-align:center;padding:50px 24px;
    background:rgba(212,175,55,.05);border:1px solid rgba(212,175,55,.18);border-radius:22px;
  }
  .hint-state .e-icon{font-size:2.6em;margin-bottom:12px;}
  .hint-state p{color:rgba(255,255,255,.55);font-size:.88em;line-height:1.7;}

  /* ===== ACTIONS ===== */
  .actions{width:100%;display:flex;gap:14px;flex-wrap:wrap;justify-content:center;margin-top:10px;}
  .btn-premium{
    position:relative;padding:13px 26px;border:none;border-radius:14px;
    font-family:'Inter',sans-serif;font-size:.82em;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;
    cursor:pointer;overflow:hidden;transition:transform .25s cubic-bezier(.34,1.56,.64,1), box-shadow .35s;
    text-decoration:none;display:inline-flex;align-items:center;gap:8px;
  }
  .btn-premium:hover{transform:translateY(-3px) scale(1.02);}
  .btn-outline{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.15);color:rgba(255,255,255,.75);}
  .btn-outline:hover{background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.3);}
  .btn-green{background:linear-gradient(135deg,rgba(99,250,180,.22),rgba(99,250,180,.1));border:1px solid rgba(99,250,180,.45);color:#6efabc;}
  .btn-green:hover{box-shadow:0 8px 28px rgba(99,250,180,.28);}

  /* ===== FOOTER ===== */
  .site-footer{position:relative;z-index:1;text-align:center;padding:36px 20px;font-size:.8em;color:rgba(255,255,255,.5);border-top:1px solid rgba(255,255,255,.06);line-height:1.8;}

  @media(max-width:600px){
    .hero-inner h1{font-size:1.9em;}
    .search-form{flex-direction:column;}
    .item-meta{gap:8px 14px;}
    .actions{flex-direction:column;}
    .btn-premium{justify-content:center;}
  }
</style>
</head>
<body>

<div id="particles"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<!-- HERO -->
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow"><i data-lucide="sparkle" class="lucide-ic"></i> YOLAZCAKE Sintang <i data-lucide="sparkle" class="lucide-ic"></i></p>
    <h1>Cek Status Booking & Pesanan</h1>
    <p class="hero-sub">Pantau status reservasi meja dan pesanan makanan Anda secara langsung</p>
    <div class="hero-divider"><span></span><span class="diamond"><i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i></span><span></span></div>
  </div>
</div>

<!-- WRAPPER -->
<div class="page-wrapper">

  <!-- SEARCH -->
  <div class="search-card">
    <div class="search-title"><i data-lucide="search" class="lucide-ic"></i> Cari Berdasarkan Nomor HP</div>
    <p class="search-desc">Masukkan nomor HP yang Anda gunakan saat booking meja atau memesan makanan & minuman untuk melihat status terbaru.</p>
    <form class="search-form" method="GET" action="status.php">
      <input type="text" name="no_hp" placeholder="Contoh: 0815-7815-7888" value="<?= htmlspecialchars($no_hp) ?>" required>
      <button type="submit" class="btn-cari"><i data-lucide="search" class="lucide-ic"></i> Cek Status</button>
    </form>
  </div>

  <?php if ($do_search): ?>

    <?php if (empty($bookings) && empty($pemesanans)): ?>
      <div class="empty-state">
        <div class="e-icon"><i data-lucide="notebook-text" class="lucide-ic"></i></div>
        <h3>Belum Ada Data Ditemukan</h3>
        <p>Tidak ada booking maupun pesanan dengan nomor HP <strong>"<?= htmlspecialchars($no_hp) ?>"</strong>. Pastikan nomor yang dimasukkan sama persis dengan saat booking/pemesanan.</p>
      </div>
    <?php else: ?>

      <?php if (!empty($bookings)): ?>
      <div class="result-section">
        <div class="result-section-title"><i data-lucide="calendar" class="lucide-ic"></i> Booking Meja Anda <span class="count"><?= count($bookings) ?></span></div>
        <?php foreach ($bookings as $b): list($cls, $label) = badge_booking($b['status']); ?>
        <div class="item-card">
          <div class="item-top">
            <div class="item-id"><i data-lucide="ticket" class="lucide-ic"></i> #<?= str_pad($b['id_booking'], 4, '0', STR_PAD_LEFT) ?></div>
            <span class="status-badge <?= $cls ?>"><?= $label ?></span>
          </div>
          <div class="item-meta">
            <span><i data-lucide="user" class="lucide-ic"></i> <b><?= htmlspecialchars($b['nama_pemesan']) ?></b></span>
            <span><i data-lucide="calendar" class="lucide-ic"></i> <b><?= date('d M Y', strtotime($b['tanggal_booking'])) ?></b></span>
            <span><i data-lucide="clock" class="lucide-ic"></i> <b><?= date('H:i', strtotime($b['jam_booking'])) ?> WIB</b></span>
            <span><i data-lucide="users" class="lucide-ic"></i> <b><?= htmlspecialchars($b['jumlah_orang']) ?> orang</b></span>
            <?php if (!empty($b['nomor_meja_terdaftar'])): ?>
            <span><i data-lucide="armchair" class="lucide-ic"></i> <b>Meja <?= htmlspecialchars($b['nomor_meja_terdaftar']) ?></b></span>
            <?php endif; ?>
          </div>
          <?php if (!empty($b['catatan'])): ?>
          <div class="item-bottom"><span style="font-size:.82em;color:rgba(255,255,255,.5);"><i data-lucide="file-text" class="lucide-ic"></i> <?= htmlspecialchars($b['catatan']) ?></span></div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if (!empty($pemesanans)): ?>
      <div class="result-section">
        <div class="result-section-title"><i data-lucide="shopping-bag" class="lucide-ic"></i> Pesanan Makanan & Minuman Anda <span class="count"><?= count($pemesanans) ?></span></div>
        <?php foreach ($pemesanans as $p): list($cls, $label) = badge_pesanan($p['status_pesanan']); list($pcls, $plabel) = badge_bayar($p['status_pembayaran']); ?>
        <div class="item-card">
          <div class="item-top">
            <div class="item-id"><i data-lucide="package" class="lucide-ic"></i> <?= htmlspecialchars($p['kode_pesanan']) ?></div>
            <span class="status-badge <?= $cls ?>"><?= $label ?></span>
          </div>
          <div class="item-meta">
            <span><i data-lucide="user" class="lucide-ic"></i> <b><?= htmlspecialchars($p['nama_pemesan']) ?></b></span>
            <span><i data-lucide="calendar" class="lucide-ic"></i> <b><?= date('d M Y, H:i', strtotime($p['tanggal'])) ?> WIB</b></span>
            <span class="pay-tag <?= $pcls ?>"><?= $plabel ?></span>
            <?php if (!empty($p['nomor_meja'])): ?>
            <span><i data-lucide="armchair" class="lucide-ic"></i> <b>Meja <?= htmlspecialchars($p['nomor_meja']) ?></b></span>
            <?php endif; ?>
          </div>
          <div class="item-bottom">
            <span class="item-total"><i data-lucide="wallet" class="lucide-ic"></i> Rp <?= number_format($p['total_harga'], 0, ',', '.') ?></span>
            <a href="pemesanan/detail_pemesanan.php?id=<?= $p['id_pemesanan'] ?>" class="item-link">Lihat Detail <i data-lucide="arrow-right" class="lucide-ic"></i></a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

    <?php endif; ?>

  <?php else: ?>
    <div class="hint-state">
      <div class="e-icon"><i data-lucide="sparkles" class="lucide-ic"></i></div>
      <p>Masukkan nomor HP Anda di atas untuk melihat status booking meja<br>dan pesanan makanan & minuman secara real-time.</p>
    </div>
  <?php endif; ?>

  <!-- ACTIONS -->
  <div class="actions" style="margin-top:32px;">
    <a href="index.php" class="btn-premium btn-outline"><i data-lucide="home" class="lucide-ic"></i> Kembali ke Beranda</a>
    <a href="booking/booking.php" class="btn-premium btn-outline"><i data-lucide="calendar" class="lucide-ic"></i> Booking Meja</a>
    <a href="pemesanan/menuu.php" class="btn-premium btn-green"><i data-lucide="cake-slice" class="lucide-ic"></i> Pesan Makanan</a>
  </div>

</div>

<!-- FOOTER -->
<div class="site-footer">
  © <?= date('Y') ?> YOLAZCAKE Sintang • Cafe • Bakery • Boutique<br>
  Jl. Lintas Melawi, Ladang, Sintang, Kalimantan Barat<br>
  WA: 0815-7815-7888
</div>

<script>
(function(){
  const hero = document.getElementById('pageHero');
  const colors = ['#D4AF37','#FFE4B5','#E8A0BF','#fff','#f9ce34','#b8860b'];
  for(let i=0;i<22;i++){
    const d = document.createElement('div'); d.className = 'sparkle';
    const s = Math.random()*5+2;
    d.style.cssText = `width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;bottom:${Math.random()*30}%;animation-duration:${4+Math.random()*7}s;animation-delay:${Math.random()*5}s;opacity:0;`;
    hero.appendChild(d);
  }
})();
(function(){
  const c = document.getElementById('particles');
  const colors = ['rgba(212,175,55,.4)','rgba(232,160,191,.3)','rgba(255,255,255,.15)'];
  for(let i=0;i<16;i++){
    const p = document.createElement('div'); p.className = 'particle';
    const s = Math.random()*5+2;
    p.style.cssText = `width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;animation-duration:${10+Math.random()*12}s;animation-delay:${Math.random()*10}s;`;
    c.appendChild(p);
  }
})();
</script>


<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide){lucide.createIcons();}</script>
</body>
</html>
