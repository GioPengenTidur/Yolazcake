<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location: ../auth/login.php");
    exit();
}
if(($_SESSION['role'] ?? '') !== 'admin'){
    header("Location: ../dashboard.php");
    exit();
}
require_once '../config/koneksi.php';

$query = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");

$total_query  = mysqli_query($conn, "SELECT COUNT(*) AS t, SUM(role='admin') AS a, SUM(role='kasir') AS k, SUM(role='pengunjung') AS p FROM users");
$stats        = mysqli_fetch_assoc($total_query);
$total_user   = $stats['t'] ?? 0;
$total_admin  = $stats['a'] ?? 0;
$total_kasir  = $stats['k'] ?? 0;
$total_pengunjung = $stats['p'] ?? 0;

// Kategori peran yang tersedia untuk dropdown ubah pangkat
$role_options = [
    'pengunjung' => ['label' => 'Pengunjung', 'icon' => '<i data-lucide="eye" class="lucide-ic"></i>'],
    'kasir'      => ['label' => 'Kasir',      'icon' => '<i data-lucide="user-round" class="lucide-ic"></i>‍<i data-lucide="chef-hat" class="lucide-ic"></i>'],
    'admin'      => ['label' => 'Admin',      'icon' => '<i data-lucide="crown" class="lucide-ic"></i>'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="../assets/css/lucide-icons.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Akun – YOLAZCAKE</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      min-height: 100vh;
      font-family: 'Inter', sans-serif;
      background: linear-gradient(160deg, #1e0e3a 0%, #2d1560 50%, #1a0a2e 100%);
      position: relative;
      overflow-x: hidden;
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

    /* hero */
    .page-hero {
      position: relative;
      height: 260px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      background: linear-gradient(135deg, #2b1a11 0%, #4a2c1a 40%, #6d3e26 70%, #3a1f0e 100%);
      z-index: 1;
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
      100% { transform:translateY(-280px) rotate(360deg); opacity:0; }
    }

    .hero-inner { position:relative; z-index:2; text-align:center; color:#fff; }

    .hero-eyebrow {
      font-size:0.72em; font-weight:500; letter-spacing:5px; text-transform:uppercase;
      color:#D4AF37; margin-bottom:10px;
      opacity:0; animation:fadeSlideDown 0.8s forwards 0.3s;
    }

    .hero-inner h1 {
      font-family:'Playfair Display',serif; font-size:3em; font-weight:700;
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

    /* content wrapper */
    .page-wrapper {
      position:relative; z-index:1;
      padding: 36px 28px 80px;
      max-width: 1100px;
      margin: 0 auto;
    }

    /* top bar */
    .top-bar {
      display:flex; justify-content:space-between; align-items:center;
      margin-bottom:28px;
      opacity:0; animation:cardReveal 0.7s forwards 0.7s;
    }

    .section-eyebrow {
      font-size:0.72em; font-weight:600; letter-spacing:4px;
      text-transform:uppercase; color:#D4AF37;
    }

    .btn-tambah {
      display:inline-flex; align-items:center; gap:8px;
      padding:12px 26px;
      background:linear-gradient(135deg, #D4AF37 0%, #b8860b 50%, #D4AF37 100%);
      background-size:200% 100%;
      animation:goldSlide 3s linear infinite;
      color:#1e0e3a; font-family:'Inter',sans-serif;
      font-size:0.82em; font-weight:700; letter-spacing:1.5px; text-transform:uppercase;
      border:none; border-radius:999px; cursor:pointer; text-decoration:none;
      box-shadow:0 6px 22px rgba(212,175,55,0.35), 0 0 30px rgba(212,175,55,0.18);
      transition:transform 0.25s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.35s;
      position:relative; overflow:hidden;
    }

    .btn-tambah::before {
      content:''; position:absolute; inset:0;
      background:linear-gradient(90deg,transparent,rgba(255,255,255,0.18),transparent);
      transform:translateX(-100%); transition:transform 0.5s;
    }

    .btn-tambah:hover::before { transform:translateX(100%); }
    .btn-tambah:hover {
      transform:translateY(-3px) scale(1.04);
      box-shadow:0 12px 36px rgba(212,175,55,0.5), 0 0 50px rgba(212,175,55,0.28);
    }

    @keyframes goldSlide { 0%{background-position:0% 0;} 100%{background-position:200% 0;} }

    /* stats bar */
    .stats-row {
      display:grid; grid-template-columns:repeat(4,1fr); gap:16px;
      margin-bottom:28px;
      opacity:0; animation:cardReveal 0.7s forwards 0.85s;
    }

    .stat-card {
      background:rgba(255,255,255,0.06);
      backdrop-filter:blur(16px);
      border:1px solid rgba(255,255,255,0.1);
      border-radius:18px; padding:20px 22px;
      display:flex; align-items:center; gap:14px;
      transition:border-color 0.35s, box-shadow 0.35s;
    }

    .stat-card:hover {
      border-color:rgba(212,175,55,0.35);
      box-shadow:0 0 24px rgba(212,175,55,0.2);
    }

    .stat-card::before {
      content:''; display:block;
      width:3px; height:40px; border-radius:999px;
      background:linear-gradient(to bottom, #D4AF37, #b8860b);
      flex-shrink:0;
    }

    .stat-icon { font-size:1.5em; }
    .stat-val { font-family:'Playfair Display',serif; font-size:1.7em; font-weight:700; color:#D4AF37; line-height:1; }
    .stat-lbl { font-size:0.75em; color:rgba(255,255,255,0.5); margin-top:2px; letter-spacing:0.5px; }

    /* table card */
    .table-card {
      background:rgba(255,255,255,0.05);
      backdrop-filter:blur(20px);
      border:1px solid rgba(255,255,255,0.1);
      border-radius:24px;
      overflow:hidden;
      position:relative;
      opacity:0; animation:cardReveal 0.8s forwards 1.0s;
    }

    .table-card::before {
      content:''; position:absolute; top:0; left:0; right:0; height:3px;
      background:linear-gradient(90deg, #D4AF37, #ee2a7b, #D4AF37);
      background-size:200% 100%;
      animation:goldSlide 4s linear infinite;
    }

    @keyframes cardReveal { to{opacity:1;transform:translateY(0);} }

    table { width:100%; border-collapse:collapse; }

    thead tr {
      background:rgba(212,175,55,0.12);
      border-bottom:1px solid rgba(212,175,55,0.25);
    }

    thead th {
      padding:16px 20px;
      font-size:0.7em; font-weight:700; letter-spacing:2.5px;
      text-transform:uppercase; color:rgba(212,175,55,0.9);
      text-align:left;
    }

    tbody tr {
      border-bottom:1px solid rgba(255,255,255,0.06);
      transition:background 0.25s;
    }

    tbody tr:last-child { border-bottom:none; }
    tbody tr:hover { background:rgba(212,175,55,0.06); }

    tbody td {
      padding:15px 20px;
      font-size:0.9em; color:rgba(255,255,255,0.8);
      vertical-align:middle;
    }

    .td-no {
      color:rgba(212,175,55,0.6);
      font-weight:700; font-size:0.82em; text-align:center; width:50px;
    }

    .td-nama { font-weight:600; color:#fff; }

    .you-tag {
      display:inline-block; margin-left:8px;
      font-size:0.68em; font-weight:700; letter-spacing:0.5px;
      color:rgba(255,255,255,0.45);
      border:1px solid rgba(255,255,255,0.18);
      border-radius:999px; padding:2px 8px;
    }

    /* role badge */
    .role-badge {
      display:inline-flex; align-items:center; gap:5px;
      border-radius:999px; padding:5px 13px;
      font-size:0.78em; font-weight:700; letter-spacing:0.5px;
    }
    .role-admin {
      background:rgba(212,175,55,0.16);
      border:1px solid rgba(212,175,55,0.4);
      color:#D4AF37;
    }
    .role-kasir {
      background:rgba(99,102,241,0.16);
      border:1px solid rgba(99,102,241,0.4);
      color:#a5b4fc;
    }
    .role-pengunjung {
      background:rgba(255,255,255,0.09);
      border:1px solid rgba(255,255,255,0.22);
      color:rgba(255,255,255,0.65);
    }

    /* role dropdown (ubah pangkat) */
    .role-select-wrap { position:relative; display:inline-block; }
    .role-select {
      appearance:none; -webkit-appearance:none; -moz-appearance:none;
      display:inline-flex; align-items:center; gap:5px;
      border-radius:999px; padding:6px 30px 6px 13px;
      font-size:0.78em; font-weight:700; letter-spacing:0.5px;
      font-family:'Inter',sans-serif;
      cursor:pointer; outline:none;
      background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23D4AF37' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
      background-repeat:no-repeat; background-position:right 9px center; background-size:13px;
      transition:border-color 0.25s, box-shadow 0.25s, opacity 0.25s;
    }
    .role-select:hover:not(:disabled) { box-shadow:0 0 0 3px rgba(212,175,55,0.12); }
    .role-select:focus:not(:disabled) { box-shadow:0 0 0 3px rgba(212,175,55,0.22); }
    .role-select:disabled { opacity:0.5; cursor:not-allowed; }
    .role-select option { background:#2d1560; color:#fff; }

    .role-select.role-admin {
      background-color:rgba(212,175,55,0.16); border:1px solid rgba(212,175,55,0.4); color:#D4AF37;
    }
    .role-select.role-kasir {
      background-color:rgba(99,102,241,0.16); border:1px solid rgba(99,102,241,0.4); color:#a5b4fc;
    }
    .role-select.role-pengunjung {
      background-color:rgba(255,255,255,0.09); border:1px solid rgba(255,255,255,0.22); color:rgba(255,255,255,0.65);
    }

    .role-locked {
      display:inline-flex; align-items:center; gap:5px;
      font-size:0.72em; color:rgba(255,255,255,0.35); margin-top:5px;
    }

    /* action buttons */
    .action-cell { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }

    .btn-act {
      display:inline-flex; align-items:center; gap:5px;
      padding:7px 14px; border-radius:8px;
      font-size:0.75em; font-weight:600; letter-spacing:0.5px;
      text-decoration:none; border:1px solid transparent;
      cursor:pointer; background:none;
      font-family:'Inter',sans-serif;
      transition:transform 0.2s, box-shadow 0.25s, background 0.25s;
    }

    .btn-act:hover { transform:translateY(-2px); }
    .btn-act:disabled { opacity:0.35; cursor:not-allowed; transform:none; }

    .btn-edit {
      background:rgba(212,175,55,0.18); border-color:rgba(212,175,55,0.4);
      color:#D4AF37;
    }
    .btn-edit:hover { background:rgba(212,175,55,0.32); box-shadow:0 4px 16px rgba(212,175,55,0.3); }

    .btn-hapus {
      background:rgba(239,68,68,0.14); border-color:rgba(239,68,68,0.35);
      color:#fca5a5;
    }
    .btn-hapus:hover { background:rgba(239,68,68,0.28); box-shadow:0 4px 16px rgba(239,68,68,0.25); }

    .btn-editakun {
      background:rgba(99,102,241,0.16); border-color:rgba(99,102,241,0.4);
      color:#a5b4fc;
    }
    .btn-editakun:hover { background:rgba(99,102,241,0.3); box-shadow:0 4px 16px rgba(99,102,241,0.25); }

    .td-email { color:rgba(255,255,255,0.65); font-size:0.85em; }
    .no-email { color:rgba(255,255,255,0.3); font-style:italic; font-size:0.95em; }

    /* flash message */
    .flash-msg {
      margin-bottom:20px; padding:14px 20px; border-radius:14px;
      font-size:0.88em; font-weight:500;
      opacity:0; animation:cardReveal 0.6s forwards 0.55s;
    }
    .flash-ok {
      background:rgba(212,175,55,0.14); border:1px solid rgba(212,175,55,0.35); color:#FFE4B5;
    }
    .flash-err {
      background:rgba(239,68,68,0.14); border:1px solid rgba(239,68,68,0.35); color:#fca5a5;
    }

    /* empty state */
    .empty-state {
      text-align:center; padding:60px 20px; color:rgba(255,255,255,0.4);
    }
    .empty-state .es-icon { font-size:3em; margin-bottom:16px; opacity:0.5; }
    .empty-state p { font-family:'Playfair Display',serif; font-size:1.2em; }

    /* particles */
    .particle { position:fixed; border-radius:50%; pointer-events:none; animation:particleFloat linear infinite; z-index:0; }
    @keyframes particleFloat {
      0%   { transform:translateY(100vh) scale(0); opacity:0; }
      10%  { opacity:0.5; }
      90%  { opacity:0.3; }
      100% { transform:translateY(-100px) scale(1); opacity:0; }
    }

    .back-link {
      display:inline-flex; align-items:center; gap:8px;
      padding:10px 22px;
      background:rgba(212,175,55,0.1);
      border:1px solid rgba(212,175,55,0.3);
      color:#D4AF37; text-decoration:none;
      font-size:0.82em; font-weight:600; letter-spacing:1px;
      border-radius:999px;
      margin-bottom:18px;
      transition:transform 0.25s, box-shadow 0.3s, background 0.3s;
      opacity:0; animation:cardReveal 0.6s forwards 0.55s;
    }
    .back-link:hover {
      transform:translateX(-3px);
      background:rgba(212,175,55,0.2);
      box-shadow:0 6px 20px rgba(212,175,55,0.25);
    }

    @media(max-width:768px){
      .stats-row { grid-template-columns:repeat(2,1fr); }
      .hero-inner h1 { font-size:2em; }
      .page-wrapper { padding:24px 16px 60px; }
      .action-cell { flex-direction:column; align-items:flex-start; }
    }

    /* ══════════════════ Modal Edit Akun ══════════════════ */
    .modal-overlay {
      position: fixed; inset: 0; z-index: 200;
      background: rgba(10,5,20,0.72);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      display: flex; align-items: center; justify-content: center;
      opacity: 0; pointer-events: none;
      transition: opacity 0.3s ease;
      padding: 20px;
    }
    .modal-overlay.show { opacity: 1; pointer-events: all; }

    .modal-box {
      width: 100%; max-width: 420px;
      background: rgba(255,255,255,0.06);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border: 1px solid rgba(255,255,255,0.14);
      border-radius: 24px;
      padding: 32px 30px;
      position: relative;
      overflow: hidden;
      transform: translateY(30px) scale(0.96);
      opacity: 0;
      transition: transform 0.4s cubic-bezier(0.34,1.56,0.64,1), opacity 0.35s ease;
      box-shadow: 0 30px 80px rgba(0,0,0,0.5), 0 0 40px rgba(99,102,241,0.15);
    }
    .modal-overlay.show .modal-box { transform: translateY(0) scale(1); opacity: 1; }

    .modal-box::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
      background: linear-gradient(90deg, #6366f1, #D4AF37, #6366f1);
      background-size: 300% 100%;
      animation: goldSlide 4s linear infinite;
    }

    .modal-title {
      font-family: 'Playfair Display', serif;
      font-size: 1.4em; font-weight: 700; color:#fff;
      margin-bottom: 4px;
    }
    .modal-sub {
      font-size: 0.82em; color: rgba(255,255,255,0.5);
      margin-bottom: 22px;
    }
    .modal-sub strong { color: #a5b4fc; }

    .modal-field { margin-bottom: 16px; }
    .modal-field label {
      display: block; font-size: 0.75em; font-weight: 600;
      letter-spacing: 1.5px; text-transform: uppercase;
      color: rgba(212,175,55,0.8); margin-bottom: 7px;
    }
    .modal-field input {
      width: 100%; padding: 12px 14px;
      background: rgba(255,255,255,0.07);
      border: 1px solid rgba(255,255,255,0.14);
      border-radius: 12px; color: #fff;
      font-family: 'Inter', sans-serif; font-size: 0.92em;
      outline: none;
      transition: border-color 0.3s, background 0.3s;
    }
    .modal-field input:focus {
      border-color: rgba(99,102,241,0.6);
      background: rgba(255,255,255,0.1);
      box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
    }
    .modal-field input::placeholder { color: rgba(255,255,255,0.3); }
    .modal-field .field-note {
      font-size: 0.72em; color: rgba(255,255,255,0.4); margin-top: 6px;
    }

    .modal-error {
      display: none; background: rgba(238,42,123,0.15);
      border: 1px solid rgba(238,42,123,0.35); border-radius: 10px;
      padding: 10px 14px; font-size: 0.8em; color: #ff8ab5;
      margin-bottom: 14px;
    }
    .modal-error.show { display: block; }

    .modal-actions { display: flex; gap: 10px; margin-top: 22px; }
    .btn-modal-cancel {
      flex: 1; padding: 12px; border-radius: 12px;
      background: none; border: 1px solid rgba(255,255,255,0.16);
      color: rgba(255,255,255,0.6); font-family:'Inter',sans-serif;
      font-size: 0.85em; font-weight: 500; cursor: pointer;
      transition: border-color 0.25s, color 0.25s;
    }
    .btn-modal-cancel:hover { border-color: rgba(255,255,255,0.3); color:#fff; }

    .btn-modal-save {
      flex: 1.4; padding: 12px; border-radius: 12px; border: none;
      background: linear-gradient(135deg, #6366f1 0%, #4f46e5 50%, #6366f1 100%);
      background-size: 200% 100%;
      color: #fff; font-family:'Inter',sans-serif;
      font-size: 0.85em; font-weight: 700; letter-spacing: 0.5px;
      cursor: pointer;
      box-shadow: 0 6px 20px rgba(99,102,241,0.35);
      transition: transform 0.25s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.3s;
    }
    .btn-modal-save:hover { transform: translateY(-2px); box-shadow: 0 10px 26px rgba(99,102,241,0.5); }
    .btn-modal-save:disabled { opacity: 0.6; cursor: not-allowed; transform:none; }

    /* Status overlay proses -> hasil, konsisten dengan halaman login/register */
    .status-overlay {
      position: fixed; inset: 0; z-index: 300;
      background: rgba(20,10,5,0.72);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      display: flex; align-items: center; justify-content: center;
      opacity: 0; pointer-events: none;
      transition: opacity 0.3s ease;
    }
    .status-overlay.show { opacity: 1; pointer-events: all; }
    .status-box {
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(255,255,255,0.15);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border-radius: 24px; padding: 42px 48px;
      display: flex; flex-direction: column; align-items: center; gap: 16px;
      min-width: 240px;
      transform: scale(0.85);
      transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
      box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    }
    .status-overlay.show .status-box { transform: scale(1); }

    .spinner {
      width: 54px; height: 54px; border-radius: 50%;
      border: 4px solid rgba(212,175,55,0.2); border-top-color: #D4AF37;
      animation: spin 0.8s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    .result-icon {
      width: 54px; height: 54px; border-radius: 50%;
      display: none; align-items: center; justify-content: center; position: relative;
    }
    .result-icon.success {
      display: flex; background: rgba(155,232,164,0.15); border: 2px solid #9be8a4;
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
      display: flex; background: rgba(238,42,123,0.15); border: 2px solid #ff8ab5;
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
  </style>
</head>
<body>

<div id="particles"></div>

<!-- HERO -->
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow"><i data-lucide="sparkle" class="lucide-ic"></i> YOLAZCAKE Sintang <i data-lucide="sparkle" class="lucide-ic"></i></p>
    <h1>Kelola Akun</h1>
    <p class="hero-sub">Atur akun staff: ubah peran atau hapus akun</p>
    <div class="hero-divider">
      <span></span><span class="diamond"><i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i> <i data-lucide="sparkle" class="lucide-ic"></i></span><span></span>
    </div>
  </div>
</div>

<div class="page-wrapper">

  <a href="../dashboard.php" class="back-link"><i data-lucide="arrow-left" class="lucide-ic"></i> Dashboard</a>

  <!-- top bar -->
  <div class="top-bar">
    <span class="section-eyebrow"><i data-lucide="sparkle" class="lucide-ic"></i> Daftar Akun</span>
    <button type="button" class="btn-tambah" onclick="bukaModalTambah()">+ Tambah Akun</button>
  </div>

  <?php if(isset($_GET['ok'])): ?>
    <div class="flash-msg flash-ok">
      <?php
        $msgs = [
          'role'   => '<i data-lucide="check" class="lucide-ic"></i> Peran akun berhasil diubah.',
          'hapus'  => '<i data-lucide="check" class="lucide-ic"></i> Akun berhasil dihapus.',
          'edit'   => '<i data-lucide="check" class="lucide-ic"></i> Data akun berhasil diperbarui.',
          'tambah' => '<i data-lucide="check" class="lucide-ic"></i> Akun baru berhasil dibuat.',
        ];
        echo $msgs[$_GET['ok']] ?? '<i data-lucide="check" class="lucide-ic"></i> Berhasil.';
      ?>
    </div>
  <?php elseif(isset($_GET['err'])): ?>
    <div class="flash-msg flash-err">
      <?php
        $errs = [
          'self'      => '<i data-lucide="x" class="lucide-ic"></i> Tidak bisa mengubah / menghapus akun yang sedang kamu pakai sendiri.',
          'last_admin'=> '<i data-lucide="x" class="lucide-ic"></i> Tidak bisa menghapus / menurunkan admin terakhir. Minimal harus ada 1 admin.',
          'notfound'  => '<i data-lucide="x" class="lucide-ic"></i> Akun tidak ditemukan.',
        ];
        echo $errs[$_GET['err']] ?? '<i data-lucide="x" class="lucide-ic"></i> Terjadi kesalahan.';
      ?>
    </div>
  <?php endif; ?>

  <!-- stats -->
  <div class="stats-row">
    <div class="stat-card">
      <span class="stat-icon"><i data-lucide="user" class="lucide-ic"></i></span>
      <div>
        <div class="stat-val"><?= $total_user; ?></div>
        <div class="stat-lbl">Total Akun</div>
      </div>
    </div>
    <div class="stat-card">
      <span class="stat-icon"><i data-lucide="crown" class="lucide-ic"></i></span>
      <div>
        <div class="stat-val"><?= $total_admin; ?></div>
        <div class="stat-lbl">Admin</div>
      </div>
    </div>
    <div class="stat-card">
      <span class="stat-icon"><i data-lucide="user-round" class="lucide-ic"></i>‍<i data-lucide="chef-hat" class="lucide-ic"></i></span>
      <div>
        <div class="stat-val"><?= $total_kasir; ?></div>
        <div class="stat-lbl">Kasir</div>
      </div>
    </div>
    <div class="stat-card">
      <span class="stat-icon"><i data-lucide="eye" class="lucide-ic"></i></span>
      <div>
        <div class="stat-val"><?= $total_pengunjung; ?></div>
        <div class="stat-lbl">Pengunjung</div>
      </div>
    </div>
  </div>

  <!-- table -->
  <div class="table-card">
    <table>
      <thead>
        <tr>
          <th style="text-align:center;">No</th>
          <th>Username</th>
          <th>Email</th>
          <th>Peran</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $no = 1;
        $rows = mysqli_num_rows($query);
        if($rows > 0):
          while($data = mysqli_fetch_assoc($query)):
            $is_me = (isset($_SESSION['username']) && $_SESSION['username'] === $data['username']);
        ?>
        <tr>
          <td class="td-no"><?= $no++; ?></td>
          <td class="td-nama">
            <?= htmlspecialchars($data['username']); ?>
            <?php if($is_me): ?><span class="you-tag">Kamu</span><?php endif; ?>
          </td>
          <td class="td-email"><?= !empty($data['email']) ? htmlspecialchars($data['email']) : '<span class="no-email">- belum ada -</span>'; ?></td>
          <td>
            <div class="role-select-wrap">
              <select class="role-select role-<?= htmlspecialchars($data['role']); ?>"
                      data-old-role="<?= htmlspecialchars($data['role']); ?>"
                      data-id="<?= (int)$data['id']; ?>"
                      data-username="<?= htmlspecialchars($data['username'], ENT_QUOTES); ?>"
                      <?= $is_me ? 'disabled' : ''; ?>
                      onchange="ubahPangkat(this)">
                <?php foreach($role_options as $rv => $ro): ?>
                  <option value="<?= $rv; ?>" <?= $data['role'] === $rv ? 'selected' : ''; ?>><?= $ro['icon']; ?> <?= $ro['label']; ?></option>
                <?php endforeach; ?>
              </select>
              <?php if($is_me): ?>
                <div class="role-locked"><i data-lucide="lock" class="lucide-ic"></i> Akun sendiri</div>
              <?php endif; ?>
            </div>
          </td>
          <td>
            <div class="action-cell">
              <button type="button"
                 class="btn-act btn-editakun"
                 onclick="bukaModalEdit(<?= (int)$data['id']; ?>, <?= htmlspecialchars(json_encode($data['username']), ENT_QUOTES); ?>, <?= htmlspecialchars(json_encode($data['email'] ?? ''), ENT_QUOTES); ?>)"><i data-lucide="pencil" class="lucide-ic"></i> Edit</button>

              <button type="button"
                 class="btn-act btn-hapus"
                 <?= $is_me ? 'disabled' : ''; ?>
                 onclick="hapusAkun(<?= (int)$data['id']; ?>, <?= htmlspecialchars(json_encode($data['username']), ENT_QUOTES); ?>)"><i data-lucide="trash-2" class="lucide-ic"></i> Hapus</button>
            </div>
          </td>
        </tr>
        <?php endwhile; else: ?>
        <tr>
          <td colspan="5">
            <div class="empty-state">
              <div class="es-icon"><i data-lucide="user" class="lucide-ic"></i></div>
              <p>Belum ada akun</p>
            </div>
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>

<!-- Modal Edit Akun -->
<div class="modal-overlay" id="modalOverlay">
  <div class="modal-box">
    <div class="modal-title">Edit Akun</div>
    <div class="modal-sub">Ubah email dan/atau reset password untuk <strong id="modalUsername">-</strong></div>

    <div class="modal-error" id="modalError"><span id="modalErrorText"></span></div>

    <form id="formEditAkun" novalidate>
      <input type="hidden" id="editId" name="id">

      <div class="modal-field">
        <label for="editEmail">Email Gmail</label>
        <input type="email" id="editEmail" name="email" placeholder="contoh: nama@gmail.com" autocomplete="off">
        <div class="field-note">Dipakai untuk login via Gmail &amp; fitur lupa password.</div>
      </div>

      <div class="modal-field">
        <label for="editPassword">Password Baru (opsional)</label>
        <input type="password" id="editPassword" name="password" placeholder="Kosongkan jika tidak ingin ubah password" autocomplete="new-password">
      </div>

      <div class="modal-field">
        <label for="editConfirmPassword">Konfirmasi Password Baru</label>
        <input type="password" id="editConfirmPassword" name="confirm_password" placeholder="Ulangi password baru" autocomplete="new-password">
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-modal-cancel" id="btnModalCancel">Batal</button>
        <button type="submit" class="btn-modal-save" id="btnModalSave"><i data-lucide="save" class="lucide-ic"></i> Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Tambah Akun -->
<div class="modal-overlay" id="modalTambahOverlay">
  <div class="modal-box">
    <div class="modal-title">Tambah Akun</div>
    <div class="modal-sub">Buat akun baru untuk Admin, Kasir, atau Pengunjung</div>

    <div class="modal-error" id="modalTambahError"><span id="modalTambahErrorText"></span></div>

    <form id="formTambahAkun" novalidate>
      <div class="modal-field">
        <label for="tambahUsername">Username</label>
        <input type="text" id="tambahUsername" name="username" placeholder="mis: kasir_baru" autocomplete="off" required>
      </div>

      <div class="modal-field">
        <label for="tambahEmail">Email Gmail (opsional)</label>
        <input type="email" id="tambahEmail" name="email" placeholder="contoh: nama@gmail.com" autocomplete="off">
      </div>

      <div class="modal-field">
        <label for="tambahPassword">Password</label>
        <input type="password" id="tambahPassword" name="password" placeholder="Minimal 6 karakter" autocomplete="new-password" required>
      </div>

      <div class="modal-field">
        <label for="tambahRole">Peran</label>
        <select id="tambahRole" name="role" required>
          <?php foreach($role_options as $rv => $ro): ?>
            <option value="<?= $rv; ?>"><?= $ro['icon']; ?> <?= $ro['label']; ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-modal-cancel" id="btnModalTambahCancel">Batal</button>
        <button type="submit" class="btn-modal-save" id="btnModalTambahSave"><i data-lucide="sparkle" class="lucide-ic"></i> Buat Akun</button>
      </div>
    </form>
  </div>
</div>

<!-- Status overlay: proses -> selesai -> hasil (konsisten dengan login/register) -->
<div class="status-overlay" id="statusOverlay">
  <div class="status-box">
    <div class="spinner" id="statusSpinner"></div>
    <div class="result-icon" id="statusResultIcon"></div>
    <div class="status-text" id="statusText">Memproses...</div>
    <div class="status-sub" id="statusSub"></div>
  </div>
</div>

<script>
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

  /* ══════════════════ Modal Edit Akun ══════════════════ */
  const modalOverlay   = document.getElementById('modalOverlay');
  const modalUsername   = document.getElementById('modalUsername');
  const formEditAkun    = document.getElementById('formEditAkun');
  const editIdInput     = document.getElementById('editId');
  const editEmailInput  = document.getElementById('editEmail');
  const editPasswordInput        = document.getElementById('editPassword');
  const editConfirmPasswordInput = document.getElementById('editConfirmPassword');
  const modalError      = document.getElementById('modalError');
  const modalErrorText  = document.getElementById('modalErrorText');
  const btnModalSave    = document.getElementById('btnModalSave');

  function bukaModalEdit(id, username, email) {
    editIdInput.value = id;
    modalUsername.textContent = username;
    editEmailInput.value = email || '';
    editPasswordInput.value = '';
    editConfirmPasswordInput.value = '';
    hideModalError();
    modalOverlay.classList.add('show');
    setTimeout(() => editEmailInput.focus(), 300);
  }

  function tutupModalEdit() {
    modalOverlay.classList.remove('show');
  }

  function showModalError(msg) {
    modalErrorText.textContent = msg;
    modalError.classList.add('show');
  }
  function hideModalError() {
    modalError.classList.remove('show');
  }

  document.getElementById('btnModalCancel').addEventListener('click', tutupModalEdit);
  modalOverlay.addEventListener('click', (e) => {
    if (e.target === modalOverlay) tutupModalEdit();
  });

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

  formEditAkun.addEventListener('submit', async function (e) {
    e.preventDefault();
    hideModalError();

    const id    = editIdInput.value;
    const email = editEmailInput.value.trim();
    const pass  = editPasswordInput.value;
    const conf  = editConfirmPasswordInput.value;

    if (email !== '' && !/^[^\s@]+@gmail\.com$/i.test(email)) {
      showModalError('Email harus berupa alamat @gmail.com yang valid.');
      return;
    }
    if (pass !== '' || conf !== '') {
      if (pass.length < 6) { showModalError('Password baru minimal 6 karakter.'); return; }
      if (pass !== conf)   { showModalError('Konfirmasi password baru tidak sama.'); return; }
    }

    btnModalSave.disabled = true;
    tutupModalEdit();
    showProcessing('Menyimpan...', 'Sedang memperbarui data akun');

    try {
      const res = await fetch('edit_user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams({ id, email, password: pass, confirm_password: conf })
      });
      const data = await res.json();

      if (data.success) {
        showResult(true, 'Berhasil Disimpan!', 'Mengalihkan halaman...');
        setTimeout(() => {
          window.location.href = data.redirect || 'data_user.php?ok=edit';
        }, 1200);
      } else {
        showResult(false, 'Gagal Menyimpan', data.message || 'Terjadi kesalahan.');
        setTimeout(() => {
          hideOverlay();
          btnModalSave.disabled = false;
          modalOverlay.classList.add('show');
          showModalError(data.message || 'Terjadi kesalahan. Coba lagi.');
        }, 1500);
      }
    } catch (err) {
      showResult(false, 'Terjadi Kesalahan', 'Gagal terhubung ke server. Coba lagi.');
      setTimeout(() => {
        hideOverlay();
        btnModalSave.disabled = false;
        modalOverlay.classList.add('show');
      }, 1500);
    }
  });

  /* ══════════════════ Modal Tambah Akun ══════════════════ */
  const modalTambahOverlay  = document.getElementById('modalTambahOverlay');
  const formTambahAkun      = document.getElementById('formTambahAkun');
  const tambahUsernameInput = document.getElementById('tambahUsername');
  const tambahEmailInput    = document.getElementById('tambahEmail');
  const tambahPasswordInput = document.getElementById('tambahPassword');
  const tambahRoleInput     = document.getElementById('tambahRole');
  const modalTambahError    = document.getElementById('modalTambahError');
  const modalTambahErrorText = document.getElementById('modalTambahErrorText');
  const btnModalTambahSave  = document.getElementById('btnModalTambahSave');

  function bukaModalTambah() {
    formTambahAkun.reset();
    hideModalTambahError();
    modalTambahOverlay.classList.add('show');
    setTimeout(() => tambahUsernameInput.focus(), 300);
  }

  function tutupModalTambah() {
    modalTambahOverlay.classList.remove('show');
  }

  function showModalTambahError(msg) {
    modalTambahErrorText.textContent = msg;
    modalTambahError.classList.add('show');
  }
  function hideModalTambahError() {
    modalTambahError.classList.remove('show');
  }

  document.getElementById('btnModalTambahCancel').addEventListener('click', tutupModalTambah);
  modalTambahOverlay.addEventListener('click', (e) => {
    if (e.target === modalTambahOverlay) tutupModalTambah();
  });

  formTambahAkun.addEventListener('submit', async function (e) {
    e.preventDefault();
    hideModalTambahError();

    const username = tambahUsernameInput.value.trim();
    const email    = tambahEmailInput.value.trim();
    const password = tambahPasswordInput.value;
    const role     = tambahRoleInput.value;

    if (username.length < 3) {
      showModalTambahError('Username minimal 3 karakter.');
      return;
    }
    if (email !== '' && !/^[^\s@]+@gmail\.com$/i.test(email)) {
      showModalTambahError('Email harus berupa alamat @gmail.com yang valid.');
      return;
    }
    if (password.length < 6) {
      showModalTambahError('Password minimal 6 karakter.');
      return;
    }

    btnModalTambahSave.disabled = true;
    tutupModalTambah();
    showProcessing('Menyimpan...', 'Sedang membuat akun baru');

    try {
      const res = await fetch('tambah_user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams({ username, email, password, role })
      });
      const data = await res.json();

      if (data.success) {
        showResult(true, 'Akun Berhasil Dibuat!', 'Mengalihkan halaman...');
        setTimeout(() => {
          window.location.href = data.redirect || 'data_user.php?ok=tambah';
        }, 1200);
      } else {
        showResult(false, 'Gagal Membuat Akun', data.message || 'Terjadi kesalahan.');
        setTimeout(() => {
          hideOverlay();
          btnModalTambahSave.disabled = false;
          modalTambahOverlay.classList.add('show');
          showModalTambahError(data.message || 'Terjadi kesalahan. Coba lagi.');
        }, 1500);
      }
    } catch (err) {
      showResult(false, 'Terjadi Kesalahan', 'Gagal terhubung ke server. Coba lagi.');
      setTimeout(() => {
        hideOverlay();
        btnModalTambahSave.disabled = false;
        modalTambahOverlay.classList.add('show');
      }, 1500);
    }
  });

  /* ══════════════════ Ubah Pangkat (dropdown) ══════════════════ */
  const roleLabels = {
    admin: 'Admin',
    kasir: 'Kasir',
    pengunjung: 'Pengunjung'
  };

  function ubahPangkat(selectEl) {
    const id       = selectEl.dataset.id;
    const username = selectEl.dataset.username;
    const oldRole  = selectEl.dataset.oldRole;
    const newRole  = selectEl.value;

    if (newRole === oldRole) return;

    const labelBaru = roleLabels[newRole] || newRole;
    const ok = confirm(`Ubah pangkat "${username}" menjadi ${labelBaru}?`);
    if (!ok) {
      selectEl.value = oldRole;
      return;
    }

    selectEl.disabled = true;
    showProcessing('Mengubah Pangkat...', `Menjadikan ${username} sebagai ${labelBaru}`);

    fetch('ubah_role.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
      body: new URLSearchParams({ id, role: newRole })
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          selectEl.dataset.oldRole = newRole;
          selectEl.classList.remove('role-admin', 'role-kasir', 'role-pengunjung');
          selectEl.classList.add('role-' + newRole);
          showResult(true, 'Pangkat Diubah!', `${username} kini menjadi ${labelBaru}`);
          setTimeout(() => {
            window.location.href = 'data_user.php?ok=role';
          }, 1200);
        } else {
          selectEl.value = oldRole;
          selectEl.disabled = false;
          showResult(false, 'Gagal Mengubah', data.message || 'Terjadi kesalahan.');
          setTimeout(hideOverlay, 1800);
        }
      })
      .catch(() => {
        selectEl.value = oldRole;
        selectEl.disabled = false;
        showResult(false, 'Terjadi Kesalahan', 'Gagal terhubung ke server. Coba lagi.');
        setTimeout(hideOverlay, 1800);
      });
  }

  /* ══════════════════ Hapus Akun ══════════════════ */
  function hapusAkun(id, username) {
    const ok = confirm(`Yakin ingin menghapus akun "${username}"? Tindakan ini tidak bisa dibatalkan.`);
    if (!ok) return;

    showProcessing('Menghapus Akun...', `Menghapus data akun ${username}`);

    fetch('hapus_user.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
      body: new URLSearchParams({ id })
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          showResult(true, 'Akun Terhapus!', `"${username}" telah dihapus dari daftar`);
          setTimeout(() => {
            window.location.href = 'data_user.php?ok=hapus';
          }, 1200);
        } else {
          showResult(false, 'Gagal Menghapus', data.message || 'Terjadi kesalahan.');
          setTimeout(hideOverlay, 1800);
        }
      })
      .catch(() => {
        showResult(false, 'Terjadi Kesalahan', 'Gagal terhubung ke server. Coba lagi.');
        setTimeout(hideOverlay, 1800);
      });
  }
</script>

<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide){lucide.createIcons();}</script>
</body>
</html>
