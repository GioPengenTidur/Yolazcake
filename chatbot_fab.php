<?php
/* ============================================================
   FLOATING CHATBOT BUTTON — akses Yola AI (Pusat Bantuan)
   ------------------------------------------------------------
   Include file ini HANYA di 5 halaman ini (sama seperti rating_fab
   & status_fab), TEPAT SETELAH include rating_fab.php:
     index.php, produk/menu.php, gallery.php, about.php, contact.php

   - SELALU muncul, baik user sudah login maupun belum (beda
     dengan rating_fab yang cuma muncul kalau sudah login),
     supaya tetap bisa dipakai untuk tanya-tanya sebelum login.
   - Diposisikan di kanan-bawah, SEJAJAR/DEKAT dengan rating-fab:
       * Kalau rating-fab lagi tampil (user login) -> ditumpuk
         PERSIS DI ATAS rating-fab.
       * Kalau rating-fab tidak tampil (user belum login) ->
         menempati posisi dasar yang sama dengan rating-fab.
   - Ikon bisa diatur lewat config/chatbot_fab_config.php:
       'default' -> sparkle gradient ala Gemini (biru-ungu-pink)
       'gambar'  -> pakai logo/gambar sendiri
   - Self-contained: CSS & JS ada di file ini sendiri, mengikuti
     pola rating_fab.php / status_fab.php.

   Cara pakai di file yang ada di sub-folder (mis. produk/menu.php):
     <?php $chatbotFabBase = '../'; include '../chatbot_fab.php'; ?>

   Cara pakai di file yang ada di root (index.php, gallery.php, dll):
     <?php include 'chatbot_fab.php'; ?>
   ============================================================ */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($chatbotFabBase)) {
    $chatbotFabBase = '';
}

require_once __DIR__ . '/config/chatbot_fab_config.php';

$sudahLoginUntukChat = isset($_SESSION['username']);

// Kalau rating-fab lagi tampil (butuh login), chatbot-fab ditumpuk
// di atasnya. Kalau tidak, chatbot-fab turun menempati posisi dasar.
$chatFabBottomDesktop = $sudahLoginUntukChat ? 132 : 34;
$chatFabBottomMobile  = $sudahLoginUntukChat ? 108 : 24;

$iconMode  = defined('CHATBOT_FAB_ICON_MODE') ? CHATBOT_FAB_ICON_MODE : 'default';
$iconGambar = $chatbotFabBase . (defined('CHATBOT_FAB_IMAGE') ? CHATBOT_FAB_IMAGE : '');
$iconVideo = $chatbotFabBase . (defined('CHATBOT_FAB_VIDEO') ? CHATBOT_FAB_VIDEO : '');
$iconVideoZoom = defined('CHATBOT_FAB_VIDEO_ZOOM') ? CHATBOT_FAB_VIDEO_ZOOM : 1.35;
?>
<style>
  .chatbot-fab-wrap{
    position:fixed; right:28px; bottom:<?= $chatFabBottomDesktop ?>px; z-index:1000;
    display:flex; align-items:center; flex-direction:row-reverse;
  }
  .chatbot-fab{
    position:relative; width:64px; height:64px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    text-decoration:none; cursor:pointer; overflow:hidden;
    background:linear-gradient(135deg,#4C8DF6 0%,#9168F1 45%,#F6577A 80%,#4C8DF6 100%);
    background-size:260% 260%;
    animation:chatbotGradSlide 5s ease infinite, chatbotFloatBob 3.6s ease-in-out infinite;
    box-shadow:0 8px 32px rgba(76,141,246,.4), 0 0 50px rgba(246,87,122,.22);
    transition:transform .35s cubic-bezier(.34,1.56,.64,1), box-shadow .35s;
  }
  .chatbot-fab:hover{
    transform:scale(1.14) rotate(8deg);
    box-shadow:0 14px 46px rgba(76,141,246,.55), 0 0 70px rgba(246,87,122,.32);
  }
  .chatbot-fab-ring{
    position:absolute; inset:0; border-radius:50%;
    border:2px solid rgba(145,104,241,.55);
    animation:chatbotPulseRing 2.6s ease-out infinite;
    pointer-events:none;
  }
  .chatbot-fab-ring.ring-delay{ animation-delay:1.3s; border-color:rgba(246,87,122,.5); }
  .chatbot-fab-icon{
    position:relative; z-index:2; width:30px; height:30px;
    display:flex; align-items:center; justify-content:center;
  }
  .chatbot-fab-icon img,
  .chatbot-fab-icon video{
    width:100%; height:100%; object-fit:cover; object-position:center;
    border-radius:50%; transform:scale(1.55); transform-origin:center;
  }
  .chatbot-fab-icon video{
    transform:scale(<?= htmlspecialchars($iconVideoZoom) ?>);
    pointer-events:none;
    background:#2d1560; /* fallback warna brand, bukan hitam, selama video belum siap tampil */
  }
  .chatbot-fab-badge{
    position:absolute; top:-3px; right:-3px; width:17px; height:17px;
    border-radius:50%; background:linear-gradient(135deg,#4C8DF6,#9168F1);
    border:2.5px solid #FFF8EE; z-index:3;
    display:flex; align-items:center; justify-content:center;
    font-size:.5em; font-weight:800; color:#fff; font-family:'Inter',sans-serif;
    animation:chatbotBadgePulse 2s ease-in-out infinite;
  }
  body.dark .chatbot-fab-badge{ border-color:#121212; }
  .chatbot-fab-tooltip{
    position:absolute; right:76px; white-space:nowrap;
    background:rgba(20,14,40,.92); color:#E7E4FF;
    font-family:'Inter',sans-serif; font-size:.78rem; font-weight:600;
    letter-spacing:.3px; padding:9px 16px; border-radius:999px;
    border:1px solid rgba(145,104,241,.35);
    opacity:0; transform:translateX(8px); pointer-events:none;
    transition:opacity .3s, transform .3s;
  }
  .chatbot-fab-wrap:hover .chatbot-fab-tooltip{ opacity:1; transform:translateX(0); }
  .chatbot-fab-sparkle{
    position:absolute; border-radius:50%; pointer-events:none;
    animation:chatbotSparkleFloat 2.4s ease-out infinite;
  }

  @keyframes chatbotGradSlide{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}
  @keyframes chatbotPulseRing{0%{transform:scale(.9);opacity:.7}70%{transform:scale(1.6);opacity:0}100%{transform:scale(1.6);opacity:0}}
  @keyframes chatbotSparkleFloat{0%{transform:translateY(0) scale(0);opacity:0}30%{opacity:1}100%{transform:translateY(-46px) scale(1);opacity:0}}
  @keyframes chatbotFloatBob{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}
  @keyframes chatbotBadgePulse{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.18);opacity:.85}}

  @media (max-width:768px){
    .chatbot-fab-wrap{ right:18px; bottom:<?= $chatFabBottomMobile ?>px; }
    .chatbot-fab{ width:56px; height:56px; }
    .chatbot-fab-icon{ width:26px; height:26px; }
    .chatbot-fab-tooltip{ display:none; }
  }
</style>

<div class="chatbot-fab-wrap" id="chatbotFabWrap">
  <span class="chatbot-fab-tooltip">Chat sama Yola AI 💬</span>
  <a href="<?= htmlspecialchars($chatbotFabBase) ?>bantuan/index.php"
     class="chatbot-fab" id="chatbotFabBtn"
     aria-label="Chat sama Yola AI"
     title="Chat sama Yola AI">
    <span class="chatbot-fab-ring"></span>
    <span class="chatbot-fab-ring ring-delay"></span>
    <span class="chatbot-fab-icon">
      <?php if ($iconMode === 'video' && !empty($iconVideo)): ?>
        <video src="<?= htmlspecialchars($iconVideo) ?>" poster="<?= htmlspecialchars($iconGambar) ?>" preload="auto" autoplay loop muted playsinline disablepictureinpicture aria-label="Yola AI"></video>
      <?php elseif ($iconMode === 'gambar' && !empty($iconGambar)): ?>
        <img src="<?= htmlspecialchars($iconGambar) ?>" alt="Yola AI">
      <?php else: ?>
        <svg width="100%" height="100%" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <linearGradient id="chatbotSparkleGrad" x1="2" y1="2" x2="22" y2="22" gradientUnits="userSpaceOnUse">
              <stop offset="0%" stop-color="#FFFFFF"/>
              <stop offset="55%" stop-color="#F3EEFF"/>
              <stop offset="100%" stop-color="#FFE9F0"/>
            </linearGradient>
          </defs>
          <path fill="url(#chatbotSparkleGrad)" d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/>
          <path stroke="url(#chatbotSparkleGrad)" stroke-width="1.4" stroke-linecap="round" d="M20 2.5v3M21.5 4h-3"/>
          <path stroke="url(#chatbotSparkleGrad)" stroke-width="1.4" stroke-linecap="round" d="M4 17v2M5 18H3"/>
        </svg>
      <?php endif; ?>
    </span>
    <span class="chatbot-fab-badge">AI</span>
  </a>
</div>

<script>
(function(){
  const wrap = document.getElementById('chatbotFabWrap');
  const btn  = document.getElementById('chatbotFabBtn');
  if(!wrap || !btn) return;

  /* sparkle premium di sekitar tombol, warna senada gradient Gemini */
  const colors = ['#4C8DF6','#9168F1','#F6577A','#fff','#C9A8FF'];
  function spawnSparkle(){
    const s = document.createElement('span');
    s.className = 'chatbot-fab-sparkle';
    const size = Math.random()*4+2;
    const angle = Math.random()*Math.PI*2;
    const dist = 26 + Math.random()*14;
    s.style.cssText = `
      width:${size}px;height:${size}px;
      right:${32 + Math.cos(angle)*dist}px;
      top:${32 + Math.sin(angle)*dist}px;
      background:${colors[Math.floor(Math.random()*colors.length)]};
      box-shadow:0 0 6px ${colors[0]};
    `;
    wrap.appendChild(s);
    setTimeout(()=> s.remove(), 2500);
  }
  setInterval(spawnSparkle, 750);
})();
</script>
