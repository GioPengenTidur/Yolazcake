<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['keranjang']) || empty($_SESSION['keranjang'])) {
    header('Location: menuu.php');
    exit;
}

$nama_pemesan = $_SESSION['nama_pemesan'] ?? 'Pelanggan';
$no_hp        = $_SESSION['no_hp'] ?? '-';

$id_booking     = $_SESSION['id_booking'] ?? null;
$id_booking_sql = $id_booking ? "'$id_booking'" : "NULL";

$tanggal     = date('Y-m-d H:i:s');
$total_harga = 0;

// Hitung total & kumpulkan item keranjang
$items = [];
foreach ($_SESSION['keranjang'] as $id_produk => $jumlah) {
    $q = mysqli_query($conn, "SELECT nama_produk, harga FROM produk WHERE id_produk='$id_produk'");
    $p = mysqli_fetch_assoc($q);
    if ($p) {
        $subtotal     = $p['harga'] * $jumlah;
        $total_harga += $subtotal;
        $items[]      = [
            'nama'     => $p['nama_produk'],
            'harga'    => $p['harga'],
            'jumlah'   => $jumlah,
            'subtotal' => $subtotal,
        ];
    }
}

$kode_pesanan = "ORD" . date("YmdHis");

mysqli_query($conn, "
INSERT INTO pemesanan
(kode_pesanan, id_member, id_booking, tanggal, total_harga,
 nama_pemesan, no_hp, metode_pembayaran, status_pembayaran, status_pesanan)
VALUES
('$kode_pesanan', NULL, $id_booking_sql, '$tanggal', '$total_harga',
 '$nama_pemesan', '$no_hp', 'QRIS', 'Lunas', 'Menunggu')
");

$id_pemesanan = mysqli_insert_id($conn);

foreach ($_SESSION['keranjang'] as $id_produk => $jumlah) {
    $q        = mysqli_query($conn, "SELECT harga FROM produk WHERE id_produk='$id_produk'");
    $p        = mysqli_fetch_assoc($q);
    $subtotal = $p['harga'] * $jumlah;
    mysqli_query($conn, "
        INSERT INTO detail_pemesanan (id_pemesanan, id_produk, jumlah, subtotal)
        VALUES ('$id_pemesanan', '$id_produk', '$jumlah', '$subtotal')
    ");
}

unset($_SESSION['keranjang']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pesanan Berhasil – YOLAZCAKE</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}

    body {
      min-height:100vh;
      font-family:'Inter',sans-serif;
      background:linear-gradient(160deg,#1e0e3a 0%,#2d1560 50%,#1a0a2e 100%);
      position:relative;
      overflow-x:hidden;
    }
    body::before {
      content:'';position:fixed;inset:0;
      background:
        radial-gradient(ellipse at 20% 30%,rgba(212,175,55,.10) 0%,transparent 55%),
        radial-gradient(ellipse at 80% 70%,rgba(232,160,191,.10) 0%,transparent 55%);
      pointer-events:none;z-index:0;
    }

    @keyframes goldSlide{0%{background-position:0% 0;}100%{background-position:200% 0;}}
    @keyframes fadeSlideDown{from{opacity:0;transform:translateY(-20px);}to{opacity:1;transform:translateY(0);}}
    @keyframes fadeSlideUp{from{opacity:0;transform:translateY(30px);}to{opacity:1;transform:translateY(0);}}
    @keyframes shimmerText{0%{background-position:100% 0;}100%{background-position:-100% 0;}}
    @keyframes pulseBadge{0%,100%{box-shadow:0 0 0 0 rgba(99,250,180,.5);}50%{box-shadow:0 0 0 10px rgba(99,250,180,0);}}
    @keyframes spinIn{0%{transform:scale(0) rotate(-180deg);opacity:0;}60%{transform:scale(1.2) rotate(10deg);opacity:1;}100%{transform:scale(1) rotate(0deg);opacity:1;}}
    @keyframes floatBounce{0%,100%{transform:translateY(0);}50%{transform:translateY(-12px);}}
    @keyframes particleFloat{0%{transform:translateY(100vh) scale(0);opacity:0;}10%{opacity:.6;}90%{opacity:.3;}100%{transform:translateY(-100px) scale(1);opacity:0;}}
    @keyframes confettiFall{0%{transform:translateY(-30px) rotate(0deg);opacity:1;}100%{transform:translateY(110vh) rotate(720deg);opacity:0;}}
    @keyframes ringPulse{0%{transform:scale(1);opacity:.7;}100%{transform:scale(2.2);opacity:0;}}
    @keyframes checkDraw{0%{stroke-dashoffset:80;}100%{stroke-dashoffset:0;}}
    @keyframes heroAurora{0%{opacity:.6;transform:scale(1);}100%{opacity:1;transform:scale(1.08) translateX(10px);}}
    @keyframes floatDot{0%{transform:translateY(0) rotate(0deg);opacity:0;}20%{opacity:1;}80%{opacity:.8;}100%{transform:translateY(-280px) rotate(360deg);opacity:0;}}

    /* ── HERO ── */
    .page-hero {
      position:relative;height:260px;
      display:flex;flex-direction:column;align-items:center;justify-content:center;
      overflow:hidden;
      background:linear-gradient(135deg,#2b1a11 0%,#4a2c1a 40%,#6d3e26 70%,#3a1f0e 100%);
      z-index:1;
    }
    .page-hero::before {
      content:'';position:absolute;inset:0;
      background:
        radial-gradient(ellipse at 30% 50%,rgba(212,175,55,.18) 0%,transparent 60%),
        radial-gradient(ellipse at 75% 40%,rgba(232,160,191,.15) 0%,transparent 55%);
      animation:heroAurora 8s ease-in-out infinite alternate;
    }
    .sparkle{position:absolute;border-radius:50%;pointer-events:none;animation:floatDot linear infinite;}
    .hero-inner{position:relative;z-index:2;text-align:center;color:#fff;}
    .hero-eyebrow{
      font-size:.72em;font-weight:500;letter-spacing:5px;text-transform:uppercase;
      color:#D4AF37;margin-bottom:10px;
      opacity:0;animation:fadeSlideDown .8s forwards .3s;
    }
    .hero-inner h1{
      font-family:'Playfair Display',serif;font-size:2.8em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,#D4AF37 60%,#FFE4B5 80%,#fff);
      background-size:200% 100%;
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
      animation:shimmerText 4s ease-in-out infinite,fadeSlideDown .9s forwards .5s;
      opacity:0;
    }
    .hero-inner .hero-sub{
      font-size:.9em;color:rgba(255,255,255,.65);margin-top:10px;
      opacity:0;animation:fadeSlideDown .9s forwards .9s;
    }
    .hero-divider{
      margin-top:16px;display:flex;justify-content:center;align-items:center;gap:12px;
      opacity:0;animation:fadeSlideDown .9s forwards 1.1s;
    }
    .hero-divider span{display:block;width:60px;height:1px;background:linear-gradient(to right,transparent,#D4AF37);}
    .hero-divider span:last-child{background:linear-gradient(to left,transparent,#D4AF37);}
    .hero-divider .diamond{color:#D4AF37;font-size:.75em;letter-spacing:4px;}

    /* ── PARTICLES & CONFETTI ── */
    .particle{position:fixed;border-radius:50%;pointer-events:none;animation:particleFloat linear infinite;z-index:0;}
    .confetti-piece{
      position:fixed;top:-30px;border-radius:3px;
      pointer-events:none;z-index:999;
      animation:confettiFall linear forwards;
    }

    /* ── PAGE WRAPPER ── */
    .page-wrapper{
      position:relative;z-index:1;
      display:flex;flex-direction:column;align-items:center;
      padding:20px 20px 120px;
      max-width:820px;margin:0 auto;
    }

    /* ── SUCCESS HERO SECTION ── */
    .success-section{
      width:100%;text-align:center;
      padding:40px 20px 20px;
      opacity:0;animation:fadeSlideUp .8s forwards 1.4s;
    }

    /* Ring burst behind icon */
    .icon-wrap{
      position:relative;display:inline-flex;align-items:center;justify-content:center;
      width:110px;height:110px;margin:0 auto 28px;
    }
    .ring{
      position:absolute;inset:0;border-radius:50%;
      border:3px solid rgba(99,250,180,.5);
      animation:ringPulse 1.8s ease-out infinite;
    }
    .ring:nth-child(2){animation-delay:.6s;border-color:rgba(212,175,55,.4);}
    .ring:nth-child(3){animation-delay:1.2s;border-color:rgba(232,160,191,.35);}
    .check-circle{
      position:relative;z-index:2;
      width:110px;height:110px;border-radius:50%;
      background:linear-gradient(135deg,rgba(99,250,180,.2),rgba(99,250,180,.08));
      border:2px solid rgba(99,250,180,.5);
      display:flex;align-items:center;justify-content:center;
      animation:spinIn .9s cubic-bezier(.34,1.56,.64,1) 1.2s both, floatBounce 3s ease-in-out 2.5s infinite;
    }
    .check-circle svg{
      width:52px;height:52px;
    }
    .check-circle svg polyline{
      stroke:#6efabc;stroke-width:5;stroke-linecap:round;stroke-linejoin:round;fill:none;
      stroke-dasharray:80;stroke-dashoffset:80;
      animation:checkDraw .6s ease-out 2.1s forwards;
    }

    .success-title{
      font-family:'Playfair Display',serif;font-size:2em;font-weight:700;
      background:linear-gradient(135deg,#6efabc 0%,#D4AF37 50%,#6efabc 100%);
      background-size:200% 100%;
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
      animation:shimmerText 3s ease-in-out infinite;
      margin-bottom:10px;
    }
    .success-sub{
      font-size:.95em;color:rgba(255,255,255,.6);line-height:1.7;
      max-width:480px;margin:0 auto;
    }
    .success-sub strong{color:#D4AF37;}

    /* ── GOLD RULE ── */
    .gold-rule{display:flex;align-items:center;gap:12px;width:100%;margin:28px 0;}
    .gold-rule::before,.gold-rule::after{content:'';flex:1;height:1px;background:linear-gradient(to right,transparent,rgba(212,175,55,.4));}
    .gold-rule::after{background:linear-gradient(to left,transparent,rgba(212,175,55,.4));}
    .gold-rule span{color:#D4AF37;font-size:.65em;letter-spacing:3px;white-space:nowrap;}

    /* ── KODE BANNER ── */
    .kode-banner{
      width:100%;
      background:linear-gradient(135deg,rgba(212,175,55,.14) 0%,rgba(212,175,55,.06) 100%);
      border:1px solid rgba(212,175,55,.4);border-radius:20px;
      padding:22px 28px;
      display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;
      margin-bottom:24px;position:relative;overflow:hidden;
      opacity:0;animation:fadeSlideUp .8s forwards 1.6s;
    }
    .kode-banner::before{
      content:'';position:absolute;top:0;left:0;right:0;height:2px;
      background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);
      background-size:200%;animation:goldSlide 4s linear infinite;
    }
    .kode-label{font-size:.72em;color:rgba(212,175,55,.7);letter-spacing:2px;text-transform:uppercase;margin-bottom:4px;}
    .kode-value{
      font-family:'Playfair Display',serif;font-size:1.6em;font-weight:700;color:#D4AF37;letter-spacing:2px;
    }
    .kode-pulse{
      display:inline-flex;align-items:center;gap:8px;
      background:rgba(99,250,180,.15);border:1px solid rgba(99,250,180,.35);
      color:#6efabc;font-size:.8em;font-weight:600;letter-spacing:1px;
      padding:6px 16px;border-radius:999px;
      animation:pulseBadge 2s ease-in-out infinite;
    }

    /* ── ORDER INFO CARD ── */
    .order-card{
      width:100%;
      background:rgba(255,255,255,.05);
      backdrop-filter:blur(20px);
      border:1px solid rgba(255,255,255,.1);
      border-radius:24px;overflow:hidden;position:relative;
      margin-bottom:24px;
      opacity:0;animation:fadeSlideUp .8s forwards 1.8s;
    }
    .order-card::before{
      content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,#D4AF37,#ee2a7b,#D4AF37);
      background-size:200%;animation:goldSlide 4s linear infinite;
    }

    .card-header{
      padding:18px 26px;
      background:linear-gradient(135deg,rgba(212,175,55,.1),rgba(212,175,55,.04));
      border-bottom:1px solid rgba(212,175,55,.2);
      display:flex;align-items:center;gap:10px;
    }
    .card-header h3{
      font-family:'Playfair Display',serif;font-size:1.05em;font-weight:700;
      background:linear-gradient(135deg,#fff,#D4AF37);
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
    }

    .info-row{
      display:flex;align-items:center;
      border-bottom:1px solid rgba(255,255,255,.06);
      transition:background .25s;
    }
    .info-row:last-child{border-bottom:none;}
    .info-row:hover{background:rgba(212,175,55,.04);}
    .info-label{
      width:200px;flex-shrink:0;padding:14px 22px;
      font-size:.78em;font-weight:600;letter-spacing:.5px;text-transform:uppercase;
      color:rgba(212,175,55,.8);
      background:rgba(212,175,55,.05);
      border-right:1px solid rgba(212,175,55,.12);
      display:flex;align-items:center;gap:8px;
    }
    .info-val{
      flex:1;padding:14px 22px;
      font-size:.9em;color:rgba(255,255,255,.88);
    }
    .info-val.green{color:#6efabc;font-weight:600;}
    .info-val.gold{color:#D4AF37;font-weight:600;}

    /* ── ITEMS TABLE ── */
    .items-card{
      width:100%;
      background:rgba(255,255,255,.04);
      backdrop-filter:blur(20px);
      border:1px solid rgba(255,255,255,.1);
      border-radius:24px;overflow:hidden;
      margin-bottom:24px;
      opacity:0;animation:fadeSlideUp .8s forwards 2s;
    }
    .items-table{width:100%;border-collapse:collapse;}
    .items-table thead tr{
      background:linear-gradient(135deg,rgba(212,175,55,.12),rgba(212,175,55,.05));
      border-bottom:1px solid rgba(212,175,55,.25);
    }
    .items-table thead th{
      padding:14px 20px;font-size:.75em;font-weight:600;letter-spacing:2px;
      text-transform:uppercase;color:#D4AF37;text-align:left;
    }
    .items-table thead th:last-child{text-align:right;}
    .items-table tbody tr{
      border-bottom:1px solid rgba(255,255,255,.06);
      transition:background .2s;
    }
    .items-table tbody tr:last-child{border-bottom:none;}
    .items-table tbody tr:hover{background:rgba(212,175,55,.04);}
    .items-table tbody td{
      padding:13px 20px;font-size:.88em;color:rgba(255,255,255,.85);
    }
    .items-table tbody td:last-child{text-align:right;color:#6efabc;font-weight:600;}
    .item-qty{
      display:inline-flex;align-items:center;justify-content:center;
      width:28px;height:28px;border-radius:8px;
      background:rgba(212,175,55,.15);border:1px solid rgba(212,175,55,.3);
      color:#D4AF37;font-size:.8em;font-weight:700;
    }

    /* ── TOTAL BOX ── */
    .total-box{
      width:100%;
      background:linear-gradient(135deg,rgba(99,250,180,.1),rgba(99,250,180,.04));
      border:1px solid rgba(99,250,180,.3);border-radius:20px;
      padding:24px 30px;
      display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;
      margin-bottom:32px;
      opacity:0;animation:fadeSlideUp .8s forwards 2.2s;
    }
    .total-label-wrap .tl{font-size:.75em;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.45);margin-bottom:4px;}
    .total-label-wrap .tv{
      font-family:'Playfair Display',serif;font-size:2em;font-weight:700;
      background:linear-gradient(135deg,#6efabc,#D4AF37);
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
    }
    .paid-badge{
      display:inline-flex;align-items:center;gap:8px;
      background:rgba(99,250,180,.15);border:1px solid rgba(99,250,180,.4);
      color:#6efabc;font-size:.85em;font-weight:600;letter-spacing:1px;
      padding:8px 20px;border-radius:999px;
    }

    /* ── STATUS TIMELINE ── */
    .timeline{
      width:100%;margin-bottom:32px;
      opacity:0;animation:fadeSlideUp .8s forwards 2.4s;
    }
    .timeline-title{
      font-family:'Playfair Display',serif;font-size:1em;font-weight:700;
      color:rgba(255,255,255,.7);margin-bottom:18px;text-align:center;
      letter-spacing:1px;
    }
    .timeline-steps{
      display:flex;align-items:center;justify-content:center;gap:0;
    }
    .tl-step{
      display:flex;flex-direction:column;align-items:center;gap:8px;
      flex:1;
    }
    .tl-dot{
      width:40px;height:40px;border-radius:50%;
      display:flex;align-items:center;justify-content:center;font-size:1.1em;
      border:2px solid transparent;transition:all .3s;
    }
    .tl-dot.active{
      background:linear-gradient(135deg,rgba(212,175,55,.25),rgba(212,175,55,.1));
      border-color:rgba(212,175,55,.6);
      box-shadow:0 0 18px rgba(212,175,55,.35);
    }
    .tl-dot.done{
      background:linear-gradient(135deg,rgba(99,250,180,.25),rgba(99,250,180,.1));
      border-color:rgba(99,250,180,.5);
      box-shadow:0 0 14px rgba(99,250,180,.3);
    }
    .tl-dot.pending{
      background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.15);
    }
    .tl-lbl{font-size:.7em;color:rgba(255,255,255,.5);text-align:center;letter-spacing:.5px;}
    .tl-lbl.active-lbl{color:#D4AF37;font-weight:600;}
    .tl-connector{
      flex:1;height:2px;max-width:60px;
      background:linear-gradient(to right,rgba(99,250,180,.4),rgba(212,175,55,.25));
      margin-bottom:22px;
    }

    /* ── ACTION BUTTONS ── */
    .actions{
      width:100%;display:flex;gap:14px;flex-wrap:wrap;justify-content:center;
      opacity:0;animation:fadeSlideUp .8s forwards 2.6s;
    }
    .btn-premium{
      position:relative;padding:14px 30px;border:none;border-radius:14px;
      font-family:'Inter',sans-serif;font-size:.85em;font-weight:700;
      letter-spacing:2px;text-transform:uppercase;cursor:pointer;
      overflow:hidden;transition:transform .25s cubic-bezier(.34,1.56,.64,1),box-shadow .35s;
      text-decoration:none;display:inline-flex;align-items:center;gap:10px;
    }
    .btn-premium::before{
      content:'';position:absolute;inset:0;
      background:linear-gradient(90deg,transparent,rgba(255,255,255,.18),transparent);
      transform:translateX(-100%);transition:transform .5s;
    }
    .btn-premium:hover::before{transform:translateX(100%);}
    .btn-premium:hover{transform:translateY(-3px) scale(1.02);}

    .btn-gold{
      background:linear-gradient(135deg,#D4AF37 0%,#b8860b 50%,#D4AF37 100%);
      background-size:200% 100%;color:#1e0e3a;
      animation:goldSlide 3s linear infinite;
      box-shadow:0 8px 28px rgba(212,175,55,.35),0 0 40px rgba(212,175,55,.18);
    }
    .btn-gold:hover{box-shadow:0 12px 40px rgba(212,175,55,.55),0 0 60px rgba(212,175,55,.3);}

    .btn-green{
      background:linear-gradient(135deg,rgba(99,250,180,.25),rgba(99,250,180,.1));
      border:1px solid rgba(99,250,180,.45);color:#6efabc;
    }
    .btn-green:hover{
      background:linear-gradient(135deg,rgba(99,250,180,.35),rgba(99,250,180,.18));
      box-shadow:0 8px 28px rgba(99,250,180,.3);
    }

    .btn-outline{
      background:rgba(255,255,255,.06);
      border:1px solid rgba(255,255,255,.15);color:rgba(255,255,255,.7);
    }
    .btn-outline:hover{
      background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.3);
    }

    /* ── FOOTER ── */
    .site-footer{
      position:relative;z-index:1;text-align:center;
      padding:36px 20px;font-size:.8em;
      color:rgba(255,255,255,.5);
      border-top:1px solid rgba(255,255,255,.06);
      line-height:1.8;
    }

    @media(max-width:600px){
      .hero-inner h1{font-size:2em;}
      .info-label{width:130px;padding:12px 14px;}
      .info-val{padding:12px 14px;}
      .kode-value{font-size:1.2em;}
      .total-label-wrap .tv{font-size:1.5em;}
      .tl-connector{max-width:30px;}
      .actions{flex-direction:column;}
      .btn-premium{justify-content:center;}
    }

    /* ══════════════════ LOADING OVERLAY ══════════════════ */
    @keyframes loaderRotate{0%{transform:rotate(0deg);}100%{transform:rotate(360deg);}}
    @keyframes loaderRotateRev{0%{transform:rotate(360deg);}100%{transform:rotate(0deg);}}
    @keyframes loaderCakeBounce{0%,100%{transform:translateY(0) scale(1);}50%{transform:translateY(-8px) scale(1.06);}}
    @keyframes loaderDots{0%,20%{opacity:0;}50%{opacity:1;}100%{opacity:0;}}
    @keyframes loaderTextShimmer{0%{background-position:100% 0;}100%{background-position:-100% 0;}}
    @keyframes overlayFadeOut{0%{opacity:1;visibility:visible;}100%{opacity:0;visibility:hidden;}}
    @keyframes loaderFloat{0%,100%{transform:translateY(0);}50%{transform:translateY(-14px);}}

    #loadingOverlay{
      position:fixed;inset:0;z-index:99999;
      display:flex;flex-direction:column;align-items:center;justify-content:center;
      background:linear-gradient(160deg,#1e0e3a 0%,#2d1560 50%,#1a0a2e 100%);
      transition:opacity .7s ease, visibility .7s ease;
    }
    #loadingOverlay::before{
      content:'';position:absolute;inset:0;
      background:
        radial-gradient(ellipse at 25% 30%,rgba(212,175,55,.14) 0%,transparent 55%),
        radial-gradient(ellipse at 78% 68%,rgba(232,160,191,.12) 0%,transparent 55%);
      animation:heroAurora 6s ease-in-out infinite alternate;
    }
    #loadingOverlay.hide{animation:overlayFadeOut .7s ease forwards;pointer-events:none;}

    .loader-stage{
      position:relative;z-index:2;display:flex;flex-direction:column;align-items:center;
      animation:loaderFloat 3.2s ease-in-out infinite;
    }
    .loader-rings{position:relative;width:130px;height:130px;margin-bottom:34px;}
    .loader-ring{
      position:absolute;inset:0;border-radius:50%;
      border:3px solid transparent;
    }
    .loader-ring.r1{
      border-top-color:#D4AF37;border-right-color:rgba(212,175,55,.25);
      animation:loaderRotate 1.4s linear infinite;
    }
    .loader-ring.r2{
      inset:14px;
      border-bottom-color:#ee2a7b;border-left-color:rgba(238,42,123,.25);
      animation:loaderRotateRev 1.8s linear infinite;
    }
    .loader-ring.r3{
      inset:28px;
      border-top-color:#6efabc;border-right-color:rgba(110,250,188,.2);
      animation:loaderRotate 1.1s linear infinite;
    }
    .loader-cake{
      position:absolute;inset:0;display:flex;align-items:center;justify-content:center;
      font-size:2.4em;
      animation:loaderCakeBounce 1.6s ease-in-out infinite;
      filter:drop-shadow(0 0 14px rgba(212,175,55,.5));
    }
    .loader-title{
      font-family:'Playfair Display',serif;font-size:1.5em;font-weight:700;letter-spacing:1px;
      background:linear-gradient(135deg,#fff 30%,#D4AF37 60%,#FFE4B5 80%,#fff);
      background-size:200% 100%;
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
      animation:loaderTextShimmer 3s ease-in-out infinite;
      margin-bottom:10px;
    }
    .loader-status{
      font-size:.85em;color:rgba(255,255,255,.55);letter-spacing:.5px;
      display:flex;align-items:center;gap:4px;margin-bottom:26px;
    }
    .loader-status .dot{animation:loaderDots 1.4s ease-in-out infinite;}
    .loader-status .dot:nth-child(2){animation-delay:.2s;}
    .loader-status .dot:nth-child(3){animation-delay:.4s;}
    .loader-bar-track{
      width:220px;height:5px;border-radius:999px;overflow:hidden;
      background:rgba(255,255,255,.08);border:1px solid rgba(212,175,55,.2);
    }
    .loader-bar-fill{
      height:100%;width:0%;border-radius:999px;
      background:linear-gradient(90deg,#D4AF37,#ee2a7b,#6efabc,#D4AF37);
      background-size:300% 100%;
      animation:goldSlide 2.5s linear infinite;
      transition:width 2.4s cubic-bezier(.4,.1,.2,1);
    }
  </style>
</head>
<body>

<!-- ═══════════════ LOADING OVERLAY ═══════════════ -->
<div id="loadingOverlay">
  <div class="loader-stage">
    <div class="loader-rings">
      <div class="loader-ring r1"></div>
      <div class="loader-ring r2"></div>
      <div class="loader-ring r3"></div>
      <div class="loader-cake">🎂</div>
    </div>
    <div class="loader-title">YOLAZCAKE</div>
    <div class="loader-status">
      Memproses pesanan Anda<span class="dot">.</span><span class="dot">.</span><span class="dot">.</span>
    </div>
    <div class="loader-bar-track"><div class="loader-bar-fill" id="loaderBar"></div></div>
  </div>
</div>

<div id="particles"></div>
<div id="confetti-container"></div>

<!-- ═══════════════ HERO ═══════════════ -->
<div class="page-hero" id="pageHero">
  <div class="hero-inner">
    <p class="hero-eyebrow">✦ YOLAZCAKE Sintang ✦</p>
    <h1>Konfirmasi Pesanan</h1>
    <p class="hero-sub">Pembayaran berhasil & pesanan sedang diproses</p>
    <div class="hero-divider">
      <span></span><span class="diamond">✦ ✦ ✦</span><span></span>
    </div>
  </div>
</div>

<!-- ═══════════════ PAGE WRAPPER ═══════════════ -->
<div class="page-wrapper">

  <!-- SUCCESS ANIMATION -->
  <div class="success-section">
    <div class="icon-wrap">
      <div class="ring"></div>
      <div class="ring"></div>
      <div class="ring"></div>
      <div class="check-circle">
        <svg viewBox="0 0 52 52">
          <polyline points="14,27 22,36 38,18"/>
        </svg>
      </div>
    </div>
    <div class="success-title">🎉 Pesanan Berhasil Diproses!</div>
    <p class="success-sub">
      Halo <strong><?= htmlspecialchars($nama_pemesan) ?></strong>, terima kasih telah memesan di YOLAZCAKE!<br>
      Tim kami segera memproses pesanan Anda dengan penuh cinta 🎂
    </p>
  </div>

  <!-- GOLD RULE -->
  <div class="gold-rule"><span>✦ ✦ ✦</span></div>

  <!-- KODE PESANAN BANNER -->
  <div class="kode-banner">
    <div>
      <div class="kode-label">Kode Pesanan Anda</div>
      <div class="kode-value"><?= $kode_pesanan ?></div>
    </div>
    <span class="kode-pulse">⚡ Sedang Diproses</span>
  </div>

  <!-- INFO PEMESAN -->
  <div class="order-card">
    <div class="card-header">
      <span>👤</span><h3>Informasi Pemesan</h3>
    </div>
    <div class="info-row">
      <div class="info-label">👤 Nama</div>
      <div class="info-val"><?= htmlspecialchars($nama_pemesan) ?></div>
    </div>
    <div class="info-row">
      <div class="info-label">📱 No. HP</div>
      <div class="info-val"><?= htmlspecialchars($no_hp) ?></div>
    </div>
    <div class="info-row">
      <div class="info-label">📅 Tanggal</div>
      <div class="info-val"><?= date('d M Y, H:i', strtotime($tanggal)) ?> WIB</div>
    </div>
    <div class="info-row">
      <div class="info-label">💳 Pembayaran</div>
      <div class="info-val gold">QRIS</div>
    </div>
    <div class="info-row">
      <div class="info-label">🔖 Status Bayar</div>
      <div class="info-val green">✓ Lunas</div>
    </div>
  </div>

  <!-- ITEMS YANG DIPESAN -->
  <?php if(!empty($items)): ?>
  <div class="items-card">
    <div class="card-header">
      <span>🛒</span><h3>Item yang Dipesan</h3>
    </div>
    <table class="items-table">
      <thead>
        <tr>
          <th>Produk</th>
          <th style="text-align:center;">Qty</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($items as $item): ?>
        <tr>
          <td><?= htmlspecialchars($item['nama']) ?></td>
          <td style="text-align:center;"><span class="item-qty"><?= $item['jumlah'] ?></span></td>
          <td>Rp <?= number_format($item['subtotal'],0,',','.') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <!-- TOTAL HARGA -->
  <div class="total-box">
    <div class="total-label-wrap">
      <div class="tl">💰 Total Pembayaran</div>
      <div class="tv">Rp <?= number_format($total_harga,0,',','.') ?></div>
    </div>
    <span class="paid-badge">✅ Sudah Dibayar</span>
  </div>

  <!-- STATUS TIMELINE -->
  <div class="timeline">
    <div class="timeline-title">📍 Status Pesanan Anda</div>
    <div class="timeline-steps">
      <div class="tl-step">
        <div class="tl-dot done">✅</div>
        <div class="tl-lbl">Pesanan<br>Diterima</div>
      </div>
      <div class="tl-connector"></div>
      <div class="tl-step">
        <div class="tl-dot done">💳</div>
        <div class="tl-lbl">Pembayaran<br>Lunas</div>
      </div>
      <div class="tl-connector"></div>
      <div class="tl-step">
        <div class="tl-dot active">⏳</div>
        <div class="tl-lbl active-lbl">Sedang<br>Diproses</div>
      </div>
      <div class="tl-connector"></div>
      <div class="tl-step">
        <div class="tl-dot pending">📦</div>
        <div class="tl-lbl">Siap<br>Diambil</div>
      </div>
      <div class="tl-connector"></div>
      <div class="tl-step">
        <div class="tl-dot pending">🎉</div>
        <div class="tl-lbl">Selesai</div>
      </div>
    </div>
  </div>

  <!-- ACTION BUTTONS -->
  <div class="actions">
    <a href="../index.php" class="btn-premium btn-gold">🏠 Kembali ke Beranda</a>
    <a href="detail_pemesanan.php?id=<?= $id_pemesanan ?>" class="btn-premium btn-green">🔍 Lihat Detail Pesanan</a>
    <a href="menuu.php" class="btn-premium btn-outline">🎂 Pesan Lagi</a>
  </div>

</div>

<!-- FOOTER -->
<div class="site-footer">
  © 2026 YOLAZCAKE Sintang • Cafe • Bakery • Boutique<br>
  Jl. Lintas Melawi, Ladang, Sintang, Kalimantan Barat<br>
  WA: 0815-7815-7888
</div>

<!-- ═══════════════ SCRIPTS ═══════════════ -->
<script>
  /* Hero sparkles */
  (function(){
    const hero = document.getElementById('pageHero');
    const colors=['#D4AF37','#FFE4B5','#E8A0BF','#fff','#f9ce34','#b8860b'];
    for(let i=0;i<22;i++){
      const d=document.createElement('div');d.className='sparkle';
      const s=Math.random()*5+2;
      d.style.cssText=`width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;bottom:${Math.random()*30}%;animation-duration:${4+Math.random()*7}s;animation-delay:${Math.random()*5}s;opacity:0;`;
      hero.appendChild(d);
    }
  })();

  /* Background particles */
  (function(){
    const c=document.getElementById('particles');
    const colors=['rgba(212,175,55,.4)','rgba(232,160,191,.3)','rgba(255,255,255,.15)'];
    for(let i=0;i<16;i++){
      const p=document.createElement('div');p.className='particle';
      const s=Math.random()*5+2;
      p.style.cssText=`width:${s}px;height:${s}px;background:${colors[Math.floor(Math.random()*colors.length)]};left:${Math.random()*100}%;animation-duration:${10+Math.random()*12}s;animation-delay:${Math.random()*10}s;`;
      c.appendChild(p);
    }
  })();

  /* 🎊 CONFETTI SURPRISE — muncul saat halaman selesai load */
  function launchConfetti(){
    const container = document.getElementById('confetti-container');
    const colors    = ['#D4AF37','#FFE4B5','#6efabc','#ee2a7b','#fff','#b8860b','#c06efa','#6395fa','#f9ce34'];
    const shapes    = ['square','rect','circle'];
    const count     = 120;

    for(let i=0;i<count;i++){
      setTimeout(()=>{
        const el    = document.createElement('div');
        el.className= 'confetti-piece';
        const shape = shapes[Math.floor(Math.random()*shapes.length)];
        const color = colors[Math.floor(Math.random()*colors.length)];
        const size  = Math.random()*10+6;
        const left  = Math.random()*100;
        const delay = Math.random()*0.8;
        const dur   = 2.5 + Math.random()*2;

        let extra='';
        if(shape==='circle') extra='border-radius:50%;';
        if(shape==='rect')   extra=`width:${size*2}px;`;

        el.style.cssText = `
          left:${left}%;
          width:${size}px;height:${size}px;
          background:${color};
          opacity:${0.7+Math.random()*0.3};
          ${extra}
          animation-duration:${dur}s;
          animation-delay:${delay}s;
        `;
        container.appendChild(el);
        setTimeout(()=>el.remove(), (delay+dur)*1000+300);
      }, i * 18);
    }
  }

  /* Tembak confetti setelah animasi check selesai */
  setTimeout(launchConfetti, 2200);
  /* Tembak gelombang kedua */
  setTimeout(launchConfetti, 4500);

  /* ═══ LOADING OVERLAY CONTROLLER ═══ */
  (function(){
    const overlay = document.getElementById('loadingOverlay');
    const bar      = document.getElementById('loaderBar');
    const statusEl = document.querySelector('#loadingOverlay .loader-status');

    const steps = [
      'Memvalidasi pesanan',
      'Menyimpan data ke sistem',
      'Mengonfirmasi pembayaran',
      'Menyiapkan konfirmasi'
    ];
    let i = 0;
    function setStep(text){
      statusEl.innerHTML = text + '<span class="dot">.</span><span class="dot">.</span><span class="dot">.</span>';
    }

    // isi progress bar bertahap
    requestAnimationFrame(()=> bar.style.width = '100%');

    const stepInterval = setInterval(()=>{
      i++;
      if (i < steps.length){
        setStep(steps[i]);
      } else {
        clearInterval(stepInterval);
      }
    }, 600);

    // tutup overlay setelah "selesai memproses"
    window.addEventListener('load', function(){
      setTimeout(()=>{
        overlay.classList.add('hide');
        setTimeout(()=> overlay.remove(), 750);
      }, 2500);
    });
  })();
</script>

</body>
</html>
