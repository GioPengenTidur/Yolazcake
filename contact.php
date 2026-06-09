<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact - YOLAZCAKE Sintang</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- NAVBAR -->
<nav>

  <!-- LEFT -->
  <div class="nav-left">
    <img src="https://lh3.googleusercontent.com/gps-cs/ACgwaOvrk66Mw6TuaNg3tG6p8G9hJq_wOTUoBmpbb3qtX0t9CN0D6K8ns6HxQUsk_xRrGiRBD__9n78mwhr3RZ7cwM3UINa2Jjzvzx2U1l8S2SP93wZa3ga4xfn1BY446aaj_CJ_6ACQYiN58RQ=w203-h304-k-no">
    <h2>YOLAZCAKE</h2>
  </div>

  <!-- CENTER -->
  <ul class="main-nav">
    <li onclick="window.location.href='index.php'">Home</li>
    <li onclick="window.location.href='produk/menu.php'">Menu</li>
    <li onclick="window.location.href='gallery.php'">Gallery</li>
    <li onclick="window.location.href='about.php'">About</li>
    <li class="active" onclick="window.location.href='contact.php'">Contact</li>
  </ul>

  <!-- RIGHT -->
   <div class="nav-right">

<?php if(isset($_SESSION['username'])){ ?>

<div class="account-dropdown">

<button class="account-btn">
👤 <?php echo htmlspecialchars($_SESSION['username']); ?> ▼
</button>

<div class="account-menu">

<a href="member/member.php">
Member
</a>

<a href="auth/logout.php">
Logout
</a>

</div>

</div>

<?php } else { ?>

<button class="login-btn"
onclick="window.location.href='auth/login.php'">
Login
</button>

<?php } ?>


<div class="hamburger" onclick="toggleMenu()" id="hamburger">☰</div>
<div class="dark-btn" onclick="toggleDark()">🌙</div>

</div>

  <!-- DROPDOWN -->
  <div class="dropdown" id="dropdown">

    <p onclick="window.location.href='about.php#story'">
      Back Story
    </p>

    <p onclick="window.location.href='produk/menu.php#Product'">
      Featured Product
    </p>

    <p onclick="window.location.href='produk/menu.php#promo'">
      Promo
    </p>

    <p onclick="window.location.href='gallery.php#Rating'">
      Rating
    </p>

    <p onclick="window.location.href='gallery.php#gallery'">
      Gallery
    </p>

    <p onclick="window.location.href='about.php#team'">
      Team
    </p>

    <p onclick="window.location.href='contact.php#location'">
      Location
    </p>

    <p onclick="window.location.href='contact.php#contact'">
      Contact & Sosmed
    </p>

  </div>

</nav>

<!-- LOCATION -->
<section id="location">

  <h2>Lokasi & Jam Operasional</h2>

  <div class="grid">

    <div class="card fade">

      <h3 style="padding:20px">
        Alamat
      </h3>

      <iframe 
      src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d997.453989154095!2d111.48428296954839!3d0.06236769999606715!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31fe210037bc6e03%3A0x8886e2dfe7f1e1e3!2sYOLAZCAKE%20SINTANG!5e0!3m2!1sen!2sid!4v1777045337846!5m2!1sen!2sid"
      width="100%" 
      height="280"
      style="border:0; padding:0 20px 20px; border-radius:12px;"
      allowfullscreen=""
      loading="lazy">
      </iframe>

      <p style="padding:0 20px 30px">
        Jl. Lintas Melawi, Ladang, Kec. Sintang, Kabupaten Sintang, Kalimantan Barat
      </p>

      <p style="padding:0 20px 30px">
        <strong>WA:</strong> 0815-7815-7888
      </p>

    </div>

    <div class="card fade">

      <h3 style="padding:20px">
        Jam Buka
      </h3>

      <p style="padding:0 20px 10px">
        Senin – Minggu: 08.00 – 22.00 WIB
      </p>

      <p style="padding:0 20px">
        Boutique Lantai 2 buka sampai pukul 21.00
      </p>

    </div>

  </div>

</section>

<!-- CONTACT -->
<section id="contact">

  <h2>Media Sosial & Kontak</h2>

  <div class="grid">

    <!-- INSTAGRAM -->
    <div class="card fade"
    style="text-align:center;padding:40px">

      <h3>Instagram</h3>

      <div class="ig-icon">
        <span></span>
      </div>

      <a href="https://www.instagram.com/yolazcake.stg/"
      target="_blank"
      class="contact-info">

        instagram.com/yolazcake.stg

      </a>

      <p style="margin-top:20px">
        Ikuti kami untuk update menu terbaru & promo!
      </p>

    </div>

    <!-- FORM -->
    <div class="card fade">

      <h3 style="text-align:center;margin-bottom:20px">
        Hubungi Kami via WhatsApp
      </h3>

      <form id="waForm">

        <input
        type="text"
        id="wa_nama"
        placeholder="Nama Lengkap"
        required

        style="
        width:100%;
        padding:12px;
        margin-bottom:10px;
        border-radius:10px;
        border:1px solid #ccc;
        ">

        <input
        type="tel"
        id="wa_nomor"
        placeholder="Nomor WhatsApp Anda"
        required

        style="
        width:100%;
        padding:12px;
        margin-bottom:10px;
        border-radius:10px;
        border:1px solid #ccc;
        ">

        <textarea
        id="wa_pesan"
        placeholder="Pesan / Reservasi"
        required

        style="
        width:100%;
        padding:12px;
        margin-bottom:15px;
        border-radius:10px;
        border:1px solid #ccc;
        height:120px;
        "></textarea>

        <button type="submit"
        style="width:100%">
          Kirim ke WhatsApp
        </button>

      </form>

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

<script src="js/style.js"></script>

</body>
</html>
