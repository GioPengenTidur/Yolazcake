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
    <img src="https://lh3.googleusercontent.com/gps-cs/ACgwaOvoAgx_np916BKRaCfH4lmlCs_x0RKdpCtIdsUB9mdvsZ9qegbRKhaZ3_HDH6aDDL5pElMCtKGM45rf2T-ZQIhJxwOVGP0csrs6fHnan7HiyP_C_2EvfzczGolnSFKn8hCEAfdKgRMCyY4M=w203-h304-k-no">
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
      style="background-image:url('https://lh3.googleusercontent.com/gps-cs-s/APNQkAE2DHqZ4jOiItnuiNlWRnDZo78Q1s3k3Iq1tM36PGEsoLPrMJ4-YWYIKOzYP0c_Ch-TkChvjYYbGhRQEiff-RotWcl5T_BWN00uqUiK0mLgNHkuUA02sHkV_eEq61Fgwwe0GwQ3w15AV6AP=w203-h252-k-no')">
      </div>

      <h3>Chef Baker</h3>
      <p>Spesialis kue homemade premium</p>
    </div>

    <div class="card fade">
      <div class="card-bg"
      style="background-image:url('https://lh3.googleusercontent.com/gps-cs/ACgwaOtSRZzBy614AF-a_J8QRPPPNYbR8fV4jm7J2XvpVAfxuW7Iw-2fhere7B-90d_e8h4UogyIO4WCLX8uLVErv7NQWMMSTD1u96gtIF_CWuhwiYZYjBCREpmEqSdbCTkw32DJmO4f-zKCWk70=w203-h451-k-no')">
      </div>

      <h3>Barista</h3>
      <p>Ahli latte art & specialty coffee</p>
    </div>

    <div class="card fade">
      <div class="card-bg"
      style="background-image:url('https://lh3.googleusercontent.com/gps-cs/ACgwaOtdwA5HQnCg9VmCAzMoeaFZ-oCWCBSNPggxLP0QzKjbE4pVg2R-uk3Ma2mTmeZ0gBtx2M8ejZbaTkuBuBpq5t77GEyhnvxJNI0WjVw_fWAOcTAX8rYcustABOYken3ImlDlMeHgopjYbWI=w203-h444-k-no')">
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
