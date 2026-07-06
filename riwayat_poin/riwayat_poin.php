<?php
session_start();
require_once __DIR__.'/../config/staff_guard.php';
require_staff_login();
include '../config/koneksi.php';

// Handle tambah poin manual
if(isset($_POST['tambah_poin'])){
    $id_member = (int)$_POST['id_member'];
    $jenis     = $_POST['jenis'];
    $poin      = (int)$_POST['poin'];
    $ket       = trim($_POST['keterangan']);

    $stmt = $conn->prepare("SELECT nama FROM member WHERE id_member=?");
    $stmt->bind_param("i", $id_member);
    $stmt->execute();
    $m = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if($id_member && $poin > 0){
        $stmt = $conn->prepare("INSERT INTO riwayat_poin (id_member,jenis,poin,keterangan) VALUES (?,?,?,?)");
        $stmt->bind_param("isis", $id_member, $jenis, $poin, $ket);
        $stmt->execute();
        $stmt->close();

        // Update total poin member
        if($jenis==='Masuk'){
            $stmt = $conn->prepare("UPDATE member SET poin=poin+? WHERE id_member=?");
        } else {
            $stmt = $conn->prepare("UPDATE member SET poin=GREATEST(0,poin-?) WHERE id_member=?");
        }
        $stmt->bind_param("ii", $poin, $id_member);
        $stmt->execute();
        $stmt->close();
    }

    include 'success_overlay.php';
    tampilkan_sukses([
        'proses_judul' => 'Menyimpan Riwayat Poin…',
        'proses_sub'   => 'Sedang mencatat perubahan poin member',
        'sukses_judul' => 'Poin Berhasil Ditambahkan!',
        'sukses_sub'   => htmlspecialchars($poin).' poin '.htmlspecialchars($jenis).' untuk "'.htmlspecialchars($m['nama'] ?? 'member').'"',
        'redirect'     => 'riwayat_poin.php',
        'tombol_label' => 'Lanjutkan ke Riwayat Poin',
    ]);
    exit;
}

// Handle hapus entry
if(isset($_GET['hapus'])){
    $id = (int)$_GET['hapus'];

    $stmt = $conn->prepare("SELECT * FROM riwayat_poin WHERE id_riwayat=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $rw = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if($rw){
        // Rollback poin member
        if($rw['jenis']==='Masuk'){
            $stmt = $conn->prepare("UPDATE member SET poin=GREATEST(0,poin-?) WHERE id_member=?");
        } else {
            $stmt = $conn->prepare("UPDATE member SET poin=poin+? WHERE id_member=?");
        }
        $stmt->bind_param("ii", $rw['poin'], $rw['id_member']);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM riwayat_poin WHERE id_riwayat=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    include 'success_overlay.php';
    tampilkan_sukses([
        'proses_judul' => 'Menghapus Entri…',
        'proses_sub'   => 'Sedang memproses penghapusan & mengembalikan poin',
        'sukses_judul' => 'Entri Berhasil Dihapus!',
        'sukses_sub'   => 'Entri riwayat telah dihapus dan poin member sudah dikembalikan',
        'redirect'     => 'riwayat_poin.php',
        'tombol_label' => 'Lanjutkan ke Riwayat Poin',
    ]);
    exit;
}

// Filter
$filter_member = (int)($_GET['member'] ?? 0);
if($filter_member){
    $stmt = $conn->prepare("SELECT rp.*,m.nama FROM riwayat_poin rp LEFT JOIN member m ON m.id_member=rp.id_member WHERE rp.id_member=? ORDER BY rp.created_at DESC");
    $stmt->bind_param("i", $filter_member);
    $stmt->execute();
    $query = $stmt->get_result();
} else {
    $query = $conn->query("SELECT rp.*,m.nama FROM riwayat_poin rp LEFT JOIN member m ON m.id_member=rp.id_member ORDER BY rp.created_at DESC");
}
$members = mysqli_query($conn,"SELECT id_member,nama,poin FROM member ORDER BY nama");

$stats = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(CASE WHEN jenis='Masuk' THEN poin ELSE 0 END) as total_masuk, SUM(CASE WHEN jenis='Keluar' THEN poin ELSE 0 END) as total_keluar, COUNT(*) as total FROM riwayat_poin"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Riwayat Poin – YOLAZCAKE</title>
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

    .page-wrapper{position:relative;z-index:1;padding:36px 28px 80px;max-width:1200px;margin:0 auto;}
    .btn-back{display:inline-flex;align-items:center;gap:8px;padding:10px 22px;
      background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.3);color:#D4AF37;
      font-size:.82em;font-weight:600;border-radius:999px;text-decoration:none;transition:transform .25s,background .3s;margin-bottom:24px;}
    .btn-back:hover{transform:translateX(-3px);background:rgba(212,175,55,.2);}

    .alert{padding:14px 20px;border-radius:12px;margin-bottom:20px;font-size:.88em;display:flex;align-items:center;gap:10px;}
    .alert-success{background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.3);color:#6ee7b7;}
    @keyframes cardReveal{from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:translateY(0);}}

    .stats-row{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:28px;opacity:0;animation:cardReveal .7s forwards .5s;}
    .stat-card{background:rgba(255,255,255,.06);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.1);
      border-radius:18px;padding:20px 22px;display:flex;align-items:center;gap:14px;
      transition:border-color .35s,box-shadow .35s,transform .3s;}
    .stat-card:hover{border-color:rgba(212,175,55,.35);box-shadow:0 0 24px rgba(212,175,55,.2);transform:translateY(-3px);}
    .stat-card::before{content:'';display:block;width:3px;height:40px;border-radius:999px;
      background:linear-gradient(to bottom,#D4AF37,#b8860b);flex-shrink:0;}
    .stat-icon{font-size:1.5em;}.stat-val{font-family:'Playfair Display',serif;font-size:1.7em;font-weight:700;color:#D4AF37;line-height:1;}
    .stat-lbl{font-size:.75em;color:rgba(255,255,255,.5);margin-top:2px;}

    .main-grid{display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;}

    /* TABLE */
    .table-card{background:rgba(255,255,255,.05);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.1);
      border-radius:24px;overflow:hidden;position:relative;opacity:0;animation:cardReveal .8s forwards .7s;}
    .table-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);background-size:200%;animation:goldSlide 4s linear infinite;}
    @keyframes goldSlide{0%{background-position:0;}100%{background-position:200%;}}
    .card-header{padding:18px 22px;border-bottom:1px solid rgba(255,255,255,.08);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;}
    .card-title{font-family:'Playfair Display',serif;font-size:1.1em;color:#D4AF37;}
    .filter-row{display:flex;gap:8px;align-items:center;}
    select.filter-sel{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);color:#fff;
      padding:7px 12px;border-radius:10px;font-size:.8em;cursor:pointer;}
    select.filter-sel:focus{outline:none;border-color:#D4AF37;}
    .btn-filter{padding:7px 14px;background:rgba(212,175,55,.15);border:1px solid rgba(212,175,55,.3);
      color:#D4AF37;border-radius:10px;font-size:.8em;font-weight:600;cursor:pointer;text-decoration:none;}
    .btn-filter:hover{background:rgba(212,175,55,.25);}
    table{width:100%;border-collapse:collapse;}
    thead tr{background:rgba(212,175,55,.12);border-bottom:1px solid rgba(212,175,55,.25);}
    thead th{padding:13px 16px;font-size:.7em;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:rgba(212,175,55,.9);text-align:left;}
    tbody tr{border-bottom:1px solid rgba(255,255,255,.06);transition:background .25s;}
    tbody tr:hover{background:rgba(212,175,55,.06);}
    tbody td{padding:13px 16px;font-size:.87em;color:rgba(255,255,255,.8);vertical-align:middle;}
    .td-nama{font-weight:600;color:#fff;}
    .badge-masuk{display:inline-flex;align-items:center;gap:4px;background:rgba(16,185,129,.15);
      border:1px solid rgba(16,185,129,.3);color:#6ee7b7;padding:3px 10px;border-radius:999px;font-size:.78em;font-weight:700;}
    .badge-keluar{display:inline-flex;align-items:center;gap:4px;background:rgba(239,68,68,.12);
      border:1px solid rgba(239,68,68,.3);color:#fca5a5;padding:3px 10px;border-radius:999px;font-size:.78em;font-weight:700;}
    .poin-val{font-family:'Playfair Display',serif;font-size:1.1em;font-weight:700;color:#D4AF37;}
    .btn-hapus-sm{background:rgba(239,68,68,.14);border:1px solid rgba(239,68,68,.35);color:#fca5a5;
      padding:5px 10px;border-radius:7px;font-size:.72em;text-decoration:none;transition:background .2s,transform .2s;}
    .btn-hapus-sm:hover{background:rgba(239,68,68,.28);transform:translateY(-2px);}
    .empty-state{text-align:center;padding:50px 20px;color:rgba(255,255,255,.4);}

    /* ADD POIN FORM */
    .form-panel{background:rgba(255,255,255,.05);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.1);
      border-radius:24px;padding:26px;position:relative;overflow:hidden;opacity:0;animation:cardReveal .8s forwards .9s;}
    .form-panel::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);background-size:200%;animation:goldSlide 4s linear infinite;}
    .form-panel h3{font-family:'Playfair Display',serif;font-size:1.1em;color:#D4AF37;margin-bottom:18px;}
    .form-group{margin-bottom:16px;}
    label{display:block;font-size:.74em;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:rgba(212,175,55,.9);margin-bottom:7px;}
    input,select.inp,textarea{width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);
      color:#fff;padding:10px 13px;border-radius:10px;font-family:'Inter',sans-serif;font-size:.87em;
      transition:border-color .3s;outline:none;}
    input:focus,select.inp:focus,textarea:focus{border-color:#D4AF37;box-shadow:0 0 0 3px rgba(212,175,55,.12);}
    select.inp option{background:#1e0e3a;}
    .jenis-row{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
    .jenis-opt{display:none;}
    .jenis-label{display:flex;align-items:center;justify-content:center;gap:6px;padding:10px;
      border-radius:10px;border:1px solid rgba(255,255,255,.15);cursor:pointer;font-size:.82em;font-weight:600;transition:all .25s;}
    .jenis-masuk:checked+.jenis-label-masuk{background:rgba(16,185,129,.2);border-color:rgba(16,185,129,.5);color:#6ee7b7;}
    .jenis-keluar:checked+.jenis-label-keluar{background:rgba(239,68,68,.15);border-color:rgba(239,68,68,.4);color:#fca5a5;}
    .btn-submit{width:100%;padding:12px;background:linear-gradient(135deg,#D4AF37 0%,#b8860b 50%,#D4AF37 100%);
      background-size:200%;animation:goldSlide 3s linear infinite;color:#1e0e3a;font-weight:700;
      font-size:.85em;letter-spacing:2px;text-transform:uppercase;border:none;border-radius:10px;cursor:pointer;
      transition:transform .25s,box-shadow .3s;}
    .btn-submit:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(212,175,55,.4);}

    @media(max-width:768px){.main-grid{grid-template-columns:1fr;}.stats-row{grid-template-columns:1fr;}.hero-inner h1{font-size:2em;}.page-wrapper{padding:24px 16px 60px;}}
  </style>
</head>
<body>
<div id="particles"></div>
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow">✦ YOLAZCAKE Sintang ✦</p>
    <h1>Riwayat Poin</h1>
    <p class="hero-sub">Pantau perolehan & penukaran poin member</p>
    <div class="hero-divider"><span></span><span class="diamond">✦ ✦ ✦</span><span></span></div>
  </div>
</div>

<div class="page-wrapper">
  <a href="../dashboard.php" class="btn-back">← Dashboard</a>

  <div class="stats-row">
    <div class="stat-card"><span class="stat-icon">⭐</span>
      <div><div class="stat-val"><?= number_format($stats['total_masuk']??0) ?></div><div class="stat-lbl">Total Poin Masuk</div></div></div>
    <div class="stat-card"><span class="stat-icon">🎁</span>
      <div><div class="stat-val"><?= number_format($stats['total_keluar']??0) ?></div><div class="stat-lbl">Total Poin Keluar</div></div></div>
    <div class="stat-card"><span class="stat-icon">📋</span>
      <div><div class="stat-val"><?= number_format($stats['total']??0) ?></div><div class="stat-lbl">Total Transaksi</div></div></div>
  </div>

  <div class="main-grid">

    <!-- TABLE -->
    <div class="table-card">
      <div class="card-header">
        <span class="card-title">⭐ Log Poin</span>
        <form method="GET" class="filter-row">
          <select name="member" class="filter-sel">
            <option value="">Semua Member</option>
            <?php mysqli_data_seek($members,0); while($m=mysqli_fetch_assoc($members)): ?>
            <option value="<?= $m['id_member'] ?>" <?= $filter_member==$m['id_member']?'selected':'' ?>>
              <?= htmlspecialchars($m['nama']) ?> (<?= $m['poin'] ?> ⭐)
            </option>
            <?php endwhile; ?>
          </select>
          <button type="submit" class="btn-filter">Filter</button>
          <?php if($filter_member): ?><a href="riwayat_poin.php" class="btn-filter">Reset</a><?php endif; ?>
        </form>
      </div>
      <div style="overflow-x:auto;">
      <table>
        <thead><tr><th>Tgl</th><th>Member</th><th>Jenis</th><th>Poin</th><th>Keterangan</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php $rows=mysqli_num_rows($query); if($rows>0): while($d=mysqli_fetch_assoc($query)): ?>
        <tr>
          <td style="font-size:.78em;color:rgba(255,255,255,.5);white-space:nowrap;"><?= date('d M Y H:i',strtotime($d['created_at'])) ?></td>
          <td class="td-nama"><?= htmlspecialchars($d['nama']??'—') ?></td>
          <td><?php if($d['jenis']==='Masuk'): ?><span class="badge-masuk">↑ Masuk</span>
              <?php else: ?><span class="badge-keluar">↓ Keluar</span><?php endif; ?></td>
          <td><span class="poin-val"><?= $d['jenis']==='Masuk'?'+':'-' ?><?= number_format($d['poin']) ?> ⭐</span></td>
          <td style="color:rgba(255,255,255,.6);font-size:.83em;"><?= htmlspecialchars($d['keterangan']??'—') ?></td>
          <td><a href="?hapus=<?= $d['id_riwayat'] ?>&<?= $filter_member?"member=$filter_member":'' ?>"
               class="btn-hapus-sm" onclick="return confirm('Hapus entri ini? Poin member akan dikembalikan.')">🗑️</a></td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="6"><div class="empty-state">Belum ada riwayat poin</div></td></tr>
        <?php endif; ?>
        </tbody>
      </table>
      </div>
    </div>

    <!-- ADD FORM -->
    <div class="form-panel">
      <h3>➕ Tambah Poin Manual</h3>
      <form method="POST">
        <div class="form-group">
          <label>Member</label>
          <select name="id_member" class="inp" required>
            <option value="">-- Pilih Member --</option>
            <?php mysqli_data_seek($members,0); while($m=mysqli_fetch_assoc($members)): ?>
            <option value="<?= $m['id_member'] ?>"><?= htmlspecialchars($m['nama']) ?> (<?= $m['poin'] ?> ⭐)</option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Jenis Transaksi</label>
          <div class="jenis-row">
            <div>
              <input type="radio" name="jenis" id="jMasuk" value="Masuk" class="jenis-opt jenis-masuk" checked>
              <label for="jMasuk" class="jenis-label jenis-label-masuk">⬆️ Masuk</label>
            </div>
            <div>
              <input type="radio" name="jenis" id="jKeluar" value="Keluar" class="jenis-opt jenis-keluar">
              <label for="jKeluar" class="jenis-label jenis-label-keluar">⬇️ Keluar</label>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label>Jumlah Poin</label>
          <input type="number" name="poin" placeholder="0" min="1" required>
        </div>
        <div class="form-group">
          <label>Keterangan</label>
          <input type="text" name="keterangan" placeholder="Contoh: Pembelian Americano x2">
        </div>
        <button type="submit" name="tambah_poin" class="btn-submit">⭐ Simpan Poin</button>
      </form>
    </div>
  </div>
</div>

<script>
(function(){
  const hero=document.getElementById('pageHero');
  const colors=['#D4AF37','#FFE4B5','#ee2a7b','#fff'];
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
