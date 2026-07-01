<?php
session_start();
if(!isset($_SESSION['username'])){ header("Location: ../auth/login.php"); exit(); }
include '../config/koneksi.php';

$error = '';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $judul     = mysqli_real_escape_string($conn, trim($_POST['judul']));
    $deskripsi = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));
    $kategori  = mysqli_real_escape_string($conn, $_POST['kategori']);

    if(!$judul){
        $error = "Judul foto wajib diisi!";
    } elseif(!isset($_FILES['foto']) || $_FILES['foto']['error'] !== 0){
        $error = "Foto wajib diupload!";
    } else {
        $namaFoto = time()."_".basename($_FILES['foto']['name']);
        $tmpFoto  = $_FILES['foto']['tmp_name'];
        $tujuan   = "../assets/img/galeri/".$namaFoto;

        if(!is_dir("../assets/img/galeri")){
            mkdir("../assets/img/galeri", 0777, true);
        }

        if(move_uploaded_file($tmpFoto, $tujuan)){
            mysqli_query($conn,
                "INSERT INTO galeri (judul,deskripsi,kategori,foto) VALUES ('$judul','$deskripsi','$kategori','$namaFoto')");
            header("Location: data_galeri.php?msg=tambah"); exit();
        } else {
            $error = "Gagal mengupload foto. Coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Foto Galeri – YOLAZCAKE</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{min-height:100vh;font-family:'Inter',sans-serif;
      background:linear-gradient(160deg,#1e0e3a 0%,#2d1560 50%,#1a0a2e 100%);color:#fff;overflow-x:hidden;}
    body::before{content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
      background:radial-gradient(ellipse at 20% 30%,rgba(212,175,55,.10) 0%,transparent 55%),
                 radial-gradient(ellipse at 80% 70%,rgba(138,43,226,.10) 0%,transparent 55%);}
    .page-hero{position:relative;height:200px;display:flex;align-items:center;justify-content:center;
      background:linear-gradient(135deg,#0d1b2a 0%,#1a3a5c 50%,#0d1b2a 100%);z-index:1;}
    .page-hero::before{content:'';position:absolute;inset:0;
      background:radial-gradient(ellipse at 50% 50%,rgba(212,175,55,.15) 0%,transparent 60%);
      animation:pulse 5s ease-in-out infinite alternate;}
    @keyframes pulse{0%{opacity:.6;}100%{opacity:1;}}
    .hero-inner{position:relative;z-index:2;text-align:center;}
    .hero-eyebrow{font-size:.72em;font-weight:500;letter-spacing:5px;text-transform:uppercase;color:#D4AF37;margin-bottom:8px;}
    .hero-inner h1{font-family:'Playfair Display',serif;font-size:2.4em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,#D4AF37 60%,#FFE4B5 80%);
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
    .wrapper{position:relative;z-index:1;max-width:560px;margin:40px auto;padding:0 20px 80px;}
    .form-card{background:rgba(255,255,255,.05);backdrop-filter:blur(20px);
      border:1px solid rgba(255,255,255,.1);border-radius:24px;padding:36px;
      position:relative;overflow:hidden;animation:cardReveal .7s forwards;}
    .form-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,#D4AF37,#8A2BE2,#D4AF37);background-size:200%;animation:goldSlide 4s linear infinite;}
    @keyframes goldSlide{0%{background-position:0;}100%{background-position:200%;}}
    @keyframes cardReveal{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
    .form-group{margin-bottom:22px;}
    label{display:block;font-size:.78em;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;
      color:rgba(212,175,55,.9);margin-bottom:8px;}
    input,select,textarea{width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);
      color:#fff;padding:12px 16px;border-radius:12px;font-family:'Inter',sans-serif;font-size:.9em;
      transition:border-color .3s,box-shadow .3s;outline:none;}
    input:focus,select:focus,textarea:focus{border-color:#D4AF37;box-shadow:0 0 0 3px rgba(212,175,55,.12);}
    input::placeholder,textarea::placeholder{color:rgba(255,255,255,.3);}
    select option{background:#1e0e3a;color:#fff;}
    textarea{resize:vertical;min-height:80px;}
    input[type=file]{padding:10px;cursor:pointer;}
    .btn-submit{width:100%;padding:14px;background:linear-gradient(135deg,#D4AF37 0%,#b8860b 50%,#D4AF37 100%);
      background-size:200%;animation:goldSlide 3s linear infinite;color:#1e0e3a;font-family:'Inter',sans-serif;
      font-size:.9em;font-weight:700;letter-spacing:2px;text-transform:uppercase;border:none;border-radius:12px;
      cursor:pointer;transition:transform .25s,box-shadow .3s;margin-top:8px;}
    .btn-submit:hover{transform:translateY(-2px);box-shadow:0 10px 30px rgba(212,175,55,.4);}
    .btn-back{display:inline-flex;align-items:center;gap:8px;padding:10px 22px;margin-bottom:24px;
      background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.3);color:#D4AF37;
      font-size:.82em;font-weight:600;border-radius:999px;text-decoration:none;transition:transform .25s,background .3s;}
    .btn-back:hover{transform:translateX(-3px);background:rgba(212,175,55,.2);}
    .alert-err{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);color:#fca5a5;
      padding:12px 16px;border-radius:10px;font-size:.85em;margin-bottom:20px;}
  </style>
</head>
<body>
<div class="page-hero">
  <div class="hero-inner">
    <p class="hero-eyebrow">✦ Admin Panel ✦</p>
    <h1>Tambah Foto Galeri</h1>
  </div>
</div>
<div class="wrapper">
  <a href="data_galeri.php" class="btn-back">← Kembali</a>
  <?php if($error): ?><div class="alert-err">⚠️ <?= $error ?></div><?php endif; ?>
  <div class="form-card">
    <form method="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label>Judul Foto</label>
        <input type="text" name="judul" placeholder="Contoh: Interior Cafe" maxlength="100" required>
      </div>
      <div class="form-group">
        <label>Deskripsi</label>
        <textarea name="deskripsi" placeholder="Contoh: Pemandangan di dalam cafe"></textarea>
      </div>
      <div class="form-group">
        <label>Kategori</label>
        <select name="kategori" required>
          <option value="interior">🏠 Interior</option>
          <option value="kue">🍰 Kue & Pastry</option>
          <option value="coffee">☕ Coffee</option>
          <option value="boutique">👗 Boutique</option>
        </select>
      </div>
      <div class="form-group">
        <label>Upload Foto</label>
        <input type="file" name="foto" accept="image/*" required>
      </div>
      <button type="submit" class="btn-submit">🖼️ Simpan Foto</button>
    </form>
  </div>
</div>
</body>
</html>
