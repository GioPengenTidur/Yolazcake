<?php
session_start();
require_once '../config/koneksi.php';

// Booking meja WAJIB login -- kalau belum, tampilkan peringatan di halaman
// login lalu balik lagi otomatis ke sini setelah berhasil masuk.
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php?notice=booking&redirect=" . urlencode('../booking/booking.php'));
    exit();
}

// Ambil meja yang masih Tersedia
$meja_list = mysqli_query($conn, "SELECT * FROM meja WHERE status='Tersedia' ORDER BY nomor_meja ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="../assets/css/lucide-icons.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Booking Meja – YOLAZCAKE</title>
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

    /* hero */
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

    @keyframes heroAurora {0%{opacity:.6;transform:scale(1);}100%{opacity:1;transform:scale(1.08) translateX(10px);}}
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
      margin:32px auto 0;padding:0 32px;max-width:760px;width:100%;
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

    /* card */
    .page-wrapper{
      position:relative;z-index:1;
      display:flex;justify-content:center;
      padding:28px 20px 80px;
    }

    .form-card{
      width:100%;max-width:760px;
      background:rgba(255,255,255,.06);
      backdrop-filter:blur(20px);
      border:1px solid rgba(255,255,255,.1);
      border-radius:28px;padding:48px 48px 52px;
      position:relative;overflow:hidden;
      opacity:0;transform:translateY(40px);
      animation:cardReveal .85s cubic-bezier(.22,.68,0,1.2) forwards .6s;
      transition:border-color .45s,box-shadow .45s;
    }

    .form-card:hover{
      border-color:rgba(212,175,55,.35);
      box-shadow:
        0 25px 65px rgba(0,0,0,.35),
        0 0 35px rgba(212,175,55,.28),
        0 0 70px rgba(212,175,55,.14),
        0 0 110px rgba(212,175,55,.06);
    }

    .form-card::before{
      content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);
      background-size:200% 100%;animation:goldSlide 4s linear infinite;
    }

    @keyframes cardReveal{to{opacity:1;transform:translateY(0);}}

    .new-badge{
      display:inline-flex;align-items:center;gap:8px;
      background:linear-gradient(135deg,rgba(99,250,180,.15),rgba(99,250,180,.06));
      border:1px solid rgba(99,250,180,.35);
      color:#6efabc;font-size:.78em;font-weight:600;letter-spacing:1.5px;
      padding:6px 18px;border-radius:999px;margin-bottom:28px;
    }

    .card-title{
      font-family:'Playfair Display',serif;font-size:1.9em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,#D4AF37 70%);
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
      margin-bottom:6px;
    }

    .card-sub{font-size:.88em;color:rgba(255,255,255,.5);margin-bottom:36px;}

    .gold-rule-h{display:flex;align-items:center;gap:10px;margin-bottom:36px;}
    .gold-rule-h::before,.gold-rule-h::after{content:'';flex:1;height:1px;background:linear-gradient(to right,transparent,rgba(212,175,55,.5));}
    .gold-rule-h::after{background:linear-gradient(to left,transparent,rgba(212,175,55,.5));}
    .gold-rule-h span{color:#D4AF37;font-size:.65em;letter-spacing:3px;}

    /* info strip: jam operasional */
    .info-strip{
      display:flex; align-items:center; gap:12px;
      background:rgba(212,175,55,.08);
      border:1px solid rgba(212,175,55,.25);
      border-radius:14px; padding:14px 20px;
      margin-bottom:30px;
      font-size:.85em; color:rgba(255,255,255,.75);
    }
    .info-strip .ii{font-size:1.3em;flex-shrink:0;}
    .info-strip b{color:#D4AF37;}

    /* fields */
    .field-group{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;}
    .field-group.full{grid-template-columns:1fr;}
    .field{display:flex;flex-direction:column;gap:8px;}

    .field label{
      font-size:.72em;font-weight:600;letter-spacing:2px;
      text-transform:uppercase;color:rgba(212,175,55,.85);
    }

    .field-icon-wrap{position:relative;}
    .field-icon{position:absolute;left:16px;top:50%;transform:translateY(-50%);font-size:1em;pointer-events:none;opacity:.7;}
    .field-icon-wrap.textarea-wrap .field-icon{top:18px;transform:none;}

    .field input,.field textarea{
      width:100%;
      background:rgba(255,255,255,.07);
      border:1px solid rgba(255,255,255,.12);
      border-radius:12px;
      padding:14px 18px 14px 46px;
      font-family:'Inter',sans-serif;font-size:.95em;color:#fff;
      outline:none;
      transition:border-color .3s,box-shadow .3s,background .3s;
      color-scheme: dark;
    }

    .field textarea{resize:vertical;min-height:100px;line-height:1.6;}
    .field input::placeholder,.field textarea::placeholder{color:rgba(255,255,255,.3);}

    .field input:focus,.field textarea:focus{
      border-color:rgba(212,175,55,.6);
      background:rgba(212,175,55,.07);
      box-shadow:0 0 0 3px rgba(212,175,55,.12),0 0 20px rgba(212,175,55,.2);
    }

    /* submit */
    .btn-row{display:flex;gap:14px;margin-top:36px;}

    .btn-premium{
      flex:1;position:relative;padding:16px 32px;border:none;border-radius:14px;
      font-family:'Inter',sans-serif;font-size:.9em;font-weight:700;
      letter-spacing:2px;text-transform:uppercase;cursor:pointer;
      overflow:hidden;transition:transform .25s cubic-bezier(.34,1.56,.64,1),box-shadow .35s;
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
      background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);
      color:rgba(255,255,255,.75);text-decoration:none;
      display:flex;align-items:center;justify-content:center;
    }

    .btn-cancel:hover{
      background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.3);
      box-shadow:0 8px 24px rgba(255,255,255,.08);
    }

    /* particles */
    .particle{position:fixed;border-radius:50%;pointer-events:none;animation:particleFloat linear infinite;z-index:0;}
    @keyframes particleFloat{
      0%{transform:translateY(100vh) scale(0);opacity:0;}
      10%{opacity:.5;}90%{opacity:.3;}
      100%{transform:translateY(-100px) scale(1);opacity:0;}
    }

    @media(max-width:640px){
      .form-card{padding:32px 22px 40px;}
      .field-group{grid-template-columns:1fr;}
      .hero-inner h1{font-size:2em;}
      .btn-row{flex-direction:column;}
    }
  </style>
</head>
<body>

<div id="particles"></div>

<!-- HERO -->
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow"><i data-lucide="sparkle" class="lucide-ic"></i> YOLAZCAKE Sintang <i data-lucide="sparkle" class="lucide-ic"></i></p>
    <h1>Booking Meja</h1>
    <p class="hero-sub">Reservasi meja Anda dan nikmati momen spesial di YOLAZCAKE</p>
    <div class="hero-divider">
      <span></span><span class="diamond"><i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i></span><span></span>
    </div>
  </div>
</div>

<div class="back-link">
  <a href="../index.php"><i data-lucide="arrow-left" class="lucide-ic"></i> Kembali ke Beranda</a>
</div>

<div class="page-wrapper">
  <div class="form-card">

    <div class="new-badge"><i data-lucide="sparkles" class="lucide-ic"></i> Reservasi Baru</div>
    <h2 class="card-title">Pesan Meja Anda</h2>
    <p class="card-sub">Isi data dengan lengkap untuk mengamankan meja pilihan Anda</p>
    <div class="gold-rule-h"><span><i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i></span></div>

    <div class="info-strip">
      <span class="ii"><i data-lucide="clock" class="lucide-ic"></i></span>
      <span>Jam operasional booking: <b>08:00 – 22:00</b> &nbsp;•&nbsp; Maksimal <b>5 booking</b> per slot jam</span>
    </div>

    <form action="proses_booking.php" method="POST">

      <div class="field-group">
        <div class="field">
          <label>Nama Pemesan</label>
          <div class="field-icon-wrap">
            <span class="field-icon"><i data-lucide="user" class="lucide-ic"></i></span>
            <input type="text" name="nama_pemesan" placeholder="Nama lengkap Anda" required>
          </div>
        </div>
        <div class="field">
          <label>Nomor HP</label>
          <div class="field-icon-wrap">
            <span class="field-icon"><i data-lucide="smartphone" class="lucide-ic"></i></span>
            <input type="text" name="no_hp" placeholder="08xxxxxxxxxx" required>
          </div>
        </div>
      </div>

      <div class="field-group">
        <div class="field">
          <label>Tanggal Booking</label>
          <div class="field-icon-wrap">
            <span class="field-icon"><i data-lucide="calendar" class="lucide-ic"></i></span>
            <input type="date" name="tanggal_booking" required>
          </div>
        </div>
        <div class="field">
          <label>Jam Booking</label>
          <div class="field-icon-wrap">
            <span class="field-icon"><i data-lucide="clock" class="lucide-ic"></i></span>
            <input type="time" name="jam_booking" min="08:00" max="22:00" required>
          </div>
        </div>
      </div>

      <div class="field-group">
        <div class="field">
          <label>Jumlah Orang</label>
          <div class="field-icon-wrap">
            <span class="field-icon"><i data-lucide="users" class="lucide-ic"></i></span>
            <input type="number" name="jumlah_orang" min="1" placeholder="Contoh: 2" required>
          </div>
        </div>
        <div class="field">
          <label>Pilih Meja (Opsional)</label>
          <div class="field-icon-wrap">
            <span class="field-icon"><i data-lucide="armchair" class="lucide-ic"></i></span>
            <select name="id_meja" style="width:100%;padding:14px 14px 14px 44px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.15);border-radius:12px;color:#fff;font-size:.95em;appearance:none;cursor:pointer;">
              <option value="" style="background:#1a0a2e;">-- Pilih meja (opsional) --</option>
              <?php if($meja_list && mysqli_num_rows($meja_list) > 0): ?>
                <?php while($m = mysqli_fetch_assoc($meja_list)): ?>
                  <option value="<?= $m['id_meja'] ?>" style="background:#1a0a2e;">
                    Meja <?= htmlspecialchars($m['nomor_meja']) ?> (Kapasitas: <?= $m['kapasitas'] ?> orang)
                  </option>
                <?php endwhile; ?>
              <?php else: ?>
                <option value="" disabled style="background:#1a0a2e;">Belum ada meja tersedia</option>
              <?php endif; ?>
            </select>
          </div>
        </div>
      </div>

      <div class="field-group full">
        <div class="field">
          <label>Catatan (Opsional)</label>
          <div class="field-icon-wrap textarea-wrap">
            <span class="field-icon"><i data-lucide="file-text" class="lucide-ic"></i></span>
            <textarea name="catatan" rows="4" placeholder="Permintaan khusus, alergi makanan, perayaan, dll..."></textarea>
          </div>
        </div>
      </div>

      <div class="btn-row">
        <a href="../index.php" class="btn-premium btn-cancel"><i data-lucide="x" class="lucide-ic"></i> Batal</a>
        <button type="submit" class="btn-premium btn-save"><i data-lucide="sparkle" class="lucide-ic"></i> Booking Sekarang</button>
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

  /* batas minimal tanggal booking = hari ini */
  (function(){
    const dateInput = document.querySelector('input[name="tanggal_booking"]');
    if(dateInput){
      const today = new Date().toISOString().split('T')[0];
      dateInput.setAttribute('min', today);
    }
  })();
</script>


<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide){lucide.createIcons();}</script>
</body>
</html>
