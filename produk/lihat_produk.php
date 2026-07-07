<?php
session_start();
include '../config/koneksi.php';
require_once '../config/ulasan_helper.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare(
    "SELECT p.*, COALESCE(k.nama_kategori, 'Lainnya') AS nama_kategori, k.icon AS kategori_icon
     FROM produk p
     LEFT JOIN kategori k ON k.id_kategori = p.id_kategori
     WHERE p.id_produk = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$produk = $stmt->get_result()->fetch_assoc();

if (!$produk) {
    header("Location: menu.php");
    exit;
}

$ringkasan = get_ringkasan_rating_produk($conn, $id);
$ulasan    = get_ulasan_produk($conn, $id);
$sudah_login = isset($_SESSION['username']);

$stok = (int)$produk['stok'];
if ($stok <= 0) { $stok_class='stok-habis'; $stok_label='Habis'; }
elseif ($stok <= 5) { $stok_class='stok-low'; $stok_label=$stok.' pcs tersisa'; }
else { $stok_class='stok-ok'; $stok_label=$stok.' pcs tersedia'; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="../assets/css/lucide-icons.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($produk['nama_produk']) ?> – YOLAZCAKE</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{min-height:100vh;font-family:'Inter',sans-serif;
      background:linear-gradient(160deg,#1e0e3a 0%,#2d1560 50%,#1a0a2e 100%);color:#fff;overflow-x:hidden;}
    body::before{content:'';position:fixed;inset:0;pointer-events:none;
      background:radial-gradient(ellipse at 20% 30%,rgba(212,175,55,.10) 0%,transparent 55%),
                 radial-gradient(ellipse at 80% 70%,rgba(232,160,191,.10) 0%,transparent 55%);}
    .page-hero{position:relative;height:220px;display:flex;flex-direction:column;align-items:center;justify-content:center;overflow:hidden;
      background:linear-gradient(135deg,#2b1a11 0%,#4a2c1a 40%,#6d3e26 70%,#3a1f0e 100%);z-index:1;}
    .hero-inner{position:relative;z-index:2;text-align:center;}
    .hero-eyebrow{font-size:.72em;font-weight:500;letter-spacing:5px;text-transform:uppercase;color:#D4AF37;margin-bottom:8px;}
    .hero-inner h1{font-family:'Playfair Display',serif;font-size:2em;font-weight:700;color:#fff;}
    .back-link{position:relative;z-index:2;display:inline-flex;align-items:center;gap:8px;margin:32px auto 0;padding:0 32px;max-width:820px;width:100%;}
    .back-link a{font-size:.82em;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:rgba(212,175,55,.85);text-decoration:none;
      border:1px solid rgba(212,175,55,.3);padding:7px 18px;border-radius:999px;background:rgba(212,175,55,.06);}
    .back-link a:hover{background:rgba(212,175,55,.16);border-color:rgba(212,175,55,.7);color:#D4AF37;}
    .page-wrapper{position:relative;z-index:1;display:flex;flex-direction:column;align-items:center;padding:28px 20px 80px;max-width:820px;margin:0 auto;}

    .main-card{width:100%;background:rgba(255,255,255,.06);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.1);
      border-radius:28px;overflow:hidden;position:relative;margin-bottom:28px;}
    .main-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);background-size:200% 100%;animation:goldSlide 4s linear infinite;}
    @keyframes goldSlide{0%{background-position:0% 0;}100%{background-position:200% 0;}}
    .detail-body{padding:24px 28px 32px;display:grid;grid-template-columns:260px 1fr;gap:28px;}
    @media(max-width:640px){.detail-body{grid-template-columns:1fr;}}
    .detail-photo-wrap{border-radius:20px;overflow:hidden;border:1px solid rgba(212,175,55,.25);height:fit-content;}
    .detail-photo-wrap img{width:100%;height:250px;object-fit:cover;display:block;}
    .detail-info{display:flex;flex-direction:column;gap:14px;}
    .detail-name{font-family:'Playfair Display',serif;font-size:1.7em;font-weight:700;color:#fff;}
    .detail-price{font-size:1.4em;font-weight:700;color:#D4AF37;}
    .stok-badge{display:inline-flex;align-items:center;gap:6px;width:fit-content;padding:6px 16px;border-radius:999px;font-size:.78em;font-weight:600;}
    .stok-ok{background:rgba(99,250,180,.12);border:1px solid rgba(99,250,180,.3);color:#6efabc;}
    .stok-low{background:rgba(255,180,50,.12);border:1px solid rgba(255,180,50,.3);color:#ffb432;}
    .stok-habis{background:rgba(255,80,80,.12);border:1px solid rgba(255,80,80,.3);color:#ff6060;}
    .detail-desc-text{font-size:.92em;line-height:1.7;color:rgba(255,255,255,.75);background:rgba(255,255,255,.04);
      border:1px solid rgba(255,255,255,.08);border-radius:14px;padding:14px 16px;white-space:pre-line;}
    .rating-summary{display:flex;align-items:center;gap:10px;}
    .rating-summary .avg{font-family:'Playfair Display',serif;font-size:1.4em;font-weight:700;color:#D4AF37;}
    .btn-add{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 26px;border:none;border-radius:14px;
      font-weight:700;letter-spacing:1px;text-transform:uppercase;font-size:.82em;cursor:pointer;text-decoration:none;
      background:linear-gradient(135deg,#D4AF37 0%,#b8860b 50%,#D4AF37 100%);color:#1e0e3a;width:fit-content;}

    .review-section{width:100%;}
    .section-title{font-family:'Playfair Display',serif;font-size:1.3em;font-weight:700;color:#D4AF37;margin-bottom:16px;}
    .form-card{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:20px;padding:22px;margin-bottom:22px;}
    .star-input{display:flex;gap:6px;font-size:1.8em;margin:6px 0 4px;}
    .star-input span{cursor:pointer;color:rgba(255,255,255,.25);transition:color .15s;}
    .star-input span.active{color:#D4AF37;}
    .field-label{font-size:.78em;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:rgba(212,175,55,.85);display:block;margin-top:12px;}
    textarea{width:100%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.15);border-radius:12px;
      padding:12px 14px;color:#fff;font-family:'Inter',sans-serif;font-size:.9em;margin-top:6px;resize:vertical;min-height:80px;}
    textarea:focus{outline:none;border-color:#D4AF37;box-shadow:0 0 0 3px rgba(212,175,55,.15);}
    .btn-kirim{margin-top:16px;padding:12px 26px;border:none;border-radius:14px;font-weight:700;letter-spacing:1px;
      text-transform:uppercase;font-size:.82em;cursor:pointer;
      background:linear-gradient(135deg,#D4AF37 0%,#b8860b 50%,#D4AF37 100%);color:#1e0e3a;}
    .form-msg{margin-top:12px;padding:10px 14px;border-radius:10px;font-size:.85em;display:none;}
    .form-msg.ok{background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.3);color:#6ee7b7;}
    .form-msg.err{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);color:#fca5a5;}
    .login-note{background:rgba(212,175,55,.08);border:1px solid rgba(212,175,55,.25);border-radius:14px;padding:16px 20px;font-size:.88em;color:rgba(255,255,255,.75);}
    .login-note a{color:#D4AF37;font-weight:600;}

    .review-item{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:16px 18px;margin-bottom:12px;}
    .review-head{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;}
    .review-name{font-weight:700;color:#fff;}
    .review-date{font-size:.75em;color:rgba(255,255,255,.4);}
    .review-stars{margin:4px 0;}
    .review-text{font-size:.88em;color:rgba(255,255,255,.75);line-height:1.6;margin-top:4px;}
    .empty-state{text-align:center;padding:30px;color:rgba(255,255,255,.4);font-size:.9em;}
  </style>
</head>
<body>

<div class="page-hero"><div class="hero-inner">
  <p class="hero-eyebrow"><i data-lucide="sparkle" class="lucide-ic"></i> YOLAZCAKE Sintang <i data-lucide="sparkle" class="lucide-ic"></i></p>
  <h1><?= htmlspecialchars($produk['nama_produk']) ?></h1>
</div></div>

<div class="back-link"><a href="menu.php"><i data-lucide="arrow-left" class="lucide-ic"></i> Kembali ke Menu</a></div>

<div class="page-wrapper">

  <div class="main-card">
    <div class="detail-body">
      <div class="detail-photo-wrap">
        <img src="../assets/img/produk/<?= htmlspecialchars($produk['foto']) ?>" alt="<?= htmlspecialchars($produk['nama_produk']) ?>">
      </div>
      <div class="detail-info">
        <div class="detail-name"><?= htmlspecialchars($produk['nama_produk']) ?></div>
        <div class="detail-price">Rp <?= number_format($produk['harga'],0,',','.') ?></div>

        <div class="rating-summary">
          <?= render_bintang($ringkasan['avg'], '1.3em') ?>
          <span class="avg"><?= number_format($ringkasan['avg'],1) ?></span>
          <span style="color:rgba(255,255,255,.5);font-size:.85em;">(<?= $ringkasan['jumlah'] ?> ulasan)</span>
        </div>

        <div style="display:flex;gap:10px;flex-wrap:wrap;">
          <div class="stok-badge <?= $stok_class ?>"><i data-lucide="package" class="lucide-ic"></i> <?= $stok_label ?></div>
          <div class="stok-badge" style="background:rgba(212,175,55,.12);border:1px solid rgba(212,175,55,.3);color:#D4AF37;">
            <?= $produk['kategori_icon'] ?: '<i data-lucide="utensils" class="lucide-ic"></i>' ?> <?= htmlspecialchars($produk['nama_kategori']) ?>
          </div>
        </div>

        <div class="detail-desc-text"><?= nl2br(htmlspecialchars($produk['deskripsi'])) ?></div>

        <?php if ($stok > 0): ?>
        <a href="menuu.php" class="btn-add"><i data-lucide="shopping-cart" class="lucide-ic"></i> Pesan di Menu</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="review-section">
    <div class="section-title"><i data-lucide="star" class="lucide-ic lucide-fill"></i> Ulasan Pelanggan</div>

    <?php if ($sudah_login): ?>
    <div class="form-card">
      <span class="field-label" style="margin-top:0;">Beri Rating</span>
      <div class="star-input" id="starInput" data-value="0">
        <span data-val="1"><i data-lucide="star" class="lucide-ic lucide-fill"></i></span><span data-val="2"><i data-lucide="star" class="lucide-ic lucide-fill"></i></span><span data-val="3"><i data-lucide="star" class="lucide-ic lucide-fill"></i></span><span data-val="4"><i data-lucide="star" class="lucide-ic lucide-fill"></i></span><span data-val="5"><i data-lucide="star" class="lucide-ic lucide-fill"></i></span>
      </div>
      <label class="field-label">Komentar (opsional)</label>
      <textarea id="komentarProduk" placeholder="Bagaimana rasanya menurutmu?"></textarea>
      <button class="btn-kirim" id="btnKirimUlasanProduk">Kirim Ulasan</button>
      <div class="form-msg" id="formMsgProduk"></div>
    </div>
    <?php else: ?>
    <div class="login-note" style="margin-bottom:22px;">
      <i data-lucide="message-circle" class="lucide-ic"></i> <a href="../auth/login.php">Login</a> terlebih dahulu untuk memberi ulasan produk ini.
    </div>
    <?php endif; ?>

    <div id="reviewListProduk">
      <?php if (!empty($ulasan)): foreach ($ulasan as $u): ?>
        <div class="review-item">
          <div class="review-head">
            <span class="review-name"><i data-lucide="user" class="lucide-ic"></i> <?= htmlspecialchars($u['nama_reviewer']) ?></span>
            <span class="review-date"><?= date('d M Y', strtotime($u['created_at'])) ?></span>
          </div>
          <div class="review-stars"><?= render_bintang((float)$u['rating']) ?></div>
          <?php if (!empty($u['komentar'])): ?>
            <p class="review-text"><?= nl2br(htmlspecialchars($u['komentar'])) ?></p>
          <?php endif; ?>
        </div>
      <?php endforeach; else: ?>
        <div class="empty-state"><i data-lucide="inbox" class="lucide-ic"></i> Belum ada ulasan untuk produk ini, jadilah yang pertama!</div>
      <?php endif; ?>
    </div>
  </div>

</div>

<script>
<?php if ($sudah_login): ?>
document.getElementById('starInput').addEventListener('click', function(e){
  // Pakai closest() karena setelah lucide.createIcons() jalan, <i data-lucide="star">
  // berubah jadi <svg> di dalam <span>, jadi klik bisa kena svg/path, bukan span-nya.
  const target = e.target.closest('span[data-val]');
  if (!target || !this.contains(target)) return;
  const val = parseInt(target.dataset.val, 10);
  this.dataset.value = val;
  [...this.children].forEach(s => s.classList.toggle('active', parseInt(s.dataset.val,10) <= val));
});

document.getElementById('btnKirimUlasanProduk').addEventListener('click', function(){
  const btn = this;
  const msg = document.getElementById('formMsgProduk');
  const rating = document.getElementById('starInput').dataset.value || 0;
  const komentar = document.getElementById('komentarProduk').value.trim();

  if (rating == 0) {
    msg.className = 'form-msg err'; msg.style.display = 'block';
    msg.textContent = 'Silakan pilih rating bintang terlebih dahulu.';
    return;
  }

  btn.disabled = true; btn.textContent = 'Mengirim…';

  fetch('../ulasan/proses_ulasan_produk.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: new URLSearchParams({ id_produk: <?= (int)$id ?>, rating: rating, komentar: komentar })
  })
  .then(res => res.json())
  .then(data => {
    msg.className = 'form-msg ' + (data.success ? 'ok' : 'err');
    msg.style.display = 'block';
    msg.innerHTML = (data.success ? '<i data-lucide="check-circle" class="lucide-ic"></i> ' : '<i data-lucide="alert-triangle" class="lucide-ic"></i> ') + data.message;
    if (window.lucide) lucide.createIcons();
    if (data.success) setTimeout(() => window.location.reload(), 1200);
    else { btn.disabled = false; btn.textContent = 'Kirim Ulasan'; }
  })
  .catch(() => {
    msg.className = 'form-msg err'; msg.style.display = 'block';
    msg.innerHTML = '<i data-lucide="alert-triangle" class="lucide-ic"></i> Gagal terhubung ke server. Coba lagi.';
    if (window.lucide) lucide.createIcons();
    btn.disabled = false; btn.textContent = 'Kirim Ulasan';
  });
});
<?php endif; ?>
</script>


<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide){lucide.createIcons();}</script>
</body>
</html>
