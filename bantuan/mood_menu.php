<?php
session_start();
$sudahLogin = isset($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="../assets/css/lucide-icons.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rekomendasi Mood – YOLAZCAKE</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    :root{
      --gold:#D4AF37; --gold-l:#FFE88A;
      --rose:#EE2A7B; --purple:#8A2BE2;
      --bg1:#0d0520; --bg2:#1a0a3a; --bg3:#150830;
      --glass:rgba(255,255,255,.045); --gb:rgba(255,255,255,.10);
      --text:#fff; --muted:rgba(255,255,255,.5);
    }
    html,body{height:100%;}
    body{
      min-height:100vh;font-family:'Inter',sans-serif;
      background:linear-gradient(140deg,var(--bg1) 0%,var(--bg2) 50%,var(--bg3) 100%);
      color:var(--text);display:flex;align-items:center;justify-content:center;
      padding:24px;position:relative;overflow-x:hidden;
    }
    body::before{
      content:'';position:fixed;inset:0;pointer-events:none;
      background:radial-gradient(ellipse 60% 50% at 15% 15%,rgba(212,175,55,.10) 0%,transparent 55%),
                 radial-gradient(ellipse 55% 50% at 85% 85%,rgba(138,43,226,.12) 0%,transparent 55%);
      animation:drift 12s ease-in-out infinite alternate;
    }
    @keyframes drift{0%{opacity:.7;}100%{opacity:1;transform:scale(1.05);}}

    .wrap{
      position:relative;z-index:2;width:100%;max-width:640px;
      background:var(--glass);backdrop-filter:blur(24px);
      border:1px solid var(--gb);border-radius:26px;overflow:hidden;
      box-shadow:0 30px 80px rgba(0,0,0,.45);
      opacity:0;transform:translateY(24px);animation:cardIn .7s cubic-bezier(.22,.68,0,1.2) forwards .1s;
    }
    @keyframes cardIn{to{opacity:1;transform:translateY(0);}}

    .head{
      display:flex;align-items:center;gap:14px;padding:22px 26px;
      border-bottom:1px solid var(--gb);
      background:linear-gradient(90deg,rgba(212,175,55,.08),transparent);
    }
    .head-icon{
      width:46px;height:46px;border-radius:14px;flex-shrink:0;overflow:hidden;
      background:linear-gradient(135deg,var(--purple),var(--rose) 60%,var(--gold));
      display:flex;align-items:center;justify-content:center;
      box-shadow:0 4px 14px rgba(0,0,0,.35);
    }
    .head-icon img{width:100%;height:100%;object-fit:cover;object-position:center;transform:scale(1.55);}
    .head h1{font-family:'Playfair Display',serif;font-size:1.15em;font-weight:700;}
    .head .sub{font-size:.72em;color:var(--muted);margin-top:2px;}
    .back-link{
      margin-left:auto;color:var(--muted);text-decoration:none;font-size:.78em;
      display:flex;align-items:center;gap:6px;padding:7px 12px;border-radius:10px;
      border:1px solid rgba(255,255,255,.1);transition:.2s;flex-shrink:0;
    }
    .back-link:hover{color:#fff;background:rgba(255,255,255,.06);}

    .body{padding:24px 26px 28px;}

    .step{display:none;}
    .step.active{display:block;animation:cardIn .4s forwards;}

    .q-label{font-size:.9em;font-weight:600;color:#EDE9FF;margin-bottom:14px;}
    .opsi-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
    .opsi{
      display:flex;flex-direction:column;align-items:center;gap:8px;
      padding:18px 10px;border-radius:16px;cursor:pointer;text-align:center;
      background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);
      transition:.2s;
    }
    .opsi:hover{background:rgba(212,175,55,.12);border-color:rgba(212,175,55,.35);transform:translateY(-2px);}
    .opsi.selected{background:rgba(212,175,55,.2);border-color:var(--gold);}
    .opsi .emoji{font-size:1.7em;}
    .opsi .label{font-size:.8em;font-weight:600;color:#EDE9FF;}

    .progress-dots{display:flex;gap:6px;justify-content:center;margin:18px 0 4px;}
    .dot{width:6px;height:6px;border-radius:50%;background:rgba(255,255,255,.18);transition:.3s;}
    .dot.done{background:var(--gold);}

    .loading-row{display:none;align-items:center;justify-content:center;gap:10px;padding:30px 0;color:var(--muted);font-size:.85em;}
    .loading-row.show{display:flex;}
    .spinner{
      width:18px;height:18px;border-radius:50%;flex-shrink:0;
      border:2.5px solid rgba(255,255,255,.18);border-top-color:var(--gold-l);
      animation:spin .7s linear infinite;
    }
    @keyframes spin{to{transform:rotate(360deg);}}

    .hasil-wrap{display:none;}
    .hasil-wrap.show{display:block;animation:cardIn .5s forwards;}
    .hasil-label{
      font-size:.7em;letter-spacing:.6px;text-transform:uppercase;color:var(--gold-l);
      margin-bottom:8px;display:flex;align-items:center;gap:6px;
    }
    .hasil-card{
      background:rgba(255,255,255,.05);border:1px solid rgba(212,175,55,.25);
      border-radius:16px;padding:18px 20px;font-size:.88em;line-height:1.7;
      white-space:pre-wrap;color:#EDE9FF;
    }
    .btn-ulang{
      margin-top:16px;width:100%;padding:12px;border-radius:12px;cursor:pointer;
      background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.14);color:var(--muted);
      font-family:'Inter',sans-serif;font-weight:600;font-size:.82em;
      display:flex;align-items:center;justify-content:center;gap:8px;transition:.2s;
    }
    .btn-ulang:hover{color:#fff;background:rgba(255,255,255,.08);}

    .error-box{
      display:none;margin-top:16px;padding:12px 16px;border-radius:12px;
      background:rgba(246,87,122,.12);border:1px solid rgba(246,87,122,.35);
      color:#FFD1E6;font-size:.82em;
    }
    .error-box.show{display:block;}
  </style>
</head>
<body>

<div class="wrap">
  <div class="head">
    <div class="head-icon"><img src="../assets/img/logo/yola-ai-icon.png" alt="Yola AI"></div>
    <div>
      <h1>Cocokin Mood Kamu</h1>
      <div class="sub">2 pertanyaan, Yola carikan menu yang pas ✨</div>
    </div>
    <a href="index.php" class="back-link"><i data-lucide="arrow-left" class="lucide-ic"></i> Kembali</a>
  </div>

  <div class="body">
    <div class="progress-dots">
      <span class="dot" id="dot1"></span>
      <span class="dot" id="dot2"></span>
    </div>

    <!-- Step 1: mood rasa -->
    <div class="step active" id="step1">
      <div class="q-label">Lagi pengen rasa yang gimana nih?</div>
      <div class="opsi-grid">
        <div class="opsi" data-value="manis manja">
          <span class="emoji">🍰</span><span class="label">Manis Manja</span>
        </div>
        <div class="opsi" data-value="segar dan asem">
          <span class="emoji">🍋</span><span class="label">Segar & Asem</span>
        </div>
        <div class="opsi" data-value="hangat dan nyaman">
          <span class="emoji">☕</span><span class="label">Hangat & Nyaman</span>
        </div>
        <div class="opsi" data-value="pahit dan kuat">
          <span class="emoji">🍫</span><span class="label">Pahit & Kuat (coklat/kopi)</span>
        </div>
      </div>
    </div>

    <!-- Step 2: konteks -->
    <div class="step" id="step2">
      <div class="q-label">Lagi buat momen apa?</div>
      <div class="opsi-grid">
        <div class="opsi" data-value="nyantai sendirian">
          <span class="emoji">📖</span><span class="label">Nyantai Sendirian</span>
        </div>
        <div class="opsi" data-value="ngobrol bareng temen/keluarga">
          <span class="emoji">👥</span><span class="label">Bareng Temen/Keluarga</span>
        </div>
        <div class="opsi" data-value="lagi butuh energi/gercep">
          <span class="emoji">⚡</span><span class="label">Butuh Energi Cepat</span>
        </div>
        <div class="opsi" data-value="mau self reward/manjain diri">
          <span class="emoji">🎁</span><span class="label">Self Reward</span>
        </div>
      </div>
    </div>

    <div class="loading-row" id="loadingRow">
      <span class="spinner"></span> Yola lagi nyocokin menu buat kamu...
    </div>

    <div class="error-box" id="errorBox"></div>

    <div class="hasil-wrap" id="hasilWrap">
      <div class="hasil-label"><i data-lucide="sparkles" class="lucide-ic" style="width:14px;height:14px;"></i> Rekomendasi Yola</div>
      <div class="hasil-card" id="hasilCard"></div>
      <button type="button" class="btn-ulang" id="btnUlang"><i data-lucide="rotate-ccw" class="lucide-ic" style="width:14px;height:14px;"></i> Coba Lagi</button>
    </div>
  </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
if (window.lucide) lucide.createIcons();

const step1     = document.getElementById('step1');
const step2     = document.getElementById('step2');
const dot1      = document.getElementById('dot1');
const dot2      = document.getElementById('dot2');
const loadingRow= document.getElementById('loadingRow');
const errorBox  = document.getElementById('errorBox');
const hasilWrap = document.getElementById('hasilWrap');
const hasilCard = document.getElementById('hasilCard');
const btnUlang  = document.getElementById('btnUlang');

let jawabMood    = null;
let jawabKonteks  = null;

step1.querySelectorAll('.opsi').forEach(opsi => {
  opsi.addEventListener('click', () => {
    jawabMood = opsi.dataset.value;
    step1.classList.remove('active');
    step2.classList.add('active');
    dot1.classList.add('done');
  });
});

step2.querySelectorAll('.opsi').forEach(opsi => {
  opsi.addEventListener('click', () => {
    jawabKonteks = opsi.dataset.value;
    opsi.classList.add('selected');
    dot2.classList.add('done');
    ambilRekomendasi();
  });
});

btnUlang.addEventListener('click', () => {
  jawabMood = null;
  jawabKonteks = null;
  hasilWrap.classList.remove('show');
  errorBox.classList.remove('show');
  step2.classList.remove('active');
  step2.querySelectorAll('.opsi').forEach(o => o.classList.remove('selected'));
  dot1.classList.remove('done');
  dot2.classList.remove('done');
  step1.classList.add('active');
});

async function ambilRekomendasi() {
  step2.classList.remove('active');
  loadingRow.classList.add('show');
  errorBox.classList.remove('show');
  hasilWrap.classList.remove('show');

  try {
    const res = await fetch('mood_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ mood: jawabMood, konteks: jawabKonteks })
    });
    const data = await res.json();

    loadingRow.classList.remove('show');

    if (data.rekomendasi) {
      hasilCard.textContent = data.rekomendasi;
      hasilWrap.classList.add('show');
    } else {
      errorBox.textContent = data.error || 'Gagal ambil rekomendasi. Coba lagi ya.';
      errorBox.classList.add('show');
    }
  } catch (err) {
    loadingRow.classList.remove('show');
    errorBox.textContent = 'Gagal terhubung ke server. Coba lagi bentar ya 🙏';
    errorBox.classList.add('show');
  }
}
</script>
</body>
</html>
