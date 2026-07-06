<?php
session_start();
require_once __DIR__.'/../config/staff_guard.php';
require_staff_login();
include '../config/koneksi.php';

// Handle hapus
if(isset($_GET['hapus'])){
    $id = (int)$_GET['hapus'];

    $stmt = $conn->prepare("SELECT kode_promo FROM promo WHERE id_promo=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $p = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM promo WHERE id_promo=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    include 'success_overlay.php';
    tampilkan_sukses([
        'proses_judul' => 'Menghapus Promo…',
        'proses_sub'   => 'Sedang memproses penghapusan data promo',
        'sukses_judul' => 'Promo Berhasil Dihapus!',
        'sukses_sub'   => 'Promo "'.htmlspecialchars($p['kode_promo'] ?? '').'" telah dihapus dari data',
        'redirect'     => 'data_promo.php',
        'tombol_label' => 'Lanjutkan ke Kelola Promo',
    ]);
    exit;
}

// Toggle status aktif/nonaktif
if(isset($_GET['toggle'])){
    $id = (int)$_GET['toggle'];

    $stmt = $conn->prepare("SELECT kode_promo,status FROM promo WHERE id_promo=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $d = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $baru = null;
    if($d){
        $baru = $d['status']==='Aktif' ? 'Nonaktif' : 'Aktif';
        $stmt = $conn->prepare("UPDATE promo SET status=? WHERE id_promo=?");
        $stmt->bind_param("si", $baru, $id);
        $stmt->execute();
        $stmt->close();
    }

    include 'success_overlay.php';
    tampilkan_sukses([
        'proses_judul' => 'Memperbarui Status Promo…',
        'proses_sub'   => 'Sedang menyimpan perubahan status promo',
        'sukses_judul' => 'Status Promo Berhasil Diperbarui!',
        'sukses_sub'   => 'Promo "'.htmlspecialchars($d['kode_promo'] ?? '').'" kini berstatus '.htmlspecialchars($baru ?? '-'),
        'redirect'     => 'data_promo.php',
        'tombol_label' => 'Lanjutkan ke Kelola Promo',
    ]);
    exit;
}

// Tambah
if(isset($_POST['tambah'])){
    $kode      = strtoupper(trim($_POST['kode_promo']));
    $judul     = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $diskon    = (int)$_POST['diskon_persen'];
    $minbel    = (int)$_POST['min_belanja'];
    $poin      = (int)$_POST['poin_bonus'];
    $mulai     = $_POST['tanggal_mulai'];
    $selesai   = $_POST['tanggal_selesai'];

    if($kode && $judul){
        $stmt = $conn->prepare("SELECT id_promo FROM promo WHERE kode_promo=?");
        $stmt->bind_param("s", $kode);
        $stmt->execute();
        $cek = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if(!$cek){
            $stmt = $conn->prepare(
                "INSERT INTO promo (kode_promo,judul,deskripsi,diskon_persen,min_belanja,poin_bonus,tanggal_mulai,tanggal_selesai,status)
                 VALUES (?,?,?,?,?,?,?,?,'Aktif')");
            $stmt->bind_param("sssiiiss", $kode, $judul, $deskripsi, $diskon, $minbel, $poin, $mulai, $selesai);
            $stmt->execute();
            $stmt->close();
        }
    }

    include 'success_overlay.php';
    tampilkan_sukses([
        'proses_judul' => 'Menyimpan Promo…',
        'proses_sub'   => 'Sedang menambahkan promo baru',
        'sukses_judul' => 'Promo Berhasil Ditambahkan!',
        'sukses_sub'   => 'Promo "'.htmlspecialchars($kode).'" kini aktif untuk pelanggan',
        'redirect'     => 'data_promo.php',
        'tombol_label' => 'Lanjutkan ke Kelola Promo',
    ]);
    exit;
}

// Edit
if(isset($_POST['edit'])){
    $id        = (int)$_POST['id_promo'];
    $kode      = strtoupper(trim($_POST['kode_promo']));
    $judul     = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $diskon    = (int)$_POST['diskon_persen'];
    $minbel    = (int)$_POST['min_belanja'];
    $poin      = (int)$_POST['poin_bonus'];
    $mulai     = $_POST['tanggal_mulai'];
    $selesai   = $_POST['tanggal_selesai'];

    if($kode && $judul){
        $stmt = $conn->prepare(
            "UPDATE promo SET kode_promo=?, judul=?, deskripsi=?,
             diskon_persen=?, min_belanja=?, poin_bonus=?,
             tanggal_mulai=?, tanggal_selesai=? WHERE id_promo=?");
        $stmt->bind_param("sssiiissi", $kode, $judul, $deskripsi, $diskon, $minbel, $poin, $mulai, $selesai, $id);
        $stmt->execute();
        $stmt->close();
    }

    include 'success_overlay.php';
    tampilkan_sukses([
        'proses_judul' => 'Memperbarui Promo…',
        'proses_sub'   => 'Sedang menyimpan perubahan data promo',
        'sukses_judul' => 'Promo Berhasil Diperbarui!',
        'sukses_sub'   => 'Promo "'.htmlspecialchars($kode).'" telah diperbarui',
        'redirect'     => 'data_promo.php',
        'tombol_label' => 'Lanjutkan ke Kelola Promo',
    ]);
    exit;
}

$hari_ini = date('Y-m-d');
$query = mysqli_query($conn, "SELECT *,
    CASE WHEN tanggal_selesai IS NOT NULL AND tanggal_selesai < '$hari_ini' THEN 1 ELSE 0 END as kadaluarsa
    FROM promo ORDER BY id_promo DESC");
$total = mysqli_num_rows($query);
$stats = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total,
            SUM(status='Aktif') as aktif,
            SUM(status='Nonaktif') as nonaktif
     FROM promo"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Promo – YOLAZCAKE</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{min-height:100vh;font-family:'Inter',sans-serif;
      background:linear-gradient(160deg,#1e0e3a 0%,#2d1560 50%,#1a0a2e 100%);color:#fff;overflow-x:hidden;}
    body::before{content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
      background:radial-gradient(ellipse at 20% 30%,rgba(212,175,55,.10) 0%,transparent 55%),
                 radial-gradient(ellipse at 80% 70%,rgba(232,160,191,.10) 0%,transparent 55%);}
    .page-hero{position:relative;height:240px;display:flex;flex-direction:column;align-items:center;justify-content:center;overflow:hidden;
      background:linear-gradient(135deg,#2b1a11 0%,#4a2c1a 40%,#6d3e26 70%,#3a1f0e 100%);z-index:1;}
    .page-hero::before{content:'';position:absolute;inset:0;
      background:radial-gradient(ellipse at 30% 50%,rgba(212,175,55,.18) 0%,transparent 60%),
                 radial-gradient(ellipse at 75% 40%,rgba(232,160,191,.15) 0%,transparent 55%);
      animation:heroAurora 8s ease-in-out infinite alternate;}
    @keyframes heroAurora{0%{opacity:.6;transform:scale(1);}100%{opacity:1;transform:scale(1.08) translateX(10px);}}
    .sparkle{position:absolute;border-radius:50%;pointer-events:none;animation:floatDot linear infinite;}
    @keyframes floatDot{0%{transform:translateY(0) rotate(0deg);opacity:0;}20%{opacity:1;}80%{opacity:.8;}100%{transform:translateY(-280px) rotate(360deg);opacity:0;}}
    .hero-inner{position:relative;z-index:2;text-align:center;}
    .hero-eyebrow{font-size:.72em;font-weight:500;letter-spacing:5px;text-transform:uppercase;color:#D4AF37;margin-bottom:10px;opacity:0;animation:fadeSlideDown .8s forwards .3s;}
    .hero-inner h1{font-family:'Playfair Display',serif;font-size:3em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,#D4AF37 60%,#FFE4B5 80%,#fff);background-size:200%;
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
      animation:shimmerText 4s ease-in-out infinite,fadeSlideDown .9s forwards .5s;opacity:0;}
    .hero-sub{font-size:.9em;color:rgba(255,255,255,.65);margin-top:10px;opacity:0;animation:fadeSlideDown .9s forwards .9s;}
    .hero-divider{margin-top:16px;display:flex;justify-content:center;align-items:center;gap:12px;opacity:0;animation:fadeSlideDown .9s forwards 1.1s;}
    .hero-divider span{display:block;width:60px;height:1px;background:linear-gradient(to right,transparent,#D4AF37);}
    .hero-divider span:last-child{background:linear-gradient(to left,transparent,#D4AF37);}
    .hero-divider .diamond{color:#D4AF37;font-size:.75em;letter-spacing:4px;}
    @keyframes shimmerText{0%{background-position:100% 0;}100%{background-position:-100% 0;}}
    @keyframes fadeSlideDown{from{opacity:0;transform:translateY(-18px);}to{opacity:1;transform:translateY(0);}}

    .page-wrapper{position:relative;z-index:1;padding:36px 28px 80px;max-width:1100px;margin:0 auto;}
    .btn-back{display:inline-flex;align-items:center;gap:8px;padding:10px 22px;
      background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.3);color:#D4AF37;
      font-size:.82em;font-weight:600;letter-spacing:1px;border-radius:999px;text-decoration:none;
      transition:transform .25s,background .3s;margin-bottom:24px;}
    .btn-back:hover{transform:translateX(-3px);background:rgba(212,175,55,.2);}

    .top-bar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;margin-bottom:24px;}
    .btn-tambah{display:inline-flex;align-items:center;gap:8px;padding:12px 26px;
      background:linear-gradient(135deg,#D4AF37 0%,#b8860b 50%,#D4AF37 100%);background-size:200%;
      animation:goldSlide 3s linear infinite;color:#1e0e3a;font-size:.82em;font-weight:700;
      letter-spacing:1.5px;text-transform:uppercase;border:none;border-radius:999px;cursor:pointer;
      box-shadow:0 6px 22px rgba(212,175,55,.35);transition:transform .25s,box-shadow .35s;}
    .btn-tambah:hover{transform:translateY(-3px) scale(1.04);box-shadow:0 12px 36px rgba(212,175,55,.5);}
    @keyframes goldSlide{0%{background-position:0;}100%{background-position:200%;}}

    .stats-row{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:28px;}
    .stat-card{background:rgba(255,255,255,.06);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.1);
      border-radius:18px;padding:20px 22px;display:flex;align-items:center;gap:14px;}
    .stat-icon{font-size:1.5em;}
    .stat-val{font-family:'Playfair Display',serif;font-size:1.7em;font-weight:700;color:#D4AF37;line-height:1;}
    .stat-lbl{font-size:.75em;color:rgba(255,255,255,.5);margin-top:2px;}

    .alert{padding:14px 20px;border-radius:12px;margin-bottom:20px;font-size:.88em;display:flex;align-items:center;gap:10px;}
    .alert-success{background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.3);color:#6ee7b7;}

    .promo-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:18px;}
    .promo-card{background:rgba(255,255,255,.05);backdrop-filter:blur(20px);
      border:1px solid rgba(255,255,255,.1);border-radius:22px;padding:24px;position:relative;overflow:hidden;
      transition:transform .3s,border-color .3s,box-shadow .3s;}
    .promo-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);background-size:200%;animation:goldSlide 4s linear infinite;}
    .promo-card:hover{transform:translateY(-6px);border-color:rgba(212,175,55,.3);box-shadow:0 12px 40px rgba(212,175,55,.15);}
    .promo-card.nonaktif{opacity:.55;}
    .promo-top{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;}
    .promo-kode{font-family:'Playfair Display',serif;font-size:1.4em;font-weight:700;color:#D4AF37;letter-spacing:1px;}
    .promo-diskon{background:rgba(238,42,123,.15);border:1px solid rgba(238,42,123,.35);color:#ff85b8;
      padding:4px 14px;border-radius:999px;font-size:.85em;font-weight:700;}
    .promo-judul{font-weight:600;margin-top:10px;color:#fff;}
    .promo-desc{font-size:.82em;color:rgba(255,255,255,.55);margin-top:4px;min-height:30px;}
    .promo-meta{display:flex;flex-wrap:wrap;gap:10px;margin-top:14px;font-size:.75em;color:rgba(255,255,255,.6);}
    .promo-meta span{background:rgba(255,255,255,.06);padding:4px 10px;border-radius:8px;}
    .status-badge{display:inline-block;padding:5px 14px;border-radius:999px;font-size:.7em;font-weight:700;
      letter-spacing:.8px;text-transform:uppercase;margin-top:14px;cursor:pointer;border:none;}
    .s-aktif{background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.35);color:#6ee7b7;}
    .s-nonaktif{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.15);color:rgba(255,255,255,.5);}
    .badge-expired{display:inline-block;margin-left:6px;background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.35);
      color:#fca5a5;padding:5px 12px;border-radius:999px;font-size:.7em;font-weight:700;text-transform:uppercase;}
    .promo-actions{display:flex;gap:8px;margin-top:16px;flex-wrap:wrap;}
    .btn-act{display:inline-flex;align-items:center;gap:5px;padding:7px 13px;border-radius:8px;
      font-size:.72em;font-weight:600;text-decoration:none;border:1px solid transparent;cursor:pointer;
      font-family:'Inter',sans-serif;transition:transform .2s,box-shadow .25s,background .25s;background:none;}
    .btn-act:hover{transform:translateY(-2px);}
    .btn-edit{background:rgba(212,175,55,.18);border-color:rgba(212,175,55,.4);color:#D4AF37;}
    .btn-edit:hover{background:rgba(212,175,55,.32);}
    .btn-hapus{background:rgba(239,68,68,.14);border-color:rgba(239,68,68,.35);color:#fca5a5;}
    .btn-hapus:hover{background:rgba(239,68,68,.28);}
    .empty-state{text-align:center;padding:80px 20px;color:rgba(255,255,255,.4);grid-column:1/-1;}
    .empty-state .es-icon{font-size:3.5em;margin-bottom:16px;opacity:.5;}

    /* MODAL */
    .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(4px);
      display:none;align-items:center;justify-content:center;z-index:50;}
    .modal-overlay.open{display:flex;}
    .modal{background:#1e0e3a;border:1px solid rgba(212,175,55,.3);border-radius:24px;padding:32px;
      width:92%;max-width:480px;max-height:90vh;overflow-y:auto;position:relative;}
    .modal h3{font-family:'Playfair Display',serif;color:#D4AF37;margin-bottom:20px;}
    .modal-close{position:absolute;top:14px;right:16px;background:none;border:none;color:rgba(255,255,255,.5);
      font-size:1.3em;cursor:pointer;}
    .modal-close:hover{color:#fff;}
    .form-group{margin-bottom:16px;}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
    label{display:block;font-size:.72em;font-weight:600;letter-spacing:1.2px;text-transform:uppercase;color:rgba(212,175,55,.9);margin-bottom:6px;}
    input,textarea{width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);
      color:#fff;padding:10px 14px;border-radius:10px;font-family:'Inter',sans-serif;font-size:.86em;outline:none;}
    input:focus,textarea:focus{border-color:#D4AF37;}
    textarea{resize:vertical;min-height:60px;}
    .btn-submit{width:100%;padding:12px;background:linear-gradient(135deg,#D4AF37 0%,#b8860b 50%,#D4AF37 100%);
      color:#1e0e3a;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;border:none;border-radius:10px;
      cursor:pointer;font-size:.85em;margin-top:6px;}

    @media(max-width:768px){.stats-row{grid-template-columns:1fr;}.hero-inner h1{font-size:2em;}.page-wrapper{padding:24px 16px 60px;}}
  </style>
</head>
<body>
<div id="particles"></div>
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow">✦ YOLAZCAKE Sintang ✦</p>
    <h1>Kelola Promo</h1>
    <p class="hero-sub">Atur kode promo & diskon untuk pelanggan</p>
    <div class="hero-divider"><span></span><span class="diamond">✦ ✦ ✦</span><span></span></div>
  </div>
</div>

<div class="page-wrapper">
  <a href="../dashboard.php" class="btn-back">← Dashboard</a>

  <div class="top-bar">
    <span class="section-eyebrow" style="font-size:.72em;font-weight:600;letter-spacing:4px;text-transform:uppercase;color:#D4AF37;">✦ Daftar Promo</span>
    <button class="btn-tambah" onclick="openAdd()">+ Tambah Promo</button>
  </div>

  <div class="stats-row">
    <div class="stat-card"><span class="stat-icon">🏷️</span>
      <div><div class="stat-val"><?= $stats['total'] ?: 0 ?></div><div class="stat-lbl">Total Promo</div></div></div>
    <div class="stat-card"><span class="stat-icon">✅</span>
      <div><div class="stat-val"><?= $stats['aktif'] ?: 0 ?></div><div class="stat-lbl">Aktif</div></div></div>
    <div class="stat-card"><span class="stat-icon">⛔</span>
      <div><div class="stat-val"><?= $stats['nonaktif'] ?: 0 ?></div><div class="stat-lbl">Nonaktif</div></div></div>
  </div>

  <div class="promo-grid">
    <?php if($total > 0): while($d = mysqli_fetch_assoc($query)): ?>
    <div class="promo-card <?= $d['status']==='Nonaktif'?'nonaktif':'' ?>">
      <div class="promo-top">
        <span class="promo-kode">🏷️ <?= htmlspecialchars($d['kode_promo']) ?></span>
        <span class="promo-diskon"><?= $d['diskon_persen'] ?>% OFF</span>
      </div>
      <div class="promo-judul"><?= htmlspecialchars($d['judul']) ?></div>
      <div class="promo-desc"><?= $d['deskripsi'] ? htmlspecialchars($d['deskripsi']) : '-' ?></div>
      <div class="promo-meta">
        <span>💰 Min. Rp <?= number_format($d['min_belanja'],0,',','.') ?></span>
        <?php if($d['poin_bonus'] > 0): ?><span>⭐ +<?= $d['poin_bonus'] ?> poin</span><?php endif; ?>
        <?php if($d['tanggal_mulai'] || $d['tanggal_selesai']): ?>
        <span>📅 <?= $d['tanggal_mulai'] ? date('d/m/y',strtotime($d['tanggal_mulai'])) : '...' ?> – <?= $d['tanggal_selesai'] ? date('d/m/y',strtotime($d['tanggal_selesai'])) : '...' ?></span>
        <?php endif; ?>
      </div>

      <a href="?toggle=<?= $d['id_promo'] ?>" style="text-decoration:none;">
        <span class="status-badge <?= $d['status']==='Aktif'?'s-aktif':'s-nonaktif' ?>">
          <?= $d['status']==='Aktif' ? '✅ Aktif' : '⛔ Nonaktif' ?>
        </span>
      </a>
      <?php if($d['kadaluarsa']): ?><span class="badge-expired">Kadaluarsa</span><?php endif; ?>

      <div class="promo-actions">
        <button class="btn-act btn-edit" onclick='openEdit(<?= json_encode($d) ?>)'>✏️ Edit</button>
        <a href="?hapus=<?= $d['id_promo'] ?>" class="btn-act btn-hapus"
           onclick="return confirm('Hapus promo <?= htmlspecialchars($d['kode_promo']) ?>?')">🗑️ Hapus</a>
      </div>
    </div>
    <?php endwhile; else: ?>
    <div class="empty-state"><div class="es-icon">🏷️</div><p>Belum ada promo. Klik "+ Tambah Promo" untuk membuat yang pertama.</p></div>
    <?php endif; ?>
  </div>
</div>

<!-- ADD MODAL -->
<div class="modal-overlay" id="addModal">
  <div class="modal">
    <button class="modal-close" onclick="closeModal('addModal')">✕</button>
    <h3>➕ Tambah Promo</h3>
    <form method="POST">
      <div class="form-row">
        <div class="form-group"><label>Kode Promo</label><input type="text" name="kode_promo" placeholder="YOLA25" required style="text-transform:uppercase;"></div>
        <div class="form-group"><label>Diskon (%)</label><input type="number" name="diskon_persen" min="1" max="100" placeholder="25" required></div>
      </div>
      <div class="form-group"><label>Judul Promo</label><input type="text" name="judul" placeholder="Diskon Akhir Pekan" required></div>
      <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" placeholder="Syarat & ketentuan singkat..."></textarea></div>
      <div class="form-row">
        <div class="form-group"><label>Min. Belanja (Rp)</label><input type="number" name="min_belanja" min="0" placeholder="150000"></div>
        <div class="form-group"><label>Bonus Poin</label><input type="number" name="poin_bonus" min="0" placeholder="50"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Tanggal Mulai</label><input type="date" name="tanggal_mulai"></div>
        <div class="form-group"><label>Tanggal Selesai</label><input type="date" name="tanggal_selesai"></div>
      </div>
      <button type="submit" name="tambah" class="btn-submit">✅ Simpan Promo</button>
    </form>
  </div>
</div>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="editModal">
  <div class="modal">
    <button class="modal-close" onclick="closeModal('editModal')">✕</button>
    <h3>✏️ Edit Promo</h3>
    <form method="POST">
      <input type="hidden" name="id_promo" id="e_id">
      <div class="form-row">
        <div class="form-group"><label>Kode Promo</label><input type="text" name="kode_promo" id="e_kode" required style="text-transform:uppercase;"></div>
        <div class="form-group"><label>Diskon (%)</label><input type="number" name="diskon_persen" id="e_diskon" min="1" max="100" required></div>
      </div>
      <div class="form-group"><label>Judul Promo</label><input type="text" name="judul" id="e_judul" required></div>
      <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" id="e_deskripsi"></textarea></div>
      <div class="form-row">
        <div class="form-group"><label>Min. Belanja (Rp)</label><input type="number" name="min_belanja" id="e_minbel" min="0"></div>
        <div class="form-group"><label>Bonus Poin</label><input type="number" name="poin_bonus" id="e_poin" min="0"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Tanggal Mulai</label><input type="date" name="tanggal_mulai" id="e_mulai"></div>
        <div class="form-group"><label>Tanggal Selesai</label><input type="date" name="tanggal_selesai" id="e_selesai"></div>
      </div>
      <button type="submit" name="edit" class="btn-submit">💾 Simpan Perubahan</button>
    </form>
  </div>
</div>

<script>
function openAdd(){ document.getElementById('addModal').classList.add('open'); }
function closeModal(id){ document.getElementById(id).classList.remove('open'); }
function openEdit(d){
  document.getElementById('e_id').value = d.id_promo;
  document.getElementById('e_kode').value = d.kode_promo;
  document.getElementById('e_diskon').value = d.diskon_persen;
  document.getElementById('e_judul').value = d.judul;
  document.getElementById('e_deskripsi').value = d.deskripsi;
  document.getElementById('e_minbel').value = d.min_belanja;
  document.getElementById('e_poin').value = d.poin_bonus;
  document.getElementById('e_mulai').value = d.tanggal_mulai;
  document.getElementById('e_selesai').value = d.tanggal_selesai;
  document.getElementById('editModal').classList.add('open');
}
document.querySelectorAll('.modal-overlay').forEach(function(ov){
  ov.addEventListener('click', function(e){ if(e.target === this) this.classList.remove('open'); });
});

(function(){
  const hero=document.getElementById('pageHero');
  const colors=['#D4AF37','#FFE4B5','#8A2BE2','#fff'];
  for(let i=0;i<20;i++){
    const d=document.createElement('div');d.className='sparkle';
    const s=Math.random()*5+2;
    d.style.cssText=`width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;bottom:${Math.random()*30}%;animation-duration:${4+Math.random()*7}s;animation-delay:${Math.random()*5}s;opacity:0;`;
    hero.appendChild(d);
  }
})();
</script>
</body>
</html>
