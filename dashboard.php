<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location: auth/login.php");
    exit();
}
require_once 'config/koneksi.php';

/* ── STATS ── */
$s_booking   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t, SUM(status='Pending') AS p FROM booking"));
$s_pesanan   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t, SUM(status_pesanan='Menunggu') AS p FROM pemesanan"));
$s_produk    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t FROM produk"));
$s_member    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t FROM member"));

/* ── RECENT BOOKING (5) ── */
$q_booking = mysqli_query($conn,"SELECT * FROM booking ORDER BY created_at DESC LIMIT 5");

/* ── RECENT PEMESANAN (5) ── */
$q_pesanan = mysqli_query($conn,"SELECT * FROM pemesanan ORDER BY tanggal DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin – YOLAZCAKE</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
/* ════════════════════════════════════════════
   RESET & ROOT
════════════════════════════════════════════ */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
:root{
  --gold:#D4AF37;
  --gold-l:#FFE88A;
  --gold-d:#A07C10;
  --rose:#EE2A7B;
  --purple:#8A2BE2;
  --bg1:#0d0520;
  --bg2:#1a0a3a;
  --bg3:#150830;
  --glass:rgba(255,255,255,0.045);
  --glass-h:rgba(255,255,255,0.08);
  --gb:rgba(255,255,255,0.10);
  --text:#ffffff;
  --muted:rgba(255,255,255,0.50);
  --sidebar-w:260px;
}

html{scroll-behavior:smooth;}

body{
  min-height:100vh;
  font-family:'Inter',sans-serif;
  background:linear-gradient(140deg,var(--bg1) 0%,var(--bg2) 50%,var(--bg3) 100%);
  color:var(--text);
  overflow-x:hidden;
}

/* ── AMBIENT GLOW ── */
body::before{
  content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
  background:
    radial-gradient(ellipse 60% 50% at 15% 25%,rgba(212,175,55,.1) 0%,transparent 70%),
    radial-gradient(ellipse 50% 60% at 85% 70%,rgba(138,43,226,.08) 0%,transparent 70%),
    radial-gradient(ellipse 40% 40% at 60% 10%,rgba(238,42,123,.06) 0%,transparent 60%);
  animation:ambientShift 14s ease-in-out infinite alternate;
}
@keyframes ambientShift{0%{opacity:.6;transform:scale(1);}100%{opacity:1;transform:scale(1.05) translate(8px,-8px);}}

/* ════════════════════════════════════════════
   LAYOUT WRAPPER
════════════════════════════════════════════ */
.admin-layout{
  display:flex;
  min-height:100vh;
  position:relative;
  z-index:1;
}

/* ════════════════════════════════════════════
   SIDEBAR
════════════════════════════════════════════ */
.sidebar{
  width:var(--sidebar-w);
  flex-shrink:0;
  position:fixed;
  top:0;left:0;
  height:100vh;
  display:flex;
  flex-direction:column;
  background:rgba(13,5,32,0.92);
  backdrop-filter:blur(28px) saturate(1.4);
  border-right:1px solid rgba(212,175,55,0.15);
  z-index:100;
  overflow:hidden;
  transition:transform .35s cubic-bezier(.22,.68,0,1.2);
}

/* top glow line */
.sidebar::before{
  content:'';position:absolute;top:0;left:0;right:0;height:3px;
  background:linear-gradient(90deg,#D4AF37,#EE2A7B,#8A2BE2,#D4AF37);
  background-size:300% 100%;
  animation:goldSlide 5s linear infinite;
}

/* ambient orb inside sidebar */
.sidebar::after{
  content:'';
  position:absolute;bottom:-80px;left:-80px;
  width:260px;height:260px;
  background:radial-gradient(circle,rgba(212,175,55,.07) 0%,transparent 65%);
  border-radius:50%;pointer-events:none;
}

/* brand */
.sb-brand{
  padding:28px 24px 22px;
  border-bottom:1px solid rgba(255,255,255,.07);
  position:relative;
}

.sb-label{
  font-size:.62em;font-weight:700;letter-spacing:4px;text-transform:uppercase;
  color:rgba(212,175,55,.6);margin-bottom:12px;
}

.sb-logo{
  display:flex;align-items:center;gap:12px;text-decoration:none;
}
.sb-logo img{
  width:42px;height:42px;border-radius:50%;object-fit:cover;
  border:2px solid rgba(212,175,55,.45);
  box-shadow:0 0 18px rgba(212,175,55,.35);
}
.sb-logo-text{
  font-family:'Playfair Display',serif;
  font-size:1.2em;font-weight:700;
  background:linear-gradient(135deg,#D4AF37 30%,#FFE88A 70%);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}

.sb-badge{
  margin-top:10px;
  display:inline-flex;align-items:center;gap:6px;
  padding:4px 14px;
  background:linear-gradient(135deg,rgba(212,175,55,.18),rgba(212,175,55,.06));
  border:1px solid rgba(212,175,55,.35);
  border-radius:999px;
  font-size:.68em;font-weight:700;letter-spacing:2px;text-transform:uppercase;
  color:var(--gold);
  position:relative;overflow:hidden;
}
.sb-badge::before{
  content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;
  background:linear-gradient(90deg,transparent,rgba(212,175,55,.25),transparent);
  animation:shimmer 2.5s linear infinite;
}
@keyframes shimmer{to{left:200%;}}

/* nav scroll area */
.sb-nav{
  flex:1;overflow-y:auto;padding:20px 0;
  scrollbar-width:none;
}
.sb-nav::-webkit-scrollbar{display:none;}

.sb-section-label{
  font-size:.60em;font-weight:700;letter-spacing:3px;text-transform:uppercase;
  color:rgba(255,255,255,.28);
  padding:14px 24px 6px;
  display:flex;align-items:center;gap:8px;
}
.sb-section-label::after{
  content:'';flex:1;height:1px;
  background:linear-gradient(to right,rgba(255,255,255,.1),transparent);
}

.sb-link{
  display:flex;align-items:center;gap:12px;
  padding:11px 24px;
  color:rgba(255,255,255,.65);
  font-size:.875em;font-weight:500;
  text-decoration:none;
  border-left:3px solid transparent;
  transition:background .25s,color .25s,border-color .25s,padding-left .2s;
  position:relative;
}
.sb-link:hover{
  background:rgba(212,175,55,.08);
  color:#fff;
  border-left-color:rgba(212,175,55,.4);
  padding-left:28px;
}
.sb-link.active{
  background:rgba(212,175,55,.12);
  color:var(--gold);
  border-left-color:var(--gold);
  font-weight:600;
}
.sb-link.active .sb-link-icon{
  filter:drop-shadow(0 0 6px rgba(212,175,55,.6));
}

.sb-link-icon{font-size:1.1em;width:20px;text-align:center;flex-shrink:0;}

.sb-link-badge{
  margin-left:auto;
  background:linear-gradient(135deg,#EE2A7B,#D4AF37);
  color:#fff;font-size:.65em;font-weight:700;
  padding:2px 8px;border-radius:999px;min-width:20px;text-align:center;
  box-shadow:0 2px 10px rgba(238,42,123,.4);
}

.sb-link-new{
  margin-left:auto;
  background:rgba(46,213,115,.15);
  border:1px solid rgba(46,213,115,.35);
  color:#2ed573;font-size:.62em;font-weight:700;
  padding:2px 8px;border-radius:999px;
}

/* add-type nav links */
.sb-link.add-link{
  color:rgba(212,175,55,.75);
}
.sb-link.add-link:hover{
  color:var(--gold);
  background:rgba(212,175,55,.1);
}

/* footer of sidebar */
.sb-footer{
  padding:16px 16px 24px;
  border-top:1px solid rgba(255,255,255,.07);
}

.sb-logout{
  display:flex;align-items:center;justify-content:center;gap:10px;
  padding:12px;
  background:rgba(238,42,123,.1);
  border:1px solid rgba(238,42,123,.28);
  border-radius:14px;
  color:#EE2A7B;
  font-size:.84em;font-weight:600;
  text-decoration:none;
  transition:background .25s,box-shadow .3s,transform .2s;
}
.sb-logout:hover{
  background:rgba(238,42,123,.2);
  box-shadow:0 6px 20px rgba(238,42,123,.25);
  transform:translateY(-2px);
}

/* ════════════════════════════════════════════
   MAIN CONTENT
════════════════════════════════════════════ */
.main-content{
  flex:1;
  margin-left:var(--sidebar-w);
  min-height:100vh;
  display:flex;
  flex-direction:column;
}

/* ── TOPBAR ── */
.topbar{
  position:sticky;top:0;z-index:50;
  display:flex;align-items:center;justify-content:space-between;
  padding:16px 32px;
  background:rgba(13,5,32,.88);
  backdrop-filter:blur(24px) saturate(1.3);
  border-bottom:1px solid rgba(212,175,55,.12);
  box-shadow:0 4px 30px rgba(0,0,0,.35);
}

.topbar-left{
  display:flex;align-items:center;gap:16px;
}

.hamburger-btn{
  display:none;
  background:rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.12);
  border-radius:10px;padding:8px 10px;
  cursor:pointer;font-size:1.1em;color:rgba(255,255,255,.8);
  transition:background .2s;
}
.hamburger-btn:hover{background:rgba(212,175,55,.15);color:var(--gold);}

.topbar-title{
  font-family:'Playfair Display',serif;
  font-size:1.15em;font-weight:700;
  background:linear-gradient(135deg,#fff 40%,#D4AF37 80%);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}

.topbar-right{
  display:flex;align-items:center;gap:12px;
}

.topbar-time{
  font-size:.78em;color:var(--muted);letter-spacing:.5px;
}

.topbar-user{
  display:flex;align-items:center;gap:10px;
  padding:8px 16px;
  background:rgba(212,175,55,.08);
  border:1px solid rgba(212,175,55,.22);
  border-radius:999px;
}
.topbar-avatar{
  width:30px;height:30px;border-radius:50%;
  background:linear-gradient(135deg,#D4AF37,#EE2A7B);
  display:flex;align-items:center;justify-content:center;
  font-size:.8em;font-weight:700;flex-shrink:0;
}
.topbar-username{
  font-size:.82em;font-weight:600;color:var(--gold);
}

.btn-website{
  display:inline-flex;align-items:center;gap:8px;
  padding:9px 18px;
  background:rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.14);
  border-radius:10px;
  color:rgba(255,255,255,.8);
  font-size:.8em;font-weight:600;
  text-decoration:none;
  transition:background .25s,color .25s,border-color .25s,transform .2s;
}
.btn-website:hover{
  background:rgba(212,175,55,.14);
  color:var(--gold);border-color:rgba(212,175,55,.35);
  transform:translateY(-1px);
}

/* ── PAGE BODY ── */
.page-body{
  flex:1;
  padding:36px 32px 60px;
}

/* ── HERO BANNER ── */
.dash-hero{
  position:relative;
  border-radius:28px;
  padding:44px 48px;
  margin-bottom:32px;
  overflow:hidden;
  background:linear-gradient(135deg,#1a0533 0%,#2e1060 40%,#1e0a44 70%,#0d0520 100%);
  border:1px solid rgba(212,175,55,.15);
  animation:fadeUp .8s forwards .1s;opacity:0;
}
.dash-hero::before{
  content:'';position:absolute;inset:0;
  background:
    radial-gradient(ellipse 70% 60% at 20% 60%,rgba(212,175,55,.18) 0%,transparent 55%),
    radial-gradient(ellipse 50% 65% at 80% 30%,rgba(238,42,123,.16) 0%,transparent 55%),
    radial-gradient(ellipse 40% 50% at 55% 90%,rgba(138,43,226,.1) 0%,transparent 55%);
  animation:heroMesh 10s ease-in-out infinite alternate;
}
@keyframes heroMesh{0%{opacity:.7;transform:scale(1);}100%{opacity:1;transform:scale(1.08) rotate(1deg);}}

.dash-hero-grid{
  position:absolute;inset:0;pointer-events:none;
  background-image:
    linear-gradient(rgba(212,175,55,.03) 1px,transparent 1px),
    linear-gradient(90deg,rgba(212,175,55,.03) 1px,transparent 1px);
  background-size:50px 50px;
  animation:gridDrift 18s linear infinite;
}
@keyframes gridDrift{from{background-position:0 0;}to{background-position:50px 50px;}}

.dash-hero-inner{position:relative;z-index:2;}

.dash-eyebrow{
  display:inline-flex;align-items:center;gap:8px;
  font-size:.68em;font-weight:700;letter-spacing:4px;text-transform:uppercase;
  color:var(--gold);margin-bottom:14px;
}
.dash-eyebrow::before{content:'✦';}

.dash-hero h1{
  font-family:'Playfair Display',serif;
  font-size:2.4em;font-weight:700;line-height:1.15;
  margin-bottom:10px;
}
.dash-hero h1 span{
  background:linear-gradient(135deg,#fff 0%,#D4AF37 35%,#FFE88A 55%,#EE2A7B 80%,#fff 100%);
  background-size:300% 100%;
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  animation:heroShimmer 5s ease-in-out infinite;
}
@keyframes heroShimmer{0%,100%{background-position:0% 0;}50%{background-position:100% 0;}}

.dash-hero-sub{
  font-size:.9em;color:rgba(255,255,255,.55);
  display:flex;align-items:center;gap:10px;
}
.dash-hero-sub span{
  display:inline-flex;align-items:center;gap:5px;
  background:rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.1);
  border-radius:999px;padding:4px 14px;
  font-size:.9em;
}

/* decorative number */
.dash-hero-deco{
  position:absolute;right:48px;top:50%;transform:translateY(-50%);
  font-family:'Playfair Display',serif;
  font-size:9em;font-weight:700;line-height:1;
  background:linear-gradient(135deg,rgba(212,175,55,.12),rgba(212,175,55,.03));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  pointer-events:none;user-select:none;
  z-index:1;
}

/* ── STATS GRID ── */
.stats-grid{
  display:grid;
  grid-template-columns:repeat(4,1fr);
  gap:18px;
  margin-bottom:32px;
}

.stat-card{
  position:relative;
  background:var(--glass);
  backdrop-filter:blur(20px);
  border:1px solid var(--gb);
  border-radius:22px;padding:26px 22px;
  display:flex;flex-direction:column;gap:8px;
  overflow:hidden;
  opacity:0;
  transition:border-color .35s,box-shadow .35s,transform .3s;
  cursor:default;
}
.stat-card.s1{animation:fadeUp .7s forwards .25s;--sc:#D4AF37;--sg:rgba(212,175,55,.2);}
.stat-card.s2{animation:fadeUp .7s forwards .35s;--sc:#EE2A7B;--sg:rgba(238,42,123,.2);}
.stat-card.s3{animation:fadeUp .7s forwards .45s;--sc:#8A2BE2;--sg:rgba(138,43,226,.2);}
.stat-card.s4{animation:fadeUp .7s forwards .55s;--sc:#2ed573;--sg:rgba(46,213,115,.2);}

.stat-card::before{
  content:'';position:absolute;bottom:0;left:0;right:0;height:3px;
  background:var(--sc,#D4AF37);
  transform:scaleX(0);transform-origin:left;
  transition:transform .4s ease;
}
.stat-card:hover::before{transform:scaleX(1);}
.stat-card:hover{
  background:var(--glass-h);
  border-color:rgba(212,175,55,.25);
  box-shadow:0 12px 40px rgba(0,0,0,.3),0 0 30px var(--sg,rgba(212,175,55,.15));
  transform:translateY(-4px);
}

.stat-icon-wrap{
  width:44px;height:44px;border-radius:13px;flex-shrink:0;
  background:rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.1);
  display:flex;align-items:center;justify-content:center;font-size:1.3em;
  margin-bottom:4px;
}

.stat-val{
  font-family:'Playfair Display',serif;
  font-size:2.2em;font-weight:700;
  color:var(--sc,#D4AF37);line-height:1;
}
.stat-lbl{font-size:.72em;color:var(--muted);letter-spacing:1px;text-transform:uppercase;}

.stat-badge{
  margin-top:4px;
  display:inline-flex;align-items:center;gap:4px;
  font-size:.68em;font-weight:700;color:#EE2A7B;
  background:rgba(238,42,123,.12);
  border:1px solid rgba(238,42,123,.3);
  border-radius:999px;padding:2px 10px;
  width:fit-content;
}
.stat-badge.ok{color:#2ed573;background:rgba(46,213,115,.1);border-color:rgba(46,213,115,.3);}

/* ── SECTION TITLE ── */
.section-hd{
  display:flex;align-items:center;gap:12px;
  margin-bottom:20px;
}
.section-hd-line{
  flex:1;height:1px;
  background:linear-gradient(to right,rgba(212,175,55,.25),transparent);
}
.section-hd h2{
  font-family:'Playfair Display',serif;
  font-size:1.15em;font-weight:700;color:var(--text);white-space:nowrap;
}
.section-hd .sh-icon{
  width:36px;height:36px;border-radius:10px;
  background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.25);
  display:flex;align-items:center;justify-content:center;font-size:1em;
}

/* ── MANAGEMENT CARDS GRID ── */
.mgmt-grid{
  display:grid;
  grid-template-columns:repeat(2,1fr);
  gap:20px;
  margin-bottom:32px;
}

.mgmt-card{
  position:relative;
  background:var(--glass);
  backdrop-filter:blur(22px);
  border:1px solid var(--gb);
  border-radius:24px;
  padding:32px 30px;
  overflow:hidden;
  opacity:0;
  transition:border-color .35s,box-shadow .35s,transform .3s,background .3s;
}
.mgmt-card.m1{animation:fadeUp .7s forwards .35s;}
.mgmt-card.m2{animation:fadeUp .7s forwards .45s;}
.mgmt-card.m3{animation:fadeUp .7s forwards .55s;}
.mgmt-card.m4{animation:fadeUp .7s forwards .65s;}

/* animated top border */
.mgmt-card::before{
  content:'';position:absolute;top:0;left:0;right:0;height:3px;
  background:linear-gradient(90deg,#D4AF37,#EE2A7B,#8A2BE2,#D4AF37);
  background-size:300% 100%;
  animation:goldSlide 5s linear infinite;
}

/* subtle corner glow */
.mgmt-card::after{
  content:'';
  position:absolute;top:-60px;right:-60px;
  width:180px;height:180px;
  background:radial-gradient(circle,var(--mc-glow,rgba(212,175,55,.08)) 0%,transparent 65%);
  border-radius:50%;pointer-events:none;
  transition:transform .4s;
}
.mgmt-card:hover::after{transform:scale(1.3);}

.mgmt-card:hover{
  background:var(--glass-h);
  border-color:rgba(212,175,55,.25);
  box-shadow:0 16px 48px rgba(0,0,0,.35),0 0 40px rgba(212,175,55,.1);
  transform:translateY(-5px);
}

.mgmt-card.booking{--mc-glow:rgba(212,175,55,.1);}
.mgmt-card.pemesanan{--mc-glow:rgba(238,42,123,.08);}
.mgmt-card.produk{--mc-glow:rgba(138,43,226,.08);}
.mgmt-card.member{--mc-glow:rgba(46,213,115,.08);}

.mc-top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:18px;}

.mc-icon{
  width:52px;height:52px;border-radius:16px;
  display:flex;align-items:center;justify-content:center;font-size:1.5em;
  background:rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.1);
  box-shadow:0 4px 20px rgba(0,0,0,.2);
}

.mc-count{
  font-family:'Playfair Display',serif;
  font-size:2.6em;font-weight:700;
  background:linear-gradient(135deg,#D4AF37,#FFE88A);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  line-height:1;
}

.mc-title{
  font-family:'Playfair Display',serif;
  font-size:1.25em;font-weight:700;margin-bottom:8px;
}

.mc-desc{font-size:.82em;color:var(--muted);line-height:1.6;margin-bottom:22px;}

.mc-actions{display:flex;flex-wrap:wrap;gap:10px;}

.btn-primary{
  display:inline-flex;align-items:center;gap:7px;
  padding:10px 20px;
  background:rgba(212,175,55,.12);
  border:1px solid rgba(212,175,55,.35);
  border-radius:10px;
  color:var(--gold);
  font-size:.8em;font-weight:600;
  text-decoration:none;
  transition:background .25s,box-shadow .3s,transform .2s;
  position:relative;overflow:hidden;
}
.btn-primary::before{
  content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;
  background:linear-gradient(90deg,transparent,rgba(212,175,55,.15),transparent);
  transition:left .35s;
}
.btn-primary:hover::before{left:100%;}
.btn-primary:hover{
  background:rgba(212,175,55,.22);
  box-shadow:0 6px 22px rgba(212,175,55,.3);
  transform:translateY(-2px);
}

.btn-secondary{
  display:inline-flex;align-items:center;gap:7px;
  padding:10px 20px;
  background:rgba(255,255,255,.05);
  border:1px solid rgba(255,255,255,.12);
  border-radius:10px;
  color:rgba(255,255,255,.75);
  font-size:.8em;font-weight:600;
  text-decoration:none;
  transition:background .25s,color .25s,border-color .25s,transform .2s;
}
.btn-secondary:hover{
  background:rgba(255,255,255,.1);
  color:#fff;
  border-color:rgba(255,255,255,.25);
  transform:translateY(-2px);
}

.btn-accent{
  display:inline-flex;align-items:center;gap:7px;
  padding:10px 20px;
  background:rgba(238,42,123,.1);
  border:1px solid rgba(238,42,123,.3);
  border-radius:10px;
  color:#EE2A7B;
  font-size:.8em;font-weight:600;
  text-decoration:none;
  transition:background .25s,box-shadow .3s,transform .2s;
}
.btn-accent:hover{
  background:rgba(238,42,123,.2);
  box-shadow:0 6px 22px rgba(238,42,123,.28);
  transform:translateY(-2px);
}

.btn-add{
  display:inline-flex;align-items:center;gap:7px;
  padding:10px 20px;
  background:rgba(46,213,115,.08);
  border:1px solid rgba(46,213,115,.28);
  border-radius:10px;
  color:#2ed573;
  font-size:.8em;font-weight:600;
  text-decoration:none;
  transition:background .25s,box-shadow .3s,transform .2s;
}
.btn-add:hover{
  background:rgba(46,213,115,.18);
  box-shadow:0 6px 22px rgba(46,213,115,.25);
  transform:translateY(-2px);
}

.pending-tag{
  display:inline-flex;align-items:center;gap:5px;
  padding:3px 10px;
  background:rgba(238,42,123,.15);
  border:1px solid rgba(238,42,123,.35);
  border-radius:999px;
  font-size:.68em;font-weight:700;color:#EE2A7B;
  margin-top:4px;
}

/* ── ACTIVITY SECTION ── */
.activity-grid{
  display:grid;
  grid-template-columns:repeat(2,1fr);
  gap:20px;
  margin-bottom:32px;
}

.activity-card{
  position:relative;
  background:var(--glass);
  backdrop-filter:blur(22px);
  border:1px solid var(--gb);
  border-radius:24px;
  overflow:hidden;
  opacity:0;
}
.activity-card.a1{animation:fadeUp .7s forwards .6s;}
.activity-card.a2{animation:fadeUp .7s forwards .7s;}

.activity-card::before{
  content:'';position:absolute;top:0;left:0;right:0;height:3px;
  background:linear-gradient(90deg,#D4AF37,#EE2A7B,#8A2BE2,#D4AF37);
  background-size:300% 100%;
  animation:goldSlide 5s linear infinite;
}

.ac-head{
  display:flex;align-items:center;justify-content:space-between;
  padding:22px 26px 16px;
  border-bottom:1px solid rgba(255,255,255,.06);
}
.ac-head-left{display:flex;align-items:center;gap:10px;}
.ac-head-icon{font-size:1.1em;}
.ac-head-title{font-family:'Playfair Display',serif;font-size:.98em;font-weight:700;}
.ac-head-link{
  font-size:.75em;font-weight:600;color:var(--gold);
  text-decoration:none;
  display:inline-flex;align-items:center;gap:4px;
  opacity:.8;transition:opacity .2s;
}
.ac-head-link:hover{opacity:1;}

.ac-table{width:100%;border-collapse:collapse;}
.ac-table thead th{
  padding:10px 16px;
  font-size:.63em;font-weight:700;letter-spacing:2px;text-transform:uppercase;
  color:rgba(212,175,55,.7);text-align:left;
  background:rgba(212,175,55,.05);
  border-bottom:1px solid rgba(212,175,55,.1);
}
.ac-table tbody tr{border-bottom:1px solid rgba(255,255,255,.05);}
.ac-table tbody tr:last-child{border-bottom:none;}
.ac-table tbody tr:hover{background:rgba(212,175,55,.04);}
.ac-table tbody td{padding:11px 16px;font-size:.82em;color:rgba(255,255,255,.75);}
.td-name{font-weight:600;color:#fff !important;}

.s-badge{
  display:inline-flex;align-items:center;gap:4px;
  padding:3px 10px;border-radius:999px;
  font-size:.7em;font-weight:700;white-space:nowrap;
}
.s-pending{background:rgba(212,175,55,.15);border:1px solid rgba(212,175,55,.4);color:#D4AF37;}
.s-ok{background:rgba(99,250,180,.15);border:1px solid rgba(99,250,180,.4);color:#6efabc;}
.s-batal{background:rgba(239,68,68,.14);border:1px solid rgba(239,68,68,.35);color:#fca5a5;}
.s-lunas{background:rgba(46,213,115,.12);border:1px solid rgba(46,213,115,.3);color:#2ed573;}
.s-menunggu{background:rgba(212,175,55,.12);border:1px solid rgba(212,175,55,.35);color:#D4AF37;}

.ac-empty{
  text-align:center;padding:30px;color:var(--muted);font-size:.85em;
}

/* ── QUICK LINKS STRIP ── */
.quick-links{
  display:grid;
  grid-template-columns:repeat(4,1fr);
  gap:14px;
  margin-bottom:32px;
  opacity:0;
  animation:fadeUp .7s forwards .75s;
}
.ql-item{
  display:flex;flex-direction:column;align-items:center;gap:10px;
  padding:22px 16px;
  background:var(--glass);
  backdrop-filter:blur(20px);
  border:1px solid var(--gb);
  border-radius:18px;
  text-decoration:none;
  color:rgba(255,255,255,.7);
  font-size:.8em;font-weight:600;
  text-align:center;
  transition:background .25s,border-color .25s,color .25s,transform .25s,box-shadow .3s;
  position:relative;overflow:hidden;
}
.ql-item::before{
  content:'';position:absolute;top:0;left:0;right:0;height:2px;
  background:var(--ql-color,#D4AF37);
  transform:scaleX(0);transform-origin:left;
  transition:transform .35s;
}
.ql-item:hover::before{transform:scaleX(1);}
.ql-item:hover{
  background:var(--glass-h);
  border-color:rgba(212,175,55,.25);
  color:#fff;
  transform:translateY(-4px);
  box-shadow:0 12px 32px rgba(0,0,0,.3);
}
.ql-icon{font-size:1.7em;}
.ql-item:nth-child(1){--ql-color:#D4AF37;}
.ql-item:nth-child(2){--ql-color:#EE2A7B;}
.ql-item:nth-child(3){--ql-color:#8A2BE2;}
.ql-item:nth-child(4){--ql-color:#2ed573;}

/* ── FOOTER ── */
.dash-footer{
  text-align:center;padding:24px;
  font-size:.75em;color:var(--muted);
  border-top:1px solid rgba(255,255,255,.05);
  line-height:1.9;
}
.dash-footer-brand{
  font-family:'Playfair Display',serif;font-size:1.1em;
  background:linear-gradient(135deg,#D4AF37,#FFE88A);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  margin-bottom:4px;
}

/* ── PARTICLES ── */
.particle{position:fixed;border-radius:50%;pointer-events:none;animation:pFloat linear infinite;z-index:0;}
@keyframes pFloat{
  0%{transform:translateY(100vh) scale(0);opacity:0;}
  10%{opacity:.45;}90%{opacity:.25;}
  100%{transform:translateY(-120px) scale(1);opacity:0;}
}

/* ── OVERLAY (mobile) ── */
.sidebar-overlay{
  display:none;
  position:fixed;inset:0;background:rgba(0,0,0,.7);
  backdrop-filter:blur(4px);z-index:90;
}

/* ════════════════════════════════════════════
   ANIMATIONS
════════════════════════════════════════════ */
@keyframes fadeUp{
  from{opacity:0;transform:translateY(20px);}
  to{opacity:1;transform:translateY(0);}
}
@keyframes goldSlide{
  0%{background-position:0% 0;}100%{background-position:200% 0;}
}

/* ════════════════════════════════════════════
   RESPONSIVE
════════════════════════════════════════════ */
@media(max-width:1100px){
  .stats-grid{grid-template-columns:repeat(2,1fr);}
  .quick-links{grid-template-columns:repeat(2,1fr);}
}
@media(max-width:900px){
  .activity-grid{grid-template-columns:1fr;}
  .mgmt-grid{grid-template-columns:1fr;}
  .dash-hero-deco{display:none;}
}
@media(max-width:768px){
  :root{--sidebar-w:260px;}
  .sidebar{transform:translateX(-100%);}
  .sidebar.open{transform:translateX(0);}
  .sidebar-overlay.open{display:block;}
  .main-content{margin-left:0;}
  .hamburger-btn{display:flex;}
  .page-body{padding:24px 16px 48px;}
  .dash-hero{padding:28px 22px;}
  .dash-hero h1{font-size:1.7em;}
  .topbar{padding:14px 18px;}
  .topbar-time{display:none;}
  .stats-grid{grid-template-columns:1fr 1fr;gap:12px;}
  .quick-links{grid-template-columns:repeat(2,1fr);}
}
@media(max-width:480px){
  .stats-grid{grid-template-columns:1fr;}
  .mc-actions{flex-direction:column;}
  .mc-actions a{width:100%;justify-content:center;}
}
</style>
</head>
<body>

<!-- PARTICLES -->
<div id="particles-wrap"></div>

<!-- SIDEBAR OVERLAY (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- ═══════════════════════════════════
     LAYOUT
══════════════════════════════════════ -->
<div class="admin-layout">

  <!-- ────────── SIDEBAR ────────── -->
  <aside class="sidebar" id="sidebar">

    <!-- Brand -->
    <div class="sb-brand">
      <div class="sb-label">Admin Panel</div>
      <a class="sb-logo" href="index.php">
        <img src="assets/img/Yolazcake.png" alt="YOLAZCAKE">
        <span class="sb-logo-text">YOLAZCAKE</span>
      </a>
      <div class="sb-badge">👑 Admin</div>
    </div>

    <!-- Nav -->
    <nav class="sb-nav">

      <!-- UTAMA -->
      <div class="sb-section-label">Utama</div>
      <a class="sb-link active" href="dashboard.php">
        <span class="sb-link-icon">🏠</span> Dashboard
      </a>
      <a class="sb-link" href="index.php" target="_blank">
        <span class="sb-link-icon">🌐</span> Lihat Website
      </a>

      <!-- KELOLA DATA -->
      <div class="sb-section-label">Kelola Data</div>
      <a class="sb-link" href="booking/admin_booking.php">
        <span class="sb-link-icon">📋</span> Booking
        <?php if(($s_booking['p'] ?? 0) > 0): ?>
          <span class="sb-link-badge"><?= $s_booking['p'] ?></span>
        <?php endif; ?>
      </a>
      <a class="sb-link" href="pemesanan/data_pemesanan.php">
        <span class="sb-link-icon">🛍️</span> Pemesanan
        <?php if(($s_pesanan['p'] ?? 0) > 0): ?>
          <span class="sb-link-badge"><?= $s_pesanan['p'] ?></span>
        <?php endif; ?>
      </a>
      <a class="sb-link" href="produk/data_produk.php">
        <span class="sb-link-icon">🍰</span> Produk
      </a>
      <a class="sb-link" href="member/data_member.php">
        <span class="sb-link-icon">👥</span> Member
      </a>

      <!-- TAMBAH DATA -->
      <div class="sb-section-label">Tambah Data</div>
      <a class="sb-link add-link" href="produk/tambah_produk.php">
        <span class="sb-link-icon">➕</span> Tambah Produk
        <span class="sb-link-new">Baru</span>
      </a>
      <a class="sb-link add-link" href="member/tambah_member.php">
        <span class="sb-link-icon">➕</span> Tambah Member
        <span class="sb-link-new">Baru</span>
      </a>

    </nav>

    <!-- Footer / Logout -->
    <div class="sb-footer">
      <a class="sb-logout" href="auth/logout.php" onclick="return confirm('Yakin ingin keluar?')">
        🚪 <span>Keluar</span>
      </a>
    </div>

  </aside>
  <!-- end sidebar -->

  <!-- ────────── MAIN ────────── -->
  <div class="main-content">

    <!-- TOPBAR -->
    <div class="topbar">
      <div class="topbar-left">
        <button class="hamburger-btn" id="hamburgerBtn" onclick="toggleSidebar()">☰</button>
        <div class="topbar-title">Dashboard Admin</div>
      </div>
      <div class="topbar-right">
        <span class="topbar-time" id="topbarTime"></span>
        <a class="btn-website" href="index.php" target="_blank">🌐 Lihat Website</a>
        <div class="topbar-user">
          <div class="topbar-avatar">
            <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
          </div>
          <span class="topbar-username"><?= htmlspecialchars($_SESSION['username']) ?></span>
        </div>
      </div>
    </div>

    <!-- PAGE BODY -->
    <div class="page-body">

      <!-- ─── HERO BANNER ─── -->
      <div class="dash-hero">
        <div class="dash-hero-grid"></div>
        <div class="dash-hero-inner">
          <div class="dash-eyebrow">Panel Kontrol YOLAZCAKE</div>
          <h1><span>Selamat Datang, <?= htmlspecialchars($_SESSION['username']) ?>!</span></h1>
          <div class="dash-hero-sub">
            <span>📅 <?= date("d F Y") ?></span>
            <span>⏰ <span id="heroTime"></span></span>
            <span>✦ Sintang, Kalimantan Barat</span>
          </div>
        </div>
        <div class="dash-hero-deco">YZ</div>
      </div>

      <!-- ─── STATS ─── -->
      <div class="stats-grid">
        <div class="stat-card s1">
          <div class="stat-icon-wrap">📋</div>
          <div class="stat-val" data-count="<?= $s_booking['t'] ?? 0 ?>"><?= $s_booking['t'] ?? 0 ?></div>
          <div class="stat-lbl">Total Booking</div>
          <?php if(($s_booking['p'] ?? 0) > 0): ?>
            <div class="stat-badge">⏳ <?= $s_booking['p'] ?> Pending</div>
          <?php endif; ?>
        </div>
        <div class="stat-card s2">
          <div class="stat-icon-wrap">🛍️</div>
          <div class="stat-val" data-count="<?= $s_pesanan['t'] ?? 0 ?>"><?= $s_pesanan['t'] ?? 0 ?></div>
          <div class="stat-lbl">Total Pemesanan</div>
          <?php if(($s_pesanan['p'] ?? 0) > 0): ?>
            <div class="stat-badge">⏳ <?= $s_pesanan['p'] ?> Menunggu</div>
          <?php endif; ?>
        </div>
        <div class="stat-card s3">
          <div class="stat-icon-wrap">🍰</div>
          <div class="stat-val" data-count="<?= $s_produk['t'] ?? 0 ?>"><?= $s_produk['t'] ?? 0 ?></div>
          <div class="stat-lbl">Total Produk</div>
          <div class="stat-badge ok">✓ Aktif</div>
        </div>
        <div class="stat-card s4">
          <div class="stat-icon-wrap">👥</div>
          <div class="stat-val" data-count="<?= $s_member['t'] ?? 0 ?>"><?= $s_member['t'] ?? 0 ?></div>
          <div class="stat-lbl">Total Member</div>
          <div class="stat-badge ok">✓ Terdaftar</div>
        </div>
      </div>

      <!-- ─── MANAGEMENT CARDS ─── -->
      <div class="section-hd">
        <div class="sh-icon">⚙️</div>
        <h2>Manajemen Data</h2>
        <div class="section-hd-line"></div>
      </div>

      <div class="mgmt-grid">

        <!-- Kelola Booking -->
        <div class="mgmt-card booking m1">
          <div class="mc-top">
            <div class="mc-icon">📋</div>
            <div class="mc-count"><?= $s_booking['t'] ?? 0 ?></div>
          </div>
          <div class="mc-title">Kelola Booking</div>
          <div class="mc-desc">
            Konfirmasi, batalkan, atau hapus reservasi meja pelanggan. Pantau status booking secara real-time.
          </div>
          <?php if(($s_booking['p'] ?? 0) > 0): ?>
            <div class="pending-tag">⏳ <?= $s_booking['p'] ?> booking menunggu konfirmasi</div>
          <?php endif; ?>
          <div class="mc-actions" style="margin-top:18px;">
            <a class="btn-primary" href="booking/admin_booking.php">📋 Lihat Semua</a>
          </div>
        </div>

        <!-- Kelola Pemesanan -->
        <div class="mgmt-card pemesanan m2">
          <div class="mc-top">
            <div class="mc-icon">🛍️</div>
            <div class="mc-count"><?= $s_pesanan['t'] ?? 0 ?></div>
          </div>
          <div class="mc-title">Kelola Pemesanan</div>
          <div class="mc-desc">
            Monitor seluruh transaksi pesanan, update status pembayaran dan pengiriman produk ke meja.
          </div>
          <?php if(($s_pesanan['p'] ?? 0) > 0): ?>
            <div class="pending-tag">⏳ <?= $s_pesanan['p'] ?> pesanan menunggu diproses</div>
          <?php endif; ?>
          <div class="mc-actions" style="margin-top:18px;">
            <a class="btn-primary" href="pemesanan/data_pemesanan.php">🛍️ Lihat Semua</a>
          </div>
        </div>

        <!-- Kelola Produk -->
        <div class="mgmt-card produk m3">
          <div class="mc-top">
            <div class="mc-icon">🍰</div>
            <div class="mc-count"><?= $s_produk['t'] ?? 0 ?></div>
          </div>
          <div class="mc-title">Kelola Produk</div>
          <div class="mc-desc">
            Tambah, edit, atau hapus menu produk kafe. Pantau stok dan kelola harga produk dengan mudah.
          </div>
          <div class="mc-actions" style="margin-top:26px;">
            <a class="btn-primary" href="produk/data_produk.php">🍰 Lihat Semua</a>
            <a class="btn-add" href="produk/tambah_produk.php">➕ Tambah Produk</a>
          </div>
        </div>

        <!-- Kelola Member -->
        <div class="mgmt-card member m4">
          <div class="mc-top">
            <div class="mc-icon">👥</div>
            <div class="mc-count"><?= $s_member['t'] ?? 0 ?></div>
          </div>
          <div class="mc-title">Kelola Member</div>
          <div class="mc-desc">
            Kelola data pelanggan terdaftar, edit informasi, pantau poin loyalitas, dan tambah member baru.
          </div>
          <div class="mc-actions" style="margin-top:26px;">
            <a class="btn-primary" href="member/data_member.php">👥 Lihat Semua</a>
            <a class="btn-add" href="member/tambah_member.php">➕ Tambah Member</a>
          </div>
        </div>

      </div>
      <!-- end mgmt-grid -->

      <!-- ─── QUICK ACCESS ─── -->
      <div class="section-hd">
        <div class="sh-icon">⚡</div>
        <h2>Akses Cepat</h2>
        <div class="section-hd-line"></div>
      </div>

      <div class="quick-links">
        <a class="ql-item" href="index.php" target="_blank">
          <span class="ql-icon">🏠</span>
          <span>Beranda Website</span>
        </a>
        <a class="ql-item" href="pemesanan/menuu.php" target="_blank">
          <span class="ql-icon">☕</span>
          <span>Menu Kafe</span>
        </a>
        <a class="ql-item" href="booking/booking.php" target="_blank">
          <span class="ql-icon">🗓️</span>
          <span>Form Booking</span>
        </a>
        <a class="ql-item" href="auth/logout.php" onclick="return confirm('Yakin ingin keluar?')">
          <span class="ql-icon">🚪</span>
          <span>Logout</span>
        </a>
      </div>

      <!-- ─── RECENT ACTIVITY ─── -->
      <div class="section-hd">
        <div class="sh-icon">🕐</div>
        <h2>Aktivitas Terbaru</h2>
        <div class="section-hd-line"></div>
      </div>

      <div class="activity-grid">

        <!-- Booking Terbaru -->
        <div class="activity-card a1">
          <div class="ac-head">
            <div class="ac-head-left">
              <span class="ac-head-icon">📋</span>
              <span class="ac-head-title">Booking Terbaru</span>
            </div>
            <a class="ac-head-link" href="booking/admin_booking.php">Lihat Semua →</a>
          </div>
          <table class="ac-table">
            <thead>
              <tr>
                <th>Nama</th>
                <th>Tanggal</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $rows = mysqli_num_rows($q_booking);
              if($rows > 0):
                while($r = mysqli_fetch_assoc($q_booking)):
                  $sc = $r['status']==='Dikonfirmasi' ? 's-ok' : ($r['status']==='Dibatalkan' ? 's-batal' : 's-pending');
                  $si = $r['status']==='Dikonfirmasi' ? '✅' : ($r['status']==='Dibatalkan' ? '🚫' : '⏳');
              ?>
              <tr>
                <td class="td-name"><?= htmlspecialchars($r['nama_pemesan']) ?></td>
                <td><?= htmlspecialchars($r['tanggal_booking']) ?></td>
                <td><span class="s-badge <?= $sc ?>"><?= $si ?> <?= htmlspecialchars($r['status']) ?></span></td>
              </tr>
              <?php endwhile; else: ?>
              <tr><td colspan="3" class="ac-empty">Belum ada data booking</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Pemesanan Terbaru -->
        <div class="activity-card a2">
          <div class="ac-head">
            <div class="ac-head-left">
              <span class="ac-head-icon">🛍️</span>
              <span class="ac-head-title">Pemesanan Terbaru</span>
            </div>
            <a class="ac-head-link" href="pemesanan/data_pemesanan.php">Lihat Semua →</a>
          </div>
          <table class="ac-table">
            <thead>
              <tr>
                <th>Pemesan</th>
                <th>Total</th>
                <th>Bayar</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $rows2 = mysqli_num_rows($q_pesanan);
              if($rows2 > 0):
                while($r2 = mysqli_fetch_assoc($q_pesanan)):
                  $pc = $r2['status_pembayaran']==='Lunas' ? 's-lunas' : 's-menunggu';
                  $pi = $r2['status_pembayaran']==='Lunas' ? '✅' : '⏳';
              ?>
              <tr>
                <td class="td-name"><?= htmlspecialchars($r2['nama_pemesan']) ?></td>
                <td>Rp <?= number_format($r2['total_harga'],0,',','.') ?></td>
                <td><span class="s-badge <?= $pc ?>"><?= $pi ?> <?= htmlspecialchars($r2['status_pembayaran']) ?></span></td>
              </tr>
              <?php endwhile; else: ?>
              <tr><td colspan="3" class="ac-empty">Belum ada data pemesanan</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

      </div>
      <!-- end activity-grid -->

    </div>
    <!-- end page-body -->

    <!-- FOOTER -->
    <div class="dash-footer">
      <div class="dash-footer-brand">YOLAZCAKE</div>
      © <?= date('Y') ?> YOLAZCAKE Sintang · Admin Panel · All rights reserved
    </div>

  </div>
  <!-- end main-content -->

</div>
<!-- end admin-layout -->


<script>
/* ── PARTICLES ── */
(function(){
  const wrap = document.getElementById('particles-wrap');
  const colors = ['rgba(212,175,55,.35)','rgba(232,160,191,.25)','rgba(138,43,226,.2)','rgba(255,255,255,.12)'];
  for(let i=0;i<18;i++){
    const p = document.createElement('div');
    p.className = 'particle';
    const s = Math.random()*5+2;
    p.style.cssText=`width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;animation-duration:${12+Math.random()*14}s;animation-delay:${Math.random()*12}s;`;
    wrap.appendChild(p);
  }
})();

/* ── CLOCK ── */
function updateClock(){
  const now = new Date();
  const h = String(now.getHours()).padStart(2,'0');
  const m = String(now.getMinutes()).padStart(2,'0');
  const s = String(now.getSeconds()).padStart(2,'0');
  const t = `${h}:${m}:${s}`;
  const el1 = document.getElementById('heroTime');
  const el2 = document.getElementById('topbarTime');
  if(el1) el1.textContent = t;
  if(el2) el2.textContent = t;
}
updateClock();
setInterval(updateClock, 1000);

/* ── COUNTER ANIMATION ── */
function animateCounter(el, target, dur){
  let start = 0;
  const step = target / (dur / 16);
  const tick = () => {
    start = Math.min(start + step, target);
    el.textContent = Math.floor(start);
    if(start < target) requestAnimationFrame(tick);
  };
  requestAnimationFrame(tick);
}
window.addEventListener('load', () => {
  setTimeout(() => {
    document.querySelectorAll('.stat-val[data-count]').forEach(el => {
      animateCounter(el, parseInt(el.dataset.count), 1000);
    });
  }, 400);
});

/* ── SIDEBAR TOGGLE (mobile) ── */
function toggleSidebar(){
  const sb = document.getElementById('sidebar');
  const ov = document.getElementById('sidebarOverlay');
  const btn = document.getElementById('hamburgerBtn');
  const open = sb.classList.toggle('open');
  ov.classList.toggle('open', open);
  btn.textContent = open ? '✕' : '☰';
}
function closeSidebar(){
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebarOverlay').classList.remove('open');
  document.getElementById('hamburgerBtn').textContent = '☰';
}
</script>

</body>
</html>
