<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="../assets/css/lucide-icons.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hubungi Admin – YOLAZCAKE Sintang</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

  <style>
    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

    body {
      min-height: 100vh;
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #2b1a11 0%, #4a2c1a 40%, #3a1f0e 70%, #1e0e3a 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      overflow-x: hidden;
      position: relative;
      padding: 40px 16px;
    }

    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background:
        radial-gradient(ellipse at 20% 40%, rgba(212,175,55,0.18) 0%, transparent 55%),
        radial-gradient(ellipse at 80% 60%, rgba(232,160,191,0.15) 0%, transparent 55%),
        radial-gradient(ellipse at 55% 10%, rgba(45,21,96,0.5) 0%, transparent 60%);
      animation: auroraShift 10s ease-in-out infinite alternate;
      pointer-events: none;
      z-index: 0;
    }
    @keyframes auroraShift {
      0%   { opacity: 0.7; transform: scale(1) translateX(0); }
      100% { opacity: 1;   transform: scale(1.06) translateX(15px); }
    }

    .sparkle { position: fixed; border-radius: 50%; pointer-events: none; z-index: 0; animation: floatUp linear infinite; }
    @keyframes floatUp {
      0%   { transform: translateY(0) rotate(0deg); opacity: 0; }
      15%  { opacity: 1; }
      85%  { opacity: 0.7; }
      100% { transform: translateY(-100vh) rotate(720deg); opacity: 0; }
    }

    .back-link {
      position: fixed;
      top: 24px; left: 28px;
      display: flex; align-items: center; gap: 8px;
      color: rgba(212,175,55,0.75);
      text-decoration: none;
      font-size: 0.85em; font-weight: 500; letter-spacing: 1px;
      z-index: 20;
      transition: color 0.3s, transform 0.3s;
      opacity: 0; animation: fadeUp 0.8s forwards 1.4s;
    }
    .back-link:hover { color: #D4AF37; transform: translateX(-4px); }

    @keyframes fadeUp { from{opacity:0;transform:translateY(20px);} to{opacity:1;transform:translateY(0);} }
    @keyframes fadeSlideDown { from{opacity:0;transform:translateY(-16px);} to{opacity:1;transform:translateY(0);} }
    @keyframes shimmerText { 0%{background-position:100% 0;} 100%{background-position:-100% 0;} }
    @keyframes goldSlide { 0%{background-position:0% 0;} 100%{background-position:300% 0;} }
    @keyframes cardReveal { to{opacity:1;transform:translateY(0);} }

    .wrap {
      position: relative; z-index: 10;
      width: 100%; max-width: 500px;
      opacity: 0; transform: translateY(40px);
      animation: cardReveal 0.9s cubic-bezier(.22,.68,0,1.2) 0.25s forwards;
    }

    .card {
      background: rgba(255,255,255,0.06);
      backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 28px;
      padding: 40px 38px 36px;
      position: relative; overflow: hidden;
      box-shadow: 0 30px 80px rgba(0,0,0,0.45), 0 0 40px rgba(212,175,55,0.12);
    }
    .card::before {
      content: ''; position: absolute; top:0; left:0; right:0; height:3px;
      background: linear-gradient(90deg, #D4AF37, #ee2a7b, #D4AF37, #FFE4B5, #D4AF37);
      background-size: 300% 100%;
      animation: goldSlide 4s linear infinite;
    }

    .brand { text-align: center; margin-bottom: 26px; }
    .brand .eyebrow {
      font-size: 0.72em; font-weight: 600; letter-spacing: 5px; text-transform: uppercase;
      color: rgba(212,175,55,0.85); display: block; margin-bottom: 10px;
      opacity: 0; animation: fadeSlideDown 0.8s forwards 0.5s;
    }
    .brand h1 {
      font-family: 'Playfair Display', serif; font-size: 1.9em; font-weight: 700;
      background: linear-gradient(135deg, #fff 30%, #D4AF37 60%, #FFE4B5 80%, #fff);
      background-size: 250% 100%;
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
      animation: shimmerText 4s ease-in-out infinite, fadeSlideDown 0.9s forwards 0.6s;
      opacity: 0; line-height: 1.2;
    }
    .brand .subtitle {
      font-size: 0.85em; color: rgba(255,255,255,0.55); margin-top: 10px; display: block;
      line-height: 1.55;
      opacity: 0; animation: fadeSlideDown 0.9s forwards 0.8s;
    }

    .info-box {
      display: flex; gap: 10px; align-items: flex-start;
      background: rgba(212,175,55,0.08); border: 1px solid rgba(212,175,55,0.25);
      border-radius: 14px; padding: 13px 16px; margin-bottom: 22px;
      font-size: 0.8em; color: rgba(255,255,255,0.65); line-height: 1.6;
      opacity: 0; animation: fadeSlideDown 0.9s forwards 0.95s;
    }
    .info-box .ii { font-size: 1.2em; flex-shrink: 0; }

    .frm { display: flex; flex-direction: column; gap: 18px; }
    .field-group { position: relative; opacity: 0; animation: fadeUp 0.6s forwards; }
    .field-group label {
      display: block; font-size: 0.76em; font-weight: 600; letter-spacing: 1.5px;
      text-transform: uppercase; color: rgba(212,175,55,0.8); margin-bottom: 8px;
    }
    .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

    .field-group input, .field-group select, .field-group textarea {
      width: 100%; padding: 13px 16px;
      background: rgba(255,255,255,0.07);
      border: 1px solid rgba(255,255,255,0.14);
      border-radius: 14px; color: #fff;
      font-family: 'Inter', sans-serif; font-size: 0.93em;
      outline: none;
      transition: border-color 0.3s, box-shadow 0.3s, background 0.3s;
    }
    .field-group select { cursor: pointer; }
    .field-group select option { background: #2d1560; color: #fff; }
    .field-group textarea { resize: vertical; min-height: 100px; line-height: 1.5; }
    .field-group input::placeholder, .field-group textarea::placeholder { color: rgba(255,255,255,0.3); }
    .field-group input:focus, .field-group select:focus, .field-group textarea:focus {
      border-color: rgba(212,175,55,0.6);
      background: rgba(255,255,255,0.1);
      box-shadow: 0 0 0 3px rgba(212,175,55,0.12), 0 0 20px rgba(212,175,55,0.15);
    }
    .field-note { font-size: 0.72em; color: rgba(255,255,255,0.35); margin-top: 6px; }

    .btn-send {
      margin-top: 4px; width: 100%; padding: 15px 20px;
      background: linear-gradient(135deg, #D4AF37 0%, #b8922a 50%, #D4AF37 100%);
      background-size: 200% 100%;
      border: none; border-radius: 14px; color: #1e0e0a;
      font-family: 'Playfair Display', serif; font-size: 1.02em; font-weight: 700; letter-spacing: 1px;
      cursor: pointer; position: relative; overflow: hidden;
      transition: transform 0.35s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.35s ease, opacity 0.3s ease;
      box-shadow: 0 6px 20px rgba(212,175,55,0.35), 0 0 30px rgba(212,175,55,0.2);
      opacity: 0; animation: fadeUp 0.6s forwards 0.5s;
    }
    .btn-send:hover { transform: translateY(-3px) scale(1.02); box-shadow: 0 12px 35px rgba(212,175,55,0.55), 0 0 50px rgba(212,175,55,0.35); }
    .btn-send:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    .error-msg {
      display: none; background: rgba(238,42,123,0.15); border: 1px solid rgba(238,42,123,0.35);
      border-radius: 12px; padding: 12px 16px; font-size: 0.85em; color: #ff8ab5;
      text-align: center; animation: fadeUp 0.4s ease;
    }
    .error-msg.show { display: block; }

    .footer-link {
      margin-top: 24px; text-align: center; font-size: 0.82em; color: rgba(255,255,255,0.4);
      opacity: 0; animation: fadeUp 0.8s forwards 1.2s;
    }
    .footer-link a { color: #D4AF37; text-decoration: none; font-weight: 500; transition: color 0.2s; }
    .footer-link a:hover { color: #FFE4B5; }

    /* ══════════════════ Status overlay (proses <i data-lucide="arrow-right" class="lucide-ic"></i> hasil) ══════════════════ */
    .status-overlay {
      position: fixed; inset: 0; z-index: 100;
      background: rgba(20,10,5,0.72);
      backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
      display: flex; align-items: center; justify-content: center;
      opacity: 0; pointer-events: none; transition: opacity 0.3s ease;
    }
    .status-overlay.show { opacity: 1; pointer-events: all; }
    .status-box {
      background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15);
      backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
      border-radius: 24px; padding: 42px 48px;
      display: flex; flex-direction: column; align-items: center; gap: 16px;
      min-width: 260px; max-width: 90vw;
      transform: scale(0.85);
      transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
      box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    }
    .status-overlay.show .status-box { transform: scale(1); }

    .spinner { width: 54px; height: 54px; border-radius: 50%; border: 4px solid rgba(212,175,55,0.2); border-top-color: #D4AF37; animation: spin 0.8s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }

    .result-icon { width: 54px; height: 54px; border-radius: 50%; display: none; align-items: center; justify-content: center; position: relative; }
    .result-icon.success { display: flex; background: rgba(155,232,164,0.15); border: 2px solid #9be8a4; animation: popIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); }
    .result-icon.success svg { width: 26px; height: 26px; }
    .result-icon.success path { stroke: #9be8a4; stroke-width: 3; fill: none; stroke-linecap: round; stroke-linejoin: round; stroke-dasharray: 30; stroke-dashoffset: 30; animation: drawCheck 0.4s ease forwards 0.15s; }
    @keyframes drawCheck { to { stroke-dashoffset: 0; } }
    .result-icon.fail { display: flex; background: rgba(238,42,123,0.15); border: 2px solid #ff8ab5; animation: popIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), shake 0.5s ease 0.1s; }
    .result-icon.fail::before, .result-icon.fail::after { content: ''; position: absolute; width: 22px; height: 3px; background: #ff8ab5; border-radius: 2px; }
    .result-icon.fail::before { transform: rotate(45deg); }
    .result-icon.fail::after  { transform: rotate(-45deg); }
    @keyframes popIn { 0%{transform:scale(0.5);opacity:0;} 100%{transform:scale(1);opacity:1;} }
    @keyframes shake { 0%,100%{transform:translateX(0);} 25%{transform:translateX(-6px);} 75%{transform:translateX(6px);} }

    .status-text { font-family: 'Playfair Display', serif; font-size: 1.1em; font-weight: 600; color: #fff; text-align: center; }
    .status-sub { font-size: 0.82em; color: rgba(255,255,255,0.55); text-align: center; margin-top: -8px; }
    .btn-back-login {
      display: none; margin-top: 4px; padding: 10px 22px; border-radius: 999px;
      background: linear-gradient(135deg, #D4AF37, #b8860b); color: #1e0e3a;
      font-size: 0.8em; font-weight: 700; text-decoration: none;
    }
    .btn-back-login.show { display: inline-block; }

    @media(max-width:480px){ .field-row { grid-template-columns: 1fr; } .card { padding: 32px 24px 30px; } }
  </style>
</head>
<body>

  <a href="login.php" class="back-link">&#8592; Kembali ke Login</a>

  <div id="sparkles"></div>

  <div class="wrap">
    <div class="card">

      <div class="brand">
        <span class="eyebrow"><i data-lucide="sparkle" class="lucide-ic"></i> YOLAZCAKE Sintang <i data-lucide="sparkle" class="lucide-ic"></i></span>
        <h1>Hubungi Admin</h1>
        <span class="subtitle">Lupa password, email tidak bisa diakses, atau kendala akun lainnya?<br>Kirim pesan, admin akan membantu Anda secara manual.</span>
      </div>

      <div class="info-box">
        <span class="ii"><i data-lucide="lightbulb" class="lucide-ic"></i></span>
        <span>Cara ini cocok kalau Anda <strong style="color:#FFE4B5;">tidak bisa pakai fitur "Lupa Password"</strong> (misalnya email sudah tidak aktif). Admin akan menghubungi Anda balik melalui email/WhatsApp yang Anda isi di bawah.</span>
      </div>

      <div class="error-msg" id="errorMsg"></div>

      <form class="frm" id="formHubungiAdmin" novalidate>

        <div class="field-group" style="animation-delay:.15s">
          <label for="nama">Nama Lengkap</label>
          <input type="text" id="nama" name="nama" placeholder="Nama Anda" autocomplete="name" required>
        </div>

        <div class="field-group" style="animation-delay:.2s">
          <label for="username_terkait">Username Akun (jika ada)</label>
          <input type="text" id="username_terkait" name="username_terkait" placeholder="Username akun YOLAZCAKE Anda" autocomplete="username">
          <div class="field-note">Membantu admin menemukan akun Anda lebih cepat.</div>
        </div>

        <div class="field-row">
          <div class="field-group" style="animation-delay:.25s">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="email@gmail.com" autocomplete="email">
          </div>
          <div class="field-group" style="animation-delay:.25s">
            <label for="no_hp">No. WhatsApp</label>
            <input type="tel" id="no_hp" name="no_hp" placeholder="08xxxxxxxxxx" autocomplete="tel">
          </div>
        </div>

        <div class="field-group" style="animation-delay:.3s">
          <label for="masalah">Kategori Masalah</label>
          <select id="masalah" name="masalah" required>
            <option value="" disabled selected>Pilih kategori masalah</option>
            <option value="Lupa Password"><i data-lucide="key" class="lucide-ic"></i> Lupa Password</option>
            <option value="Tidak Bisa Login"><i data-lucide="ban" class="lucide-ic"></i> Tidak Bisa Login</option>
            <option value="Email/Username Salah"><i data-lucide="mail" class="lucide-ic"></i> Email / Username Salah</option>
            <option value="Lainnya"><i data-lucide="help-circle" class="lucide-ic"></i> Lainnya</option>
          </select>
        </div>

        <div class="field-group" style="animation-delay:.35s">
          <label for="pesan">Jelaskan Kendala Anda</label>
          <textarea id="pesan" name="pesan" placeholder="Contoh: Saya lupa password akun dengan username 'budi123', mohon dibantu reset." required></textarea>
        </div>

        <button type="submit" class="btn-send" id="btnSend"><i data-lucide="mail" class="lucide-ic"></i> Kirim ke Admin</button>
      </form>

      <div class="footer-link">
        Ingat password Anda? <a href="login.php">Kembali ke Login</a>
      </div>

    </div>
  </div>

  <!-- Status overlay: proses -> hasil -->
  <div class="status-overlay" id="statusOverlay">
    <div class="status-box">
      <div class="spinner" id="statusSpinner"></div>
      <div class="result-icon" id="statusResultIcon"></div>
      <div class="status-text" id="statusText">Memproses...</div>
      <div class="status-sub" id="statusSub"></div>
      <a href="login.php" class="btn-back-login" id="btnBackLogin">Kembali ke Login</a>
    </div>
  </div>

  <script>
    /* Floating sparkles */
    (function(){
      const wrap = document.getElementById('sparkles');
      const colors = ['#D4AF37','#FFE4B5','#E8A0BF','#fff','#f9ce34'];
      for(let i = 0; i < 24; i++){
        const d = document.createElement('div');
        d.className = 'sparkle';
        const s = Math.random()*4+2;
        d.style.cssText = `width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;bottom:${Math.random()*20}%;animation-duration:${6+Math.random()*8}s;animation-delay:${Math.random()*8}s;`;
        wrap.appendChild(d);
      }
    })();

    const form        = document.getElementById('formHubungiAdmin');
    const errorMsg     = document.getElementById('errorMsg');
    const btnSend       = document.getElementById('btnSend');
    const overlay      = document.getElementById('statusOverlay');
    const spinnerEl     = document.getElementById('statusSpinner');
    const resultIconEl  = document.getElementById('statusResultIcon');
    const statusTextEl  = document.getElementById('statusText');
    const statusSubEl   = document.getElementById('statusSub');
    const btnBackLogin  = document.getElementById('btnBackLogin');

    function showError(msg){ errorMsg.textContent = msg; errorMsg.classList.add('show'); }
    function hideError(){ errorMsg.classList.remove('show'); }

    function showProcessing(text, sub){
      spinnerEl.style.display = 'block';
      resultIconEl.className = 'result-icon';
      resultIconEl.innerHTML = '';
      btnBackLogin.classList.remove('show');
      statusTextEl.textContent = text || 'Memproses...';
      statusSubEl.textContent = sub || 'Mohon tunggu sebentar';
      overlay.classList.add('show');
    }
    function showResult(success, text, sub, showBack){
      spinnerEl.style.display = 'none';
      resultIconEl.className = 'result-icon ' + (success ? 'success' : 'fail');
      resultIconEl.innerHTML = success
        ? '<svg viewBox="0 0 24 24"><path d="M4 12l5 5L20 7"/></svg>'
        : '';
      statusTextEl.textContent = text;
      statusSubEl.textContent = sub || '';
      if (showBack) btnBackLogin.classList.add('show');
    }
    function hideOverlay(){ overlay.classList.remove('show'); }

    form.addEventListener('submit', async function(e){
      e.preventDefault();
      hideError();

      const data = {
        nama: document.getElementById('nama').value.trim(),
        username_terkait: document.getElementById('username_terkait').value.trim(),
        email: document.getElementById('email').value.trim(),
        no_hp: document.getElementById('no_hp').value.trim(),
        masalah: document.getElementById('masalah').value,
        pesan: document.getElementById('pesan').value.trim(),
      };

      if (!data.nama) { showError('Nama wajib diisi.'); return; }
      if (!data.email && !data.no_hp) { showError('Isi minimal salah satu: email atau nomor WhatsApp.'); return; }
      if (!data.masalah) { showError('Pilih kategori masalah terlebih dahulu.'); return; }
      if (!data.pesan) { showError('Jelaskan kendala akun Anda.'); return; }

      btnSend.disabled = true;
      showProcessing('Mengirim Pesan...', 'Sedang menghubungkan Anda ke admin');

      try {
        const res = await fetch('proses_hubungi_admin.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
          body: new URLSearchParams(data)
        });
        const json = await res.json();

        if (json.success) {
          showResult(true, 'Pesan Terkirim!', json.message || 'Admin akan segera menghubungi Anda.', true);
          form.reset();
        } else {
          showResult(false, 'Gagal Mengirim', json.message || 'Terjadi kesalahan, coba lagi.');
          setTimeout(() => { hideOverlay(); btnSend.disabled = false; showError(json.message || 'Terjadi kesalahan.'); }, 1600);
        }
      } catch (err) {
        showResult(false, 'Terjadi Kesalahan', 'Gagal terhubung ke server. Coba lagi.');
        setTimeout(() => { hideOverlay(); btnSend.disabled = false; }, 1600);
      }
    });
  </script>


<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide){lucide.createIcons();}</script>
</body>
</html>
