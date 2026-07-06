<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Menuju Member – YOLAZCAKE</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
  body{
    min-height:100vh;
    font-family:'Inter',sans-serif;
    background:linear-gradient(160deg,#1e0e3a 0%,#2d1560 50%,#1a0a2e 100%);
    display:flex;align-items:center;justify-content:center;
    padding:20px;
  }
  .card{
    max-width:480px;width:100%;
    background:rgba(255,255,255,.04);
    border:1px solid rgba(212,175,55,.25);
    border-radius:20px;
    padding:40px 32px;
    text-align:center;
    color:#fff;
    backdrop-filter:blur(6px);
  }
  .icon{font-size:3em;margin-bottom:14px;}
  h1{
    font-family:'Playfair Display',serif;
    font-size:1.6em;font-weight:700;
    background:linear-gradient(135deg,#fff 30%,#D4AF37 60%,#FFE4B5 80%,#fff);
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
    margin-bottom:10px;
  }
  p.sub{color:rgba(255,255,255,.7);font-size:.95em;line-height:1.6;margin-bottom:28px;}
  .progress-wrap{
    background:rgba(255,255,255,.08);
    border-radius:999px;height:16px;overflow:hidden;margin-bottom:12px;
    border:1px solid rgba(212,175,55,.2);
  }
  .progress-bar{
    height:100%;border-radius:999px;
    background:linear-gradient(90deg,#D4AF37,#FFE4B5);
    width:<?= $persen ?>%;
    transition:width .6s ease;
  }
  .progress-label{
    font-size:.85em;color:#D4AF37;font-weight:600;margin-bottom:28px;
  }
  .stat-row{
    display:flex;justify-content:center;gap:28px;margin-bottom:28px;flex-wrap:wrap;
  }
  .stat{
    background:rgba(255,255,255,.05);border-radius:14px;padding:14px 20px;min-width:100px;
  }
  .stat .num{font-size:1.6em;font-weight:700;color:#D4AF37;}
  .stat .lbl{font-size:.75em;color:rgba(255,255,255,.6);margin-top:2px;}
  .actions{display:flex;flex-direction:column;gap:12px;}
  .btn{
    display:block;padding:13px 20px;border-radius:12px;text-decoration:none;
    font-weight:600;font-size:.95em;transition:.2s;
  }
  .btn-gold{
    background:linear-gradient(135deg,#D4AF37,#b8860b);color:#1a0a2e;
  }
  .btn-gold:hover{opacity:.9;}
  .btn-outline{
    border:1px solid rgba(255,255,255,.25);color:#fff;background:transparent;
  }
  .btn-outline:hover{background:rgba(255,255,255,.06);}
</style>
</head>
<body>
  <div class="card">
    <div class="icon">🎯</div>
    <h1>Menuju Member YOLAZCAKE</h1>
    <p class="sub">
      Member itu bukan cuma daftar akun — buktikan dulu langgananmu!
      Booking meja atau pesan online sebanyak <strong><?= $syarat ?>x</strong>,
      dan kamu otomatis jadi member dengan poin & reward eksklusif.
    </p>

    <div class="progress-wrap"><div class="progress-bar"></div></div>
    <div class="progress-label"><?= $progres ?> / <?= $syarat ?> transaksi (<?= $persen ?>%)</div>

    <div class="stat-row">
      <div class="stat">
        <div class="num"><?= $progres ?></div>
        <div class="lbl">Sudah Dilakukan</div>
      </div>
      <div class="stat">
        <div class="num"><?= $sisa ?></div>
        <div class="lbl">Kurang Lagi</div>
      </div>
    </div>

    <div class="actions">
      <a href="../booking/booking.php" class="btn btn-gold">🪑 Booking Meja Sekarang</a>
      <a href="../pemesanan/menuu.php" class="btn btn-gold">🛒 Pesan Menu Sekarang</a>
      <a href="../index.php" class="btn btn-outline">🏠 Kembali ke Beranda</a>
    </div>
  </div>
</body>
</html>
