<?php
/**
 * Reusable premium "processing -> success" overlay.
 * Panggil tampilkan_sukses([...]) lalu exit; setelah operasi database selesai.
 */
function tampilkan_sukses($opsi = []){

    $prosesJudul   = $opsi['proses_judul']   ?? 'Memproses…';
    $prosesSub     = $opsi['proses_sub']     ?? 'Mohon tunggu sebentar';
    $suksesJudul   = $opsi['sukses_judul']   ?? 'Berhasil!';
    $suksesSub     = $opsi['sukses_sub']     ?? 'Proses telah selesai';
    $redirect      = $opsi['redirect']       ?? 'data_produk.php';
    $tombolLabel   = $opsi['tombol_label']   ?? 'Lanjutkan ke Data Produk';
    $delaySukses   = (int)($opsi['delay_sukses']   ?? 1300);
    $delayRedirect = (int)($opsi['delay_redirect'] ?? 2700);
    $mode          = $opsi['mode'] ?? 'sukses'; // 'sukses' atau 'hapus' (pengaruh warna aksen proses)
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="../assets/css/lucide-icons.css">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($suksesJudul); ?> – YOLAZCAKE</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}

  html,body{height:100%;}

  body{
    min-height:100vh;
    font-family:'Inter',sans-serif;
    background:linear-gradient(160deg,#1e0e3a 0%,#2d1560 50%,#1a0a2e 100%);
    position:relative;
    overflow:hidden;
    display:flex;
    align-items:center;
    justify-content:center;
  }

  body::before{
    content:'';position:fixed;inset:0;
    background:
      radial-gradient(ellipse at 20% 30%,rgba(212,175,55,.12) 0%,transparent 55%),
      radial-gradient(ellipse at 80% 70%,rgba(232,160,191,.12) 0%,transparent 55%);
    pointer-events:none;z-index:0;
  }

  .particle{position:fixed;border-radius:50%;pointer-events:none;animation:particleFloat linear infinite;z-index:0;}
  @keyframes particleFloat{
    0%{transform:translateY(100vh) scale(0);opacity:0;}
    10%{opacity:.5;}90%{opacity:.3;}
    100%{transform:translateY(-100px) scale(1);opacity:0;}
  }

  /* ── CARD ── */
  .success-card{
    position:relative;z-index:2;
    width:92%;max-width:440px;
    padding:56px 40px 44px;
    text-align:center;
    background:rgba(255,255,255,.06);
    backdrop-filter:blur(22px);
    border:1px solid rgba(255,255,255,.1);
    border-radius:28px;
    overflow:hidden;
    opacity:0;transform:translateY(30px) scale(.96);
    animation:cardIn .7s cubic-bezier(.22,.68,0,1.2) forwards .1s;
    box-shadow:0 25px 65px rgba(0,0,0,.35);
  }

  @keyframes cardIn{to{opacity:1;transform:translateY(0) scale(1);}}

  .success-card::before{
    content:'';position:absolute;top:0;left:0;right:0;height:3px;
    background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);
    background-size:200% 100%;animation:goldSlide 4s linear infinite;
  }
  @keyframes goldSlide{0%{background-position:0% 0;}100%{background-position:200% 0;}}

  .eyebrow{
    font-size:.7em;font-weight:500;letter-spacing:4px;text-transform:uppercase;
    color:#D4AF37;margin-bottom:26px;opacity:.85;
  }

  /* ── ICON ── */
  .icon-wrap{
    position:relative;
    width:110px;height:110px;
    margin:0 auto 30px;
  }

  .spinner-ring{
    position:absolute;inset:0;border-radius:50%;
    border:4px solid rgba(212,175,55,.15);
    border-top-color:#D4AF37;
    border-right-color:#FFE4B5;
    animation:spin .85s linear infinite;
    transition:opacity .35s ease, transform .35s ease;
  }

  .spinner-dot{
    position:absolute;inset:0;display:flex;align-items:center;justify-content:center;
    font-size:1.8em;
    transition:opacity .3s ease;
  }

  @keyframes spin{to{transform:rotate(360deg);}}

  .check-badge{
    position:absolute;inset:0;border-radius:50%;
    background:linear-gradient(135deg,#6efabc 0%,#3ddc97 55%,#1fae74 100%);
    display:flex;align-items:center;justify-content:center;
    transform:scale(0);opacity:0;
    box-shadow:0 0 0 rgba(110,250,188,0);
    transition:transform .55s cubic-bezier(.34,1.56,.64,1), opacity .3s ease, box-shadow .6s ease .1s;
  }

  .check-badge svg{width:52px;height:52px;overflow:visible;}
  .check-badge path{
    fill:none;stroke:#fff;stroke-width:4.5;
    stroke-linecap:round;stroke-linejoin:round;
    stroke-dasharray:40;stroke-dashoffset:40;
    transition:stroke-dashoffset .4s ease .35s;
  }

  .ping-ring{
    position:absolute;inset:0;border-radius:50%;
    border:2px solid rgba(110,250,188,.55);
    opacity:0;
  }

  /* ── SUCCESS STATE ── */
  .icon-wrap.success .spinner-ring{opacity:0;transform:scale(.4) rotate(180deg);}
  .icon-wrap.success .spinner-dot{opacity:0;}
  .icon-wrap.success .check-badge{
    transform:scale(1);opacity:1;
    box-shadow:0 0 30px rgba(110,250,188,.55),0 0 60px rgba(110,250,188,.28);
  }
  .icon-wrap.success .check-badge path{stroke-dashoffset:0;}
  .icon-wrap.success .ping-ring{animation:pingExpand 1.1s ease-out forwards;}
  .icon-wrap.success .ping-ring.p2{animation-delay:.28s;}

  @keyframes pingExpand{
    0%{transform:scale(.7);opacity:.65;}
    100%{transform:scale(2);opacity:0;}
  }

  .burst-particle{
    position:absolute;top:50%;left:50%;
    width:6px;height:6px;border-radius:50%;
    pointer-events:none;
    animation:burstOut .85s ease-out forwards;
  }
  @keyframes burstOut{
    0%{transform:translate(-50%,-50%) scale(1);opacity:1;}
    100%{transform:translate(calc(-50% + var(--tx)),calc(-50% + var(--ty))) scale(0);opacity:0;}
  }

  /* ── TEXT ── */
  .status-title{
    font-family:'Playfair Display',serif;
    font-size:1.55em;font-weight:700;
    color:#fff;
    min-height:1.3em;
    transition:opacity .3s ease;
  }

  .status-title.fade,.status-sub.fade{opacity:0;}

  .status-sub{
    margin-top:10px;
    font-size:.9em;
    color:rgba(255,255,255,.6);
    line-height:1.6;
    min-height:1.4em;
    transition:opacity .3s ease;
  }

  /* ── PROGRESS BAR ── */
  .progress-track{
    margin:26px auto 0;
    width:100%;
    height:5px;
    border-radius:999px;
    background:rgba(255,255,255,.08);
    overflow:hidden;
    transition:opacity .4s ease;
  }
  .progress-track.done{opacity:0;height:0;margin:0;}

  .progress-bar{
    height:100%;
    width:0%;
    border-radius:999px;
    background:linear-gradient(90deg,#D4AF37,#FFE4B5,#D4AF37);
    background-size:200% 100%;
    animation:goldSlide 1.4s linear infinite;
    transition:width 1.1s cubic-bezier(.4,0,.2,1);
  }

  /* ── DIVIDER ── */
  .gold-rule{
    display:flex;align-items:center;gap:10px;
    margin:26px 0 22px;
    opacity:0;transition:opacity .4s ease;
  }
  .gold-rule.show{opacity:1;}
  .gold-rule::before,.gold-rule::after{content:'';flex:1;height:1px;background:linear-gradient(to right,transparent,rgba(212,175,55,.5));}
  .gold-rule::after{background:linear-gradient(to left,transparent,rgba(212,175,55,.5));}
  .gold-rule span{color:#D4AF37;font-size:.65em;letter-spacing:3px;}

  /* ── BUTTON ── */
  .btn-continue{
    position:relative;
    display:inline-flex;align-items:center;gap:8px;
    padding:13px 30px;border:none;border-radius:14px;
    font-family:'Inter',sans-serif;font-size:.82em;font-weight:700;
    letter-spacing:1.5px;text-transform:uppercase;
    text-decoration:none;cursor:pointer;
    background:linear-gradient(135deg,#D4AF37 0%,#b8860b 50%,#D4AF37 100%);
    background-size:200% 100%;color:#1e0e3a;
    animation:goldSlide 3s linear infinite;
    box-shadow:0 8px 28px rgba(212,175,55,.35),0 0 40px rgba(212,175,55,.18);
    overflow:hidden;
    opacity:0;transform:translateY(14px);
    transition:opacity .45s ease, transform .45s ease, box-shadow .35s;
    pointer-events:none;
  }
  .btn-continue.show{opacity:1;transform:translateY(0);pointer-events:auto;}
  .btn-continue:hover{box-shadow:0 12px 40px rgba(212,175,55,.55),0 0 60px rgba(212,175,55,.3);transform:translateY(-3px) scale(1.02);}

  .redirect-note{
    margin-top:16px;font-size:.72em;color:rgba(255,255,255,.35);
    opacity:0;transition:opacity .4s ease;
  }
  .redirect-note.show{opacity:1;}
</style>
</head>
<body>

<div id="particles"></div>

<div class="success-card">
  <p class="eyebrow"><i data-lucide="sparkle" class="lucide-ic"></i> YOLAZCAKE Sintang <i data-lucide="sparkle" class="lucide-ic"></i></p>

  <div class="icon-wrap" id="iconWrap">
    <div class="spinner-ring" id="spinnerRing"></div>
    <div class="spinner-dot">⏳</div>

    <div class="check-badge">
      <svg viewBox="0 0 24 24"><path d="M4 12.5l5 5L20 6"/></svg>
    </div>
    <div class="ping-ring p1"></div>
    <div class="ping-ring p2"></div>
  </div>

  <h2 class="status-title" id="statusTitle"><?= htmlspecialchars($prosesJudul); ?></h2>
  <p class="status-sub" id="statusSub"><?= htmlspecialchars($prosesSub); ?></p>

  <div class="progress-track" id="progressTrack">
    <div class="progress-bar" id="progressBar"></div>
  </div>

  <div class="gold-rule" id="goldRule"><span><i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i></span></div>

  <a href="<?= htmlspecialchars($redirect); ?>" class="btn-continue" id="continueBtn">
    <i data-lucide="sparkle" class="lucide-ic"></i> <?= htmlspecialchars($tombolLabel); ?>
  </a>
  <p class="redirect-note" id="redirectNote">Mengalihkan otomatis…</p>
</div>

<script>
  /* Background particles */
  (function(){
    const c = document.getElementById('particles');
    const colors = ['rgba(212,175,55,.4)','rgba(232,160,191,.3)','rgba(110,250,188,.25)'];
    for(let i = 0; i < 16; i++){
      const p = document.createElement('div'); p.className = 'particle';
      const s = Math.random() * 5 + 2;
      p.style.cssText = `width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;animation-duration:${10+Math.random()*12}s;animation-delay:${Math.random()*10}s;`;
      c.appendChild(p);
    }
  })();

  const DELAY_SUCCESS   = <?= (int)$delaySukses; ?>;
  const DELAY_REDIRECT  = <?= (int)$delayRedirect; ?>;
  const REDIRECT_URL    = <?= json_encode($redirect); ?>;
  const SUKSES_JUDUL    = <?= json_encode($suksesJudul); ?>;
  const SUKSES_SUB      = <?= json_encode($suksesSub); ?>;

  const iconWrap   = document.getElementById('iconWrap');
  const title      = document.getElementById('statusTitle');
  const sub        = document.getElementById('statusSub');
  const progBar    = document.getElementById('progressBar');
  const progTrack  = document.getElementById('progressTrack');
  const goldRule   = document.getElementById('goldRule');
  const contBtn    = document.getElementById('continueBtn');
  const redirNote  = document.getElementById('redirectNote');

  /* Isi progress bar mengikuti durasi proses */
  requestAnimationFrame(() => { progBar.style.width = '100%'; });

  function burstSparkles(){
    const colors = ['#D4AF37','#FFE4B5','#6efabc','#ee2a7b','#fff'];
    for(let i = 0; i < 16; i++){
      const p = document.createElement('div');
      p.className = 'burst-particle';
      const angle = (Math.PI * 2 * i) / 16 + (Math.random()*0.3);
      const dist  = 55 + Math.random() * 35;
      p.style.setProperty('--tx', Math.cos(angle) * dist + 'px');
      p.style.setProperty('--ty', Math.sin(angle) * dist + 'px');
      p.style.background = colors[Math.floor(Math.random()*colors.length)];
      p.style.animationDelay = (Math.random()*0.08) + 's';
      iconWrap.appendChild(p);
      setTimeout(() => p.remove(), 1000);
    }
  }

  setTimeout(() => {
    /* Transisi teks */
    title.classList.add('fade');
    sub.classList.add('fade');

    setTimeout(() => {
      title.textContent = SUKSES_JUDUL;
      sub.textContent   = SUKSES_SUB;
      title.classList.remove('fade');
      sub.classList.remove('fade');
    }, 260);

    progTrack.classList.add('done');
    iconWrap.classList.add('success');
    burstSparkles();

    goldRule.classList.add('show');
    contBtn.classList.add('show');
    redirNote.classList.add('show');

  }, DELAY_SUCCESS);

  setTimeout(() => {
    window.location.href = REDIRECT_URL;
  }, DELAY_REDIRECT);
</script>


<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide){lucide.createIcons();}</script>
</body>
</html>
<?php
}
