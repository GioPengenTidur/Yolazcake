<?php
session_start();
require_once __DIR__.'/../config/staff_guard.php';
require_staff_login();
require_once '../config/koneksi.php';

// Tandai satu klaim sebagai sudah diambil member (verifikasi di kasir).
if (isset($_POST['tandai_selesai'])) {
    $id = (int) $_POST['tandai_selesai'];

    $stmt = $conn->prepare("SELECT rk.*, m.nama FROM reward_klaim rk LEFT JOIN member m ON m.id_member = rk.id_member WHERE rk.id_klaim=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $klaim = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($klaim && $klaim['status'] === 'Menunggu') {
        $stmt = $conn->prepare("UPDATE reward_klaim SET status='Selesai' WHERE id_klaim=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    include 'success_overlay.php';
    tampilkan_sukses([
        'proses_judul' => 'Memverifikasi Kode…',
        'proses_sub'   => 'Menandai reward sudah diambil',
        'sukses_judul' => 'Reward Terverifikasi! ✅',
        'sukses_sub'   => $klaim ? ('"'.htmlspecialchars($klaim['nama_reward']).'" untuk '.htmlspecialchars($klaim['nama'] ?? 'member').' sudah ditandai selesai.') : 'Klaim sudah diproses.',
        'redirect'     => 'verifikasi_reward.php',
        'tombol_label' => 'Lanjutkan ke Verifikasi Reward',
    ]);
    exit;
}

// Pencarian cepat pakai kode redeem (buat dicek langsung di kasir).
$kode_cari   = trim($_GET['kode'] ?? '');
$hasil_cari  = null;
if ($kode_cari !== '') {
    $stmt = $conn->prepare("SELECT rk.*, m.nama, m.no_hp FROM reward_klaim rk LEFT JOIN member m ON m.id_member = rk.id_member WHERE rk.kode_redeem = ?");
    $stmt->bind_param("s", $kode_cari);
    $stmt->execute();
    $hasil_cari = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Filter status buat tabel daftar klaim.
$filter_status = $_GET['status'] ?? 'Menunggu';
$where = '';
if ($filter_status === 'Menunggu' || $filter_status === 'Selesai') {
    $where = "WHERE rk.status='".$conn->real_escape_string($filter_status)."'";
}
$query = $conn->query(
    "SELECT rk.*, m.nama FROM reward_klaim rk LEFT JOIN member m ON m.id_member = rk.id_member $where ORDER BY rk.created_at DESC LIMIT 100"
);

$stats = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS total, SUM(status='Menunggu') AS menunggu, SUM(status='Selesai') AS selesai, COALESCE(SUM(poin_terpakai),0) AS total_poin
     FROM reward_klaim"
));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="../assets/css/lucide-icons.css">
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verifikasi Klaim Reward – YOLAZCAKE</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{min-height:100vh;font-family:'Inter',sans-serif;
      background:linear-gradient(160deg,#1e0e3a 0%,#2d1560 50%,#1a0a2e 100%);color:#fff;overflow-x:hidden;}
    body::before{content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
      background:radial-gradient(ellipse at 20% 30%,rgba(212,175,55,.10) 0%,transparent 55%),
                 radial-gradient(ellipse at 80% 70%,rgba(232,160,191,.10) 0%,transparent 55%);}

    .page-hero{position:relative;height:220px;display:flex;flex-direction:column;align-items:center;justify-content:center;overflow:hidden;
      background:linear-gradient(135deg,#2b1a11 0%,#4a2c1a 40%,#6d3e26 70%,#3a1f0e 100%);z-index:1;}
    .page-hero::before{content:'';position:absolute;inset:0;
      background:radial-gradient(ellipse at 30% 50%,rgba(212,175,55,.18) 0%,transparent 60%),
                 radial-gradient(ellipse at 75% 40%,rgba(232,160,191,.15) 0%,transparent 55%);}
    .hero-inner{position:relative;z-index:2;text-align:center;}
    .hero-eyebrow{font-size:.72em;font-weight:500;letter-spacing:5px;text-transform:uppercase;color:#D4AF37;margin-bottom:10px;}
    .hero-inner h1{font-family:'Playfair Display',serif;font-size:2.6em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,#D4AF37 60%,#FFE4B5 80%,#fff);
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
    .hero-sub{font-size:.9em;color:rgba(255,255,255,.65);margin-top:10px;}

    .page-wrapper{position:relative;z-index:1;padding:36px 28px 80px;max-width:1100px;margin:0 auto;}
    .btn-back{display:inline-flex;align-items:center;gap:8px;padding:10px 22px;
      background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.3);color:#D4AF37;
      font-size:.82em;font-weight:600;border-radius:999px;text-decoration:none;transition:transform .25s,background .3s;margin-bottom:24px;}
    .btn-back:hover{transform:translateX(-3px);background:rgba(212,175,55,.2);}

    .stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;}
    .stat-card{background:rgba(255,255,255,.06);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.1);
      border-radius:18px;padding:20px 22px;display:flex;align-items:center;gap:14px;}
    .stat-card::before{content:'';display:block;width:3px;height:40px;border-radius:999px;
      background:linear-gradient(to bottom,#D4AF37,#b8860b);flex-shrink:0;}
    .stat-val{font-family:'Playfair Display',serif;font-size:1.7em;font-weight:700;color:#D4AF37;line-height:1;}
    .stat-lbl{font-size:.75em;color:rgba(255,255,255,.5);margin-top:2px;}

    /* SEARCH BOX */
    .search-card{background:rgba(255,255,255,.05);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.1);
      border-radius:20px;padding:22px 24px;margin-bottom:24px;}
    .search-title{font-family:'Playfair Display',serif;color:#D4AF37;font-size:1.05em;margin-bottom:12px;display:flex;align-items:center;gap:8px;}
    .search-form{display:flex;gap:10px;}
    .search-form input{flex:1;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);color:#fff;
      padding:11px 16px;border-radius:12px;font-size:.9em;font-family:monospace;letter-spacing:1px;}
    .search-form input:focus{outline:none;border-color:#D4AF37;}
    .search-form button{padding:11px 22px;background:linear-gradient(135deg,#D4AF37,#b8860b);border:none;
      color:#1e0e3a;font-weight:700;border-radius:12px;cursor:pointer;font-size:.85em;display:flex;align-items:center;gap:6px;}

    .result-box{margin-top:18px;padding:18px 20px;border-radius:14px;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;}
    .result-box.found.menunggu{background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.35);}
    .result-box.found.selesai{background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3);}
    .result-box.notfound{background:rgba(238,42,123,.1);border:1px solid rgba(238,42,123,.3);color:#f4a3c6;}
    .result-info b{color:#fff;} .result-info{font-size:.88em;color:rgba(255,255,255,.75);line-height:1.7;}
    .badge-pill{padding:5px 12px;border-radius:999px;font-size:.72em;font-weight:700;text-transform:uppercase;letter-spacing:.5px;}
    .badge-pill.menunggu{background:rgba(212,175,55,.2);color:#FFE88A;}
    .badge-pill.selesai{background:rgba(16,185,129,.2);color:#6ee7b7;}
    .btn-verif{padding:10px 20px;background:linear-gradient(135deg,#D4AF37,#b8860b);border:none;
      color:#1e0e3a;font-weight:700;border-radius:12px;cursor:pointer;font-size:.8em;display:flex;align-items:center;gap:6px;}

    /* TABLE */
    .table-card{background:rgba(255,255,255,.05);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.1);
      border-radius:24px;overflow:hidden;position:relative;}
    .table-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);background-size:200%;}
    .card-header{padding:18px 22px;border-bottom:1px solid rgba(255,255,255,.08);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;}
    .card-title{font-family:'Playfair Display',serif;font-size:1.1em;color:#D4AF37;}
    .filter-tabs{display:flex;gap:6px;}
    .filter-tabs a{padding:7px 14px;border-radius:10px;font-size:.78em;font-weight:600;text-decoration:none;color:rgba(255,255,255,.6);
      background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);}
    .filter-tabs a.active{background:rgba(212,175,55,.2);border-color:rgba(212,175,55,.4);color:#D4AF37;}
    table{width:100%;border-collapse:collapse;}
    thead tr{background:rgba(212,175,55,.12);border-bottom:1px solid rgba(212,175,55,.25);}
    thead th{padding:13px 16px;font-size:.7em;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:rgba(212,175,55,.9);text-align:left;}
    tbody tr{border-bottom:1px solid rgba(255,255,255,.06);transition:background .25s;}
    tbody tr:hover{background:rgba(212,175,55,.06);}
    tbody td{padding:13px 16px;font-size:.85em;color:rgba(255,255,255,.85);}
    .kode-mono{font-family:monospace;color:#FFE88A;letter-spacing:.5px;}
    .empty-row{padding:40px;text-align:center;color:rgba(255,255,255,.4);font-size:.88em;}
  </style>
</head>
<body>

<div class="page-hero">
  <div class="hero-inner">
    <p class="hero-eyebrow">YOLAZCAKE Back Office</p>
    <h1>Verifikasi Klaim Reward</h1>
    <p class="hero-sub">Cek kode redeem member & tandai reward yang sudah diambil</p>
  </div>
</div>

<div class="page-wrapper">
  <a href="../dashboard.php" class="btn-back"><i data-lucide="arrow-left" class="lucide-ic"></i> Kembali ke Dashboard</a>

  <div class="stats-row">
    <div class="stat-card"><div><div class="stat-val"><?= (int)($stats['total'] ?? 0) ?></div><div class="stat-lbl">Total Klaim</div></div></div>
    <div class="stat-card"><div><div class="stat-val"><?= (int)($stats['menunggu'] ?? 0) ?></div><div class="stat-lbl">Menunggu Verifikasi</div></div></div>
    <div class="stat-card"><div><div class="stat-val"><?= (int)($stats['selesai'] ?? 0) ?></div><div class="stat-lbl">Sudah Diambil</div></div></div>
    <div class="stat-card"><div><div class="stat-val"><?= (int)($stats['total_poin'] ?? 0) ?></div><div class="stat-lbl">Poin Ditukar</div></div></div>
  </div>

  <div class="search-card">
    <div class="search-title"><i data-lucide="scan-line" class="lucide-ic"></i> Cek Kode Redeem</div>
    <form method="GET" class="search-form">
      <input type="text" name="kode" placeholder="Masukkan kode, contoh: YLZ-A1B2C3" value="<?= htmlspecialchars($kode_cari) ?>" autofocus>
      <button type="submit"><i data-lucide="search" class="lucide-ic"></i> Cek</button>
    </form>

    <?php if ($kode_cari !== ''): ?>
      <?php if ($hasil_cari): ?>
        <div class="result-box found <?= $hasil_cari['status'] === 'Selesai' ? 'selesai' : 'menunggu' ?>">
          <div class="result-info">
            <b><?= htmlspecialchars($hasil_cari['nama_reward']) ?></b> — <?= htmlspecialchars($hasil_cari['nama'] ?? 'Member') ?><br>
            -<?= (int)$hasil_cari['poin_terpakai'] ?> Poin · Diklaim <?= date('d M Y, H:i', strtotime($hasil_cari['created_at'])) ?>
          </div>
          <?php if ($hasil_cari['status'] === 'Menunggu'): ?>
            <form method="POST" style="display:flex;align-items:center;gap:10px;">
              <span class="badge-pill menunggu">Menunggu</span>
              <input type="hidden" name="tandai_selesai" value="<?= (int)$hasil_cari['id_klaim'] ?>">
              <button type="submit" class="btn-verif"><i data-lucide="check-circle" class="lucide-ic"></i> Tandai Sudah Diambil</button>
            </form>
          <?php else: ?>
            <span class="badge-pill selesai"><i data-lucide="check" class="lucide-ic"></i> Sudah Diambil</span>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="result-box notfound">
          <div class="result-info"><i data-lucide="x-circle" class="lucide-ic"></i> Kode "<?= htmlspecialchars($kode_cari) ?>" tidak ditemukan.</div>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <div class="table-card">
    <div class="card-header">
      <div class="card-title"><i data-lucide="clipboard-list" class="lucide-ic"></i> Daftar Klaim</div>
      <div class="filter-tabs">
        <a href="?status=Menunggu" class="<?= $filter_status === 'Menunggu' ? 'active' : '' ?>">Menunggu</a>
        <a href="?status=Selesai" class="<?= $filter_status === 'Selesai' ? 'active' : '' ?>">Selesai</a>
        <a href="?status=semua" class="<?= $filter_status === 'semua' ? 'active' : '' ?>">Semua</a>
      </div>
    </div>
    <table>
      <thead>
        <tr>
          <th>Tanggal</th><th>Member</th><th>Reward</th><th>Poin</th><th>Kode</th><th>Status</th><th></th>
        </tr>
      </thead>
      <tbody>
        <?php if ($query && $query->num_rows > 0): while ($row = $query->fetch_assoc()): ?>
        <tr>
          <td><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></td>
          <td><?= htmlspecialchars($row['nama'] ?? '-') ?></td>
          <td><?= htmlspecialchars($row['nama_reward']) ?></td>
          <td>-<?= (int)$row['poin_terpakai'] ?></td>
          <td class="kode-mono"><?= htmlspecialchars($row['kode_redeem']) ?></td>
          <td><span class="badge-pill <?= $row['status'] === 'Selesai' ? 'selesai' : 'menunggu' ?>"><?= htmlspecialchars($row['status']) ?></span></td>
          <td>
            <?php if ($row['status'] === 'Menunggu'): ?>
              <form method="POST">
                <input type="hidden" name="tandai_selesai" value="<?= (int)$row['id_klaim'] ?>">
                <button type="submit" class="btn-verif"><i data-lucide="check" class="lucide-ic"></i></button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="7" class="empty-row">Belum ada klaim reward di kategori ini.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide){lucide.createIcons();}</script>
</body>
</html>
