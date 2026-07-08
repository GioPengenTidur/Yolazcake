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

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_penerima = (int) ($_POST['id_penerima'] ?? 0);
    $poin        = (int) ($_POST['poin'] ?? 0);
    $pesan_kado  = trim($_POST['pesan'] ?? '');
    if (mb_strlen($pesan_kado) > 150) {
        $pesan_kado = mb_substr($pesan_kado, 0, 150);
    }

    if ($id_penerima <= 0) {
        $error = 'Pilih dulu member tujuan dari hasil pencarian.';
    } else {
        $hasil = gamif_transfer_poin($conn, $member, $id_penerima, $poin, $pesan_kado);
        if ($hasil['ok']) {
            include 'success_overlay.php';
            tampilkan_sukses([
                'proses_judul' => 'Mengirim Kado Poin…',
                'proses_sub'   => 'Sedang membungkus kado untuk temanmu',
                'sukses_judul' => 'Kado Terkirim! 🎁',
                'sukses_sub'   => $hasil['pesan'],
                'redirect'     => 'member.php',
                'tombol_label' => 'Kembali ke Member Area',
            ]);
            exit;
        } else {
            $error = $hasil['pesan'];
        }
    }
}

$poin_saya = (int) ($member['poin'] ?? 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="../assets/css/lucide-icons.css">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kado Poin – YOLAZCAKE</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
:root{
  --gold:#D4AF37; --gold-light:#FFE88A; --rose:#EE2A7B;
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
  background:radial-gradient(ellipse at 20% 20%,rgba(212,175,55,.12) 0%,transparent 55%),
             radial-gradient(ellipse at 85% 75%,rgba(238,42,123,.10) 0%,transparent 55%);
}
.top-nav{
  display:flex; align-items:center; justify-content:space-between;
  padding:18px 26px; position:relative; z-index:2;
}
.top-nav a{
  color:var(--text-muted); text-decoration:none; font-size:.85em;
  display:flex; align-items:center; gap:6px;
}
.top-nav a:hover{color:var(--gold);}
.wrap{max-width:560px; margin:10px auto 0; padding:0 20px; position:relative; z-index:2;}
.hero-icon{
  width:78px;height:78px;margin:10px auto 18px;border-radius:22px;
  background:linear-gradient(135deg,rgba(212,175,55,.18),rgba(238,42,123,.14));
  border:1px solid rgba(212,175,55,.3);
  display:flex;align-items:center;justify-content:center;
  color:var(--gold);
}
.hero-icon svg,.hero-icon i{width:34px;height:34px;}
h1{
  font-family:'Playfair Display',serif; text-align:center; font-size:1.9em; font-weight:700;
  background:linear-gradient(135deg,#fff 30%,var(--gold) 60%,var(--gold-light) 80%,#fff);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  margin-bottom:8px;
}
.sub{text-align:center;color:var(--text-muted);font-size:.92em;margin-bottom:28px;line-height:1.6;}
.poin-badge{
  display:flex; align-items:center; justify-content:center; gap:8px;
  background:var(--glass); border:1px solid var(--glass-border); border-radius:14px;
  padding:14px; margin-bottom:26px; font-size:.95em;
}
.poin-badge strong{color:var(--gold); font-size:1.15em;}
.card{
  background:var(--glass); border:1px solid var(--glass-border); border-radius:20px;
  padding:28px 24px; backdrop-filter:blur(10px);
}
label{display:block; font-size:.8em; color:var(--text-muted); margin:0 0 8px; letter-spacing:.5px; text-transform:uppercase;}
.field{margin-bottom:20px; position:relative;}
input[type="text"], input[type="number"], textarea{
  width:100%; padding:13px 16px; border-radius:12px; border:1px solid var(--glass-border);
  background:rgba(255,255,255,.04); color:#fff; font-family:'Inter',sans-serif; font-size:.95em;
  outline:none; transition:.2s;
}
input:focus, textarea:focus{border-color:var(--gold);}
textarea{resize:vertical; min-height:70px;}
.hasil-cari{
  position:relative; margin-top:6px; border-radius:12px; overflow:hidden;
  background:rgba(20,10,40,.96); border:1px solid var(--glass-border);
  max-height:220px; overflow-y:auto; display:none;
}
.hasil-cari.show{display:block;}
.hasil-item{padding:11px 16px; cursor:pointer; font-size:.9em; border-bottom:1px solid rgba(255,255,255,.06);}
.hasil-item:last-child{border-bottom:none;}
.hasil-item:hover{background:rgba(212,175,55,.12);}
.terpilih{
  margin-top:10px; display:none; align-items:center; gap:8px;
  background:rgba(212,175,55,.12); border:1px solid rgba(212,175,55,.3);
  padding:10px 14px; border-radius:12px; font-size:.9em; color:var(--gold-light);
}
.terpilih.show{display:flex;}
.terpilih button{
  margin-left:auto; background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:1.1em;
}
.error-box{
  background:rgba(238,42,123,.12); border:1px solid rgba(238,42,123,.35); color:#ffb8d2;
  padding:12px 16px; border-radius:12px; margin-bottom:20px; font-size:.88em;
}
.btn-kirim{
  width:100%; padding:15px; border:none; border-radius:14px; cursor:pointer;
  font-family:'Inter',sans-serif; font-weight:700; font-size:.95em; letter-spacing:1px; text-transform:uppercase;
  background:linear-gradient(135deg,var(--gold) 0%,#b8860b 50%,var(--gold) 100%);
  background-size:200% 100%; color:#1e0e3a;
  display:flex; align-items:center; justify-content:center; gap:8px;
  transition:.25s;
}
.btn-kirim:hover{transform:translateY(-2px); box-shadow:0 10px 30px rgba(212,175,55,.35);}
.hint{font-size:.75em; color:var(--text-muted); margin-top:8px;}
</style>
</head>
<body>

<div class="top-nav">
  <a href="member.php"><i data-lucide="arrow-left" class="lucide-ic"></i> Kembali ke Member Area</a>
</div>

<div class="wrap">
  <div class="hero-icon"><i data-lucide="gift" class="lucide-ic"></i></div>
  <h1>Kado Poin</h1>
  <p class="sub">Kirim sebagian poinmu ke teman sesama member YOLAZCAKE. Mereka akan langsung dapat notifikasi kado darimu!</p>

  <div class="poin-badge"><i data-lucide="star" class="lucide-ic"></i> Poin kamu saat ini: <strong><?= $poin_saya ?> Poin</strong></div>

  <?php if ($error): ?>
    <div class="error-box"><i data-lucide="alert-circle" class="lucide-ic"></i> <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="card">
    <form method="POST" id="formKado">
      <div class="field">
        <label>Cari Teman (username / email / no HP)</label>
        <input type="text" id="cariInput" placeholder="Ketik minimal 2 huruf..." autocomplete="off">
        <div class="hasil-cari" id="hasilCari"></div>
        <div class="terpilih" id="terpilihBox">
          <i data-lucide="user-check" class="lucide-ic"></i>
          <span id="terpilihNama"></span>
          <button type="button" onclick="batalkanPilihan()"><i data-lucide="x" class="lucide-ic"></i></button>
        </div>
        <input type="hidden" name="id_penerima" id="idPenerima" value="">
      </div>

      <div class="field">
        <label>Jumlah Poin</label>
        <input type="number" name="poin" min="1" max="<?= $poin_saya ?>" placeholder="cth. 20" required>
        <p class="hint">Maksimal <?= $poin_saya ?> poin (sesuai poin kamu sekarang).</p>
      </div>

      <div class="field">
        <label>Pesan (opsional)</label>
        <textarea name="pesan" maxlength="150" placeholder="Tulis pesan singkat buat temanmu..."></textarea>
      </div>

      <button type="submit" class="btn-kirim" id="btnKirim" disabled>
        <i data-lucide="send" class="lucide-ic"></i> Kirim Kado Poin
      </button>
    </form>
  </div>
</div>

<script>
const cariInput   = document.getElementById('cariInput');
const hasilCari    = document.getElementById('hasilCari');
const idPenerima   = document.getElementById('idPenerima');
const terpilihBox  = document.getElementById('terpilihBox');
const terpilihNama = document.getElementById('terpilihNama');
const btnKirim     = document.getElementById('btnKirim');

let timer = null;

cariInput.addEventListener('input', () => {
  clearTimeout(timer);
  const q = cariInput.value.trim();
  idPenerima.value = '';
  btnKirim.disabled = true;
  if (q.length < 2) { hasilCari.classList.remove('show'); hasilCari.innerHTML=''; return; }

  timer = setTimeout(() => {
    fetch('cari_member_ajax.php?q=' + encodeURIComponent(q))
      .then(r => r.json())
      .then(data => {
        hasilCari.innerHTML = '';
        if (!data.success) {
          hasilCari.innerHTML = '<div class="hasil-item">' + (data.message || 'Terjadi kesalahan.') + '</div>';
          hasilCari.classList.add('show');
          return;
        }
        if (!data.hasil.length) {
          hasilCari.innerHTML = '<div class="hasil-item">Tidak ada member ditemukan (pastikan dia sudah jadi member, bukan cuma punya akun).</div>';
          hasilCari.classList.add('show');
          return;
        }
        data.hasil.forEach(m => {
          const div = document.createElement('div');
          div.className = 'hasil-item';
          div.textContent = m.label;
          div.onclick = () => pilihMember(m);
          hasilCari.appendChild(div);
        });
        hasilCari.classList.add('show');
      })
      .catch(err => {
        hasilCari.innerHTML = '<div class="hasil-item">Gagal memuat (cek koneksi/console browser). ' + err.message + '</div>';
        hasilCari.classList.add('show');
      });
  }, 300);
});

function pilihMember(m){
  idPenerima.value = m.id_member;
  terpilihNama.textContent = m.label;
  terpilihBox.classList.add('show');
  hasilCari.classList.remove('show');
  cariInput.value = '';
  btnKirim.disabled = false;
}

function batalkanPilihan(){
  idPenerima.value = '';
  terpilihBox.classList.remove('show');
  btnKirim.disabled = true;
}

document.addEventListener('click', (e) => {
  if (!hasilCari.contains(e.target) && e.target !== cariInput) {
    hasilCari.classList.remove('show');
  }
});
</script>

<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide){lucide.createIcons();}</script>
</body>
</html>
