<?php
include '../config/koneksi.php';

if(isset($_POST['simpan'])){
    $nama_produk = $_POST['nama_produk'];
    $harga       = $_POST['harga'];
    $deskripsi   = $_POST['deskripsi'];
    $stok        = $_POST['stok'];

    $namaFoto = $_FILES['foto']['name'];
    $tmpFoto  = $_FILES['foto']['tmp_name'];

    move_uploaded_file(
        $tmpFoto,
        "../assets/img/produk/" . $namaFoto
    );

    $query = mysqli_query($conn,"
        INSERT INTO produk
        (nama_produk,harga,deskripsi,foto,stok)
        VALUES
        (
            '$nama_produk',
            '$harga',
            '$deskripsi',
            '$namaFoto',
            '$stok'
        )
    ");

    if($query){
        include 'success_overlay.php';
        tampilkan_sukses([
            'proses_judul' => 'Menyimpan Produk…',
            'proses_sub'   => 'Sedang menambahkan produk baru ke katalog',
            'sukses_judul' => 'Produk Berhasil Ditambahkan!',
            'sukses_sub'   => '"'.htmlspecialchars($nama_produk).'" kini telah tersedia di katalog',
            'redirect'     => 'data_produk.php',
            'tombol_label' => 'Lanjutkan ke Data Produk',
        ]);
        exit;
    } else {
        echo mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Produk – YOLAZCAKE</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}

    body{
      min-height:100vh;
      font-family:'Inter',sans-serif;
      background:linear-gradient(160deg,#1e0e3a 0%,#2d1560 50%,#1a0a2e 100%);
      position:relative;
      overflow-x:hidden;
    }

    body::before{
      content:'';position:fixed;inset:0;
      background:
        radial-gradient(ellipse at 20% 30%,rgba(212,175,55,.10) 0%,transparent 55%),
        radial-gradient(ellipse at 80% 70%,rgba(232,160,191,.10) 0%,transparent 55%);
      pointer-events:none;z-index:0;
    }

    /* ── HERO ── */
    .page-hero{
      position:relative;height:260px;
      display:flex;flex-direction:column;align-items:center;justify-content:center;
      overflow:hidden;
      background:linear-gradient(135deg,#2b1a11 0%,#4a2c1a 40%,#6d3e26 70%,#3a1f0e 100%);
      z-index:1;
    }
    .page-hero::before{
      content:'';position:absolute;inset:0;
      background:
        radial-gradient(ellipse at 30% 50%,rgba(212,175,55,.18) 0%,transparent 60%),
        radial-gradient(ellipse at 75% 40%,rgba(232,160,191,.15) 0%,transparent 55%);
      animation:heroAurora 8s ease-in-out infinite alternate;
    }
    @keyframes heroAurora{0%{opacity:.6;transform:scale(1);}100%{opacity:1;transform:scale(1.08) translateX(10px);}}

    .sparkle{position:absolute;border-radius:50%;pointer-events:none;animation:floatDot linear infinite;}
    @keyframes floatDot{
      0%{transform:translateY(0) rotate(0deg);opacity:0;}
      20%{opacity:1;}80%{opacity:.8;}
      100%{transform:translateY(-280px) rotate(360deg);opacity:0;}
    }

    .hero-inner{position:relative;z-index:2;text-align:center;color:#fff;}

    .hero-eyebrow{
      font-size:.72em;font-weight:500;letter-spacing:5px;text-transform:uppercase;
      color:#D4AF37;margin-bottom:10px;
      opacity:0;animation:fadeSlideDown .8s forwards .3s;
    }
    .hero-inner h1{
      font-family:'Playfair Display',serif;font-size:2.8em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,#D4AF37 60%,#FFE4B5 80%,#fff);
      background-size:200% 100%;
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
      animation:shimmerText 4s ease-in-out infinite,fadeSlideDown .9s forwards .5s;
      opacity:0;
    }
    .hero-inner .hero-sub{
      font-size:.9em;color:rgba(255,255,255,.65);margin-top:10px;
      opacity:0;animation:fadeSlideDown .9s forwards .9s;
    }
    .hero-divider{
      margin-top:16px;display:flex;justify-content:center;align-items:center;gap:12px;
      opacity:0;animation:fadeSlideDown .9s forwards 1.1s;
    }
    .hero-divider span{display:block;width:60px;height:1px;background:linear-gradient(to right,transparent,#D4AF37);}
    .hero-divider span:last-child{background:linear-gradient(to left,transparent,#D4AF37);}
    .hero-divider .diamond{color:#D4AF37;font-size:.75em;letter-spacing:4px;}

    @keyframes shimmerText{0%{background-position:100% 0;}100%{background-position:-100% 0;}}
    @keyframes fadeSlideDown{from{opacity:0;transform:translateY(-18px);}to{opacity:1;transform:translateY(0);}}
    @keyframes goldSlide{0%{background-position:0% 0;}100%{background-position:200% 0;}}

    /* ── BACK LINK ── */
    .back-link{
      position:relative;z-index:2;
      display:inline-flex;align-items:center;gap:8px;
      margin:32px auto 0;padding:0 32px;max-width:720px;width:100%;
    }
    .back-link a{
      font-size:.82em;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;
      color:rgba(212,175,55,.85);text-decoration:none;
      border:1px solid rgba(212,175,55,.3);padding:7px 18px;border-radius:999px;
      transition:all .3s;background:rgba(212,175,55,.06);
    }
    .back-link a:hover{
      background:rgba(212,175,55,.16);border-color:rgba(212,175,55,.7);
      box-shadow:0 0 18px rgba(212,175,55,.25);color:#D4AF37;
    }

    /* ── PAGE WRAPPER ── */
    .page-wrapper{
      position:relative;z-index:1;
      display:flex;flex-direction:column;align-items:center;
      padding:28px 20px 80px;
      max-width:720px;margin:0 auto;
    }

    /* ── TOP BAR ── */
    .top-bar{
      width:100%;display:flex;align-items:center;justify-content:space-between;
      margin-bottom:28px;flex-wrap:wrap;gap:14px;
    }
    .section-title{
      font-family:'Playfair Display',serif;font-size:1.6em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,#D4AF37 70%);
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
    }
    .new-badge{
      display:inline-flex;align-items:center;gap:8px;
      background:linear-gradient(135deg,rgba(99,250,180,.15),rgba(99,250,180,.06));
      border:1px solid rgba(99,250,180,.35);
      color:#6efabc;font-size:.78em;font-weight:600;letter-spacing:1.5px;
      padding:6px 18px;border-radius:999px;
    }

    /* ── MAIN CARD ── */
    .main-card{
      width:100%;
      background:rgba(255,255,255,.06);
      backdrop-filter:blur(20px);
      border:1px solid rgba(255,255,255,.1);
      border-radius:28px;
      position:relative;overflow:hidden;
      opacity:0;transform:translateY(40px);
      animation:cardReveal .85s cubic-bezier(.22,.68,0,1.2) forwards .6s;
      transition:border-color .45s,box-shadow .45s;
    }
    .main-card:hover{
      border-color:rgba(212,175,55,.35);
      box-shadow:
        0 25px 65px rgba(0,0,0,.35),
        0 0 35px rgba(212,175,55,.28),
        0 0 70px rgba(212,175,55,.14),
        0 0 110px rgba(212,175,55,.06);
    }
    .main-card::before{
      content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);
      background-size:200% 100%;animation:goldSlide 4s linear infinite;
    }
    @keyframes cardReveal{to{opacity:1;transform:translateY(0);}}

    /* ── GOLD RULE ── */
    .gold-rule-h{display:flex;align-items:center;gap:10px;padding:20px 28px 4px;}
    .gold-rule-h::before,.gold-rule-h::after{content:'';flex:1;height:1px;background:linear-gradient(to right,transparent,rgba(212,175,55,.5));}
    .gold-rule-h::after{background:linear-gradient(to left,transparent,rgba(212,175,55,.5));}
    .gold-rule-h span{color:#D4AF37;font-size:.65em;letter-spacing:3px;}

    /* ── FORM BODY ── */
    .form-body{
      padding:12px 32px 36px;
      display:flex;flex-direction:column;gap:22px;
    }

    /* ── FORM GROUP ── */
    .form-group{
      display:flex;flex-direction:column;gap:8px;
      opacity:0;transform:translateX(-20px);
      animation:slideInLeft .5s forwards;
    }
    .form-group:nth-child(1){animation-delay:.75s;}
    .form-group:nth-child(2){animation-delay:.88s;}
    .form-group:nth-child(3){animation-delay:1.01s;}
    .form-group:nth-child(4){animation-delay:1.14s;}
    .form-group:nth-child(5){animation-delay:1.27s;}
    @keyframes slideInLeft{to{opacity:1;transform:translateX(0);}}

    .form-label{
      font-size:.76em;font-weight:600;letter-spacing:2px;text-transform:uppercase;
      color:rgba(212,175,55,.9);
      display:flex;align-items:center;gap:8px;
    }
    .form-label .lbl-icon{font-size:1.1em;}

    /* ── INPUTS ── */
    .form-input,
    .form-textarea{
      width:100%;
      background:rgba(255,255,255,.05);
      border:1px solid rgba(255,255,255,.12);
      border-radius:14px;
      padding:14px 18px;
      color:#fff;
      font-family:'Inter',sans-serif;
      font-size:.92em;
      transition:border-color .3s, box-shadow .3s, background .3s;
      outline:none;
      -webkit-appearance:none;
    }
    .form-input::placeholder,
    .form-textarea::placeholder{color:rgba(255,255,255,.25);}

    .form-input:focus,
    .form-textarea:focus{
      border-color:rgba(212,175,55,.55);
      background:rgba(212,175,55,.06);
      box-shadow:0 0 0 3px rgba(212,175,55,.12), 0 0 20px rgba(212,175,55,.1);
    }
    .form-textarea{resize:vertical;min-height:100px;line-height:1.6;}

    /* ── ROW 2-COL ── */
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
    @media(max-width:560px){.form-row{grid-template-columns:1fr;}}

    /* ── FILE UPLOAD ── */
    .file-upload-wrap{
      position:relative;
      border:2px dashed rgba(212,175,55,.3);
      border-radius:14px;
      padding:28px 20px;
      text-align:center;
      cursor:pointer;
      background:rgba(212,175,55,.04);
      transition:border-color .3s, background .3s;
      overflow:hidden;
    }
    .file-upload-wrap:hover,
    .file-upload-wrap.dragover{
      border-color:rgba(212,175,55,.7);
      background:rgba(212,175,55,.09);
      box-shadow:0 0 24px rgba(212,175,55,.15);
    }
    .file-upload-wrap input[type="file"]{
      position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;
    }
    .file-upload-icon{font-size:2.4em;margin-bottom:8px;opacity:.7;}
    .file-upload-label{
      font-size:.82em;color:rgba(255,255,255,.55);line-height:1.6;
    }
    .file-upload-label strong{color:#D4AF37;}
    .file-preview-wrap{margin-top:14px;display:none;}
    .file-preview-wrap img{
      max-width:120px;max-height:120px;object-fit:cover;
      border-radius:12px;border:1px solid rgba(212,175,55,.3);
      box-shadow:0 0 18px rgba(212,175,55,.2);
    }
    .file-name-tag{
      display:inline-block;margin-top:8px;
      font-size:.75em;color:#D4AF37;
      background:rgba(212,175,55,.1);
      border:1px solid rgba(212,175,55,.3);
      padding:4px 14px;border-radius:999px;
    }

    /* ── DIVIDER ── */
    .form-divider{
      height:1px;
      background:linear-gradient(to right,transparent,rgba(212,175,55,.3),transparent);
      margin:4px 0;
    }

    /* ── SUBMIT BUTTON ── */
    .btn-submit-wrap{
      padding:0 32px 36px;
      display:flex;justify-content:flex-end;gap:14px;flex-wrap:wrap;
      opacity:0;animation:fadeSlideDown .6s forwards 1.4s;
    }

    .btn-premium{
      position:relative;padding:13px 28px;border:none;border-radius:14px;
      font-family:'Inter',sans-serif;font-size:.85em;font-weight:700;
      letter-spacing:2px;text-transform:uppercase;cursor:pointer;
      overflow:hidden;transition:transform .25s cubic-bezier(.34,1.56,.64,1),box-shadow .35s;
      text-decoration:none;display:inline-flex;align-items:center;gap:8px;
    }
    .btn-premium::before{
      content:'';position:absolute;inset:0;
      background:linear-gradient(90deg,transparent,rgba(255,255,255,.15),transparent);
      transform:translateX(-100%);transition:transform .5s;
    }
    .btn-premium:hover::before{transform:translateX(100%);}
    .btn-premium:hover{transform:translateY(-3px) scale(1.02);}

    .btn-add{
      background:linear-gradient(135deg,#D4AF37 0%,#b8860b 50%,#D4AF37 100%);
      background-size:200% 100%;color:#1e0e3a;
      animation:goldSlide 3s linear infinite;
      box-shadow:0 8px 28px rgba(212,175,55,.35),0 0 40px rgba(212,175,55,.18);
    }
    .btn-add:hover{box-shadow:0 12px 40px rgba(212,175,55,.55),0 0 60px rgba(212,175,55,.3);}

    .btn-cancel{
      background:rgba(255,255,255,.07);
      border:1px solid rgba(255,255,255,.15);
      color:rgba(255,255,255,.7);
    }
    .btn-cancel:hover{
      background:rgba(255,80,80,.12);
      border-color:rgba(255,80,80,.35);
      color:#ff8080;
      box-shadow:0 6px 20px rgba(255,80,80,.15);
    }

    /* ── PARTICLES ── */
    .particle{position:fixed;border-radius:50%;pointer-events:none;animation:particleFloat linear infinite;z-index:0;}
    @keyframes particleFloat{
      0%{transform:translateY(100vh) scale(0);opacity:0;}
      10%{opacity:.5;}90%{opacity:.3;}
      100%{transform:translateY(-100px) scale(1);opacity:0;}
    }

    /* ── HINT TEXT ── */
    .form-hint{font-size:.74em;color:rgba(255,255,255,.35);margin-top:2px;}

    @media(max-width:768px){
      .hero-inner h1{font-size:2em;}
      .form-body{padding:12px 20px 28px;}
      .btn-submit-wrap{padding:0 20px 28px;}
    }
  </style>
</head>
<body>

<div id="particles"></div>

<!-- HERO -->
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow">✦ YOLAZCAKE Sintang ✦</p>
    <h1>Tambah Produk</h1>
    <p class="hero-sub">Tambahkan menu atau produk baru ke katalog YOLAZCAKE</p>
    <div class="hero-divider">
      <span></span><span class="diamond">✦ ✦ ✦</span><span></span>
    </div>
  </div>
</div>

<div class="back-link">
  <a href="data_produk.php">← Kembali ke Data Produk</a>
</div>

<div class="page-wrapper">

  <div class="top-bar">
    <div>
      <div class="new-badge">🎂 Manajemen Produk</div>
      <h2 class="section-title" style="margin-top:10px;">Form Tambah Produk</h2>
    </div>
  </div>

  <div class="main-card">
    <div class="gold-rule-h"><span>✦ ✦ ✦</span></div>

    <form method="POST" enctype="multipart/form-data">

      <div class="form-body">

        <!-- Nama Produk -->
        <div class="form-group">
          <label class="form-label">
            <span class="lbl-icon">🎂</span> Nama Produk
          </label>
          <input
            type="text"
            name="nama_produk"
            class="form-input"
            placeholder="Contoh: Black Forest Premium"
            required
          >
        </div>

        <!-- Harga & Stok (2 kolom) -->
        <div class="form-row">
          <div class="form-group" style="animation-delay:.88s">
            <label class="form-label">
              <span class="lbl-icon">💰</span> Harga (Rp)
            </label>
            <input
              type="number"
              name="harga"
              class="form-input"
              placeholder="Contoh: 85000"
              min="0"
              required
            >
            <span class="form-hint">Masukkan harga tanpa titik atau koma</span>
          </div>
          <div class="form-group" style="animation-delay:1.01s">
            <label class="form-label">
              <span class="lbl-icon">📦</span> Stok
            </label>
            <input
              type="number"
              name="stok"
              class="form-input"
              placeholder="Contoh: 20"
              min="0"
              required
            >
            <span class="form-hint">Jumlah stok tersedia (pcs)</span>
          </div>
        </div>

        <!-- Deskripsi -->
        <div class="form-group" style="animation-delay:1.14s">
          <label class="form-label">
            <span class="lbl-icon">📝</span> Deskripsi Produk
          </label>
          <textarea
            name="deskripsi"
            class="form-textarea"
            placeholder="Ceritakan keunggulan produk ini…"
            rows="4"
          ></textarea>
        </div>

        <div class="form-divider"></div>

        <!-- Foto -->
        <div class="form-group" style="animation-delay:1.27s">
          <label class="form-label">
            <span class="lbl-icon">📸</span> Foto Produk
          </label>
          <div class="file-upload-wrap" id="fileWrap">
            <input
              type="file"
              name="foto"
              id="fileInput"
              accept="image/*"
              required
            >
            <div class="file-upload-icon">🖼️</div>
            <div class="file-upload-label">
              <strong>Klik untuk memilih foto</strong> atau seret &amp; lepas di sini<br>
              Format: JPG, PNG, WEBP · Maks. 5 MB
            </div>
          </div>
          <div class="file-preview-wrap" id="previewWrap">
            <img id="previewImg" src="" alt="Preview">
            <div id="fileNameTag" class="file-name-tag"></div>
          </div>
        </div>

      </div><!-- /.form-body -->

      <div class="btn-submit-wrap">
        <a href="data_produk.php" class="btn-premium btn-cancel">✕ Batal</a>
        <button type="submit" name="simpan" class="btn-premium btn-add">✦ Simpan Produk</button>
      </div>

    </form>
  </div><!-- /.main-card -->

</div><!-- /.page-wrapper -->

<!-- FOOTER -->
<div style="position:relative;z-index:1;text-align:center;padding:36px 20px;font-size:.8em;color:rgba(255,255,255,.5);border-top:1px solid rgba(255,255,255,.06);line-height:1.8;">
  © 2026 YOLAZCAKE Sintang • Cafe • Bakery • Boutique<br>
  Jl. Lintas Melawi, Ladang, Sintang, Kalimantan Barat<br>
  WA: 0815-7815-7888
</div>

<script>
  /* Hero sparkles */
  (function(){
    const hero = document.getElementById('pageHero');
    const colors = ['#D4AF37','#FFE4B5','#E8A0BF','#fff','#f9ce34','#b8860b'];
    for(let i = 0; i < 22; i++){
      const d = document.createElement('div'); d.className = 'sparkle';
      const s = Math.random() * 5 + 2;
      d.style.cssText = `width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;bottom:${Math.random()*30}%;animation-duration:${4+Math.random()*7}s;animation-delay:${Math.random()*5}s;opacity:0;`;
      hero.appendChild(d);
    }
  })();

  /* Background particles */
  (function(){
    const c = document.getElementById('particles');
    const colors = ['rgba(212,175,55,.4)','rgba(232,160,191,.3)','rgba(255,255,255,.15)'];
    for(let i = 0; i < 16; i++){
      const p = document.createElement('div'); p.className = 'particle';
      const s = Math.random() * 5 + 2;
      p.style.cssText = `width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;animation-duration:${10+Math.random()*12}s;animation-delay:${Math.random()*10}s;`;
      c.appendChild(p);
    }
  })();

  /* File upload preview */
  (function(){
    const input    = document.getElementById('fileInput');
    const wrap     = document.getElementById('fileWrap');
    const prevWrap = document.getElementById('previewWrap');
    const prevImg  = document.getElementById('previewImg');
    const nameTag  = document.getElementById('fileNameTag');

    input.addEventListener('change', function(){
      const file = this.files[0];
      if(!file) return;
      const reader = new FileReader();
      reader.onload = e => {
        prevImg.src   = e.target.result;
        nameTag.textContent = file.name;
        prevWrap.style.display = 'block';
      };
      reader.readAsDataURL(file);
    });

    /* Drag & drop */
    wrap.addEventListener('dragover', e => { e.preventDefault(); wrap.classList.add('dragover'); });
    wrap.addEventListener('dragleave', ()  => { wrap.classList.remove('dragover'); });
    wrap.addEventListener('drop', e => {
      e.preventDefault();
      wrap.classList.remove('dragover');
      const file = e.dataTransfer.files[0];
      if(!file || !file.type.startsWith('image/')) return;
      /* Inject file into input */
      const dt = new DataTransfer();
      dt.items.add(file);
      input.files = dt.files;
      input.dispatchEvent(new Event('change'));
    });
  })();

  /* Gold shimmer on input focus */
  document.querySelectorAll('.form-input,.form-textarea').forEach(el => {
    el.addEventListener('focus', () => el.style.setProperty('--glow','1'));
    el.addEventListener('blur',  () => el.style.removeProperty('--glow'));
  });
</script>

</body>
</html>
