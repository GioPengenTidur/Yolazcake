<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/koneksi.php';
require_once '../config/member_helper.php';
require_once '../config/gamifikasi_helper.php';

$member = get_current_member($conn);
if ($member === null) {
    header("Location: member.php");
    exit();
}

$hasilCheckin = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkin'])) {
    $hasilCheckin = gamif_lakukan_checkin($conn, $member);
    // Ambil ulang data member supaya streak_saat_ini di halaman sinkron.
    $member = get_current_member($conn);

    if ($hasilCheckin['ok']) {
        include 'success_overlay.php';
        $badgeText = '';
        if (!empty($hasilCheckin['badge_baru'])) {
            $namaBadge = array_map(fn($b) => $b['nama'], $hasilCheckin['badge_baru']);
            $badgeText = ' Kamu juga membuka badge: ' . implode(', ', $namaBadge) . '! 🏆';
        }
        tampilkan_sukses([
            'proses_judul' => 'Mencatat Checkin…',
            'proses_sub'   => 'Sedang memperbarui streak kamu',
            'sukses_judul' => 'Checkin Berhasil! 🔥',
            'sukses_sub'   => $hasilCheckin['pesan'] . $badgeText,
            'redirect'     => 'streak.php',
            'tombol_label' => 'Lihat Streak & Badge',
        ]);
        exit;
    }
}

$info = gamif_get_streak_info($conn, $member);

// Bangun kalender 30 hari terakhir untuk ditampilkan sebagai titik-titik.
$kalender = [];
for ($i = 29; $i >= 0; $i--) {
    $tgl = date('Y-m-d', strtotime("-$i day"));
    $kalender[] = ['tanggal' => $tgl, 'checkin' => isset($info['tanggal_checkin'][$tgl])];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="../assets/css/lucide-icons.css">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Streak & Badge – YOLAZCAKE</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
:root{
  --gold:#D4AF37; --gold-light:#FFE88A; --rose:#EE2A7B; --flame:#FF7A3D;
  --bg1:#0d0520; --bg2:#1a0a3a; --bg3:#150830;
  --glass:rgba(255,255,255,.045); --glass-border:rgba(255,255,255,.10);
  --text:#fff; --text-muted:rgba(255,255,255,.55);
}
body{
  min-height:100vh; font-family:'Inter',sans-serif; color:var(--text);
  background:linear-gradient(160deg,var(--bg1) 0%,var(--bg2) 50%,var(--bg3) 100%);
  padding:0 0 60px;
}
body::before{
  content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
  background:radial-gradient(ellipse at 20% 20%,rgba(255,122,61,.10) 0%,transparent 55%),
             radial-gradient(ellipse at 85% 75%,rgba(212,175,55,.10) 0%,transparent 55%);
}
.top-nav{display:flex; align-items:center; justify-content:space-between; padding:18px 26px; position:relative; z-index:2;}
.top-nav a{color:var(--text-muted); text-decoration:none; font-size:.85em; display:flex; align-items:center; gap:6px;}
.top-nav a:hover{color:var(--gold);}
.wrap{max-width:640px; margin:0 auto; padding:0 20px; position:relative; z-index:2;}
.hero-icon{
  width:78px;height:78px;margin:6px auto 16px;border-radius:22px;
  background:linear-gradient(135deg,rgba(255,122,61,.2),rgba(212,175,55,.14));
  border:1px solid rgba(255,122,61,.3); display:flex;align-items:center;justify-content:center; color:var(--flame);
}
.hero-icon i{width:34px;height:34px;}
h1{
  font-family:'Playfair Display',serif; text-align:center; font-size:1.9em; font-weight:700;
  background:linear-gradient(135deg,#fff 30%,var(--flame) 55%,var(--gold-light) 80%,#fff);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  margin-bottom:8px;
}
.sub{text-align:center;color:var(--text-muted);font-size:.92em;margin-bottom:26px;line-height:1.6;}

.streak-hero{
  background:var(--glass); border:1px solid var(--glass-border); border-radius:20px;
  padding:28px 24px; text-align:center; margin-bottom:22px; backdrop-filter:blur(10px);
}
.streak-num{font-size:3em; font-weight:800; color:var(--flame); line-height:1; display:flex; align-items:center; justify-content:center; gap:10px;}
.streak-num i{width:34px;height:34px;}
.streak-lbl{color:var(--text-muted); font-size:.85em; margin-top:6px; text-transform:uppercase; letter-spacing:1px;}
.streak-best{margin-top:16px; font-size:.85em; color:var(--gold-light);}

.btn-checkin{
  margin-top:20px; width:100%; padding:15px; border:none; border-radius:14px; cursor:pointer;
  font-family:'Inter',sans-serif; font-weight:700; font-size:.95em; letter-spacing:1px; text-transform:uppercase;
  background:linear-gradient(135deg,var(--flame) 0%,#c9440c 50%,var(--flame) 100%);
  color:#fff; display:flex; align-items:center; justify-content:center; gap:8px; transition:.25s;
}
.btn-checkin:hover{transform:translateY(-2px); box-shadow:0 10px 30px rgba(255,122,61,.35);}
.btn-checkin:disabled{opacity:.5; cursor:not-allowed; transform:none; box-shadow:none;}

.section-title{
  font-family:'Playfair Display',serif; font-size:1.15em; font-weight:700; margin:30px 0 14px;
  display:flex; align-items:center; gap:8px; color:#fff;
}
.section-title i{color:var(--gold); width:20px;height:20px;}

.kalender{
  display:grid; grid-template-columns:repeat(10,1fr); gap:6px;
  background:var(--glass); border:1px solid var(--glass-border); border-radius:16px; padding:16px;
}
.hari-dot{
  aspect-ratio:1; border-radius:8px; background:rgba(255,255,255,.06);
  display:flex; align-items:center; justify-content:center; font-size:.6em; color:var(--text-muted);
}
.hari-dot.aktif{background:linear-gradient(135deg,var(--flame),var(--gold)); color:#1e0e3a; font-weight:700;}

.badge-grid{display:grid; grid-template-columns:repeat(2,1fr); gap:14px; margin-top:14px;}
.badge-card{
  position:relative; background:var(--glass); border:1px solid var(--glass-border); border-radius:16px;
  padding:20px 14px; text-align:center;
}
.badge-card.unlocked{border-color:rgba(212,175,55,.4); background:rgba(212,175,55,.06);}
.badge-card.locked{opacity:.5;}
.badge-icon{width:44px;height:44px; margin:0 auto 10px; color:var(--gold);}
.badge-icon i{width:44px;height:44px;}
.badge-nama{font-weight:700; font-size:.9em; margin-bottom:4px;}
.badge-desc{font-size:.72em; color:var(--text-muted); line-height:1.4;}
.badge-lock{position:absolute; top:10px; right:10px; color:var(--text-muted);}
.badge-lock i{width:16px;height:16px;}
</style>
</head>
<body>

<div class="top-nav">
  <a href="member.php"><i data-lucide="arrow-left" class="lucide-ic"></i> Kembali ke Member Area</a>
</div>

<div class="wrap">
  <div class="hero-icon"><i data-lucide="flame" class="lucide-ic"></i></div>
  <h1>Streak & Badge</h1>
  <p class="sub">Checkin tiap hari biar streak-mu nggak putus, dan buka badge eksklusif YOLAZCAKE!</p>

  <div class="streak-hero">
    <div class="streak-num"><i data-lucide="flame" class="lucide-ic"></i> <?= $info['streak_saat_ini'] ?></div>
    <div class="streak-lbl">Hari Berturut-turut</div>
    <div class="streak-best"><i data-lucide="trophy" class="lucide-ic"></i> Rekor terbaikmu: <?= $info['streak_terbaik'] ?> hari</div>

    <form method="POST">
      <button type="submit" name="checkin" class="btn-checkin" <?= $info['sudah_checkin'] ? 'disabled' : '' ?>>
        <?php if ($info['sudah_checkin']): ?>
          <i data-lucide="check-circle" class="lucide-ic"></i> Sudah Checkin Hari Ini
        <?php else: ?>
          <i data-lucide="flame" class="lucide-ic"></i> Checkin Hari Ini
        <?php endif; ?>
      </button>
    </form>
  </div>

  <div class="section-title"><i data-lucide="calendar-days" class="lucide-ic"></i> 30 Hari Terakhir</div>
  <div class="kalender">
    <?php foreach ($kalender as $h): ?>
      <div class="hari-dot <?= $h['checkin'] ? 'aktif' : '' ?>" title="<?= $h['tanggal'] ?>">
        <?= date('j', strtotime($h['tanggal'])) ?>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="section-title"><i data-lucide="award" class="lucide-ic"></i> Badge Kamu</div>
  <div class="badge-grid">
    <?php foreach ($info['badges'] as $b): ?>
      <div class="badge-card <?= $b['unlocked'] ? 'unlocked' : 'locked' ?>">
        <?php if (!$b['unlocked']): ?><span class="badge-lock"><i data-lucide="lock" class="lucide-ic"></i></span><?php endif; ?>
        <div class="badge-icon"><i data-lucide="<?= htmlspecialchars($b['icon']) ?>" class="lucide-ic"></i></div>
        <div class="badge-nama"><?= htmlspecialchars($b['nama']) ?></div>
        <div class="badge-desc"><?= htmlspecialchars($b['desc']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide){lucide.createIcons();}</script>
</body>
</html>
