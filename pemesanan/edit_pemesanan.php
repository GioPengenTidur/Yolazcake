<?php
include '../config/koneksi.php';

$id = $_GET['id'] ?? 0;

$query = mysqli_query($conn,
    "SELECT * FROM pemesanan WHERE id_pemesanan='$id'");
$data = mysqli_fetch_assoc($query);

if(!$data){
    echo "<script>alert('Data tidak ditemukan!');window.location='data_pemesanan.php';</script>";
    exit();
}

if(isset($_POST['update'])){
    $status_pesanan    = mysqli_real_escape_string($conn, $_POST['status_pesanan']);
    $nama_pemesan      = mysqli_real_escape_string($conn, $_POST['nama_pemesan']);
    $no_hp             = mysqli_real_escape_string($conn, $_POST['no_hp']);

    mysqli_query($conn,"
        UPDATE pemesanan
        SET status_pesanan='$status_pesanan',
            nama_pemesan='$nama_pemesan',
            no_hp='$no_hp'
        WHERE id_pemesanan='$id'
    ");

    echo "
    <script>
    document.getElementById('successOverlay').style.display='flex';
    setTimeout(function(){ window.location='data_pemesanan.php'; }, 2200);
    </script>
    ";
    // refresh data setelah update
    $data['status_pesanan'] = $status_pesanan;
    $data['nama_pemesan']   = $nama_pemesan;
    $data['no_hp']          = $no_hp;
}

// Status config
$statusList = [
    'Menunggu'    => ['icon'=>'⏳','color'=>'#ffb432','bg'=>'rgba(255,180,50,.15)','border'=>'rgba(255,180,50,.35)'],
    'Diproses'    => ['icon'=>'⚙️','color'=>'#60a5fa','bg'=>'rgba(96,165,250,.15)', 'border'=>'rgba(96,165,250,.35)'],
    'Siap Diambil'=> ['icon'=>'📦','color'=>'#c084fc','bg'=>'rgba(192,132,252,.15)','border'=>'rgba(192,132,252,.35)'],
    'Selesai'     => ['icon'=>'✅','color'=>'#6efabc','bg'=>'rgba(99,250,180,.15)', 'border'=>'rgba(99,250,180,.35)'],
    'Dibatalkan'  => ['icon'=>'❌','color'=>'#f87171','bg'=>'rgba(248,113,113,.15)','border'=>'rgba(248,113,113,.35)'],
];
$currentStatus = $data['status_pesanan'] ?? 'Menunggu';
$cs = $statusList[$currentStatus] ?? $statusList['Menunggu'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Pemesanan – YOLAZCAKE</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}

    body{
      min-height:100vh;
      font-family:'Inter',sans-serif;
      background:linear-gradient(160deg,#1e0e3a 0%,#2d1560 50%,#1a0a2e 100%);
      position:relative;overflow-x:hidden;
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

    /* ── PAGE WRAPPER ── */
    .page-wrapper{
      position:relative;z-index:1;
      display:flex;flex-direction:column;align-items:center;
      padding:28px 20px 120px;
      max-width:860px;margin:0 auto;
    }

    /* ── TOP BAR ── */
    .top-bar{
      width:100%;display:flex;align-items:center;justify-content:space-between;
      margin-bottom:32px;flex-wrap:wrap;gap:14px;
    }
    .section-title{
      font-family:'Playfair Display',serif;font-size:1.6em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,#D4AF37 70%);
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
    }
    .kode-badge{
      display:inline-flex;align-items:center;gap:8px;
      background:rgba(212,175,55,.1);
      border:1px solid rgba(212,175,55,.3);
      color:#D4AF37;font-size:.8em;font-weight:700;letter-spacing:2px;
      padding:7px 20px;border-radius:999px;font-family:'Inter',monospace;
    }

    /* ── GOLD RULE ── */
    .gold-rule{display:flex;align-items:center;gap:12px;width:100%;margin-bottom:28px;}
    .gold-rule::before,.gold-rule::after{content:'';flex:1;height:1px;background:linear-gradient(to right,transparent,rgba(212,175,55,.4));}
    .gold-rule::after{background:linear-gradient(to left,transparent,rgba(212,175,55,.4));}
    .gold-rule span{color:#D4AF37;font-size:.65em;letter-spacing:3px;white-space:nowrap;}

    /* ── INFO GRID ── */
    .info-grid{
      width:100%;display:grid;grid-template-columns:repeat(3,1fr);gap:14px;
      margin-bottom:24px;
    }
    .info-tile{
      background:rgba(255,255,255,.05);
      border:1px solid rgba(255,255,255,.09);
      border-radius:16px;padding:18px 20px;
      opacity:0;transform:translateY(20px);
      transition:border-color .4s,box-shadow .4s;
    }
    .info-tile.visible{opacity:1;transform:translateY(0);}
    .info-tile:hover{
      border-color:rgba(212,175,55,.25);
      box-shadow:0 8px 28px rgba(0,0,0,.25),0 0 18px rgba(212,175,55,.1);
    }
    .info-tile-label{font-size:.7em;letter-spacing:1.5px;text-transform:uppercase;color:rgba(212,175,55,.7);margin-bottom:6px;}
    .info-tile-val{font-size:.95em;font-weight:600;color:#fff;line-height:1.3;}
    .info-tile-val.gold{color:#D4AF37;}

    /* Status pill inside tile */
    .status-pill{
      display:inline-flex;align-items:center;gap:6px;
      padding:4px 14px;border-radius:999px;font-size:.82em;font-weight:700;
      border:1px solid;
    }

    /* ── EDIT CARD ── */
    .edit-card{
      width:100%;
      background:rgba(255,255,255,.06);
      backdrop-filter:blur(20px);
      border:1px solid rgba(255,255,255,.1);
      border-radius:24px;
      position:relative;overflow:hidden;
      padding:36px 36px 32px;
      opacity:0;animation:fadeSlideDown .9s forwards .55s;
    }
    .edit-card::before{
      content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);
      background-size:200% 100%;animation:goldSlide 4s linear infinite;
    }
    .edit-card-title{
      font-family:'Playfair Display',serif;font-size:1.2em;font-weight:700;
      color:#fff;margin-bottom:26px;
      display:flex;align-items:center;gap:10px;
    }
    .edit-card-title::after{
      content:'';flex:1;height:1px;
      background:linear-gradient(to right,rgba(212,175,55,.3),transparent);
    }

    /* ── FORM ELEMENTS ── */
    .form-section{margin-bottom:28px;}
    .form-section-label{
      font-size:.72em;font-weight:700;letter-spacing:2px;text-transform:uppercase;
      color:rgba(212,175,55,.7);margin-bottom:14px;
      display:flex;align-items:center;gap:8px;
    }
    .form-section-label::after{content:'';flex:1;height:1px;background:rgba(212,175,55,.15);}

    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
    .form-group{margin-bottom:0;}
    .form-label{
      display:block;font-size:.75em;font-weight:600;letter-spacing:1.5px;
      text-transform:uppercase;color:rgba(212,175,55,.85);margin-bottom:8px;
    }
    .form-input{
      width:100%;padding:13px 18px;
      background:rgba(255,255,255,.06);
      border:1px solid rgba(212,175,55,.22);
      border-radius:12px;
      font-family:'Inter',sans-serif;font-size:.9em;color:#fff;
      outline:none;transition:border-color .3s,box-shadow .3s;
    }
    .form-input::placeholder{color:rgba(255,255,255,.28);}
    .form-input:focus{
      border-color:rgba(212,175,55,.6);
      box-shadow:0 0 0 3px rgba(212,175,55,.1);
    }

    /* ── STATUS SELECTOR ── */
    .status-grid{
      display:grid;grid-template-columns:repeat(5,1fr);gap:10px;
      margin-bottom:4px;
    }
    .status-opt{display:none;}
    .status-opt-label{
      display:flex;flex-direction:column;align-items:center;gap:6px;
      padding:14px 8px;border-radius:14px;cursor:pointer;
      border:2px solid rgba(255,255,255,.08);
      background:rgba(255,255,255,.04);
      transition:all .25s;text-align:center;
      user-select:none;
    }
    .status-opt-label:hover{
      border-color:rgba(212,175,55,.3);
      background:rgba(212,175,55,.06);
    }
    .status-opt:checked + .status-opt-label{
      border-color:var(--s-color);
      background:var(--s-bg);
      box-shadow:0 0 0 1px var(--s-color),0 6px 20px rgba(0,0,0,.2);
    }
    .status-opt-icon{font-size:1.5em;}
    .status-opt-text{font-size:.72em;font-weight:700;color:rgba(255,255,255,.7);letter-spacing:.5px;}
    .status-opt:checked + .status-opt-label .status-opt-text{color:var(--s-color);}

    /* ── BTN PREMIUM ── */
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
    .btn-gold{
      background:linear-gradient(135deg,#D4AF37 0%,#b8860b 50%,#D4AF37 100%);
      background-size:200% 100%;color:#1e0e3a;
      animation:goldSlide 3s linear infinite;
      box-shadow:0 8px 28px rgba(212,175,55,.35),0 0 40px rgba(212,175,55,.18);
    }
    .btn-gold:hover{box-shadow:0 12px 40px rgba(212,175,55,.55),0 0 60px rgba(212,175,55,.3);}
    .btn-ghost{
      background:rgba(255,255,255,.06);
      border:1px solid rgba(255,255,255,.15);
      color:rgba(255,255,255,.7);
    }
    .btn-ghost:hover{background:rgba(255,255,255,.1);color:#fff;}

    /* ── SUCCESS OVERLAY ── */
    #successOverlay{
      display:none;position:fixed;inset:0;z-index:9999;
      background:rgba(14,6,30,.88);backdrop-filter:blur(10px);
      align-items:center;justify-content:center;flex-direction:column;gap:20px;
    }
    .success-icon{font-size:4em;animation:popIn .5s cubic-bezier(.34,1.56,.64,1);}
    .success-title{
      font-family:'Playfair Display',serif;font-size:1.8em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,#D4AF37 70%);
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
    }
    .success-sub{color:rgba(255,255,255,.5);font-size:.88em;}
    .success-ring{
      width:110px;height:110px;border-radius:50%;
      background:rgba(212,175,55,.1);
      border:2px solid rgba(212,175,55,.4);
      display:flex;align-items:center;justify-content:center;
      animation:ringPulse 1.5s ease-in-out infinite;
    }
    @keyframes ringPulse{0%,100%{box-shadow:0 0 0 0 rgba(212,175,55,.4);}50%{box-shadow:0 0 0 18px rgba(212,175,55,0);}}
    @keyframes popIn{from{transform:scale(0) rotate(-15deg);}to{transform:scale(1) rotate(0);}}

    /* ── PARTICLES ── */
    .particle{position:fixed;border-radius:50%;pointer-events:none;animation:particleFloat linear infinite;z-index:0;}
    @keyframes particleFloat{
      0%{transform:translateY(100vh) scale(0);opacity:0;}
      10%{opacity:.5;}90%{opacity:.3;}
      100%{transform:translateY(-100px) scale(1);opacity:0;}
    }

    /* ── FOOTER ── */
    .site-footer{
      position:relative;z-index:1;text-align:center;
      padding:36px 20px;font-size:.8em;
      color:rgba(255,255,255,.5);
      border-top:1px solid rgba(255,255,255,.06);
      line-height:1.8;
    }

    /* ── RESPONSIVE ── */
    @media(max-width:700px){
      .hero-inner h1{font-size:2em;}
      .info-grid{grid-template-columns:1fr 1fr;}
      .status-grid{grid-template-columns:repeat(3,1fr);}
      .form-row{grid-template-columns:1fr;}
      .edit-card{padding:24px 18px;}
    }
    @media(max-width:420px){
      .info-grid{grid-template-columns:1fr;}
      .status-grid{grid-template-columns:repeat(2,1fr);}
    }
  </style>
</head>
<body>

<div id="particles"></div>

<!-- SUCCESS OVERLAY -->
<div id="successOverlay">
  <div class="success-ring">
    <div class="success-icon">✦</div>
  </div>
  <div class="success-title">Status Diperbarui!</div>
  <div class="success-sub">Mengalihkan ke halaman data pemesanan…</div>
</div>

<!-- ═══════════════ HERO ═══════════════ -->
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow">✦ YOLAZCAKE Sintang ✦</p>
    <h1>Edit Pemesanan</h1>
    <p class="hero-sub">Perbarui informasi dan status pesanan pelanggan</p>
    <div class="hero-divider">
      <span></span><span class="diamond">✦ ✦ ✦</span><span></span>
    </div>
  </div>
</div>

<div class="back-link">
  <a href="data_pemesanan.php">← Kembali ke Data Pemesanan</a>
</div>

<!-- ═══════════════ PAGE WRAPPER ═══════════════ -->
<div class="page-wrapper">

  <!-- TOP BAR -->
  <div class="top-bar">
    <div>
      <div class="kode-badge">📋 <?= htmlspecialchars($data['kode_pesanan']) ?></div>
      <h2 class="section-title" style="margin-top:10px;">Detail & Edit Pesanan</h2>
    </div>
  </div>

  <div class="gold-rule"><span>✦ ✦ ✦</span></div>

  <!-- ─── INFO TILES ─── -->
  <div class="info-grid">

    <div class="info-tile">
      <div class="info-tile-label">👤 Nama Pemesan</div>
      <div class="info-tile-val"><?= htmlspecialchars($data['nama_pemesan']) ?></div>
    </div>

    <div class="info-tile">
      <div class="info-tile-label">📱 Nomor HP</div>
      <div class="info-tile-val"><?= htmlspecialchars($data['no_hp']) ?></div>
    </div>

    <div class="info-tile">
      <div class="info-tile-label">📅 Tanggal</div>
      <div class="info-tile-val"><?= htmlspecialchars($data['tanggal']) ?></div>
    </div>

    <div class="info-tile">
      <div class="info-tile-label">💰 Total Harga</div>
      <div class="info-tile-val gold">Rp <?= number_format($data['total_harga'],0,',','.') ?></div>
    </div>

    <div class="info-tile">
      <div class="info-tile-label">💳 Metode Pembayaran</div>
      <div class="info-tile-val"><?= htmlspecialchars($data['metode_pembayaran']) ?></div>
    </div>

    <div class="info-tile">
      <div class="info-tile-label">🏷️ Status Pembayaran</div>
      <div class="info-tile-val">
        <?php if($data['status_pembayaran'] == 'Lunas'): ?>
          <span class="status-pill" style="color:#6efabc;border-color:rgba(99,250,180,.4);background:rgba(99,250,180,.1);">✓ Lunas</span>
        <?php else: ?>
          <span class="status-pill" style="color:#ffb432;border-color:rgba(255,180,50,.4);background:rgba(255,180,50,.1);">⏳ <?= htmlspecialchars($data['status_pembayaran']) ?></span>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <!-- ─── EDIT FORM ─── -->
  <div class="edit-card">
    <div class="edit-card-title">✦ Form Edit Pemesanan</div>

    <form method="POST">

      <!-- Data Pemesan -->
      <div class="form-section">
        <div class="form-section-label">Data Pemesan</div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Nama Pemesan</label>
            <input type="text" name="nama_pemesan" class="form-input"
                   value="<?= htmlspecialchars($data['nama_pemesan']) ?>"
                   placeholder="Nama pemesan" required>
          </div>
          <div class="form-group">
            <label class="form-label">Nomor WhatsApp / HP</label>
            <input type="tel" name="no_hp" class="form-input"
                   value="<?= htmlspecialchars($data['no_hp']) ?>"
                   placeholder="Contoh: 08123456789" required>
          </div>
        </div>
      </div>

      <!-- Status Pesanan -->
      <div class="form-section">
        <div class="form-section-label">Status Pesanan</div>
        <div class="status-grid">

          <?php
          $statusDefs = [
            'Menunggu'     => ['icon'=>'⏳', 'text'=>'Menunggu',     'color'=>'#ffb432','bg'=>'rgba(255,180,50,.15)'],
            'Diproses'     => ['icon'=>'⚙️', 'text'=>'Diproses',     'color'=>'#60a5fa','bg'=>'rgba(96,165,250,.15)'],
            'Siap Diambil' => ['icon'=>'📦', 'text'=>'Siap Diambil', 'color'=>'#c084fc','bg'=>'rgba(192,132,252,.15)'],
            'Selesai'      => ['icon'=>'✅', 'text'=>'Selesai',      'color'=>'#6efabc','bg'=>'rgba(99,250,180,.15)'],
            'Dibatalkan'   => ['icon'=>'❌', 'text'=>'Dibatalkan',   'color'=>'#f87171','bg'=>'rgba(248,113,113,.15)'],
          ];
          foreach($statusDefs as $val => $s):
            $checked = ($data['status_pesanan'] == $val) ? 'checked' : '';
          ?>
          <div>
            <input type="radio" name="status_pesanan" id="s_<?= str_replace(' ','_',$val) ?>"
                   value="<?= $val ?>" class="status-opt" <?= $checked ?>>
            <label for="s_<?= str_replace(' ','_',$val) ?>" class="status-opt-label"
                   style="--s-color:<?= $s['color'] ?>;--s-bg:<?= $s['bg'] ?>;">
              <span class="status-opt-icon"><?= $s['icon'] ?></span>
              <span class="status-opt-text"><?= $s['text'] ?></span>
            </label>
          </div>
          <?php endforeach; ?>

        </div>
      </div>

      <!-- Action Buttons -->
      <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;">
        <button type="submit" name="update" class="btn-premium btn-gold" style="flex:1;min-width:180px;justify-content:center;padding:15px 28px;">
          ✦ Simpan Perubahan
        </button>
        <a href="data_pemesanan.php" class="btn-premium btn-ghost" style="justify-content:center;">
          ← Batal
        </a>
      </div>

    </form>
  </div>

</div>

<!-- ═══════════════ FOOTER ═══════════════ -->
<div class="site-footer">
  © 2026 YOLAZCAKE Sintang • Cafe • Bakery • Boutique<br>
  Jl. Lintas Melawi, Ladang, Sintang, Kalimantan Barat<br>
  WA: 0815-7815-7888
</div>

<script>
  /* Hero sparkles */
  (function(){
    const hero = document.getElementById('pageHero');
    const colors = ['#D4AF37','#FFE4B5','#E8A0BF','#fff','#f9ce34','#b8860b'];
    for(let i=0;i<22;i++){
      const d = document.createElement('div'); d.className='sparkle';
      const s = Math.random()*5+2;
      d.style.cssText=`width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;bottom:${Math.random()*30}%;animation-duration:${4+Math.random()*7}s;animation-delay:${Math.random()*5}s;opacity:0;`;
      hero.appendChild(d);
    }
  })();

  /* Background particles */
  (function(){
    const c = document.getElementById('particles');
    const colors=['rgba(212,175,55,.4)','rgba(232,160,191,.3)','rgba(255,255,255,.15)'];
    for(let i=0;i<16;i++){
      const p=document.createElement('div');p.className='particle';
      const s=Math.random()*5+2;
      p.style.cssText=`width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;animation-duration:${10+Math.random()*12}s;animation-delay:${Math.random()*10}s;`;
      c.appendChild(p);
    }
  })();

  /* Info tiles reveal */
  (function(){
    const tiles = document.querySelectorAll('.info-tile');
    const io = new IntersectionObserver(entries=>{
      entries.forEach((e,i)=>{
        if(e.isIntersecting){
          setTimeout(()=>{
            e.target.style.transition='opacity .5s ease, transform .5s ease, border-color .4s, box-shadow .4s';
            e.target.classList.add('visible');
          }, i*80);
          io.unobserve(e.target);
        }
      });
    },{threshold:0.1});
    tiles.forEach(t=>io.observe(t));
  })();
</script>

</body>
</html>
