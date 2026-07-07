<?php
session_start();
require_once __DIR__.'/../config/staff_guard.php';
require_staff_login();
include '../config/koneksi.php';

// Handle hapus
if(isset($_GET['hapus'])){
    $id = (int)$_GET['hapus'];

    $stmt = $conn->prepare("SELECT nomor_meja FROM meja WHERE id_meja=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $m = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM meja WHERE id_meja=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    include 'success_overlay.php';
    tampilkan_sukses([
        'proses_judul' => 'Menghapus Meja…',
        'proses_sub'   => 'Sedang memproses penghapusan data meja',
        'sukses_judul' => 'Meja Berhasil Dihapus!',
        'sukses_sub'   => 'Meja "'.htmlspecialchars($m['nomor_meja'] ?? '').'" telah dihapus dari data',
        'redirect'     => 'data_meja.php',
        'tombol_label' => 'Lanjutkan ke Data Meja',
    ]);
    exit;
}

// Handle ubah status cepat
if(isset($_GET['status']) && isset($_GET['id'])){
    $id  = (int)$_GET['id'];
    $st  = $_GET['status'];

    $stmt = $conn->prepare("SELECT nomor_meja FROM meja WHERE id_meja=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $m = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $conn->prepare("UPDATE meja SET status=? WHERE id_meja=?");
    $stmt->bind_param("si", $st, $id);
    $stmt->execute();
    $stmt->close();

    include 'success_overlay.php';
    tampilkan_sukses([
        'proses_judul' => 'Memperbarui Status Meja…',
        'proses_sub'   => 'Sedang menyimpan perubahan status meja',
        'sukses_judul' => 'Status Meja Berhasil Diperbarui!',
        'sukses_sub'   => 'Meja "'.htmlspecialchars($m['nomor_meja'] ?? '').'" kini berstatus '.htmlspecialchars($st),
        'redirect'     => 'data_meja.php',
        'tombol_label' => 'Lanjutkan ke Data Meja',
    ]);
    exit;
}

$query = mysqli_query($conn, "SELECT * FROM meja ORDER BY nomor_meja ASC");
$stats = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total,
            SUM(status='Tersedia') as tersedia,
            SUM(status='Terisi') as terisi,
            SUM(status='Dipesan') as dipesan
     FROM meja"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="../assets/css/lucide-icons.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Meja – YOLAZCAKE</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    :root{
      --gold:#D4AF37;--gold-l:#FFE88A;--gold-d:#b8860b;
      --rose:#ee2a7b;--purple:#8A2BE2;
      --bg1:#0d0520;--bg2:#1a0a3a;--bg3:#150830;
      --glass:rgba(255,255,255,0.05);--glass-h:rgba(255,255,255,0.08);
      --text:#fff;--muted:rgba(255,255,255,0.5);
    }
    html{scroll-behavior:smooth;}
    body{min-height:100vh;font-family:'Inter',sans-serif;
      background:linear-gradient(160deg,#1e0e3a 0%,#2d1560 50%,#1a0a2e 100%);
      color:var(--text);overflow-x:hidden;}
    body::before{content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
      background:
        radial-gradient(ellipse at 20% 30%,rgba(212,175,55,.10) 0%,transparent 55%),
        radial-gradient(ellipse at 80% 70%,rgba(232,160,191,.10) 0%,transparent 55%);}

    /* ── HERO ── */
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

    /* ── WRAPPER ── */
    .page-wrapper{position:relative;z-index:1;padding:36px 28px 80px;max-width:1100px;margin:0 auto;}

    /* ── TOP BAR ── */
    .top-bar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;
      margin-bottom:28px;opacity:0;animation:cardReveal .7s forwards .7s;}
    .section-eyebrow{font-size:.72em;font-weight:600;letter-spacing:4px;text-transform:uppercase;color:var(--gold);}
    .btn-tambah{display:inline-flex;align-items:center;gap:8px;padding:12px 26px;
      background:linear-gradient(135deg,var(--gold) 0%,var(--gold-d) 50%,var(--gold) 100%);background-size:200% 100%;
      animation:goldSlide 3s linear infinite;color:#1e0e3a;font-family:'Inter',sans-serif;
      font-size:.82em;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;
      border:none;border-radius:999px;cursor:pointer;text-decoration:none;
      box-shadow:0 6px 22px rgba(212,175,55,.35);transition:transform .25s,box-shadow .35s;position:relative;overflow:hidden;}
    .btn-tambah::before{content:'';position:absolute;inset:0;
      background:linear-gradient(90deg,transparent,rgba(255,255,255,.18),transparent);transform:translateX(-100%);transition:transform .5s;}
    .btn-tambah:hover::before{transform:translateX(100%);}
    .btn-tambah:hover{transform:translateY(-3px) scale(1.04);box-shadow:0 12px 36px rgba(212,175,55,.5);}
    @keyframes goldSlide{0%{background-position:0% 0;}100%{background-position:200% 0;}}

    /* ── STATS ── */
    .stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;opacity:0;animation:cardReveal .7s forwards .85s;}
    .stat-card{background:rgba(255,255,255,.06);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.1);
      border-radius:18px;padding:20px 22px;display:flex;align-items:center;gap:14px;
      transition:border-color .35s,box-shadow .35s,transform .3s;}
    .stat-card:hover{border-color:rgba(212,175,55,.35);box-shadow:0 0 24px rgba(212,175,55,.2);transform:translateY(-3px);}
    .stat-card::before{content:'';display:block;width:3px;height:40px;border-radius:999px;
      background:linear-gradient(to bottom,var(--gold),var(--gold-d));flex-shrink:0;}
    .stat-icon{font-size:1.5em;}
    .stat-val{font-family:'Playfair Display',serif;font-size:1.7em;font-weight:700;color:var(--gold);line-height:1;}
    .stat-lbl{font-size:.75em;color:var(--muted);margin-top:2px;}

    /* ── ALERT ── */
    .alert{padding:14px 20px;border-radius:12px;margin-bottom:20px;font-size:.88em;
      display:flex;align-items:center;gap:10px;opacity:0;animation:cardReveal .5s forwards;}
    .alert-success{background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.3);color:#6ee7b7;}

    /* ── MEJA GRID ── */
    .meja-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:18px;
      opacity:0;animation:cardReveal .8s forwards 1.0s;}
    .meja-card{background:var(--glass);backdrop-filter:blur(20px);
      border:1px solid rgba(255,255,255,.1);border-radius:22px;padding:24px;
      position:relative;overflow:hidden;transition:transform .3s,border-color .3s,box-shadow .3s;}
    .meja-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,var(--gold),var(--rose),var(--gold));background-size:200%;animation:goldSlide 4s linear infinite;}
    .meja-card:hover{transform:translateY(-6px);border-color:rgba(212,175,55,.3);box-shadow:0 12px 40px rgba(212,175,55,.15);}

    .meja-num{font-family:'Playfair Display',serif;font-size:2em;font-weight:700;color:var(--gold);}
    .meja-cap{font-size:.8em;color:var(--muted);margin-top:2px;}
    .meja-note{font-size:.82em;color:rgba(255,255,255,.6);margin-top:8px;min-height:32px;}

    .status-badge{display:inline-block;padding:5px 14px;border-radius:999px;font-size:.72em;font-weight:700;
      letter-spacing:.8px;text-transform:uppercase;margin-top:12px;}
    .s-tersedia{background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.35);color:#6ee7b7;}
    .s-terisi{background:rgba(239,68,68,.14);border:1px solid rgba(239,68,68,.35);color:#fca5a5;}
    .s-dipesan{background:rgba(212,175,55,.12);border:1px solid rgba(212,175,55,.35);color:var(--gold);}
    .s-nonaktif{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.15);color:var(--muted);}

    .meja-actions{display:flex;gap:8px;margin-top:16px;flex-wrap:wrap;}
    .btn-act{display:inline-flex;align-items:center;gap:5px;padding:7px 13px;border-radius:8px;
      font-size:.72em;font-weight:600;text-decoration:none;border:1px solid transparent;
      transition:transform .2s,box-shadow .25s,background .25s;}
    .btn-act:hover{transform:translateY(-2px);}
    .btn-edit{background:rgba(212,175,55,.18);border-color:rgba(212,175,55,.4);color:var(--gold);}
    .btn-edit:hover{background:rgba(212,175,55,.32);box-shadow:0 4px 16px rgba(212,175,55,.3);}
    .btn-hapus{background:rgba(239,68,68,.14);border-color:rgba(239,68,68,.35);color:#fca5a5;}
    .btn-hapus:hover{background:rgba(239,68,68,.28);box-shadow:0 4px 16px rgba(239,68,68,.25);}

    .status-select{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);
      color:#fff;padding:6px 10px;border-radius:8px;font-size:.72em;cursor:pointer;
      margin-top:4px;width:100%;}
    .status-select:focus{outline:none;border-color:var(--gold);}

    /* ── EMPTY ── */
    .empty-state{text-align:center;padding:80px 20px;color:var(--muted);grid-column:1/-1;}
    .empty-state .es-icon{font-size:3.5em;margin-bottom:16px;opacity:.5;}

    /* ── PARTICLE ── */
    .particle{position:fixed;border-radius:50%;pointer-events:none;animation:particleFloat linear infinite;z-index:0;}
    @keyframes particleFloat{0%{transform:translateY(100vh) scale(0);opacity:0;}10%{opacity:.5;}90%{opacity:.3;}100%{transform:translateY(-100px) scale(1);opacity:0;}}
    @keyframes cardReveal{to{opacity:1;transform:translateY(0);}}

    .btn-back{display:inline-flex;align-items:center;gap:8px;padding:10px 22px;
      background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.3);color:var(--gold);
      font-size:.82em;font-weight:600;letter-spacing:1px;border-radius:999px;text-decoration:none;
      transition:transform .25s,box-shadow .3s,background .3s;margin-bottom:24px;display:inline-flex;}
    .btn-back:hover{transform:translateX(-3px);background:rgba(212,175,55,.2);box-shadow:0 6px 20px rgba(212,175,55,.25);}

    @media(max-width:768px){
      .stats-row{grid-template-columns:repeat(2,1fr);}
      .hero-inner h1{font-size:2em;}
      .page-wrapper{padding:24px 16px 60px;}
    }
  </style>
</head>
<body>
<div id="particles"></div>

<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow"><i data-lucide="sparkle" class="lucide-ic"></i> YOLAZCAKE Sintang <i data-lucide="sparkle" class="lucide-ic"></i></p>
    <h1>Manajemen Meja</h1>
    <p class="hero-sub">Kelola status dan kapasitas meja cafe</p>
    <div class="hero-divider"><span></span><span class="diamond"><i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i></span><span></span></div>
  </div>
</div>

<div class="page-wrapper">

  <a href="../dashboard.php" class="btn-back"><i data-lucide="arrow-left" class="lucide-ic"></i> Dashboard</a>

  <div class="top-bar">
    <span class="section-eyebrow"><i data-lucide="sparkle" class="lucide-ic"></i> Daftar Meja</span>
    <a href="tambah_meja.php" class="btn-tambah">+ Tambah Meja</a>
  </div>

  <div class="stats-row">
    <div class="stat-card"><span class="stat-icon"><i data-lucide="armchair" class="lucide-ic"></i></span>
      <div><div class="stat-val"><?= $stats['total'] ?></div><div class="stat-lbl">Total Meja</div></div></div>
    <div class="stat-card"><span class="stat-icon"><i data-lucide="check-circle" class="lucide-ic"></i></span>
      <div><div class="stat-val"><?= $stats['tersedia'] ?></div><div class="stat-lbl">Tersedia</div></div></div>
    <div class="stat-card"><span class="stat-icon"><i data-lucide="circle" class="lucide-ic lucide-fill" style="color:#ef4444"></i></span>
      <div><div class="stat-val"><?= $stats['terisi'] ?></div><div class="stat-lbl">Terisi</div></div></div>
    <div class="stat-card"><span class="stat-icon"><i data-lucide="clipboard-list" class="lucide-ic"></i></span>
      <div><div class="stat-val"><?= $stats['dipesan'] ?></div><div class="stat-lbl">Dipesan</div></div></div>
  </div>

  <div class="meja-grid">
    <?php if(mysqli_num_rows($query) > 0): while($d = mysqli_fetch_assoc($query)): ?>
    <div class="meja-card">
      <div class="meja-num"><i data-lucide="armchair" class="lucide-ic"></i> <?= htmlspecialchars($d['nomor_meja']) ?></div>
      <div class="meja-cap"><i data-lucide="user" class="lucide-ic"></i> Kapasitas: <?= $d['kapasitas'] ?> orang</div>
      <div class="meja-note"><?= $d['keterangan'] ? htmlspecialchars($d['keterangan']) : '<span style="opacity:.4">Tidak ada keterangan</span>' ?></div>

      <?php
        $sc = match($d['status']){
          'Tersedia'   => 's-tersedia',
          'Terisi'     => 's-terisi',
          'Dipesan'    => 's-dipesan',
          default      => 's-nonaktif'
        };
      ?>
      <span class="status-badge <?= $sc ?>"><?= $d['status'] ?></span>

      <!-- Ubah status cepat -->
      <form method="get" style="margin-top:10px;">
        <input type="hidden" name="id" value="<?= $d['id_meja'] ?>">
        <select name="status" class="status-select" onchange="this.form.submit()">
          <?php foreach(['Tersedia','Terisi','Dipesan','Tidak Aktif'] as $s): ?>
          <option value="<?= $s ?>" <?= $d['status']===$s?'selected':'' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </form>

      <div class="meja-actions">
        <a href="edit_meja.php?id=<?= $d['id_meja'] ?>" class="btn-act btn-edit"><i data-lucide="pencil" class="lucide-ic"></i> Edit</a>
        <a href="?hapus=<?= $d['id_meja'] ?>" class="btn-act btn-hapus"
           onclick="return confirm('Yakin hapus meja <?= htmlspecialchars($d['nomor_meja']) ?>?')"><i data-lucide="trash-2" class="lucide-ic"></i> Hapus</a>
      </div>
    </div>
    <?php endwhile; else: ?>
    <div class="empty-state"><div class="es-icon"><i data-lucide="armchair" class="lucide-ic"></i></div><p>Belum ada data meja</p></div>
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
  const c=document.getElementById('particles');
  const pc=['rgba(212,175,55,.4)','rgba(138,43,226,.3)','rgba(255,255,255,.15)'];
  for(let i=0;i<14;i++){
    const p=document.createElement('div'); p.className='particle';
    const s=Math.random()*4+2;
    p.style.cssText=`width:${s}px;height:${s}px;background:${pc[Math.floor(Math.random()*pc.length)]};left:${Math.random()*100}%;animation-duration:${10+Math.random()*12}s;animation-delay:${Math.random()*10}s;`;
    c.appendChild(p);
  }
})();
</script>

<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide){lucide.createIcons();}</script>
</body>
</html>
