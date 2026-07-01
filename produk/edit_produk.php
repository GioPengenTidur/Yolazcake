<?php
include '../config/koneksi.php';

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM produk WHERE id_produk = ?");

$stmt->bind_param("i", $id);

$stmt->execute();
$result = $stmt->get_result();
$produk = $result->fetch_assoc();

if(isset($_POST['update'])){

    $nama_produk = $_POST['nama_produk'];
    $harga       = $_POST['harga'];
    $deskripsi   = $_POST['deskripsi'];
    $stok        = $_POST['stok'];
    $kategori    = $_POST['kategori'];

    if($_FILES['foto']['name'] != ''){

        $foto = $_FILES['foto']['name'];
        $tmp  = $_FILES['foto']['tmp_name'];

        move_uploaded_file(
            $tmp,
            "../assets/img/produk/".$foto
        );

        mysqli_query($conn,"
            UPDATE produk
            SET
            nama_produk='$nama_produk',
            harga='$harga',
            deskripsi='$deskripsi',
            stok='$stok',
            kategori='$kategori',
            foto='$foto'
            WHERE id_produk='$id'
        ");

    } else {

        mysqli_query($conn,"
            UPDATE produk
            SET
            nama_produk='$nama_produk',
            harga='$harga',
            deskripsi='$deskripsi',
            stok='$stok',
            kategori='$kategori'
            WHERE id_produk='$id'
        ");

    }

    echo "
    <script>
        alert('Produk berhasil diupdate!');
        window.location='data_produk.php';
    </script>
    ";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Produk – YOLAZCAKE</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}

    body {
      min-height:100vh;
      font-family:'Inter',sans-serif;
      background:linear-gradient(160deg,#1e0e3a 0%,#2d1560 50%,#1a0a2e 100%);
      position:relative;
      overflow-x:hidden;
    }

    body::before {
      content:''; position:fixed; inset:0;
      background:
        radial-gradient(ellipse at 20% 30%,rgba(212,175,55,.10) 0%,transparent 55%),
        radial-gradient(ellipse at 80% 70%,rgba(232,160,191,.10) 0%,transparent 55%);
      pointer-events:none; z-index:0;
    }

    /* HERO */
    .page-hero {
      position:relative; height:260px;
      display:flex; flex-direction:column; align-items:center; justify-content:center;
      overflow:hidden;
      background:linear-gradient(135deg,#2b1a11 0%,#4a2c1a 40%,#6d3e26 70%,#3a1f0e 100%);
      z-index:1;
    }

    .page-hero::before {
      content:''; position:absolute; inset:0;
      background:
        radial-gradient(ellipse at 30% 50%,rgba(212,175,55,.18) 0%,transparent 60%),
        radial-gradient(ellipse at 75% 40%,rgba(232,160,191,.15) 0%,transparent 55%);
      animation:heroAurora 8s ease-in-out infinite alternate;
    }

    @keyframes heroAurora{0%{opacity:.6;transform:scale(1);}100%{opacity:1;transform:scale(1.08) translateX(10px);}}
    .sparkle{position:absolute;border-radius:50%;pointer-events:none;animation:floatDot linear infinite;}
    @keyframes floatDot{0%{transform:translateY(0) rotate(0deg);opacity:0;}20%{opacity:1;}80%{opacity:.8;}100%{transform:translateY(-280px) rotate(360deg);opacity:0;}}

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
      font-size:.9em; color:rgba(255,255,255,.65); margin-top:10px;
      opacity:0; animation:fadeSlideDown .9s forwards .9s;
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

    /* back link */
    .back-link{
      position:relative;z-index:2;
      display:inline-flex;align-items:center;gap:8px;
      margin:32px auto 0;padding:0 32px;max-width:860px;width:100%;
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

    /* PAGE WRAPPER */
    .page-wrapper{
      position:relative;z-index:1;
      display:flex;flex-direction:column;align-items:center;
      padding:28px 20px 80px;
      max-width:860px;margin:0 auto;
    }

    /* TOP BAR */
    .top-bar{
      width:100%;display:flex;align-items:center;justify-content:space-between;
      margin-bottom:28px;flex-wrap:wrap;gap:14px;
    }

    .section-title{
      font-family:'Playfair Display',serif;font-size:1.6em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,#D4AF37 70%);
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
    }

    .edit-badge{
      display:inline-flex;align-items:center;gap:8px;
      background:linear-gradient(135deg,rgba(255,180,50,.15),rgba(255,180,50,.06));
      border:1px solid rgba(255,180,50,.35);
      color:#ffb432;font-size:.78em;font-weight:600;letter-spacing:1.5px;
      padding:6px 18px;border-radius:999px;
    }

    /* MAIN CARD */
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

    /* GOLD RULE */
    .gold-rule-h{display:flex;align-items:center;gap:10px;padding:20px 28px 4px;}
    .gold-rule-h::before,.gold-rule-h::after{content:'';flex:1;height:1px;background:linear-gradient(to right,transparent,rgba(212,175,55,.5));}
    .gold-rule-h::after{background:linear-gradient(to left,transparent,rgba(212,175,55,.5));}
    .gold-rule-h span{color:#D4AF37;font-size:.65em;letter-spacing:3px;}

    /* FORM */
    .form-body{
      padding:28px 36px 40px;
      display:grid;grid-template-columns:1fr 1fr;gap:24px 32px;
    }

    .form-group{
      display:flex;flex-direction:column;gap:8px;
    }

    .form-group.full-width{
      grid-column:1 / -1;
    }

    .form-label{
      font-size:.72em;font-weight:600;letter-spacing:2px;text-transform:uppercase;
      color:rgba(212,175,55,.85);
    }

    .form-control{
      width:100%;
      background:rgba(255,255,255,.06);
      border:1px solid rgba(255,255,255,.12);
      border-radius:12px;
      padding:13px 16px;
      font-family:'Inter',sans-serif;font-size:.9em;
      color:#fff;
      outline:none;
      transition:border-color .3s, box-shadow .3s, background .3s;
      -webkit-appearance:none;appearance:none;
    }

    .form-control::placeholder{
      color:rgba(255,255,255,.3);
    }

    .form-control:focus{
      border-color:rgba(212,175,55,.55);
      background:rgba(255,255,255,.09);
      box-shadow:0 0 0 3px rgba(212,175,55,.12), 0 0 20px rgba(212,175,55,.08);
    }

    textarea.form-control{
      resize:vertical;min-height:110px;line-height:1.6;
    }

    select.form-control option{
      background:#2d1560;color:#fff;
    }

    /* PREFIX INPUT */
    .input-prefix-wrap{
      position:relative;display:flex;align-items:center;
    }

    .input-prefix{
      position:absolute;left:16px;
      font-size:.9em;font-weight:600;color:rgba(212,175,55,.7);
      pointer-events:none;user-select:none;
    }

    .input-prefix-wrap .form-control{
      padding-left:52px;
    }

    /* FOTO PREVIEW */
    .foto-section{
      grid-column:1 / -1;
      display:grid;grid-template-columns:180px 1fr;gap:24px;
      align-items:start;
    }

    .foto-preview-box{
      display:flex;flex-direction:column;gap:12px;align-items:center;
    }

    .foto-current{
      width:160px;height:160px;object-fit:cover;
      border-radius:16px;
      border:2px solid rgba(212,175,55,.3);
      transition:transform .3s,box-shadow .3s;
    }

    .foto-current:hover{
      transform:scale(1.04);
      box-shadow:0 0 24px rgba(212,175,55,.35);
    }

    .foto-label-current{
      font-size:.7em;letter-spacing:2px;text-transform:uppercase;
      color:rgba(255,255,255,.35);
    }

    .foto-upload-box{
      display:flex;flex-direction:column;gap:8px;
    }

    .file-drop{
      position:relative;
      border:1.5px dashed rgba(212,175,55,.35);
      border-radius:14px;
      padding:28px 20px;
      text-align:center;
      cursor:pointer;
      transition:border-color .3s,background .3s;
      background:rgba(212,175,55,.04);
    }

    .file-drop:hover{
      border-color:rgba(212,175,55,.65);
      background:rgba(212,175,55,.08);
    }

    .file-drop input[type="file"]{
      position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer;
    }

    .file-drop-icon{font-size:2em;margin-bottom:8px;}

    .file-drop-text{
      font-size:.82em;color:rgba(255,255,255,.5);line-height:1.5;
    }

    .file-drop-text strong{
      color:rgba(212,175,55,.8);display:block;font-size:1.05em;margin-bottom:3px;
    }

    #foto-new-preview{
      width:100%;max-width:280px;height:150px;object-fit:cover;
      border-radius:12px;border:1px solid rgba(212,175,55,.25);
      display:none;margin-top:8px;
    }

    /* FORM DIVIDER */
    .form-divider{
      grid-column:1 / -1;
      height:1px;
      background:linear-gradient(to right,transparent,rgba(212,175,55,.3),transparent);
      margin:4px 0;
    }

    /* BUTTONS */
    .form-actions{
      grid-column:1 / -1;
      display:flex;align-items:center;justify-content:flex-end;
      gap:14px;flex-wrap:wrap;margin-top:8px;
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

    .btn-save{
      background:linear-gradient(135deg,#D4AF37 0%,#b8860b 50%,#D4AF37 100%);
      background-size:200% 100%;color:#1e0e3a;
      animation:goldSlide 3s linear infinite;
      box-shadow:0 8px 28px rgba(212,175,55,.35),0 0 40px rgba(212,175,55,.18);
    }

    .btn-save:hover{box-shadow:0 12px 40px rgba(212,175,55,.55),0 0 60px rgba(212,175,55,.3);}

    .btn-cancel{
      background:rgba(255,255,255,.07);
      border:1px solid rgba(255,255,255,.18);
      color:rgba(255,255,255,.75);
      animation:none;box-shadow:none;
    }

    .btn-cancel:hover{
      background:rgba(255,255,255,.12);
      border-color:rgba(255,255,255,.35);
      box-shadow:0 6px 20px rgba(0,0,0,.25);
      color:#fff;
    }

    /* PARTICLES */
    .particle{position:fixed;border-radius:50%;pointer-events:none;animation:particleFloat linear infinite;z-index:0;}
    @keyframes particleFloat{
      0%{transform:translateY(100vh) scale(0);opacity:0;}
      10%{opacity:.5;}90%{opacity:.3;}
      100%{transform:translateY(-100px) scale(1);opacity:0;}
    }

    /* STOK INDICATOR */
    .stok-hint{
      font-size:.72em;color:rgba(255,255,255,.35);margin-top:4px;
    }

    @media(max-width:768px){
      .hero-inner h1{font-size:2em;}
      .form-body{grid-template-columns:1fr;padding:20px 20px 32px;}
      .form-group.full-width{grid-column:auto;}
      .form-actions{grid-column:auto;}
      .form-divider{grid-column:auto;}
      .foto-section{grid-template-columns:1fr;grid-column:auto;}
      .foto-preview-box{flex-direction:row;justify-content:flex-start;}
    }
  </style>
</head>
<body>

<div id="particles"></div>

<!-- HERO -->
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow">✦ YOLAZCAKE Sintang ✦</p>
    <h1>Edit Produk</h1>
    <p class="hero-sub">Perbarui informasi produk YOLAZCAKE dengan mudah</p>
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
      <div class="edit-badge">✏️ Edit Produk</div>
      <h2 class="section-title" style="margin-top:10px;">
        <?= htmlspecialchars($produk['nama_produk']); ?>
      </h2>
    </div>
  </div>

  <div class="main-card">
    <div class="gold-rule-h"><span>✦ ✦ ✦</span></div>

    <form method="POST" enctype="multipart/form-data">
      <div class="form-body">

        <!-- Nama Produk -->
        <div class="form-group">
          <label class="form-label">Nama Produk</label>
          <input
            type="text"
            name="nama_produk"
            class="form-control"
            placeholder="Masukkan nama produk…"
            value="<?= htmlspecialchars($produk['nama_produk']); ?>"
            required>
        </div>

        <!-- Kategori -->
        <div class="form-group">
          <label class="form-label">Kategori</label>
          <select name="kategori" class="form-control">
            <?php
              $kategoriList = ['Cake','Bakery','Minuman','Snack','Lainnya'];
              foreach($kategoriList as $kat){
                $sel = (isset($produk['kategori']) && $produk['kategori']==$kat) ? 'selected' : '';
                echo "<option value=\"$kat\" $sel>$kat</option>";
              }
            ?>
          </select>
        </div>

        <!-- Harga -->
        <div class="form-group">
          <label class="form-label">Harga</label>
          <div class="input-prefix-wrap">
            <span class="input-prefix">Rp</span>
            <input
              type="number"
              name="harga"
              class="form-control"
              placeholder="0"
              value="<?= htmlspecialchars($produk['harga']); ?>"
              min="0"
              required>
          </div>
        </div>

        <!-- Stok -->
        <div class="form-group">
          <label class="form-label">Stok</label>
          <input
            type="number"
            name="stok"
            class="form-control"
            placeholder="0"
            value="<?= htmlspecialchars($produk['stok']); ?>"
            min="0"
            required>
          <p class="stok-hint">⚠ Stok ≤ 5 akan ditampilkan sebagai peringatan rendah</p>
        </div>

        <!-- Deskripsi -->
        <div class="form-group full-width">
          <label class="form-label">Deskripsi Produk</label>
          <textarea
            name="deskripsi"
            class="form-control"
            placeholder="Tulis deskripsi produk…"><?= htmlspecialchars($produk['deskripsi']); ?></textarea>
        </div>

        <div class="form-divider"></div>

        <!-- Foto -->
        <div class="foto-section">
          <div class="foto-preview-box">
            <img
              src="../assets/img/produk/<?= htmlspecialchars($produk['foto']); ?>"
              alt="Foto Saat Ini"
              class="foto-current"
              id="foto-current-img">
            <span class="foto-label-current">Foto Saat Ini</span>
          </div>

          <div class="foto-upload-box">
            <label class="form-label">Ganti Foto <span style="color:rgba(255,255,255,.3);font-size:.9em;text-transform:none;letter-spacing:0;">(Opsional)</span></label>
            <div class="file-drop" id="fileDrop">
              <input type="file" name="foto" id="fotoInput" accept="image/*">
              <div class="file-drop-icon">🖼️</div>
              <div class="file-drop-text">
                <strong>Klik atau seret foto ke sini</strong>
                JPG, PNG, WEBP • Maks 2 MB
              </div>
            </div>
            <img id="foto-new-preview" src="" alt="Preview Foto Baru">
          </div>
        </div>

        <div class="form-divider"></div>

        <!-- Actions -->
        <div class="form-actions">
          <a href="data_produk.php" class="btn-premium btn-cancel">✕ Batal</a>
          <button type="submit" name="update" class="btn-premium btn-save">✦ Simpan Perubahan</button>
        </div>

      </div>
    </form>
  </div>

</div>

<!-- FOOTER -->
<div style="position:relative;z-index:1;text-align:center;padding:36px 20px;font-size:.8em;color:rgba(255,255,255,.5);border-top:1px solid rgba(255,255,255,.06);line-height:1.8;">
  © 2026 YOLAZCAKE Sintang • Cafe • Bakery • Boutique<br>
  Jl. Lintas Melawi, Ladang, Sintang, Kalimantan Barat<br>
  WA: 0815-7815-7888
</div>

<script>
  // Hero sparkles
  (function(){
    const hero=document.getElementById('pageHero');
    const colors=['#D4AF37','#FFE4B5','#E8A0BF','#fff','#f9ce34','#b8860b'];
    for(let i=0;i<22;i++){
      const d=document.createElement('div');d.className='sparkle';
      const s=Math.random()*5+2;
      d.style.cssText=`width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;bottom:${Math.random()*30}%;animation-duration:${4+Math.random()*7}s;animation-delay:${Math.random()*5}s;opacity:0;`;
      hero.appendChild(d);
    }
  })();

  // Background particles
  (function(){
    const c=document.getElementById('particles');
    const colors=['rgba(212,175,55,.4)','rgba(232,160,191,.3)','rgba(255,255,255,.15)'];
    for(let i=0;i<16;i++){
      const p=document.createElement('div');p.className='particle';
      const s=Math.random()*5+2;
      p.style.cssText=`width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;animation-duration:${10+Math.random()*12}s;animation-delay:${Math.random()*10}s;`;
      c.appendChild(p);
    }
  })();

  // Foto preview on file select
  (function(){
    const input   = document.getElementById('fotoInput');
    const preview = document.getElementById('foto-new-preview');
    const drop    = document.getElementById('fileDrop');

    input.addEventListener('change', function(){
      const file = this.files[0];
      if(!file) return;
      const reader = new FileReader();
      reader.onload = e => {
        preview.src     = e.target.result;
        preview.style.display = 'block';
        drop.querySelector('.file-drop-text strong').textContent = file.name;
      };
      reader.readAsDataURL(file);
    });
  })();
</script>

</body>
</html>
