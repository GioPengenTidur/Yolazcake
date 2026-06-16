<?php
session_start();

if(!isset($_SESSION['username'])){
    header("Location: ../auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Member – YOLAZCAKE</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  /* ─── CSS VARIABLES (Dark/Light toggle) ─── */
  :root {
    --bg-body: linear-gradient(160deg, #1e0e3a 0%, #2d1560 50%, #1a0a2e 100%);
    --nav-bg: rgba(30, 14, 58, 0.82);
    --nav-border: rgba(212,175,55,0.18);
    --dropdown-bg: rgba(30,14,58,0.97);
    --dropdown-border: rgba(212,175,55,0.25);
    --dropdown-item: rgba(255,255,255,0.75);
    --dropdown-item-hover-bg: rgba(212,175,55,0.12);
    --dropdown-item-hover-color: #D4AF37;
    --card-bg: rgba(255,255,255,0.05);
    --card-border: rgba(255,255,255,0.1);
    --text-main: #ffffff;
    --text-muted: rgba(255,255,255,0.55);
    --dark-btn-icon: '🌙';
  }

  /* Light mode overrides */
  body.light {
    --bg-body: linear-gradient(160deg, #f5f0ff 0%, #ede4ff 50%, #f8f5ff 100%);
    --nav-bg: rgba(255,255,255,0.88);
    --nav-border: rgba(212,175,55,0.3);
    --dropdown-bg: rgba(255,255,255,0.98);
    --dropdown-border: rgba(212,175,55,0.3);
    --dropdown-item: #333;
    --dropdown-item-hover-bg: rgba(212,175,55,0.1);
    --dropdown-item-hover-color: #b8860b;
    --card-bg: rgba(255,255,255,0.65);
    --card-border: rgba(0,0,0,0.08);
    --text-main: #1a0a2e;
    --text-muted: rgba(30,14,58,0.55);
  }

  body {
    min-height: 100vh;
    font-family: 'Inter', sans-serif;
    background: var(--bg-body);
    position: relative;
    overflow-x: hidden;
    transition: background 0.4s ease;
  }

  body::before {
    content: '';
    position: fixed;
    inset: 0;
    background:
      radial-gradient(ellipse at 20% 30%, rgba(212,175,55,0.10) 0%, transparent 55%),
      radial-gradient(ellipse at 80% 70%, rgba(232,160,191,0.10) 0%, transparent 55%);
    pointer-events: none;
    z-index: 0;
  }

  /* ─── NAVBAR ─────────────────────────────────── */
  nav {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 36px;
    background: var(--nav-bg);
    backdrop-filter: blur(18px);
    border-bottom: 1px solid var(--nav-border);
    box-shadow: 0 4px 24px rgba(0,0,0,0.25);
    transition: background 0.4s ease, border-color 0.4s ease;
  }

  .nav-left {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .nav-left img {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(212,175,55,0.5);
    box-shadow: 0 0 12px rgba(212,175,55,0.3);
  }

  .nav-left h2 {
    font-family: 'Playfair Display', serif;
    font-size: 1.25em;
    font-weight: 700;
    background: linear-gradient(135deg, #D4AF37 30%, #FFE4B5 70%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  body.light .nav-left h2 {
    background: linear-gradient(135deg, #b8860b 30%, #D4AF37 70%);
    -webkit-background-clip: text;
    background-clip: text;
  }

  .nav-right {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  /* Hamburger */
  .hamburger {
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 10px;
    padding: 7px 13px;
    cursor: pointer;
    font-size: 1.1em;
    color: rgba(255,255,255,0.85);
    transition: background 0.25s, color 0.25s, transform 0.4s ease;
    line-height: 1;
  }

  body.light .hamburger {
    background: rgba(0,0,0,0.06);
    border-color: rgba(0,0,0,0.12);
    color: #333;
  }

  .hamburger:hover {
    background: rgba(212,175,55,0.2);
    color: #D4AF37;
    border-color: rgba(212,175,55,0.3);
  }

  .hamburger.active {
    transform: rotate(180deg) scale(1.15);
    background: rgba(212,175,55,0.18);
    color: #D4AF37;
  }

  /* Dark Mode Button */
  .dark-btn {
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 10px;
    padding: 7px 13px;
    cursor: pointer;
    font-size: 1em;
    color: rgba(255,255,255,0.85);
    transition: background 0.25s, color 0.25s;
    line-height: 1;
  }

  body.light .dark-btn {
    background: rgba(0,0,0,0.06);
    border-color: rgba(0,0,0,0.12);
    color: #333;
  }

  .dark-btn:hover {
    background: rgba(212,175,55,0.18);
    color: #D4AF37;
    border-color: rgba(212,175,55,0.3);
  }

  /* ─── DROPDOWN (animasi sama seperti menu utama) ── */
  .dropdown {
    position: absolute;
    top: 70px;
    right: 24px;
    background: var(--dropdown-bg);
    backdrop-filter: blur(20px);
    border: 1px solid var(--dropdown-border);
    border-radius: 18px;
    padding: 14px 0;
    min-width: 200px;
    box-shadow: 0 16px 48px rgba(0,0,0,0.35), 0 0 0 1px rgba(212,175,55,0.05);
    z-index: 999;

    /* Animasi: tersembunyi */
    opacity: 0;
    transform: translateY(-16px) scale(0.97);
    pointer-events: none;
    transition: opacity 0.38s cubic-bezier(.22,.68,0,1.2),
                transform 0.38s cubic-bezier(.22,.68,0,1.2);
  }

  /* Saat aktif */
  .dropdown.show {
    opacity: 1;
    transform: translateY(0) scale(1);
    pointer-events: auto;
  }

  /* Divider top accent */
  .dropdown::before {
    content: '';
    position: absolute;
    top: 0; left: 14px; right: 14px;
    height: 2px;
    border-radius: 2px 2px 0 0;
    background: linear-gradient(90deg, #D4AF37, #ee2a7b, #D4AF37);
    background-size: 200% 100%;
    animation: goldSlide 3s linear infinite;
  }

  /* Item dropdown – awalnya tersembunyi */
  .dropdown p {
    padding: 11px 24px;
    color: var(--dropdown-item);
    font-size: 0.9em;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s, color 0.2s, padding-left 0.2s;
    opacity: 0;
    transform: translateY(-8px);
  }

  /* Item muncul satu per satu saat .show aktif */
  .dropdown.show p {
    animation: slideDown 0.38s ease forwards;
  }

  .dropdown.show p:nth-child(1) { animation-delay: 0.04s; }
  .dropdown.show p:nth-child(2) { animation-delay: 0.09s; }
  .dropdown.show p:nth-child(3) { animation-delay: 0.14s; }
  .dropdown.show p:nth-child(4) { animation-delay: 0.19s; }
  .dropdown.show p:nth-child(5) { animation-delay: 0.24s; }
  .dropdown.show p:nth-child(6) { animation-delay: 0.29s; }
  .dropdown.show p:nth-child(7) { animation-delay: 0.34s; }
  .dropdown.show p:nth-child(8) { animation-delay: 0.39s; }

  @keyframes slideDown {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .dropdown p:hover {
    background: var(--dropdown-item-hover-bg);
    color: var(--dropdown-item-hover-color);
    padding-left: 30px;
  }

  .dropdown .dropdown-divider {
    height: 1px;
    background: rgba(212,175,55,0.12);
    margin: 6px 16px;
  }

  /* ─── HERO ────────────────────────────────────── */
  .page-hero {
    position: relative;
    height: 300px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: linear-gradient(135deg, #2b1a11 0%, #4a2c1a 40%, #6d3e26 70%, #3a1f0e 100%);
    z-index: 1;
    margin-top: 65px;
  }

  .page-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
      radial-gradient(ellipse at 30% 50%, rgba(212,175,55,0.18) 0%, transparent 60%),
      radial-gradient(ellipse at 75% 40%, rgba(232,160,191,0.15) 0%, transparent 55%);
    animation: heroAurora 8s ease-in-out infinite alternate;
  }

  @keyframes heroAurora {
    0%   { opacity:0.6; transform:scale(1); }
    100% { opacity:1;   transform:scale(1.08) translateX(10px); }
  }

  .sparkle { position:absolute; border-radius:50%; pointer-events:none; animation:floatDot linear infinite; }

  @keyframes floatDot {
    0%   { transform:translateY(0) rotate(0deg); opacity:0; }
    20%  { opacity:1; }
    80%  { opacity:0.8; }
    100% { transform:translateY(-320px) rotate(360deg); opacity:0; }
  }

  .hero-inner { position:relative; z-index:2; text-align:center; color:#fff; }

  .hero-eyebrow {
    font-size:0.72em; font-weight:500; letter-spacing:5px; text-transform:uppercase;
    color:#D4AF37; margin-bottom:10px;
    opacity:0; animation:fadeSlideDown 0.8s forwards 0.3s;
  }

  .hero-inner h1 {
    font-family:'Playfair Display',serif; font-size:2.8em; font-weight:700;
    background:linear-gradient(135deg, #fff 30%, #D4AF37 60%, #FFE4B5 80%, #fff);
    background-size:200% 100%;
    -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
    animation:shimmerText 4s ease-in-out infinite, fadeSlideDown 0.9s forwards 0.5s;
    opacity:0;
  }

  .hero-inner .hero-sub {
    font-size:0.9em; color:rgba(255,255,255,0.65); margin-top:10px;
    opacity:0; animation:fadeSlideDown 0.9s forwards 0.9s;
  }

  .hero-divider {
    margin-top:16px; display:flex; justify-content:center; align-items:center; gap:12px;
    opacity:0; animation:fadeSlideDown 0.9s forwards 1.1s;
  }

  .hero-divider span { display:block; width:60px; height:1px; background:linear-gradient(to right,transparent,#D4AF37); }
  .hero-divider span:last-child { background:linear-gradient(to left,transparent,#D4AF37); }
  .hero-divider .diamond { color:#D4AF37; font-size:0.75em; letter-spacing:4px; }

  @keyframes shimmerText { 0%{background-position:100% 0;} 100%{background-position:-100% 0;} }
  @keyframes fadeSlideDown { from{opacity:0;transform:translateY(-18px);} to{opacity:1;transform:translateY(0);} }
  @keyframes goldSlide { 0%{background-position:0% 0;} 100%{background-position:200% 0;} }

  /* ─── LAYOUT ──────────────────────────────────── */
  .page-wrapper {
    position:relative; z-index:1;
    padding: 40px 28px 80px;
    max-width: 1100px;
    margin: 0 auto;
  }

  @keyframes cardReveal {
    from { opacity:0; transform:translateY(20px); }
    to   { opacity:1; transform:translateY(0); }
  }

  /* ─── TOPBAR ──────────────────────────────────── */
  .top-bar {
    display:flex; justify-content:space-between; align-items:center;
    margin-bottom:28px;
    opacity:0; animation:cardReveal 0.7s forwards 0.7s;
  }

  .section-eyebrow {
    font-size:0.72em; font-weight:600; letter-spacing:4px;
    text-transform:uppercase; color:#D4AF37;
  }

  .btn-back {
    display:inline-flex; align-items:center; gap:8px;
    padding:10px 22px;
    background:rgba(212,175,55,0.1);
    border:1px solid rgba(212,175,55,0.3);
    color:#D4AF37; font-family:'Inter',sans-serif;
    font-size:0.82em; font-weight:600; letter-spacing:1px;
    border-radius:999px; text-decoration:none;
    transition:transform 0.25s, box-shadow 0.3s, background 0.3s;
  }

  .btn-back:hover {
    transform:translateX(-3px);
    background:rgba(212,175,55,0.2);
    box-shadow:0 6px 20px rgba(212,175,55,0.25);
  }

  /* ─── GREETING CARD ───────────────────────────── */
  .greeting-card {
    position:relative;
    background:var(--card-bg);
    backdrop-filter:blur(20px);
    border:1px solid var(--card-border);
    border-radius:24px;
    padding:38px 40px;
    margin-bottom:28px;
    overflow:hidden;
    opacity:0; animation:cardReveal 0.8s forwards 0.85s;
    transition: background 0.4s, border-color 0.4s;
  }

  .greeting-card::before {
    content:''; position:absolute; top:0; left:0; right:0; height:3px;
    background:linear-gradient(90deg, #D4AF37, #ee2a7b, #D4AF37);
    background-size:200% 100%;
    animation:goldSlide 4s linear infinite;
  }

  .greeting-card::after {
    content:'';
    position:absolute;
    top:-60px; right:-60px;
    width:200px; height:200px;
    background:radial-gradient(circle, rgba(212,175,55,0.12) 0%, transparent 70%);
    border-radius:50%;
    pointer-events:none;
  }

  .greeting-name {
    font-family:'Playfair Display',serif;
    font-size:2em; font-weight:700;
    background:linear-gradient(135deg, var(--text-main, #fff) 30%, #D4AF37 70%);
    -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
    margin-bottom:8px;
  }

  .greeting-sub {
    font-size:0.9em; color:var(--text-muted);
  }

  .greeting-badge {
    display:inline-flex; align-items:center; gap:7px;
    margin-top:18px;
    padding:8px 20px;
    background:linear-gradient(135deg, rgba(212,175,55,0.2), rgba(184,134,11,0.1));
    border:1px solid rgba(212,175,55,0.4);
    border-radius:999px;
    font-size:0.8em; font-weight:600; color:#D4AF37; letter-spacing:1px;
  }

  /* ─── STATS ROW ───────────────────────────────── */
  .stats-row {
    display:grid; grid-template-columns:repeat(3,1fr); gap:16px;
    margin-bottom:28px;
    opacity:0; animation:cardReveal 0.7s forwards 1.0s;
  }

  .stat-card {
    background:var(--card-bg);
    backdrop-filter:blur(16px);
    border:1px solid var(--card-border);
    border-radius:18px; padding:22px;
    display:flex; align-items:center; gap:14px;
    transition:border-color 0.35s, box-shadow 0.35s, transform 0.3s, background 0.4s;
    cursor:default;
  }

  .stat-card:hover {
    border-color:rgba(212,175,55,0.35);
    box-shadow:0 0 28px rgba(212,175,55,0.22);
    transform:translateY(-3px);
  }

  .stat-card::before {
    content:''; display:block;
    width:3px; height:40px; border-radius:999px;
    background:linear-gradient(to bottom, #D4AF37, #b8860b);
    flex-shrink:0;
  }

  .stat-icon { font-size:1.5em; }
  .stat-val { font-family:'Playfair Display',serif; font-size:1.7em; font-weight:700; color:#D4AF37; line-height:1; }
  .stat-lbl { font-size:0.75em; color:var(--text-muted); margin-top:2px; letter-spacing:0.5px; }

  /* ─── SECTION CARD BASE ───────────────────────── */
  .section-card {
    background:var(--card-bg);
    backdrop-filter:blur(20px);
    border:1px solid var(--card-border);
    border-radius:24px;
    padding:36px 36px 32px;
    margin-bottom:24px;
    position:relative;
    overflow:hidden;
    opacity:0;
    transition: background 0.4s, border-color 0.4s;
  }

  .section-card::before {
    content:''; position:absolute; top:0; left:0; right:0; height:3px;
    background:linear-gradient(90deg, #D4AF37, #ee2a7b, #D4AF37);
    background-size:200% 100%;
    animation:goldSlide 4s linear infinite;
  }

  .section-card.delay1 { animation:cardReveal 0.8s forwards 1.1s; }
  .section-card.delay2 { animation:cardReveal 0.8s forwards 1.25s; }
  .section-card.delay3 { animation:cardReveal 0.8s forwards 1.4s; }
  .section-card.delay4 { animation:cardReveal 0.8s forwards 1.55s; }

  .section-title {
    font-family:'Playfair Display',serif;
    font-size:1.3em; font-weight:700; color:var(--text-main, #fff);
    margin-bottom:24px;
    display:flex; align-items:center; gap:10px;
  }

  .section-title::after {
    content:''; flex:1; height:1px;
    background:linear-gradient(to right, rgba(212,175,55,0.3), transparent);
  }

  /* ─── STATUS BOXES ────────────────────────────── */
  .status-grid {
    display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px;
  }

  .status-box {
    background:rgba(255,255,255,0.06);
    border:1px solid rgba(255,255,255,0.1);
    border-radius:16px; padding:24px;
    text-align:center;
    transition:border-color 0.35s, box-shadow 0.35s, transform 0.3s;
    cursor:default;
  }

  body.light .status-box {
    background: rgba(0,0,0,0.04);
    border-color: rgba(0,0,0,0.08);
  }

  .status-box:hover {
    border-color:rgba(212,175,55,0.4);
    box-shadow:0 0 28px rgba(212,175,55,0.2), 0 8px 24px rgba(0,0,0,0.2);
    transform:translateY(-5px) scale(1.02);
  }

  .status-box .s-icon { font-size:2.2em; margin-bottom:12px; }
  .status-box h3 { font-size:0.75em; font-weight:600; letter-spacing:2px; text-transform:uppercase; color:rgba(212,175,55,0.8); margin-bottom:8px; }
  .status-box p { font-family:'Playfair Display',serif; font-size:1.05em; color:var(--text-main, #fff); }

  /* ─── PROMO BOXES ─────────────────────────────── */
  .promo-grid {
    display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px;
  }

  .promo-box {
    background:rgba(255,255,255,0.06);
    border:1px solid rgba(232,160,191,0.2);
    border-radius:16px; padding:28px 24px;
    text-align:center;
    position:relative; overflow:hidden;
    transition:border-color 0.35s, box-shadow 0.35s, transform 0.3s;
    cursor:default;
  }

  body.light .promo-box {
    background: rgba(0,0,0,0.03);
    border-color: rgba(232,160,191,0.3);
  }

  .promo-box::before {
    content:''; position:absolute; top:0; left:0; right:0; height:2px;
    background:linear-gradient(90deg, #ee2a7b, #D4AF37, #ee2a7b);
    background-size:200% 100%;
    animation:goldSlide 3s linear infinite;
  }

  .promo-box:hover {
    border-color:rgba(232,160,191,0.45);
    box-shadow:0 0 30px rgba(232,160,191,0.2), 0 8px 24px rgba(0,0,0,0.2);
    transform:translateY(-6px) scale(1.02);
  }

  .promo-code {
    font-family:'Playfair Display',serif;
    font-size:1.4em; font-weight:700;
    background:linear-gradient(135deg, #ee2a7b, #D4AF37);
    -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
    margin-bottom:10px;
  }

  .promo-box p { font-size:0.88em; color:var(--text-muted); line-height:1.5; }

  /* ─── POINTS ──────────────────────────────────── */
  .points-main {
    background:linear-gradient(135deg, rgba(212,175,55,0.15), rgba(184,134,11,0.08));
    border:1px solid rgba(212,175,55,0.3);
    border-radius:18px; padding:36px;
    text-align:center; margin-bottom:20px;
    position:relative; overflow:hidden;
    transition:box-shadow 0.35s;
  }

  .points-main:hover {
    box-shadow:0 0 40px rgba(212,175,55,0.25);
  }

  .points-main::after {
    content:'⭐';
    position:absolute; top:-10px; right:20px;
    font-size:6em; opacity:0.06;
    pointer-events:none;
  }

  .points-number {
    font-family:'Playfair Display',serif;
    font-size:4em; font-weight:700;
    background:linear-gradient(135deg, #D4AF37, #FFE4B5, #D4AF37);
    background-size:200% 100%;
    -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
    animation:shimmerText 3s ease-in-out infinite;
    line-height:1; margin-bottom:8px;
  }

  .points-lbl { font-size:0.85em; color:var(--text-muted); letter-spacing:2px; text-transform:uppercase; }

  .reward-grid {
    display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:14px;
  }

  .reward-box {
    background:rgba(255,255,255,0.06);
    border:1px solid rgba(212,175,55,0.2);
    border-radius:14px; padding:20px;
    text-align:center;
    transition:border-color 0.35s, box-shadow 0.35s, transform 0.3s;
    cursor:default;
  }

  body.light .reward-box {
    background: rgba(0,0,0,0.03);
  }

  .reward-box:hover {
    border-color:rgba(212,175,55,0.45);
    box-shadow:0 0 22px rgba(212,175,55,0.2);
    transform:translateY(-4px);
  }

  .reward-box .r-icon { font-size:1.8em; margin-bottom:8px; }
  .reward-box .r-poin { font-size:0.72em; color:rgba(212,175,55,0.7); letter-spacing:1px; font-weight:600; margin-bottom:4px; }
  .reward-box .r-name { font-family:'Playfair Display',serif; font-size:0.95em; color:var(--text-main, #fff); font-weight:600; }

  /* ─── HISTORY ─────────────────────────────────── */
  .history-grid {
    display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px;
  }

  .history-item {
    background:rgba(255,255,255,0.06);
    border:1px solid rgba(255,255,255,0.1);
    border-left:3px solid #D4AF37;
    border-radius:14px; padding:22px;
    transition:border-color 0.35s, box-shadow 0.35s, transform 0.3s;
    cursor:default;
  }

  body.light .history-item {
    background: rgba(0,0,0,0.04);
    border-color: rgba(0,0,0,0.08);
    border-left-color: #D4AF37;
  }

  .history-item:hover {
    border-left-color:#FFE4B5;
    box-shadow:0 0 24px rgba(212,175,55,0.18);
    transform:translateY(-4px);
  }

  .history-item h3 { font-size:0.72em; font-weight:600; letter-spacing:2px; text-transform:uppercase; color:rgba(212,175,55,0.75); margin-bottom:10px; }
  .history-item p { font-family:'Playfair Display',serif; font-size:1.05em; color:var(--text-main, #fff); }

  /* ─── PARTICLES ───────────────────────────────── */
  .particle { position:fixed; border-radius:50%; pointer-events:none; animation:particleFloat linear infinite; z-index:0; }
  @keyframes particleFloat {
    0%   { transform:translateY(100vh) scale(0); opacity:0; }
    10%  { opacity:0.5; }
    90%  { opacity:0.3; }
    100% { transform:translateY(-100px) scale(1); opacity:0; }
  }

  /* ─── FOOTER ──────────────────────────────────── */
  .footer {
    position:relative; z-index:1;
    text-align:center;
    padding:36px 20px;
    font-size:0.8em; color:var(--text-muted);
    border-top:1px solid rgba(255,255,255,0.06);
    line-height:1.8;
  }

  body.light .footer {
    border-top-color: rgba(0,0,0,0.08);
  }

  /* ─── RESPONSIVE ──────────────────────────────── */
  @media(max-width:768px){
    nav { padding:12px 18px; }
    .page-hero { height:240px; }
    .hero-inner h1 { font-size:2em; }
    .page-wrapper { padding:24px 16px 60px; }
    .stats-row { grid-template-columns:1fr; }
    .greeting-card { padding:26px 22px; }
    .section-card { padding:24px 20px; }
    .dropdown { right: 12px; min-width: 180px; }
  }
</style>
</head>
<body>

<div id="particles"></div>

<!-- NAVBAR -->
<nav>
  <div class="nav-left">
    <img src="https://lh3.googleusercontent.com/gps-cs/ACgwaOvrk66Mw6TuaNg3tG6p8G9hJq_wOTUoBmpbb3qtX0t9CN0D6K8ns6HxQUsk_xRrGiRBD__9n78mwhr3RZ7cwM3UINa2Jjzvzx2U1l8S2SP93wZa3ga4xfn1BY446aaj_CJ_6ACQYiN58RQ=w203-h304-k-no" alt="logo">
    <h2>YOLAZCAKE</h2>
  </div>

  <!-- Navbar desktop (5 li onclick dihapus, hanya hamburger yang digunakan) -->

  <div class="nav-right">
    <div class="hamburger" onclick="toggleMenu()" id="hamburger">☰</div>
    <div class="dark-btn" id="darkBtn" onclick="toggleDark()">☀️</div>
  </div>

  <!-- DROPDOWN dengan animasi slideDown sama seperti halaman menu -->
  <div class="dropdown" id="dropdown">
    <p onclick="window.location.href='../index.php'">🏠 Home</p>
    <p onclick="window.location.href='../produk/menu.php'">☕ Menu</p>
    <p onclick="window.location.href='../gallery.php'">🖼️ Gallery</p>
    <p onclick="window.location.href='../about.php'">✨ About</p>
    <p onclick="window.location.href='../contact.php'">📞 Contact</p>
    <div class="dropdown-divider"></div>
    <p onclick="window.location.href='../auth/logout.php'" style="color:#ee2a7b;">🚪 Logout</p>
  </div>
</nav>

<!-- HERO -->
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow">✦ YOLAZCAKE Sintang ✦</p>
    <h1>Member Area</h1>
    <p class="hero-sub">Halaman eksklusif untuk member setia YOLAZCAKE</p>
    <div class="hero-divider">
      <span></span><span class="diamond">✦ ✦ ✦</span><span></span>
    </div>
  </div>
</div>

<div class="page-wrapper">

  <!-- TOP BAR -->
  <div class="top-bar">
    <span class="section-eyebrow">✦ Member Dashboard</span>
    <a href="../index.php" class="btn-back">🏠 Kembali ke Website</a>
  </div>

  <!-- GREETING CARD -->
  <div class="greeting-card">
    <div class="greeting-name">👋 Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>!</div>
    <div class="greeting-sub">Selamat datang kembali di YOLAZCAKE — nikmati berbagai keuntungan eksklusif Anda.</div>
    <div class="greeting-badge">✦ &nbsp;Gold Member &nbsp;✦</div>
  </div>

  <!-- STATS ROW -->
  <div class="stats-row">
    <div class="stat-card">
      <span class="stat-icon">⭐</span>
      <div>
        <div class="stat-val">250</div>
        <div class="stat-lbl">Poin Saya</div>
      </div>
    </div>
    <div class="stat-card">
      <span class="stat-icon">🎯</span>
      <div>
        <div class="stat-val">Gold</div>
        <div class="stat-lbl">Level Member</div>
      </div>
    </div>
    <div class="stat-card">
      <span class="stat-icon">📅</span>
      <div>
        <div class="stat-val">6 Bln</div>
        <div class="stat-lbl">Sudah Bergabung</div>
      </div>
    </div>
  </div>

  <!-- STATUS SECTION -->
  <div class="section-card delay1">
    <div class="section-title">✅ Status Member Anda</div>
    <div class="status-grid">
      <div class="status-box">
        <div class="s-icon">✅</div>
        <h3>Status</h3>
        <p>Member Aktif</p>
      </div>
      <div class="status-box">
        <div class="s-icon">🎯</div>
        <h3>Tier</h3>
        <p>Gold Member</p>
      </div>
      <div class="status-box">
        <div class="s-icon">📅</div>
        <h3>Bergabung</h3>
        <p>6 Bulan Lalu</p>
      </div>
    </div>
  </div>

  <!-- PROMO SECTION -->
  <div class="section-card delay2">
    <div class="section-title">🎁 Promo Khusus Member</div>
    <div class="promo-grid">
      <div class="promo-box">
        <div class="promo-code">YOLA10</div>
        <p>Diskon 10% untuk semua produk bakery</p>
      </div>
      <div class="promo-box">
        <div class="promo-code">FREECOFFEE</div>
        <p>Gratis 1 kopi untuk pembelian di atas Rp50.000</p>
      </div>
      <div class="promo-box">
        <div class="promo-code">BUY2GET1</div>
        <p>Beli 2 roti premium, gratis 1 roti pilihan</p>
      </div>
    </div>
  </div>

  <!-- POINTS SECTION -->
  <div class="section-card delay3">
    <div class="section-title">⭐ Poin & Reward Saya</div>
    <div class="points-main">
      <div class="points-number">250</div>
      <div class="points-lbl">Poin Terkumpul</div>
    </div>
    <div class="reward-grid">
      <div class="reward-box">
        <div class="r-icon">🎁</div>
        <div class="r-poin">100 POIN</div>
        <div class="r-name">Diskon 5%</div>
      </div>
      <div class="reward-box">
        <div class="r-icon">☕</div>
        <div class="r-poin">200 POIN</div>
        <div class="r-name">Gratis Kopi</div>
      </div>
      <div class="reward-box">
        <div class="r-icon">🎂</div>
        <div class="r-poin">500 POIN</div>
        <div class="r-name">Gratis Cake</div>
      </div>
    </div>
  </div>

  <!-- HISTORY SECTION -->
  <div class="section-card delay4">
    <div class="section-title">📅 Riwayat & Data Member</div>
    <div class="history-grid">
      <div class="history-item">
        <h3>Login Terakhir</h3>
        <p><?php echo date("d F Y"); ?></p>
      </div>
      <div class="history-item">
        <h3>Status Hari Ini</h3>
        <p>Aktif</p>
      </div>
      <div class="history-item">
        <h3>Total Kunjungan</h3>
        <p>15x</p>
      </div>
    </div>
  </div>

</div>

<!-- FOOTER -->
<div class="footer">
  © 2026 YOLAZCAKE Sintang • Cafe • Bakery • Boutique<br>
  Jl. Lintas Melawi, Ladang, Sintang, Kalimantan Barat<br>
  WA: 0815-7815-7888
</div>

<script>
  /* ── Sparkles in hero ── */
  (function(){
    const hero = document.getElementById('pageHero');
    const colors = ['#D4AF37','#FFE4B5','#E8A0BF','#fff','#f9ce34','#b8860b'];
    for(let i = 0; i < 22; i++){
      const d = document.createElement('div');
      d.className = 'sparkle';
      const s = Math.random()*5+2;
      d.style.cssText = `width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;bottom:${Math.random()*30}%;animation-duration:${4+Math.random()*7}s;animation-delay:${Math.random()*5}s;opacity:0;`;
      hero.appendChild(d);
    }
  })();

  /* ── Background particles ── */
  (function(){
    const c = document.getElementById('particles');
    const colors = ['rgba(212,175,55,0.4)','rgba(232,160,191,0.3)','rgba(255,255,255,0.15)'];
    for(let i=0;i<16;i++){
      const p=document.createElement('div'); p.className='particle';
      const s=Math.random()*5+2;
      p.style.cssText=`width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;animation-duration:${10+Math.random()*12}s;animation-delay:${Math.random()*10}s;`;
      c.appendChild(p);
    }
  })();

  /* ── Hamburger & Dropdown Toggle ── */
  function toggleMenu(){
    const dropdown = document.getElementById('dropdown');
    const burger   = document.getElementById('hamburger');

    dropdown.classList.toggle('show');
    burger.classList.toggle('active');

    burger.innerHTML = burger.classList.contains('active') ? '✖' : '☰';
  }

  /* Tutup dropdown saat item diklik */
  document.querySelectorAll('.dropdown p').forEach(item => {
    item.addEventListener('click', () => {
      const dropdown = document.getElementById('dropdown');
      const burger   = document.getElementById('hamburger');
      dropdown.classList.remove('show');
      burger.classList.remove('active');
      burger.innerHTML = '☰';
    });
  });

  /* Tutup dropdown saat klik di luar */
  document.addEventListener('click', (e) => {
    const dropdown = document.getElementById('dropdown');
    const burger   = document.getElementById('hamburger');
    if (!dropdown.contains(e.target) && !burger.contains(e.target)) {
      dropdown.classList.remove('show');
      burger.classList.remove('active');
      burger.innerHTML = '☰';
    }
  });

  /* ── Dark / Light Mode ── */
  function toggleDark(){
    const isLight = document.body.classList.toggle('light');
    const btn = document.getElementById('darkBtn');
    btn.textContent = isLight ? '☀️' : '🌙';
    localStorage.setItem('memberTheme', isLight ? 'light' : 'dark');
  }

  /* Restore saved theme on load */
  (function(){
    const saved = localStorage.getItem('memberTheme');
    if(saved === 'light'){
      document.body.classList.add('light');
      document.getElementById('darkBtn').textContent = '☀️';
    }
  })();
</script>

</body>
</html>
