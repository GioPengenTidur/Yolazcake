<?php
session_start();
require_once __DIR__.'/../config/staff_guard.php';
require_staff_login();
include '../config/koneksi.php';
include '../config/upload_helper.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM menu_highlight_foto WHERE id_foto = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
if(!$data){ header("Location: data_menu_foto.php"); exit(); }

$labelSection = ['highlight'=>'Highlights Menu','unggulan'=>'Produk Unggulan'];

$error = '';
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!isset($_FILES['foto']) || $_FILES['foto']['error'] !== 0){
        $error = "Silakan pilih foto baru untuk diupload!";
    } else {
        $upload = upload_gambar($_FILES['foto'], '../assets/img/menu_highlight/');

        if(!$upload['success']){
            $error = $upload['error'];
        } else {
            // Hapus foto lama HANYA jika sebelumnya upload kustom (bukan foto default bawaan produk)
            $fotoLama = $data['foto_path'];
            if(strpos($fotoLama, 'assets/img/menu_highlight/') === 0){
                $pathLama = "../".$fotoLama;
                if(file_exists($pathLama)) unlink($pathLama);
            }

            $fotoPathBaru = "assets/img/menu_highlight/".$upload['filename'];
            $stmt2 = $conn->prepare("UPDATE menu_highlight_foto SET foto_path = ? WHERE id_foto = ?");
            $stmt2->bind_param("si", $fotoPathBaru, $id);
            $stmt2->execute();

            include 'success_overlay.php';
            tampilkan_sukses([
                'proses_judul' => 'Memperbarui Foto Menu…',
                'proses_sub'   => 'Sedang menyimpan perubahan foto pada halaman Menu',
                'sukses_judul' => 'Foto Berhasil Diperbarui!',
                'sukses_sub'   => 'Foto "'.htmlspecialchars($data['nama_kartu']).'" ('.htmlspecialchars($data['label_slide'] ?? 'Slide').') kini tampil di halaman Menu',
                'redirect'     => 'data_menu_foto.php',
                'tombol_label' => 'Lanjutkan ke Data Foto Menu',
            ]);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="../assets/css/lucide-icons.css">
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Foto Menu – YOLAZCAKE</title>
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
    .hero-inner h1{font-family:'Playfair Display',serif;font-size:2.2em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,#D4AF37 60%,#FFE4B5 80%);
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
    .wrapper{position:relative;z-index:1;max-width:560px;margin:40px auto;padding:0 20px 80px;}
    .info-row{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px;}
    .info-chip{padding:6px 14px;border-radius:999px;font-size:.72em;font-weight:700;letter-spacing:.5px;
      text-transform:uppercase;background:rgba(212,175,55,.14);border:1px solid rgba(212,175,55,.35);color:#D4AF37;}
    .preview{width:100%;max-height:260px;object-fit:cover;border-radius:14px;margin-bottom:18px;
      border:1px solid rgba(255,255,255,.15);}
    .form-card{background:rgba(255,255,255,.05);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.1);
      border-radius:24px;padding:36px;position:relative;overflow:hidden;animation:cardReveal .7s forwards;}
    .form-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,#D4AF37,#8A2BE2,#D4AF37);background-size:200%;animation:goldSlide 4s linear infinite;}
    @keyframes goldSlide{0%{background-position:0;}100%{background-position:200%;}}
    @keyframes cardReveal{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
    .form-group{margin-bottom:22px;}
    label{display:block;font-size:.78em;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:rgba(212,175,55,.9);margin-bottom:8px;}
    input[type=file]{width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);
      color:#fff;padding:12px 16px;border-radius:12px;font-family:'Inter',sans-serif;font-size:.9em;
      transition:border-color .3s,box-shadow .3s;outline:none;cursor:pointer;}
    input[type=file]:focus{border-color:#D4AF37;box-shadow:0 0 0 3px rgba(212,175,55,.12);}
    .hint{font-size:.75em;color:rgba(255,255,255,.4);margin-top:6px;}
    .btn-submit{width:100%;padding:14px;background:linear-gradient(135deg,#D4AF37 0%,#b8860b 50%,#D4AF37 100%);
      background-size:200%;animation:goldSlide 3s linear infinite;color:#1e0e3a;font-size:.9em;font-weight:700;
      letter-spacing:2px;text-transform:uppercase;border:none;border-radius:12px;cursor:pointer;
      transition:transform .25s,box-shadow .3s;margin-top:8px;}
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
    <p class="hero-eyebrow"><i data-lucide="sparkle" class="lucide-ic"></i> Admin Panel <i data-lucide="sparkle" class="lucide-ic"></i></p>
    <h1>Ganti Foto Menu</h1>
  </div>
</div>
<div class="wrapper">
  <a href="data_menu_foto.php" class="btn-back"><i data-lucide="arrow-left" class="lucide-ic"></i> Kembali</a>
  <div class="info-row">
    <span class="info-chip"><?= htmlspecialchars($labelSection[$data['section']] ?? $data['section']) ?></span>
    <span class="info-chip"><?= htmlspecialchars($data['nama_kartu']) ?></span>
    <span class="info-chip"><?= htmlspecialchars($data['label_slide'] ?? ('Slide '.($data['slide_index']+1))) ?></span>
  </div>
  <?php if($error): ?><div class="alert-err"><i data-lucide="alert-triangle" class="lucide-ic"></i> <?= $error ?></div><?php endif; ?>
  <div class="form-card">
    <img class="preview" src="../<?= htmlspecialchars($data['foto_path']) ?>" alt="">
    <form method="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label>Upload Foto Baru</label>
        <input type="file" name="foto" accept="image/*" required>
        <p class="hint">Foto ini akan menggantikan gambar yang tampil pada kartu «<?= htmlspecialchars($data['nama_kartu']) ?>» di halaman Menu.</p>
      </div>
      <button type="submit" class="btn-submit"><i data-lucide="save" class="lucide-ic"></i> Simpan Perubahan</button>
    </form>
  </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide){lucide.createIcons();}</script>
</body>
</html>
