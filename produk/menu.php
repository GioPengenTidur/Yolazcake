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
    <li onclick="window.location.href='../index.php'">Home</li>
    <li class="active" onclick="window.location.href='menu.php'">Menu</li>
    <li onclick="window.location.href='../gallery.php'">Gallery</li>
    <li onclick="window.location.href='../about.php'">About</li>
    <li onclick="window.location.href='../contact.php'">Contact</li>
  </ul>

  <!-- RIGHT -->
   <div class="nav-right">

<?php if(isset($_SESSION['username'])){ ?>

<div class="account-dropdown">

<button class="account-btn">
👤 <?php echo htmlspecialchars($_SESSION['username']); ?> ▼
</button>

<div class="account-menu">

<a href="../member/member.php">
Member
</a>

<a href="../auth/logout.php">
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

    <p onclick="window.location.href='../about.php#story'">
      Back Story
    </p>

    <p onclick="window.location.href='menu.php#Product'">
      Featured Product
    </p>

    <p onclick="window.location.href='menu.php#promo'">
      Promo
    </p>

    <p onclick="window.location.href='../gallery.php#Rating'">
      Rating
    </p>

    <p onclick="window.location.href='../gallery.php#gallery'">
      Gallery
    </p>

    <p onclick="window.location.href='../about.php#team'">
      Team
    </p>

    <p onclick="window.location.href='../contact.php#location'">
      Location
    </p>

    <p onclick="window.location.href='../contact.php#contact'">
      Contact & Sosmed
    </p>

  </div>

</nav>

<!-- MENU -->
<section id="menu">

  <h2>Highlights Menu</h2>

  <div class="grid">

    <div class="card fade">
      <div class="card-bg"
      style="background-image:url('https://lh3.googleusercontent.com/gps-cs-s/APNQkAEgqBd6NWb-UkCt8EaP0d1IZd1uvxczXtXKLqfRDBq92UVlXrN7tU3acmu48PE8dVq0U1smQuLn2flGuhSv8SBGDmMDQ4HDzPt0inOcEPrHgnuDXY7D_wn_tuiP-mcCPSzYHXrsy13Bbp1g=w203-h152-k-no')">
      </div>

      <h3>Donut</h3>
      <p>Donat homemade dengan bahan premium</p>
    </div>

    <div class="card fade">
      <div class="card-bg"
      style="background-image:url('https://lh3.googleusercontent.com/gps-cs-s/APNQkAGWRsx9I5aMBRYOOIDyWkQMcl9RgKnp_CD8Y4owK9qdPvMwPDSU6uWjs8zKKq107WLpgmVmTYOpaxMgSrhRpl-MCPZGyAYPgpltFO7hVh4tSM0DQa9uk4KWs-CsI5GZN3ftRsdhsHNp2_F6=w203-h270-k-no')">
      </div>

      <h3>Specialty Coffee</h3>
      <p>Espresso, Latte Art, Cold Brew</p>
    </div>

    <div class="card fade">
      <div class="card-bg"
      style="background-image:url('https://lh3.googleusercontent.com/gps-cs-s/APNQkAEXu3cICx0XzVk5ifOQLcq8DaRGPwYOUSx6elFyjgJDoYYLJOttOcjNLKbElDZzTk8VJ14uTV5ONvHLE2VUCa4qaZ4fjq_0xt0eBsF46IBS95dXaiLtfjGMEPpGE2UMxT8Phd1rXJ-ZZtJb=w203-h360-k-no')">
      </div>

      <h3>Dessert & Minuman</h3>
      <p>Non-coffee & Signature Drink</p>
    </div>

    <div class="card fade">
      <div class="card-bg"
      style="background-image:url('https://lh3.googleusercontent.com/gps-cs-s/APNQkAGe6TtlEB63M_38pWHypN5RgdEpc-MPweIkN612OoMq6jVFNIYvjif89o7cFH8WVJ74_nlMy-qMu-UAyYRhJSOeEUbA9e9kOuLtDKBXbjN6fRPR4QHhMmR3a7XbkkrxnoLjeHNsC6oueVuz=w203-h360-k-no')">
      </div>

      <h3>Boutique Lantai 2</h3>
      <p>Pakaian zaman modern & aesthetic</p>
    </div>

  </div>

</section>

<!-- PRODUK -->
<section id="Product">

  <h2>Produk Unggulan</h2>

  <div class="grid">

    <div class="card fade">
      <div class="card-bg"
      style="background-image:url('https://lh3.googleusercontent.com/gps-cs/ACgwaOtWF5HzYzXaM6dBjrab014V7y-PZn8h9KrsgafxgTxu2ADQ2mtyB_d7EyXo_y64YZf3Wp34CU3-w8NgtQtKWltwn4P4NPfMQnFf7oKEVdUeBHu04LS8ziBE_vH63BFpD9hbIIirijIKdBBM=w203-h270-k-no')">
      </div>

      <h3>Trio Cake</h3>
      <p>Chocolate indulgence cake, klepon cake, red velvet cake</p>
    </div>

    <div class="card fade">
      <div class="card-bg"
      style="background-image:url('https://lh3.googleusercontent.com/gps-cs/ACgwaOto90T0TUjEx8jHIUbAYKPBFPnwY7iKuE_6bZwDsrv7_mxSR6i9wFbYVmQQpOYJR5DRK0h1uc9bzV7iCRcqSKLI6cGQNBAZ5Ui4oPSKMgbGE7AsrQ3wyNwhicBRHvh_qv7PEKc-jLw0UKAz=w203-h451-k-no')">
      </div>

      <h3>Signature Latte</h3>
      <p>Dibuat dengan biji kopi pilihan dan susu premium</p>
    </div>

    <div class="card fade">
      <div class="card-bg"
      style="background-image:url('https://lh3.googleusercontent.com/gps-cs-s/APNQkAGg993faAY2Ys8iGoOjKBLZBEwmPBloN1S6YhZ58JBdKNm2EofF0FphUfkiJPPCKTSu9ZzJdSWTxLuS0mgxz_taVN6rPWwf_6K2OnfeGU46JBIhIW-Ot2cSx9ZeW8VXkr1y4MJRnTwALrM=w203-h270-k-no')">
      </div>

      <h3>Donat</h3>
      <p>Donat homemade dengan bahan premium</p>
    </div>

    <div class="card fade">
      <div class="card-bg"
      style="background-image:url('https://lh3.googleusercontent.com/gps-cs-s/APNQkAHwym8Tw4ZZuGMh1dOl8C38jt2mfcaFW9B7vsFI35LrmzLTCCLZd1dMyfH3d8cjcBExEyuq1LsjJOHpncww-G9rjNxhUvnB79iQwQEoRv-h4KtE6fKbJvn0f--0BLP7qUWS-V1RBXkTNDk=w203-h270-k-no')">
      </div>

      <h3>Boutique Collection</h3>
      <p>Perpaduan style, elegance, dan confidence</p>
    </div>

  </div>

</section>

<!-- PROMO -->
<section id="promo">

  <h2>Promo Spesial</h2>

  <div class="card fade" id="PromoCard">

    <h3 id="HeaderDiskon">
      Diskon 25% untuk Setiap Pembelian di Atas Rp150.000
    </h3>

    <p>
      Berlaku untuk cake, dessert, dan minuman pilihan. Hanya di akhir pekan!
    </p>

    <button id="PromoBtn"
onclick="window.location.href='../promo.php'">
  Ambil Promo
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

</body>
</html>