<?php
session_start();
require_once __DIR__.'/../config/staff_guard.php';
require_staff_login();
include '../config/koneksi.php';

// Handle hapus (moderasi: admin bisa menghapus ulasan yang tidak pantas)
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM ulasan_produk WHERE id_ulasan=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: data_ulasan_produk.php?ok=hapus");
    exit;
}

// Tandai semua ulasan sebagai sudah dibaca begitu admin membuka halaman ini,
// supaya badge notifikasi "ulasan baru" di sidebar ter-reset (butuh kolom
// `dibaca`, lihat database/tambah_kolom_dibaca_ulasan.sql).
@mysqli_query($conn, "UPDATE ulasan_produk SET dibaca=1 WHERE dibaca=0");

$filter_produk = isset($_GET['produk']) ? (int)$_GET['produk'] : 0;

$where  = $filter_produk ? "WHERE up.id_produk = ?" : "";
$sql = "SELECT up.*, p.nama_produk, p.foto
        FROM ulasan_produk up
        JOIN produk p ON p.id_produk = up.id_produk
        $where
        ORDER BY up.created_at DESC";
if ($filter_produk) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $filter_produk);
    $stmt->execute();
    $query = $stmt->get_result();
} else {
    $query = $conn->query($sql);
}

$stats = $conn->query(
    "SELECT COUNT(*) AS total, COALESCE(AVG(rating),0) AS avg_rating,
            SUM(rating<=2) AS jelek
     FROM ulasan_produk"
)->fetch_assoc();

$daftar_produk = $conn->query(
    "SELECT DISTINCT p.id_produk, p.nama_produk
     FROM ulasan_produk up JOIN produk p ON p.id_produk = up.id_produk
     ORDER BY p.nama_produk ASC"
)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="../assets/css/lucide-icons.css">
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ulasan Produk – YOLAZCAKE</title>
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
    .hero-inner{position:relative;z-index:2;text-align:center;}
    .hero-eyebrow{font-size:.72em;font-weight:500;letter-spacing:5px;text-transform:uppercase;color:#D4AF37;margin-bottom:10px;}
    .hero-inner h1{font-family:'Playfair Display',serif;font-size:2.6em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,#D4AF37 60%,#FFE4B5 80%,#fff);background-size:200%;
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
    .hero-sub{font-size:.9em;color:rgba(255,255,255,.65);margin-top:10px;}
    .page-wrapper{position:relative;z-index:1;padding:36px 28px 80px;max-width:1100px;margin:0 auto;}
    .btn-back{display:inline-flex;align-items:center;gap:8px;padding:10px 22px;
      background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.3);color:#D4AF37;
      font-size:.82em;font-weight:600;border-radius:999px;text-decoration:none;margin-bottom:24px;}
    .alert{padding:14px 20px;border-radius:12px;margin-bottom:20px;font-size:.88em;}
    .alert-success{background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.3);color:#6ee7b7;}
    .stats-row{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:28px;}
    .stat-card{background:rgba(255,255,255,.06);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.1);
      border-radius:18px;padding:18px 20px;display:flex;align-items:center;gap:12px;}
    .stat-icon{font-size:1.4em;}.stat-val{font-family:'Playfair Display',serif;font-size:1.6em;font-weight:700;color:#D4AF37;line-height:1;}
    .stat-lbl{font-size:.72em;color:rgba(255,255,255,.5);margin-top:2px;}
    .filter-tabs{display:flex;gap:10px;margin-bottom:22px;flex-wrap:wrap;}
    .filter-tab{padding:8px 20px;border-radius:999px;border:1px solid rgba(255,255,255,.15);
      color:rgba(255,255,255,.6);font-size:.8em;font-weight:600;text-decoration:none;transition:all .25s;}
    .filter-tab:hover{border-color:#D4AF37;color:#D4AF37;}
    .filter-tab.active{background:rgba(212,175,55,.15);border-color:rgba(212,175,55,.4);color:#D4AF37;}
    .table-card{background:rgba(255,255,255,.05);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.1);
      border-radius:24px;overflow:hidden;position:relative;}
    table{width:100%;border-collapse:collapse;}
    thead tr{background:rgba(212,175,55,.12);border-bottom:1px solid rgba(212,175,55,.25);}
    thead th{padding:14px 18px;font-size:.7em;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:rgba(212,175,55,.9);text-align:left;}
    tbody tr{border-bottom:1px solid rgba(255,255,255,.06);transition:background .25s;}
    tbody tr:hover{background:rgba(212,175,55,.08);}
    tbody td{padding:14px 18px;font-size:.87em;color:rgba(255,255,255,.8);vertical-align:middle;}
    .td-produk{display:flex;align-items:center;gap:10px;font-weight:700;color:#fff;}
    .td-produk img{width:36px;height:36px;object-fit:cover;border-radius:8px;}
    .td-komentar{font-size:.85em;color:rgba(255,255,255,.55);max-width:280px;}
    .btn-act{display:inline-flex;align-items:center;gap:4px;padding:5px 11px;border-radius:7px;font-size:.72em;font-weight:600;text-decoration:none;border:1px solid transparent;cursor:pointer;}
    .btn-hapus{background:rgba(239,68,68,.12);border-color:rgba(239,68,68,.3);color:#fca5a5;}
    .btn-hapus:hover{background:rgba(239,68,68,.25);}
    .empty-state{text-align:center;padding:60px;color:rgba(255,255,255,.4);}
    @media(max-width:768px){.stats-row{grid-template-columns:1fr;}}
  </style>
</head>
<body>
<div class="page-hero"><div class="hero-inner">
  <p class="hero-eyebrow"><i data-lucide="sparkle" class="lucide-ic"></i> YOLAZCAKE Sintang <i data-lucide="sparkle" class="lucide-ic"></i></p>
  <h1><i data-lucide="star" class="lucide-ic lucide-fill"></i> Ulasan Produk</h1>
  <p class="hero-sub">Moderasi rating & komentar pelanggan per produk</p>
</div></div>

<div class="page-wrapper">
  <a href="../dashboard.php" class="btn-back"><i data-lucide="arrow-left" class="lucide-ic"></i> Dashboard</a>

  <?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success"><i data-lucide="check-circle" class="lucide-ic"></i> Ulasan berhasil dihapus.</div>
  <?php endif; ?>

  <div class="stats-row">
    <div class="stat-card"><span class="stat-icon"><i data-lucide="file-text" class="lucide-ic"></i></span><div><div class="stat-val"><?= $stats['total'] ?></div><div class="stat-lbl">Total Ulasan</div></div></div>
    <div class="stat-card"><span class="stat-icon"><i data-lucide="star" class="lucide-ic lucide-fill"></i></span><div><div class="stat-val"><?= number_format($stats['avg_rating'],1) ?></div><div class="stat-lbl">Rata-rata Rating</div></div></div>
    <div class="stat-card"><span class="stat-icon"><i data-lucide="alert-triangle" class="lucide-ic"></i></span><div><div class="stat-val"><?= (int)$stats['jelek'] ?></div><div class="stat-lbl">Rating ≤ 2 (perlu perhatian)</div></div></div>
  </div>

  <?php if (!empty($daftar_produk)): ?>
  <div class="filter-tabs">
    <a href="data_ulasan_produk.php" class="filter-tab <?= !$filter_produk?'active':'' ?>">Semua Produk</a>
    <?php foreach ($daftar_produk as $dp): ?>
      <a href="?produk=<?= $dp['id_produk'] ?>" class="filter-tab <?= $filter_produk==$dp['id_produk']?'active':'' ?>"><?= htmlspecialchars($dp['nama_produk']) ?></a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="table-card">
    <div style="overflow-x:auto;">
    <table>
      <thead><tr><th>Tgl</th><th>Produk</th><th>Reviewer</th><th>Rating</th><th>Komentar</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php $rows = mysqli_num_rows($query); if ($rows > 0): while ($d = mysqli_fetch_assoc($query)): ?>
      <tr>
        <td style="font-size:.75em;color:rgba(255,255,255,.5);white-space:nowrap;"><?= date('d M Y', strtotime($d['created_at'])) ?></td>
        <td class="td-produk">
          <img src="../assets/img/produk/<?= htmlspecialchars($d['foto']) ?>" alt="">
          <?= htmlspecialchars($d['nama_produk']) ?>
        </td>
        <td><?= htmlspecialchars($d['nama_reviewer']) ?></td>
        <td><?php for($i=1;$i<=5;$i++): ?><span style="color:<?= $i<=$d['rating']?'#D4AF37':'rgba(255,255,255,.2)' ?>;"><i data-lucide="star" class="lucide-ic lucide-fill"></i></span><?php endfor; ?></td>
        <td class="td-komentar"><?= htmlspecialchars($d['komentar'] ?: '—') ?></td>
        <td>
          <a href="?hapus=<?= $d['id_ulasan'] ?>" class="btn-act btn-hapus" onclick="return confirm('Hapus ulasan ini?')"><i data-lucide="trash-2" class="lucide-ic"></i> Hapus</a>
        </td>
      </tr>
      <?php endwhile; else: ?>
      <tr><td colspan="6"><div class="empty-state"><i data-lucide="inbox" class="lucide-ic"></i> Belum ada ulasan produk</div></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide){lucide.createIcons();}</script>
</body>
</html>
