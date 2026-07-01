<?php
include '../config/koneksi.php';

$id = $_GET['id'];

$query = mysqli_query(
    $conn,
    "SELECT * FROM member WHERE id_member='$id'"
);

$member = mysqli_fetch_assoc($query);

if(isset($_POST['update'])){

    $nama   = $_POST['nama'];
    $email  = $_POST['email'];
    $no_hp  = $_POST['no_hp'];
    $alamat = $_POST['alamat'];
    $poin   = $_POST['poin'];

    mysqli_query($conn,"
        UPDATE member
        SET
        nama='$nama',
        email='$email',
        no_hp='$no_hp',
        alamat='$alamat',
        poin='$poin'
        WHERE id_member='$id'
    ");

    echo "
    <script>
    alert('Data member berhasil diupdate');
    window.location='data_member.php';
    </script>
    ";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Member – YOLAZCAKE</title>
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

    /* ---- ambient glow bg ---- */
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

    .hero-inner {
      position: relative;
      z-index: 2;
      text-align: center;
      color: #fff;
    }

    .hero-eyebrow {
      font-size: 0.72em;
      font-weight: 500;
      letter-spacing: 5px;
      text-transform: uppercase;
      color: #D4AF37;
      margin-bottom: 10px;
      opacity: 0;
      animation: fadeSlideDown 0.8s forwards 0.3s;
    }

    .hero-inner h1 {
      font-family: 'Playfair Display', serif;
      font-size: 2.8em;
      font-weight: 700;
      background: linear-gradient(135deg, #fff 30%, #D4AF37 60%, #FFE4B5 80%, #fff);
      background-size: 200% 100%;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      animation: shimmerText 4s ease-in-out infinite, fadeSlideDown 0.9s forwards 0.5s;
      opacity: 0;
    }

    @keyframes shimmerText {
      0%   { background-position: 100% 0; }
      100% { background-position: -100% 0; }
    }

    .hero-divider {
      position: relative;
      z-index: 2;
      margin-top: 16px;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 12px;
      opacity: 0;
      animation: fadeSlideDown 0.9s forwards 0.9s;
    }

    .hero-divider span { display: block; width: 60px; height: 1px; background: linear-gradient(to right, transparent, #D4AF37); }
    .hero-divider span:last-child { background: linear-gradient(to left, transparent, #D4AF37); }
    .hero-divider .diamond { color: #D4AF37; font-size: 0.75em; letter-spacing: 4px; }

    @keyframes fadeSlideDown {
      from { opacity: 0; transform: translateY(-18px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* ---- back link ---- */
    .back-link {
      position: relative;
      z-index: 2;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin: 32px auto 0;
      padding: 0 32px;
      max-width: 700px;
      width: 100%;
    }

    .back-link a {
      font-size: 0.82em;
      font-weight: 500;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: rgba(212,175,55,0.85);
      text-decoration: none;
      border: 1px solid rgba(212,175,55,0.3);
      padding: 7px 18px;
      border-radius: 999px;
      transition: all 0.3s ease;
      background: rgba(212,175,55,0.06);
    }

    .back-link a:hover {
      background: rgba(212,175,55,0.16);
      border-color: rgba(212,175,55,0.7);
      box-shadow: 0 0 18px rgba(212,175,55,0.25);
      color: #D4AF37;
    }

    /* ---- main card ---- */
    .page-wrapper {
      position: relative;
      z-index: 1;
      display: flex;
      justify-content: center;
      padding: 28px 20px 80px;
    }

    .form-card {
      width: 100%;
      max-width: 700px;
      background: rgba(255,255,255,0.06);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 28px;
      padding: 48px 48px 52px;
      position: relative;
      overflow: hidden;
      opacity: 0;
      transform: translateY(40px);
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
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 3px;
      background: linear-gradient(90deg, #D4AF37, #ee2a7b, #D4AF37);
      background-size: 200% 100%;
      animation: goldSlide 4s linear infinite;
    }

    @keyframes goldSlide {
      0%   { background-position: 0% 0; }
      100% { background-position: 200% 0; }
    }

    @keyframes cardReveal {
      to { opacity: 1; transform: translateY(0); }
    }

    /* member id badge */
    .member-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: linear-gradient(135deg, rgba(212,175,55,0.2), rgba(212,175,55,0.08));
      border: 1px solid rgba(212,175,55,0.4);
      color: #D4AF37;
      font-size: 0.78em;
      font-weight: 600;
      letter-spacing: 1.5px;
      padding: 6px 18px;
      border-radius: 999px;
      margin-bottom: 28px;
    }

    .card-title {
      font-family: 'Playfair Display', serif;
      font-size: 1.9em;
      font-weight: 700;
      background: linear-gradient(135deg, #fff 30%, #D4AF37 70%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 6px;
    }

    .card-sub {
      font-size: 0.88em;
      color: rgba(255,255,255,0.5);
      margin-bottom: 36px;
    }

    .gold-rule-h {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 36px;
    }

    .gold-rule-h::before,
    .gold-rule-h::after {
      content: '';
      flex: 1;
      height: 1px;
      background: linear-gradient(to right, transparent, rgba(212,175,55,0.5));
    }

    .gold-rule-h::after {
      background: linear-gradient(to left, transparent, rgba(212,175,55,0.5));
    }

    .gold-rule-h span { color: #D4AF37; font-size: 0.65em; letter-spacing: 3px; }

    /* ---- form fields ---- */
    .field-group {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 20px;
    }

    .field-group.full { grid-template-columns: 1fr; }

    .field {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .field label {
      font-size: 0.72em;
      font-weight: 600;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: rgba(212,175,55,0.85);
    }

    .field-icon-wrap {
      position: relative;
    }

    .field-icon {
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 1em;
      pointer-events: none;
      opacity: 0.7;
    }

    .field-icon-wrap.textarea-wrap .field-icon {
      top: 18px;
      transform: none;
    }

    .field input,
    .field textarea {
      width: 100%;
      background: rgba(255,255,255,0.07);
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 12px;
      padding: 14px 18px 14px 46px;
      font-family: 'Inter', sans-serif;
      font-size: 0.95em;
      color: #fff;
      outline: none;
      transition: border-color 0.3s, box-shadow 0.3s, background 0.3s;
    }

    .field textarea {
      resize: vertical;
      min-height: 110px;
      line-height: 1.6;
    }

    .field input::placeholder,
    .field textarea::placeholder {
      color: rgba(255,255,255,0.3);
    }

    .field input:focus,
    .field textarea:focus {
      border-color: rgba(212,175,55,0.6);
      background: rgba(212,175,55,0.07);
      box-shadow:
        0 0 0 3px rgba(212,175,55,0.12),
        0 0 20px rgba(212,175,55,0.2);
    }

    /* poin highlight */
    .field.poin-field input {
      font-weight: 700;
      font-size: 1.05em;
      color: #D4AF37;
      text-align: center;
    }

    /* ---- submit ---- */
    .btn-row {
      display: flex;
      gap: 14px;
      margin-top: 36px;
    }

    .btn-premium {
      flex: 1;
      position: relative;
      padding: 16px 32px;
      border: none;
      border-radius: 14px;
      font-family: 'Inter', sans-serif;
      font-size: 0.9em;
      font-weight: 700;
      letter-spacing: 2px;
      text-transform: uppercase;
      cursor: pointer;
      overflow: hidden;
      transition: transform 0.25s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.35s;
    }

    .btn-premium::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
      transform: translateX(-100%);
      transition: transform 0.5s ease;
    }

    .btn-premium:hover::before { transform: translateX(100%); }

    .btn-premium:hover {
      transform: translateY(-3px) scale(1.02);
    }

    .btn-save {
      background: linear-gradient(135deg, #D4AF37 0%, #b8860b 50%, #D4AF37 100%);
      background-size: 200% 100%;
      color: #1e0e3a;
      animation: goldSlide 3s linear infinite;
      box-shadow: 0 8px 28px rgba(212,175,55,0.35), 0 0 40px rgba(212,175,55,0.18);
    }

    .btn-save:hover {
      box-shadow: 0 12px 40px rgba(212,175,55,0.55), 0 0 60px rgba(212,175,55,0.3);
    }

    .btn-cancel {
      background: rgba(255,255,255,0.07);
      border: 1px solid rgba(255,255,255,0.15);
      color: rgba(255,255,255,0.75);
      text-decoration: none;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .btn-cancel:hover {
      background: rgba(255,255,255,0.12);
      border-color: rgba(255,255,255,0.3);
      box-shadow: 0 8px 24px rgba(255,255,255,0.08);
    }

    /* ---- floating particles ---- */
    .particle {
      position: fixed;
      border-radius: 50%;
      pointer-events: none;
      animation: particleFloat linear infinite;
      z-index: 0;
    }

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
    }
  </style>
</head>
<body>

<!-- Floating particles -->
<div id="particles"></div>

<!-- HERO -->
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow">✦ YOLAZCAKE Sintang ✦</p>
    <h1>Edit Data Member</h1>
    <div class="hero-divider">
      <span></span>
      <span class="diamond">✦ ✦ ✦</span>
      <span></span>
    </div>
  </div>
</div>

<div class="back-link">
  <a href="data_member.php">← Kembali ke Data Member</a>
</div>

<div class="page-wrapper">
  <div class="form-card">

    <div class="member-badge">✦ MEMBER #<?= htmlspecialchars($member['id_member']); ?> ✦</div>
    <h2 class="card-title">Edit Informasi Member</h2>
    <p class="card-sub">Perbarui data member dengan lengkap dan akurat</p>

    <div class="gold-rule-h"><span>✦ ✦ ✦</span></div>

    <form method="POST">

      <div class="field-group">
        <div class="field">
          <label>Nama Lengkap</label>
          <div class="field-icon-wrap">
            <span class="field-icon">👤</span>
            <input type="text" name="nama"
                   value="<?= htmlspecialchars($member['nama']); ?>"
                   placeholder="Nama member" required>
          </div>
        </div>
        <div class="field">
          <label>Email</label>
          <div class="field-icon-wrap">
            <span class="field-icon">✉️</span>
            <input type="email" name="email"
                   value="<?= htmlspecialchars($member['email']); ?>"
                   placeholder="email@domain.com" required>
          </div>
        </div>
      </div>

      <div class="field-group">
        <div class="field">
          <label>No. Handphone</label>
          <div class="field-icon-wrap">
            <span class="field-icon">📱</span>
            <input type="text" name="no_hp"
                   value="<?= htmlspecialchars($member['no_hp']); ?>"
                   placeholder="08xxxxxxxxxx" required>
          </div>
        </div>
        <div class="field poin-field">
          <label>Poin Member</label>
          <div class="field-icon-wrap">
            <span class="field-icon">⭐</span>
            <input type="number" name="poin"
                   value="<?= htmlspecialchars($member['poin']); ?>"
                   placeholder="0" required>
          </div>
        </div>
      </div>

      <div class="field-group full">
        <div class="field">
          <label>Alamat</label>
          <div class="field-icon-wrap textarea-wrap">
            <span class="field-icon">📍</span>
            <textarea name="alamat"
                      placeholder="Alamat lengkap member..."><?= htmlspecialchars($member['alamat']); ?></textarea>
          </div>
        </div>
      </div>

      <div class="btn-row">
        <a href="data_member.php" class="btn-premium btn-cancel">✕ Batal</a>
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
