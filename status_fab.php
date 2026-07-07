<?php
/* ============================================================
   FLOATING STATUS BUTTON — cek status booking & pesanan
   ------------------------------------------------------------
   Include file ini di HALAMAN INI SAJA:
     index.php, produk/menu.php, gallery.php, about.php, contact.php

   - HANYA muncul kalau user sudah booking ATAU sudah pesan
     (dicek dari $_SESSION['id_booking'] / $_SESSION['no_hp']).
   - Self-contained: CSS & JS ada di file ini sendiri, jadi TIDAK
     bergantung pada landing.css (yang cuma di-load index.php).
     Ini penyebab utama kenapa sebelumnya cuma muncul teks polos
     di halaman selain index.

   Cara pakai di file yang ada di sub-folder (mis. produk/menu.php):
     <?php $statusFabBase = '../'; include '../status_fab.php'; ?>

   Cara pakai di file yang ada di root (index.php, gallery.php, dll):
     <?php include 'status_fab.php'; ?>
   ============================================================ */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($statusFabBase)) {
    $statusFabBase = '';
}

$adaBookingAtauPesanan = !empty($_SESSION['id_booking']) || !empty($_SESSION['no_hp']);

if ($adaBookingAtauPesanan):
?>
<style>
  .status-fab-wrap{
    position:fixed; left:28px; bottom:34px; z-index:1000;
    display:flex; align-items:center;
  }
  .status-fab{
    position:relative; width:64px; height:64px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    text-decoration:none; font-size:1.55em; cursor:pointer;
    background:linear-gradient(135deg,#D4AF37 0%,#A07C10 35%,#E8A0BF 70%,#D4AF37 100%);
    background-size:240% 240%;
    animation:statusGoldSlide 4s ease infinite, statusFloatBob 3.4s ease-in-out infinite;
    box-shadow:0 8px 32px rgba(212,175,55,.45), 0 0 50px rgba(232,160,191,.18);
    transition:transform .35s cubic-bezier(.34,1.56,.64,1), box-shadow .35s;
  }
  .status-fab:hover{
    transform:scale(1.12) rotate(8deg);
    box-shadow:0 14px 46px rgba(212,175,55,.6), 0 0 70px rgba(232,160,191,.3);
  }
  /* efek "bergetar seperti telepon berdering" — dipicu berkala lewat JS */
  .status-fab.is-ringing{
    animation:statusGoldSlide 4s ease infinite, statusRingShake .8s ease-in-out;
  }
  .status-fab-ring{
    position:absolute; inset:0; border-radius:50%;
    border:2px solid rgba(212,175,55,.55);
    animation:statusPulseRing 2.6s ease-out infinite;
    pointer-events:none;
  }
  .status-fab-ring.ring-delay{ animation-delay:1.3s; border-color:rgba(232,160,191,.5); }
  .status-fab-badge{
    position:absolute; top:-4px; right:-4px; width:16px; height:16px;
    border-radius:50%; background:linear-gradient(135deg,#EE2A7B,#E8A0BF);
    border:2.5px solid #FFF8EE; z-index:2;
    animation:statusBadgePulse 2s ease-in-out infinite;
  }
  body.dark .status-fab-badge{ border-color:#121212; }
  .status-fab-tooltip{
    position:absolute; left:76px; white-space:nowrap;
    background:rgba(45,24,16,.92); color:#FFE4B5;
    font-family:'Inter',sans-serif; font-size:.78rem; font-weight:600;
    letter-spacing:.3px; padding:9px 16px; border-radius:999px;
    border:1px solid rgba(212,175,55,.35);
    opacity:0; transform:translateX(-8px); pointer-events:none;
    transition:opacity .3s, transform .3s;
  }
  .status-fab-wrap:hover .status-fab-tooltip{ opacity:1; transform:translateX(0); }
  .status-fab-sparkle{
    position:absolute; border-radius:50%; pointer-events:none;
    animation:statusSparkleFloat 2.4s ease-out infinite;
  }

  @keyframes statusGoldSlide{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}
  @keyframes statusPulseRing{0%{transform:scale(.9);opacity:.7}70%{transform:scale(1.6);opacity:0}100%{transform:scale(1.6);opacity:0}}
  @keyframes statusBadgePulse{0%,100%{box-shadow:0 0 0 0 rgba(232,160,191,.6)}50%{box-shadow:0 0 0 8px rgba(232,160,191,0)}}
  @keyframes statusSparkleFloat{0%{transform:translateY(0) scale(0);opacity:0}30%{opacity:1}100%{transform:translateY(-46px) scale(1);opacity:0}}
  @keyframes statusFloatBob{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}
  @keyframes statusRingShake{
    0%,100%{ transform:translateY(0) rotate(0deg); }
    10%{ transform:translateY(-4px) rotate(-13deg); }
    20%{ transform:translateY(0) rotate(11deg); }
    30%{ transform:translateY(-3px) rotate(-10deg); }
    40%{ transform:translateY(0) rotate(8deg); }
    50%{ transform:translateY(-3px) rotate(-7deg); }
    60%{ transform:translateY(0) rotate(5deg); }
    70%{ transform:translateY(-2px) rotate(-3deg); }
    80%{ transform:translateY(0) rotate(2deg); }
    90%{ transform:translateY(-1px) rotate(0deg); }
  }

  @media (max-width:768px){
    .status-fab-wrap{ left:18px; bottom:24px; }
    .status-fab{ width:56px; height:56px; font-size:1.35em; }
    .status-fab-tooltip{ display:none; }
  }
</style>

<div class="status-fab-wrap" id="statusFabWrap">
  <span class="status-fab-tooltip">Cek Status Booking & Pesanan</span>
  <a href="<?= htmlspecialchars($statusFabBase) ?>status.php"
     class="status-fab" id="statusFabBtn"
     aria-label="Cek Status Booking & Pesanan"
     title="Cek Status Booking & Pesanan">
    <span class="status-fab-ring"></span>
    <span class="status-fab-ring ring-delay"></span>
    <i data-lucide="clipboard-list" class="lucide-ic"></i>
    <span class="status-fab-badge"></span>
  </a>
</div>

<script>
(function(){
  const wrap = document.getElementById('statusFabWrap');
  const btn  = document.getElementById('statusFabBtn');
  if(!wrap || !btn) return;

  /* sparkle premium di sekitar tombol */
  const colors = ['#D4AF37','#FFE4B5','#E8A0BF','#fff','#f9ce34'];
  function spawnSparkle(){
    const s = document.createElement('span');
    s.className = 'status-fab-sparkle';
    const size = Math.random()*4+2;
    const angle = Math.random()*Math.PI*2;
    const dist = 26 + Math.random()*14;
    s.style.cssText = `
      width:${size}px;height:${size}px;
      left:${32 + Math.cos(angle)*dist}px;
      top:${32 + Math.sin(angle)*dist}px;
      background:${colors[Math.floor(Math.random()*colors.length)]};
      box-shadow:0 0 6px ${colors[0]};
    `;
    wrap.appendChild(s);
    setTimeout(()=> s.remove(), 2500);
  }
  setInterval(spawnSparkle, 650);

  /* efek bergetar seperti telepon berdering, berkala */
  function ring(){
    btn.classList.add('is-ringing');
    setTimeout(()=> btn.classList.remove('is-ringing'), 800);
  }
  setTimeout(ring, 1500);
  setInterval(ring, 6000);
})();
</script>
<?php endif; ?>
