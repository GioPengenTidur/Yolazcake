<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/koneksi.php';
require_once '../config/member_helper.php';
require_once '../config/gamifikasi_helper.php';
require_once '../config/reward_helper.php';

$member = get_current_member($conn);
if ($member === null) {
    header("Location: member.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['klaim_poin'])) {
    $poinReward = (int) $_POST['klaim_poin'];
    $namaReward = '';
    foreach (REWARD_MILESTONES as $r) {
        if ($r['poin'] === $poinReward) { $namaReward = $r['nama']; break; }
    }

    if ($namaReward !== '') {
        $hasil = reward_klaim_proses($conn, $member, $poinReward, $namaReward);

        if ($hasil['ok']) {
            include 'success_overlay.php';
            tampilkan_sukses([
                'proses_judul' => 'Memproses Klaim…',
                'proses_sub'   => 'Sedang menukar poin kamu',
                'sukses_judul' => 'Reward Berhasil Diklaim! 🎉',
                'sukses_sub'   => $hasil['pesan'] . ' Tunjukkan kode ' . $hasil['kode'] . ' ke kasir ya.',
                'redirect'     => 'klaim_reward.php',
                'tombol_label' => 'Lihat Reward Saya',
            ]);
            exit;
        }
    } else {
        $hasil = ['ok' => false, 'pesan' => 'Reward tidak dikenali.'];
    }
}

// Ambil ulang data member supaya poin di halaman sinkron kalau baru saja diklaim.
$member = get_current_member($conn);
$poin   = (int) ($member['poin'] ?? 0);
$riwayat_klaim = [];
try {
    $riwayat_klaim = reward_riwayat_klaim($conn, (int) $member['id_member'], 10);
} catch (Throwable $e) {
    // Tabel reward_klaim mungkin belum diimport dari migration -- halaman tetap jalan.
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="../assets/css/lucide-icons.css">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Klaim Reward – YOLAZCAKE</title>
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
  background:radial-gradient(ellipse at 20% 20%,rgba(212,175,55,.10) 0%,transparent 55%),
             radial-gradient(ellipse at 85% 75%,rgba(238,42,123,.10) 0%,transparent 55%);
}
.top-nav{display:flex; align-items:center; justify-content:space-between; padding:18px 26px; position:relative; z-index:2;}
.top-nav a{color:var(--text-muted); text-decoration:none; font-size:.85em; display:flex; align-items:center; gap:6px;}
.top-nav a:hover{color:var(--gold);}
.wrap{max-width:640px; margin:0 auto; padding:0 20px; position:relative; z-index:2;}
.hero-icon{
  width:78px;height:78px;margin:6px auto 16px;border-radius:22px;
  background:linear-gradient(135deg,rgba(212,175,55,.2),rgba(238,42,123,.14));
  border:1px solid rgba(212,175,55,.3); display:flex;align-items:center;justify-content:center; color:var(--gold);
}
.hero-icon i{width:34px;height:34px;}
h1{
  font-family:'Playfair Display',serif; text-align:center; font-size:1.9em; font-weight:700;
  background:linear-gradient(135deg,#fff 30%,var(--gold) 55%,var(--rose) 80%,#fff);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  margin-bottom:8px;
}
.sub{text-align:center;color:var(--text-muted);font-size:.92em;margin-bottom:26px;line-height:1.6;}

.poin-hero{
  background:var(--glass); border:1px solid var(--glass-border); border-radius:20px;
  padding:26px 24px; text-align:center; margin-bottom:24px; backdrop-filter:blur(10px);
}
.poin-num{font-size:2.6em; font-weight:800; color:var(--gold); line-height:1; display:flex; align-items:center; justify-content:center; gap:10px;}
.poin-num i{width:30px;height:30px;}
.poin-lbl{color:var(--text-muted); font-size:.85em; margin-top:6px; text-transform:uppercase; letter-spacing:1px;}

.section-title{
  font-family:'Playfair Display',serif; font-size:1.15em; font-weight:700; margin:30px 0 14px;
  display:flex; align-items:center; gap:8px; color:#fff;
}
.section-title i{color:var(--gold); width:20px;height:20px;}

.reward-list{display:flex; flex-direction:column; gap:14px;}
.reward-card{
  position:relative; background:var(--glass); border:1px solid var(--glass-border); border-radius:18px;
  padding:20px; display:flex; align-items:center; gap:16px; backdrop-filter:blur(8px);
}
.reward-card.unlocked{border-color:rgba(212,175,55,.4); background:rgba(212,175,55,.06);}
.reward-card.locked{opacity:.6;}
.reward-card-icon{
  width:52px;height:52px; flex-shrink:0; border-radius:14px; display:flex; align-items:center; justify-content:center;
  background:linear-gradient(135deg,rgba(212,175,55,.2),rgba(238,42,123,.14)); color:var(--gold);
}
.reward-card-icon i{width:26px;height:26px;}
.reward-card-body{flex:1; min-width:0;}
.reward-card-nama{font-weight:700; font-size:1em; margin-bottom:3px;}
.reward-card-syarat{font-size:.78em; color:var(--text-muted);}
.reward-card-syarat.siap{color:var(--gold-light);}
.reward-progress-bg{margin-top:8px; height:5px; border-radius:999px; background:rgba(255,255,255,.08); overflow:hidden;}
.reward-progress-fill{height:100%; border-radius:999px; background:linear-gradient(90deg,var(--gold),var(--rose));}

.btn-klaim{
  flex-shrink:0; padding:10px 18px; border:none; border-radius:12px; cursor:pointer;
  font-family:'Inter',sans-serif; font-weight:700; font-size:.78em; letter-spacing:.5px; text-transform:uppercase;
  background:linear-gradient(135deg,var(--gold) 0%,#b8860b 50%,var(--gold) 100%);
  color:#1e0e3a; display:flex; align-items:center; gap:6px; transition:.25s;
}
.btn-klaim i{width:15px;height:15px;}
.btn-klaim:hover{transform:translateY(-2px); box-shadow:0 10px 25px rgba(212,175,55,.35);}
.lock-pill{
  flex-shrink:0; display:flex; align-items:center; gap:6px; font-size:.75em; color:var(--text-muted);
  padding:8px 14px; border:1px solid var(--glass-border); border-radius:12px;
}
.lock-pill i{width:14px;height:14px;}

.riwayat-list{display:flex; flex-direction:column; gap:10px;}
.riwayat-item{
  background:var(--glass); border:1px solid var(--glass-border); border-radius:14px;
  padding:14px 16px; display:flex; align-items:center; justify-content:space-between; gap:10px;
}
.riwayat-nama{font-weight:600; font-size:.88em;}
.riwayat-tgl{font-size:.72em; color:var(--text-muted); margin-top:2px;}
.riwayat-kode{
  font-family:monospace; font-size:.8em; color:var(--gold-light); background:rgba(212,175,55,.1);
  padding:5px 10px; border-radius:8px; letter-spacing:.5px; white-space:nowrap;
}
.riwayat-empty{color:var(--text-muted); font-size:.85em; text-align:center; padding:20px;}
</style>
</head>
<body>

<div class="top-nav">
  <a href="member.php"><i data-lucide="arrow-left" class="lucide-ic"></i> Kembali ke Member Area</a>
</div>

<div class="wrap">
  <div class="hero-icon"><i data-lucide="gift" class="lucide-ic"></i></div>
  <h1>Klaim Reward</h1>
  <p class="sub">Tukar poin kamu dengan reward favorit. Poin akan dipotong sesuai syarat reward.</p>

  <div class="poin-hero">
    <div class="poin-num"><i data-lucide="star" class="lucide-ic"></i> <?= $poin ?></div>
    <div class="poin-lbl">Total Poin Kamu</div>
  </div>

  <div class="section-title"><i data-lucide="sparkles" class="lucide-ic"></i> Reward Tersedia</div>
  <div class="reward-list">
    <?php foreach (REWARD_MILESTONES as $r):
      $unlocked = $poin >= $r['poin'];
      $pct = min(100, (int) round(($poin / $r['poin']) * 100));
    ?>
      <div class="reward-card <?= $unlocked ? 'unlocked' : 'locked' ?>">
        <div class="reward-card-icon"><i data-lucide="<?= htmlspecialchars($r['icon']) ?>" class="lucide-ic"></i></div>
        <div class="reward-card-body">
          <div class="reward-card-nama"><?= htmlspecialchars($r['nama']) ?></div>
          <?php if ($unlocked): ?>
            <div class="reward-card-syarat siap"><i data-lucide="check-circle" class="lucide-ic" style="width:12px;height:12px;"></i> Siap diklaim — <?= $r['poin'] ?> Poin</div>
          <?php else: ?>
            <div class="reward-card-syarat"><?= $r['poin'] ?> Poin dibutuhkan · kurang <?= $r['poin'] - $poin ?> poin lagi</div>
            <div class="reward-progress-bg"><div class="reward-progress-fill" style="width:<?= $pct ?>%"></div></div>
          <?php endif; ?>
        </div>
        <?php if ($unlocked): ?>
          <form method="POST">
            <input type="hidden" name="klaim_poin" value="<?= $r['poin'] ?>">
            <button type="submit" class="btn-klaim"><i data-lucide="gift" class="lucide-ic"></i> Klaim</button>
          </form>
        <?php else: ?>
          <div class="lock-pill"><i data-lucide="lock" class="lucide-ic"></i> Terkunci</div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="section-title"><i data-lucide="history" class="lucide-ic"></i> Riwayat Klaim</div>
  <div class="riwayat-list">
    <?php if (empty($riwayat_klaim)): ?>
      <div class="riwayat-empty">Belum ada reward yang diklaim.</div>
    <?php else: foreach ($riwayat_klaim as $rk): ?>
      <div class="riwayat-item">
        <div>
          <div class="riwayat-nama"><?= htmlspecialchars($rk['nama_reward']) ?></div>
          <div class="riwayat-tgl"><?= date('d M Y, H:i', strtotime($rk['created_at'])) ?> · -<?= (int) $rk['poin_terpakai'] ?> Poin</div>
        </div>
        <div class="riwayat-kode"><?= htmlspecialchars($rk['kode_redeem']) ?></div>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide){lucide.createIcons();}</script>
</body>
</html>
