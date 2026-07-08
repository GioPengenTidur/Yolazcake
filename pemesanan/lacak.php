<?php
session_start();
include '../config/koneksi.php';

$id_get   = (int)($_GET['id'] ?? 0);
$kode_get = trim($_GET['kode'] ?? '');
$cari     = trim($_POST['cari_kode'] ?? '');

$data = null;
$error = null;

if ($id_get > 0 && $kode_get !== '') {
    $stmt = $conn->prepare("SELECT * FROM pemesanan WHERE id_pemesanan = ? AND kode_pesanan = ?");
    $stmt->bind_param("is", $id_get, $kode_get);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$data) { $error = 'Pesanan tidak ditemukan.'; }
} elseif ($cari !== '') {
    $stmt = $conn->prepare("SELECT * FROM pemesanan WHERE kode_pesanan = ?");
    $stmt->bind_param("s", $cari);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$data) { $error = 'Kode pesanan tidak ditemukan. Cek lagi kode yang kamu masukkan.'; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="../assets/css/lucide-icons.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lacak Pesanan – YOLAZCAKE</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    :root{--gold:#D4AF37;--gold-d:#b8860b;--mint:#6efabc;--muted:rgba(255,255,255,.5);}
    body{min-height:100vh;font-family:'Inter',sans-serif;
      background:linear-gradient(160deg,#1e0e3a 0%,#2d1560 50%,#1a0a2e 100%);color:#fff;}
    body::before{content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
      background:radial-gradient(ellipse at 20% 30%,rgba(212,175,55,.10) 0%,transparent 55%),
                 radial-gradient(ellipse at 80% 70%,rgba(232,160,191,.10) 0%,transparent 55%);}
    @keyframes pulseBadge{0%,100%{box-shadow:0 0 0 0 rgba(110,250,188,.5);}50%{box-shadow:0 0 0 8px rgba(110,250,188,0);}}
    @keyframes fadeUp{from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:translateY(0);}}

    .page-hero{position:relative;height:220px;display:flex;flex-direction:column;align-items:center;justify-content:center;
      background:linear-gradient(135deg,#2b1a11 0%,#4a2c1a 40%,#6d3e26 70%,#3a1f0e 100%);z-index:1;text-align:center;}
    .page-hero h1{font-family:'Playfair Display',serif;font-size:2.2em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,var(--gold) 60%,#FFE4B5 80%,#fff);
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
    .page-hero p{font-size:.85em;color:rgba(255,255,255,.6);margin-top:8px;}

    .wrap{position:relative;z-index:1;max-width:640px;margin:0 auto;padding:32px 20px 90px;}
    .back-link{display:inline-flex;gap:8px;align-items:center;color:var(--gold);text-decoration:none;
      font-size:.82em;margin-bottom:20px;border:1px solid rgba(212,175,55,.3);padding:8px 18px;border-radius:999px;}

    .card{background:rgba(255,255,255,.06);backdrop-filter:blur(18px);border:1px solid rgba(255,255,255,.1);
      border-radius:20px;padding:26px;margin-bottom:20px;opacity:0;animation:fadeUp .6s forwards;}

    .search-row{display:flex;gap:10px;flex-wrap:wrap;}
    .search-row input{flex:1;min-width:180px;padding:13px 16px;border-radius:12px;border:1px solid rgba(255,255,255,.15);
      background:rgba(255,255,255,.06);color:#fff;font-size:.9em;}
    .btn-gold{background:linear-gradient(135deg,var(--gold),var(--gold-d));color:#1e0e3a;font-weight:700;
      border:none;border-radius:12px;padding:13px 24px;cursor:pointer;font-size:.85em;letter-spacing:1px;text-transform:uppercase;}

    .error-box{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.35);color:#fca5a5;
      padding:14px 18px;border-radius:12px;font-size:.85em;margin-bottom:18px;}

    .kode-badge{font-family:'Playfair Display',serif;font-size:1.4em;font-weight:700;color:var(--gold);}
    .live-tag{display:inline-flex;align-items:center;gap:6px;font-size:.65em;color:var(--mint);
      text-transform:uppercase;letter-spacing:1px;font-weight:700;}
    .live-dot{width:6px;height:6px;border-radius:50%;background:var(--mint);animation:pulseBadge 1.6s ease-in-out infinite;}

    .info-row{display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid rgba(255,255,255,.07);font-size:.85em;}
    .info-row:last-child{border-bottom:none;}
    .info-row .l{color:var(--muted);}

    .timeline-steps{display:flex;justify-content:space-between;margin:22px 0 4px;position:relative;}
    .tl-step{flex:1;text-align:center;position:relative;}
    .tl-dot{width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;
      margin:0 auto 8px;font-size:1em;background:rgba(255,255,255,.08);border:2px solid rgba(255,255,255,.15);}
    .tl-dot.done{background:rgba(110,250,188,.18);border-color:var(--mint);}
    .tl-dot.active{background:rgba(212,175,55,.2);border-color:var(--gold);animation:pulseBadge 1.6s ease-in-out infinite;}
    .tl-lbl{font-size:.68em;color:var(--muted);line-height:1.3;}
    .tl-lbl.active-lbl{color:var(--gold);font-weight:700;}
    .tl-connector{position:absolute;top:19px;left:-50%;width:100%;height:2px;background:rgba(255,255,255,.12);z-index:-1;}
    .tl-step:first-child .tl-connector{display:none;}
  </style>
</head>
<body>

<div class="page-hero">
  <h1><i data-lucide="map-pin" class="lucide-ic"></i> Lacak Pesanan</h1>
  <p>Pantau status pesananmu real-time, tanpa perlu telpon kasir</p>
</div>

<div class="wrap">
  <a href="menuu.php" class="back-link"><i data-lucide="arrow-left" class="lucide-ic"></i> Kembali ke Menu</a>

  <?php if(!$data): ?>
  <div class="card">
    <form method="POST">
      <label style="font-size:.8em;color:rgba(255,255,255,.6);display:block;margin-bottom:8px;">Masukkan kode pesanan (mis. ORD20260708123456)</label>
      <div class="search-row">
        <input type="text" name="cari_kode" placeholder="Kode Pesanan" value="<?= htmlspecialchars($cari) ?>" required>
        <button type="submit" class="btn-gold">Cari</button>
      </div>
    </form>
    <?php if($error): ?><div class="error-box" style="margin-top:16px;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  </div>
  <?php else: ?>
  <div class="card" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
    <div>
      <div style="font-size:.68em;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);">Kode Pesanan</div>
      <div class="kode-badge"><?= htmlspecialchars($data['kode_pesanan']) ?></div>
    </div>
    <span class="live-tag"><span class="live-dot"></span> Live</span>
  </div>

  <div class="card">
    <div class="info-row"><span class="l">Nama</span><span><?= htmlspecialchars($data['nama_pemesan']) ?></span></div>
    <?php if($data['nomor_meja']): ?><div class="info-row"><span class="l">Meja</span><span>Meja <?= htmlspecialchars($data['nomor_meja']) ?></span></div><?php endif; ?>
    <div class="info-row"><span class="l">Total</span><span>Rp <?= number_format($data['total_harga'],0,',','.') ?></span></div>
    <div class="info-row"><span class="l">Pembayaran</span><span id="statusBayar"><?= htmlspecialchars($data['status_pembayaran']) ?></span></div>
  </div>

  <div class="card">
    <div style="font-size:.85em;font-weight:700;margin-bottom:6px;">Status Pesanan</div>
    <div class="timeline-steps" id="timelineSteps">
      <div class="tl-step" data-step="terima"><div class="tl-connector"></div><div class="tl-dot done">✅</div><div class="tl-lbl">Diterima</div></div>
      <div class="tl-step" data-step="Diproses"><div class="tl-connector"></div><div class="tl-dot pending">⏳</div><div class="tl-lbl">Diproses</div></div>
      <div class="tl-step" data-step="Siap Diambil"><div class="tl-connector"></div><div class="tl-dot pending">📦</div><div class="tl-lbl">Siap Diambil</div></div>
      <div class="tl-step" data-step="Selesai"><div class="tl-connector"></div><div class="tl-dot pending">🎉</div><div class="tl-lbl">Selesai</div></div>
    </div>
    <p id="note" style="text-align:center;font-size:.78em;color:var(--muted);margin-top:14px;">Halaman ini auto-update, nggak perlu refresh.</p>
  </div>
  <?php endif; ?>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide){lucide.createIcons();}</script>

<?php if($data): ?>
<script>
(function(){
  const idPesanan = <?= (int)$data['id_pemesanan'] ?>;
  const kodePesanan = <?= json_encode($data['kode_pesanan']) ?>;
  const wrap = document.getElementById('timelineSteps');
  const note = document.getElementById('note');
  const order = ['Menunggu','Diproses','Siap Diambil','Selesai'];

  function applyStatus(status, statusBayar){
    document.getElementById('statusBayar').textContent = statusBayar;
    const idx = order.indexOf(status);
    wrap.querySelectorAll('[data-step]').forEach(step=>{
      const key = step.dataset.step;
      const dot = step.querySelector('.tl-dot');
      const lbl = step.querySelector('.tl-lbl');
      if(key === 'terima') return;
      const stepIdx = order.indexOf(key);
      dot.classList.remove('done','active','pending');
      lbl.classList.remove('active-lbl');
      if(status === 'Dibatalkan'){ dot.classList.add('pending'); dot.textContent = '✖️'; return; }
      if(stepIdx < idx || status === 'Selesai'){ dot.classList.add('done'); dot.textContent='✅'; }
      else if(stepIdx === idx){ dot.classList.add('active'); lbl.classList.add('active-lbl'); }
      else { dot.classList.add('pending'); }
    });
    if(status === 'Selesai') note.textContent = '🎉 Pesanan selesai. Terima kasih!';
    if(status === 'Dibatalkan') note.textContent = 'Pesanan ini dibatalkan.';
  }

  function poll(){
    fetch('status_ajax.php?id='+idPesanan+'&kode='+encodeURIComponent(kodePesanan))
      .then(r=>r.json()).then(d=>{ if(d && d.status_pesanan){ applyStatus(d.status_pesanan, d.status_pembayaran); } })
      .catch(()=>{});
  }
  poll();
  setInterval(poll, 6000);
})();
</script>
<?php endif; ?>
</body>
</html>
