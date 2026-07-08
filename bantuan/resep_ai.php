<?php
session_start();
$sudahLogin = isset($_SESSION['username']);
$namaUser   = $sudahLogin ? $_SESSION['username'] : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="../assets/css/lucide-icons.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Resep Kreasi AI – YOLAZCAKE</title>
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
    body{
      min-height:100vh;font-family:'Inter',sans-serif;
      background:linear-gradient(140deg,var(--bg1) 0%,var(--bg2) 50%,var(--bg3) 100%);
      color:var(--text);display:flex;
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
      margin:auto;
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
    .intro{font-size:.85em;color:var(--muted);line-height:1.6;margin-bottom:18px;}
    .intro b{color:var(--gold-l);}

    label{display:block;font-size:.78em;color:var(--gold-l);font-weight:600;margin-bottom:8px;}
    textarea{
      width:100%;min-height:90px;resize:vertical;
      background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.12);
      border-radius:14px;padding:14px 16px;color:#fff;font-family:'Inter',sans-serif;
      font-size:.9em;outline:none;transition:border-color .2s;line-height:1.5;
    }
    textarea:focus{border-color:rgba(212,175,55,.5);}
    textarea::placeholder{color:rgba(255,255,255,.32);}

    .chips-contoh{display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;}
    .chip-contoh{
      font-size:.72em;padding:6px 12px;border-radius:99px;cursor:pointer;
      background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.28);color:var(--gold-l);
      transition:.2s;
    }
    .chip-contoh:hover{background:rgba(212,175,55,.2);}

    .btn-generate{
      margin-top:18px;width:100%;padding:14px;border-radius:14px;border:none;cursor:pointer;
      background:linear-gradient(135deg,var(--gold),var(--rose));color:#1a0533;
      font-family:'Inter',sans-serif;font-weight:700;font-size:.9em;
      display:flex;align-items:center;justify-content:center;gap:8px;
      transition:transform .2s;
    }
    .btn-generate:hover{transform:translateY(-1px) scale(1.01);}
    .btn-generate:disabled{opacity:.55;cursor:not-allowed;transform:none;}

    .hasil-wrap{margin-top:22px;display:none;}
    .hasil-wrap.show{display:block;animation:cardIn .5s forwards;}
    .hasil-card{
      background:rgba(255,255,255,.05);border:1px solid rgba(212,175,55,.25);
      border-radius:16px;padding:18px 20px;font-size:.88em;line-height:1.7;
      white-space:pre-wrap;color:#EDE9FF;
    }
    .hasil-label{
      font-size:.7em;letter-spacing:.6px;text-transform:uppercase;color:var(--gold-l);
      margin-bottom:8px;display:flex;align-items:center;gap:6px;
    }

    .loading-row{display:none;align-items:center;gap:10px;margin-top:18px;color:var(--muted);font-size:.82em;}
    .loading-row.show{display:flex;}
    .spinner{
      width:16px;height:16px;border-radius:50%;flex-shrink:0;
      border:2.5px solid rgba(255,255,255,.18);border-top-color:var(--gold-l);
      animation:spin .7s linear infinite;
    }
    @keyframes spin{to{transform:rotate(360deg);}}

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
      <h1>Resep Kreasi Yola AI</h1>
      <div class="sub">Bahan sisa di rumah? Yola kasih ide kreasinya ✨</div>
    </div>
    <a href="index.php" class="back-link"><i data-lucide="arrow-left" class="lucide-ic"></i> Kembali</a>
  </div>

  <div class="body">
    <div class="intro">
      Kasih tau Yola <b>bahan-bahan yang kamu punya</b> di rumah (boleh sisa, boleh seadanya),
      nanti Yola kasih 1-2 ide kreasi kue/minuman ala cafe yang bisa kamu coba bikin sendiri.
      Santai aja nulisnya, gak perlu formal~
    </div>

    <form id="formResep">
      <label for="bahanInput">Bahan yang kamu punya</label>
      <textarea id="bahanInput" placeholder="Contoh: 2 butir telur, sisa susu UHT setengah kotak, tepung terigu, sedikit coklat bubuk..."></textarea>

      <div class="chips-contoh">
        <div class="chip-contoh" data-isi="Telur 2 butir, tepung terigu, gula, mentega sisa sedikit">🥚 Contoh 1</div>
        <div class="chip-contoh" data-isi="Roti tawar sisa, susu UHT, pisang mateng, coklat bubuk">🍞 Contoh 2</div>
        <div class="chip-contoh" data-isi="Sisa whipped cream, buah-buahan di kulkas, biskuit">🍓 Contoh 3</div>
      </div>

      <button type="submit" class="btn-generate" id="btnGenerate">
        <i data-lucide="sparkles" class="lucide-ic"></i> Bikinin Ide Resep
      </button>
    </form>

    <div class="loading-row" id="loadingRow">
      <span class="spinner"></span> Yola lagi mikirin ide kreasinya...
    </div>

    <div class="error-box" id="errorBox"></div>

    <div class="hasil-wrap" id="hasilWrap">
      <div class="hasil-label"><i data-lucide="chef-hat" class="lucide-ic" style="width:14px;height:14px;"></i> Ide dari Yola</div>
      <div class="hasil-card" id="hasilCard"></div>
    </div>
  </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
if (window.lucide) lucide.createIcons();

const formResep   = document.getElementById('formResep');
const bahanInput  = document.getElementById('bahanInput');
const btnGenerate = document.getElementById('btnGenerate');
const loadingRow  = document.getElementById('loadingRow');
const errorBox    = document.getElementById('errorBox');
const hasilWrap   = document.getElementById('hasilWrap');
const hasilCard   = document.getElementById('hasilCard');

document.querySelectorAll('.chip-contoh').forEach(chip => {
  chip.addEventListener('click', () => {
    bahanInput.value = chip.dataset.isi;
    bahanInput.focus();
  });
});

formResep.addEventListener('submit', async (e) => {
  e.preventDefault();
  const bahan = bahanInput.value.trim();
  if (!bahan) { bahanInput.focus(); return; }

  btnGenerate.disabled = true;
  loadingRow.classList.add('show');
  errorBox.classList.remove('show');
  hasilWrap.classList.remove('show');

  try {
    const res = await fetch('resep_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ bahan })
    });
    const data = await res.json();

    if (data.resep) {
      hasilCard.textContent = data.resep;
      hasilWrap.classList.add('show');
    } else {
      errorBox.textContent = data.error || 'Gagal membuat resep. Coba lagi ya.';
      errorBox.classList.add('show');
    }
  } catch (err) {
    errorBox.textContent = 'Gagal terhubung ke server. Coba lagi bentar ya 🙏';
    errorBox.classList.add('show');
  }

  btnGenerate.disabled = false;
  loadingRow.classList.remove('show');
});
</script>
</body>
</html>
