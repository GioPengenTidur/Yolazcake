<?php
session_start();
include '../config/koneksi.php';
require_once '../config/ulasan_helper.php';

$ringkasan = get_ringkasan_rating_tempat($conn);
$daftar    = get_ulasan_tempat($conn);
$nama_default = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="../assets/css/lucide-icons.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rating Tempat & Makanan – YOLAZCAKE</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{min-height:100vh;font-family:'Inter',sans-serif;
      background:linear-gradient(160deg,#1e0e3a 0%,#2d1560 50%,#1a0a2e 100%);color:#fff;overflow-x:hidden;}
    body::before{content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
      background:radial-gradient(ellipse at 20% 30%,rgba(212,175,55,.10) 0%,transparent 55%),
                 radial-gradient(ellipse at 80% 70%,rgba(232,160,191,.10) 0%,transparent 55%);}
    .page-hero{position:relative;height:260px;display:flex;flex-direction:column;align-items:center;justify-content:center;overflow:hidden;
      background:linear-gradient(135deg,#2b1a11 0%,#4a2c1a 40%,#6d3e26 70%,#3a1f0e 100%);z-index:1;}
    .page-hero::before{content:'';position:absolute;inset:0;
      background:radial-gradient(ellipse at 30% 50%,rgba(212,175,55,.18) 0%,transparent 60%),
                 radial-gradient(ellipse at 75% 40%,rgba(232,160,191,.15) 0%,transparent 55%);
      animation:heroAurora 8s ease-in-out infinite alternate;}
    @keyframes heroAurora{0%{opacity:.6;transform:scale(1);}100%{opacity:1;transform:scale(1.08) translateX(10px);}}
    .hero-inner{position:relative;z-index:2;text-align:center;}
    .hero-eyebrow{font-size:.72em;font-weight:500;letter-spacing:5px;text-transform:uppercase;color:#D4AF37;margin-bottom:10px;}
    .hero-inner h1{font-family:'Playfair Display',serif;font-size:2.6em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,#D4AF37 60%,#FFE4B5 80%,#fff);background-size:200%;
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
      animation:shimmerText 4s ease-in-out infinite;}
    @keyframes shimmerText{0%{background-position:100% 0;}100%{background-position:-100% 0;}}
    .hero-sub{font-size:.9em;color:rgba(255,255,255,.65);margin-top:10px;}

    .back-link{position:relative;z-index:2;display:inline-flex;align-items:center;gap:8px;margin:32px auto 0;padding:0 32px;max-width:900px;width:100%;}
    .back-link a{font-size:.82em;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:rgba(212,175,55,.85);text-decoration:none;
      border:1px solid rgba(212,175,55,.3);padding:7px 18px;border-radius:999px;background:rgba(212,175,55,.06);}
    .back-link a:hover{background:rgba(212,175,55,.16);border-color:rgba(212,175,55,.7);color:#D4AF37;}

    .page-wrapper{position:relative;z-index:1;padding:28px 20px 80px;max-width:900px;margin:0 auto;}

    .summary-row{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:32px;}
    .summary-card{background:rgba(255,255,255,.06);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.1);
      border-radius:18px;padding:20px;text-align:center;}
    .summary-icon{font-size:1.8em;margin-bottom:6px;}
    .summary-val{font-family:'Playfair Display',serif;font-size:1.8em;font-weight:700;color:#D4AF37;}
    .summary-lbl{font-size:.75em;color:rgba(255,255,255,.55);margin-top:4px;}

    .form-card{background:rgba(255,255,255,.06);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.1);
      border-radius:24px;padding:28px;margin-bottom:32px;position:relative;overflow:hidden;}
    .form-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);background-size:200%;animation:goldSlide 4s linear infinite;}
    @keyframes goldSlide{0%{background-position:0;}100%{background-position:200%;}}
    .form-card h3{font-family:'Playfair Display',serif;color:#D4AF37;font-size:1.3em;margin-bottom:16px;}
    .star-input{display:flex;gap:6px;font-size:1.8em;margin:6px 0 4px;}
    .star-input span{cursor:pointer;color:rgba(255,255,255,.25);transition:color .15s;}
    .star-input span.active{color:#D4AF37;}
    .field-label{font-size:.78em;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:rgba(212,175,55,.85);margin-top:16px;display:block;}
    input[type=text], textarea{
      width:100%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.15);border-radius:12px;
      padding:12px 14px;color:#fff;font-family:'Inter',sans-serif;font-size:.9em;margin-top:6px;}
    textarea{resize:vertical;min-height:90px;}
    input:focus, textarea:focus{outline:none;border-color:#D4AF37;box-shadow:0 0 0 3px rgba(212,175,55,.15);}
    .btn-kirim{margin-top:20px;padding:13px 30px;border:none;border-radius:14px;font-weight:700;letter-spacing:1.5px;
      text-transform:uppercase;font-size:.85em;cursor:pointer;
      background:linear-gradient(135deg,#D4AF37 0%,#b8860b 50%,#D4AF37 100%);color:#1e0e3a;}
    .form-msg{margin-top:14px;padding:10px 14px;border-radius:10px;font-size:.85em;display:none;}
    .form-msg.ok{background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.3);color:#6ee7b7;}
    .form-msg.err{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);color:#fca5a5;}

    .review-list{display:flex;flex-direction:column;gap:14px;}
    .review-item{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:18px 20px;}
    .review-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;flex-wrap:wrap;gap:6px;}
    .review-name{font-weight:700;color:#fff;}
    .review-date{font-size:.75em;color:rgba(255,255,255,.4);}
    .review-stars{font-size:.85em;margin:4px 0;display:flex;gap:14px;flex-wrap:wrap;}
    .review-stars span.lbl{color:rgba(255,255,255,.5);margin-right:4px;}
    .review-text{font-size:.88em;color:rgba(255,255,255,.75);line-height:1.6;margin-top:6px;}
    .empty-state{text-align:center;padding:40px;color:rgba(255,255,255,.4);}
  </style>
</head>
<body>

<div class="page-hero">
  <div class="hero-inner">
    <p class="hero-eyebrow"><i data-lucide="sparkle" class="lucide-ic"></i> YOLAZCAKE Sintang <i data-lucide="sparkle" class="lucide-ic"></i></p>
    <h1>Rating Tempat & Makanan</h1>
    <p class="hero-sub">Ceritakan pengalamanmu — enak dan nyamannya YOLAZCAKE</p>
  </div>
</div>

<div class="back-link"><a href="../index.php"><i data-lucide="arrow-left" class="lucide-ic"></i> Kembali ke Beranda</a></div>

<div class="page-wrapper">

  <div class="summary-row">
    <div class="summary-card"><div class="summary-icon"><i data-lucide="file-text" class="lucide-ic"></i></div><div class="summary-val"><?= $ringkasan['jumlah'] ?></div><div class="summary-lbl">Total Ulasan</div></div>
    <div class="summary-card"><div class="summary-icon"><i data-lucide="utensils" class="lucide-ic"></i></div><div class="summary-val"><?= number_format($ringkasan['avg_makanan'],1) ?></div><div class="summary-lbl">Rasa Makanan</div></div>
    <div class="summary-card"><div class="summary-icon"><i data-lucide="sofa" class="lucide-ic"></i></div><div class="summary-val"><?= number_format($ringkasan['avg_tempat'],1) ?></div><div class="summary-lbl">Kenyamanan Tempat</div></div>
  </div>

  <div class="form-card">
    <h3><i data-lucide="pen-line" class="lucide-ic"></i> Beri Ulasan</h3>

    <span class="field-label">Rasa Makanan</span>
    <div class="star-input" data-target="rating_makanan">
      <span data-val="1"><i data-lucide="star" class="lucide-ic lucide-fill"></i></span><span data-val="2"><i data-lucide="star" class="lucide-ic lucide-fill"></i></span><span data-val="3"><i data-lucide="star" class="lucide-ic lucide-fill"></i></span><span data-val="4"><i data-lucide="star" class="lucide-ic lucide-fill"></i></span><span data-val="5"><i data-lucide="star" class="lucide-ic lucide-fill"></i></span>
    </div>

    <span class="field-label">Kenyamanan Tempat</span>
    <div class="star-input" data-target="rating_tempat">
      <span data-val="1"><i data-lucide="star" class="lucide-ic lucide-fill"></i></span><span data-val="2"><i data-lucide="star" class="lucide-ic lucide-fill"></i></span><span data-val="3"><i data-lucide="star" class="lucide-ic lucide-fill"></i></span><span data-val="4"><i data-lucide="star" class="lucide-ic lucide-fill"></i></span><span data-val="5"><i data-lucide="star" class="lucide-ic lucide-fill"></i></span>
    </div>

    <?php if (!$nama_default): ?>
    <label class="field-label">Nama</label>
    <input type="text" id="namaUlasan" placeholder="Nama Anda">
    <?php endif; ?>

    <label class="field-label">Komentar (opsional)</label>
    <textarea id="komentarUlasan" placeholder="Ceritakan pengalamanmu di YOLAZCAKE…"></textarea>

    <button class="btn-kirim" id="btnKirimUlasan">Kirim Ulasan</button>
    <div class="form-msg" id="formMsg"></div>
  </div>

  <div class="review-list" id="reviewList">
    <?php if (!empty($daftar)): foreach ($daftar as $d): ?>
      <div class="review-item">
        <div class="review-head">
          <span class="review-name"><i data-lucide="user" class="lucide-ic"></i> <?= htmlspecialchars($d['nama_reviewer']) ?></span>
          <span class="review-date"><?= date('d M Y', strtotime($d['created_at'])) ?></span>
        </div>
        <div class="review-stars">
          <span><span class="lbl"><i data-lucide="utensils" class="lucide-ic"></i> Makanan</span><?php for($i=1;$i<=5;$i++): ?><span style="color:<?= $i<=$d['rating_makanan']?'#D4AF37':'rgba(255,255,255,.2)' ?>;"><i data-lucide="star" class="lucide-ic lucide-fill"></i></span><?php endfor; ?></span>
          <span><span class="lbl"><i data-lucide="sofa" class="lucide-ic"></i> Tempat</span><?php for($i=1;$i<=5;$i++): ?><span style="color:<?= $i<=$d['rating_tempat']?'#D4AF37':'rgba(255,255,255,.2)' ?>;"><i data-lucide="star" class="lucide-ic lucide-fill"></i></span><?php endfor; ?></span>
        </div>
        <?php if (!empty($d['komentar'])): ?>
          <p class="review-text"><?= nl2br(htmlspecialchars($d['komentar'])) ?></p>
        <?php endif; ?>
      </div>
    <?php endforeach; else: ?>
      <div class="empty-state"><i data-lucide="inbox" class="lucide-ic"></i> Belum ada ulasan, jadilah yang pertama!</div>
    <?php endif; ?>
  </div>

</div>

<script>
  // Bintang interaktif untuk kedua kelompok rating
  document.querySelectorAll('.star-input').forEach(function(group){
    group.addEventListener('click', function(e){
      if (e.target.tagName !== 'SPAN') return;
      const val = parseInt(e.target.dataset.val, 10);
      group.dataset.value = val;
      [...group.children].forEach(function(s){
        s.classList.toggle('active', parseInt(s.dataset.val,10) <= val);
      });
    });
  });

  document.getElementById('btnKirimUlasan').addEventListener('click', function(){
    const btn = this;
    const msg = document.getElementById('formMsg');
    const ratingMakanan = document.querySelector('.star-input[data-target="rating_makanan"]').dataset.value || 0;
    const ratingTempat  = document.querySelector('.star-input[data-target="rating_tempat"]').dataset.value || 0;
    const komentar = document.getElementById('komentarUlasan').value.trim();
    const namaInput = document.getElementById('namaUlasan');
    const nama = namaInput ? namaInput.value.trim() : '';

    if (ratingMakanan == 0 || ratingTempat == 0) {
      msg.className = 'form-msg err'; msg.style.display='block';
      msg.textContent = 'Silakan beri rating bintang untuk makanan dan tempat.';
      return;
    }

    btn.disabled = true; btn.textContent = 'Mengirim…';

    fetch('proses_ulasan_tempat.php', {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({
        rating_makanan: ratingMakanan,
        rating_tempat: ratingTempat,
        komentar: komentar,
        nama: nama
      })
    })
    .then(res => res.json())
    .then(data => {
      msg.className = 'form-msg ' + (data.success ? 'ok' : 'err');
      msg.style.display = 'block';
      msg.textContent = (data.success ? '<i data-lucide="check-circle" class="lucide-ic"></i> ' : '<i data-lucide="alert-triangle" class="lucide-ic"></i> ') + data.message;
      if (data.success) {
        setTimeout(() => window.location.reload(), 1200);
      } else {
        btn.disabled = false; btn.textContent = 'Kirim Ulasan';
      }
    })
    .catch(() => {
      msg.className = 'form-msg err'; msg.style.display='block';
      msg.textContent = '<i data-lucide="alert-triangle" class="lucide-ic"></i> Gagal terhubung ke server. Coba lagi.';
      btn.disabled = false; btn.textContent = 'Kirim Ulasan';
    });
  });
</script>

<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide){lucide.createIcons();}</script>
</body>
</html>
