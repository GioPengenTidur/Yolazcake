<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gallery - YOLAZCAKE Sintang</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- NAVBAR -->
<nav>

  <!-- LEFT -->
  <div class="nav-left">
    <img src="https://lh3.googleusercontent.com/gps-cs/ACgwaOvxohuhY52bPDZuekQ33U7e3zGj4STY3XbWIuJFGodKp7_LNIk7cZZH854xdPWD1ZIslCTr69dulDH6xFPnWL6jblC-UQjruHAUO-ewDjjztuwY1hwNCxEqMdQpKJumxj105Vv_jJZFjXo=w203-h304-k-no">
    <h2>YOLAZCAKE</h2>
  </div>

  <!-- CENTER -->
  <ul class="main-nav">
    <li onclick="window.location.href='index.php'">Home</li>
    <li onclick="window.location.href='produk/menu.php'">Menu</li>
    <li class="active" onclick="window.location.href='../gallery.php'">Gallery</li>
    <li onclick="window.location.href='about.php'">About</li>
    <li onclick="window.location.href='contact.php'">Contact</li>
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

<!-- GALLERY -->
<section id="gallery">

  <h2>Galeri YOLAZCAKE</h2>

  <div class="grid">

    <div class="card fade">
      <div class="card-bg"
      style="background-image:url('https://lh3.googleusercontent.com/gps-cs-s/APNQkAH7ipBFsSxhjhnwkXBTuy71HTo-psQHfnyHd8wuvyPGVB0u3ytjpgmiyRRLr0j3urAxueVEGj7xx82_aRv2eAAhj98CkBCVWROyNnScQ1mc7J26O7x7jGuAo8RcZSKuDijU8ScaiGelYymU=w203-h270-k-no')">
      </div>

      <h3>Interior Cafe</h3>
      <p>Pemandangan di dalam cafe</p>
    </div>

    <div class="card fade">
      <div class="card-bg"
      style="background-image:url('https://lh3.googleusercontent.com/gps-cs-s/APNQkAEZtydrykpJbwuRHxQODQm9NXJOVdFZoaA8FDBRxijt_xMOIQsgKV5_HkV9UI_Zr-ksLLyDM9w3H744cZhcnVNsEwwm6CQJrh9BXhc4NmMD8yxb6ajSwi7FwVPirQvB5-hwfYJqrGN88wy9=w203-h270-k-no')">
      </div>

      <h3>Display Kue</h3>
      <p>Pameran kue yang baru keluar dari oven</p>
    </div>

    <div class="card fade">
      <div class="card-bg"
      style="background-image:url('https://lh3.googleusercontent.com/gps-cs-s/APNQkAGmYlPqHV4AIbKMVkdAsz7_dxTdYGggEfu-LfdCKIYkNnwMYb4-1pJ1hAXVTWpgALJha09Yun2loJ_vRn5AONUU2LRTrtw7aAObAnfCRmnaSmOL9C0TjGwrbDul94lAloiEjwckQObpmrM=w203-h270-k-no')">
      </div>

      <h3>Coffee Corner</h3>
      <p>Tempat pembuatan coffee</p>
    </div>

    <div class="card fade">
      <div class="card-bg"
      style="background-image:url('https://lh3.googleusercontent.com/gps-cs-s/APNQkAGe6TtlEB63M_38pWHypN5RgdEpc-MPweIkN612OoMq6jVFNIYvjif89o7cFH8WVJ74_nlMy-qMu-UAyYRhJSOeEUbA9e9kOuLtDKBXbjN6fRPR4QHhMmR3a7XbkkrxnoLjeHNsC6oueVuz=w203-h360-k-no')">
      </div>

      <h3>Boutique Lantai 2</h3>
      <p>Pakaian wanita modern & aesthetic</p>
    </div>

  </div>

</section>

<!-- RATING -->
<section id="Rating">

  <h2>Rating & Testimoni</h2>

  <div id="Ulasan">

    <h3 id="Stars">
      4,8 <span class="rating-stars">★★★★★</span>
    </h3>

    <p id="JumlahUlasan">
      (26 ulasan)
    </p>

    <p>
      <strong>Toko Roti • Rp 25.000 – 50.000</strong>
    </p>

    <p id="JamBuka">
      Buka setiap hari • Tutup pukul 22.00 WIB
    </p>

    <div class="card fade" id="Testimoni">

      <h3>Apa kata pelanggan kami?</h3>

      <div class="testimonial fade">
        "Kuenya mantap banget, harganya murah meriah, dan matcha-nya enak!"
      </div>

      <div class="testimonial fade">
        "Tempatnya cozy banget! Kuenya enak, kopinya mantap, dan bisa langsung belanja baju di atas. Recomended!"
      </div>

      <div class="testimonial fade">
        "Dessertnya selalu fresh. Boutique-nya juga aesthetic, cocok buat cewek-cewek yang suka foto-foto."
      </div>

      <div class="testimonial fade">
        "Satu-satunya tempat di Sintang yang bisa makan enak sambil belanja fashion. Pelayanannya ramah!"
      </div>

      <p id="Ref">
        — Dari Google Review
      </p>

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
