<?php
include '../config/koneksi.php';

$id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM pemesanan WHERE id_pemesanan= ?");

$stmt->bind_param("i", $id);

$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();

if(!$data){
    die("Data pemesanan tidak ditemukan");
}

if(isset($_POST['update'])){

    $status_pesanan    = mysqli_real_escape_string($conn, $_POST['status_pesanan']);
    $status_pembayaran = mysqli_real_escape_string($conn, $_POST['status_pembayaran']);

    mysqli_query($conn,"
    UPDATE pemesanan
    SET status_pesanan='$status_pesanan',
        status_pembayaran='$status_pembayaran'
    WHERE id_pemesanan='$id'
    ");

    echo "
    <script>
    alert('Status pemesanan berhasil diperbarui');
    window.location='data_pemesanan.php';
    </script>
    ";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Pemesanan – YOLAZCAKE</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      min-height: 100vh;
      font-family: 'Inter', sans-serif;
      background: linear-gradient(160deg, #1e0e3a 0%, #2d1560 50%, #1a0a2e 100%);
      position: relative;
      overflow-x: hidden;
    }

    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background:
        radial-gradient(ellipse at 20% 30%, rgba(212,175,55,0.10) 0%, transparent 55%),
        radial-gradient(ellipse at 80% 70%, rgba(232,160,191,0.10) 0%, transparent 55%);
      pointer-events: none;
      z-index: 0;
    }

    /* ---- hero banner ---- */
    .page-hero {
      position: relative;
      height: 240px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      background: linear-gradient(135deg, #2b1a11 0%, #4a2c1a 40%, #6d3e26 70%, #3a1f0e 100%);
      z-index: 1;
    }

    .page-hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background:
        radial-gradient(ellipse at 30% 50%, rgba(212,175,55,0.18) 0%, transparent 60%),
        radial-gradient(ellipse at 75% 40%, rgba(232,160,191,0.15) 0%, transparent 55%);
      animation: heroAurora 8s ease-in-out infinite alternate;
    }

    @keyframes heroAurora {
      0%   { opacity: 0.6; transform: scale(1); }
      100% { opacity: 1;   transform: scale(1.08) translateX(10px); }
    }

    .sparkle {
      position: absolute;
      border-radius: 50%;
      pointer-events: none;
      animation: floatDot linear infinite;
    }

    @keyframes floatDot {
      0%   { transform: translateY(0) rotate(0deg);   opacity: 0; }
      20%  { opacity: 1; }
      80%  { opacity: 0.8; }
      100% { transform: translateY(-260px) rotate(360deg); opacity: 0; }
    }

    .hero-inner { position: relative; z-index: 2; text-align: center; color: #fff; }

    .hero-eyebrow {
      font-size: 0.72em; font-weight: 500; letter-spacing: 5px; text-transform: uppercase;
      color: #D4AF37; margin-bottom: 10px;
      opacity: 0; animation: fadeSlideDown 0.8s forwards 0.3s;
    }

    .hero-inner h1 {
      font-family: 'Playfair Display', serif; font-size: 2.8em; font-weight: 700;
      background: linear-gradient(135deg, #fff 30%, #D4AF37 60%, #FFE4B5 80%, #fff);
      background-size: 200% 100%;
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
      animation: shimmerText 4s ease-in-out infinite, fadeSlideDown 0.9s forwards 0.5s;
      opacity: 0;
    }

    @keyframes shimmerText { 0% { background-position: 100% 0; } 100% { background-position: -100% 0; } }

    .hero-divider {
      position: relative; z-index: 2; margin-top: 16px;
      display: flex; justify-content: center; align-items: center; gap: 12px;
      opacity: 0; animation: fadeSlideDown 0.9s forwards 0.9s;
    }
    .hero-divider span { display: block; width: 60px; height: 1px; background: linear-gradient(to right, transparent, #D4AF37); }
    .hero-divider span:last-child { background: linear-gradient(to left, transparent, #D4AF37); }
    .hero-divider .diamond { color: #D4AF37; font-size: 0.75em; letter-spacing: 4px; }

    @keyframes fadeSlideDown { from { opacity: 0; transform: translateY(-18px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes goldSlide { 0% { background-position: 0% 0; } 100% { background-position: 200% 0; } }

    /* ---- back link ---- */
    .back-link {
      position: relative; z-index: 2;
      display: inline-flex; align-items: center; gap: 8px;
      margin: 32px auto 0; padding: 0 32px; max-width: 700px; width: 100%;
    }
    .back-link a {
      font-size: 0.82em; font-weight: 500; letter-spacing: 1.5px; text-transform: uppercase;
      color: rgba(212,175,55,0.85); text-decoration: none;
      border: 1px solid rgba(212,175,55,0.3); padding: 7px 18px; border-radius: 999px;
      transition: all 0.3s ease; background: rgba(212,175,55,0.06);
    }
    .back-link a:hover {
      background: rgba(212,175,55,0.16); border-color: rgba(212,175,55,0.7);
      box-shadow: 0 0 18px rgba(212,175,55,0.25); color: #D4AF37;
    }

    /* ---- main card ---- */
    .page-wrapper { position: relative; z-index: 1; display: flex; justify-content: center; padding: 28px 20px 80px; }

    .form-card {
      width: 100%; max-width: 700px;
      background: rgba(255,255,255,0.06);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 28px;
      padding: 48px 48px 52px;
      position: relative; overflow: hidden;
      opacity: 0; transform: translateY(40px);
      animation: cardReveal 0.85s cubic-bezier(.22,.68,0,1.2) forwards 0.6s;
      transition: border-color 0.45s, box-shadow 0.45s;
    }
    .form-card:hover {
      border-color: rgba(212,175,55,0.35);
      box-shadow:
        0 25px 65px rgba(0,0,0,0.35),
        0 0 35px rgba(212,175,55,0.28),
        0 0 70px rgba(212,175,55,0.14),
        0 0 110px rgba(212,175,55,0.06);
    }
    .form-card::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
      background: linear-gradient(90deg, #D4AF37, #ee2a7b, #D4AF37);
      background-size: 200% 100%; animation: goldSlide 4s linear infinite;
    }
    @keyframes cardReveal { to { opacity: 1; transform: translateY(0); } }

    .kode-badge-lg {
      display: inline-flex; align-items: center; gap: 8px;
      background: linear-gradient(135deg, rgba(212,175,55,0.2), rgba(212,175,55,0.08));
      border: 1px solid rgba(212,175,55,0.4);
      color: #D4AF37; font-size: 0.78em; font-weight: 600; letter-spacing: 1.5px;
      padding: 6px 18px; border-radius: 999px; margin-bottom: 28px;
    }

    .card-title {
      font-family: 'Playfair Display', serif; font-size: 1.9em; font-weight: 700;
      background: linear-gradient(135deg, #fff 30%, #D4AF37 70%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
      margin-bottom: 6px;
    }
    .card-sub { font-size: 0.88em; color: rgba(255,255,255,0.5); margin-bottom: 30px; }

    .gold-rule-h { display: flex; align-items: center; gap: 12px; margin-bottom: 30px; }
    .gold-rule-h::before, .gold-rule-h::after { content: ''; flex: 1; height: 1px; background: linear-gradient(to right, transparent, rgba(212,175,55,0.4)); }
    .gold-rule-h::after { background: linear-gradient(to left, transparent, rgba(212,175,55,0.4)); }
    .gold-rule-h span { color: #D4AF37; font-size: 0.65em; letter-spacing: 3px; white-space: nowrap; }

    /* ---- info summary (read-only) ---- */
    .info-box {
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 18px;
      padding: 6px 24px;
      margin-bottom: 34px;
    }
    .info-line {
      display: flex; align-items: center; justify-content: space-between;
      gap: 16px; padding: 13px 0;
      border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    .info-line:last-child { border-bottom: none; }
    .info-key {
      font-size: 0.72em; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase;
      color: rgba(212,175,55,0.75); display: flex; align-items: center; gap: 8px; white-space: nowrap;
    }
    .info-val { font-size: 0.92em; color: rgba(255,255,255,0.88); text-align: right; }
    .info-val.gold { color: #D4AF37; font-weight: 600; }
    .info-val.green { color: #6efabc; font-weight: 700; }

    /* ---- field ---- */
    .field-group { display: grid; grid-template-columns: 1fr 1fr; gap: 22px; margin-bottom: 22px; }
    .field-group.full { grid-template-columns: 1fr; }
    .field { display: flex; flex-direction: column; gap: 8px; }
    .field label {
      font-size: 0.72em; font-weight: 600; letter-spacing: 2px; text-transform: uppercase;
      color: rgba(212,175,55,0.85);
    }
    .field-icon-wrap { position: relative; }
    .field-icon {
      position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
      font-size: 1em; pointer-events: none; opacity: 0.7; z-index: 1;
    }
    .field select {
      width: 100%; appearance: none; -webkit-appearance: none;
      background: rgba(255,255,255,0.07);
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 12px;
      padding: 14px 40px 14px 46px;
      font-family: 'Inter', sans-serif; font-size: 0.95em; font-weight: 600;
      color: #fff; cursor: pointer; outline: none;
      transition: border-color 0.3s, box-shadow 0.3s, background 0.3s;
    }
    .field select option { background: #2d1560; color: #fff; }
    .field select:focus {
      border-color: rgba(212,175,55,0.6);
      background-color: rgba(212,175,55,0.07);
      box-shadow: 0 0 0 3px rgba(212,175,55,0.12), 0 0 20px rgba(212,175,55,0.2);
    }
    .select-arrow {
      position: absolute; right: 16px; top: 50%; transform: translateY(-50%);
      color: rgba(212,175,55,0.7); font-size: 0.8em; pointer-events: none;
    }

    /* ---- submit ---- */
    .btn-row { display: flex; gap: 14px; margin-top: 36px; }
    .btn-premium {
      flex: 1; position: relative; padding: 16px 32px; border: none; border-radius: 14px;
      font-family: 'Inter', sans-serif; font-size: 0.9em; font-weight: 700;
      letter-spacing: 2px; text-transform: uppercase; cursor: pointer; overflow: hidden;
      transition: transform 0.25s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.35s;
      text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .btn-premium::before {
      content: ''; position: absolute; inset: 0;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
      transform: translateX(-100%); transition: transform 0.5s ease;
    }
    .btn-premium:hover::before { transform: translateX(100%); }
    .btn-premium:hover { transform: translateY(-3px) scale(1.02); }

    .btn-save {
      background: linear-gradient(135deg, #D4AF37 0%, #b8860b 50%, #D4AF37 100%);
      background-size: 200% 100%; color: #1e0e3a;
      animation: goldSlide 3s linear infinite;
      box-shadow: 0 8px 28px rgba(212,175,55,0.35), 0 0 40px rgba(212,175,55,0.18);
    }
    .btn-save:hover { box-shadow: 0 12px 40px rgba(212,175,55,0.55), 0 0 60px rgba(212,175,55,0.3); }

    .btn-cancel {
      background: rgba(255,255,255,0.07);
      border: 1px solid rgba(255,255,255,0.15);
      color: rgba(255,255,255,0.75);
    }
    .btn-cancel:hover {
      background: rgba(255,255,255,0.12); border-color: rgba(255,255,255,0.3);
      box-shadow: 0 8px 24px rgba(255,255,255,0.08);
    }

    /* ---- floating particles ---- */
    .particle { position: fixed; border-radius: 50%; pointer-events: none; animation: particleFloat linear infinite; z-index: 0; }
    @keyframes particleFloat {
      0%   { transform: translateY(100vh) scale(0); opacity: 0; }
      10%  { opacity: 0.5; }
      90%  { opacity: 0.3; }
      100% { transform: translateY(-100px) scale(1); opacity: 0; }
    }

    /* ---- responsive ---- */
    @media (max-width: 640px) {
      .form-card { padding: 32px 22px 40px; }
      .field-group { grid-template-columns: 1fr; }
      .hero-inner h1 { font-size: 2em; }
      .btn-row { flex-direction: column; }
      .info-line { flex-direction: column; align-items: flex-start; gap: 4px; }
      .info-val { text-align: left; }
    }
  </style>
</head>
<body>

<div id="particles"></div>

<!-- HERO -->
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow">✦ YOLAZCAKE Sintang ✦</p>
    <h1>Edit Pemesanan</h1>
    <div class="hero-divider">
      <span></span><span class="diamond">✦ ✦ ✦</span><span></span>
    </div>
  </div>
</div>

<div class="back-link">
  <a href="data_pemesanan.php">← Kembali ke Data Pemesanan</a>
</div>

<div class="page-wrapper">
  <div class="form-card">

    <div class="kode-badge-lg">✦ <?= htmlspecialchars($data['kode_pesanan']) ?> ✦</div>
    <h2 class="card-title">Perbarui Status Pesanan</h2>
    <p class="card-sub">Ubah status pesanan &amp; pembayaran pelanggan</p>

    <div class="gold-rule-h"><span>✦ ✦ ✦</span></div>

    <!-- INFO READ-ONLY -->
    <div class="info-box">
      <div class="info-line">
        <span class="info-key">👤 Nama Pemesan</span>
        <span class="info-val"><?= htmlspecialchars($data['nama_pemesan']) ?></span>
      </div>
      <div class="info-line">
        <span class="info-key">📱 No. HP</span>
        <span class="info-val"><?= htmlspecialchars($data['no_hp']) ?></span>
      </div>
      <div class="info-line">
        <span class="info-key">📅 Tanggal</span>
        <span class="info-val"><?= htmlspecialchars($data['tanggal']) ?></span>
      </div>
      <div class="info-line">
        <span class="info-key">💳 Metode Bayar</span>
        <span class="info-val gold"><?= htmlspecialchars($data['metode_pembayaran'] ?? '-') ?></span>
      </div>
      <div class="info-line">
        <span class="info-key">💰 Total Harga</span>
        <span class="info-val green">Rp <?= number_format($data['total_harga'],0,',','.') ?></span>
      </div>
    </div>

    <form method="POST">

      <div class="field-group">
        <div class="field">
          <label>Status Pesanan</label>
          <div class="field-icon-wrap">
            <span class="field-icon">📦</span>
            <select name="status_pesanan" required>
              <?php
              $opsi_pesanan = ['Menunggu','Diproses','Siap Diambil','Selesai','Dibatalkan'];
              foreach($opsi_pesanan as $opt){
                  $sel = ($data['status_pesanan'] === $opt) ? 'selected' : '';
                  echo "<option value=\"$opt\" $sel>$opt</option>";
              }
              ?>
            </select>
            <span class="select-arrow">▾</span>
          </div>
        </div>

        <div class="field">
          <label>Status Pembayaran</label>
          <div class="field-icon-wrap">
            <span class="field-icon">🔖</span>
            <select name="status_pembayaran" required>
              <?php
              $opsi_bayar = ['Menunggu','Lunas','Gagal'];
              foreach($opsi_bayar as $opt){
                  $sel = ($data['status_pembayaran'] === $opt) ? 'selected' : '';
                  echo "<option value=\"$opt\" $sel>$opt</option>";
              }
              ?>
            </select>
            <span class="select-arrow">▾</span>
          </div>
        </div>
      </div>

      <div class="btn-row">
        <a href="data_pemesanan.php" class="btn-premium btn-cancel">✕ Batal</a>
        <button type="submit" name="update" class="btn-premium btn-save">✦ Simpan Perubahan</button>
      </div>

    </form>
  </div>
</div>

<script>
  /* sparkles hero */
  (function(){
    const hero = document.getElementById('pageHero');
    const colors = ['#D4AF37','#FFE4B5','#E8A0BF','#fff','#f9ce34','#b8860b'];
    for(let i = 0; i < 20; i++){
      const d = document.createElement('div');
      d.className = 'sparkle';
      const s = Math.random() * 5 + 2;
      d.style.cssText = `width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;bottom:${Math.random()*30}%;animation-duration:${4+Math.random()*7}s;animation-delay:${Math.random()*5}s;opacity:0;`;
      hero.appendChild(d);
    }
  })();

  /* floating particles */
  (function(){
    const c = document.getElementById('particles');
    const colors = ['rgba(212,175,55,0.4)','rgba(232,160,191,0.35)','rgba(255,255,255,0.15)'];
    for(let i = 0; i < 16; i++){
      const p = document.createElement('div');
      p.className = 'particle';
      const s = Math.random() * 5 + 2;
      p.style.cssText = `width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;animation-duration:${10+Math.random()*12}s;animation-delay:${Math.random()*10}s;`;
      c.appendChild(p);
    }
  })();
</script>
</body>
</html>
