<?php
require_once '../config/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nama_pemesan = mysqli_real_escape_string($conn, $_POST['nama_pemesan']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $tanggal_booking = $_POST['tanggal_booking'];

    date_default_timezone_set('Asia/Jakarta');

    $tanggal_input = date('Y-m-d', strtotime($tanggal_booking));
    $tanggal_hari_ini = date('Y-m-d');

    if ($tanggal_input < $tanggal_hari_ini) {
        $error_type = 'tanggal';
        $error_msg = 'Tanggal booking tidak boleh kurang dari hari ini!';
        // akan ditangani di HTML
    } else {

        $jam_booking = $_POST['jam_booking'];

        if ($jam_booking < '08:00' || $jam_booking > '22:00') {
            $error_type = 'jam';
            $error_msg = 'Booking hanya dapat dilakukan pada jam operasional YOLAZCAKE (08:00 - 22:00).';
        } else {

            $cek_booking = mysqli_query(
                $conn,
                "SELECT COUNT(*) AS total
                 FROM booking
                 WHERE tanggal_booking = '$tanggal_booking'
                 AND jam_booking = '$jam_booking'
                 AND status != 'Dibatalkan'"
            );
            $hasil = mysqli_fetch_assoc($cek_booking);

            if ($hasil['total'] >= 5) {
                $error_type = 'penuh';
                $error_msg = 'Maaf, slot booking pada tanggal dan jam tersebut sudah penuh.';
            } else {

                $jumlah_orang = $_POST['jumlah_orang'];
                $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);

                $query = "INSERT INTO booking (
                                nama_pemesan, no_hp, tanggal_booking,
                                jam_booking, jumlah_orang, catatan
                            ) VALUES (
                                '$nama_pemesan', '$no_hp', '$tanggal_booking',
                                '$jam_booking', '$jumlah_orang', '$catatan'
                            )";

                if (mysqli_query($conn, $query)) {
                    $id_booking = mysqli_insert_id($conn);
                    session_start();
                    $_SESSION['id_booking'] = $id_booking;
                    $_SESSION['nama_pemesan'] = $nama_pemesan;
                    $_SESSION['no_hp'] = $no_hp;
                    $success = true;
                    $tanggal_fmt = date('d F Y', strtotime($tanggal_booking));
                } else {
                    $error_type = 'db';
                    $error_msg = 'Terjadi kesalahan pada sistem: ' . mysqli_error($conn);
                }
            }
        }
    }
} else {
    header("Location: booking.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proses Booking – YOLAZCAKE</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}

    :root {
      --gold:#D4AF37; --gold-light:#FFE4B5; --gold-dark:#b8860b;
      --pink:#E8A0BF; --bg-deep:#1e0e3a; --bg-mid:#2d1560; --bg-dark:#1a0a2e;
      --glass:rgba(255,255,255,.06); --glass-border:rgba(255,255,255,.1);
    }

    body {
      min-height:100vh;
      font-family:'Inter',sans-serif;
      background:linear-gradient(160deg,var(--bg-deep) 0%,var(--bg-mid) 50%,var(--bg-dark) 100%);
      position:relative;
      /* overflow diatur dinamis lewat JS supaya bisa scroll setelah loading */
      overflow:hidden;
      display:flex;align-items:center;justify-content:center;
    }

    body.allow-scroll {
      overflow-y:auto;
      align-items:flex-start;
      padding:40px 16px;
    }

    body::before {
      content:'';position:fixed;inset:0;
      background:
        radial-gradient(ellipse at 20% 30%,rgba(212,175,55,.10) 0%,transparent 55%),
        radial-gradient(ellipse at 80% 70%,rgba(232,160,191,.10) 0%,transparent 55%);
      pointer-events:none;z-index:0;
    }

    /* ===== ORB / FLOATING BLOBS ===== */
    .orb {
      position:fixed;border-radius:50%;pointer-events:none;z-index:0;
      filter:blur(70px);opacity:.35;
    }
    .orb-1 {
      width:420px;height:420px;
      background:radial-gradient(circle,rgba(212,175,55,.55),rgba(212,175,55,.05));
      top:-80px;left:-80px;
      animation:orbFloat1 18s ease-in-out infinite;
    }
    .orb-2 {
      width:350px;height:350px;
      background:radial-gradient(circle,rgba(232,160,191,.55),rgba(232,160,191,.05));
      bottom:-60px;right:-60px;
      animation:orbFloat2 22s ease-in-out infinite;
    }
    .orb-3 {
      width:280px;height:280px;
      background:radial-gradient(circle,rgba(120,80,255,.45),rgba(120,80,255,.05));
      top:40%;left:60%;
      animation:orbFloat3 15s ease-in-out infinite;
    }
    .orb-4 {
      width:200px;height:200px;
      background:radial-gradient(circle,rgba(212,175,55,.35),rgba(232,160,191,.1));
      top:60%;left:10%;
      animation:orbFloat4 25s ease-in-out infinite;
    }
    @keyframes orbFloat1{
      0%,100%{transform:translate(0,0) scale(1);}
      33%{transform:translate(80px,50px) scale(1.1);}
      66%{transform:translate(-30px,80px) scale(.95);}
    }
    @keyframes orbFloat2{
      0%,100%{transform:translate(0,0) scale(1);}
      40%{transform:translate(-70px,-60px) scale(1.08);}
      70%{transform:translate(40px,-30px) scale(.92);}
    }
    @keyframes orbFloat3{
      0%,100%{transform:translate(0,0) scale(1);}
      50%{transform:translate(-100px,60px) scale(1.15);}
    }
    @keyframes orbFloat4{
      0%,100%{transform:translate(0,0) scale(1);}
      30%{transform:translate(60px,-80px) scale(1.1);}
      60%{transform:translate(-40px,40px) scale(.9);}
    }

    /* PARTICLES */
    .particle{position:fixed;border-radius:50%;pointer-events:none;animation:particleFloat linear infinite;z-index:1;}
    @keyframes particleFloat{
      0%{transform:translateY(100vh) scale(0);opacity:0;}
      10%{opacity:.5;}90%{opacity:.3;}
      100%{transform:translateY(-100px) scale(1);opacity:0;}
    }

    /* ===== LOADING DONE ANIMATION ===== */
    .loading-done-wrap {
      display:none;flex-direction:column;align-items:center;gap:12px;
      animation:fadeIn .4s ease forwards;
    }
    .loading-done-wrap.show { display:flex; }
    @keyframes fadeIn{from{opacity:0;transform:scale(.85);}to{opacity:1;transform:scale(1);}}

    .done-circle {
      width:80px;height:80px;border-radius:50%;
      background:radial-gradient(circle,rgba(212,175,55,.25),rgba(212,175,55,.05));
      border:2px solid rgba(212,175,55,.6);
      display:flex;align-items:center;justify-content:center;
      box-shadow:0 0 0 0 rgba(212,175,55,.5);
      animation:doneGlow .6s ease-out forwards;
    }
    @keyframes doneGlow{
      0%{transform:scale(0);box-shadow:0 0 0 0 rgba(212,175,55,.5);}
      60%{transform:scale(1.15);}
      100%{transform:scale(1);box-shadow:0 0 0 20px rgba(212,175,55,0);}
    }
    .done-svg{width:38px;height:38px;}
    .done-circle-path{
      stroke:var(--gold);stroke-width:3;fill:none;
      stroke-dasharray:100;stroke-dashoffset:100;
      animation:drawDoneCircle .4s ease-out forwards .15s;
    }
    .done-check{
      stroke:var(--gold);stroke-width:4;fill:none;stroke-linecap:round;stroke-linejoin:round;
      stroke-dasharray:36;stroke-dashoffset:36;
      animation:drawDoneCheck .3s ease-out forwards .55s;
    }
    @keyframes drawDoneCircle{to{stroke-dashoffset:0;}}
    @keyframes drawDoneCheck{to{stroke-dashoffset:0;}}

    .done-label {
      font-size:.85em;font-weight:600;letter-spacing:2px;
      color:var(--gold);opacity:0;
      animation:fadeUp .4s ease forwards .7s;
    }
    .done-bar-wrap {
      width:180px;height:3px;background:rgba(255,255,255,.1);border-radius:99px;overflow:hidden;
      opacity:0;animation:fadeUp .4s ease forwards .8s;
    }
    .done-bar {
      height:100%;border-radius:99px;
      background:linear-gradient(90deg,var(--gold),var(--gold-light),var(--gold));
      animation:doneFill .5s ease forwards .85s;
      width:0%;
    }
    @keyframes doneFill{from{width:0%;}to{width:100%;}}

    /* ===== LOADING SCREEN ===== */
    #loadingScreen {
      position:fixed;inset:0;z-index:1000;
      background:linear-gradient(160deg,var(--bg-deep) 0%,var(--bg-mid) 50%,var(--bg-dark) 100%);
      display:flex;flex-direction:column;align-items:center;justify-content:center;
      transition:opacity .8s ease, visibility .8s ease;
    }

    #loadingScreen.hide {
      opacity:0;visibility:hidden;
    }

    .loading-logo {
      font-family:'Playfair Display',serif;
      font-size:2em;font-weight:700;
      background:linear-gradient(135deg,#fff 30%,var(--gold) 60%,var(--gold-light) 80%,#fff);
      background-size:200% 100%;
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
      animation:shimmerText 2s ease-in-out infinite;
      margin-bottom:40px;letter-spacing:2px;
    }
    @keyframes shimmerText{0%{background-position:100% 0;}100%{background-position:-100% 0;}}

    /* Premium spinner ring */
    .spinner-wrap {
      position:relative;width:110px;height:110px;margin-bottom:32px;
    }

    .spinner-ring {
      position:absolute;inset:0;border-radius:50%;
      border:2px solid transparent;
      animation:spinRing 1.4s cubic-bezier(.68,-.55,.27,1.55) infinite;
    }
    .spinner-ring:nth-child(1){
      border-top-color:var(--gold);border-right-color:var(--gold);
      animation-duration:1.4s;
    }
    .spinner-ring:nth-child(2){
      inset:12px;
      border-bottom-color:var(--pink);border-left-color:var(--pink);
      animation-duration:1s;animation-direction:reverse;
    }
    .spinner-ring:nth-child(3){
      inset:24px;
      border-top-color:rgba(255,255,255,.5);border-right-color:rgba(255,255,255,.5);
      animation-duration:1.8s;
    }

    @keyframes spinRing {
      0%{transform:rotate(0deg);}
      100%{transform:rotate(360deg);}
    }

    .spinner-center {
      position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
      width:34px;height:34px;
      background:radial-gradient(circle,var(--gold) 0%,var(--gold-dark) 100%);
      border-radius:50%;
      box-shadow:0 0 20px rgba(212,175,55,.7),0 0 40px rgba(212,175,55,.4);
      animation:pulseDot 1.4s ease-in-out infinite;
    }
    @keyframes pulseDot{
      0%,100%{transform:translate(-50%,-50%) scale(1);box-shadow:0 0 20px rgba(212,175,55,.7),0 0 40px rgba(212,175,55,.4);}
      50%{transform:translate(-50%,-50%) scale(1.15);box-shadow:0 0 30px rgba(212,175,55,.9),0 0 60px rgba(212,175,55,.6);}
    }

    .loading-steps {
      display:flex;flex-direction:column;align-items:center;gap:8px;
    }

    .loading-text {
      font-size:.9em;font-weight:500;color:rgba(255,255,255,.75);
      letter-spacing:1px;
      animation:fadeTextCycle 1.8s ease-in-out infinite;
    }

    @keyframes fadeTextCycle{
      0%,100%{opacity:.4;}50%{opacity:1;}
    }

    .loading-dots {
      display:flex;gap:6px;margin-top:4px;
    }

    .loading-dots span {
      width:6px;height:6px;border-radius:50%;background:var(--gold);
      animation:dotBounce .9s ease-in-out infinite;
    }
    .loading-dots span:nth-child(2){animation-delay:.15s;}
    .loading-dots span:nth-child(3){animation-delay:.3s;}

    @keyframes dotBounce{
      0%,100%{transform:translateY(0);opacity:.4;}
      50%{transform:translateY(-8px);opacity:1;}
    }

    .loading-bar-wrap {
      width:220px;height:3px;background:rgba(255,255,255,.1);border-radius:99px;
      margin-top:20px;overflow:hidden;
    }
    .loading-bar {
      height:100%;border-radius:99px;
      background:linear-gradient(90deg,var(--gold),var(--pink),var(--gold));
      background-size:200% 100%;
      animation:barSlide 1.8s ease-in-out forwards, goldSlide 1s linear infinite;
      width:0%;
    }
    @keyframes barSlide{
      0%{width:0%;}30%{width:40%;}65%{width:70%;}100%{width:95%;}
    }
    @keyframes goldSlide{
      0%{background-position:0% 0;}100%{background-position:200% 0;}
    }

    /* ===== RESULT CARD ===== */
    #resultScreen {
      position:relative;z-index:2;
      width:100%;max-width:620px;
      padding:28px 20px;
      display:none;
    }

    #resultScreen.show {
      display:block;animation:cardReveal .9s cubic-bezier(.22,.68,0,1.2) forwards;
    }

    @keyframes cardReveal{
      from{opacity:0;transform:translateY(50px) scale(.95);}
      to{opacity:1;transform:translateY(0) scale(1);}
    }

    .result-card {
      background:rgba(255,255,255,.06);
      backdrop-filter:blur(24px);
      border:1px solid rgba(255,255,255,.1);
      border-radius:28px;
      padding:52px 44px 48px;
      position:relative;overflow:hidden;
      text-align:center;
    }

    .result-card.success {border-color:rgba(212,175,55,.3);}
    .result-card.error   {border-color:rgba(255,100,100,.25);}

    /* Animated top border */
    .result-card::before {
      content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,var(--gold),var(--pink),var(--gold));
      background-size:200% 100%;animation:goldSlide 3s linear infinite;
    }

    .result-card.error::before {
      background:linear-gradient(90deg,#ff6464,#ff9e64,#ff6464);
      background-size:200% 100%;
    }

    /* glow aura */
    .result-card.success::after {
      content:'';position:absolute;inset:-1px;border-radius:28px;
      background:radial-gradient(ellipse at 50% 0%,rgba(212,175,55,.15),transparent 65%);
      pointer-events:none;
    }

    /* ===== SUCCESS ICON ===== */
    .icon-wrap {
      width:100px;height:100px;border-radius:50%;
      margin:0 auto 28px;
      position:relative;
      display:flex;align-items:center;justify-content:center;
    }

    .icon-wrap.success {
      background:radial-gradient(circle,rgba(212,175,55,.2),rgba(212,175,55,.05));
      border:2px solid rgba(212,175,55,.4);
      box-shadow:0 0 0 0 rgba(212,175,55,.5);
      animation:iconPulse 2s ease-out forwards 0s,iconGlow 3s ease-in-out infinite 1s;
    }

    @keyframes iconPulse{
      0%{transform:scale(.5);opacity:0;}
      60%{transform:scale(1.1);}
      100%{transform:scale(1);opacity:1;}
    }
    @keyframes iconGlow{
      0%,100%{box-shadow:0 0 0 0 rgba(212,175,55,.5),0 0 30px rgba(212,175,55,.15);}
      50%{box-shadow:0 0 0 14px rgba(212,175,55,0),0 0 50px rgba(212,175,55,.35);}
    }

    .icon-wrap.error {
      background:radial-gradient(circle,rgba(255,100,100,.15),rgba(255,100,100,.03));
      border:2px solid rgba(255,100,100,.3);
      animation:iconPulse .7s ease-out forwards;
    }

    .icon-wrap .icon-emoji {font-size:2.8em;line-height:1;}

    /* checkmark SVG */
    .check-svg{width:52px;height:52px;}
    .check-circle{
      stroke:var(--gold);stroke-width:3;fill:none;
      stroke-dasharray:166;stroke-dashoffset:166;
      animation:drawCircle .6s ease-out forwards .1s;
    }
    .check-mark{
      stroke:var(--gold);stroke-width:4;fill:none;stroke-linecap:round;stroke-linejoin:round;
      stroke-dasharray:50;stroke-dashoffset:50;
      animation:drawCheck .4s ease-out forwards .7s;
    }
    @keyframes drawCircle{to{stroke-dashoffset:0;}}
    @keyframes drawCheck{to{stroke-dashoffset:0;}}

    .result-title {
      font-family:'Playfair Display',serif;font-size:1.8em;font-weight:700;
      margin-bottom:10px;
    }

    .result-title.success {
      background:linear-gradient(135deg,#fff 30%,var(--gold) 70%);
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
    }

    .result-title.error {
      background:linear-gradient(135deg,#fff 30%,#ff8080 70%);
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
    }

    .result-sub {
      font-size:.88em;color:rgba(255,255,255,.55);margin-bottom:32px;line-height:1.6;
    }

    /* Booking detail card */
    .booking-detail {
      background:rgba(212,175,55,.07);
      border:1px solid rgba(212,175,55,.2);
      border-radius:16px;padding:22px 24px;
      margin-bottom:28px;text-align:left;
    }

    .detail-title {
      font-size:.7em;font-weight:600;letter-spacing:2.5px;text-transform:uppercase;
      color:var(--gold);margin-bottom:14px;
      display:flex;align-items:center;gap:8px;
    }

    .detail-row {
      display:flex;justify-content:space-between;align-items:center;
      padding:9px 0;border-bottom:1px solid rgba(255,255,255,.05);
      font-size:.88em;
    }
    .detail-row:last-child{border-bottom:none;}
    .detail-row .dk {color:rgba(255,255,255,.5);}
    .detail-row .dv {color:#fff;font-weight:500;text-align:right;}

    /* Booking ID badge */
    .booking-id-badge {
      display:inline-flex;align-items:center;gap:8px;
      background:linear-gradient(135deg,rgba(212,175,55,.2),rgba(212,175,55,.08));
      border:1px solid rgba(212,175,55,.4);
      border-radius:12px;padding:10px 20px;margin-bottom:28px;
      font-size:.85em;font-weight:600;letter-spacing:1px;color:var(--gold);
    }

    /* question / action section */
    .question-box {
      background:rgba(255,255,255,.04);
      border:1px solid rgba(255,255,255,.1);
      border-radius:16px;padding:22px 24px;margin-bottom:24px;
      opacity:0;animation:fadeUp .6s ease forwards 1.6s;
    }

    .question-box p {
      font-size:.92em;color:rgba(255,255,255,.75);line-height:1.6;margin-bottom:16px;
    }

    .question-box p strong {color:#fff;}

    @keyframes fadeUp{from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:translateY(0);}}

    /* buttons */
    .btn-row {
      display:flex;gap:12px;
      opacity:0;animation:fadeUp .6s ease forwards 1.9s;
    }

    .btn-prem {
      flex:1;position:relative;padding:15px 22px;border:none;border-radius:14px;
      font-family:'Inter',sans-serif;font-size:.88em;font-weight:700;
      letter-spacing:1.5px;text-transform:uppercase;cursor:pointer;
      overflow:hidden;transition:transform .25s cubic-bezier(.34,1.56,.64,1),box-shadow .35s;
      text-decoration:none;display:flex;align-items:center;justify-content:center;gap:8px;
    }

    .btn-prem::before {
      content:'';position:absolute;inset:0;
      background:linear-gradient(90deg,transparent,rgba(255,255,255,.15),transparent);
      transform:translateX(-100%);transition:transform .5s;
    }
    .btn-prem:hover::before{transform:translateX(100%);}
    .btn-prem:hover{transform:translateY(-3px) scale(1.02);}

    .btn-gold {
      background:linear-gradient(135deg,var(--gold) 0%,var(--gold-dark) 50%,var(--gold) 100%);
      background-size:200% 100%;color:var(--bg-deep);
      animation:goldSlide 3s linear infinite;
      box-shadow:0 8px 28px rgba(212,175,55,.35),0 0 40px rgba(212,175,55,.18);
    }
    .btn-gold:hover{box-shadow:0 12px 40px rgba(212,175,55,.55),0 0 60px rgba(212,175,55,.3);}

    .btn-ghost {
      background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.14);
      color:rgba(255,255,255,.7);
    }
    .btn-ghost:hover{
      background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.28);
      box-shadow:0 8px 24px rgba(255,255,255,.07);
    }

    .btn-danger {
      background:rgba(255,100,100,.15);border:1px solid rgba(255,100,100,.3);
      color:#ff8080;
    }
    .btn-danger:hover{
      background:rgba(255,100,100,.22);border-color:rgba(255,100,100,.5);
      box-shadow:0 8px 24px rgba(255,100,100,.15);
    }

    /* stars burst */
    .stars-wrap{
      position:fixed;inset:0;pointer-events:none;z-index:999;overflow:hidden;
    }
    .star-burst{
      position:absolute;
      width:6px;height:6px;border-radius:50%;
      animation:starBurst ease-out forwards;
    }
    @keyframes starBurst{
      0%{transform:translate(0,0) scale(1);opacity:1;}
      100%{transform:translate(var(--dx),var(--dy)) scale(0);opacity:0;}
    }

    /* gold rule */
    .gold-rule{display:flex;align-items:center;gap:10px;margin:24px 0 20px;}
    .gold-rule::before,.gold-rule::after{content:'';flex:1;height:1px;background:linear-gradient(to right,transparent,rgba(212,175,55,.4));}
    .gold-rule::after{background:linear-gradient(to left,transparent,rgba(212,175,55,.4));}
    .gold-rule span{color:var(--gold);font-size:.65em;letter-spacing:3px;}

    @media(max-width:560px){
      .result-card{padding:36px 20px 32px;}
      .btn-row{flex-direction:column;}
      .result-title{font-size:1.5em;}
    }
  </style>
</head>
<body>

<!-- PARTICLES -->
<div id="particles"></div>

<!-- FLOATING ORBS -->
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>
<div class="orb orb-4"></div>

<!-- STARS BURST -->
<div class="stars-wrap" id="starsWrap"></div>

<!-- ===== LOADING SCREEN ===== -->
<div id="loadingScreen">
  <div class="loading-logo">✦ YOLAZCAKE</div>

  <!-- Spinner (sembunyikan saat done) -->
  <div class="spinner-wrap" id="spinnerWrap">
    <div class="spinner-ring"></div>
    <div class="spinner-ring"></div>
    <div class="spinner-ring"></div>
    <div class="spinner-center"></div>
  </div>

  <!-- Done animation (muncul sebelum halaman pindah) -->
  <div class="loading-done-wrap" id="doneWrap">
    <div class="done-circle">
      <svg class="done-svg" viewBox="0 0 40 40" fill="none">
        <circle class="done-circle-path" cx="20" cy="20" r="17"/>
        <path class="done-check" d="M11 20 L18 27 L30 13"/>
      </svg>
    </div>
    <div class="done-label">SELESAI</div>
    <div class="done-bar-wrap"><div class="done-bar"></div></div>
  </div>

  <div class="loading-steps" id="loadingSteps">
    <div class="loading-text" id="loadingText">Memproses booking Anda...</div>
    <div class="loading-dots">
      <span></span><span></span><span></span>
    </div>
  </div>

  <div class="loading-bar-wrap" id="loadingBarWrap">
    <div class="loading-bar" id="loadingBar"></div>
  </div>
</div>

<!-- ===== RESULT SCREEN ===== -->
<div id="resultScreen">

<?php if (!empty($success)): ?>
  <!-- SUCCESS -->
  <div class="result-card success">

    <div class="icon-wrap success">
      <svg class="check-svg" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle class="check-circle" cx="26" cy="26" r="24"/>
        <path class="check-mark" d="M14 26 L22 34 L38 18"/>
      </svg>
    </div>

    <h2 class="result-title success">Booking Berhasil!</h2>
    <p class="result-sub">Meja Anda telah berhasil dipesan. Kami menunggu kehadiran Anda di YOLAZCAKE ✦</p>

    <div class="booking-id-badge">
      🎫 ID Booking <strong>#<?= str_pad($id_booking, 4, '0', STR_PAD_LEFT) ?></strong>
    </div>

    <div class="booking-detail">
      <div class="detail-title">✦ &nbsp;Detail Reservasi</div>
      <div class="detail-row">
        <span class="dk">👤 Nama</span>
        <span class="dv"><?= htmlspecialchars($nama_pemesan) ?></span>
      </div>
      <div class="detail-row">
        <span class="dk">📱 No. HP</span>
        <span class="dv"><?= htmlspecialchars($no_hp) ?></span>
      </div>
      <div class="detail-row">
        <span class="dk">📅 Tanggal</span>
        <span class="dv"><?= $tanggal_fmt ?></span>
      </div>
      <div class="detail-row">
        <span class="dk">🕐 Jam</span>
        <span class="dv"><?= htmlspecialchars($jam_booking) ?> WIB</span>
      </div>
      <div class="detail-row">
        <span class="dk">👥 Jumlah Orang</span>
        <span class="dv"><?= htmlspecialchars($jumlah_orang) ?> orang</span>
      </div>
      <?php if (!empty($catatan)): ?>
      <div class="detail-row">
        <span class="dk">📝 Catatan</span>
        <span class="dv" style="max-width:60%;word-break:break-word;"><?= htmlspecialchars($catatan) ?></span>
      </div>
      <?php endif; ?>
    </div>

    <div class="question-box">
      <p>Ingin melengkapi kunjungan Anda? <strong>Pesan makanan & minuman</strong> sekarang dan nikmati kemudahan tanpa antri saat tiba! 🍰</p>
    </div>

    <div class="btn-row">
      <a href="../index.php" class="btn-prem btn-ghost">🏠 Kembali ke Beranda</a>
      <a href="../pemesanan/menuu.php?id_booking=<?= $id_booking ?>" class="btn-prem btn-gold">🍰 Pesan Makanan</a>
    </div>

  </div>

<?php else: ?>
  <!-- ERROR -->
  <div class="result-card error">

    <div class="icon-wrap error">
      <span class="icon-emoji">⚠️</span>
    </div>

    <h2 class="result-title error">Booking Gagal</h2>
    <p class="result-sub"><?= htmlspecialchars($error_msg ?? 'Terjadi kesalahan yang tidak diketahui.') ?></p>

    <div class="gold-rule"><span>✦ ✦ ✦</span></div>

    <div class="btn-row" style="opacity:1;animation:none;margin-top:8px;">
      <a href="booking.php" class="btn-prem btn-danger">← Coba Lagi</a>
      <a href="../index.php" class="btn-prem btn-ghost">🏠 Beranda</a>
    </div>
  </div>

<?php endif; ?>

</div><!-- /resultScreen -->

<script>
(function(){
  // Particles
  const c=document.getElementById('particles');
  const cols=['rgba(212,175,55,.4)','rgba(232,160,191,.3)','rgba(255,255,255,.15)'];
  for(let i=0;i<18;i++){
    const p=document.createElement('div');p.className='particle';
    const s=Math.random()*5+2;
    p.style.cssText=`width:${s}px;height:${s}px;background:${cols[Math.floor(Math.random()*cols.length)]};left:${Math.random()*100}%;animation-duration:${10+Math.random()*12}s;animation-delay:${Math.random()*10}s;z-index:1;`;
    c.appendChild(p);
  }

  // Loading sequence
  const isSuccess = <?= !empty($success) ? 'true' : 'false' ?>;
  const texts = isSuccess
    ? ['Memproses data Anda…','Menyimpan reservasi…','Mengkonfirmasi slot meja…','Menyiapkan konfirmasi…']
    : ['Memvalidasi data Anda…','Memeriksa ketersediaan…'];

  const textEl   = document.getElementById('loadingText');
  const spinnerWrap = document.getElementById('spinnerWrap');
  const doneWrap    = document.getElementById('doneWrap');
  const loadingSteps= document.getElementById('loadingSteps');
  const loadingBarWrap = document.getElementById('loadingBarWrap');

  let ti = 0;
  const cycleText = () => {
    if(ti < texts.length){ textEl.textContent = texts[ti++]; }
  };
  const txtInterval = setInterval(cycleText, 700);
  cycleText();

  // Delay sebelum tampilkan animasi "done"
  const delay = isSuccess ? 2400 : 1200;

  setTimeout(() => {
    clearInterval(txtInterval);

    // Tampilkan animasi done (spinner → centang)
    textEl.textContent = isSuccess ? 'Booking berhasil diproses!' : 'Selesai diproses…';
    spinnerWrap.style.transition = 'opacity .3s, transform .3s';
    spinnerWrap.style.opacity = '0';
    spinnerWrap.style.transform = 'scale(.8)';
    loadingBarWrap.style.transition = 'opacity .3s';
    loadingBarWrap.style.opacity = '0';

    setTimeout(() => {
      spinnerWrap.style.display = 'none';
      loadingBarWrap.style.display = 'none';
      doneWrap.classList.add('show');

      // Setelah animasi done selesai, fade keluar loading & tampil result
      setTimeout(() => {
        const ls = document.getElementById('loadingScreen');
        ls.classList.add('hide');

        setTimeout(() => {
          ls.style.display = 'none';

          // Aktifkan scroll setelah loading hilang
          document.body.classList.add('allow-scroll');

          const rs = document.getElementById('resultScreen');
          rs.classList.add('show');

          // Scroll ke atas konten
          window.scrollTo({top:0, behavior:'smooth'});

          // Stars burst only on success
          if(isSuccess) launchStars();
        }, 800);
      }, 1400); // durasi animasi done sebelum fade keluar

    }, 350);

  }, delay);

  // Star burst celebration
  function launchStars(){
    const wrap = document.getElementById('starsWrap');
    const colors=['#D4AF37','#FFE4B5','#E8A0BF','#ffffff','#f9ce34','#ee2a7b'];
    const cx = window.innerWidth/2, cy = window.innerHeight/2;

    for(let i=0;i<55;i++){
      const star = document.createElement('div');
      star.className = 'star-burst';
      const angle = Math.random() * Math.PI * 2;
      const dist = 120 + Math.random() * 280;
      const dx = Math.cos(angle) * dist;
      const dy = Math.sin(angle) * dist;
      const size = 4 + Math.random() * 8;
      const dur = 0.7 + Math.random() * 0.8;
      const delay2 = Math.random() * 0.5;
      star.style.cssText = `
        left:${cx}px;top:${cy}px;
        width:${size}px;height:${size}px;
        background:${colors[Math.floor(Math.random()*colors.length)]};
        border-radius:${Math.random()>.5?'50%':'3px'};
        --dx:${dx}px;--dy:${dy}px;
        animation-duration:${dur}s;
        animation-delay:${delay2}s;
        box-shadow:0 0 6px ${colors[0]};
      `;
      wrap.appendChild(star);
      setTimeout(()=>star.remove(),(dur+delay2+.1)*1000);
    }

    // Second wave
    setTimeout(()=>{
      for(let i=0;i<30;i++){
        const star=document.createElement('div');star.className='star-burst';
        const angle=Math.random()*Math.PI*2;
        const dist=60+Math.random()*200;
        const dx=Math.cos(angle)*dist;
        const dy=Math.sin(angle)*dist;
        const size=3+Math.random()*6;
        const dur=0.6+Math.random()*0.6;
        const delay2=Math.random()*0.4;
        star.style.cssText=`
          left:${cx}px;top:${cy}px;
          width:${size}px;height:${size}px;
          background:${colors[Math.floor(Math.random()*colors.length)]};
          border-radius:50%;
          --dx:${dx}px;--dy:${dy}px;
          animation-duration:${dur}s;animation-delay:${delay2}s;
        `;
        wrap.appendChild(star);
        setTimeout(()=>star.remove(),(dur+delay2+.1)*1000);
      }
    }, 500);
  }
})();
</script>

</body>
</html>
