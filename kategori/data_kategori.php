<?php
session_start();
require_once __DIR__.'/../config/staff_guard.php';
require_staff_login();
include '../config/koneksi.php';

// Handle hapus
if(isset($_GET['hapus'])){
    $id = (int)$_GET['hapus'];

    $stmt = $conn->prepare("SELECT nama_kategori FROM kategori WHERE id_kategori=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $k = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Lepas produk dari kategori ini dulu
    $stmt = $conn->prepare("UPDATE produk SET id_kategori=NULL WHERE id_kategori=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM kategori WHERE id_kategori=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    include 'success_overlay.php';
    tampilkan_sukses([
        'proses_judul' => 'Menghapus Kategori…',
        'proses_sub'   => 'Sedang memproses penghapusan data kategori',
        'sukses_judul' => 'Kategori Berhasil Dihapus!',
        'sukses_sub'   => '"'.htmlspecialchars($k['nama_kategori'] ?? 'Kategori').'" telah dihapus dari data',
        'redirect'     => 'data_kategori.php',
        'tombol_label' => 'Lanjutkan ke Data Kategori',
    ]);
    exit;
}

// Handle tambah inline
if(isset($_POST['tambah'])){
    $nama = trim($_POST['nama_kategori']);
    $desk = trim($_POST['deskripsi']);
    $icon = trim($_POST['icon']);

    if($nama){
        $stmt = $conn->prepare("INSERT INTO kategori (nama_kategori,deskripsi,icon) VALUES (?,?,?)");
        $stmt->bind_param("sss", $nama, $desk, $icon);
        $stmt->execute();
        $stmt->close();
    }

    include 'success_overlay.php';
    tampilkan_sukses([
        'proses_judul' => 'Menyimpan Kategori…',
        'proses_sub'   => 'Sedang menambahkan kategori baru',
        'sukses_judul' => 'Kategori Berhasil Ditambahkan!',
        'sukses_sub'   => '"'.htmlspecialchars($nama).'" kini tersedia sebagai kategori produk',
        'redirect'     => 'data_kategori.php',
        'tombol_label' => 'Lanjutkan ke Data Kategori',
    ]);
    exit;
}

// Handle edit inline
if(isset($_POST['edit'])){
    $id   = (int)$_POST['id_kategori'];
    $nama = trim($_POST['nama_kategori']);
    $desk = trim($_POST['deskripsi']);
    $icon = trim($_POST['icon']);

    if($nama){
        $stmt = $conn->prepare("UPDATE kategori SET nama_kategori=?,deskripsi=?,icon=? WHERE id_kategori=?");
        $stmt->bind_param("sssi", $nama, $desk, $icon, $id);
        $stmt->execute();
        $stmt->close();
    }

    include 'success_overlay.php';
    tampilkan_sukses([
        'proses_judul' => 'Memperbarui Kategori…',
        'proses_sub'   => 'Sedang menyimpan perubahan data kategori',
        'sukses_judul' => 'Kategori Berhasil Diperbarui!',
        'sukses_sub'   => '"'.htmlspecialchars($nama).'" telah diperbarui',
        'redirect'     => 'data_kategori.php',
        'tombol_label' => 'Lanjutkan ke Data Kategori',
    ]);
    exit;
}

$query = mysqli_query($conn,"SELECT k.*, COUNT(p.id_produk) as jml_produk FROM kategori k LEFT JOIN produk p ON p.id_kategori=k.id_kategori GROUP BY k.id_kategori ORDER BY k.nama_kategori ASC");
$total_kat = mysqli_num_rows($query);
mysqli_data_seek($query,0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kategori Produk – YOLAZCAKE</title>
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

    .page-wrapper{position:relative;z-index:1;padding:36px 28px 80px;max-width:1000px;margin:0 auto;}
    .btn-back{display:inline-flex;align-items:center;gap:8px;padding:10px 22px;
      background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.3);color:#D4AF37;
      font-size:.82em;font-weight:600;letter-spacing:1px;border-radius:999px;text-decoration:none;
      transition:transform .25s,background .3s;margin-bottom:24px;}
    .btn-back:hover{transform:translateX(-3px);background:rgba(212,175,55,.2);}

    .alert{padding:14px 20px;border-radius:12px;margin-bottom:20px;font-size:.88em;display:flex;align-items:center;gap:10px;animation:cardReveal .5s forwards;}
    .alert-success{background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.3);color:#6ee7b7;}
    @keyframes cardReveal{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}

    /* GRID LAYOUT */
    .main-grid{display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;}

    /* TABLE CARD */
    .table-card{background:rgba(255,255,255,.05);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.1);
      border-radius:24px;overflow:hidden;position:relative;animation:cardReveal .8s forwards .6s;opacity:0;}
    .table-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);background-size:200%;animation:goldSlide 4s linear infinite;}
    @keyframes goldSlide{0%{background-position:0;}100%{background-position:200%;}}
    .card-header{padding:20px 24px;border-bottom:1px solid rgba(255,255,255,.08);
      display:flex;justify-content:space-between;align-items:center;}
    .card-title{font-family:'Playfair Display',serif;font-size:1.2em;font-weight:700;color:#D4AF37;}
    .badge-count{background:rgba(212,175,55,.15);border:1px solid rgba(212,175,55,.3);
      color:#D4AF37;padding:4px 12px;border-radius:999px;font-size:.78em;font-weight:700;}
    table{width:100%;border-collapse:collapse;}
    thead tr{background:rgba(212,175,55,.12);border-bottom:1px solid rgba(212,175,55,.25);}
    thead th{padding:14px 18px;font-size:.7em;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:rgba(212,175,55,.9);text-align:left;}
    tbody tr{border-bottom:1px solid rgba(255,255,255,.06);transition:background .25s;}
    tbody tr:hover{background:rgba(212,175,55,.06);}
    tbody td{padding:14px 18px;font-size:.88em;color:rgba(255,255,255,.8);vertical-align:middle;}
    .td-icon{font-size:1.6em;text-align:center;}
    .td-nama{font-weight:600;color:#fff;}
    .badge-produk{display:inline-flex;align-items:center;gap:5px;background:rgba(99,102,241,.15);
      border:1px solid rgba(99,102,241,.3);border-radius:999px;padding:3px 10px;font-size:.78em;color:#a5b4fc;}
    .action-cell{display:flex;gap:8px;align-items:center;}
    .btn-act{display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:8px;
      font-size:.72em;font-weight:600;text-decoration:none;border:1px solid transparent;cursor:pointer;
      transition:transform .2s,box-shadow .25s,background .25s;background:none;font-family:'Inter',sans-serif;}
    .btn-act:hover{transform:translateY(-2px);}
    .btn-edit{background:rgba(212,175,55,.18);border-color:rgba(212,175,55,.4);color:#D4AF37;}
    .btn-edit:hover{background:rgba(212,175,55,.32);box-shadow:0 4px 16px rgba(212,175,55,.3);}
    .btn-hapus{background:rgba(239,68,68,.14);border-color:rgba(239,68,68,.35);color:#fca5a5;}
    .btn-hapus:hover{background:rgba(239,68,68,.28);box-shadow:0 4px 16px rgba(239,68,68,.25);}
    .empty-state{text-align:center;padding:50px 20px;color:rgba(255,255,255,.4);}

    /* FORM PANEL */
    .form-panel{background:rgba(255,255,255,.05);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.1);
      border-radius:24px;padding:28px;position:relative;overflow:hidden;animation:cardReveal .8s forwards .8s;opacity:0;}
    .form-panel::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,#8A2BE2,#D4AF37,#8A2BE2);background-size:200%;animation:goldSlide 4s linear infinite;}
    .form-panel h3{font-family:'Playfair Display',serif;font-size:1.2em;color:#D4AF37;margin-bottom:20px;}
    .form-group{margin-bottom:18px;}
    label{display:block;font-size:.75em;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:rgba(212,175,55,.9);margin-bottom:7px;}
    input[type=text],textarea{width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);
      color:#fff;padding:11px 14px;border-radius:10px;font-family:'Inter',sans-serif;font-size:.88em;
      transition:border-color .3s;outline:none;}
    input[type=text]:focus,textarea:focus{border-color:#D4AF37;box-shadow:0 0 0 3px rgba(212,175,55,.12);}
    input[type=text]::placeholder,textarea::placeholder{color:rgba(255,255,255,.3);}
    .icon-row{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;}
    .icon-btn{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);border-radius:8px;
      padding:8px 12px;font-size:1.3em;cursor:pointer;transition:transform .2s,border-color .2s;}
    .icon-btn:hover{transform:scale(1.2);border-color:#D4AF37;}
    .btn-submit{width:100%;padding:12px;background:linear-gradient(135deg,#D4AF37 0%,#b8860b 50%,#D4AF37 100%);
      background-size:200%;animation:goldSlide 3s linear infinite;color:#1e0e3a;font-weight:700;
      font-size:.85em;letter-spacing:2px;text-transform:uppercase;border:none;border-radius:10px;
      cursor:pointer;transition:transform .25s,box-shadow .3s;margin-top:4px;}
    .btn-submit:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(212,175,55,.4);}

    /* Modal edit */
    .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(8px);
      z-index:900;display:none;align-items:center;justify-content:center;}
    .modal-overlay.open{display:flex;}
    .modal{background:linear-gradient(160deg,#1e0e3a,#2d1560);border:1px solid rgba(212,175,55,.25);
      border-radius:24px;padding:32px;width:90%;max-width:440px;position:relative;}
    .modal::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,#D4AF37,#8A2BE2,#D4AF37);border-radius:24px 24px 0 0;}
    .modal h3{font-family:'Playfair Display',serif;color:#D4AF37;margin-bottom:20px;}
    .modal-close{position:absolute;top:14px;right:16px;background:none;border:none;color:rgba(255,255,255,.5);
      font-size:1.3em;cursor:pointer;}
    .modal-close:hover{color:#fff;}

    @media(max-width:768px){.main-grid{grid-template-columns:1fr;}.hero-inner h1{font-size:2em;}.page-wrapper{padding:24px 16px 60px;}}
  </style>
</head>
<body>
<div id="particles"></div>
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow">✦ YOLAZCAKE Sintang ✦</p>
    <h1>Kategori Produk</h1>
    <p class="hero-sub">Kelola pengelompokan menu cafe</p>
    <div class="hero-divider"><span></span><span class="diamond">✦ ✦ ✦</span><span></span></div>
  </div>
</div>

<div class="page-wrapper">
  <a href="../dashboard.php" class="btn-back">← Dashboard</a>

  <div class="main-grid">

    <!-- TABLE -->
    <div class="table-card">
      <div class="card-header">
        <span class="card-title">🏷️ Daftar Kategori</span>
        <span class="badge-count"><?= $total_kat ?> kategori</span>
      </div>
      <table>
        <thead><tr><th>Icon</th><th>Nama Kategori</th><th>Deskripsi</th><th>Produk</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php if($total_kat > 0): while($d = mysqli_fetch_assoc($query)): ?>
        <tr>
          <td class="td-icon"><?= $d['icon'] ?: '🍽️' ?></td>
          <td class="td-nama"><?= htmlspecialchars($d['nama_kategori']) ?></td>
          <td style="color:rgba(255,255,255,.55);font-size:.83em;"><?= $d['deskripsi'] ? htmlspecialchars($d['deskripsi']) : '-' ?></td>
          <td><span class="badge-produk">🍽️ <?= $d['jml_produk'] ?></span></td>
          <td>
            <div class="action-cell">
              <button class="btn-act btn-edit"
                onclick="openEdit(<?= $d['id_kategori'] ?>,'<?= addslashes($d['nama_kategori']) ?>','<?= addslashes($d['deskripsi']) ?>','<?= $d['icon'] ?>')">✏️ Edit</button>
              <a href="?hapus=<?= $d['id_kategori'] ?>" class="btn-act btn-hapus"
                 onclick="return confirm('Hapus kategori <?= htmlspecialchars($d['nama_kategori']) ?>? Produk terkait tidak akan dihapus.')">🗑️</a>
            </div>
          </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="5"><div class="empty-state"><p>Belum ada kategori</p></div></td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- ADD FORM -->
    <div class="form-panel">
      <h3>➕ Tambah Kategori</h3>
      <form method="POST">
        <div class="form-group">
          <label>Nama Kategori</label>
          <input type="text" name="nama_kategori" placeholder="Contoh: Minuman" required>
        </div>
        <div class="form-group">
          <label>Deskripsi</label>
          <textarea name="deskripsi" placeholder="Deskripsi singkat..." rows="3"></textarea>
        </div>
        <div class="form-group">
          <label>Icon Emoji</label>
          <input type="text" name="icon" id="iconInput" placeholder="🍽️" maxlength="5">
          <div class="icon-row">
            <?php foreach(['🥤','🍰','🎂','🍟','☕','🧁','🍜','🥪','🍹','🧃'] as $ic): ?>
            <button type="button" class="icon-btn" onclick="document.getElementById('iconInput').value='<?= $ic ?>'"><?= $ic ?></button>
            <?php endforeach; ?>
          </div>
        </div>
        <button type="submit" name="tambah" class="btn-submit">✅ Simpan Kategori</button>
      </form>
    </div>
  </div>
</div>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="editModal">
  <div class="modal">
    <button class="modal-close" onclick="closeEdit()">✕</button>
    <h3>✏️ Edit Kategori</h3>
    <form method="POST">
      <input type="hidden" name="id_kategori" id="editId">
      <div class="form-group" style="margin-bottom:16px;">
        <label>Nama Kategori</label>
        <input type="text" name="nama_kategori" id="editNama" required>
      </div>
      <div class="form-group" style="margin-bottom:16px;">
        <label>Deskripsi</label>
        <textarea name="deskripsi" id="editDesk" rows="3"></textarea>
      </div>
      <div class="form-group" style="margin-bottom:20px;">
        <label>Icon</label>
        <input type="text" name="icon" id="editIcon" maxlength="5">
      </div>
      <button type="submit" name="edit" class="btn-submit">💾 Simpan</button>
    </form>
  </div>
</div>

<script>
function openEdit(id,nama,desk,icon){
  document.getElementById('editId').value=id;
  document.getElementById('editNama').value=nama;
  document.getElementById('editDesk').value=desk;
  document.getElementById('editIcon').value=icon;
  document.getElementById('editModal').classList.add('open');
}
function closeEdit(){ document.getElementById('editModal').classList.remove('open'); }
document.getElementById('editModal').addEventListener('click',function(e){if(e.target===this)closeEdit();});

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
