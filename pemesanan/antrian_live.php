<?php
session_start();
require_once __DIR__.'/../config/staff_guard.php';
require_staff_login();
require_once __DIR__.'/../config/csrf_helper.php';
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="../assets/css/lucide-icons.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Live Antrian & Meja – YOLAZCAKE</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    :root{
      --gold:#D4AF37;--gold-d:#b8860b;--rose:#ee2a7b;--mint:#6efabc;
      --glass:rgba(255,255,255,0.05);--muted:rgba(255,255,255,0.5);
    }
    body{min-height:100vh;font-family:'Inter',sans-serif;
      background:linear-gradient(160deg,#1e0e3a 0%,#2d1560 50%,#1a0a2e 100%);
      color:#fff;overflow-x:hidden;}
    body::before{content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
      background:radial-gradient(ellipse at 20% 30%,rgba(212,175,55,.10) 0%,transparent 55%),
                 radial-gradient(ellipse at 80% 70%,rgba(232,160,191,.10) 0%,transparent 55%);}
    @keyframes pulseBadge{0%,100%{box-shadow:0 0 0 0 rgba(110,250,188,.5);}50%{box-shadow:0 0 0 8px rgba(110,250,188,0);}}
    @keyframes cardReveal{from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:translateY(0);}}

    .page-hero{position:relative;height:190px;display:flex;flex-direction:column;
      align-items:center;justify-content:center;overflow:hidden;
      background:linear-gradient(135deg,#2b1a11 0%,#4a2c1a 40%,#6d3e26 70%,#3a1f0e 100%);z-index:1;text-align:center;}
    .hero-eyebrow{font-size:.72em;font-weight:500;letter-spacing:5px;text-transform:uppercase;color:var(--gold);margin-bottom:8px;}
    .page-hero h1{font-family:'Playfair Display',serif;font-size:2.2em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,var(--gold) 60%,#FFE4B5 80%,#fff);
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
    .live-tag{display:inline-flex;align-items:center;gap:6px;font-size:.65em;color:var(--mint);
      text-transform:uppercase;letter-spacing:1px;font-weight:700;margin-top:8px;}
    .live-dot{width:7px;height:7px;border-radius:50%;background:var(--mint);animation:pulseBadge 1.6s ease-in-out infinite;}

    .page-wrapper{position:relative;z-index:1;padding:28px 26px 80px;max-width:1300px;margin:0 auto;}
    .btn-back{display:inline-flex;align-items:center;gap:8px;padding:10px 22px;
      background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.3);color:var(--gold);
      font-size:.82em;font-weight:600;letter-spacing:1px;border-radius:999px;text-decoration:none;
      margin-bottom:24px;transition:transform .25s,background .3s;}
    .btn-back:hover{transform:translateX(-3px);background:rgba(212,175,55,.2);}

    .section-title{font-family:'Playfair Display',serif;font-size:1.3em;font-weight:700;margin:26px 0 14px;
      display:flex;align-items:center;gap:10px;}

    /* KANBAN */
    .kanban{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;}
    .kanban-col{background:var(--glass);border:1px solid rgba(255,255,255,.1);border-radius:18px;padding:16px;min-height:120px;}
    .kanban-col h3{font-size:.8em;letter-spacing:1px;text-transform:uppercase;margin-bottom:12px;
      display:flex;align-items:center;justify-content:space-between;color:var(--muted);}
    .kanban-col h3 .count{background:rgba(212,175,55,.2);color:var(--gold);border-radius:999px;padding:2px 10px;font-size:.9em;}
    .order-card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:14px;
      padding:14px;margin-bottom:10px;animation:cardReveal .4s;transition:border-color .3s;}
    .order-card:hover{border-color:rgba(212,175,55,.35);}
    .order-kode{font-weight:700;font-size:.85em;color:var(--gold);}
    .order-meta{font-size:.72em;color:var(--muted);margin-top:3px;line-height:1.6;}
    .order-actions{display:flex;gap:6px;margin-top:10px;flex-wrap:wrap;}
    .btn-mini{border:none;border-radius:8px;padding:6px 10px;font-size:.68em;font-weight:700;cursor:pointer;
      transition:transform .2s;letter-spacing:.3px;}
    .btn-mini:hover{transform:translateY(-2px);}
    .btn-mini.next{background:linear-gradient(135deg,var(--gold),var(--gold-d));color:#1e0e3a;}
    .btn-mini.done{background:linear-gradient(135deg,var(--mint),#2fae7a);color:#0a2e1e;}
    .btn-mini.cancel{background:rgba(239,68,68,.18);color:#fca5a5;border:1px solid rgba(239,68,68,.35);}
    .empty-col{text-align:center;color:var(--muted);font-size:.78em;padding:20px 0;}

    /* MEJA */
    .meja-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px;}
    .meja-mini{background:var(--glass);border:1px solid rgba(255,255,255,.1);border-radius:14px;padding:14px;text-align:center;}
    .meja-mini .num{font-family:'Playfair Display',serif;font-size:1.3em;font-weight:700;color:var(--gold);}
    .meja-mini .cap{font-size:.7em;color:var(--muted);margin:2px 0 8px;}
    .meja-status{display:inline-block;padding:4px 12px;border-radius:999px;font-size:.65em;font-weight:700;letter-spacing:.5px;text-transform:uppercase;}
    .s-tersedia{background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.35);color:#6ee7b7;}
    .s-terisi{background:rgba(239,68,68,.14);border:1px solid rgba(239,68,68,.35);color:#fca5a5;}
    .s-dipesan{background:rgba(212,175,55,.12);border:1px solid rgba(212,175,55,.35);color:var(--gold);}
    .s-nonaktif{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.15);color:var(--muted);}
    .btn-reset-meja{display:block;width:100%;margin-top:8px;border:none;border-radius:8px;padding:6px 0;
      font-size:.65em;font-weight:700;letter-spacing:.3px;cursor:pointer;background:rgba(212,175,55,.15);
      color:var(--gold);border:1px solid rgba(212,175,55,.3);transition:transform .2s;}
    .btn-reset-meja:hover{transform:translateY(-2px);background:rgba(212,175,55,.28);}

    @media(max-width:900px){ .kanban{grid-template-columns:1fr;} }

    /* ── TOAST SUKSES (konsisten sama success_overlay, versi ringkas) ── */
    #toastWrap{position:fixed;bottom:24px;right:24px;z-index:999;display:flex;flex-direction:column;gap:10px;align-items:flex-end;}
    .toast{
      display:flex;align-items:center;gap:12px;padding:14px 20px;border-radius:14px;
      background:rgba(30,14,58,.92);backdrop-filter:blur(16px);
      border:1px solid rgba(110,250,188,.4);box-shadow:0 12px 34px rgba(0,0,0,.4),0 0 30px rgba(110,250,188,.15);
      transform:translateX(120%);opacity:0;transition:transform .4s cubic-bezier(.22,.68,0,1.2),opacity .3s;
      max-width:320px;
    }
    .toast.show{transform:translateX(0);opacity:1;}
    .toast .toast-icon{width:30px;height:30px;border-radius:50%;flex-shrink:0;
      background:linear-gradient(135deg,#6efabc,#2fae7a);display:flex;align-items:center;justify-content:center;
      color:#0a2e1e;font-size:.9em;font-weight:700;}
    .toast .toast-text{font-size:.8em;color:#fff;line-height:1.4;}
    .toast .toast-text strong{color:#6efabc;}
  </style>
</head>
<body>

<div class="page-hero">
  <p class="hero-eyebrow"><i data-lucide="radio" class="lucide-ic"></i> YOLAZCAKE Sintang</p>
  <h1>Live Antrian & Status Meja</h1>
  <span class="live-tag"><span class="live-dot"></span> Update otomatis tiap 5 detik</span>
</div>

<div class="page-wrapper">
  <a href="../dashboard.php" class="btn-back"><i data-lucide="arrow-left" class="lucide-ic"></i> Dashboard</a>

  <div class="section-title"><i data-lucide="clipboard-list" class="lucide-ic"></i> Antrian Pesanan Aktif</div>
  <div class="kanban" id="kanban">
    <div class="kanban-col" data-status="Menunggu">
      <h3>Menunggu <span class="count" id="countMenunggu">0</span></h3>
      <div class="col-body" id="colMenunggu"></div>
    </div>
    <div class="kanban-col" data-status="Diproses">
      <h3>Diproses <span class="count" id="countDiproses">0</span></h3>
      <div class="col-body" id="colDiproses"></div>
    </div>
    <div class="kanban-col" data-status="Siap Diambil">
      <h3>Siap Diambil <span class="count" id="countSiap">0</span></h3>
      <div class="col-body" id="colSiap"></div>
    </div>
  </div>

  <div class="section-title"><i data-lucide="armchair" class="lucide-ic"></i> Status Meja</div>
  <div class="meja-grid" id="mejaGrid"></div>
</div>

<div id="toastWrap"></div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
const CSRF_TOKEN = <?= json_encode($csrf); ?>;
const statusClass = {'Tersedia':'s-tersedia','Terisi':'s-terisi','Dipesan':'s-dipesan','Tidak Aktif':'s-nonaktif'};
const nextStatus = {'Menunggu':'Diproses','Diproses':'Siap Diambil','Siap Diambil':'Selesai'};
const nextLabel  = {'Menunggu':'Mulai Proses','Diproses':'Siap Diambil','Siap Diambil':'Selesai'};

function showToast(text, icon){
  const wrap = document.getElementById('toastWrap');
  const t = document.createElement('div');
  t.className = 'toast';
  t.innerHTML = `<span class="toast-icon">${icon || '✓'}</span><span class="toast-text">${text}</span>`;
  wrap.appendChild(t);
  requestAnimationFrame(()=>t.classList.add('show'));
  setTimeout(()=>{
    t.classList.remove('show');
    setTimeout(()=>t.remove(), 400);
  }, 2600);
}

function ubahStatusPesanan(id, status, kode){
  const fd = new FormData();
  fd.append('tipe','pesanan'); fd.append('id', id); fd.append('status', status); fd.append('csrf', CSRF_TOKEN);
  fetch('ubah_status_cepat.php', {method:'POST', body:fd})
    .then(r=>r.json())
    .then(res=>{
      if(res && res.ok){
        const label = status === 'Dibatalkan' ? 'dibatalkan' : 'diubah jadi <strong>'+status+'</strong>';
        showToast('Pesanan <strong>'+(kode||'')+'</strong> '+label, status === 'Dibatalkan' ? '✕' : '✓');
      }
      load();
    })
    .catch(()=>load());
}

function renderPesanan(list){
  const cols = {'Menunggu':document.getElementById('colMenunggu'),'Diproses':document.getElementById('colDiproses'),'Siap Diambil':document.getElementById('colSiap')};
  Object.values(cols).forEach(c=>c.innerHTML='');
  const counts = {'Menunggu':0,'Diproses':0,'Siap Diambil':0};

  list.forEach(p=>{
    counts[p.status_pesanan] = (counts[p.status_pesanan]||0)+1;
    const card = document.createElement('div');
    card.className = 'order-card';
    const waktu = p.tanggal ? p.tanggal.split(' ')[1]?.slice(0,5) : '';
    card.innerHTML = `
      <div class="order-kode">${p.kode_pesanan}</div>
      <div class="order-meta">
        👤 ${p.nama_pemesan || '-'}${p.nomor_meja ? ' · 🪑 Meja '+p.nomor_meja : ''}<br>
        💰 Rp ${Number(p.total_harga).toLocaleString('id-ID')} · ⏰ ${waktu}
      </div>
      <div class="order-actions"></div>
    `;
    const actions = card.querySelector('.order-actions');
    if(nextStatus[p.status_pesanan]){
      const btn = document.createElement('button');
      btn.className = 'btn-mini ' + (nextStatus[p.status_pesanan] === 'Selesai' ? 'done' : 'next');
      btn.textContent = nextLabel[p.status_pesanan];
      btn.onclick = ()=>ubahStatusPesanan(p.id_pemesanan, nextStatus[p.status_pesanan], p.kode_pesanan);
      actions.appendChild(btn);
    }
    const btnCancel = document.createElement('button');
    btnCancel.className = 'btn-mini cancel';
    btnCancel.textContent = 'Batalkan';
    btnCancel.onclick = ()=>{ if(confirm('Batalkan pesanan '+p.kode_pesanan+'?')) ubahStatusPesanan(p.id_pemesanan, 'Dibatalkan', p.kode_pesanan); };
    actions.appendChild(btnCancel);

    cols[p.status_pesanan].appendChild(card);
  });

  document.getElementById('countMenunggu').textContent = counts['Menunggu'];
  document.getElementById('countDiproses').textContent = counts['Diproses'];
  document.getElementById('countSiap').textContent = counts['Siap Diambil'];

  Object.entries(cols).forEach(([k,el])=>{
    if(!el.children.length) el.innerHTML = '<div class="empty-col">Tidak ada pesanan</div>';
  });
}

function ubahStatusMeja(id, status, nomor){
  const fd = new FormData();
  fd.append('tipe','meja'); fd.append('id', id); fd.append('status', status); fd.append('csrf', CSRF_TOKEN);
  fetch('ubah_status_cepat.php', {method:'POST', body:fd})
    .then(r=>r.json())
    .then(res=>{
      if(res && res.ok){ showToast('Meja <strong>'+nomor+'</strong> direset jadi <strong>Tersedia</strong>'); }
      load();
    })
    .catch(()=>load());
}

function renderMeja(list){
  const grid = document.getElementById('mejaGrid');
  grid.innerHTML = '';
  list.forEach(m=>{
    const el = document.createElement('div');
    el.className = 'meja-mini';
    el.innerHTML = `
      <div class="num">${m.nomor_meja}</div>
      <div class="cap">${m.kapasitas} orang</div>
      <span class="meja-status ${statusClass[m.status] || 's-nonaktif'}">${m.status}</span>
    `;
    if(m.status === 'Terisi'){
      const btn = document.createElement('button');
      btn.className = 'btn-reset-meja';
      btn.textContent = 'Reset ke Tersedia';
      btn.onclick = ()=>ubahStatusMeja(m.id_meja, 'Tersedia', m.nomor_meja);
      el.appendChild(btn);
    }
    grid.appendChild(el);
  });
}

function load(){
  fetch('live_ajax.php').then(r=>r.json()).then(data=>{
    if(data.error) return;
    renderPesanan(data.pesanan || []);
    renderMeja(data.meja || []);
  }).catch(()=>{});
}
load();
setInterval(load, 5000);
if(window.lucide){lucide.createIcons();}
</script>
</body>
</html>
