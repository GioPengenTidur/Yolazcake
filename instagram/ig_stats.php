<?php
session_start();
require_once __DIR__.'/../config/staff_guard.php';
require_staff_login();
include '../config/koneksi.php';
require_once '../config/ig_stats_helper.php';

$data  = ambil_ig_stats($conn);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $followers = (int) ($_POST['followers'] ?? 0);
    $following = (int) ($_POST['following'] ?? 0);
    $posts     = (int) ($_POST['posts'] ?? 0);

    if ($followers < 0 || $following < 0 || $posts < 0) {
        $error = "Angka tidak boleh negatif!";
    } else {
        $updated_by = $_SESSION['username'] ?? 'admin';
        $stmt = $conn->prepare(
            "INSERT INTO ig_stats (id, followers, following, posts, updated_by)
             VALUES (1, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE followers=VALUES(followers), following=VALUES(following),
                                      posts=VALUES(posts), updated_by=VALUES(updated_by)"
        );
        $stmt->bind_param("iiis", $followers, $following, $posts, $updated_by);
        $stmt->execute();
        $stmt->close();

        include 'success_overlay.php';
        tampilkan_sukses([
            'proses_judul' => 'Memperbarui Statistik Instagram…',
            'proses_sub'   => 'Sedang menyimpan angka followers, following, dan posts',
            'sukses_judul' => 'Statistik Instagram Diperbarui!',
            'sukses_sub'   => 'Angka baru sudah tersimpan dan akan langsung tampil di halaman Contact',
            'redirect'     => 'ig_stats.php',
            'tombol_label' => 'Kembali ke Halaman Ini',
        ]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="../assets/css/lucide-icons.css">
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Statistik Instagram – YOLAZCAKE</title>
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
      background:radial-gradient(ellipse at 50% 50%,rgba(212,175,55,.15) 0%,transparent 60%);}
    .hero-inner{position:relative;z-index:2;text-align:center;}
    .hero-eyebrow{font-size:.72em;font-weight:500;letter-spacing:5px;text-transform:uppercase;color:#D4AF37;margin-bottom:8px;}
    .hero-inner h1{font-family:'Playfair Display',serif;font-size:2.4em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,#D4AF37 60%,#FFE4B5 80%);
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
    .wrapper{position:relative;z-index:1;max-width:560px;margin:40px auto;padding:0 20px 80px;}
    .form-card{background:rgba(255,255,255,.05);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.1);
      border-radius:24px;padding:36px;position:relative;overflow:hidden;animation:cardReveal .7s forwards;}
    .form-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,#D4AF37,#8A2BE2,#D4AF37);background-size:200%;animation:goldSlide 4s linear infinite;}
    @keyframes goldSlide{0%{background-position:0;}100%{background-position:200%;}}
    @keyframes cardReveal{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
    .form-group{margin-bottom:22px;}
    label{display:block;font-size:.78em;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:rgba(212,175,55,.9);margin-bottom:8px;}
    input{width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);
      color:#fff;padding:12px 16px;border-radius:12px;font-family:'Inter',sans-serif;font-size:.9em;
      transition:border-color .3s,box-shadow .3s;outline:none;}
    input:focus{border-color:#D4AF37;box-shadow:0 0 0 3px rgba(212,175,55,.12);}
    .form-row{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;}
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
    .info-note{display:flex;gap:10px;background:rgba(212,175,55,.07);border:1px solid rgba(212,175,55,.2);
      padding:14px 16px;border-radius:12px;font-size:.8em;color:rgba(255,255,255,.75);line-height:1.5;margin-bottom:24px;}
    .info-note i{color:#D4AF37;flex-shrink:0;margin-top:2px;}
    .last-update{font-size:.78em;color:rgba(255,255,255,.5);text-align:center;margin-top:18px;}
    @media(max-width:560px){.form-row{grid-template-columns:1fr;}}
  </style>
</head>
<body>
<div class="page-hero">
  <div class="hero-inner">
    <p class="hero-eyebrow"><i data-lucide="sparkle" class="lucide-ic"></i> Admin Panel <i data-lucide="sparkle" class="lucide-ic"></i></p>
    <h1>Statistik Instagram</h1>
  </div>
</div>
<div class="wrapper">
  <a href="../dashboard.php" class="btn-back"><i data-lucide="arrow-left" class="lucide-ic"></i> Kembali ke Dashboard</a>

  <div class="info-note">
    <i data-lucide="info" class="lucide-ic"></i>
    <span>Angka ini <strong>tidak</strong> tarik data otomatis dari Instagram. Cek akun
      <strong>@yolazcake.stg</strong> asli secara berkala, lalu masukkan angka terbarunya di sini.
      Setelah disimpan, kartu Instagram di halaman Contact langsung ikut berubah.</span>
  </div>

  <?php if ($error): ?><div class="alert-err"><i data-lucide="alert-triangle" class="lucide-ic"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

  <div class="form-card">
    <form method="POST">
      <div class="form-row">
        <div class="form-group">
          <label>Followers</label>
          <input type="number" name="followers" value="<?= (int) $data['followers'] ?>" min="0" required>
        </div>
        <div class="form-group">
          <label>Following</label>
          <input type="number" name="following" value="<?= (int) $data['following'] ?>" min="0" required>
        </div>
        <div class="form-group">
          <label>Posts</label>
          <input type="number" name="posts" value="<?= (int) $data['posts'] ?>" min="0" required>
        </div>
      </div>
      <button type="submit" class="btn-submit"><i data-lucide="save" class="lucide-ic"></i> Simpan Perubahan</button>
    </form>
    <?php if (!empty($data['updated_at'])): ?>
    <div class="last-update">
      Terakhir diperbarui <?= date('d M Y, H:i', strtotime($data['updated_at'])) ?> WIB
      <?= !empty($data['updated_by']) ? 'oleh '.htmlspecialchars($data['updated_by']) : '' ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide){lucide.createIcons();}</script>
</body>
</html>
