<?php
session_start();
include '../config/koneksi.php';

$token = trim($_GET['token'] ?? '');
if ($token === '') { die('Link tidak valid.'); }

$stmt = $conn->prepare("
    SELECT sb.*, p.kode_pesanan, p.nama_pemesan, p.total_harga, p.status_pesanan, p.nomor_meja
    FROM split_bill sb
    JOIN pemesanan p ON p.id_pemesanan = sb.id_pemesanan
    WHERE sb.token = ?
");
$stmt->bind_param("s", $token);
$stmt->execute();
$split = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$split) { die('Split bill tidak ditemukan atau link salah.'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="../assets/css/lucide-icons.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Split Bill <?= htmlspecialchars($split['kode_pesanan']) ?> – YOLAZCAKE</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    :root{--gold:#D4AF37;--gold-d:#b8860b;--mint:#6efabc;--rose:#ee2a7b;--muted:rgba(255,255,255,.5);}
    body{min-height:100vh;font-family:'Inter',sans-serif;
      background:linear-gradient(160deg,#1e0e3a 0%,#2d1560 50%,#1a0a2e 100%);color:#fff;}
    body::before{content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
      background:radial-gradient(ellipse at 20% 30%,rgba(212,175,55,.10) 0%,transparent 55%),
                 radial-gradient(ellipse at 80% 70%,rgba(232,160,191,.10) 0%,transparent 55%);}
    @keyframes pulseBadge{0%,100%{box-shadow:0 0 0 0 rgba(110,250,188,.5);}50%{box-shadow:0 0 0 8px rgba(110,250,188,0);}}
    @keyframes fadeUp{from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:translateY(0);}}
    @keyframes confettiFall{0%{transform:translateY(-30px) rotate(0);opacity:1;}100%{transform:translateY(110vh) rotate(720deg);opacity:0;}}

    .page-hero{position:relative;height:200px;display:flex;flex-direction:column;align-items:center;justify-content:center;
      background:linear-gradient(135deg,#2b1a11 0%,#4a2c1a 40%,#6d3e26 70%,#3a1f0e 100%);text-align:center;}
    .page-hero h1{font-family:'Playfair Display',serif;font-size:2.1em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,var(--gold) 60%,#FFE4B5 80%,#fff);
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
    .page-hero p{font-size:.85em;color:rgba(255,255,255,.6);margin-top:8px;}

    .wrap{position:relative;z-index:1;max-width:640px;margin:0 auto;padding:28px 18px 90px;}
    .card{background:rgba(255,255,255,.06);backdrop-filter:blur(18px);border:1px solid rgba(255,255,255,.1);
      border-radius:20px;padding:24px;margin-bottom:18px;opacity:0;animation:fadeUp .6s forwards;}

    .top-row{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;}
    .kode{font-family:'Playfair Display',serif;font-size:1.3em;font-weight:700;color:var(--gold);}
    .live-tag{display:inline-flex;align-items:center;gap:6px;font-size:.62em;color:var(--mint);
      text-transform:uppercase;letter-spacing:1px;font-weight:700;}
    .live-dot{width:6px;height:6px;border-radius:50%;background:var(--mint);animation:pulseBadge 1.6s ease-in-out infinite;}

    .total-box{text-align:center;padding:16px;background:rgba(212,175,55,.08);border:1px solid rgba(212,175,55,.25);border-radius:14px;margin:16px 0;}
    .total-box .lbl{font-size:.68em;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);}
    .total-box .val{font-family:'Playfair Display',serif;font-size:1.7em;font-weight:700;color:var(--gold);margin-top:4px;}
    .per-orang{text-align:center;font-size:.85em;color:rgba(255,255,255,.7);margin-top:-6px;margin-bottom:16px;}
    .per-orang strong{color:var(--mint);}

    .progress-bar{width:100%;height:10px;border-radius:999px;background:rgba(255,255,255,.08);overflow:hidden;margin-bottom:6px;}
    .progress-fill{height:100%;background:linear-gradient(90deg,var(--mint),#2fae7a);border-radius:999px;transition:width .5s;}
    .progress-lbl{text-align:center;font-size:.75em;color:var(--muted);margin-bottom:18px;}

    .peserta-list{display:flex;flex-direction:column;gap:10px;}
    .peserta-row{display:flex;align-items:center;gap:12px;background:rgba(255,255,255,.04);
      border:1px solid rgba(255,255,255,.08);border-radius:14px;padding:12px 16px;transition:border-color .3s,background .3s;}
    .peserta-row.paid{border-color:rgba(110,250,188,.4);background:rgba(110,250,188,.06);}
    .peserta-name{flex:1;font-size:.9em;font-weight:600;cursor:text;border-bottom:1px dashed transparent;}
    .peserta-name:hover{border-color:rgba(255,255,255,.3);}
    .peserta-amt{font-size:.78em;color:var(--muted);white-space:nowrap;}
    .btn-toggle{border:none;border-radius:999px;padding:8px 16px;font-size:.72em;font-weight:700;letter-spacing:.5px;
      cursor:pointer;white-space:nowrap;transition:transform .2s;}
    .btn-toggle:hover{transform:translateY(-2px);}
    .btn-toggle.belum{background:rgba(255,255,255,.1);color:rgba(255,255,255,.7);border:1px solid rgba(255,255,255,.2);}
    .btn-toggle.sudah{background:linear-gradient(135deg,var(--mint),#2fae7a);color:#0a2e1e;}

    .share-box{display:flex;gap:10px;flex-wrap:wrap;}
    .share-box input{flex:1;min-width:180px;padding:11px 14px;border-radius:10px;border:1px solid rgba(255,255,255,.15);
      background:rgba(255,255,255,.05);color:rgba(255,255,255,.7);font-size:.78em;}
    .btn-copy{background:rgba(212,175,55,.15);border:1px solid rgba(212,175,55,.4);color:var(--gold);
      border-radius:10px;padding:11px 18px;font-size:.78em;font-weight:600;cursor:pointer;}

    .lunas-banner{text-align:center;padding:16px;background:rgba(110,250,188,.12);border:1px solid rgba(110,250,188,.4);
      border-radius:14px;color:var(--mint);font-weight:700;font-size:.95em;display:none;}
    .lunas-banner.show{display:block;}

    .confetti-piece{position:fixed;top:-30px;border-radius:3px;pointer-events:none;z-index:999;animation:confettiFall linear forwards;}

    #toastWrap{position:fixed;bottom:24px;right:24px;left:24px;z-index:999;display:flex;flex-direction:column;gap:10px;align-items:flex-end;}
    .toast{
      display:flex;align-items:center;gap:12px;padding:13px 18px;border-radius:14px;
      background:rgba(30,14,58,.92);backdrop-filter:blur(16px);
      border:1px solid rgba(110,250,188,.4);box-shadow:0 12px 34px rgba(0,0,0,.4),0 0 30px rgba(110,250,188,.15);
      transform:translateX(120%);opacity:0;transition:transform .4s cubic-bezier(.22,.68,0,1.2),opacity .3s;
      max-width:320px;
    }
    .toast.show{transform:translateX(0);opacity:1;}
    .toast .toast-icon{width:28px;height:28px;border-radius:50%;flex-shrink:0;
      background:linear-gradient(135deg,#6efabc,#2fae7a);display:flex;align-items:center;justify-content:center;
      color:#0a2e1e;font-size:.85em;font-weight:700;}
    .toast .toast-text{font-size:.78em;color:#fff;line-height:1.4;}
    .toast .toast-text strong{color:#6efabc;}
  </style>
</head>
<body>

<div class="page-hero">
  <h1><i data-lucide="users" class="lucide-ic"></i> Split Bill</h1>
  <p>Patungan bareng-bareng, tiap orang tandai bagiannya sendiri</p>
</div>

<div class="wrap">
  <div class="card">
    <div class="top-row">
      <div>
        <div style="font-size:.68em;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);">Pesanan</div>
        <div class="kode"><?= htmlspecialchars($split['kode_pesanan']) ?></div>
      </div>
      <span class="live-tag"><span class="live-dot"></span> Live</span>
    </div>
    <div class="total-box">
      <div class="lbl">Total Tagihan</div>
      <div class="val" id="totalHarga">Rp <?= number_format($split['total_harga'],0,',','.') ?></div>
    </div>
    <div class="per-orang">Dibagi <strong id="jumlahOrang"><?= (int)$split['jumlah_orang'] ?></strong> orang → tiap orang <strong id="perOrang">Rp <?= number_format($split['nominal_per_orang'],0,',','.') ?></strong></div>

    <div class="lunas-banner" id="lunasBanner">🎉 Semua sudah bayar! Lunas total.</div>

    <div class="progress-bar"><div class="progress-fill" id="progressFill" style="width:0%;"></div></div>
    <div class="progress-lbl" id="progressLbl">Memuat…</div>

    <div class="peserta-list" id="pesertaList"></div>
  </div>

  <div class="card">
    <div style="font-size:.82em;font-weight:700;margin-bottom:10px;">🔗 Bagikan link ini ke teman-temanmu</div>
    <div class="share-box">
      <input type="text" id="shareLink" readonly value="">
      <button class="btn-copy" onclick="copyLink()"><i data-lucide="copy" class="lucide-ic"></i> Salin</button>
    </div>
  </div>
</div>

<div id="toastWrap"></div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
const token = <?= json_encode($token) ?>;
document.getElementById('shareLink').value = window.location.href;

function copyLink(){
  const inp = document.getElementById('shareLink');
  inp.select(); inp.setSelectionRange(0, 99999);
  navigator.clipboard?.writeText(inp.value);
  const btn = document.querySelector('.btn-copy');
  const old = btn.innerHTML;
  btn.innerHTML = '✓ Tersalin';
  setTimeout(()=>btn.innerHTML = old, 1500);
}

function renderPeserta(peserta){
  const list = document.getElementById('pesertaList');
  list.innerHTML = '';
  peserta.forEach(p=>{
    const row = document.createElement('div');
    row.className = 'peserta-row' + (p.status_bayar === 'Sudah' ? ' paid' : '');
    row.innerHTML = `
      <span class="peserta-name" contenteditable="true" data-id="${p.id_bayar}">${p.nama_peserta.replace(/</g,'&lt;')}</span>
      <span class="peserta-amt">${document.getElementById('perOrang').textContent}</span>
      <button class="btn-toggle ${p.status_bayar === 'Sudah' ? 'sudah' : 'belum'}" data-id="${p.id_bayar}">
        ${p.status_bayar === 'Sudah' ? '✓ Sudah Bayar' : 'Belum Bayar'}
      </button>
    `;
    list.appendChild(row);
  });

  list.querySelectorAll('.btn-toggle').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const namaPeserta = btn.closest('.peserta-row').querySelector('.peserta-name').textContent.trim();
      const fd = new FormData();
      fd.append('token', token);
      fd.append('action', 'toggle');
      fd.append('id_bayar', btn.dataset.id);
      const akanJadiSudah = btn.classList.contains('belum');
      fetch('split_bill_ajax.php', {method:'POST', body:fd}).then(r=>r.json()).then(data=>{
        showToast(
          akanJadiSudah ? '<strong>'+namaPeserta+'</strong> sudah menandai lunas' : '<strong>'+namaPeserta+'</strong> dibatalkan tandanya',
          akanJadiSudah ? '✓' : '↺'
        );
        refresh(data);
      });
    });
  });

  list.querySelectorAll('.peserta-name').forEach(el=>{
    let lastVal = el.textContent;
    el.addEventListener('blur', ()=>{
      const val = el.textContent.trim();
      if(val && val !== lastVal){
        const namaLama = lastVal;
        lastVal = val;
        const fd = new FormData();
        fd.append('token', token);
        fd.append('action', 'rename');
        fd.append('id_bayar', el.dataset.id);
        fd.append('nama', val);
        fetch('split_bill_ajax.php', {method:'POST', body:fd}).then(r=>r.json()).then(data=>{
          showToast('Nama diubah jadi <strong>'+val+'</strong>', '✎');
          refresh(data);
        });
      }
    });
  });
}

let confettiFired = false;
function showToast(text, icon){
  const wrap = document.getElementById('toastWrap');
  const t = document.createElement('div');
  t.className = 'toast';
  t.innerHTML = `<span class="toast-icon">${icon || '✓'}</span><span class="toast-text">${text}</span>`;
  wrap.appendChild(t);
  requestAnimationFrame(()=>t.classList.add('show'));
  setTimeout(()=>{ t.classList.remove('show'); setTimeout(()=>t.remove(), 400); }, 2400);
}

function fireConfetti(){
  if(confettiFired) return;
  confettiFired = true;
  const colors=['#D4AF37','#ee2a7b','#6efabc','#fff'];
  for(let i=0;i<60;i++){
    const c=document.createElement('div');c.className='confetti-piece';
    const s=Math.random()*8+5;
    c.style.cssText=`width:${s}px;height:${s*0.5}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;animation-duration:${2.5+Math.random()*2}s;`;
    document.body.appendChild(c);
    setTimeout(()=>c.remove(), 5000);
  }
}

function refresh(data){
  if(!data || data.error) return;
  document.getElementById('jumlahOrang').textContent = data.jumlah_orang;
  document.getElementById('progressFill').style.width = Math.round((data.sudah_bayar / data.jumlah_orang) * 100) + '%';
  document.getElementById('progressLbl').textContent = `${data.sudah_bayar} dari ${data.jumlah_orang} sudah bayar`;
  renderPeserta(data.peserta);
  const banner = document.getElementById('lunasBanner');
  if(data.sudah_bayar === data.jumlah_orang){
    banner.classList.add('show');
    fireConfetti();
  } else {
    banner.classList.remove('show');
    confettiFired = false;
  }
}

function poll(){
  const fd = new FormData();
  fd.append('token', token);
  fd.append('action', 'status');
  fetch('split_bill_ajax.php', {method:'POST', body:fd}).then(r=>r.json()).then(refresh).catch(()=>{});
}
poll();
setInterval(poll, 5000);
if(window.lucide){lucide.createIcons();}
</script>
</body>
</html>
