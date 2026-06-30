<?php
session_start();
if(isset($_SESSION['username'])){
    header("Location: ../index.php");
    exit();
}

$old_username = isset($_GET['username']) ? htmlspecialchars($_GET['username']) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Buat Akun – YOLAZCAKE Sintang</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

  <style>
    /* ── CSS Variables (match style.css / login.php) ── */
    :root {
      --cream:   #FFF3E0;
      --beige:   #F5E6D3;
      --brown:   #6D4C41;
      --pink:    #E8A0BF;
      --gold:    #D4AF37;
      --white:   #ffffff;
      --dark1:   #2b1a11;
      --dark2:   #1e0e3a;
      --dark3:   #2d1560;
    }

    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

    body {
      min-height: 100vh;
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, var(--dark1) 0%, #4a2c1a 40%, #3a1f0e 70%, var(--dark2) 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      position: relative;
    }

    /* ── Aurora background ── */
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

    /* ── Floating sparkles ── */
    .sparkle {
      position: fixed;
      border-radius: 50%;
      pointer-events: none;
      z-index: 0;
      animation: floatUp linear infinite;
    }

    @keyframes floatUp {
      0%   { transform: translateY(0) rotate(0deg);   opacity: 0; }
      15%  { opacity: 1; }
      85%  { opacity: 0.7; }
      100% { transform: translateY(-100vh) rotate(720deg); opacity: 0; }
    }

    /* ── Register card wrapper ── */
    .login-wrapper {
      position: relative;
      z-index: 10;
      width: 100%;
      max-width: 440px;
      padding: 20px;
      opacity: 0;
      transform: translateY(40px);
      animation: cardReveal 0.9s cubic-bezier(.22,.68,0,1.2) 0.3s forwards;
    }

    @keyframes cardReveal {
      to { opacity: 1; transform: translateY(0); }
    }

    /* ── Card ── */
    .login-card {
      background: rgba(255,255,255,0.06);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 28px;
      padding: 44px 42px 40px;
      position: relative;
      overflow: hidden;
      box-shadow:
        0 30px 80px rgba(0,0,0,0.45),
        0 0 40px rgba(212,175,55,0.12),
        0 0 80px rgba(212,175,55,0.06);
      transition: box-shadow 0.4s ease;
    }

    .login-card:hover {
      box-shadow:
        0 35px 90px rgba(0,0,0,0.5),
        0 0 50px rgba(212,175,55,0.28),
        0 0 100px rgba(212,175,55,0.14);
    }

    /* Gold shimmer top line */
    .login-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 3px;
      background: linear-gradient(90deg, #D4AF37, #ee2a7b, #D4AF37, #FFE4B5, #D4AF37);
      background-size: 300% 100%;
      animation: goldSlide 4s linear infinite;
    }

    /* Subtle inner glow orb */
    .login-card::after {
      content: '';
      position: absolute;
      bottom: -60px; right: -60px;
      width: 200px; height: 200px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(212,175,55,0.14) 0%, transparent 70%);
      pointer-events: none;
    }

    @keyframes goldSlide {
      0%   { background-position: 0% 0; }
      100% { background-position: 300% 0; }
    }

    /* ── Header brand ── */
    .login-brand {
      text-align: center;
      margin-bottom: 32px;
    }

    .login-brand .eyebrow {
      font-size: 0.72em;
      font-weight: 600;
      letter-spacing: 5px;
      text-transform: uppercase;
      color: rgba(212,175,55,0.85);
      display: block;
      margin-bottom: 10px;
      opacity: 0;
      animation: fadeSlideDown 0.8s forwards 0.6s;
    }

    .login-brand h1 {
      font-family: 'Playfair Display', serif;
      font-size: 2.4em;
      font-weight: 700;
      background: linear-gradient(135deg, #fff 30%, #D4AF37 60%, #FFE4B5 80%, #fff);
      background-size: 250% 100%;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      animation: shimmerText 4s ease-in-out infinite, fadeSlideDown 0.9s forwards 0.7s;
      opacity: 0;
      line-height: 1.15;
    }

    .login-brand .subtitle {
      font-size: 0.88em;
      color: rgba(255,255,255,0.55);
      margin-top: 10px;
      display: block;
      opacity: 0;
      animation: fadeSlideDown 0.9s forwards 0.9s;
    }

    /* Gold divider */
    .gold-divider {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin: 14px 0 28px;
      opacity: 0;
      animation: fadeSlideDown 0.9s forwards 1s;
    }
    .gold-divider::before,
    .gold-divider::after {
      content: '';
      display: block;
      width: 55px; height: 1px;
      background: linear-gradient(to right, transparent, #D4AF37);
    }
    .gold-divider::after {
      background: linear-gradient(to left, transparent, #D4AF37);
    }
    .gold-divider .diamond {
      color: #D4AF37;
      font-size: 0.65em;
      letter-spacing: 4px;
    }

    @keyframes shimmerText {
      0%   { background-position: 100% 0; }
      100% { background-position: -100% 0; }
    }
    @keyframes fadeSlideDown {
      from { opacity: 0; transform: translateY(-16px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Form ── */
    .login-form {
      display: flex;
      flex-direction: column;
      gap: 20px;
      opacity: 0;
      animation: fadeUp 0.8s forwards 1.1s;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .field-group {
      position: relative;
    }

    .field-group label {
      display: block;
      font-size: 0.78em;
      font-weight: 600;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: rgba(212,175,55,0.8);
      margin-bottom: 8px;
    }

    .field-group .field-icon {
      position: absolute;
      left: 16px;
      bottom: 13px;
      font-size: 1.1em;
      color: rgba(212,175,55,0.55);
      pointer-events: none;
      transition: color 0.3s;
    }

    .field-group input {
      width: 100%;
      padding: 14px 16px 14px 46px;
      background: rgba(255,255,255,0.07);
      border: 1px solid rgba(255,255,255,0.14);
      border-radius: 14px;
      color: #fff;
      font-family: 'Inter', sans-serif;
      font-size: 0.97em;
      outline: none;
      transition: border-color 0.3s, box-shadow 0.3s, background 0.3s;
    }

    .field-group input::placeholder {
      color: rgba(255,255,255,0.3);
    }

    .field-group input:focus {
      border-color: rgba(212,175,55,0.6);
      background: rgba(255,255,255,0.1);
      box-shadow:
        0 0 0 3px rgba(212,175,55,0.12),
        0 0 20px rgba(212,175,55,0.2),
        0 0 40px rgba(212,175,55,0.08);
    }

    .field-group input:focus + .field-icon,
    .field-group:focus-within .field-icon {
      color: rgba(212,175,55,0.9);
    }

    /* Inline hint under password field (kekuatan / kecocokan password) */
    .field-hint {
      display: block;
      margin-top: 6px;
      font-size: 0.74em;
      color: rgba(255,255,255,0.35);
      transition: color 0.25s;
    }
    .field-hint.ok   { color: #9be8a4; }
    .field-hint.bad  { color: #ff8ab5; }

    /* ── Submit button ── */
    .btn-login {
      margin-top: 8px;
      width: 100%;
      padding: 15px 20px;
      background: linear-gradient(135deg, #D4AF37 0%, #b8922a 50%, #D4AF37 100%);
      background-size: 200% 100%;
      border: none;
      border-radius: 14px;
      color: #1e0e0a;
      font-family: 'Playfair Display', serif;
      font-size: 1.05em;
      font-weight: 700;
      letter-spacing: 1px;
      cursor: pointer;
      position: relative;
      overflow: hidden;
      transition:
        transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1),
        box-shadow 0.35s ease,
        background-position 0.5s ease;
      box-shadow:
        0 6px 20px rgba(212,175,55,0.35),
        0 0 30px rgba(212,175,55,0.2);
    }

    /* Shine sweep on hover */
    .btn-login::before {
      content: '';
      position: absolute;
      top: 0; left: -100%;
      width: 60%; height: 100%;
      background: linear-gradient(120deg, transparent 30%, rgba(255,255,255,0.35) 50%, transparent 70%);
      transition: left 0.5s ease;
      pointer-events: none;
    }

    /* ── ZOOM EFFECT: activated when form is complete ── */
    .btn-login.active {
      animation: zoomPulse 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
    }

    @keyframes zoomPulse {
      0%   { transform: scale(1); }
      40%  { transform: scale(1.08); box-shadow: 0 10px 40px rgba(212,175,55,0.6), 0 0 60px rgba(212,175,55,0.4); }
      70%  { transform: scale(0.97); }
      100% { transform: scale(1.04); box-shadow: 0 8px 30px rgba(212,175,55,0.5), 0 0 50px rgba(212,175,55,0.3); }
    }

    .btn-login:hover {
      transform: translateY(-3px) scale(1.03);
      background-position: 100% 0;
      box-shadow:
        0 12px 35px rgba(212,175,55,0.55),
        0 0 50px rgba(212,175,55,0.35),
        0 0 80px rgba(212,175,55,0.15);
    }

    .btn-login:hover::before {
      left: 140%;
    }

    .btn-login:active {
      transform: scale(0.97);
    }

    .btn-login .btn-text { position: relative; z-index: 1; }
    .btn-login .btn-icon { position: relative; z-index: 1; margin-left: 8px; }

    /* ── Error message ── */
    .error-msg {
      display: none;
      background: rgba(238,42,123,0.15);
      border: 1px solid rgba(238,42,123,0.35);
      border-radius: 12px;
      padding: 12px 16px;
      font-size: 0.85em;
      color: #ff8ab5;
      text-align: center;
      animation: fadeUp 0.4s ease;
    }
    .error-msg.show { display: block; }

    /* ── Footer link ── */
    .login-footer {
      margin-top: 24px;
      text-align: center;
      font-size: 0.82em;
      color: rgba(255,255,255,0.4);
      opacity: 0;
      animation: fadeUp 0.8s forwards 1.4s;
    }

    .login-footer a {
      color: #D4AF37;
      text-decoration: none;
      font-weight: 500;
      transition: color 0.2s;
    }
    .login-footer a:hover { color: #FFE4B5; }

    /* ── Back link ── */
    .back-link {
      position: fixed;
      top: 24px; left: 28px;
      display: flex;
      align-items: center;
      gap: 8px;
      color: rgba(212,175,55,0.75);
      text-decoration: none;
      font-size: 0.85em;
      font-weight: 500;
      letter-spacing: 1px;
      z-index: 20;
      transition: color 0.3s, transform 0.3s;
      opacity: 0;
      animation: fadeUp 0.8s forwards 1.5s;
    }
    .back-link:hover {
      color: #D4AF37;
      transform: translateX(-4px);
    }
  </style>
</head>
<body>

  <!-- Back link -->
  <a href="login.php" class="back-link">&#8592; Kembali ke Login</a>

  <!-- Floating sparkles (generated by JS) -->
  <div id="sparkles"></div>

  <!-- Register card -->
  <div class="login-wrapper">
    <div class="login-card">

      <div class="login-brand">
        <span class="eyebrow">✨ Bergabung Dengan Kami</span>
        <h1>YOLAZCAKE</h1>
        <span class="subtitle">Buat akun baru untuk mengakses sistem</span>
        <div class="gold-divider"><span class="diamond">✦ ✦ ✦</span></div>
      </div>

      <!-- Error feedback -->
      <div class="error-msg" id="errorMsg">
        ⚠ <span id="errorText">Terjadi kesalahan. Coba lagi.</span>
      </div>

      <form class="login-form" action="proses_register.php" method="POST" id="registerForm" novalidate>

        <div class="field-group">
          <label for="username">Username</label>
          <input
            type="text"
            id="username"
            name="username"
            placeholder="Pilih username"
            autocomplete="username"
            value="<?= $old_username ?>"
            required
          >
          <span class="field-icon">👤</span>
        </div>

        <div class="field-group">
          <label for="password">Password</label>
          <input
            type="password"
            id="password"
            name="password"
            placeholder="Buat password (min. 6 karakter)"
            autocomplete="new-password"
            required
          >
          <span class="field-icon">🔒</span>
        </div>

        <div class="field-group">
          <label for="confirm_password">Konfirmasi Password</label>
          <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            placeholder="Ulangi password"
            autocomplete="new-password"
            required
          >
          <span class="field-icon">🔐</span>
          <span class="field-hint" id="matchHint"></span>
        </div>

        <button type="submit" class="btn-login" id="btnRegister">
          <span class="btn-text">Daftar</span>
          <span class="btn-icon">→</span>
        </button>

      </form>

      <div class="login-footer">
        Sudah punya akun? <a href="login.php">Masuk</a>
      </div>

    </div>
  </div>

  <script>
    /* ── Sparkles ── */
    (function() {
      const container = document.getElementById('sparkles');
      const colors = ['rgba(212,175,55,0.7)', 'rgba(232,160,191,0.6)', 'rgba(255,255,255,0.5)', 'rgba(212,175,55,0.4)'];
      for (let i = 0; i < 26; i++) {
        const s = document.createElement('div');
        s.classList.add('sparkle');
        const size = Math.random() * 5 + 2;
        s.style.cssText = `
          width: ${size}px;
          height: ${size}px;
          left: ${Math.random() * 100}vw;
          top: ${Math.random() * 100}vh;
          background: ${colors[Math.floor(Math.random() * colors.length)]};
          animation-duration: ${Math.random() * 8 + 6}s;
          animation-delay: ${Math.random() * 6}s;
          box-shadow: 0 0 ${size * 2}px ${colors[0]};
        `;
        container.appendChild(s);
      }
    })();

    /* ── Zoom on button + live password-match hint ── */
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const confirmInput  = document.getElementById('confirm_password');
    const btnRegister   = document.getElementById('btnRegister');
    const matchHint     = document.getElementById('matchHint');

    function checkFields() {
      const filled = usernameInput.value.trim() !== '' &&
                     passwordInput.value.trim() !== '' &&
                     confirmInput.value.trim() !== '';
      if (filled) {
        btnRegister.classList.add('active');
      } else {
        btnRegister.classList.remove('active');
      }

      if (confirmInput.value === '') {
        matchHint.textContent = '';
        matchHint.classList.remove('ok', 'bad');
      } else if (passwordInput.value === confirmInput.value) {
        matchHint.textContent = '✓ Password cocok';
        matchHint.classList.add('ok');
        matchHint.classList.remove('bad');
      } else {
        matchHint.textContent = '✕ Password belum sama';
        matchHint.classList.add('bad');
        matchHint.classList.remove('ok');
      }
    }

    usernameInput.addEventListener('input', checkFields);
    passwordInput.addEventListener('input', checkFields);
    confirmInput.addEventListener('input', checkFields);

    function showError(msg) {
      document.getElementById('errorText').textContent = msg;
      document.getElementById('errorMsg').classList.add('show');
    }

    /* ── Validasi + zoom effect saat submit ── */
    document.getElementById('registerForm').addEventListener('submit', function(e) {
      const u = usernameInput.value.trim();
      const p = passwordInput.value;
      const c = confirmInput.value;

      if (!u || !p || !c) {
        e.preventDefault();
        showError('Semua kolom wajib diisi.');
        return;
      }
      if (p.length < 6) {
        e.preventDefault();
        showError('Password minimal 6 karakter.');
        return;
      }
      if (p !== c) {
        e.preventDefault();
        showError('Konfirmasi password tidak sama.');
        return;
      }

      // Zoom effect sebelum submit
      btnRegister.classList.remove('active');
      void btnRegister.offsetWidth; // reflow
      btnRegister.classList.add('active');
    });

    /* ── PHP error flag ── */
    <?php if(isset($_GET['error'])): ?>
    showError(<?= json_encode(match($_GET['error']) {
        'empty'   => 'Semua kolom wajib diisi.',
        'short'   => 'Password minimal 6 karakter.',
        'mismatch'=> 'Konfirmasi password tidak sama.',
        'taken'   => 'Username sudah dipakai, coba yang lain.',
        default   => 'Terjadi kesalahan. Coba lagi.',
    }) ?>);
    <?php endif; ?>
  </script>
</body>
</html>
