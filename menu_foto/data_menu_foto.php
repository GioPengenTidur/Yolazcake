<?php
session_start();
require_once __DIR__.'/../config/staff_guard.php';
require_staff_login();
include '../config/koneksi.php';

$filter = ($_GET['section'] ?? '') === 'unggulan' ? 'unggulan' : (($_GET['section'] ?? '') === 'highlight' ? 'highlight' : '');
if ($filter) {
    $stmt = $conn->prepare("SELECT * FROM menu_highlight_foto WHERE section=? ORDER BY section, card_index, slide_index");
    $stmt->bind_param("s", $filter);
    $stmt->execute();
    $query = $stmt->get_result();
} else {
    $query = $conn->query("SELECT * FROM menu_highlight_foto ORDER BY section, card_index, slide_index");
}

$grouped = [];
$total   = 0;
while($d = mysqli_fetch_assoc($query)){
    $grouped[$d['section']][$d['card_index']]['nama'] = $d['nama_kartu'];
    $grouped[$d['section']][$d['card_index']]['slides'][$d['slide_index']] = $d;
    $total++;
}

$stats = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total,
            SUM(section='highlight') as highlight,
            SUM(section='unggulan') as unggulan
     FROM menu_highlight_foto"));

$labelSection = ['highlight'=>'<i data-lucide="sparkle" class="lucide-ic"></i> Highlights Menu','unggulan'=>'<i data-lucide="sparkle" class="lucide-ic"></i> Produk Unggulan'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="../assets/css/lucide-icons.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Foto Menu – YOLAZCAKE</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    :root{
      --gold:#D4AF37;--gold-l:#FFE88A;--gold-d:#b8860b;
      --rose:#ee2a7b;--purple:#8A2BE2;
      --glass:rgba(255,255,255,0.05);--muted:rgba(255,255,255,0.5);
    }
    html{scroll-behavior:smooth;}
    body{min-height:100vh;font-family:'Inter',sans-serif;
      background:linear-gradient(160deg,#1e0e3a 0%,#2d1560 50%,#1a0a2e 100%);
      color:#fff;overflow-x:hidden;}
    body::before{content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
      background:radial-gradient(ellipse at 20% 30%,rgba(212,175,55,.10) 0%,transparent 55%),
                 radial-gradient(ellipse at 80% 70%,rgba(232,160,191,.10) 0%,transparent 55%);}

    .page-hero{position:relative;height:240px;display:flex;flex-direction:column;
      align-items:center;justify-content:center;overflow:hidden;
      background:linear-gradient(135deg,#2b1a11 0%,#4a2c1a 40%,#6d3e26 70%,#3a1f0e 100%);z-index:1;}
    .page-hero::before{content:'';position:absolute;inset:0;
      background:radial-gradient(ellipse at 30% 50%,rgba(212,175,55,.18) 0%,transparent 60%),
                 radial-gradient(ellipse at 75% 40%,rgba(232,160,191,.15) 0%,transparent 55%);
      animation:heroAurora 8s ease-in-out infinite alternate;}
    @keyframes heroAurora{0%{opacity:.6;transform:scale(1);}100%{opacity:1;transform:scale(1.08) translateX(10px);}}
    .sparkle{position:absolute;border-radius:50%;pointer-events:none;animation:floatDot linear infinite;}
    @keyframes floatDot{0%{transform:translateY(0) rotate(0deg);opacity:0;}20%{opacity:1;}80%{opacity:.8;}100%{transform:translateY(-280px) rotate(360deg);opacity:0;}}
    .hero-inner{position:relative;z-index:2;text-align:center;color:#fff;}
    .hero-eyebrow{font-size:.72em;font-weight:500;letter-spacing:5px;text-transform:uppercase;color:var(--gold);margin-bottom:10px;opacity:0;animation:fadeSlideDown .8s forwards .3s;}
    .hero-inner h1{font-family:'Playfair Display',serif;font-size:3em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,var(--gold) 60%,#FFE4B5 80%,#fff);
      background-size:200% 100%;-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
      animation:shimmerText 4s ease-in-out infinite,fadeSlideDown .9s forwards .5s;opacity:0;}
    .hero-sub{font-size:.9em;color:rgba(255,255,255,.65);margin-top:10px;opacity:0;animation:fadeSlideDown .9s forwards .9s;}
    .hero-divider{margin-top:16px;display:flex;justify-content:center;align-items:center;gap:12px;opacity:0;animation:fadeSlideDown .9s forwards 1.1s;}
    .hero-divider span{display:block;width:60px;height:1px;background:linear-gradient(to right,transparent,var(--gold));}
    .hero-divider span:last-child{background:linear-gradient(to left,transparent,var(--gold));}
    .hero-divider .diamond{color:var(--gold);font-size:.75em;letter-spacing:4px;}
    @keyframes shimmerText{0%{background-position:100% 0;}100%{background-position:-100% 0;}}
    @keyframes fadeSlideDown{from{opacity:0;transform:translateY(-18px);}to{opacity:1;transform:translateY(0);}}

    .page-wrapper{position:relative;z-index:1;padding:36px 28px 80px;max-width:1280px;margin:0 auto;}
    .btn-back{display:inline-flex;align-items:center;gap:8px;padding:10px 22px;
      background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.3);color:var(--gold);
      font-size:.82em;font-weight:600;letter-spacing:1px;border-radius:999px;text-decoration:none;
      transition:transform .25s,box-shadow .3s,background .3s;margin-bottom:24px;}
    .btn-back:hover{transform:translateX(-3px);background:rgba(212,175,55,.2);box-shadow:0 6px 20px rgba(212,175,55,.25);}

    .top-bar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;
      margin-bottom:28px;opacity:0;animation:cardReveal .7s forwards .7s;}
    .section-eyebrow{font-size:.72em;font-weight:600;letter-spacing:4px;text-transform:uppercase;color:var(--gold);}

    .stats-row{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:28px;opacity:0;animation:cardReveal .7s forwards .85s;}
    .stat-card{background:rgba(255,255,255,.06);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.1);
      border-radius:18px;padding:18px 20px;display:flex;align-items:center;gap:12px;
      transition:border-color .35s,box-shadow .35s,transform .3s;}
    .stat-card:hover{border-color:rgba(212,175,55,.35);box-shadow:0 0 24px rgba(212,175,55,.2);transform:translateY(-3px);}
    .stat-icon{font-size:1.4em;}
    .stat-val{font-family:'Playfair Display',serif;font-size:1.5em;font-weight:700;color:var(--gold);line-height:1;}
    .stat-lbl{font-size:.72em;color:var(--muted);margin-top:2px;}

    .filter-row{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:30px;opacity:0;animation:cardReveal .7s forwards .9s;}
    .filter-chip{padding:9px 18px;border-radius:999px;font-size:.8em;font-weight:600;text-decoration:none;
      border:1px solid rgba(255,255,255,.15);color:rgba(255,255,255,.7);background:rgba(255,255,255,.04);
      transition:all .25s;}
    .filter-chip:hover{border-color:var(--gold);color:var(--gold);}
    .filter-chip.active{background:rgba(212,175,55,.18);border-color:var(--gold);color:var(--gold);}

    .section-block{margin-bottom:44px;opacity:0;animation:cardReveal .8s forwards 1.0s;}
    .section-title{font-family:'Playfair Display',serif;font-size:1.3em;font-weight:700;color:#fff;
      margin-bottom:18px;display:flex;align-items:center;gap:10px;}
    .section-title .dot{width:8px;height:8px;border-radius:50%;background:var(--gold);box-shadow:0 0 10px var(--gold);}

    .kartu-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:20px;}
    .kartu-card{background:var(--glass);backdrop-filter:blur(20px);
      border:1px solid rgba(255,255,255,.1);border-radius:20px;overflow:hidden;
      position:relative;transition:transform .3s,border-color .3s,box-shadow .3s;}
    .kartu-card:hover{transform:translateY(-6px);border-color:rgba(212,175,55,.3);box-shadow:0 12px 40px rgba(212,175,55,.15);}
    .kartu-head{padding:14px 18px 0;display:flex;align-items:center;justify-content:space-between;}
    .kartu-title{font-family:'Playfair Display',serif;font-weight:700;color:#fff;font-size:1em;}
    .kartu-badge{padding:4px 12px;border-radius:999px;font-size:.65em;font-weight:700;letter-spacing:.6px;
      text-transform:uppercase;background:rgba(212,175,55,.14);border:1px solid rgba(212,175,55,.35);color:var(--gold);}
    .slide-row{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;padding:14px 18px 18px;}
    .slide-item{position:relative;border-radius:12px;overflow:hidden;background:rgba(255,255,255,.05);
      border:1px solid rgba(255,255,255,.08);aspect-ratio:1/1;}
    .slide-item img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .4s;}
    .slide-item:hover img{transform:scale(1.1);}
    .slide-num{position:absolute;top:6px;left:6px;background:rgba(0,0,0,.55);color:var(--gold);
      font-size:.65em;font-weight:700;padding:2px 7px;border-radius:6px;letter-spacing:.5px;}
    .slide-edit{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;
      background:rgba(26,10,46,0);opacity:0;transition:all .3s;text-decoration:none;}
    .slide-item:hover .slide-edit{opacity:1;background:rgba(26,10,46,.55);}
    .slide-edit-btn{padding:6px 14px;border-radius:8px;font-size:.7em;font-weight:700;letter-spacing:.5px;
      background:rgba(212,175,55,.9);color:#1e0e3a;transform:translateY(6px);transition:transform .3s;}
    .slide-item:hover .slide-edit-btn{transform:translateY(0);}

    .empty-state{text-align:center;padding:80px 20px;color:var(--muted);}
    .empty-state .es-icon{font-size:3.5em;margin-bottom:16px;opacity:.5;}

    @keyframes cardReveal{to{opacity:1;transform:translateY(0);}}

    @media(max-width:768px){
      .stats-row{grid-template-columns:repeat(3,1fr);gap:10px;}
      .stat-card{padding:12px 10px;}
      .hero-inner h1{font-size:2em;}
      .page-wrapper{padding:24px 16px 60px;}
      .kartu-grid{grid-template-columns:1fr;}
    }
  </style>
</head>
<body>
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow"><i data-lucide="sparkle" class="lucide-ic"></i> YOLAZCAKE Sintang <i data-lucide="sparkle" class="lucide-ic"></i></p>
    <h1>Foto Menu &amp; Highlight</h1>
    <p class="hero-sub">Kelola foto pada bagian Highlights Menu &amp; Produk Unggulan di halaman Menu</p>
    <div class="hero-divider"><span></span><span class="diamond"><i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i></span><span></span></div>
  </div>
</div>

<div class="page-wrapper">

  <a href="../dashboard.php" class="btn-back"><i data-lucide="arrow-left" class="lucide-ic"></i> Dashboard</a>

  <div class="top-bar">
    <span class="section-eyebrow"><i data-lucide="sparkle" class="lucide-ic"></i> Daftar Foto Menu</span>
  </div>

  <div class="stats-row">
    <div class="stat-card"><span class="stat-icon"><i data-lucide="image" class="lucide-ic"></i></span>
      <div><div class="stat-val"><?= $stats['total'] ?: 0 ?></div><div class="stat-lbl">Total Slot Foto</div></div></div>
    <div class="stat-card"><span class="stat-icon"><i data-lucide="sparkle" class="lucide-ic"></i></span>
      <div><div class="stat-val"><?= $stats['highlight'] ?: 0 ?></div><div class="stat-lbl">Highlights Menu</div></div></div>
    <div class="stat-card"><span class="stat-icon"><i data-lucide="crown" class="lucide-ic"></i></span>
      <div><div class="stat-val"><?= $stats['unggulan'] ?: 0 ?></div><div class="stat-lbl">Produk Unggulan</div></div></div>
  </div>

  <div class="filter-row">
    <a href="data_menu_foto.php" class="filter-chip <?= !$filter?'active':'' ?>">Semua</a>
    <a href="?section=highlight" class="filter-chip <?= $filter==='highlight'?'active':'' ?>"><i data-lucide="sparkle" class="lucide-ic"></i> Highlights Menu</a>
    <a href="?section=unggulan" class="filter-chip <?= $filter==='unggulan'?'active':'' ?>"><i data-lucide="crown" class="lucide-ic"></i> Produk Unggulan</a>
  </div>

  <?php if($total > 0): ?>
    <?php foreach($grouped as $sectionKey => $cards): ?>
    <div class="section-block">
      <div class="section-title"><span class="dot"></span> <?= $labelSection[$sectionKey] ?? $sectionKey ?></div>
      <div class="kartu-grid">
        <?php ksort($cards); foreach($cards as $cardIndex => $card): ?>
        <div class="kartu-card">
          <div class="kartu-head">
            <span class="kartu-title"><?= htmlspecialchars($card['nama']) ?></span>
            <span class="kartu-badge">Kartu <?= $cardIndex+1 ?></span>
          </div>
          <div class="slide-row">
            <?php ksort($card['slides']); foreach($card['slides'] as $slideIndex => $s): ?>
            <div class="slide-item">
              <span class="slide-num"><?= $slideIndex+1 ?></span>
              <img src="../<?= htmlspecialchars($s['foto_path']) ?>" alt="<?= htmlspecialchars($s['nama_kartu']) ?>" loading="lazy">
              <a href="edit_menu_foto.php?id=<?= $s['id_foto'] ?>" class="slide-edit">
                <span class="slide-edit-btn"><i data-lucide="pencil" class="lucide-ic"></i> Ganti Foto</span>
              </a>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="empty-state"><div class="es-icon"><i data-lucide="image" class="lucide-ic"></i></div><p>Belum ada data foto menu. Jalankan migrasi database terlebih dahulu.</p></div>
  <?php endif; ?>

</div>

<script>
(function(){
  const hero = document.getElementById('pageHero');
  const colors = ['#D4AF37','#FFE4B5','#8A2BE2','#fff'];
  for(let i=0;i<20;i++){
    const d=document.createElement('div'); d.className='sparkle';
    const s=Math.random()*5+2;
    d.style.cssText=`width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;bottom:${Math.random()*30}%;animation-duration:${4+Math.random()*7}s;animation-delay:${Math.random()*5}s;opacity:0;`;
    hero.appendChild(d);
  }
})();
</script>

<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide){lucide.createIcons();}</script>
</body>
</html>
