<?php
/* ============================================================
   FLOATING RATING BUTTON — akses Rating Tempat & Makanan

   - HANYA muncul kalau user SUDAH LOGIN ($_SESSION['username']).

   - Diposisikan di kanan-bawah supaya tidak bentrok dengan
     status-fab (booking/pesanan) yang ada di kiri-bawah.

   ============================================================ */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($ratingFabBase)) {
    $ratingFabBase = '';
}

$sudahLogin = isset($_SESSION['username']);

if ($sudahLogin):
?>
<style>
  .rating-fab-wrap{
    position:fixed; right:28px; bottom:34px; z-index:1000;
    display:flex; align-items:center; flex-direction:row-reverse;
  }
  .rating-fab{
    position:relative; width:64px; height:64px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    text-decoration:none; font-size:1.55em; cursor:pointer;
    background:linear-gradient(135deg,#E8A0BF 0%,#D4AF37 40%,#A07C10 70%,#E8A0BF 100%);
    background-size:240% 240%;
    animation:ratingGoldSlide 4s ease infinite, ratingFloatBob 3.2s ease-in-out infinite;
    box-shadow:0 8px 32px rgba(212,175,55,.45), 0 0 50px rgba(232,160,191,.22);
    transition:transform .35s cubic-bezier(.34,1.56,.64,1), box-shadow .35s;
  }
  .rating-fab:hover{
    transform:scale(1.14) rotate(-10deg);
    box-shadow:0 14px 46px rgba(212,175,55,.6), 0 0 70px rgba(232,160,191,.32);
  }
  .rating-fab-ring{
    position:absolute; inset:0; border-radius:50%;
    border:2px solid rgba(212,175,55,.55);
    animation:ratingPulseRing 2.6s ease-out infinite;
    pointer-events:none;
  }
  .rating-fab-ring.ring-delay{ animation-delay:1.3s; border-color:rgba(232,160,191,.5); }
  .rating-fab-star{
    position:relative; z-index:2;
    animation:ratingStarSpin 5s linear infinite;
    display:inline-block;
  }
  .rating-fab-tooltip{
    position:absolute; right:76px; white-space:nowrap;
    background:rgba(45,24,16,.92); color:#FFE4B5;
    font-family:'Inter',sans-serif; font-size:.78rem; font-weight:600;
    letter-spacing:.3px; padding:9px 16px; border-radius:999px;
    border:1px solid rgba(212,175,55,.35);
    opacity:0; transform:translateX(8px); pointer-events:none;
    transition:opacity .3s, transform .3s;
  }
  .rating-fab-wrap:hover .rating-fab-tooltip{ opacity:1; transform:translateX(0); }
  .rating-fab-sparkle{
    position:absolute; border-radius:50%; pointer-events:none;
    animation:ratingSparkleFloat 2.4s ease-out infinite;
  }

  @keyframes ratingGoldSlide{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}
  @keyframes ratingPulseRing{0%{transform:scale(.9);opacity:.7}70%{transform:scale(1.6);opacity:0}100%{transform:scale(1.6);opacity:0}}
  @keyframes ratingSparkleFloat{0%{transform:translateY(0) scale(0);opacity:0}30%{opacity:1}100%{transform:translateY(-46px) scale(1);opacity:0}}
  @keyframes ratingFloatBob{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}
  @keyframes ratingStarSpin{
    0%,85%{ transform:rotate(0deg) scale(1); }
    92%{ transform:rotate(180deg) scale(1.25); }
    100%{ transform:rotate(360deg) scale(1); }
  }

  @media (max-width:768px){
    .rating-fab-wrap{ right:18px; bottom:24px; }
    .rating-fab{ width:56px; height:56px; font-size:1.35em; }
    .rating-fab-tooltip{ display:none; }
  }
</style>

<div class="rating-fab-wrap" id="ratingFabWrap">
  <span class="rating-fab-tooltip">Rating Tempat &amp; Makanan</span>
  <a href="<?= htmlspecialchars($ratingFabBase) ?>ulasan/tempat.php"
     class="rating-fab" id="ratingFabBtn"
     aria-label="Rating Tempat & Makanan"
     title="Rating Tempat & Makanan">
    <span class="rating-fab-ring"></span>
    <span class="rating-fab-ring ring-delay"></span>
    <span class="rating-fab-star"><i data-lucide="star" class="lucide-ic lucide-fill"></i></span>
  </a>
</div>

<script>
(function(){
  const wrap = document.getElementById('ratingFabWrap');
  const btn  = document.getElementById('ratingFabBtn');
  if(!wrap || !btn) return;

  /* sparkle di sekitar tombol */
  const colors = ['#D4AF37','#FFE4B5','#E8A0BF','#fff','#f9ce34'];
  function spawnSparkle(){
    const s = document.createElement('span');
    s.className = 'rating-fab-sparkle';
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
  setInterval(spawnSparkle, 700);
})();
</script>
<?php endif; ?>
