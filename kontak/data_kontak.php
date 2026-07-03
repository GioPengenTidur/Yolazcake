<?php
session_start();
if(!isset($_SESSION['username'])){ header("Location: ../auth/login.php"); exit(); }
include '../config/koneksi.php';

// Handle hapus
if(isset($_GET['hapus'])){
    $id=(int)$_GET['hapus'];
    $k = mysqli_fetch_assoc(mysqli_query($conn,"SELECT nama FROM kontak WHERE id_kontak=$id"));
    mysqli_query($conn,"DELETE FROM kontak WHERE id_kontak=$id");

    include 'success_overlay.php';
    tampilkan_sukses([
        'proses_judul' => 'Menghapus Pesan…',
        'proses_sub'   => 'Sedang memproses penghapusan pesan kontak',
        'sukses_judul' => 'Pesan Berhasil Dihapus!',
        'sukses_sub'   => 'Pesan dari "'.htmlspecialchars($k['nama'] ?? 'pengunjung').'" telah dihapus',
        'redirect'     => 'data_kontak.php',
        'tombol_label' => 'Lanjutkan ke Pesan Kontak',
    ]);
    exit;
}

// Handle ubah status
if(isset($_GET['ubah']) && isset($_GET['status'])){
    $id=(int)$_GET['ubah'];
    $st=mysqli_real_escape_string($conn,$_GET['status']);
    mysqli_query($conn,"UPDATE kontak SET status='$st' WHERE id_kontak=$id");

    include 'success_overlay.php';
    tampilkan_sukses([
        'proses_judul' => 'Memperbarui Status…',
        'proses_sub'   => 'Sedang menyimpan perubahan status pesan',
        'sukses_judul' => 'Status Berhasil Diperbarui!',
        'sukses_sub'   => 'Pesan kini berstatus "'.htmlspecialchars($st).'"',
        'redirect'     => 'data_kontak.php',
        'tombol_label' => 'Lanjutkan ke Pesan Kontak',
    ]);
    exit;
}

// Handle detail modal
$detail = null;
if(isset($_GET['baca'])){
    $id=(int)$_GET['baca'];
    $detail=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM kontak WHERE id_kontak=$id"));
    // Otomatis mark as read
    if($detail && $detail['status']==='Belum Dibaca')
        mysqli_query($conn,"UPDATE kontak SET status='Sudah Dibaca' WHERE id_kontak=$id");
}

$filter = mysqli_real_escape_string($conn, $_GET['filter'] ?? '');
$where = $filter ? "WHERE status='$filter'" : '';
$query = mysqli_query($conn,"SELECT * FROM kontak $where ORDER BY created_at DESC");
$stats = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total,
            SUM(status='Belum Dibaca') as belum,
            SUM(status='Sudah Dibaca') as sudah,
            SUM(status='Dibalas') as dibalas FROM kontak"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pesan Kontak – YOLAZCAKE</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{min-height:100vh;font-family:'Inter',sans-serif;
      background:linear-gradient(160deg,#1e0e3a 0%,#2d1560 50%,#1a0a2e 100%);color:#fff;overflow-x:hidden;}
    body::before{content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
      background:radial-gradient(ellipse at 20% 30%,rgba(212,175,55,.10) 0%,transparent 55%),
                 radial-gradient(ellipse at 80% 70%,rgba(232,160,191,.10) 0%,transparent 55%);}
    .page-hero{position:relative;height:240px;display:flex;flex-direction:column;align-items:center;justify-content:center;overflow:hidden;
      background:linear-gradient(135deg,#0a1f3a 0%,#1a4a2e 50%,#0a1f3a 100%);z-index:1;}
    .page-hero::before{content:'';position:absolute;inset:0;
      background:radial-gradient(ellipse at 30% 50%,rgba(212,175,55,.18) 0%,transparent 60%),
                 radial-gradient(ellipse at 75% 40%,rgba(16,185,129,.12) 0%,transparent 55%);
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
      font-size:.82em;font-weight:600;border-radius:999px;text-decoration:none;transition:transform .25s,background .3s;margin-bottom:24px;}
    .btn-back:hover{transform:translateX(-3px);background:rgba(212,175,55,.2);}

    .alert{padding:14px 20px;border-radius:12px;margin-bottom:20px;font-size:.88em;display:flex;align-items:center;gap:10px;}
    .alert-success{background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.3);color:#6ee7b7;}
    @keyframes cardReveal{from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:translateY(0);}}

    .stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;opacity:0;animation:cardReveal .7s forwards .5s;}
    .stat-card{background:rgba(255,255,255,.06);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.1);
      border-radius:18px;padding:18px 20px;display:flex;align-items:center;gap:12px;
      transition:border-color .35s,box-shadow .35s,transform .3s;cursor:default;}
    .stat-card:hover{border-color:rgba(212,175,55,.35);box-shadow:0 0 24px rgba(212,175,55,.2);transform:translateY(-3px);}
    .stat-card::before{content:'';display:block;width:3px;height:36px;border-radius:999px;
      background:linear-gradient(to bottom,#D4AF37,#b8860b);flex-shrink:0;}
    .stat-icon{font-size:1.4em;}.stat-val{font-family:'Playfair Display',serif;font-size:1.6em;font-weight:700;color:#D4AF37;line-height:1;}
    .stat-lbl{font-size:.72em;color:rgba(255,255,255,.5);margin-top:2px;}

    /* FILTER TABS */
    .filter-tabs{display:flex;gap:10px;margin-bottom:22px;flex-wrap:wrap;opacity:0;animation:cardReveal .7s forwards .65s;}
    .filter-tab{padding:8px 20px;border-radius:999px;border:1px solid rgba(255,255,255,.15);
      color:rgba(255,255,255,.6);font-size:.8em;font-weight:600;text-decoration:none;transition:all .25s;}
    .filter-tab:hover{border-color:#D4AF37;color:#D4AF37;}
    .filter-tab.active{background:rgba(212,175,55,.15);border-color:rgba(212,175,55,.4);color:#D4AF37;}
    .tab-badge{background:#D4AF37;color:#1e0e3a;border-radius:999px;padding:1px 7px;font-size:.75em;margin-left:5px;}

    /* TABLE */
    .table-card{background:rgba(255,255,255,.05);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.1);
      border-radius:24px;overflow:hidden;position:relative;opacity:0;animation:cardReveal .8s forwards .8s;}
    .table-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);background-size:200%;animation:goldSlide 4s linear infinite;}
    @keyframes goldSlide{0%{background-position:0;}100%{background-position:200%;}}
    table{width:100%;border-collapse:collapse;}
    thead tr{background:rgba(212,175,55,.12);border-bottom:1px solid rgba(212,175,55,.25);}
    thead th{padding:14px 18px;font-size:.7em;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:rgba(212,175,55,.9);text-align:left;}
    tbody tr{border-bottom:1px solid rgba(255,255,255,.06);transition:background .25s;}
    tbody tr.unread{background:rgba(212,175,55,.04);}
    tbody tr:hover{background:rgba(212,175,55,.08);}
    tbody td{padding:14px 18px;font-size:.87em;color:rgba(255,255,255,.8);vertical-align:middle;}
    .td-nama{font-weight:700;color:#fff;}
    .td-subjek{color:rgba(255,255,255,.7);}
    .td-preview{font-size:.8em;color:rgba(255,255,255,.45);overflow:hidden;white-space:nowrap;max-width:200px;text-overflow:ellipsis;}
    .unread-dot{display:inline-block;width:8px;height:8px;border-radius:50%;background:#D4AF37;margin-right:6px;animation:pulseGold 1.5s ease-in-out infinite;}
    @keyframes pulseGold{0%,100%{opacity:1;}50%{opacity:.3;}}
    .s-badge{display:inline-block;padding:3px 10px;border-radius:999px;font-size:.73em;font-weight:700;letter-spacing:.5px;}
    .s-belum{background:rgba(212,175,55,.15);border:1px solid rgba(212,175,55,.35);color:#D4AF37;}
    .s-sudah{background:rgba(99,102,241,.12);border:1px solid rgba(99,102,241,.3);color:#a5b4fc;}
    .s-dibalas{background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.3);color:#6ee7b7;}
    .action-cell{display:flex;gap:6px;flex-wrap:wrap;}
    .btn-act{display:inline-flex;align-items:center;gap:4px;padding:5px 11px;border-radius:7px;font-size:.72em;font-weight:600;text-decoration:none;border:1px solid transparent;cursor:pointer;transition:transform .2s,box-shadow .2s,background .2s;}
    .btn-act:hover{transform:translateY(-2px);}
    .btn-baca{background:rgba(99,102,241,.15);border-color:rgba(99,102,241,.35);color:#a5b4fc;}
    .btn-baca:hover{background:rgba(99,102,241,.28);}
    .btn-balas{background:rgba(16,185,129,.12);border-color:rgba(16,185,129,.3);color:#6ee7b7;}
    .btn-balas:hover{background:rgba(16,185,129,.25);}
    .btn-hapus{background:rgba(239,68,68,.12);border-color:rgba(239,68,68,.3);color:#fca5a5;}
    .btn-hapus:hover{background:rgba(239,68,68,.25);}
    .empty-state{text-align:center;padding:60px;color:rgba(255,255,255,.4);}

    /* MODAL */
    .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.65);backdrop-filter:blur(10px);
      z-index:900;display:none;align-items:center;justify-content:center;padding:20px;}
    .modal-overlay.open{display:flex;}
    .modal{background:linear-gradient(160deg,#1e0e3a,#2d1560);border:1px solid rgba(212,175,55,.25);
      border-radius:24px;padding:36px;width:90%;max-width:560px;position:relative;animation:cardReveal .4s forwards;}
    .modal::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);border-radius:24px 24px 0 0;}
    .modal h3{font-family:'Playfair Display',serif;color:#D4AF37;font-size:1.4em;margin-bottom:4px;}
    .modal-close{position:absolute;top:14px;right:16px;background:none;border:none;color:rgba(255,255,255,.5);font-size:1.4em;cursor:pointer;}
    .modal-close:hover{color:#fff;}
    .meta-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:18px 0;}
    .meta-item label{font-size:.68em;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:rgba(212,175,55,.7);}
    .meta-item p{font-size:.9em;color:rgba(255,255,255,.85);margin-top:3px;}
    .pesan-box{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:14px;padding:18px;
      color:rgba(255,255,255,.85);font-size:.9em;line-height:1.7;margin-top:16px;white-space:pre-wrap;}
    .modal-actions{display:flex;gap:10px;margin-top:22px;flex-wrap:wrap;}
    .btn-modal{padding:10px 20px;border-radius:10px;font-size:.82em;font-weight:700;text-decoration:none;border:1px solid transparent;cursor:pointer;transition:all .25s;}
    .btn-modal-balas{background:rgba(16,185,129,.15);border-color:rgba(16,185,129,.35);color:#6ee7b7;}
    .btn-modal-balas:hover{background:rgba(16,185,129,.3);}
    .btn-modal-hapus{background:rgba(239,68,68,.12);border-color:rgba(239,68,68,.3);color:#fca5a5;}
    .btn-modal-hapus:hover{background:rgba(239,68,68,.25);}

    @media(max-width:768px){.stats-row{grid-template-columns:repeat(2,1fr);}.hero-inner h1{font-size:2em;}.page-wrapper{padding:24px 16px 60px;}}
  </style>
</head>
<body>
<div id="particles"></div>
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow">✦ YOLAZCAKE Sintang ✦</p>
    <h1>Pesan Kontak</h1>
    <p class="hero-sub">Pesan masuk dari pengunjung website</p>
    <div class="hero-divider"><span></span><span class="diamond">✦ ✦ ✦</span><span></span></div>
  </div>
</div>

<div class="page-wrapper">
  <a href="../dashboard.php" class="btn-back">← Dashboard</a>

  <div class="stats-row">
    <div class="stat-card"><span class="stat-icon">✉️</span><div><div class="stat-val"><?= $stats['total'] ?></div><div class="stat-lbl">Total Pesan</div></div></div>
    <div class="stat-card"><span class="stat-icon">🟡</span><div><div class="stat-val"><?= $stats['belum'] ?></div><div class="stat-lbl">Belum Dibaca</div></div></div>
    <div class="stat-card"><span class="stat-icon">🟣</span><div><div class="stat-val"><?= $stats['sudah'] ?></div><div class="stat-lbl">Sudah Dibaca</div></div></div>
    <div class="stat-card"><span class="stat-icon">✅</span><div><div class="stat-val"><?= $stats['dibalas'] ?></div><div class="stat-lbl">Dibalas</div></div></div>
  </div>

  <div class="filter-tabs">
    <a href="data_kontak.php" class="filter-tab <?= !$filter?'active':'' ?>">Semua</a>
    <a href="?filter=Belum Dibaca" class="filter-tab <?= $filter==='Belum Dibaca'?'active':'' ?>">🟡 Belum Dibaca <span class="tab-badge"><?= $stats['belum'] ?></span></a>
    <a href="?filter=Sudah Dibaca" class="filter-tab <?= $filter==='Sudah Dibaca'?'active':'' ?>">🟣 Sudah Dibaca</a>
    <a href="?filter=Dibalas" class="filter-tab <?= $filter==='Dibalas'?'active':'' ?>">✅ Dibalas</a>
  </div>

  <div class="table-card">
    <div style="overflow-x:auto;">
    <table>
      <thead><tr><th>Tgl</th><th>Nama</th><th>Kontak</th><th>Subjek</th><th>Preview</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php $rows=mysqli_num_rows($query); if($rows>0): while($d=mysqli_fetch_assoc($query)): ?>
      <tr class="<?= $d['status']==='Belum Dibaca'?'unread':'' ?>">
        <td style="font-size:.75em;color:rgba(255,255,255,.5);white-space:nowrap;"><?= date('d M Y',strtotime($d['created_at'])) ?></td>
        <td class="td-nama">
          <?= $d['status']==='Belum Dibaca'?'<span class="unread-dot"></span>':'' ?>
          <?= htmlspecialchars($d['nama']) ?>
        </td>
        <td style="font-size:.8em;color:rgba(255,255,255,.55);">
          <?= $d['email']?htmlspecialchars($d['email']):'—' ?><br>
          <?= $d['no_hp']?htmlspecialchars($d['no_hp']):'—' ?>
        </td>
        <td class="td-subjek"><?= $d['subjek']?htmlspecialchars($d['subjek']):'—' ?></td>
        <td class="td-preview"><?= htmlspecialchars($d['pesan']) ?></td>
        <td>
          <?php $sc=['Belum Dibaca'=>'s-belum','Sudah Dibaca'=>'s-sudah','Dibalas'=>'s-dibalas'][$d['status']]??''; ?>
          <span class="s-badge <?= $sc ?>"><?= $d['status'] ?></span>
        </td>
        <td>
          <div class="action-cell">
            <button class="btn-act btn-baca" onclick="openDetail(<?= htmlspecialchars(json_encode($d)) ?>)">👁️ Baca</button>
            <?php if($d['status']!=='Dibalas'): ?>
            <a href="?ubah=<?= $d['id_kontak'] ?>&status=Dibalas" class="btn-act btn-balas">✉️ Balas</a>
            <?php endif; ?>
            <a href="?hapus=<?= $d['id_kontak'] ?>" class="btn-act btn-hapus"
               onclick="return confirm('Hapus pesan ini?')">🗑️</a>
          </div>
        </td>
      </tr>
      <?php endwhile; else: ?>
      <tr><td colspan="7"><div class="empty-state">📭 Tidak ada pesan</div></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>

<!-- DETAIL MODAL -->
<div class="modal-overlay" id="detailModal">
  <div class="modal">
    <button class="modal-close" onclick="closeDetail()">✕</button>
    <h3 id="mSubjek">Subjek</h3>
    <div class="meta-row">
      <div class="meta-item"><label>Nama</label><p id="mNama"></p></div>
      <div class="meta-item"><label>Tanggal</label><p id="mTgl"></p></div>
      <div class="meta-item"><label>Email</label><p id="mEmail"></p></div>
      <div class="meta-item"><label>No HP</label><p id="mHp"></p></div>
    </div>
    <div class="pesan-box" id="mPesan"></div>
    <div class="modal-actions">
      <a id="mBtnBalas" href="#" class="btn-modal btn-modal-balas">✉️ Tandai Dibalas</a>
      <a id="mBtnHapus" href="#" class="btn-modal btn-modal-hapus" onclick="return confirm('Hapus pesan ini?')">🗑️ Hapus</a>
    </div>
  </div>
</div>

<script>
function openDetail(d){
  document.getElementById('mSubjek').textContent = d.subjek || '(Tanpa Subjek)';
  document.getElementById('mNama').textContent   = d.nama;
  document.getElementById('mEmail').textContent  = d.email || '—';
  document.getElementById('mHp').textContent     = d.no_hp || '—';
  document.getElementById('mPesan').textContent  = d.pesan;
  document.getElementById('mTgl').textContent    = d.created_at;
  document.getElementById('mBtnBalas').href      = '?ubah='+d.id_kontak+'&status=Dibalas';
  document.getElementById('mBtnHapus').href      = '?hapus='+d.id_kontak;
  document.getElementById('detailModal').classList.add('open');
}
function closeDetail(){ document.getElementById('detailModal').classList.remove('open'); }
document.getElementById('detailModal').addEventListener('click',function(e){if(e.target===this)closeDetail();});

(function(){
  const hero=document.getElementById('pageHero');
  const colors=['#D4AF37','#FFE4B5','#6ee7b7','#fff'];
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
