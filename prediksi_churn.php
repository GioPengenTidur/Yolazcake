<?php
session_start();
require_once 'config/koneksi.php';

// Halaman ini khusus admin
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: auth/login.php');
    exit();
}

/* ── Ambil semua member + kapan terakhir mereka aktif (booking ATAU pesanan) ── */
$ambangHariBerisiko = 30; // dianggap "berisiko" kalau sudah >= 30 hari gak ada transaksi

$sql = "
    SELECT
        m.id_member, m.nama, m.email, m.no_hp, m.poin,
        GREATEST(
            COALESCE((SELECT MAX(p.tanggal) FROM pemesanan p WHERE p.id_member = m.id_member), '1970-01-01 00:00:00'),
            COALESCE((SELECT MAX(b.created_at) FROM booking b WHERE b.id_member = m.id_member), '1970-01-01 00:00:00')
        ) AS terakhir_aktif,
        (SELECT COUNT(*) FROM pemesanan p2 WHERE p2.id_member = m.id_member) AS total_pesanan,
        (SELECT COUNT(*) FROM booking b2 WHERE b2.id_member = m.id_member) AS total_booking
    FROM member m
    ORDER BY terakhir_aktif ASC
";
$res = mysqli_query($conn, $sql);

$daftarMember = [];
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $terakhir = $row['terakhir_aktif'];
        $belumPernah = ($terakhir === '1970-01-01 00:00:00');
        $hariSejak = $belumPernah ? null : (int) floor((time() - strtotime($terakhir)) / 86400);

        $row['hari_sejak_aktif']       = $hariSejak;
        $row['belum_pernah_transaksi'] = $belumPernah;
        $row['berisiko']               = $belumPernah || $hariSejak >= $ambangHariBerisiko;
        $daftarMember[] = $row;
    }
}

$totalMember   = count($daftarMember);
$totalBerisiko = count(array_filter($daftarMember, fn($m) => $m['berisiko']));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="assets/css/lucide-icons.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Prediksi Member Berisiko – YOLAZCAKE Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    :root{
      --gold:#D4AF37; --gold-l:#FFE88A;
      --rose:#EE2A7B; --purple:#8A2BE2;
      --bg1:#0d0520; --bg2:#1a0a3a; --bg3:#150830;
      --glass:rgba(255,255,255,.045); --gb:rgba(255,255,255,.10);
      --text:#fff; --muted:rgba(255,255,255,.5);
      --danger:#F6577A; --safe:#22c55e;
    }
    html,body{height:100%;}
    body{
      min-height:100vh;font-family:'Inter',sans-serif;
      background:linear-gradient(140deg,var(--bg1) 0%,var(--bg2) 50%,var(--bg3) 100%);
      color:var(--text);padding:32px 24px;position:relative;overflow-x:hidden;
    }
    body::before{
      content:'';position:fixed;inset:0;pointer-events:none;
      background:radial-gradient(ellipse 60% 50% at 15% 15%,rgba(212,175,55,.10) 0%,transparent 55%),
                 radial-gradient(ellipse 55% 50% at 85% 85%,rgba(138,43,226,.12) 0%,transparent 55%);
    }
    .page{position:relative;z-index:2;max-width:1080px;margin:0 auto;}

    .page-head{display:flex;align-items:center;gap:16px;margin-bottom:24px;flex-wrap:wrap;}
    .page-head h1{font-family:'Playfair Display',serif;font-size:1.6em;font-weight:700;}
    .page-head .sub{font-size:.82em;color:var(--muted);margin-top:4px;}
    .back-link{
      margin-left:auto;color:var(--muted);text-decoration:none;font-size:.8em;
      display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:10px;
      border:1px solid rgba(255,255,255,.1);transition:.2s;
    }
    .back-link:hover{color:#fff;background:rgba(255,255,255,.06);}

    .stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:24px;}
    .stat-card{
      background:var(--glass);backdrop-filter:blur(20px);border:1px solid var(--gb);
      border-radius:18px;padding:18px 20px;
    }
    .stat-card .num{font-family:'Playfair Display',serif;font-size:1.9em;font-weight:700;}
    .stat-card.danger .num{color:var(--danger);}
    .stat-card .lbl{font-size:.76em;color:var(--muted);margin-top:2px;}

    .ai-panel{
      background:var(--glass);backdrop-filter:blur(20px);border:1px solid rgba(212,175,55,.3);
      border-radius:18px;padding:20px 22px;margin-bottom:24px;
    }
    .ai-panel-head{display:flex;align-items:center;gap:12px;margin-bottom:12px;flex-wrap:wrap;}
    .ai-panel-head h2{font-size:1em;font-weight:700;color:var(--gold-l);display:flex;align-items:center;gap:8px;}
    .btn-ai{
      margin-left:auto;padding:10px 18px;border-radius:12px;border:none;cursor:pointer;
      background:linear-gradient(135deg,var(--gold),var(--rose));color:#1a0533;
      font-family:'Inter',sans-serif;font-weight:700;font-size:.82em;
      display:flex;align-items:center;gap:8px;transition:transform .2s;
    }
    .btn-ai:hover{transform:translateY(-1px) scale(1.02);}
    .btn-ai:disabled{opacity:.5;cursor:not-allowed;transform:none;}
    .ai-loading{display:none;align-items:center;gap:10px;color:var(--muted);font-size:.82em;padding:8px 0;}
    .ai-loading.show{display:flex;}
    .spinner{
      width:16px;height:16px;border-radius:50%;flex-shrink:0;
      border:2.5px solid rgba(255,255,255,.18);border-top-color:var(--gold-l);
      animation:spin .7s linear infinite;
    }
    @keyframes spin{to{transform:rotate(360deg);}}
    .ai-hasil{
      display:none;white-space:pre-wrap;font-size:.88em;line-height:1.7;color:#EDE9FF;
      background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);
      border-radius:14px;padding:16px 18px;margin-top:6px;
    }
    .ai-hasil.show{display:block;animation:fadeIn .4s forwards;}
    @keyframes fadeIn{from{opacity:0;}to{opacity:1;}}
    .ai-error{
      display:none;margin-top:10px;padding:10px 14px;border-radius:10px;font-size:.8em;
      background:rgba(246,87,122,.12);border:1px solid rgba(246,87,122,.35);color:#FFD1E6;
    }
    .ai-error.show{display:block;}

    .table-wrap{
      background:var(--glass);backdrop-filter:blur(20px);border:1px solid var(--gb);
      border-radius:18px;overflow:hidden;
    }
    table{width:100%;border-collapse:collapse;font-size:.84em;}
    thead th{
      text-align:left;padding:14px 16px;font-size:.72em;text-transform:uppercase;letter-spacing:.4px;
      color:var(--muted);border-bottom:1px solid var(--gb);background:rgba(255,255,255,.02);
    }
    tbody td{padding:13px 16px;border-bottom:1px solid rgba(255,255,255,.05);vertical-align:middle;}
    tbody tr:last-child td{border-bottom:none;}
    tbody tr:hover{background:rgba(255,255,255,.03);}
    .nama-cell{font-weight:600;color:#EDE9FF;}
    .email-cell{color:var(--muted);font-size:.9em;}
    .badge{
      display:inline-flex;align-items:center;gap:5px;padding:4px 11px;border-radius:99px;
      font-size:.76em;font-weight:600;
    }
    .badge.risk{background:rgba(246,87,122,.15);color:#FFB3C6;border:1px solid rgba(246,87,122,.35);}
    .badge.safe{background:rgba(34,197,94,.14);color:#8FE7AC;border:1px solid rgba(34,197,94,.32);}
    .hari-text{color:var(--muted);}
    .empty-row td{text-align:center;color:var(--muted);padding:36px 16px;}
  </style>
</head>
<body>

<div class="page">
  <div class="page-head">
    <div>
      <h1>Prediksi Member Berisiko</h1>
      <div class="sub">Member yang sudah <?= $ambangHariBerisiko ?>+ hari tidak bertransaksi, dibantu insight dari Yola AI</div>
    </div>
    <a href="dashboard.php" class="back-link"><i data-lucide="arrow-left" class="lucide-ic"></i> Kembali ke Dashboard</a>
  </div>

  <div class="stat-grid">
    <div class="stat-card">
      <div class="num"><?= $totalMember ?></div>
      <div class="lbl">Total Member</div>
    </div>
    <div class="stat-card danger">
      <div class="num"><?= $totalBerisiko ?></div>
      <div class="lbl">Berisiko Churn</div>
    </div>
    <div class="stat-card">
      <div class="num"><?= $totalMember > 0 ? round($totalBerisiko / $totalMember * 100) : 0 ?>%</div>
      <div class="lbl">Persentase Berisiko</div>
    </div>
  </div>

  <div class="ai-panel">
    <div class="ai-panel-head">
      <h2><i data-lucide="sparkles" class="lucide-ic"></i> Insight & Saran dari Yola AI</h2>
      <button type="button" class="btn-ai" id="btnMintaInsight">
        <i data-lucide="brain-circuit" class="lucide-ic"></i> Minta Insight AI
      </button>
    </div>
    <div class="ai-loading" id="aiLoading"><span class="spinner"></span> Yola lagi analisis data member...</div>
    <div class="ai-error" id="aiError"></div>
    <div class="ai-hasil" id="aiHasil"></div>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Nama</th>
          <th>Kontak</th>
          <th>Poin</th>
          <th>Total Transaksi</th>
          <th>Terakhir Aktif</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($daftarMember)): ?>
        <tr class="empty-row"><td colspan="6">Belum ada data member.</td></tr>
        <?php else: foreach ($daftarMember as $m): ?>
        <tr>
          <td class="nama-cell"><?= htmlspecialchars($m['nama']) ?></td>
          <td class="email-cell">
            <?= htmlspecialchars($m['email'] ?: '-') ?><br>
            <span style="font-size:.85em;"><?= htmlspecialchars($m['no_hp'] ?: '') ?></span>
          </td>
          <td><?= (int) $m['poin'] ?></td>
          <td><?= (int) $m['total_pesanan'] + (int) $m['total_booking'] ?>x</td>
          <td class="hari-text">
            <?php if ($m['belum_pernah_transaksi']): ?>
              Belum pernah transaksi
            <?php else: ?>
              <?= (int) $m['hari_sejak_aktif'] ?> hari lalu
            <?php endif; ?>
          </td>
          <td>
            <?php if ($m['berisiko']): ?>
              <span class="badge risk"><i data-lucide="alert-triangle" class="lucide-ic" style="width:12px;height:12px;"></i> Berisiko</span>
            <?php else: ?>
              <span class="badge safe"><i data-lucide="check-circle" class="lucide-ic" style="width:12px;height:12px;"></i> Aktif</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
if (window.lucide) lucide.createIcons();

const btnMintaInsight = document.getElementById('btnMintaInsight');
const aiLoading = document.getElementById('aiLoading');
const aiError   = document.getElementById('aiError');
const aiHasil   = document.getElementById('aiHasil');

btnMintaInsight.addEventListener('click', async () => {
  btnMintaInsight.disabled = true;
  aiLoading.classList.add('show');
  aiError.classList.remove('show');
  aiHasil.classList.remove('show');

  try {
    const res = await fetch('prediksi_churn_api.php');
    const data = await res.json();

    if (data.insight) {
      aiHasil.textContent = data.insight;
      aiHasil.classList.add('show');
    } else {
      aiError.textContent = data.error || 'Gagal ambil insight. Coba lagi ya.';
      aiError.classList.add('show');
    }
  } catch (err) {
    aiError.textContent = 'Gagal terhubung ke server. Coba lagi bentar ya.';
    aiError.classList.add('show');
  }

  aiLoading.classList.remove('show');
  btnMintaInsight.disabled = false;
});
</script>
</body>
</html>
