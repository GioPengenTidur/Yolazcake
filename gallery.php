<?php
session_start();
require_once 'config/koneksi.php';

// Ambil semua foto dari DB
$galeri_query = mysqli_query($conn, "SELECT * FROM galeri ORDER BY created_at DESC");
$galeri_all   = [];
$counts       = ['all'=>0,'interior'=>0,'kue'=>0,'coffee'=>0,'boutique'=>0];
while ($gr = mysqli_fetch_assoc($galeri_query)) {
    $galeri_all[] = $gr;
    $counts['all']++;
    if (isset($counts[$gr['kategori']])) $counts[$gr['kategori']]++;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gallery - YOLAZCAKE Sintang</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/responsive.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>

    /* ============================================================
       GALLERY PREMIUM — Full redesign matching menu.php aesthetic
    ============================================================ */

    /* ---- HERO BANNER (same as before, unchanged) ---- */
    .gallery-hero {
      position: relative;
      height: 360px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      overflow: hidden;
      background: linear-gradient(135deg, #2b1a11 0%, #4a2c1a 40%, #6d3e26 70%, #3a1f0e 100%);
    }

    .gallery-hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background:
        radial-gradient(circle at 20% 40%, rgba(232,160,191,0.35) 0%, transparent 45%),
        radial-gradient(circle at 80% 60%, rgba(212,175,55,0.35) 0%, transparent 45%),
        radial-gradient(circle at 50% 10%, rgba(200,162,200,0.2) 0%, transparent 40%);
      animation: orbFloat 8s ease-in-out infinite alternate;
    }

    @keyframes orbFloat {
      0%   { transform: scale(1) translateY(0); }
      100% { transform: scale(1.08) translateY(-12px); }
    }

    .gallery-hero .sparkle-wrap {
      position: absolute;
      inset: 0;
      pointer-events: none;
    }

    .sparkle {
      position: absolute;
      border-radius: 50%;
      background: rgba(255,215,0,0.75);
      animation: sparklePulse 3s ease-in-out infinite;
    }

    @keyframes sparklePulse {
      0%, 100% { opacity: 0; transform: scale(0.4); }
      50%       { opacity: 1; transform: scale(1); }
    }

    .gallery-hero-content { position: relative; z-index: 2; }

    .gallery-hero-eyebrow {
      font-family: 'Inter', sans-serif;
      font-size: 0.78em;
      font-weight: 500;
      letter-spacing: 5px;
      text-transform: uppercase;
      color: #D4AF37;
      margin-bottom: 12px;
      opacity: 0;
      animation: fadeInDown 0.9s forwards 0.3s;
    }

    .gallery-hero h1 {
      font-family: 'Playfair Display', serif;
      font-size: clamp(2.2em, 5vw, 3.6em);
      font-weight: 700;
      line-height: 1.1;
      background: linear-gradient(135deg, #fff 30%, #D4AF37 60%, #FFE4B5 80%, #fff);
      background-size: 200% 100%;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      opacity: 0;
      animation: shimmerText 4s ease-in-out infinite, fadeInDown 0.9s forwards 0.6s;
    }

    .gallery-hero h1 span {
      background: linear-gradient(90deg, #FFD700, #FFE4B5, #D4AF37, #FFF, #D4AF37);
      background-size: 300% 100%;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      animation: shimmerText 3.5s ease-in-out infinite;
    }

    .gallery-hero p {
      color: rgba(255,255,255,0.75);
      margin-top: 14px;
      font-size: 1.05em;
      opacity: 0;
      animation: fadeInDown 0.9s forwards 0.9s;
    }

    .scroll-hint {
      position: absolute;
      bottom: 20px;
      left: 0;
      right: 0;
      margin: 0 auto;
      width: fit-content;
      z-index: 3;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 5px;
      opacity: 0;
      animation: fadeInDown 1s forwards 1.5s;
    }
    .scroll-hint-arrows {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 3px;
    }
    .scroll-hint span {
      display: block;
      width: 9px;
      height: 9px;
      border-right: 2px solid rgba(255,215,0,0.85);
      border-bottom: 2px solid rgba(255,215,0,0.85);
      transform: rotate(45deg);
      animation: scrollBounce 1.4s infinite;
    }
    .scroll-hint span:nth-child(2) { animation-delay: 0.18s; }
    .scroll-hint span:nth-child(3) { animation-delay: 0.36s; }
    @keyframes scrollBounce {
      0%, 100% { opacity: 0.25; transform: rotate(45deg) translateY(-3px); }
      50%       { opacity: 1;   transform: rotate(45deg) translateY(3px); }
    }

    @keyframes shimmerText {
      0%   { background-position: 100% 0; }
      100% { background-position: -100% 0; }
    }
    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-25px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .gallery-hero-divider {
      position: relative;
      z-index: 2;
      margin-top: 22px;
      display: flex;
      justify-content: center;
      align-items: center;
      width: 100%;
      gap: 12px;
      opacity: 0;
      animation: fadeInDown 0.9s forwards 1.1s;
    }
    .gallery-hero-divider span {
      display: block;
      width: 80px;
      height: 1px;
      background: linear-gradient(to right, transparent, #D4AF37);
    }
    .gallery-hero-divider span:last-child {
      background: linear-gradient(to left, transparent, #D4AF37);
    }
    .gallery-hero-divider .hero-diamond {
      color: #D4AF37;
      font-size: 0.75em;
      letter-spacing: 4px;
      width: auto;
      height: auto;
      background: none;
    }

    /* ============================================================
       UNIFIED GALLERY SECTION — premium light with live effects
    ============================================================ */
    #gallery-section {
      background: linear-gradient(160deg, #EEF6FF 0%, #DFF0FF 55%, #EBF0FF 100%);
      position: relative;
      overflow: hidden;
      padding-bottom: 80px;
    }

    /* Animated floating glass orbs for premium feel */
    #gallery-section .orb {
      position: absolute;
      border-radius: 50%;
      pointer-events: none;
      z-index: 0;
      filter: blur(0px);
    }
    #gallery-section .orb-1 {
      width: 340px; height: 340px;
      top: -80px; right: -60px;
      background: radial-gradient(circle, rgba(212,175,55,0.13) 0%, rgba(232,160,191,0.07) 50%, transparent 70%);
      animation: orbDrift1 12s ease-in-out infinite alternate;
    }
    #gallery-section .orb-2 {
      width: 260px; height: 260px;
      bottom: 60px; left: -40px;
      background: radial-gradient(circle, rgba(232,160,191,0.15) 0%, rgba(200,162,200,0.07) 50%, transparent 70%);
      animation: orbDrift2 15s ease-in-out infinite alternate;
    }
    #gallery-section .orb-3 {
      width: 180px; height: 180px;
      top: 40%; left: 38%;
      background: radial-gradient(circle, rgba(212,175,55,0.10) 0%, transparent 65%);
      animation: orbDrift3 10s ease-in-out infinite alternate;
    }
    @keyframes orbDrift1 {
      0%   { transform: translate(0, 0) scale(1); }
      100% { transform: translate(-30px, 40px) scale(1.12); }
    }
    @keyframes orbDrift2 {
      0%   { transform: translate(0, 0) scale(1); }
      100% { transform: translate(25px, -35px) scale(1.08); }
    }
    @keyframes orbDrift3 {
      0%   { transform: translate(0, 0) scale(0.9); opacity: 0.6; }
      100% { transform: translate(-20px, 20px) scale(1.1); opacity: 1; }
    }

    /* Subtle dot mesh texture */
    #gallery-section::before {
      content: '';
      position: absolute;
      inset: 0;
      background-image: radial-gradient(circle, rgba(109,76,65,0.055) 1px, transparent 1px);
      background-size: 28px 28px;
      z-index: 0;
      pointer-events: none;
    }

    /* Gold shimmer stripe across top of section */
    #gallery-section .section-shine {
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 3px;
      background: linear-gradient(90deg, transparent, rgba(212,175,55,0.5), rgba(232,160,191,0.6), rgba(200,162,200,0.5), rgba(212,175,55,0.5), transparent);
      background-size: 300% 100%;
      animation: shineSlide 5s linear infinite;
      z-index: 1;
    }
    @keyframes shineSlide {
      0%   { background-position: 200% 0; }
      100% { background-position: -200% 0; }
    }

    /* ---- COMPACT HEADER + TABS COMBO ---- */
    .gallery-tabs-header {
      position: relative;
      z-index: 2;
      padding: 36px 40px 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0;
    }

    .gallery-tabs-header .eyebrow {
      font-family: 'Inter', sans-serif;
      font-size: 0.72rem;
      font-weight: 600;
      letter-spacing: 5px;
      text-transform: uppercase;
      color: #D4AF37;
      margin-bottom: 8px;
    }

    .gallery-tabs-header h2 {
      font-family: 'Playfair Display', serif;
      font-size: clamp(1.7em, 3vw, 2.5em);
      font-weight: 700;
      color: var(--brown);
      margin-bottom: 0;
      line-height: 1.15;
    }

    .gallery-tabs-header .subtitle-row {
      display: flex;
      align-items: center;
      gap: 14px;
      margin: 10px 0 0;
      color: #aaa;
      font-size: 0.82em;
      letter-spacing: 1.5px;
    }
    .gallery-tabs-header .subtitle-row::before,
    .gallery-tabs-header .subtitle-row::after {
      content: '';
      display: block;
      width: 40px;
      height: 1px;
      background: linear-gradient(to right, transparent, #D4AF37);
    }
    .gallery-tabs-header .subtitle-row::after {
      background: linear-gradient(to left, transparent, #D4AF37);
    }

    /* ---- FILTER TABS — redesigned compact with count pill ---- */
    .gallery-filter {
      display: flex;
      justify-content: center;
      gap: 8px;
      flex-wrap: wrap;
      padding: 22px 20px 32px;
      position: relative;
      z-index: 2;
    }

    /* Glass-card tab strip container */
    .gallery-filter-inner {
      display: flex;
      gap: 6px;
      flex-wrap: wrap;
      justify-content: center;
      background: rgba(255,255,255,0.55);
      backdrop-filter: blur(18px);
      border: 1px solid rgba(212,175,55,0.2);
      border-radius: 60px;
      padding: 6px 8px;
      box-shadow:
        0 4px 24px rgba(109,76,65,0.08),
        0 1px 4px rgba(212,175,55,0.12),
        inset 0 1px 0 rgba(255,255,255,0.9);
    }

    .filter-btn {
      padding: 9px 22px;
      border-radius: 50px;
      border: none;
      background: transparent;
      color: var(--brown);
      font-family: 'Inter', sans-serif;
      font-weight: 600;
      font-size: 0.86em;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
      position: relative;
      display: flex;
      align-items: center;
      gap: 7px;
      white-space: nowrap;
    }

    /* Count badge per tab */
    .filter-btn .tab-count {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 18px;
      height: 18px;
      font-size: 0.72em;
      font-weight: 700;
      border-radius: 50%;
      background: rgba(109,76,65,0.1);
      color: var(--brown);
      transition: all 0.3s;
    }

    .filter-btn:hover {
      background: rgba(212,175,55,0.12);
      transform: translateY(-1px);
    }

    .filter-btn.active {
      background: linear-gradient(135deg, var(--pink), var(--gold));
      color: #fff;
      box-shadow: 0 6px 20px rgba(232,160,191,0.4);
      transform: translateY(-1px);
    }
    .filter-btn.active .tab-count {
      background: rgba(255,255,255,0.25);
      color: #fff;
    }

    /* Dark mode tabs */
    body.dark .gallery-filter-inner {
      background: rgba(30,20,15,0.7);
      border-color: rgba(212,175,55,0.15);
    }
    body.dark .filter-btn { color: #ddd; }
    body.dark .filter-btn:hover { background: rgba(212,175,55,0.1); }
    body.dark .filter-btn .tab-count { background: rgba(255,255,255,0.08); color: #ddd; }

    /* ---- ABSTRACT COLLAGE MOSAIC GRID ---- */
    .gallery-mosaic {
      display: grid;
      grid-template-columns: repeat(12, 1fr);
      grid-auto-rows: 90px;
      gap: 12px;
      padding: 0 40px;
      max-width: 1400px;
      margin: 0 auto;
      position: relative;
      z-index: 2;
    }

    /* Card size variants — varied for true collage rhythm */
    .photo-card               { grid-column: span 4; grid-row: span 3; }
    .photo-card.tall          { grid-column: span 3; grid-row: span 4; }
    .photo-card.wide          { grid-column: span 6; grid-row: span 3; }
    .photo-card.wide-tall     { grid-column: span 5; grid-row: span 4; }
    .photo-card.small         { grid-column: span 3; grid-row: span 3; }
    .photo-card.hero-wide     { grid-column: span 7; grid-row: span 3; }
    .photo-card.sq            { grid-column: span 4; grid-row: span 3; }

    /* ---- PHOTO CARD base ---- */
    .photo-card {
      position: relative;
      border-radius: 20px;
      overflow: hidden;
      cursor: zoom-in;
      box-shadow: 0 8px 30px rgba(0,0,0,0.10);
      transition: transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1),
                  box-shadow 0.45s ease;
      opacity: 0;
      transform: translateY(40px) scale(0.97);
    }

    .photo-card.show {
      opacity: 1;
      transform: translateY(0) scale(1);
    }

    .photo-card:hover {
      transform: translateY(-8px) scale(1.025);
      box-shadow:
        0 22px 55px rgba(109,76,65,0.22),
        0 0 30px rgba(232,160,191,0.45),
        0 0 55px rgba(212,175,55,0.2);
      z-index: 10;
    }

    /* shimmer sweep */
    .photo-card::after {
      content: '';
      position: absolute;
      top: 0; left: -100%;
      width: 55%; height: 100%;
      background: linear-gradient(120deg, transparent, rgba(255,255,255,0.5), transparent);
      transform: skewX(-20deg);
      transition: 0.75s ease;
      pointer-events: none;
      z-index: 3;
    }
    .photo-card:hover::after { left: 130%; }

    /* golden border on hover */
    .photo-card::before {
      content: '';
      position: absolute;
      inset: 0;
      border-radius: 20px;
      padding: 2px;
      background: linear-gradient(135deg, rgba(232,160,191,0.9), rgba(212,175,55,0.9), rgba(200,162,200,0.9), rgba(232,160,191,0.9));
      background-size: 300% 300%;
      -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
      -webkit-mask-composite: xor;
      mask-composite: exclude;
      opacity: 0;
      transition: opacity 0.4s;
      animation: borderSpin 4s linear infinite;
      z-index: 4;
      pointer-events: none;
    }
    .photo-card:hover::before { opacity: 1; }

    @keyframes borderSpin {
      0%   { background-position: 0%   50%; }
      50%  { background-position: 100% 50%; }
      100% { background-position: 0%   50%; }
    }

    .photo-card img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    .photo-card:hover img { transform: scale(1.08); }

    /* Abstract overlay texture for premium collage feel */
    .photo-card .card-overlay {
      position: absolute;
      inset: 0;
      background:
        linear-gradient(135deg, rgba(232,160,191,0.08) 0%, transparent 50%),
        linear-gradient(to bottom, transparent 55%, rgba(43,26,17,0.75) 100%);
      z-index: 1;
      transition: opacity 0.4s;
    }

    /* Category badge */
    .card-badge {
      position: absolute;
      top: 14px;
      left: 14px;
      z-index: 5;
      background: rgba(212,175,55,0.88);
      color: #2b1a11;
      font-family: 'Inter', sans-serif;
      font-size: 0.62em;
      font-weight: 700;
      letter-spacing: 2px;
      text-transform: uppercase;
      padding: 4px 12px;
      border-radius: 999px;
      backdrop-filter: blur(4px);
      opacity: 0;
      transform: translateY(-6px);
      transition: opacity 0.35s, transform 0.35s;
    }
    .photo-card:hover .card-badge {
      opacity: 1;
      transform: translateY(0);
    }

    .card-label {
      position: absolute;
      bottom: 0; left: 0; right: 0;
      padding: 50px 18px 18px;
      transform: translateY(100%);
      transition: transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
      z-index: 3;
    }
    .photo-card:hover .card-label { transform: translateY(0); }
    .card-label h3 {
      color: #fff;
      font-family: 'Playfair Display', serif;
      font-size: 1em;
      font-weight: 700;
      margin-bottom: 3px;
    }
    .card-label p {
      color: rgba(255,255,255,0.75);
      font-size: 0.78em;
    }

    /* ---- LIGHTBOX ---- */
    .lightbox {
      position: fixed;
      inset: 0;
      z-index: 99999;
      display: flex;
      align-items: center;
      justify-content: center;
      pointer-events: none;
      opacity: 0;
      transition: opacity 0.4s;
    }
    .lightbox.active { opacity: 1; pointer-events: all; }
    .lightbox-overlay {
      position: absolute;
      inset: 0;
      background: rgba(10,5,2,0.92);
      backdrop-filter: blur(10px);
    }
    .lightbox-content {
      position: relative;
      z-index: 2;
      max-width: 88vw;
      max-height: 88vh;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 0 0 2px rgba(212,175,55,0.5), 0 30px 80px rgba(0,0,0,0.6);
      transform: scale(0.7);
      transition: transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .lightbox.active .lightbox-content { transform: scale(1); }
    .lightbox-content img {
      max-width: 88vw;
      max-height: 88vh;
      display: block;
      object-fit: contain;
    }
    .lightbox-content::after {
      content: '';
      position: absolute;
      top: 0; left: -100%;
      width: 50%; height: 100%;
      background: linear-gradient(120deg, transparent, rgba(255,255,255,0.25), transparent);
      transform: skewX(-15deg);
      pointer-events: none;
    }
    .lightbox.active .lightbox-content::after {
      animation: lightboxShimmer 0.8s forwards 0.2s;
    }
    @keyframes lightboxShimmer {
      0%   { left: -100%; }
      100% { left: 130%; }
    }
    .lightbox-close {
      position: absolute;
      top: 16px; right: 18px;
      z-index: 10;
      background: rgba(255,255,255,0.15);
      border: none;
      color: #fff;
      font-size: 1.5em;
      width: 44px; height: 44px;
      border-radius: 50%;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      backdrop-filter: blur(6px);
      transition: background 0.3s, transform 0.3s;
    }
    .lightbox-close:hover { background: rgba(232,160,191,0.5); transform: rotate(90deg) scale(1.1); }
    .lightbox-caption {
      position: absolute;
      bottom: 0; left: 0; right: 0;
      text-align: center;
      padding: 24px;
      background: linear-gradient(to top, rgba(10,5,2,0.9), transparent);
      color: #fff;
      z-index: 3;
    }
    .lightbox-caption h3 { font-size: 1.1em; font-weight: 700; }
    .lightbox-caption p { font-size: 0.85em; color: rgba(255,255,255,0.7); margin-top: 4px; }
    .lightbox-arrow {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      z-index: 10;
      background: rgba(255,255,255,0.1);
      border: none;
      color: #fff;
      font-size: 1.8em;
      width: 52px; height: 52px;
      border-radius: 50%;
      cursor: pointer;
      backdrop-filter: blur(6px);
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.3s, transform 0.3s;
    }
    .lightbox-arrow:hover { background: rgba(212,175,55,0.4); transform: translateY(-50%) scale(1.12); }
    .lightbox-arrow.prev { left: 20px; }
    .lightbox-arrow.next { right: 20px; }

    /* ---- RATING SECTION ---- */
    #Rating {
      padding: 80px 40px;
      background: linear-gradient(135deg, #1a0f0a 0%, #2c1a0e 60%, #3a2518 100%);
      position: relative;
      overflow: hidden;
    }
    #Rating::before {
      content: '';
      position: absolute;
      inset: 0;
      background:
        radial-gradient(circle at 15% 50%, rgba(232,160,191,0.18) 0%, transparent 50%),
        radial-gradient(circle at 85% 30%, rgba(212,175,55,0.18) 0%, transparent 50%);
      pointer-events: none;
    }
    #Rating h2 {
      text-align: center;
      color: #fff;
      font-size: clamp(1.8em, 3.5vw, 2.8em);
      font-weight: 800;
      margin-bottom: 50px;
      position: relative;
      z-index: 1;
    }
    #Rating h2::after {
      content: '';
      display: block;
      width: 60px;
      height: 3px;
      background: linear-gradient(90deg, var(--pink), var(--gold));
      margin: 12px auto 0;
      border-radius: 99px;
    }
    #Ulasan {
      max-width: 850px;
      margin: 0 auto;
      position: relative;
      z-index: 1;
      text-align: center;
    }
    #Stars {
      font-size: clamp(3em, 8vw, 5.5em);
      font-weight: 900;
      color: #fff;
      line-height: 1;
      margin-bottom: 8px;
    }
    .rating-stars {
      color: #FFD700;
      font-size: 1.1em;
      letter-spacing: 8px;
      text-shadow:
        0 0 10px rgba(255,215,0,0.9),
        0 0 25px rgba(255,215,0,0.6),
        0 0 45px rgba(255,215,0,0.4);
      display: inline-block;
      animation: starPulse 2.5s ease-in-out infinite;
    }
    @keyframes starPulse {
      0%, 100% { text-shadow: 0 0 10px rgba(255,215,0,0.9), 0 0 25px rgba(255,215,0,0.6); }
      50%       { text-shadow: 0 0 20px rgba(255,215,0,1),   0 0 50px rgba(255,215,0,0.9), 0 0 80px rgba(255,215,0,0.5); }
    }
    #JumlahUlasan { color: rgba(255,255,255,0.55); font-size: 0.9em; margin-bottom: 6px; }
    #JamBuka { color: rgba(255,255,255,0.65); font-size: 0.9em; margin-bottom: 50px; }
    #Testimoni {
      background: rgba(255,255,255,0.06) !important;
      border: 1px solid rgba(255,255,255,0.1);
      backdrop-filter: blur(10px);
    }
    #Testimoni h3 { color: var(--gold); text-align: center; margin-bottom: 28px; font-size: 1.2em; letter-spacing: 0.5px; }
    .testimonial {
      background: rgba(255,255,255,0.07) !important;
      border: 1px solid rgba(255,255,255,0.08);
      color: rgba(255,255,255,0.88) !important;
      padding: 28px 30px;
      border-radius: 18px;
      font-style: italic;
      margin-bottom: 16px;
      line-height: 1.8;
      position: relative;
      transition: transform 0.3s, box-shadow 0.3s, border-color 0.3s;
    }
    .testimonial::before {
      content: '"';
      position: absolute;
      top: -12px; left: 20px;
      font-size: 4em;
      color: var(--gold);
      opacity: 0.35;
      font-style: normal;
      line-height: 1;
    }
    .testimonial:hover {
      transform: translateY(-4px) scale(1.01);
      box-shadow: 0 15px 40px rgba(0,0,0,0.3);
      border-color: rgba(212,175,55,0.3);
    }
    #Ref { color: var(--gold); text-align: center; margin-top: 28px; font-size: 0.85em; letter-spacing: 2px; opacity: 0.75; }

    /* ---- DARK MODE ---- */
    body.dark #gallery-section {
      background: linear-gradient(160deg, #1a1020 0%, #120f1a 50%, #1a1208 100%) !important;
    }
    body.dark #gallery-section::before {
      background-image: radial-gradient(circle, rgba(212,175,55,0.04) 1px, transparent 1px);
    }
    body.dark .gallery-tabs-header h2 { color: #f0e8d0; }
    body.dark .gallery-tabs-header .eyebrow { color: rgba(212,175,55,0.8); }
    body.dark .gallery-tabs-header .subtitle-row { color: #777; }
    body.dark .photo-card { box-shadow: 0 8px 30px rgba(0,0,0,0.5); }

    /* ---- RESPONSIVE ---- */
    @media (max-width: 1100px) {
      .gallery-mosaic {
        grid-template-columns: repeat(6, 1fr);
        grid-auto-rows: 100px;
        padding: 0 24px;
        gap: 10px;
      }
      .photo-card            { grid-column: span 3; grid-row: span 3; }
      .photo-card.tall       { grid-column: span 2; grid-row: span 4; }
      .photo-card.wide       { grid-column: span 6; grid-row: span 3; }
      .photo-card.wide-tall  { grid-column: span 4; grid-row: span 4; }
      .photo-card.small      { grid-column: span 2; grid-row: span 3; }
      .photo-card.hero-wide  { grid-column: span 6; grid-row: span 3; }
      .photo-card.sq         { grid-column: span 3; grid-row: span 3; }
    }

    @media (max-width: 700px) {
      .gallery-mosaic {
        grid-template-columns: repeat(4, 1fr);
        grid-auto-rows: 100px;
        gap: 8px;
        padding: 0 16px;
      }
      .photo-card,
      .photo-card.tall,
      .photo-card.sq,
      .photo-card.small     { grid-column: span 2; grid-row: span 2; }
      .photo-card.wide,
      .photo-card.wide-tall,
      .photo-card.hero-wide { grid-column: span 4; grid-row: span 2; }
      #gallery-section { padding-bottom: 50px; }
      #Rating { padding: 60px 20px; }
      .gallery-hero { height: 300px; }
      .gallery-tabs-header { padding: 28px 16px 0; }
      .gallery-filter-inner {
        border-radius: 20px;
        padding: 8px;
        gap: 4px;
      }
      .filter-btn { padding: 8px 14px; font-size: 0.8em; }
      .filter-btn .tab-count { display: none; }
    }

    @keyframes cardReveal {
      from { opacity: 0; transform: translateY(30px) scale(0.97); }
      to   { opacity: 1; transform: translateY(0) scale(1); }
    }

  </style>
</head>
<body>

<!-- NAVBAR -->
<nav>
  <div class="nav-left">
    <img src="assets/img/Yolazcake.png" alt="YOLAZCAKE Logo">
    <h2>YOLAZCAKE</h2>
  </div>
  <ul class="main-nav">
    <li onclick="window.location.href='index.php'">Home</li>
    <li onclick="window.location.href='produk/menu.php'">Menu</li>
    <li class="active" onclick="window.location.href='gallery.php'">Gallery</li>
    <li onclick="window.location.href='about.php'">About</li>
    <li onclick="window.location.href='contact.php'">Contact</li>
  </ul>
  <div class="nav-right">
<?php if(isset($_SESSION['username'])){ ?>
<div class="account-dropdown">
<button class="account-btn">
👤 <?php echo htmlspecialchars($_SESSION['username']); ?> ▼
</button>
<div class="account-menu">
<a href="<?php echo (isset($_SESSION['role']) && $_SESSION['role']==='admin') ? 'dashboard.php' : 'member/member.php'; ?>"><?php echo (isset($_SESSION['role']) && $_SESSION['role']==='admin') ? 'Dashboard' : 'Member'; ?></a>
<a href="auth/logout.php">Logout</a>
</div>
</div>
<?php } else { ?>
<button class="login-btn" onclick="window.location.href='auth/login.php'">Login</button>
<?php } ?>
<div class="hamburger" onclick="toggleMenu()" id="hamburger">☰</div>
<div class="dark-btn" onclick="toggleDark()">🌙</div>
  </div>
  <div class="dropdown" id="dropdown">
    <p onclick="window.location.href='about.php#story'">Back Story</p>
    <p onclick="window.location.href='produk/menu.php#Product'">Featured Product</p>
    <p onclick="window.location.href='produk/menu.php#promo'">Promo</p>
    <p onclick="window.location.href='gallery.php#Rating'">Rating</p>
    <p onclick="window.location.href='gallery.php#gallery-section'">Gallery</p>
    <p onclick="window.location.href='about.php#team'">Team</p>
    <p onclick="window.location.href='contact.php#location'">Location</p>
    <p onclick="window.location.href='contact.php#contact'">Contact & Sosmed</p>
  </div>
</nav>

<!-- HERO BANNER -->
<div class="gallery-hero">
  <div class="sparkle-wrap" id="sparkleWrap"></div>
  <div class="gallery-hero-content">
    <p class="gallery-hero-eyebrow">✦ Koleksi Visual Kami ✦</p>
    <h1>Galeri <span>YOLAZCAKE</span></h1>
    <p style="font-family:'Inter',sans-serif;color:rgba(255,255,255,0.75);margin-top:14px;font-size:1em;opacity:0;animation:fadeInDown 0.9s forwards 0.9s;">Setiap sudut menyimpan cerita rasa &amp; keindahan 📸</p>
    <div class="gallery-hero-divider">
      <span></span>
      <span class="hero-diamond">✦ ✦ ✦</span>
      <span></span>
    </div>
  </div>
  <div class="scroll-hint">
    <div class="scroll-hint-arrows">
      <span></span><span></span><span></span>
    </div>
  </div>
</div>

<!-- ============================================================
     UNIFIED GALLERY SECTION — filter tabs + mosaic = one block
============================================================ -->
<section id="gallery-section">

  <!-- Floating ambient orbs -->
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="orb orb-3"></div>
  <!-- Gold shimmer stripe -->
  <div class="section-shine"></div>

  <!-- Compact header + tabs in one tight block -->
  <div class="gallery-tabs-header fade">
    <p class="eyebrow">✦ Koleksi Visual ✦</p>
    <h2>Galeri YOLAZCAKE</h2>
    <div class="subtitle-row">Sintang, Kalimantan Barat</div>
  </div>

  <!-- Filter tabs — glass pill strip (count dari DB) -->
  <div class="gallery-filter" id="galleryFilter">
    <div class="gallery-filter-inner">
      <button class="filter-btn active" data-filter="all">Semua <span class="tab-count"><?= $counts['all'] ?></span></button>
      <button class="filter-btn" data-filter="interior">Interior <span class="tab-count"><?= $counts['interior'] ?></span></button>
      <button class="filter-btn" data-filter="kue">Kue &amp; Pastry <span class="tab-count"><?= $counts['kue'] ?></span></button>
      <button class="filter-btn" data-filter="coffee">Coffee <span class="tab-count"><?= $counts['coffee'] ?></span></button>
      <button class="filter-btn" data-filter="boutique">Boutique <span class="tab-count"><?= $counts['boutique'] ?></span></button>
    </div>
  </div>

  <!-- Galeri dari Database -->
  <div class="gallery-mosaic" id="galleryGrid">

    <?php if (empty($galeri_all)): ?>
    <div style="grid-column:1/-1;text-align:center;padding:80px 20px;color:rgba(255,255,255,.4);">
      <div style="font-size:3em;margin-bottom:12px;">🖼️</div>
      <p>Belum ada foto di galeri. Tambahkan melalui panel admin.</p>
      <?php if(isset($_SESSION['username'])): ?>
      <a href="galeri/tambah_galeri.php" style="display:inline-block;margin-top:16px;padding:10px 24px;background:rgba(212,175,55,.2);border:1px solid rgba(212,175,55,.4);color:#D4AF37;border-radius:999px;text-decoration:none;font-size:.9em;">+ Tambah Foto</a>
      <?php endif; ?>
    </div>
    <?php else: ?>
    <?php
      $size_classes = ['wide-tall','tall','sq','small','wide','sq','small','tall','sq','sq','wide','sq'];
      foreach ($galeri_all as $i => $g):
        $sz = $size_classes[$i % count($size_classes)];
        $img_src = 'assets/img/galeri/' . htmlspecialchars($g['foto']);
        $fallback = 'assets/img/image.png';
    ?>
    <div class="photo-card <?= $sz ?> fade" data-category="<?= htmlspecialchars($g['kategori']) ?>"
         data-title="<?= htmlspecialchars($g['judul']) ?>"
         data-desc="<?= htmlspecialchars($g['deskripsi'] ?? '') ?>">
      <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($g['judul']) ?>" loading="lazy"
           onerror="this.src='<?= $fallback ?>'">
      <div class="card-overlay"></div>
      <div class="card-badge"><?= ucfirst(htmlspecialchars($g['kategori'])) ?></div>
      <div class="card-label">
        <h3><?= htmlspecialchars($g['judul']) ?></h3>
        <p><?= htmlspecialchars($g['deskripsi'] ?? '') ?></p>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

  </div><!-- /gallery-mosaic -->

</section>

<!-- RATING -->
<section id="Rating">
  <h2>Rating &amp; Testimoni</h2>
  <div id="Ulasan">
    <h3 id="Stars">4,8 <span class="rating-stars">★★★★★</span></h3>
    <p id="JumlahUlasan">(26 ulasan)</p>
    <p><strong style="color:var(--gold)">Toko Roti • Rp 25.000 – 50.000</strong></p>
    <p id="JamBuka">Buka setiap hari • Tutup pukul 22.00 WIB</p>
    <div class="card fade" id="Testimoni">
      <h3>Apa kata pelanggan kami?</h3>
      <div class="testimonial fade">"Kuenya mantap banget, harganya murah meriah, dan matcha-nya enak!"</div>
      <div class="testimonial fade">"Tempatnya cozy banget! Kuenya enak, kopinya mantap, dan bisa langsung belanja baju di atas. Recomended!"</div>
      <div class="testimonial fade">"Dessertnya selalu fresh. Boutique-nya juga aesthetic, cocok buat cewek-cewek yang suka foto-foto."</div>
      <div class="testimonial fade">"Satu-satunya tempat di Sintang yang bisa makan enak sambil belanja fashion. Pelayanannya ramah!"</div>
      <p id="Ref">— Dari Google Review</p>
    </div>
  </div>
</section>

<!-- FOOTER -->
<div class="footer">
  © 2026 YOLAZCAKE Sintang • Cafe • Bakery • Boutique
  <br>
  Jl. Lintas Melawi, Ladang, Sintang, Kalimantan Barat
  <br>
  WA: 0815-7815-7888
</div>

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox">
  <div class="lightbox-overlay" onclick="closeLightbox()"></div>
  <button class="lightbox-arrow prev" onclick="prevPhoto()">&#8592;</button>
  <div class="lightbox-content">
    <img id="lightboxImg" src="" alt="">
    <div class="lightbox-caption">
      <h3 id="lightboxTitle"></h3>
      <p id="lightboxDesc"></p>
    </div>
  </div>
  <button class="lightbox-arrow next" onclick="nextPhoto()">&#8594;</button>
  <button class="lightbox-close" onclick="closeLightbox()">✕</button>
</div>

<script src="js/style.js"></script>
<script>
  /* ---- SPARKLES IN HERO ---- */
  (function(){
    const wrap = document.getElementById('sparkleWrap');
    for(let i = 0; i < 22; i++){
      const s = document.createElement('div');
      s.className = 'sparkle';
      const size = Math.random() * 6 + 3;
      s.style.cssText = `
        width:${size}px; height:${size}px;
        top:${Math.random()*100}%;
        left:${Math.random()*100}%;
        animation-delay:${Math.random()*4}s;
        animation-duration:${2 + Math.random()*3}s;
      `;
      wrap.appendChild(s);
    }
  })();

  /* ---- FILTER TABS ---- */
  const filterBtns  = document.querySelectorAll('.filter-btn');
  const photoCards  = document.querySelectorAll('.photo-card');

  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const filter = btn.dataset.filter;
      let delay = 0;
      photoCards.forEach(card => {
        const show = filter === 'all' || card.dataset.category === filter;
        card.style.transition = 'opacity 0.4s, transform 0.4s';
        if(show){
          setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = '';
            card.style.pointerEvents = '';
          }, delay);
          delay += 60;
        } else {
          card.style.opacity = '0';
          card.style.transform = 'scale(0.88)';
          card.style.pointerEvents = 'none';
        }
      });
    });
  });

  /* ---- LIGHTBOX ---- */
  let currentIndex = 0;
  let visibleCards = [];

  function getVisibleCards(){
    return Array.from(photoCards).filter(c => c.style.pointerEvents !== 'none');
  }

  function openLightbox(card){
    visibleCards = getVisibleCards();
    currentIndex = visibleCards.indexOf(card);
    showPhoto(currentIndex);
    document.getElementById('lightbox').classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function showPhoto(idx){
    const card = visibleCards[idx];
    if(!card) return;
    const img   = card.querySelector('img');
    document.getElementById('lightboxImg').src            = img.src;
    document.getElementById('lightboxTitle').textContent  = card.dataset.title;
    document.getElementById('lightboxDesc').textContent   = card.dataset.desc;
    currentIndex = idx;
  }

  function closeLightbox(){
    document.getElementById('lightbox').classList.remove('active');
    document.body.style.overflow = '';
  }

  function prevPhoto(){
    showPhoto((currentIndex - 1 + visibleCards.length) % visibleCards.length);
  }

  function nextPhoto(){
    showPhoto((currentIndex + 1) % visibleCards.length);
  }

  photoCards.forEach(card => card.addEventListener('click', () => openLightbox(card)));

  document.addEventListener('keydown', e => {
    if(!document.getElementById('lightbox').classList.contains('active')) return;
    if(e.key === 'Escape')     closeLightbox();
    if(e.key === 'ArrowLeft')  prevPhoto();
    if(e.key === 'ArrowRight') nextPhoto();
  });

  /* ---- SCROLL REVEAL (same as style.js .fade → .show) ---- */
  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
      if(entry.isIntersecting){
        setTimeout(() => {
          entry.target.classList.add('show');
        }, i * 80);
        revealObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

  document.querySelectorAll('.photo-card').forEach(card => revealObserver.observe(card));

</script>

<?php include 'status_fab.php'; ?>

</body>
</html>
