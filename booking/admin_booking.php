<?php
require_once '../config/koneksi.php';

$query = mysqli_query($conn, "SELECT * FROM booking ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Booking – YOLAZCAKE</title>
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

    /* hero */
    .page-hero {
      position: relative;
      height: 260px;
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
      0%   { opacity:0.6; transform:scale(1); }
      100% { opacity:1;   transform:scale(1.08) translateX(10px); }
    }

    .sparkle { position:absolute; border-radius:50%; pointer-events:none; animation:floatDot linear infinite; }

    @keyframes floatDot {
      0%   { transform:translateY(0) rotate(0deg); opacity:0; }
      20%  { opacity:1; }
      80%  { opacity:0.8; }
      100% { transform:translateY(-280px) rotate(360deg); opacity:0; }
    }

    .hero-inner { position:relative; z-index:2; text-align:center; color:#fff; }

    .hero-eyebrow {
      font-size:0.72em; font-weight:500; letter-spacing:5px; text-transform:uppercase;
      color:#D4AF37; margin-bottom:10px;
      opacity:0; animation:fadeSlideDown 0.8s forwards 0.3s;
    }

    .hero-inner h1 {
      font-family:'Playfair Display',serif; font-size:3em; font-weight:700;
      background:linear-gradient(135deg, #fff 30%, #D4AF37 60%, #FFE4B5 80%, #fff);
      background-size:200% 100%;
      -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
      animation:shimmerText 4s ease-in-out infinite, fadeSlideDown 0.9s forwards 0.5s;
      opacity:0;
    }

    .hero-inner .hero-sub {
      font-size:0.9em; color:rgba(255,255,255,0.65); margin-top:10px;
      opacity:0; animation:fadeSlideDown 0.9s forwards 0.9s;
    }

    .hero-divider {
      margin-top:16px; display:flex; justify-content:center; align-items:center; gap:12px;
      opacity:0; animation:fadeSlideDown 0.9s forwards 1.1s;
    }

    .hero-divider span { display:block; width:60px; height:1px; background:linear-gradient(to right,transparent,#D4AF37); }
    .hero-divider span:last-child { background:linear-gradient(to left,transparent,#D4AF37); }
    .hero-divider .diamond { color:#D4AF37; font-size:0.75em; letter-spacing:4px; }

    @keyframes shimmerText { 0%{background-position:100% 0;} 100%{background-position:-100% 0;} }
    @keyframes fadeSlideDown { from{opacity:0;transform:translateY(-18px);} to{opacity:1;transform:translateY(0);} }

    /* content wrapper */
    .page-wrapper {
      position:relative; z-index:1;
      padding: 36px 28px 80px;
      max-width: 1280px;
      margin: 0 auto;
    }

    /* top bar */
    .top-bar {
      display:flex; justify-content:space-between; align-items:center;
      flex-wrap: wrap; gap: 14px;
      margin-bottom:28px;
      opacity:0; animation:cardReveal 0.7s forwards 0.7s;
    }

    .section-eyebrow {
      font-size:0.72em; font-weight:600; letter-spacing:4px;
      text-transform:uppercase; color:#D4AF37;
    }

    .btn-back {
      display:inline-flex; align-items:center; gap:8px;
      padding:10px 22px;
      background:rgba(212,175,55,0.1);
      border:1px solid rgba(212,175,55,0.3);
      color:#D4AF37; font-family:'Inter',sans-serif;
      font-size:0.82em; font-weight:600; letter-spacing:1px;
      border-radius:999px; text-decoration:none;
      transition:transform 0.25s, box-shadow 0.3s, background 0.3s;
    }

    .btn-back:hover {
      transform:translateX(-3px);
      background:rgba(212,175,55,0.2);
      box-shadow:0 6px 20px rgba(212,175,55,0.25);
    }

    @keyframes goldSlide { 0%{background-position:0% 0;} 100%{background-position:200% 0;} }

    /* stats bar */
    .stats-row {
      display:grid; grid-template-columns:repeat(4,1fr); gap:16px;
      margin-bottom:28px;
      opacity:0; animation:cardReveal 0.7s forwards 0.85s;
    }

    .stat-card {
      background:rgba(255,255,255,0.06);
      backdrop-filter:blur(16px);
      border:1px solid rgba(255,255,255,0.1);
      border-radius:18px; padding:20px 22px;
      display:flex; align-items:center; gap:14px;
      transition:border-color 0.35s, box-shadow 0.35s, transform 0.3s;
      cursor: default;
    }

    .stat-card:hover {
      border-color:rgba(212,175,55,0.35);
      box-shadow:0 0 24px rgba(212,175,55,0.2);
      transform: translateY(-3px);
    }

    .stat-card::before {
      content:''; display:block;
      width:3px; height:40px; border-radius:999px;
      background:linear-gradient(to bottom, #D4AF37, #b8860b);
      flex-shrink:0;
    }

    .stat-icon { font-size:1.5em; }
    .stat-val { font-family:'Playfair Display',serif; font-size:1.7em; font-weight:700; color:#D4AF37; line-height:1; }
    .stat-lbl { font-size:0.75em; color:rgba(255,255,255,0.5); margin-top:2px; letter-spacing:0.5px; }

    /* table card */
    .table-card {
      background:rgba(255,255,255,0.05);
      backdrop-filter:blur(20px);
      border:1px solid rgba(255,255,255,0.1);
      border-radius:24px;
      overflow:hidden;
      position:relative;
      opacity:0; animation:cardReveal 0.8s forwards 1.0s;
    }

    .table-card::before {
      content:''; position:absolute; top:0; left:0; right:0; height:3px;
      background:linear-gradient(90deg, #D4AF37, #ee2a7b, #D4AF37);
      background-size:200% 100%;
      animation:goldSlide 4s linear infinite;
    }

    @keyframes cardReveal { to{opacity:1;transform:translateY(0);} }

    .table-scroll { width:100%; overflow-x:auto; }

    table { width:100%; border-collapse:collapse; min-width: 920px; }

    thead tr {
      background:rgba(212,175,55,0.12);
      border-bottom:1px solid rgba(212,175,55,0.25);
    }

    thead th {
      padding:16px 18px;
      font-size:0.68em; font-weight:700; letter-spacing:2px;
      text-transform:uppercase; color:rgba(212,175,55,0.9);
      text-align:left;
      white-space: nowrap;
    }

    tbody tr {
      border-bottom:1px solid rgba(255,255,255,0.06);
      transition:background 0.25s;
    }

    tbody tr:last-child { border-bottom:none; }
    tbody tr:hover { background:rgba(212,175,55,0.06); }

    tbody td {
      padding:15px 18px;
      font-size:0.88em; color:rgba(255,255,255,0.8);
      vertical-align:middle;
    }

    .td-no {
      color:rgba(212,175,55,0.6);
      font-weight:700; font-size:0.82em; text-align:center; width:50px;
    }

    .td-nama { font-weight:600; color:#fff; }

    .td-catatan {
      color:rgba(255,255,255,0.55); font-size:0.85em; max-width: 220px;
      white-space: normal;
    }

    .badge-orang {
      display:inline-flex; align-items:center; gap:5px;
      background:rgba(99,102,241,0.15);
      border:1px solid rgba(99,102,241,0.35);
      border-radius:999px; padding:4px 12px;
      font-size:0.82em; color:#a5b4fc; font-weight:600;
    }

    /* status badges */
    .status-badge {
      display:inline-flex; align-items:center; gap:6px;
      padding:6px 14px; border-radius:999px;
      font-size:0.74em; font-weight:700; letter-spacing:0.5px;
      white-space: nowrap;
    }

    .status-pending {
      background: rgba(212,175,55,0.15);
      border: 1px solid rgba(212,175,55,0.4);
      color: #D4AF37;
    }

    .status-dikonfirmasi {
      background: rgba(99,250,180,0.15);
      border: 1px solid rgba(99,250,180,0.4);
      color: #6efabc;
    }

    .status-dibatalkan {
      background: rgba(239,68,68,0.14);
      border: 1px solid rgba(239,68,68,0.35);
      color: #fca5a5;
    }

    /* action buttons */
    .action-cell { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }

    .btn-act {
      display:inline-flex; align-items:center; gap:5px;
      padding:7px 14px; border-radius:8px;
      font-size:0.75em; font-weight:600; letter-spacing:0.5px;
      text-decoration:none; border:1px solid transparent;
      transition:transform 0.2s, box-shadow 0.25s, background 0.25s;
      cursor: pointer;
    }

    .btn-act:hover { transform:translateY(-2px); }

    .btn-konfirmasi {
      background:rgba(99,250,180,0.16); border-color:rgba(99,250,180,0.4);
      color:#6efabc;
    }
    .btn-konfirmasi:hover { background:rgba(99,250,180,0.3); box-shadow:0 4px 16px rgba(99,250,180,0.25); }

    .btn-batalkan {
      background:rgba(212,175,55,0.16); border-color:rgba(212,175,55,0.4);
      color:#D4AF37;
    }
    .btn-batalkan:hover { background:rgba(212,175,55,0.3); box-shadow:0 4px 16px rgba(212,175,55,0.3); }

    .btn-hapus {
      background:rgba(239,68,68,0.14); border-color:rgba(239,68,68,0.35);
      color:#fca5a5;
    }
    .btn-hapus:hover { background:rgba(239,68,68,0.28); box-shadow:0 4px 16px rgba(239,68,68,0.25); }

    .td-dash { color: rgba(255,255,255,0.3); font-size: 0.85em; }

    /* empty state */
    .empty-state {
      text-align:center; padding:60px 20px; color:rgba(255,255,255,0.4);
    }
    .empty-state .es-icon { font-size:3em; margin-bottom:16px; opacity:0.5; }
    .empty-state p { font-family:'Playfair Display',serif; font-size:1.2em; }

    /* particles */
    .particle { position:fixed; border-radius:50%; pointer-events:none; animation:particleFloat linear infinite; z-index:0; }
    @keyframes particleFloat {
      0%   { transform:translateY(100vh) scale(0); opacity:0; }
      10%  { opacity:0.5; }
      90%  { opacity:0.3; }
      100% { transform:translateY(-100px) scale(1); opacity:0; }
    }

    @media(max-width:768px){
      .stats-row { grid-template-columns:repeat(2,1fr); }
      .hero-inner h1 { font-size:2em; }
      .page-wrapper { padding:24px 16px 60px; }
      .action-cell { flex-direction:column; }
    }
  </style>
</head>
<body>

<div id="particles"></div>

<!-- HERO -->
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow">✦ YOLAZCAKE Sintang ✦</p>
    <h1>Data Booking</h1>
    <p class="hero-sub">Kelola reservasi meja pelanggan YOLAZCAKE</p>
    <div class="hero-divider">
      <span></span><span class="diamond">✦ ✦ ✦</span><span></span>
    </div>
  </div>
</div>

<div class="page-wrapper">

  <!-- top bar -->
  <div class="top-bar">
    <span class="section-eyebrow">✦ Daftar Booking</span>
    <a href="../dashboard.php" class="btn-back">📊 Dashboard Admin</a><a href="../index.php" class="btn-back" style="margin-left:8px;">🏠 Website</a>
  </div>

  <!-- stats -->
  <?php
    $stat_query = mysqli_query($conn, "
      SELECT
        COUNT(*) AS total,
        SUM(status='Pending') AS total_pending,
        SUM(status='Dikonfirmasi') AS total_konfirmasi,
        SUM(status='Dibatalkan') AS total_batal
      FROM booking
    ");
    $stats = mysqli_fetch_assoc($stat_query);
    $total_booking     = $stats['total'] ?? 0;
    $total_pending     = $stats['total_pending'] ?? 0;
    $total_konfirmasi  = $stats['total_konfirmasi'] ?? 0;
    $total_batal       = $stats['total_batal'] ?? 0;
  ?>
  <div class="stats-row">
    <div class="stat-card">
      <span class="stat-icon">📋</span>
      <div>
        <div class="stat-val"><?= $total_booking; ?></div>
        <div class="stat-lbl">Total Booking</div>
      </div>
    </div>
    <div class="stat-card">
      <span class="stat-icon">⏳</span>
      <div>
        <div class="stat-val"><?= $total_pending; ?></div>
        <div class="stat-lbl">Menunggu Konfirmasi</div>
      </div>
    </div>
    <div class="stat-card">
      <span class="stat-icon">✅</span>
      <div>
        <div class="stat-val"><?= $total_konfirmasi; ?></div>
        <div class="stat-lbl">Dikonfirmasi</div>
      </div>
    </div>
    <div class="stat-card">
      <span class="stat-icon">🚫</span>
      <div>
        <div class="stat-val"><?= $total_batal; ?></div>
        <div class="stat-lbl">Dibatalkan</div>
      </div>
    </div>
  </div>

  <!-- table -->
  <div class="table-card">
    <div class="table-scroll">
    <table>
      <thead>
        <tr>
          <th style="text-align:center;">No</th>
          <th>Nama Pemesan</th>
          <th>No. HP</th>
          <th>Tanggal</th>
          <th>Jam</th>
          <th>Orang</th>
          <th>Catatan</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $no = 1;
        $rows = mysqli_num_rows($query);
        if($rows > 0):
          while($data = mysqli_fetch_assoc($query)):

            $status = $data['status'];
            $statusClass = 'status-pending';
            $statusIcon  = '⏳';
            if($status === 'Dikonfirmasi'){ $statusClass = 'status-dikonfirmasi'; $statusIcon = '✅'; }
            elseif($status === 'Dibatalkan'){ $statusClass = 'status-dibatalkan'; $statusIcon = '🚫'; }
        ?>
        <tr>
          <td class="td-no"><?= $no++; ?></td>
          <td class="td-nama"><?= htmlspecialchars($data['nama_pemesan']); ?></td>
          <td style="color:rgba(255,255,255,0.6);"><?= htmlspecialchars($data['no_hp']); ?></td>
          <td style="color:rgba(255,255,255,0.6);"><?= htmlspecialchars($data['tanggal_booking']); ?></td>
          <td style="color:rgba(255,255,255,0.6);"><?= htmlspecialchars($data['jam_booking']); ?></td>
          <td><span class="badge-orang">👥 <?= htmlspecialchars($data['jumlah_orang']); ?></span></td>
          <td class="td-catatan"><?= $data['catatan'] !== '' ? htmlspecialchars($data['catatan']) : '<span class="td-dash">—</span>'; ?></td>
          <td>
            <span class="status-badge <?= $statusClass; ?>"><?= $statusIcon; ?> <?= htmlspecialchars($status); ?></span>
          </td>
          <td>
            <div class="action-cell">
              <?php if ($status == 'Pending') : ?>
                <a href="ubah_status.php?id=<?= $data['id_booking']; ?>&status=Dikonfirmasi"
                   class="btn-act btn-konfirmasi"
                   onclick="return confirm('Konfirmasi booking ini?')">✅ Konfirmasi</a>

                <a href="ubah_status.php?id=<?= $data['id_booking']; ?>&status=Dibatalkan"
                   class="btn-act btn-batalkan"
                   onclick="return confirm('Batalkan booking ini?')">🚫 Batalkan</a>

                <a href="hapus_booking.php?id=<?= $data['id_booking']; ?>"
                   class="btn-act btn-hapus"
                   onclick="return confirm('Yakin ingin menghapus data booking ini?')">🗑️ Hapus</a>
              <?php else : ?>
                <span class="td-dash">—</span>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endwhile; else: ?>
        <tr>
          <td colspan="9">
            <div class="empty-state">
              <div class="es-icon">📋</div>
              <p>Belum ada data booking</p>
            </div>
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>

</div>

<script>
  (function(){
    const hero = document.getElementById('pageHero');
    const colors = ['#D4AF37','#FFE4B5','#E8A0BF','#fff','#f9ce34','#b8860b'];
    for(let i = 0; i < 22; i++){
      const d = document.createElement('div');
      d.className = 'sparkle';
      const s = Math.random()*5+2;
      d.style.cssText = `width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;bottom:${Math.random()*30}%;animation-duration:${4+Math.random()*7}s;animation-delay:${Math.random()*5}s;opacity:0;`;
      hero.appendChild(d);
    }
  })();

  (function(){
    const c = document.getElementById('particles');
    const colors = ['rgba(212,175,55,0.4)','rgba(232,160,191,0.3)','rgba(255,255,255,0.15)'];
    for(let i=0;i<16;i++){
      const p=document.createElement('div'); p.className='particle';
      const s=Math.random()*5+2;
      p.style.cssText=`width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;animation-duration:${10+Math.random()*12}s;animation-delay:${Math.random()*10}s;`;
      c.appendChild(p);
    }
  })();
</script>

</body>
</html>
