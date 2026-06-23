<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menu - YOLAZCAKE Sintang</title>
  <link rel="stylesheet" href="../css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

  <style>

    /* ===== PREMIUM HERO BANNER ===== */
    .menu-hero {
      position: relative;
      height: 360px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      background: linear-gradient(135deg, #2b1a11 0%, #4a2c1a 40%, #6d3e26 70%, #3a1f0e 100%);
    }

    .menu-hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background:
        radial-gradient(ellipse at 30% 50%, rgba(212,175,55,0.18) 0%, transparent 60%),
        radial-gradient(ellipse at 75% 40%, rgba(232,160,191,0.15) 0%, transparent 55%);
      animation: heroAurora 8s ease-in-out infinite alternate;
    }

    @keyframes heroAurora {
      0%   { opacity: 0.6; transform: scale(1) translateX(0); }
      100% { opacity: 1;   transform: scale(1.08) translateX(10px); }
    }

    .menu-hero .sparkle {
      position: absolute;
      border-radius: 50%;
      pointer-events: none;
      animation: floatDot linear infinite;
    }

    @keyframes floatDot {
      0%   { transform: translateY(0)   rotate(0deg);   opacity: 0; }
      20%  { opacity: 1; }
      80%  { opacity: 0.8; }
      100% { transform: translateY(-360px) rotate(360deg); opacity: 0; }
    }

    .menu-hero-inner {
      position: relative;
      z-index: 2;
      text-align: center;
      color: #fff;
    }

    .menu-hero-inner .hero-eyebrow {
      font-family: 'Inter', sans-serif;
      font-size: 0.78em;
      font-weight: 500;
      letter-spacing: 5px;
      text-transform: uppercase;
      color: #D4AF37;
      margin-bottom: 12px;
      opacity: 0;
      animation: fadeSlideDown 0.8s forwards 0.3s;
    }

    .menu-hero-inner h1 {
      font-family: 'Playfair Display', serif;
      font-size: 3.6em;
      font-weight: 700;
      line-height: 1.1;
      background: linear-gradient(135deg, #fff 30%, #D4AF37 60%, #FFE4B5 80%, #fff);
      background-size: 200% 100%;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      animation: shimmerText 4s ease-in-out infinite, fadeSlideDown 0.9s forwards 0.5s;
      opacity: 0;
    }

    @keyframes shimmerText {
      0%   { background-position: 100% 0; }
      100% { background-position: -100% 0; }
    }

    .menu-hero-inner .hero-sub {
      font-family: 'Inter', sans-serif;
      font-size: 1em;
      color: rgba(255,255,255,0.75);
      margin-top: 14px;
      opacity: 0;
      animation: fadeSlideDown 0.9s forwards 0.9s;
    }

    .hero-divider {
      position: relative;
      z-index: 2;
      margin-top: 22px;
      display: flex;
      justify-content: center;
      align-items: center;
      width: 100%;
      gap: 12px;
      opacity: 0;
      animation: fadeSlideDown 0.9s forwards 1.1s;
    }

    .hero-divider span {
      display: block;
      width: 80px;
      height: 1px;
      background: linear-gradient(to right, transparent, #D4AF37);
    }

    .hero-divider span:last-child {
      background: linear-gradient(to left, transparent, #D4AF37);
    }

    .hero-divider .diamond {
      color: #D4AF37;
      font-size: 0.75em;
      letter-spacing: 4px;
    }

    @keyframes fadeSlideDown {
      from { opacity: 0; transform: translateY(-18px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* ===== SECTION LABEL (sama persis seperti about) ===== */
    .section-label {
      display: flex;
      flex-direction: column;
      align-items: center;
      margin-bottom: 56px;
      gap: 8px;
    }

    .section-label .eyebrow {
      font-family: 'Inter', sans-serif;
      font-size: 0.72em;
      letter-spacing: 5px;
      text-transform: uppercase;
      color: #D4AF37;
      font-weight: 600;
    }

    .section-label h2 {
      font-family: 'Playfair Display', serif;
      font-size: 2.6em;
      font-weight: 700;
      margin: 0;
      text-align: center;
      color: var(--brown);
    }

    .section-label .gold-rule {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-top: 4px;
    }

    .section-label .gold-rule::before,
    .section-label .gold-rule::after {
      content: '';
      display: block;
      width: 50px;
      height: 1px;
      background: linear-gradient(to right, transparent, #D4AF37);
    }

    .section-label .gold-rule::after {
      background: linear-gradient(to left, transparent, #D4AF37);
    }

    .section-label .gold-rule span {
      font-size: 0.6em;
      color: #D4AF37;
      letter-spacing: 3px;
    }

    /* ===== SHARED KEYFRAMES ===== */
    @keyframes goldSlide {
      0%   { background-position: 0% 0; }
      100% { background-position: 200% 0; }
    }

    @keyframes cardReveal {
      to { opacity: 1; transform: translateY(0); }
    }

    /* ===== SECTION #menu — HIGHLIGHTS ===== */
    #menu {
      padding: 80px 40px;
      background: linear-gradient(160deg, #EEF6FF 0%, #DFF0FF 100%);
      position: relative;
      overflow: hidden;
    }

    #menu::before {
      content: '';
      position: absolute;
      top: -80px; right: -80px;
      width: 320px; height: 320px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(212,175,55,0.12) 0%, transparent 70%);
      pointer-events: none;
    }

    /* Override default h2 dari style.css */
    #menu > .section-label h2,
    #Product > .section-label h2 {
      color: var(--brown);
      background: none;
      -webkit-text-fill-color: unset;
      background-clip: unset;
    }

    /* Menu grid premium */
    .menu-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 28px;
      max-width: 1100px;
      margin: 0 auto;
    }

    /* Premium menu card */
    .menu-card {
      border-radius: 24px;
      overflow: hidden;
      background: #fff;
      box-shadow: 0 8px 30px rgba(0,0,0,0.09);
      opacity: 0;
      transform: translateY(30px);
      transition: transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1),
                  box-shadow 0.45s ease;
      position: relative;
    }

    .menu-card.show {
      animation: cardReveal 0.7s cubic-bezier(.22,.68,0,1.2) forwards;
    }

    .menu-card:hover {
      transform: translateY(-10px) scale(1.03);
      box-shadow:
        0 25px 60px rgba(109,76,65,0.25),
        0 0 30px rgba(232,160,191,0.5),
        0 0 60px rgba(212,175,55,0.25),
        0 0 90px rgba(212,175,55,0.1);
    }

    .menu-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 4px;
      background: linear-gradient(90deg, #D4AF37, #6d3e26, #D4AF37);
      background-size: 200% 100%;
      animation: goldSlide 3s linear infinite;
      z-index: 5;
    }

    .menu-card-photo {
      width: 100%;
      height: 220px;
      background-size: cover;
      background-position: center;
      position: relative;
      overflow: hidden;
    }

    /* Shimmer hanya pada area foto (static single image - disabled when carousel active) */
    .menu-card-photo::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 55%;
      height: 100%;
      background: linear-gradient(
        120deg,
        transparent,
        rgba(255,255,255,0.55),
        transparent
      );
      transform: skewX(-20deg);
      transition: left 0.75s ease;
      pointer-events: none;
      z-index: 3;
    }

    .menu-card:hover .menu-card-photo::before {
      left: 130%;
    }



    .menu-card-photo::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(to bottom, transparent 50%, rgba(43,26,17,0.65) 100%);
    }

    .menu-card-badge {
      position: absolute;
      bottom: 14px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 2;
      background: rgba(212,175,55,0.88);
      color: #2b1a11;
      font-family: 'Inter', sans-serif;
      font-size: 0.65em;
      font-weight: 700;
      letter-spacing: 1.5px;
      padding: 4px 14px;
      border-radius: 999px;
      text-transform: uppercase;
      white-space: nowrap;
      backdrop-filter: blur(4px);
    }

    .menu-card-body {
      padding: 22px 24px 24px;
      position: relative;
    }

    .menu-card-body::before {
      content: '';
      position: absolute;
      top: 0; left: 24px; right: 24px;
      height: 1.5px;
      background: linear-gradient(to right, transparent, #D4AF37, transparent);
    }

    .menu-card-body h3 {
      font-family: 'Playfair Display', serif;
      font-size: 1.2em;
      color: var(--brown);
      margin-bottom: 8px;
      padding: 0;
    }

    .menu-card-body p {
      font-family: 'Inter', sans-serif;
      font-size: 0.88em;
      color: #666;
      padding: 0;
      line-height: 1.6;
    }

    /* ===== MENU CARD CAROUSEL (identik dengan product-card carousel) ===== */

    /* Carousel track untuk menu-card */
    .menu-card-photo .photo-carousel {
      display: flex;
      width: 100%;
      height: 100%;
      transition: transform 0.55s cubic-bezier(0.4, 0, 0.2, 1);
      will-change: transform;
    }

    .menu-card-photo .photo-carousel-slide {
      min-width: 100%;
      height: 220px;
      background-size: cover;
      background-position: center;
      flex-shrink: 0;
      position: relative;
    }

    /* Shimmer overlay untuk menu carousel */
    .menu-carousel-shimmer {
      position: absolute;
      top: 0;
      left: -100%;
      width: 55%;
      height: 100%;
      background: linear-gradient(
        120deg,
        transparent,
        rgba(255,255,255,0.55),
        transparent
      );
      transform: skewX(-20deg);
      transition: left 0.75s ease;
      pointer-events: none;
      z-index: 3;
    }

    .menu-card:hover .menu-carousel-shimmer {
      left: 130%;
    }

    /* Nav arrows untuk menu carousel */
    .menu-card .carousel-btn {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      z-index: 10;
      background: rgba(43, 26, 17, 0.55);
      backdrop-filter: blur(6px);
      border: 1px solid rgba(212,175,55,0.55);
      color: #D4AF37;
      width: 34px;
      height: 34px;
      border-radius: 50%;
      font-size: 1em;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.25s, box-shadow 0.25s, transform 0.25s;
      box-shadow: 0 2px 12px rgba(0,0,0,0.35);
      opacity: 0;
      pointer-events: none;
      line-height: 1;
      padding: 0;
    }

    .menu-card:hover .carousel-btn {
      opacity: 1;
      pointer-events: all;
    }

    .menu-card .carousel-btn:hover {
      background: rgba(212,175,55,0.25);
      box-shadow: 0 0 18px rgba(212,175,55,0.6), 0 0 40px rgba(212,175,55,0.2);
      transform: translateY(-50%) scale(1.12);
    }

    .menu-card .carousel-btn.prev { left: 10px; }
    .menu-card .carousel-btn.next { right: 10px; }

    /* Dots untuk menu carousel */
    .menu-card .carousel-dots {
      position: absolute;
      bottom: 44px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 10;
      display: flex;
      gap: 5px;
    }

    .menu-card .carousel-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: rgba(255,255,255,0.45);
      border: 1px solid rgba(212,175,55,0.5);
      cursor: pointer;
      transition: background 0.3s, transform 0.3s, box-shadow 0.3s;
    }

    .menu-card .carousel-dot.active {
      background: #D4AF37;
      transform: scale(1.45);
      box-shadow: 0 0 8px rgba(212,175,55,0.8), 0 0 20px rgba(212,175,55,0.4);
    }

    /* Progress bar untuk menu carousel */
    .menu-card .carousel-progress {
      position: absolute;
      bottom: 0;
      left: 0;
      height: 3px;
      background: linear-gradient(90deg, #D4AF37, #6d3e26, #D4AF37);
      background-size: 200% 100%;
      animation: goldSlide 2s linear infinite;
      z-index: 10;
      transition: width 0.55s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Counter badge untuk menu carousel */
    .menu-card .carousel-counter {
      position: absolute;
      top: 10px;
      right: 10px;
      z-index: 10;
      background: rgba(43,26,17,0.65);
      backdrop-filter: blur(6px);
      border: 1px solid rgba(212,175,55,0.4);
      color: #D4AF37;
      font-family: 'Inter', sans-serif;
      font-size: 0.68em;
      font-weight: 600;
      padding: 3px 10px;
      border-radius: 999px;
      letter-spacing: 1px;
      opacity: 0;
      transition: opacity 0.3s;
    }

    .menu-card:hover .carousel-counter {
      opacity: 1;
    }

    /* ===== SECTION #Product — DARK PURPLE (seperti #story di about) ===== */
    #Product {
      padding: 80px 40px;
      background: linear-gradient(160deg, #1e0e3a 0%, #2d1560 50%, #1a0a2e 100%);
      position: relative;
      overflow: hidden;
    }

    #Product::before {
      content: '';
      position: absolute;
      inset: 0;
      background:
        radial-gradient(ellipse at 20% 30%, rgba(212,175,55,0.12) 0%, transparent 55%),
        radial-gradient(ellipse at 80% 70%, rgba(232,160,191,0.10) 0%, transparent 55%);
      pointer-events: none;
    }

    #Product .section-label h2 {
      background: linear-gradient(135deg, #fff 30%, #D4AF37 70%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    #Product .section-label .eyebrow {
      color: rgba(212,175,55,0.85);
    }

    /* Product grid — premium cards on dark bg */
    .product-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 28px;
      max-width: 1100px;
      margin: 0 auto;
      position: relative;
      z-index: 2;
    }

    .product-card {
      border-radius: 28px;
      overflow: hidden;
      background: rgba(255,255,255,0.06);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255,255,255,0.1);
      opacity: 0;
      transform: translateY(30px);
      transition: transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1),
                  box-shadow 0.45s ease,
                  border-color 0.45s ease;
      position: relative;
    }

    .product-card.show {
      animation: cardReveal 0.75s cubic-bezier(.22,.68,0,1.2) forwards;
    }

    .product-card:hover {
      transform: translateY(-12px) scale(1.03);
      border-color: rgba(212,175,55,0.5);
      box-shadow:
        0 24px 65px rgba(0,0,0,0.4),
        0 0 35px rgba(212,175,55,0.42),
        0 0 70px rgba(212,175,55,0.2),
        0 0 110px rgba(212,175,55,0.08);
    }

    .product-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 3px;
      background: linear-gradient(90deg, #D4AF37, #ee2a7b, #D4AF37);
      background-size: 200% 100%;
      animation: goldSlide 4s linear infinite;
      z-index: 5;
    }

    .product-card-photo {
      width: 100%;
      height: 200px;
      background-size: cover;
      background-position: center;
      position: relative;
      overflow: hidden;
    }

    /* Shimmer overlay for product carousel (replaces ::before on card-photo) */
    .carousel-shimmer {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 55%;
      height: 100%;
      background: linear-gradient(
        120deg,
        transparent,
        rgba(255,255,255,0.4),
        transparent
      );
      transform: skewX(-20deg);
      transition: left 0.75s ease;
      pointer-events: none;
      z-index: 3;
    }

    .product-card:hover .carousel-shimmer {
      left: 130%;
    }

    /* Shimmer hanya pada area foto product — disabled (using .carousel-shimmer div instead) */
    .product-card-photo::before {
      display: none;
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 55%;
      height: 100%;
      background: linear-gradient(
        120deg,
        transparent,
        rgba(255,255,255,0.4),
        transparent
      );
      transform: skewX(-20deg);
      transition: left 0.75s ease;
      pointer-events: none;
      z-index: 3;
    }

    .product-card:hover .product-card-photo::before {
      left: 130%;
    }



    .product-card-photo::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(to bottom, transparent 40%, rgba(15,5,30,0.7) 100%);
    }

    .product-card-badge {
      position: absolute;
      bottom: 14px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 2;
      background: rgba(212,175,55,0.85);
      color: #2b1a11;
      font-family: 'Inter', sans-serif;
      font-size: 0.65em;
      font-weight: 700;
      letter-spacing: 1.5px;
      padding: 4px 14px;
      border-radius: 999px;
      text-transform: uppercase;
      white-space: nowrap;
      backdrop-filter: blur(4px);
    }

    .product-card-body {
      padding: 22px 24px 24px;
      position: relative;
    }

    .product-card-body::before {
      content: '';
      position: absolute;
      top: 0; left: 24px; right: 24px;
      height: 1px;
      background: linear-gradient(to right, transparent, rgba(212,175,55,0.5), transparent);
    }

    .product-card-body h3 {
      font-family: 'Playfair Display', serif;
      font-size: 1.2em;
      color: #fff;
      margin-bottom: 8px;
      padding: 0;
    }

    .product-card-body p {
      font-family: 'Inter', sans-serif;
      font-size: 0.88em;
      color: rgba(255,255,255,0.7);
      padding: 0;
      line-height: 1.6;
    }

    .product-tag-row {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      margin-top: 14px;
    }

    .product-tag {
      background: linear-gradient(135deg, rgba(212,175,55,0.2), rgba(212,175,55,0.08));
      border: 1px solid rgba(212,175,55,0.4);
      color: #D4AF37;
      font-family: 'Inter', sans-serif;
      font-size: 0.72em;
      font-weight: 600;
      padding: 4px 12px;
      border-radius: 999px;
      letter-spacing: 0.5px;
    }

    /* ===== PRODUCT CARD PHOTO SWIPE CAROUSEL ===== */
    .product-card-photo {
      position: relative;
      overflow: hidden;
    }

    /* Carousel track */
    .photo-carousel {
      display: flex;
      width: 100%;
      height: 100%;
      transition: transform 0.55s cubic-bezier(0.4, 0, 0.2, 1);
      will-change: transform;
    }

    .photo-carousel-slide {
      min-width: 100%;
      height: 200px;
      background-size: cover;
      background-position: center;
      flex-shrink: 0;
      position: relative;
    }

    /* Carousel Nav Arrows */
    .carousel-btn {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      z-index: 10;
      background: rgba(15, 5, 30, 0.55);
      backdrop-filter: blur(6px);
      border: 1px solid rgba(212,175,55,0.55);
      color: #D4AF37;
      width: 34px;
      height: 34px;
      border-radius: 50%;
      font-size: 1em;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.25s, box-shadow 0.25s, transform 0.25s;
      box-shadow: 0 2px 12px rgba(0,0,0,0.35);
      opacity: 0;
      pointer-events: none;
      line-height: 1;
      padding: 0;
    }

    .product-card:hover .carousel-btn {
      opacity: 1;
      pointer-events: all;
    }

    .carousel-btn:hover {
      background: rgba(212,175,55,0.25);
      box-shadow: 0 0 18px rgba(212,175,55,0.6), 0 0 40px rgba(212,175,55,0.2);
      transform: translateY(-50%) scale(1.12);
    }

    .carousel-btn.prev { left: 10px; }
    .carousel-btn.next { right: 10px; }

    /* Dot indicators */
    .carousel-dots {
      position: absolute;
      bottom: 44px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 10;
      display: flex;
      gap: 5px;
    }

    .carousel-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: rgba(255,255,255,0.45);
      border: 1px solid rgba(212,175,55,0.5);
      cursor: pointer;
      transition: background 0.3s, transform 0.3s, box-shadow 0.3s;
    }

    .carousel-dot.active {
      background: #D4AF37;
      transform: scale(1.45);
      box-shadow: 0 0 8px rgba(212,175,55,0.8), 0 0 20px rgba(212,175,55,0.4);
    }

    /* Progress bar */
    .carousel-progress {
      position: absolute;
      bottom: 0;
      left: 0;
      height: 3px;
      background: linear-gradient(90deg, #D4AF37, #ee2a7b, #D4AF37);
      background-size: 200% 100%;
      animation: goldSlide 2s linear infinite;
      z-index: 10;
      transition: width 0.55s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Slide counter badge */
    .carousel-counter {
      position: absolute;
      top: 10px;
      right: 10px;
      z-index: 10;
      background: rgba(15,5,30,0.65);
      backdrop-filter: blur(6px);
      border: 1px solid rgba(212,175,55,0.4);
      color: #D4AF37;
      font-family: 'Inter', sans-serif;
      font-size: 0.68em;
      font-weight: 600;
      padding: 3px 10px;
      border-radius: 999px;
      letter-spacing: 1px;
      opacity: 0;
      transition: opacity 0.3s;
    }

    .product-card:hover .carousel-counter {
      opacity: 1;
    }

    /* Touch swipe hint animation on first load */
    @keyframes swipeHint {
      0%   { transform: translateX(0); opacity: 0.6; }
      30%  { transform: translateX(-10px); opacity: 1; }
      60%  { transform: translateX(0); opacity: 0.6; }
      100% { transform: translateX(0); opacity: 0; }
    }

    .swipe-hint {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 12;
      color: rgba(255,255,255,0.9);
      font-size: 1.3em;
      pointer-events: none;
      animation: swipeHint 2s ease-out forwards;
      animation-delay: 1.2s;
      opacity: 0;
      text-shadow: 0 0 12px rgba(212,175,55,0.8);
    }

    /* Floating particles */
    .particle {
      position: absolute;
      border-radius: 50%;
      pointer-events: none;
      animation: particleFloat linear infinite;
    }

    @keyframes particleFloat {
      0%   { transform: translateY(100vh) scale(0); opacity: 0; }
      10%  { opacity: 0.6; }
      90%  { opacity: 0.4; }
      100% { transform: translateY(-100px) scale(1); opacity: 0; }
    }

    /* ===== SECTION #promo — PREMIUM GLASSMORPHISM ===== */
    #promo {
      padding: 80px 40px;
      background: linear-gradient(160deg, #EEF6FF 0%, #DFF0FF 100%);
      position: relative;
      overflow: hidden;
    }

    #promo::before {
      content: '';
      position: absolute;
      bottom: -60px; left: -60px;
      width: 280px; height: 280px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(212,175,55,0.1) 0%, transparent 70%);
      pointer-events: none;
    }

    #promo .section-label h2 {
      color: var(--brown);
    }

    .promo-card-premium {
      max-width: 760px;
      margin: 0 auto;
      background: #fff;
      border-radius: 28px;
      padding: 44px 48px;
      box-shadow: 0 10px 50px rgba(109,76,65,0.12);
      position: relative;
      overflow: hidden;
      opacity: 0;
      transform: translateY(40px);
      text-align: center;
      cursor: default;
      transition: transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1),
                  box-shadow 0.45s ease;
    }

    .promo-card-premium:hover {
      transform: translateY(-10px) scale(1.02);
      box-shadow:
        0 25px 60px rgba(109,76,65,0.22),
        0 0 30px rgba(212,175,55,0.3),
        0 0 65px rgba(212,175,55,0.14),
        0 0 100px rgba(212,175,55,0.06);
    }

    .promo-card-premium.show {
      animation: cardReveal 0.8s cubic-bezier(.22,.68,0,1.2) forwards;
    }

    .promo-card-premium::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 4px;
      background: linear-gradient(90deg, #D4AF37, #6d3e26, #D4AF37);
      background-size: 200% 100%;
      animation: goldSlide 3s linear infinite;
    }

    .promo-icon {
      font-size: 3em;
      margin-bottom: 16px;
      display: block;
    }

    .promo-card-premium h3 {
      font-family: 'Playfair Display', serif;
      font-size: 1.7em;
      color: var(--brown);
      margin-bottom: 14px;
      padding: 0;
      line-height: 1.3;
    }

    .promo-card-premium p {
      font-family: 'Inter', sans-serif;
      font-size: 0.98em;
      color: #666;
      line-height: 1.75;
      padding: 0;
      max-width: 560px;
      margin: 0 auto;
    }

    .promo-badges {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 10px;
      margin: 24px 0 30px;
    }

    .promo-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: linear-gradient(135deg, rgba(212,175,55,0.15), rgba(212,175,55,0.05));
      border: 1px solid rgba(212,175,55,0.45);
      color: #6d3e26;
      font-family: 'Inter', sans-serif;
      font-size: 0.8em;
      font-weight: 600;
      letter-spacing: 0.5px;
      padding: 8px 16px;
      border-radius: 999px;
    }

    .promo-btn-premium {
      display: inline-block;
      font-family: 'Inter', sans-serif;
      font-size: 0.95em;
      font-weight: 600;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: #2b1a11;
      background: linear-gradient(135deg, #D4AF37 0%, #f5d060 50%, #D4AF37 100%);
      background-size: 200% 100%;
      padding: 14px 40px;
      border-radius: 999px;
      border: none;
      cursor: pointer;
      transition: transform 0.3s ease, box-shadow 0.3s ease, background-position 0.5s ease;
      box-shadow: 0 6px 24px rgba(212,175,55,0.4);
      text-decoration: none;
    }

    .promo-btn-premium:hover {
      transform: translateY(-3px) scale(1.04);
      box-shadow: 0 12px 36px rgba(212,175,55,0.55);
      background-position: 100% 0;
    }

    /* ===== DARK MODE ===== */
    body.dark #menu,
    body.dark #promo {
      background: rgba(18, 25, 40, 0.95) !important;
    }

    body.dark .menu-card {
      background: #1e1e2a;
      box-shadow: 0 8px 40px rgba(0,0,0,0.4);
    }

    body.dark .menu-card-body h3 { color: #e0c88a; }
    body.dark .menu-card-body p  { color: #aaa; }

    body.dark .promo-card-premium {
      background: #1e1e2a;
      box-shadow: 0 10px 50px rgba(0,0,0,0.5);
    }

    body.dark .promo-card-premium h3 { color: #e0c88a; }
    body.dark .promo-card-premium p  { color: #aaa; }
    body.dark .promo-badge { color: #D4AF37; background: rgba(212,175,55,0.1); }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
      .menu-hero-inner h1 { font-size: 2.4em; }
      .menu-hero { height: 300px; }
      #menu, #Product, #promo { padding: 60px 20px; }
      .promo-card-premium { padding: 30px 24px; }
      .menu-grid, .product-grid { grid-template-columns: 1fr; }
    }

/* ===== PREMIUM BOOKING SECTION ===== */
.booking-section {
  padding: 90px 40px;
  background: linear-gradient(160deg, #1e0e3a 0%, #2d1560 50%, #1a0a2e 100%);
  position: relative;
  overflow: hidden;
}

.booking-section::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(ellipse at 10% 20%, rgba(212,175,55,0.14) 0%, transparent 55%),
    radial-gradient(ellipse at 90% 80%, rgba(232,160,191,0.12) 0%, transparent 55%),
    radial-gradient(ellipse at 50% 50%, rgba(109,62,150,0.15) 0%, transparent 70%);
  pointer-events: none;
  animation: bookingAura 10s ease-in-out infinite alternate;
}

@keyframes bookingAura {
  0%   { opacity: 0.7; }
  100% { opacity: 1; }
}

.booking-particles {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 0;
}

.booking-inner {
  position: relative;
  z-index: 2;
  max-width: 1100px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  align-items: center;
}

.booking-left { color: #fff; }

.booking-left .eyebrow {
  font-family: 'Inter', sans-serif;
  font-size: 0.72em;
  letter-spacing: 5px;
  text-transform: uppercase;
  color: #D4AF37;
  font-weight: 600;
  display: block;
  margin-bottom: 16px;
  opacity: 0;
  animation: fadeSlideDown 0.8s forwards 0.2s;
}

.booking-left h2 {
  font-family: 'Playfair Display', serif;
  font-size: 2.7em;
  font-weight: 700;
  line-height: 1.15;
  margin: 0 0 20px;
  background: linear-gradient(135deg, #fff 30%, #D4AF37 65%, #FFE4B5 85%, #fff);
  background-size: 200% 100%;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  animation: shimmerText 4s ease-in-out infinite, fadeSlideDown 0.9s forwards 0.4s;
  opacity: 0;
}

.booking-left p {
  font-family: 'Inter', sans-serif;
  font-size: 0.97em;
  color: rgba(255,255,255,0.72);
  line-height: 1.85;
  margin-bottom: 32px;
  opacity: 0;
  animation: fadeSlideDown 0.9s forwards 0.6s;
}

.booking-features {
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin-bottom: 36px;
  opacity: 0;
  animation: fadeSlideDown 0.9s forwards 0.8s;
}

.booking-feature-item {
  display: flex;
  align-items: center;
  gap: 12px;
  font-family: 'Inter', sans-serif;
  font-size: 0.88em;
  color: rgba(255,255,255,0.82);
}

.booking-feature-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, rgba(212,175,55,0.22), rgba(212,175,55,0.08));
  border: 1px solid rgba(212,175,55,0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1em;
  flex-shrink: 0;
  box-shadow: 0 0 12px rgba(212,175,55,0.2);
}

.booking-cta-row {
  display: flex;
  align-items: center;
  gap: 20px;
  flex-wrap: wrap;
  opacity: 0;
  animation: fadeSlideDown 0.9s forwards 1s;
}

.booking-btn-main {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-family: 'Inter', sans-serif;
  font-size: 0.95em;
  font-weight: 700;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: #2b1a11;
  background: linear-gradient(135deg, #D4AF37 0%, #f5d060 50%, #D4AF37 100%);
  background-size: 200% 100%;
  padding: 16px 40px;
  border-radius: 999px;
  border: none;
  cursor: pointer;
  text-decoration: none;
  transition: transform 0.3s ease, box-shadow 0.3s ease, background-position 0.5s ease;
  box-shadow: 0 8px 30px rgba(212,175,55,0.45);
  position: relative;
  overflow: hidden;
}

.booking-btn-main::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
  transform: translateX(-100%);
  transition: transform 0.6s ease;
}

.booking-btn-main:hover::before { transform: translateX(100%); }

.booking-btn-main:hover {
  transform: translateY(-4px) scale(1.04);
  box-shadow: 0 16px 45px rgba(212,175,55,0.6), 0 0 30px rgba(212,175,55,0.25);
  background-position: 100% 0;
}

.booking-btn-wa {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-family: 'Inter', sans-serif;
  font-size: 0.88em;
  font-weight: 600;
  color: rgba(255,255,255,0.8);
  text-decoration: none;
  border: 1px solid rgba(255,255,255,0.2);
  padding: 14px 24px;
  border-radius: 999px;
  backdrop-filter: blur(8px);
  background: rgba(255,255,255,0.06);
  transition: all 0.3s ease;
}

.booking-btn-wa:hover {
  color: #fff;
  border-color: rgba(212,175,55,0.6);
  background: rgba(212,175,55,0.1);
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(212,175,55,0.2);
}

.booking-right {
  position: relative;
  opacity: 0;
  animation: fadeSlideUp 1s cubic-bezier(.22,.68,0,1.2) forwards 0.5s;
}

@keyframes fadeSlideUp {
  from { opacity: 0; transform: translateY(40px); }
  to   { opacity: 1; transform: translateY(0); }
}

.booking-card-visual {
  background: rgba(255,255,255,0.06);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(212,175,55,0.25);
  border-radius: 28px;
  padding: 36px;
  position: relative;
  overflow: hidden;
  box-shadow:
    0 20px 60px rgba(0,0,0,0.35),
    0 0 40px rgba(212,175,55,0.08),
    inset 0 1px 0 rgba(255,255,255,0.1);
  transition: transform 0.45s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.45s ease, border-color 0.45s ease;
}

.booking-card-visual:hover {
  transform: translateY(-8px) scale(1.02);
  border-color: rgba(212,175,55,0.5);
  box-shadow:
    0 30px 80px rgba(0,0,0,0.45),
    0 0 50px rgba(212,175,55,0.2),
    0 0 100px rgba(212,175,55,0.08);
}

.booking-card-visual::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
  background: linear-gradient(90deg, #D4AF37, #ee2a7b, #D4AF37);
  background-size: 200% 100%;
  animation: goldSlide 4s linear infinite;
}

.booking-card-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
}

.booking-card-logo {
  font-family: 'Playfair Display', serif;
  font-size: 1.1em;
  font-weight: 700;
  color: #D4AF37;
  letter-spacing: 1px;
}

.booking-status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(52,211,153,0.15);
  border: 1px solid rgba(52,211,153,0.4);
  color: #6ee7b7;
  font-family: 'Inter', sans-serif;
  font-size: 0.72em;
  font-weight: 600;
  padding: 5px 12px;
  border-radius: 999px;
  letter-spacing: 1px;
}

.booking-status-dot {
  width: 7px;
  height: 7px;
  background: #34d399;
  border-radius: 50%;
  animation: pulseDot 2s ease-in-out infinite;
}

@keyframes pulseDot {
  0%, 100% { transform: scale(1); opacity: 1; }
  50%       { transform: scale(1.5); opacity: 0.6; }
}

.booking-divider-gold {
  height: 1px;
  background: linear-gradient(to right, transparent, rgba(212,175,55,0.5), transparent);
  margin-bottom: 24px;
}

.booking-form-preview {
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin-bottom: 24px;
}

.booking-form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.booking-field {
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.12);
  border-radius: 12px;
  padding: 12px 16px;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.booking-field:hover {
  border-color: rgba(212,175,55,0.4);
  box-shadow: 0 0 16px rgba(212,175,55,0.12);
}

.booking-field-label {
  font-family: 'Inter', sans-serif;
  font-size: 0.65em;
  color: rgba(212,175,55,0.8);
  font-weight: 600;
  letter-spacing: 1px;
  text-transform: uppercase;
  margin-bottom: 4px;
}

.booking-field-value {
  font-family: 'Inter', sans-serif;
  font-size: 0.88em;
  color: rgba(255,255,255,0.85);
  font-weight: 500;
}

.booking-table-chips {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-top: 6px;
}

.booking-table-chip {
  background: rgba(212,175,55,0.14);
  border: 1px solid rgba(212,175,55,0.35);
  color: #D4AF37;
  font-family: 'Inter', sans-serif;
  font-size: 0.72em;
  font-weight: 600;
  padding: 5px 14px;
  border-radius: 999px;
  cursor: pointer;
  transition: background 0.25s, box-shadow 0.25s, transform 0.2s;
}

.booking-table-chip:hover,
.booking-table-chip.selected {
  background: rgba(212,175,55,0.28);
  box-shadow: 0 0 14px rgba(212,175,55,0.35);
  transform: scale(1.06);
}

.booking-summary-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 0 0;
  border-top: 1px solid rgba(255,255,255,0.08);
}

.booking-summary-label {
  font-family: 'Inter', sans-serif;
  font-size: 0.8em;
  color: rgba(255,255,255,0.55);
}

.booking-summary-value {
  font-family: 'Playfair Display', serif;
  font-size: 1em;
  color: #D4AF37;
  font-weight: 600;
}

@media (max-width: 900px) {
  .booking-inner { grid-template-columns: 1fr; gap: 40px; }
  .booking-left h2 { font-size: 2em; }
}

@media (max-width: 768px) {
  .booking-section { padding: 60px 20px; }
  .booking-form-row { grid-template-columns: 1fr; }
}

  </style>
</head>
<body>

<!-- NAVBAR -->
<nav>
  <div class="nav-left">
    <img src="https://lh3.googleusercontent.com/gps-cs/ACgwaOvrk66Mw6TuaNg3tG6p8G9hJq_wOTUoBmpbb3qtX0t9CN0D6K8ns6HxQUsk_xRrGiRBD__9n78mwhr3RZ7cwM3UINa2Jjzvzx2U1l8S2SP93wZa3ga4xfn1BY446aaj_CJ_6ACQYiN58RQ=w203-h304-k-no">
    <h2>YOLAZCAKE</h2>
  </div>
  <ul class="main-nav">
    <li onclick="window.location.href='../index.php'">Home</li>
    <li class="active" onclick="window.location.href='menu.php'">Menu</li>
    <li onclick="window.location.href='../gallery.php'">Gallery</li>
    <li onclick="window.location.href='../about.php'">About</li>
    <li onclick="window.location.href='../contact.php'">Contact</li>
  </ul>
  <div class="nav-right">
<?php if(isset($_SESSION['username'])){ ?>
    <div class="account-dropdown">
      <button class="account-btn">👤 <?php echo htmlspecialchars($_SESSION['username']); ?> ▼</button>
      <div class="account-menu">
        <a href="../member/member.php">Member</a>
        <a href="../auth/logout.php">Logout</a>
      </div>
    </div>
<?php } else { ?>
    <button class="login-btn" onclick="window.location.href='auth/login.php'">Login</button>
<?php } ?>
    <div class="hamburger" onclick="toggleMenu()" id="hamburger">☰</div>
    <div class="dark-btn" onclick="toggleDark()">🌙</div>
  </div>
  <div class="dropdown" id="dropdown">
    <p onclick="window.location.href='../about.php#story'">Back Story</p>
    <p onclick="window.location.href='menu.php#Product'">Featured Product</p>
    <p onclick="window.location.href='menu.php#promo'">Promo</p>
    <p onclick="window.location.href='../gallery.php#Rating'">Rating</p>
    <p onclick="window.location.href='../gallery.php#gallery'">Gallery</p>
    <p onclick="window.location.href='../about.php#team'">Team</p>
    <p onclick="window.location.href='../contact.php#location'">Location</p>
    <p onclick="window.location.href='../contact.php#contact'">Contact &amp; Sosmed</p>
  </div>
</nav>

<!-- ========== PREMIUM HERO BANNER ========== -->
<div class="menu-hero" id="menuHero">
  <div class="menu-hero-inner">
    <p class="hero-eyebrow">✦ YOLAZCAKE Sintang ✦</p>
    <h1>Menu Kami</h1>
    <p class="hero-sub">Cake • Coffee • Dessert — Cita Rasa Premium di Setiap Sajian ☕🎂</p>
    <div class="hero-divider">
      <span></span>
      <span class="diamond">✦ ✦ ✦</span>
      <span></span>
    </div>
  </div>
</div>

<!-- ========== HIGHLIGHTS MENU ========== -->
<section id="menu">

  <div class="section-label fade">
    <span class="eyebrow">✦ Koleksi Spesial</span>
    <h2>Highlights Menu</h2>
    <div class="gold-rule"><span>✦ ✦ ✦</span></div>
  </div>

  <div class="menu-grid">

    <div class="menu-card fade" data-menu-carousel="0">
      <div class="menu-card-photo">
        <div class="photo-carousel" id="menu-carousel-0">
          <div class="photo-carousel-slide" style="background-image:url('https://lh3.googleusercontent.com/gps-cs/ACgwaOsjgsDIERU-B3GnR1ruk75ki6a80miDlZVCmDY3O8PrkANPzWPny2z_gk-SelfTGI2H-yaNY87aKn5ACU1ldWnWeau5Fg-T_MWBFwUu92wVsGsA7K3tvLkMDgP7o2p8orx1ylzhaqhG4H9e=w203-h360-k-no')"></div>
          <div class="photo-carousel-slide" style="background-image:url('https://lh3.googleusercontent.com/gps-cs/ACgwaOvtRJSm48PI33kYPz5Dh6vwwL1nw8MHAvAtHoqJq6nJ-b5lCobai9y7qAZs_5moRepj__aGqPFPZjPDJngkwq2RQR3XghBp_JcKoPZ3F9KcM5towlHZDV4oHBRXlAshgkvcHnDXH89bbEQG=w203-h360-k-no')"></div>
          <div class="photo-carousel-slide" style="background-image:url('https://lh3.googleusercontent.com/gps-cs/ACgwaOs97VK3bhHWSSJjZ_Kd54xMSn1NZlWoPK-2Im2c0FVR99bVRrzveW42i0UFxCbkq0O3JU2TT8WMLodIXTSbpcUd8mlWKn1vRCq_rTSLcWm94awWdlDvV38l7y57TdyAJVGgFmxZc22MK1Sf=w203-h270-k-no')"></div>
        </div>
        <div class="menu-carousel-shimmer"></div>
        <button class="carousel-btn prev" onclick="menuCarouselMove(0,-1,event)">‹</button>
        <button class="carousel-btn next" onclick="menuCarouselMove(0,1,event)">›</button>
        <div class="carousel-dots" id="menu-dots-0">
          <div class="carousel-dot active" onclick="menuCarouselGo(0,0,event)"></div>
          <div class="carousel-dot" onclick="menuCarouselGo(0,1,event)"></div>
          <div class="carousel-dot" onclick="menuCarouselGo(0,2,event)"></div>
        </div>
        <div class="carousel-counter" id="menu-counter-0">1 / 3</div>
        <div class="carousel-progress" id="menu-progress-0" style="width:33.33%"></div>
        <div class="swipe-hint">👆 Geser</div>
        <span class="menu-card-badge">✦ Homemade</span>
      </div>
      <div class="menu-card-body">
        <h3>Donut</h3>
        <p>Donat homemade dengan bahan premium terpilih, lembut dan lezat.</p>
      </div>
    </div>

    <div class="menu-card fade" data-menu-carousel="1">
      <div class="menu-card-photo">
        <div class="photo-carousel" id="menu-carousel-1">
          <div class="photo-carousel-slide" style="background-image:url('https://lh3.googleusercontent.com/gps-cs-s/APNQkAGWRsx9I5aMBRYOOIDyWkQMcl9RgKnp_CD8Y4owK9qdPvMwPDSU6uWjs8zKKq107WLpgmVmTYOpaxMgSrhRpl-MCPZGyAYPgpltFO7hVh4tSM0DQa9uk4KWs-CsI5GZN3ftRsdhsHNp2_F6=w203-h270-k-no')"></div>
          <div class="photo-carousel-slide" style="background-image:url('https://lh3.googleusercontent.com/gps-cs/ACgwaOuw_xb_C00uZMq4DtdJ8N72egFCWZf_I2zjj3254lR9dbD1f9hvZdwoQRTJTBCJMXHg9_c9E2jgPN3iSC5iNmcI0V9CxytjMghupFWvZKW091bqicz5LtmOAjC2xSqrNcSxyB5UL4lEc9Zi=w203-h451-k-no')"></div>
          <div class="photo-carousel-slide" style="background-image:url('https://lh3.googleusercontent.com/gps-cs-s/APNQkAFSe4AwjgtAeBeNDNTkjvX2RCppfb3NaGWXpY-6JlAiafOOR7RPHdz55qrxED-6cbOSDbJBUkSaJvh57u0ONwlBQ3WLceBcOdESS4xhkT4IUPpvAY1SYvmDQ1egYDWLDkebPFVvP9ScW50v=w203-h360-k-no')"></div>
        </div>
        <div class="menu-carousel-shimmer"></div>
        <button class="carousel-btn prev" onclick="menuCarouselMove(1,-1,event)">‹</button>
        <button class="carousel-btn next" onclick="menuCarouselMove(1,1,event)">›</button>
        <div class="carousel-dots" id="menu-dots-1">
          <div class="carousel-dot active" onclick="menuCarouselGo(1,0,event)"></div>
          <div class="carousel-dot" onclick="menuCarouselGo(1,1,event)"></div>
          <div class="carousel-dot" onclick="menuCarouselGo(1,2,event)"></div>
        </div>
        <div class="carousel-counter" id="menu-counter-1">1 / 3</div>
        <div class="carousel-progress" id="menu-progress-1" style="width:33.33%"></div>
        <div class="swipe-hint">👆 Geser</div>
        <span class="menu-card-badge">✦ Specialty</span>
      </div>
      <div class="menu-card-body">
        <h3>Specialty Coffee</h3>
        <p>Espresso, Latte Art, Cold Brew — diseduh dengan biji kopi pilihan.</p>
      </div>
    </div>

    <div class="menu-card fade" data-menu-carousel="2">
      <div class="menu-card-photo">
        <div class="photo-carousel" id="menu-carousel-2">
          <div class="photo-carousel-slide" style="background-image:url('https://lh3.googleusercontent.com/gps-cs-s/APNQkAFSe4AwjgtAeBeNDNTkjvX2RCppfb3NaGWXpY-6JlAiafOOR7RPHdz55qrxED-6cbOSDbJBUkSaJvh57u0ONwlBQ3WLceBcOdESS4xhkT4IUPpvAY1SYvmDQ1egYDWLDkebPFVvP9ScW50v=w203-h360-k-no')"></div>
          <div class="photo-carousel-slide" style="background-image:url('https://lh3.googleusercontent.com/gps-cs/ACgwaOs97VK3bhHWSSJjZ_Kd54xMSn1NZlWoPK-2Im2c0FVR99bVRrzveW42i0UFxCbkq0O3JU2TT8WMLodIXTSbpcUd8mlWKn1vRCq_rTSLcWm94awWdlDvV38l7y57TdyAJVGgFmxZc22MK1Sf=w203-h270-k-no')"></div>
          <div class="photo-carousel-slide" style="background-image:url('https://lh3.googleusercontent.com/gps-cs/ACgwaOuw_xb_C00uZMq4DtdJ8N72egFCWZf_I2zjj3254lR9dbD1f9hvZdwoQRTJTBCJMXHg9_c9E2jgPN3iSC5iNmcI0V9CxytjMghupFWvZKW091bqicz5LtmOAjC2xSqrNcSxyB5UL4lEc9Zi=w203-h451-k-no')"></div>
        </div>
        <div class="menu-carousel-shimmer"></div>
        <button class="carousel-btn prev" onclick="menuCarouselMove(2,-1,event)">‹</button>
        <button class="carousel-btn next" onclick="menuCarouselMove(2,1,event)">›</button>
        <div class="carousel-dots" id="menu-dots-2">
          <div class="carousel-dot active" onclick="menuCarouselGo(2,0,event)"></div>
          <div class="carousel-dot" onclick="menuCarouselGo(2,1,event)"></div>
          <div class="carousel-dot" onclick="menuCarouselGo(2,2,event)"></div>
        </div>
        <div class="carousel-counter" id="menu-counter-2">1 / 3</div>
        <div class="carousel-progress" id="menu-progress-2" style="width:33.33%"></div>
        <div class="swipe-hint">👆 Geser</div>
        <span class="menu-card-badge">✦ Signature</span>
      </div>
      <div class="menu-card-body">
        <h3>Dessert &amp; Minuman</h3>
        <p>Non-coffee &amp; Signature Drink untuk menemani hari spesialmu.</p>
      </div>
    </div>

    <div class="menu-card fade" data-menu-carousel="3">
      <div class="menu-card-photo">
        <div class="photo-carousel" id="menu-carousel-3">
          <div class="photo-carousel-slide" style="background-image:url('https://lh3.googleusercontent.com/gps-cs-s/APNQkAGe6TtlEB63M_38pWHypN5RgdEpc-MPweIkN612OoMq6jVFNIYvjif89o7cFH8WVJ74_nlMy-qMu-UAyYRhJSOeEUbA9e9kOuLtDKBXbjN6fRPR4QHhMmR3a7XbkkrxnoLjeHNsC6oueVuz=w203-h360-k-no')"></div>
          <div class="photo-carousel-slide" style="background-image:url('https://lh3.googleusercontent.com/gps-cs-s/APNQkAGTNAW5PNuDjb41NNHDB-bXNPs1EtKt7XHXoRW4iD52Wqkdl5vFbOK98l-0P1mAOSbGX1AQ3xUx2V_MWDBb_LIoeGDGh6e4gc_0DRjQV3DXtVCwO5rTsLNrrEE5UjCLiMWHGkb7quY1xwU=w203-h270-k-no')"></div>
          <div class="photo-carousel-slide" style="background-image:url('https://lh3.googleusercontent.com/gps-cs-s/APNQkAFSe4AwjgtAeBeNDNTkjvX2RCppfb3NaGWXpY-6JlAiafOOR7RPHdz55qrxED-6cbOSDbJBUkSaJvh57u0ONwlBQ3WLceBcOdESS4xhkT4IUPpvAY1SYvmDQ1egYDWLDkebPFVvP9ScW50v=w203-h360-k-no')"></div>
        </div>
        <div class="menu-carousel-shimmer"></div>
        <button class="carousel-btn prev" onclick="menuCarouselMove(3,-1,event)">‹</button>
        <button class="carousel-btn next" onclick="menuCarouselMove(3,1,event)">›</button>
        <div class="carousel-dots" id="menu-dots-3">
          <div class="carousel-dot active" onclick="menuCarouselGo(3,0,event)"></div>
          <div class="carousel-dot" onclick="menuCarouselGo(3,1,event)"></div>
          <div class="carousel-dot" onclick="menuCarouselGo(3,2,event)"></div>
        </div>
        <div class="carousel-counter" id="menu-counter-3">1 / 3</div>
        <div class="carousel-progress" id="menu-progress-3" style="width:33.33%"></div>
        <div class="swipe-hint">👆 Geser</div>
        <span class="menu-card-badge">✦ Boutique</span>
      </div>
      <div class="menu-card-body">
        <h3>Boutique Lantai 2</h3>
        <p>Pakaian modern &amp; aesthetic — kurasi koleksi fashion terkini.</p>
      </div>
    </div>

  </div>

</section>

<!-- ========== PRODUK UNGGULAN (dark purple) ========== -->
<section id="Product">

  <!-- Floating particles -->
  <div id="productParticles"></div>

  <div class="section-label fade">
    <span class="eyebrow">✦ Best Seller</span>
    <h2>Produk Unggulan</h2>
    <div class="gold-rule"><span>✦ ✦ ✦</span></div>
  </div>

  <div class="product-grid">

    <!-- CARD 1: Trio Cake -->
    <div class="product-card fade" data-carousel="0">
      <div class="product-card-photo">
        <!-- Carousel Track -->
        <div class="photo-carousel" id="carousel-0">
          <div class="photo-carousel-slide" style="background-image:url('https://lh3.googleusercontent.com/gps-cs/ACgwaOs97VK3bhHWSSJjZ_Kd54xMSn1NZlWoPK-2Im2c0FVR99bVRrzveW42i0UFxCbkq0O3JU2TT8WMLodIXTSbpcUd8mlWKn1vRCq_rTSLcWm94awWdlDvV38l7y57TdyAJVGgFmxZc22MK1Sf=w203-h270-k-no')"></div>
          <div class="photo-carousel-slide" style="background-image:url('https://lh3.googleusercontent.com/gps-cs/ACgwaOvtRJSm48PI33kYPz5Dh6vwwL1nw8MHAvAtHoqJq6nJ-b5lCobai9y7qAZs_5moRepj__aGqPFPZjPDJngkwq2RQR3XghBp_JcKoPZ3F9KcM5towlHZDV4oHBRXlAshgkvcHnDXH89bbEQG=w203-h360-k-no')"></div>
          <div class="photo-carousel-slide" style="background-image:url('https://lh3.googleusercontent.com/gps-cs/ACgwaOuw_xb_C00uZMq4DtdJ8N72egFCWZf_I2zjj3254lR9dbD1f9hvZdwoQRTJTBCJMXHg9_c9E2jgPN3iSC5iNmcI0V9CxytjMghupFWvZKW091bqicz5LtmOAjC2xSqrNcSxyB5UL4lEc9Zi=w203-h451-k-no')"></div>
        </div>
        <!-- Shimmer overlay (keeps original effect) -->
        <div class="carousel-shimmer"></div>
        <!-- Nav buttons -->
        <button class="carousel-btn prev" onclick="carouselMove(0,-1,event)">‹</button>
        <button class="carousel-btn next" onclick="carouselMove(0,1,event)">›</button>
        <!-- Dots -->
        <div class="carousel-dots" id="dots-0">
          <div class="carousel-dot active" onclick="carouselGo(0,0,event)"></div>
          <div class="carousel-dot" onclick="carouselGo(0,1,event)"></div>
          <div class="carousel-dot" onclick="carouselGo(0,2,event)"></div>
        </div>
        <!-- Counter -->
        <div class="carousel-counter" id="counter-0">1 / 3</div>
        <!-- Progress bar -->
        <div class="carousel-progress" id="progress-0" style="width:33.33%"></div>
        <!-- Swipe hint -->
        <div class="swipe-hint">👆 Geser</div>
        <!-- Badge -->
        <span class="product-card-badge">✦ Best Seller</span>
      </div>
      <div class="product-card-body">
        <h3>Trio Cake</h3>
        <p>Chocolate Indulgence, Klepon Cake, Red Velvet — trio terbaik kami.</p>
        <div class="product-tag-row">
          <span class="product-tag">🎂 Custom</span>
          <span class="product-tag">🍫 Premium</span>
          <span class="product-tag">💛 Favorit</span>
        </div>
      </div>
    </div>

    <!-- CARD 2: Signature Latte -->
    <div class="product-card fade" data-carousel="1">
      <div class="product-card-photo">
        <div class="photo-carousel" id="carousel-1">
          <div class="photo-carousel-slide" style="background-image:url('https://lh3.googleusercontent.com/gps-cs/ACgwaOuw_xb_C00uZMq4DtdJ8N72egFCWZf_I2zjj3254lR9dbD1f9hvZdwoQRTJTBCJMXHg9_c9E2jgPN3iSC5iNmcI0V9CxytjMghupFWvZKW091bqicz5LtmOAjC2xSqrNcSxyB5UL4lEc9Zi=w203-h451-k-no')"></div>
          <div class="photo-carousel-slide" style="background-image:url('https://lh3.googleusercontent.com/gps-cs-s/APNQkAGWRsx9I5aMBRYOOIDyWkQMcl9RgKnp_CD8Y4owK9qdPvMwPDSU6uWjs8zKKq107WLpgmVmTYOpaxMgSrhRpl-MCPZGyAYPgpltFO7hVh4tSM0DQa9uk4KWs-CsI5GZN3ftRsdhsHNp2_F6=w203-h270-k-no')"></div>
          <div class="photo-carousel-slide" style="background-image:url('https://lh3.googleusercontent.com/gps-cs-s/APNQkAFSe4AwjgtAeBeNDNTkjvX2RCppfb3NaGWXpY-6JlAiafOOR7RPHdz55qrxED-6cbOSDbJBUkSaJvh57u0ONwlBQ3WLceBcOdESS4xhkT4IUPpvAY1SYvmDQ1egYDWLDkebPFVvP9ScW50v=w203-h360-k-no')"></div>
        </div>
        <div class="carousel-shimmer"></div>
        <button class="carousel-btn prev" onclick="carouselMove(1,-1,event)">‹</button>
        <button class="carousel-btn next" onclick="carouselMove(1,1,event)">›</button>
        <div class="carousel-dots" id="dots-1">
          <div class="carousel-dot active" onclick="carouselGo(1,0,event)"></div>
          <div class="carousel-dot" onclick="carouselGo(1,1,event)"></div>
          <div class="carousel-dot" onclick="carouselGo(1,2,event)"></div>
        </div>
        <div class="carousel-counter" id="counter-1">1 / 3</div>
        <div class="carousel-progress" id="progress-1" style="width:33.33%"></div>
        <div class="swipe-hint">👆 Geser</div>
        <span class="product-card-badge">✦ Signature</span>
      </div>
      <div class="product-card-body">
        <h3>Signature Latte</h3>
        <p>Dibuat dengan biji kopi pilihan dan susu premium untuk citarasa sempurna.</p>
        <div class="product-tag-row">
          <span class="product-tag">☕ Latte Art</span>
          <span class="product-tag">🫘 Specialty</span>
          <span class="product-tag">✨ Premium</span>
        </div>
      </div>
    </div>

    <!-- CARD 3: Donat Premium -->
    <div class="product-card fade" data-carousel="2">
      <div class="product-card-photo">
        <div class="photo-carousel" id="carousel-2">
          <div class="photo-carousel-slide" style="background-image:url('https://lh3.googleusercontent.com/gps-cs/ACgwaOvtRJSm48PI33kYPz5Dh6vwwL1nw8MHAvAtHoqJq6nJ-b5lCobai9y7qAZs_5moRepj__aGqPFPZjPDJngkwq2RQR3XghBp_JcKoPZ3F9KcM5towlHZDV4oHBRXlAshgkvcHnDXH89bbEQG=w203-h360-k-no')"></div>
          <div class="photo-carousel-slide" style="background-image:url('https://lh3.googleusercontent.com/gps-cs-s/APNQkAEgqBd6NWb-UkCt8EaP0d1IZd1uvxczXtXKLqfRDBq92UVlXrN7tU3acmu48PE8dVq0U1smQuLn2flGuhSv8SBGDmMDQ4HDzPt0inOcEPrHgnuDXY7D_wn_tuiP-mcCPSzYHXrsy13Bbp1g=w203-h152-k-no')"></div>
          <div class="photo-carousel-slide" style="background-image:url('https://lh3.googleusercontent.com/gps-cs/ACgwaOs97VK3bhHWSSJjZ_Kd54xMSn1NZlWoPK-2Im2c0FVR99bVRrzveW42i0UFxCbkq0O3JU2TT8WMLodIXTSbpcUd8mlWKn1vRCq_rTSLcWm94awWdlDvV38l7y57TdyAJVGgFmxZc22MK1Sf=w203-h270-k-no')"></div>
        </div>
        <div class="carousel-shimmer"></div>
        <button class="carousel-btn prev" onclick="carouselMove(2,-1,event)">‹</button>
        <button class="carousel-btn next" onclick="carouselMove(2,1,event)">›</button>
        <div class="carousel-dots" id="dots-2">
          <div class="carousel-dot active" onclick="carouselGo(2,0,event)"></div>
          <div class="carousel-dot" onclick="carouselGo(2,1,event)"></div>
          <div class="carousel-dot" onclick="carouselGo(2,2,event)"></div>
        </div>
        <div class="carousel-counter" id="counter-2">1 / 3</div>
        <div class="carousel-progress" id="progress-2" style="width:33.33%"></div>
        <div class="swipe-hint">👆 Geser</div>
        <span class="product-card-badge">✦ Homemade</span>
      </div>
      <div class="product-card-body">
        <h3>Donat Premium</h3>
        <p>Donat homemade lembut dengan topping berlimpah dan bahan terbaik.</p>
        <div class="product-tag-row">
          <span class="product-tag">🍩 Topping</span>
          <span class="product-tag">💛 Homemade</span>
          <span class="product-tag">🎁 Gift</span>
        </div>
      </div>
    </div>

    <!-- CARD 4: Boutique Collection -->
    <div class="product-card fade" data-carousel="3">
      <div class="product-card-photo">
        <div class="photo-carousel" id="carousel-3">
          <div class="photo-carousel-slide" style="background-image:url('https://lh3.googleusercontent.com/gps-cs-s/APNQkAGTNAW5PNuDjb41NNHDB-bXNPs1EtKt7XHXoRW4iD52Wqkdl5vFbOK98l-0P1mAOSbGX1AQ3xUx2V_MWDBb_LIoeGDGh6e4gc_0DRjQV3DXtVCwO5rTsLNrrEE5UjCLiMWHGkb7quY1xwU=w203-h270-k-no')"></div>
          <div class="photo-carousel-slide" style="background-image:url('https://lh3.googleusercontent.com/gps-cs-s/APNQkAGe6TtlEB63M_38pWHypN5RgdEpc-MPweIkN612OoMq6jVFNIYvjif89o7cFH8WVJ74_nlMy-qMu-UAyYRhJSOeEUbA9e9kOuLtDKBXbjN6fRPR4QHhMmR3a7XbkkrxnoLjeHNsC6oueVuz=w203-h360-k-no')"></div>
          <div class="photo-carousel-slide" style="background-image:url('https://lh3.googleusercontent.com/gps-cs-s/APNQkAFSe4AwjgtAeBeNDNTkjvX2RCppfb3NaGWXpY-6JlAiafOOR7RPHdz55qrxED-6cbOSDbJBUkSaJvh57u0ONwlBQ3WLceBcOdESS4xhkT4IUPpvAY1SYvmDQ1egYDWLDkebPFVvP9ScW50v=w203-h360-k-no')"></div>
        </div>
        <div class="carousel-shimmer"></div>
        <button class="carousel-btn prev" onclick="carouselMove(3,-1,event)">‹</button>
        <button class="carousel-btn next" onclick="carouselMove(3,1,event)">›</button>
        <div class="carousel-dots" id="dots-3">
          <div class="carousel-dot active" onclick="carouselGo(3,0,event)"></div>
          <div class="carousel-dot" onclick="carouselGo(3,1,event)"></div>
          <div class="carousel-dot" onclick="carouselGo(3,2,event)"></div>
        </div>
        <div class="carousel-counter" id="counter-3">1 / 3</div>
        <div class="carousel-progress" id="progress-3" style="width:33.33%"></div>
        <div class="swipe-hint">👆 Geser</div>
        <span class="product-card-badge">✦ Style</span>
      </div>
      <div class="product-card-body">
        <h3>Boutique Collection</h3>
        <p>Perpaduan style, elegance, dan confidence untuk tampil memukau.</p>
        <div class="product-tag-row">
          <span class="product-tag">👗 Fashion</span>
          <span class="product-tag">💄 Style</span>
          <span class="product-tag">✨ Aesthetic</span>
        </div>
      </div>
    </div>

  </div>

</section>

<!-- ========== BOOKING SECTION ========== -->
<section class="booking-section" id="booking">

  <div class="booking-particles" id="bookingParticles"></div>

  <div class="booking-inner">

    <!-- KIRI: teks & CTA -->
    <div class="booking-left">
      <span class="eyebrow">✦ Table Reservation</span>
      <h2>Reserve Your<br>Favorite Table</h2>
      <p>
        Nikmati pengalaman tak terlupakan bersama keluarga dan sahabat.
        Reservasi meja lebih awal agar momen spesial Anda di YOLAZCAKE
        selalu terjamin dan nyaman.
      </p>

      <div class="booking-features">
        <div class="booking-feature-item">
          <div class="booking-feature-icon">🍰</div>
          <span>Sajian cake &amp; dessert premium langsung di meja Anda</span>
        </div>
        <div class="booking-feature-item">
          <div class="booking-feature-icon">☕</div>
          <span>Specialty coffee diseduh saat reservasi dikonfirmasi</span>
        </div>
        <div class="booking-feature-item">
          <div class="booking-feature-icon">🎉</div>
          <span>Cocok untuk gathering, ulang tahun &amp; acara spesial</span>
        </div>
        <div class="booking-feature-item">
          <div class="booking-feature-icon">📍</div>
          <span>Tersedia indoor &amp; outdoor — pemandangan kota Sintang</span>
        </div>
      </div>

      <div class="booking-cta-row">
        <a href="../booking/booking.php" class="booking-btn-main">
          📅 Booking Sekarang
        </a>
        <a href="https://wa.me/6281578157888" class="booking-btn-wa" target="_blank">
          💬 Tanya via WA
        </a>
      </div>
    </div>

    <!-- KANAN: card visual preview -->
    <div class="booking-right">
      <div class="booking-card-visual">

        <div class="booking-card-top">
          <span class="booking-card-logo">✦ YOLAZCAKE</span>
          <span class="booking-status-badge">
            <span class="booking-status-dot"></span>
            Tersedia Hari Ini
          </span>
        </div>

        <div class="booking-divider-gold"></div>

        <div class="booking-form-preview">
          <div class="booking-form-row">
            <div class="booking-field">
              <div class="booking-field-label">Tanggal</div>
              <div class="booking-field-value" id="bookingDateDisplay">—</div>
            </div>
            <div class="booking-field">
              <div class="booking-field-label">Jam</div>
              <div class="booking-field-value">10:00 – 21:00</div>
            </div>
          </div>
          <div class="booking-field">
            <div class="booking-field-label">Jumlah Tamu</div>
            <div class="booking-field-value">1 – 20 orang</div>
          </div>
          <div class="booking-field">
            <div class="booking-field-label">Pilih Meja</div>
            <div class="booking-table-chips">
              <span class="booking-table-chip selected">Window Seat</span>
              <span class="booking-table-chip">Indoor VIP</span>
              <span class="booking-table-chip">Outdoor</span>
              <span class="booking-table-chip">Rooftop</span>
            </div>
          </div>
        </div>

        <div class="booking-summary-row">
          <span class="booking-summary-label">Konfirmasi via WhatsApp</span>
          <span class="booking-summary-value">0815-7815-7888</span>
        </div>

      </div>
    </div>

  </div>
</section>

<!-- ========== PROMO SPESIAL ========== -->
<section id="promo">

  <div class="section-label fade">
    <span class="eyebrow">✦ Penawaran Eksklusif</span>
    <h2>Promo Spesial</h2>
    <div class="gold-rule"><span>✦ ✦ ✦</span></div>
  </div>

  <div class="promo-card-premium fade">
    <span class="promo-icon">🎉</span>
    <h3>Diskon 25% untuk Setiap Pembelian<br>di Atas Rp150.000</h3>
    <p>
      Berlaku untuk cake, dessert, dan minuman pilihan.
      Hanya di akhir pekan! Jangan lewatkan kesempatan menikmati
      sajian premium YOLAZCAKE dengan harga spesial.
    </p>
    <div class="promo-badges">
      <span class="promo-badge">🎂 Cake & Dessert</span>
      <span class="promo-badge">☕ Minuman Pilihan</span>
      <span class="promo-badge">📅 Akhir Pekan</span>
      <span class="promo-badge">💸 Hemat 25%</span>
    </div>
    <button class="promo-btn-premium" onclick="window.location.href='../promo.php'">
      ✦ Ambil Promo Sekarang ✦
    </button>
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

<script src="../js/style.js"></script>
<script>

  /* --- Floating sparkles in hero --- */
  (function(){
    const hero = document.getElementById('menuHero');
    const colors = ['#D4AF37','#FFE4B5','#E8A0BF','#fff','#f9ce34','#b8860b'];
    for(let i = 0; i < 24; i++){
      const dot = document.createElement('div');
      dot.classList.add('sparkle');
      const size = Math.random() * 5 + 2;
      dot.style.cssText = `
        width:${size}px; height:${size}px;
        background:${colors[Math.floor(Math.random()*colors.length)]};
        left:${Math.random()*100}%;
        bottom:${Math.random()*30}%;
        animation-duration:${4 + Math.random()*7}s;
        animation-delay:${Math.random()*5}s;
        opacity:0;
      `;
      hero.appendChild(dot);
    }
  })();

  /* --- Floating particles in #Product --- */
  (function(){
    const container = document.getElementById('productParticles');
    const colors = ['rgba(212,175,55,0.5)','rgba(232,160,191,0.4)','rgba(255,255,255,0.2)'];
    for(let i = 0; i < 18; i++){
      const p = document.createElement('div');
      p.classList.add('particle');
      const size = Math.random() * 6 + 2;
      p.style.cssText = `
        width:${size}px; height:${size}px;
        background:${colors[Math.floor(Math.random()*colors.length)]};
        left:${Math.random()*100}%;
        animation-duration:${9 + Math.random()*10}s;
        animation-delay:${Math.random()*8}s;
      `;
      container.appendChild(p);
    }
  })();

  /* --- Scroll reveal (IntersectionObserver) --- */
  (function(){
    const targets = document.querySelectorAll('.fade');
    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if(entry.isIntersecting){
          const parent = entry.target.closest('.menu-grid, .product-grid');
          const delay = parent
            ? Array.from(parent.children).indexOf(entry.target) * 0.12
            : 0;
          entry.target.style.animationDelay = delay + 's';
          entry.target.classList.add('show');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });

    targets.forEach(t => io.observe(t));
  })();
  /* ===== PRODUCT CARD PHOTO CAROUSEL ===== */
  (function(){
    const state = {}; // { [id]: { current, total, autoTimer } }

    function init(){
      document.querySelectorAll('[data-carousel]').forEach(card => {
        const id = parseInt(card.dataset.carousel);
        const track = document.getElementById('carousel-' + id);
        if(!track) return;
        const slides = track.querySelectorAll('.photo-carousel-slide');
        state[id] = { current: 0, total: slides.length };
        // Touch/swipe support
        let startX = 0, isDragging = false;
        const photoEl = track.parentElement;
        photoEl.addEventListener('touchstart', e => { startX = e.touches[0].clientX; isDragging = true; }, { passive: true });
        photoEl.addEventListener('touchend', e => {
          if(!isDragging) return;
          const dx = e.changedTouches[0].clientX - startX;
          if(Math.abs(dx) > 40) carouselMove(id, dx < 0 ? 1 : -1);
          isDragging = false;
        });
        // Start auto-slide
        startAuto(id);
        card.addEventListener('mouseenter', () => stopAuto(id));
        card.addEventListener('mouseleave', () => startAuto(id));
      });
    }

    function startAuto(id){
      stopAuto(id);
      state[id].autoTimer = setInterval(() => carouselMove(id, 1), 3200);
    }
    function stopAuto(id){
      if(state[id] && state[id].autoTimer) clearInterval(state[id].autoTimer);
    }

    window.carouselMove = function(id, dir, e){
      if(e){ e.stopPropagation(); e.preventDefault(); }
      if(!state[id]) return;
      const s = state[id];
      s.current = (s.current + dir + s.total) % s.total;
      updateCarousel(id);
    };

    window.carouselGo = function(id, idx, e){
      if(e){ e.stopPropagation(); e.preventDefault(); }
      if(!state[id]) return;
      state[id].current = idx;
      updateCarousel(id);
    };

    function updateCarousel(id){
      const s = state[id];
      const track = document.getElementById('carousel-' + id);
      if(!track) return;
      track.style.transform = `translateX(-${s.current * 100}%)`;
      // Update dots
      const dotsEl = document.getElementById('dots-' + id);
      if(dotsEl){
        dotsEl.querySelectorAll('.carousel-dot').forEach((d, i) => {
          d.classList.toggle('active', i === s.current);
        });
      }
      // Update counter
      const counter = document.getElementById('counter-' + id);
      if(counter) counter.textContent = (s.current + 1) + ' / ' + s.total;
      // Update progress bar
      const progress = document.getElementById('progress-' + id);
      if(progress) progress.style.width = ((s.current + 1) / s.total * 100) + '%';
    }

    // Init after DOM ready
    if(document.readyState === 'loading'){
      document.addEventListener('DOMContentLoaded', init);
    } else {
      init();
    }
  })();

  /* ===== MENU HIGHLIGHTS CAROUSEL (identik dengan product-card carousel) ===== */
  (function(){
    const mState = {};

    function menuInit(){
      document.querySelectorAll('[data-menu-carousel]').forEach(card => {
        const id = parseInt(card.dataset.menuCarousel);
        const track = document.getElementById('menu-carousel-' + id);
        if(!track) return;
        const slides = track.querySelectorAll('.photo-carousel-slide');
        mState[id] = { current: 0, total: slides.length };
        // Touch/swipe support
        let startX = 0, isDragging = false;
        const photoEl = card.querySelector('.menu-card-photo');
        photoEl.addEventListener('touchstart', e => { startX = e.touches[0].clientX; isDragging = true; }, { passive: true });
        photoEl.addEventListener('touchend', e => {
          if(!isDragging) return;
          const dx = e.changedTouches[0].clientX - startX;
          if(Math.abs(dx) > 40) menuCarouselMove(id, dx < 0 ? 1 : -1);
          isDragging = false;
        });
        // Auto-slide
        menuStartAuto(id);
        card.addEventListener('mouseenter', () => menuStopAuto(id));
        card.addEventListener('mouseleave', () => menuStartAuto(id));
      });
    }

    function menuStartAuto(id){
      menuStopAuto(id);
      mState[id].autoTimer = setInterval(() => menuCarouselMove(id, 1), 3200);
    }
    function menuStopAuto(id){
      if(mState[id] && mState[id].autoTimer) clearInterval(mState[id].autoTimer);
    }

    window.menuCarouselMove = function(id, dir, e){
      if(e){ e.stopPropagation(); e.preventDefault(); }
      if(!mState[id]) return;
      const s = mState[id];
      s.current = (s.current + dir + s.total) % s.total;
      menuUpdateCarousel(id);
    };

    window.menuCarouselGo = function(id, idx, e){
      if(e){ e.stopPropagation(); e.preventDefault(); }
      if(!mState[id]) return;
      mState[id].current = idx;
      menuUpdateCarousel(id);
    };

    function menuUpdateCarousel(id){
      const s = mState[id];
      const track = document.getElementById('menu-carousel-' + id);
      if(!track) return;
      track.style.transform = `translateX(-${s.current * 100}%)`;
      const dotsEl = document.getElementById('menu-dots-' + id);
      if(dotsEl){
        dotsEl.querySelectorAll('.carousel-dot').forEach((d, i) => {
          d.classList.toggle('active', i === s.current);
        });
      }
      const counter = document.getElementById('menu-counter-' + id);
      if(counter) counter.textContent = (s.current + 1) + ' / ' + s.total;
      const progress = document.getElementById('menu-progress-' + id);
      if(progress) progress.style.width = ((s.current + 1) / s.total * 100) + '%';
    }

    if(document.readyState === 'loading'){
      document.addEventListener('DOMContentLoaded', menuInit);
    } else {
      menuInit();
    }
  })();

  /* ===== BOOKING SECTION ===== */
(function(){
  function initBookingParticles(){
    const container = document.getElementById('bookingParticles');
    if(!container) return;
    const colors = ['rgba(212,175,55,0.45)','rgba(232,160,191,0.35)','rgba(255,255,255,0.18)','rgba(150,100,220,0.3)'];
    for(let i = 0; i < 22; i++){
      const p = document.createElement('div');
      p.classList.add('particle');
      const size = Math.random() * 6 + 2;
      p.style.cssText = `
        width:${size}px; height:${size}px;
        background:${colors[Math.floor(Math.random()*colors.length)]};
        left:${Math.random()*100}%;
        animation-duration:${10 + Math.random()*12}s;
        animation-delay:${Math.random()*9}s;
      `;
      container.appendChild(p);
    }
  }

  function setBookingDate(){
    const el = document.getElementById('bookingDateDisplay');
    if(!el) return;
    const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    const d = new Date();
    el.textContent = days[d.getDay()] + ', ' + d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
  }

  function initChips(){
    document.querySelectorAll('.booking-table-chip').forEach(chip => {
      chip.addEventListener('click', function(){
        document.querySelectorAll('.booking-table-chip').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
      });
    });
  }

  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', () => {
      initBookingParticles();
      setBookingDate();
      initChips();
    });
  } else {
    initBookingParticles();
    setBookingDate();
    initChips();
  }
})();

</script>

