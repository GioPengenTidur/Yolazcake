<?php
session_start();
require_once __DIR__.'/../config/staff_guard.php';
require_staff_login();
include '../config/koneksi.php';

// Handle hapus
if(isset($_GET['hapus'])){
    $id = (int)$_GET['hapus'];

    $stmt = $conn->prepare("SELECT foto,judul FROM galeri WHERE id_galeri=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $d = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if($d && $d['foto'] && file_exists("../assets/img/galeri/".$d['foto'])){
        unlink("../assets/img/galeri/".$d['foto']);
    }

    $stmt = $conn->prepare("DELETE FROM galeri WHERE id_galeri=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    include 'success_overlay.php';
    tampilkan_sukses([
        'proses_judul' => 'Menghapus Foto…',
        'proses_sub'   => 'Sedang memproses penghapusan foto galeri',
        'sukses_judul' => 'Foto Berhasil Dihapus!',
        'sukses_sub'   => '"'.htmlspecialchars($d['judul'] ?? 'Foto').'" telah dihapus dari galeri',
        'redirect'     => 'data_galeri.php',
        'tombol_label' => 'Lanjutkan ke Data Galeri',
    ]);
    exit;
}

$filter = $_GET['filter'] ?? '';
if($filter){
    $stmt = $conn->prepare("SELECT * FROM galeri WHERE kategori=? ORDER BY created_at DESC");
    $stmt->bind_param("s", $filter);
    $stmt->execute();
    $query = $stmt->get_result();
} else {
    $query = $conn->query("SELECT * FROM galeri ORDER BY created_at DESC");
}
$total  = mysqli_num_rows($query);
$stats  = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total,
            SUM(kategori='interior') as interior,
            SUM(kategori='kue') as kue,
            SUM(kategori='coffee') as coffee,
            SUM(kategori='boutique') as boutique
     FROM galeri"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Galeri – YOLAZCAKE</title>
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

    .page-wrapper{position:relative;z-index:1;padding:36px 28px 80px;max-width:1200px;margin:0 auto;}
    .btn-back{display:inline-flex;align-items:center;gap:8px;padding:10px 22px;
      background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.3);color:var(--gold);
      font-size:.82em;font-weight:600;letter-spacing:1px;border-radius:999px;text-decoration:none;
      transition:transform .25s,box-shadow .3s,background .3s;margin-bottom:24px;}
    .btn-back:hover{transform:translateX(-3px);background:rgba(212,175,55,.2);box-shadow:0 6px 20px rgba(212,175,55,.25);}

    .top-bar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;
      margin-bottom:28px;opacity:0;animation:cardReveal .7s forwards .7s;}
    .section-eyebrow{font-size:.72em;font-weight:600;letter-spacing:4px;text-transform:uppercase;color:var(--gold);}
    .btn-tambah{display:inline-flex;align-items:center;gap:8px;padding:12px 26px;
      background:linear-gradient(135deg,var(--gold) 0%,var(--gold-d) 50%,var(--gold) 100%);background-size:200% 100%;
      animation:goldSlide 3s linear infinite;color:#1e0e3a;font-size:.82em;font-weight:700;
      letter-spacing:1.5px;text-transform:uppercase;border:none;border-radius:999px;cursor:pointer;text-decoration:none;
      box-shadow:0 6px 22px rgba(212,175,55,.35);transition:transform .25s,box-shadow .35s;position:relative;overflow:hidden;}
    .btn-tambah:hover{transform:translateY(-3px) scale(1.04);box-shadow:0 12px 36px rgba(212,175,55,.5);}
    @keyframes goldSlide{0%{background-position:0% 0;}100%{background-position:200% 0;}}

    .stats-row{display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin-bottom:28px;opacity:0;animation:cardReveal .7s forwards .85s;}
    .stat-card{background:rgba(255,255,255,.06);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.1);
      border-radius:18px;padding:18px 20px;display:flex;align-items:center;gap:12px;
      transition:border-color .35s,box-shadow .35s,transform .3s;}
    .stat-card:hover{border-color:rgba(212,175,55,.35);box-shadow:0 0 24px rgba(212,175,55,.2);transform:translateY(-3px);}
    .stat-icon{font-size:1.4em;}
    .stat-val{font-family:'Playfair Display',serif;font-size:1.5em;font-weight:700;color:var(--gold);line-height:1;}
    .stat-lbl{font-size:.72em;color:var(--muted);margin-top:2px;}

    .filter-row{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:24px;opacity:0;animation:cardReveal .7s forwards .9s;}
    .filter-chip{padding:9px 18px;border-radius:999px;font-size:.8em;font-weight:600;text-decoration:none;
      border:1px solid rgba(255,255,255,.15);color:rgba(255,255,255,.7);background:rgba(255,255,255,.04);
      transition:all .25s;}
    .filter-chip:hover{border-color:var(--gold);color:var(--gold);}
    .filter-chip.active{background:rgba(212,175,55,.18);border-color:var(--gold);color:var(--gold);}

    .alert{padding:14px 20px;border-radius:12px;margin-bottom:20px;font-size:.88em;
      display:flex;align-items:center;gap:10px;opacity:0;animation:cardReveal .5s forwards;}
    .alert-success{background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.3);color:#6ee7b7;}

    .galeri-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:18px;
      opacity:0;animation:cardReveal .8s forwards 1.0s;}
    .galeri-card{background:var(--glass);backdrop-filter:blur(20px);
      border:1px solid rgba(255,255,255,.1);border-radius:20px;overflow:hidden;
      position:relative;transition:transform .3s,border-color .3s,box-shadow .3s;}
    .galeri-card:hover{transform:translateY(-6px);border-color:rgba(212,175,55,.3);box-shadow:0 12px 40px rgba(212,175,55,.15);}
    .galeri-card .foto-wrap{width:100%;height:170px;overflow:hidden;background:rgba(255,255,255,.05);}
    .galeri-card img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .4s;}
    .galeri-card:hover img{transform:scale(1.08);}
    .galeri-body{padding:16px 18px;}
    .galeri-title{font-family:'Playfair Display',serif;font-weight:700;color:#fff;font-size:1.02em;margin-bottom:4px;}
    .galeri-desc{font-size:.8em;color:var(--muted);min-height:32px;}
    .kategori-badge{display:inline-block;margin-top:8px;padding:4px 12px;border-radius:999px;
      font-size:.68em;font-weight:700;letter-spacing:.6px;text-transform:uppercase;
      background:rgba(212,175,55,.14);border:1px solid rgba(212,175,55,.35);color:var(--gold);}
    .galeri-actions{display:flex;gap:8px;margin-top:14px;}
    .btn-act{display:inline-flex;align-items:center;gap:5px;padding:7px 13px;border-radius:8px;
      font-size:.72em;font-weight:600;text-decoration:none;border:1px solid transparent;
      transition:transform .2s,box-shadow .25s,background .25s;}
    .btn-act:hover{transform:translateY(-2px);}
    .btn-edit{background:rgba(212,175,55,.18);border-color:rgba(212,175,55,.4);color:var(--gold);}
    .btn-edit:hover{background:rgba(212,175,55,.32);box-shadow:0 4px 16px rgba(212,175,55,.3);}
    .btn-hapus{background:rgba(239,68,68,.14);border-color:rgba(239,68,68,.35);color:#fca5a5;}
    .btn-hapus:hover{background:rgba(239,68,68,.28);box-shadow:0 4px 16px rgba(239,68,68,.25);}

    .empty-state{text-align:center;padding:80px 20px;color:var(--muted);grid-column:1/-1;}
    .empty-state .es-icon{font-size:3.5em;margin-bottom:16px;opacity:.5;}

    @keyframes cardReveal{to{opacity:1;transform:translateY(0);}}

    @media(max-width:768px){
      .stats-row{grid-template-columns:repeat(2,1fr);}
      .hero-inner h1{font-size:2em;}
      .page-wrapper{padding:24px 16px 60px;}
    }
  </style>
</head>
<body>
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow">✦ YOLAZCAKE Sintang ✦</p>
    <h1>Manajemen Galeri</h1>
    <p class="hero-sub">Kelola foto yang tampil di halaman Gallery</p>
    <div class="hero-divider"><span></span><span class="diamond">✦ ✦ ✦</span><span></span></div>
  </div>
</div>

<div class="page-wrapper">

  <a href="../dashboard.php" class="btn-back">← Dashboard</a>

  <div class="top-bar">
    <span class="section-eyebrow">✦ Daftar Foto Galeri</span>
    <a href="tambah_galeri.php" class="btn-tambah">+ Tambah Foto</a>
  </div>

  <div class="stats-row">
    <div class="stat-card"><span class="stat-icon">🖼️</span>
      <div><div class="stat-val"><?= $stats['total'] ?: 0 ?></div><div class="stat-lbl">Total Foto</div></div></div>
    <div class="stat-card"><span class="stat-icon">🏠</span>
      <div><div class="stat-val"><?= $stats['interior'] ?: 0 ?></div><div class="stat-lbl">Interior</div></div></div>
    <div class="stat-card"><span class="stat-icon">🍰</span>
      <div><div class="stat-val"><?= $stats['kue'] ?: 0 ?></div><div class="stat-lbl">Kue & Pastry</div></div></div>
    <div class="stat-card"><span class="stat-icon">☕</span>
      <div><div class="stat-val"><?= $stats['coffee'] ?: 0 ?></div><div class="stat-lbl">Coffee</div></div></div>
    <div class="stat-card"><span class="stat-icon">👗</span>
      <div><div class="stat-val"><?= $stats['boutique'] ?: 0 ?></div><div class="stat-lbl">Boutique</div></div></div>
  </div>

  <div class="filter-row">
    <a href="data_galeri.php" class="filter-chip <?= !$filter?'active':'' ?>">Semua</a>
    <a href="?filter=interior" class="filter-chip <?= $filter==='interior'?'active':'' ?>">🏠 Interior</a>
    <a href="?filter=kue" class="filter-chip <?= $filter==='kue'?'active':'' ?>">🍰 Kue & Pastry</a>
    <a href="?filter=coffee" class="filter-chip <?= $filter==='coffee'?'active':'' ?>">☕ Coffee</a>
    <a href="?filter=boutique" class="filter-chip <?= $filter==='boutique'?'active':'' ?>">👗 Boutique</a>
  </div>

  <div class="galeri-grid">
    <?php if($total > 0): while($d = mysqli_fetch_assoc($query)): ?>
    <div class="galeri-card">
      <div class="foto-wrap">
        <img src="../assets/img/galeri/<?= htmlspecialchars($d['foto']) ?>" alt="<?= htmlspecialchars($d['judul']) ?>" loading="lazy">
      </div>
      <div class="galeri-body">
        <div class="galeri-title"><?= htmlspecialchars($d['judul']) ?></div>
        <div class="galeri-desc"><?= $d['deskripsi'] ? htmlspecialchars($d['deskripsi']) : '-' ?></div>
        <span class="kategori-badge"><?= htmlspecialchars($d['kategori']) ?></span>
        <div class="galeri-actions">
          <a href="edit_galeri.php?id=<?= $d['id_galeri'] ?>" class="btn-act btn-edit">✏️ Edit</a>
          <a href="?hapus=<?= $d['id_galeri'] ?>" class="btn-act btn-hapus"
             onclick="return confirm('Yakin hapus foto «<?= htmlspecialchars($d['judul']) ?>»?')">🗑️ Hapus</a>
        </div>
      </div>
    </div>
    <?php endwhile; else: ?>
    <div class="empty-state"><div class="es-icon">🖼️</div><p>Belum ada foto di kategori ini</p></div>
    <?php endif; ?>
  </div>

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
</body>
</html>
