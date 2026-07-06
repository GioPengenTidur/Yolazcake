<?php
session_start();
if(isset($_SESSION['username'])){
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lupa Password – YOLAZCAKE Sintang</title>
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

    .login-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 3px;
      background: linear-gradient(90deg, #D4AF37, #ee2a7b, #D4AF37, #FFE4B5, #D4AF37);
      background-size: 300% 100%;
      animation: goldSlide 4s linear infinite;
    }

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

    .login-brand {
      text-align: center;
      margin-bottom: 26px;
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
      font-size: 2.1em;
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
      font-size: 0.86em;
      color: rgba(255,255,255,0.55);
      margin-top: 10px;
      display: block;
      opacity: 0;
      animation: fadeSlideDown 0.9s forwards 0.9s;
    }

    @keyframes shimmerText {
      0%   { background-position: 100% 0; }
      100% { background-position: -100% 0; }
    }
    @keyframes fadeSlideDown {
      from { opacity: 0; transform: translateY(-16px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Step indicator ── */
    .step-indicator {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin-bottom: 26px;
      opacity: 0;
      animation: fadeSlideDown 0.9s forwards 1s;
    }
    .step-dot {
      width: 30px; height: 30px;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 0.78em; font-weight: 700;
      color: rgba(255,255,255,0.4);
      border: 1.5px solid rgba(255,255,255,0.18);
      background: rgba(255,255,255,0.04);
      transition: all 0.35s cubic-bezier(0.34,1.56,0.64,1);
    }
    .step-dot.active {
      color: #1e0e0a;
      background: linear-gradient(135deg, #D4AF37, #FFE4B5);
      border-color: #D4AF37;
      box-shadow: 0 0 16px rgba(212,175,55,0.5);
      transform: scale(1.1);
    }
    .step-dot.done {
      color: #9be8a4;
      background: rgba(155,232,164,0.12);
      border-color: #9be8a4;
    }
    .step-line {
      width: 26px; height: 2px;
      background: rgba(255,255,255,0.15);
      border-radius: 2px;
      transition: background 0.35s;
    }
    .step-line.done { background: #9be8a4; }

    /* ── Form (reuse login-form styling) ── */
    .login-form {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .step-panel { display: none; flex-direction: column; gap: 20px; animation: fadeUp 0.5s ease; }
    .step-panel.active { display: flex; }

    .step-desc {
      font-size: 0.85em;
      color: rgba(255,255,255,0.55);
      line-height: 1.5;
      text-align: center;
      margin-bottom: -4px;
    }
    .step-desc strong { color: #FFE4B5; font-weight: 600; }

    .field-group { position: relative; }

    .field-group label {
      display: block;
      font-size: 0.78em;
      font-weight: 600;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: rgba(212,175,55,0.8);
      margin-bottom: 8px;
    }

    .input-wrap { position: relative; }

    .input-wrap .field-icon {
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
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

    .field-group input[type="password"] { padding-right: 46px; }

    .field-group input::placeholder { color: rgba(255,255,255,0.3); }

    .field-group input:focus {
      border-color: rgba(212,175,55,0.6);
      background: rgba(255,255,255,0.1);
      box-shadow:
        0 0 0 3px rgba(212,175,55,0.12),
        0 0 20px rgba(212,175,55,0.2),
        0 0 40px rgba(212,175,55,0.08);
    }

    .toggle-eye {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      width: 32px;
      height: 32px;
      background: none;
      border: none;
      cursor: pointer;
      font-size: 1.05em;
      opacity: 0.5;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
      transition: opacity 0.25s, background 0.25s, transform 0.15s;
    }
    .toggle-eye:hover { opacity: 0.85; background: rgba(255,255,255,0.06); }
    .toggle-eye:active { transform: translateY(-50%) scale(0.9); }
    .toggle-eye.active { opacity: 1; color: #D4AF37; }

    .field-hint {
      display: none;
      align-items: center;
      gap: 8px;
      margin-top: 8px;
      font-size: 0.78em;
      color: rgba(255,255,255,0.35);
      transition: color 0.25s;
    }
    .field-hint.ok, .field-hint.bad { display: flex; }
    .field-hint.ok { color: #9be8a4; }
    .field-hint.bad { color: #ff8ab5; }
    .chk-icon {
      width: 16px; height: 16px;
      border-radius: 50%;
      border: 1.5px solid rgba(255,255,255,0.3);
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
      position: relative;
      transition: border-color 0.3s, background 0.3s;
    }
    .chk-icon::after {
      content: '';
      width: 7px; height: 4px;
      border-left: 2px solid transparent;
      border-bottom: 2px solid transparent;
      transform: rotate(-45deg) translateY(-1px);
      transition: border-color 0.2s;
    }
    .field-hint.ok .chk-icon {
      border-color: #9be8a4;
      background: rgba(155,232,164,0.15);
      animation: checkPop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .field-hint.ok .chk-icon::after { border-color: #9be8a4; }
    .field-hint.bad .chk-icon { border-color: #ff8ab5; background: rgba(255,138,181,0.12); }
    @keyframes checkPop {
      0%   { transform: scale(0.6); }
      60%  { transform: scale(1.15); }
      100% { transform: scale(1); }
    }

    /* ── OTP boxes ── */
    .otp-row {
      display: flex;
      gap: 10px;
      justify-content: center;
    }
    .otp-box {
      width: 46px; height: 54px;
      text-align: center;
      font-size: 1.4em;
      font-weight: 700;
      padding: 0;
      letter-spacing: 0;
    }

    .resend-row {
      text-align: center;
      font-size: 0.82em;
      color: rgba(255,255,255,0.45);
    }
    .resend-btn {
      background: none; border: none; cursor: pointer;
      color: #D4AF37; font-weight: 600; font-size: 1em;
      font-family: 'Inter', sans-serif;
      transition: color 0.2s;
    }
    .resend-btn:hover:not(:disabled) { color: #FFE4B5; text-decoration: underline; }
    .resend-btn:disabled { color: rgba(255,255,255,0.3); cursor: not-allowed; text-decoration: none; }

    .pwd-checklist { list-style: none; margin-top: 8px; display: flex; flex-direction: column; gap: 6px; }
    .pwd-checklist li {
      display: flex; align-items: center; gap: 8px;
      font-size: 0.78em; color: rgba(255,255,255,0.35);
      transition: color 0.25s;
    }
    .pwd-checklist li.valid { color: #9be8a4; }
    .pwd-checklist li.valid .chk-icon {
      border-color: #9be8a4;
      background: rgba(155,232,164,0.15);
      animation: checkPop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .pwd-checklist li.valid .chk-icon::after { border-color: #9be8a4; }

    .btn-login {
      margin-top: 4px;
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
        background-position 0.5s ease,
        opacity 0.3s ease;
      box-shadow:
        0 6px 20px rgba(212,175,55,0.35),
        0 0 30px rgba(212,175,55,0.2);
    }
    .btn-login:disabled { opacity: 0.65; cursor: not-allowed; }
    .btn-login::before {
      content: '';
      position: absolute;
      top: 0; left: -100%;
      width: 60%; height: 100%;
      background: linear-gradient(120deg, transparent 30%, rgba(255,255,255,0.35) 50%, transparent 70%);
      transition: left 0.5s ease;
      pointer-events: none;
    }
    .btn-login.active { animation: zoomPulse 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
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
    .btn-login:hover::before { left: 140%; }
    .btn-login:active { transform: scale(0.97); }
    .btn-login .btn-text { position: relative; z-index: 1; }
    .btn-login .btn-icon { position: relative; z-index: 1; margin-left: 8px; }

    .btn-ghost {
      width: 100%;
      padding: 12px 20px;
      background: none;
      border: 1px solid rgba(255,255,255,0.16);
      border-radius: 14px;
      color: rgba(255,255,255,0.6);
      font-family: 'Inter', sans-serif;
      font-size: 0.88em;
      font-weight: 500;
      cursor: pointer;
      transition: border-color 0.25s, color 0.25s, background 0.25s;
    }
    .btn-ghost:hover { border-color: rgba(212,175,55,0.4); color: #D4AF37; background: rgba(212,175,55,0.06); }

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

    .success-msg {
      display: none;
      background: rgba(212,175,55,0.15);
      border: 1px solid rgba(212,175,55,0.4);
      border-radius: 12px;
      padding: 12px 16px;
      font-size: 0.85em;
      color: #FFE4B5;
      text-align: center;
      animation: fadeUp 0.4s ease;
    }
    .success-msg.show { display: block; }

    .login-footer {
      margin-top: 24px;
      text-align: center;
      font-size: 0.82em;
      color: rgba(255,255,255,0.4);
    }
    .login-footer a { color: #D4AF37; text-decoration: none; font-weight: 500; transition: color 0.2s; }
    .login-footer a:hover { color: #FFE4B5; }

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
    .back-link:hover { color: #D4AF37; transform: translateX(-4px); }

    /* ══════════════════ Status overlay (proses → hasil) — sama seperti login.php ══════════════════ */
    .status-overlay {
      position: fixed;
      inset: 0;
      z-index: 100;
      background: rgba(20,10,5,0.72);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.3s ease;
    }
    .status-overlay.show { opacity: 1; pointer-events: all; }
    .status-box {
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(255,255,255,0.15);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border-radius: 24px;
      padding: 42px 48px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 16px;
      min-width: 240px;
      transform: scale(0.85);
      transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
      box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    }
    .status-overlay.show .status-box { transform: scale(1); }

    .spinner {
      width: 54px; height: 54px;
      border-radius: 50%;
      border: 4px solid rgba(212,175,55,0.2);
      border-top-color: #D4AF37;
      animation: spin 0.8s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    .result-icon {
      width: 54px; height: 54px;
      border-radius: 50%;
      display: none;
      align-items: center;
      justify-content: center;
      position: relative;
    }
    .result-icon.success {
      display: flex;
      background: rgba(155,232,164,0.15);
      border: 2px solid #9be8a4;
      animation: popIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .result-icon.success svg { width: 26px; height: 26px; }
    .result-icon.success path {
      stroke: #9be8a4; stroke-width: 3; fill: none;
      stroke-linecap: round; stroke-linejoin: round;
      stroke-dasharray: 30; stroke-dashoffset: 30;
      animation: drawCheck 0.4s ease forwards 0.15s;
    }
    @keyframes drawCheck { to { stroke-dashoffset: 0; } }

    .result-icon.fail {
      display: flex;
      background: rgba(238,42,123,0.15);
      border: 2px solid #ff8ab5;
      animation: popIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), shake 0.5s ease 0.1s;
    }
    .result-icon.fail::before, .result-icon.fail::after {
      content: ''; position: absolute; width: 22px; height: 3px;
      background: #ff8ab5; border-radius: 2px;
    }
    .result-icon.fail::before { transform: rotate(45deg); }
    .result-icon.fail::after  { transform: rotate(-45deg); }

    @keyframes popIn { 0%{transform:scale(0.5);opacity:0;} 100%{transform:scale(1);opacity:1;} }
    @keyframes shake { 0%,100%{transform:translateX(0);} 25%{transform:translateX(-6px);} 75%{transform:translateX(6px);} }

    .status-text { font-family:'Playfair Display', serif; font-size:1.15em; font-weight:600; color:#fff; text-align:center; }
    .status-sub { font-size:0.82em; color:rgba(255,255,255,0.55); text-align:center; margin-top:-8px; }

    /* ══════════════════ Responsive (mobile) ══════════════════ */
    @media (max-width: 480px) {
      .login-wrapper { padding: 14px; max-width: 100%; }
      .login-card { padding: 32px 22px 28px; border-radius: 22px; }
      .login-brand { margin-bottom: 22px; }
      .login-brand h1 { font-size: 1.8em; }
      .login-brand .eyebrow { letter-spacing: 3px; font-size: 0.68em; }
      .login-brand .subtitle { font-size: 0.82em; }
      .back-link { top: 14px; left: 14px; font-size: 0.78em; }
      .field-group input { padding: 12px 14px 12px 42px; font-size: 0.92em; }
      .btn-login { padding: 13px 18px; font-size: 0.98em; }
      .step-indicator { gap: 6px; }
      .step-dot { width: 26px; height: 26px; font-size: 0.72em; }
      .step-line { width: 18px; }
      .otp-row { gap: 6px; }
      .otp-box { width: 40px; height: 48px; font-size: 1.2em; }
      .pwd-checklist li { font-size: 0.72em; }
      .status-box { padding: 28px 22px; min-width: 0; width: 82%; }
    }

    @media (max-width: 360px) {
      .login-card { padding: 26px 14px 24px; }
      .login-brand h1 { font-size: 1.55em; }
      .otp-row { gap: 4px; }
      .otp-box { width: 34px; height: 44px; font-size: 1.05em; }
    }
  </style>
</head>
<body>

  <a href="login.php" class="back-link">&#8592; Kembali ke Login</a>

  <div id="sparkles"></div>

  <div class="login-wrapper">
    <div class="login-card">

      <div class="login-brand">
        <span class="eyebrow">🔑 Reset Akses</span>
        <h1>Lupa Password</h1>
        <span class="subtitle">Ikuti langkah berikut untuk mengatur ulang password</span>
      </div>

      <div class="step-indicator">
        <div class="step-dot active" id="dot1">1</div>
        <div class="step-line" id="line1"></div>
        <div class="step-dot" id="dot2">2</div>
        <div class="step-line" id="line2"></div>
        <div class="step-dot" id="dot3">3</div>
      </div>

      <div class="error-msg" id="errorMsg"><span id="errorText">Terjadi kesalahan.</span></div>
      <div class="success-msg" id="successMsg"><span id="successText"></span></div>

      <!-- ═══ STEP 1: Masukkan email ═══ -->
      <form class="login-form" id="formStep1" novalidate>
        <div class="step-panel active" id="panelStep1">
          <p class="step-desc">Masukkan email <strong>Gmail</strong> yang terdaftar pada akun Anda. Kami akan mengirimkan kode OTP untuk verifikasi.</p>

          <div class="field-group">
            <label for="resetEmail">Email Gmail</label>
            <div class="input-wrap">
              <input type="email" id="resetEmail" name="email" placeholder="contoh: nama@gmail.com" autocomplete="email" required>
              <span class="field-icon">✉️</span>
            </div>
          </div>

          <button type="submit" class="btn-login" id="btnKirimOtp">
            <span class="btn-text">Kirim Kode OTP</span>
            <span class="btn-icon">→</span>
          </button>
        </div>
      </form>

      <!-- ═══ STEP 2: Verifikasi OTP ═══ -->
      <form class="login-form" id="formStep2" novalidate>
        <div class="step-panel" id="panelStep2">
          <p class="step-desc">Masukkan 6 digit kode OTP yang dikirim ke <strong id="targetEmailText"></strong></p>

          <div class="otp-row">
            <input type="text" inputmode="numeric" maxlength="1" class="field-group input-wrap otp-box" data-otp-index="0">
            <input type="text" inputmode="numeric" maxlength="1" class="field-group input-wrap otp-box" data-otp-index="1">
            <input type="text" inputmode="numeric" maxlength="1" class="field-group input-wrap otp-box" data-otp-index="2">
            <input type="text" inputmode="numeric" maxlength="1" class="field-group input-wrap otp-box" data-otp-index="3">
            <input type="text" inputmode="numeric" maxlength="1" class="field-group input-wrap otp-box" data-otp-index="4">
            <input type="text" inputmode="numeric" maxlength="1" class="field-group input-wrap otp-box" data-otp-index="5">
          </div>

          <div class="resend-row">
            Tidak menerima kode? <button type="button" class="resend-btn" id="btnResend" disabled>Kirim ulang (<span id="resendTimer">60</span>d)</button>
          </div>

          <button type="submit" class="btn-login" id="btnVerifOtp">
            <span class="btn-text">Verifikasi Kode</span>
            <span class="btn-icon">→</span>
          </button>
          <button type="button" class="btn-ghost" id="btnBackStep1">&#8592; Ganti Email</button>
        </div>
      </form>

      <!-- ═══ STEP 3: Password baru ═══ -->
      <form class="login-form" id="formStep3" novalidate>
        <div class="step-panel" id="panelStep3">
          <p class="step-desc">Buat password baru untuk akun Anda.</p>

          <div class="field-group">
            <label for="newPassword">Password Baru</label>
            <div class="input-wrap">
              <input type="password" id="newPassword" name="password" placeholder="Buat password baru (min. 6 karakter)" autocomplete="new-password" required>
              <span class="field-icon">🔒</span>
              <button type="button" class="toggle-eye" id="toggleNewPassword" tabindex="-1" aria-label="Tampilkan password">👁️</button>
            </div>
            <ul class="pwd-checklist" id="pwdChecklist">
              <li id="chkLength"><span class="chk-icon"></span>Minimal 6 karakter</li>
            </ul>
          </div>

          <div class="field-group">
            <label for="confirmNewPassword">Konfirmasi Password</label>
            <div class="input-wrap">
              <input type="password" id="confirmNewPassword" name="confirm_password" placeholder="Ulangi password baru" autocomplete="new-password" required>
              <span class="field-icon">🔐</span>
              <button type="button" class="toggle-eye" id="toggleConfirmNewPassword" tabindex="-1" aria-label="Tampilkan password">👁️</button>
            </div>
            <div class="field-hint" id="matchHint">
              <span class="chk-icon"></span><span class="hint-text"></span>
            </div>
          </div>

          <button type="submit" class="btn-login" id="btnResetPassword">
            <span class="btn-text">Simpan Password Baru</span>
            <span class="btn-icon">→</span>
          </button>
        </div>
      </form>

      <div class="login-footer">
        Ingat password Anda? <a href="login.php">Kembali ke Login</a>
      </div>
      <div class="login-footer" style="margin-top:6px;">
        Tidak bisa akses email? <a href="hubungi_admin.php">Hubungi Admin</a>
      </div>

    </div>
  </div>

  <div class="status-overlay" id="statusOverlay">
    <div class="status-box">
      <div class="spinner" id="statusSpinner"></div>
      <div class="result-icon" id="statusResultIcon"></div>
      <div class="status-text" id="statusText">Memproses...</div>
      <div class="status-sub" id="statusSub"></div>
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
          width: ${size}px; height: ${size}px;
          left: ${Math.random() * 100}vw; top: ${Math.random() * 100}vh;
          background: ${colors[Math.floor(Math.random() * colors.length)]};
          animation-duration: ${Math.random() * 8 + 6}s;
          animation-delay: ${Math.random() * 6}s;
          box-shadow: 0 0 ${size * 2}px ${colors[0]};
        `;
        container.appendChild(s);
      }
    })();

    /* ── Status overlay controls (sama seperti login.php) ── */
    const overlay      = document.getElementById('statusOverlay');
    const spinnerEl     = document.getElementById('statusSpinner');
    const resultIconEl  = document.getElementById('statusResultIcon');
    const statusTextEl  = document.getElementById('statusText');
    const statusSubEl   = document.getElementById('statusSub');

    function showProcessing(text, sub) {
      spinnerEl.style.display = 'block';
      resultIconEl.className = 'result-icon';
      resultIconEl.innerHTML = '';
      statusTextEl.textContent = text || 'Memproses...';
      statusSubEl.textContent = sub || 'Mohon tunggu sebentar';
      overlay.classList.add('show');
    }
    function showResult(success, text, sub) {
      spinnerEl.style.display = 'none';
      resultIconEl.className = 'result-icon ' + (success ? 'success' : 'fail');
      resultIconEl.innerHTML = success
        ? '<svg viewBox="0 0 24 24"><path d="M4 12l5 5L20 7"/></svg>'
        : '';
      statusTextEl.textContent = text;
      statusSubEl.textContent = sub || '';
    }
    function hideOverlay() { overlay.classList.remove('show'); }

    function showError(msg) {
      document.getElementById('successMsg').classList.remove('show');
      document.getElementById('errorText').textContent = msg;
      document.getElementById('errorMsg').classList.add('show');
    }
    function clearError() { document.getElementById('errorMsg').classList.remove('show'); }
    function showInlineSuccess(msg) {
      clearError();
      document.getElementById('successText').textContent = msg;
      document.getElementById('successMsg').classList.add('show');
    }

    /* ── State ── */
    let targetEmail = '';
    let resendTimerHandle = null;

    /* ── Step navigation ── */
    function goToStep(step) {
      document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
      document.getElementById('panelStep' + step).classList.add('active');

      for (let i = 1; i <= 3; i++) {
        const dot = document.getElementById('dot' + i);
        dot.classList.remove('active', 'done');
        if (i < step) dot.classList.add('done');
        else if (i === step) dot.classList.add('active');
      }
      const line1 = document.getElementById('line1');
      const line2 = document.getElementById('line2');
      line1.classList.toggle('done', step > 1);
      line2.classList.toggle('done', step > 2);

      clearError();
      document.getElementById('successMsg').classList.remove('show');
    }

    /* ══════════════════ STEP 1: Kirim OTP ══════════════════ */
    const formStep1   = document.getElementById('formStep1');
    const resetEmailInput = document.getElementById('resetEmail');
    const btnKirimOtp  = document.getElementById('btnKirimOtp');

    async function kirimOtp(isResend = false) {
      const email = resetEmailInput.value.trim();
      if (!email) { showError('Masukkan email Gmail Anda.'); return; }

      btnKirimOtp.disabled = true;
      showProcessing('Mengirim Kode...', 'Sedang mengirim OTP ke email Anda');

      try {
        const res = await fetch('proses_lupa_password.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
          body: new URLSearchParams({ email })
        });
        const data = await res.json();

        if (data.success) {
          targetEmail = email;
          document.getElementById('targetEmailText').textContent = data.masked_email || email;
          showResult(true, 'Kode Terkirim!', 'Silakan periksa inbox Gmail Anda');
          setTimeout(() => {
            hideOverlay();
            btnKirimOtp.disabled = false;
            if (!isResend) { goToStep(2); }
            resetOtpBoxes();
            otpBoxes[0].focus();
            startResendTimer();
          }, 1200);
        } else {
          showResult(false, 'Gagal Mengirim', data.message || 'Terjadi kesalahan.');
          setTimeout(() => {
            hideOverlay();
            btnKirimOtp.disabled = false;
            showError(data.message || 'Email tidak ditemukan.');
          }, 1500);
        }
      } catch (err) {
        showResult(false, 'Terjadi Kesalahan', 'Gagal terhubung ke server. Coba lagi.');
        setTimeout(() => { hideOverlay(); btnKirimOtp.disabled = false; }, 1500);
      }
    }

    formStep1.addEventListener('submit', function (e) {
      e.preventDefault();
      kirimOtp(false);
    });

    /* ══════════════════ STEP 2: Verifikasi OTP ══════════════════ */
    const formStep2 = document.getElementById('formStep2');
    const otpBoxes  = Array.from(document.querySelectorAll('.otp-box'));
    const btnVerifOtp = document.getElementById('btnVerifOtp');
    const btnResend    = document.getElementById('btnResend');
    const resendTimerEl = document.getElementById('resendTimer');

    function resetOtpBoxes() { otpBoxes.forEach(b => b.value = ''); }

    otpBoxes.forEach((box, idx) => {
      box.addEventListener('input', () => {
        box.value = box.value.replace(/[^0-9]/g, '');
        if (box.value && idx < otpBoxes.length - 1) otpBoxes[idx + 1].focus();
      });
      box.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !box.value && idx > 0) otpBoxes[idx - 1].focus();
      });
      box.addEventListener('paste', (e) => {
        e.preventDefault();
        const paste = (e.clipboardData.getData('text') || '').replace(/[^0-9]/g, '').slice(0, 6);
        paste.split('').forEach((ch, i) => { if (otpBoxes[i]) otpBoxes[i].value = ch; });
        if (paste.length) otpBoxes[Math.min(paste.length, otpBoxes.length) - 1].focus();
      });
    });

    function startResendTimer() {
      let sisa = 60;
      btnResend.disabled = true;
      resendTimerEl.textContent = sisa;
      clearInterval(resendTimerHandle);
      resendTimerHandle = setInterval(() => {
        sisa--;
        resendTimerEl.textContent = sisa;
        if (sisa <= 0) {
          clearInterval(resendTimerHandle);
          btnResend.disabled = false;
          btnResend.textContent = 'Kirim ulang kode';
        }
      }, 1000);
    }

    btnResend.addEventListener('click', () => {
      btnResend.textContent = 'Kirim ulang (';
      kirimOtp(true);
    });

    document.getElementById('btnBackStep1').addEventListener('click', () => {
      clearInterval(resendTimerHandle);
      goToStep(1);
    });

    formStep2.addEventListener('submit', async function (e) {
      e.preventDefault();
      const otp = otpBoxes.map(b => b.value).join('');
      if (otp.length !== 6) { showError('Masukkan 6 digit kode OTP.'); return; }

      btnVerifOtp.disabled = true;
      showProcessing('Memverifikasi...', 'Sedang memeriksa kode OTP Anda');

      try {
        const res = await fetch('proses_verifikasi_otp.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
          body: new URLSearchParams({ email: targetEmail, otp })
        });
        const data = await res.json();

        if (data.success) {
          showResult(true, 'Kode Terverifikasi!', 'Silakan buat password baru');
          setTimeout(() => { hideOverlay(); btnVerifOtp.disabled = false; goToStep(3); }, 1200);
        } else {
          showResult(false, 'Verifikasi Gagal', data.message || 'Kode OTP salah atau kedaluwarsa.');
          setTimeout(() => {
            hideOverlay();
            btnVerifOtp.disabled = false;
            showError(data.message || 'Kode OTP salah atau sudah kedaluwarsa.');
            resetOtpBoxes();
            otpBoxes[0].focus();
          }, 1500);
        }
      } catch (err) {
        showResult(false, 'Terjadi Kesalahan', 'Gagal terhubung ke server. Coba lagi.');
        setTimeout(() => { hideOverlay(); btnVerifOtp.disabled = false; }, 1500);
      }
    });

    /* ══════════════════ STEP 3: Password baru ══════════════════ */
    const formStep3 = document.getElementById('formStep3');
    const newPasswordInput     = document.getElementById('newPassword');
    const confirmPasswordInput = document.getElementById('confirmNewPassword');
    const btnResetPassword      = document.getElementById('btnResetPassword');
    const matchHint = document.getElementById('matchHint');
    const chkLength  = document.getElementById('chkLength');

    function setupEyeToggle(btnId, inputId) {
      const btn = document.getElementById(btnId);
      const input = document.getElementById(inputId);
      btn.addEventListener('click', () => {
        const willShow = input.type === 'password';
        input.type = willShow ? 'text' : 'password';
        btn.textContent = willShow ? '🙈' : '👁️';
        btn.classList.toggle('active', willShow);
      });
    }
    setupEyeToggle('toggleNewPassword', 'newPassword');
    setupEyeToggle('toggleConfirmNewPassword', 'confirmNewPassword');

    function checkPwdFields() {
      chkLength.classList.toggle('valid', newPasswordInput.value.length >= 6);
      if (confirmPasswordInput.value === '') {
        matchHint.classList.remove('ok', 'bad');
        matchHint.querySelector('.hint-text').textContent = '';
      } else if (newPasswordInput.value === confirmPasswordInput.value) {
        matchHint.classList.add('ok'); matchHint.classList.remove('bad');
        matchHint.querySelector('.hint-text').textContent = 'Password cocok';
      } else {
        matchHint.classList.add('bad'); matchHint.classList.remove('ok');
        matchHint.querySelector('.hint-text').textContent = 'Password belum sama';
      }
    }
    newPasswordInput.addEventListener('input', checkPwdFields);
    confirmPasswordInput.addEventListener('input', checkPwdFields);

    formStep3.addEventListener('submit', async function (e) {
      e.preventDefault();
      const p = newPasswordInput.value;
      const c = confirmPasswordInput.value;

      if (p.length < 6) { showError('Password minimal 6 karakter.'); return; }
      if (p !== c) { showError('Konfirmasi password tidak sama.'); return; }
      clearError();

      btnResetPassword.disabled = true;
      showProcessing('Menyimpan...', 'Sedang menyimpan password baru Anda');

      try {
        const res = await fetch('proses_reset_password.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
          body: new URLSearchParams({ email: targetEmail, password: p, confirm_password: c })
        });
        const data = await res.json();

        if (data.success) {
          showResult(true, 'Password Berhasil Diubah!', 'Mengalihkan ke halaman login...');
          setTimeout(() => { window.location.href = data.redirect || 'login.php'; }, 1400);
        } else {
          showResult(false, 'Gagal Menyimpan', data.message || 'Terjadi kesalahan.');
          setTimeout(() => {
            hideOverlay();
            btnResetPassword.disabled = false;
            showError(data.message || 'Terjadi kesalahan. Coba lagi.');
          }, 1500);
        }
      } catch (err) {
        showResult(false, 'Terjadi Kesalahan', 'Gagal terhubung ke server. Coba lagi.');
        setTimeout(() => { hideOverlay(); btnResetPassword.disabled = false; }, 1500);
      }
    });
  </script>
</body>
</html>
