<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About - YOLAZCAKE Sintang</title>
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
    <li class="active" onclick="window.location.href='about.php'">About</li>
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

<!-- ABOUT -->
<section id="about">

  <h2>Tentang YOLAZCAKE Sintang</h2>

  <div id="Deskripsi">
    <p>
      YOLAZCAKE Sintang adalah destinasi unik yang menggabungkan pengalaman kuliner premium dengan fashion modern. Terletak di Jl. Lintas Melawi, Ladang, Sintang, kami menyajikan kue homemade, dessert lezat, kopi spesial, dan minuman segar di lantai 1, serta boutique pakaian wanita yang stylish di lantai 2.
    </p>

    <p>
      Kami percaya bahwa setiap kunjungan harus menjadi pengalaman yang menyenangkan — tempat bersantai, bekerja, atau berbelanja dengan nyaman.
    </p>
  </div>

</section>

<!-- STORY -->
<section id="story">

  <h2>Cerita di Balik YOLAZCAKE</h2>

  <div id="Motif">
    <p>
      Dari passion seorang pemilik yang mencintai baking dan fashion, lahir YOLAZCAKE Sintang. Kami ingin menciptakan ruang di mana orang bisa menikmati rasa enak sekaligus tampil cantik. Setiap kue dibuat dengan cinta, setiap kopi diseduh dengan hati, dan setiap pakaian dipilih dengan selera tinggi.
    </p>
  </div>

</section>

<!-- TEAM -->
<section id="team">

  <h2>Tim YOLAZCAKE</h2>

  <div class="grid">

    <div class="card fade">
      <div class="card-bg"
      style="background-image:url('https://lh3.googleusercontent.com/gps-cs/ACgwaOvxKT5lSYSqtGXdOEcolvesgLmUOI5LslddKbpvMrujZolC12At5NaoivmDdUFn5UvXuXT9qDCqkXvjfNuLkqCYc4YMPUurAss2BrIJv1UwIFeNyJE0aix_tHo4cd6T3AsDiWIO1jSvMxPl=w203-h252-k-no')">
      </div>

      <h3>Chef Baker</h3>
      <p>Spesialis kue homemade premium</p>
    </div>

    <div class="card fade">
      <div class="card-bg"
      style="background-image:url('https://lh3.googleusercontent.com/gps-cs/ACgwaOvhYwysSMlaYth1DKEgkhviQ6lh1kKQitri0i1SOWzsyvlH6WOnQ0hcpnMdyFNbyzMfr6iJKwUl36Xuks9zee3KAVK4pucZQZQSmAu7jFE5icLeGF0ZV1L5fhGUdP6KlvpfapNH7KSlbF0l=w203-h451-k-no')">
      </div>

      <h3>Barista</h3>
      <p>Ahli latte art & specialty coffee</p>
    </div>

    <div class="card fade">
      <div class="card-bg"
      style="background-image:url('https://lh3.googleusercontent.com/gps-cs/ACgwaOv7c7BKrobg4hOcBrLVmOkm-mvRw18q1pB2e-xkGSQ6F34AhGeIH9tGr942BsT4llGFdzjtEC2-W6fJvaLb6cuSnQfOhvAmPfF4HYvCHaB-gAOSbdSc74Jo3WyAtaD0Yp04P8U-K0FxbCs=w203-h444-k-no')">
      </div>

      <h3>Fashion Stylist</h3>
      <p>Curator koleksi boutique</p>
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
