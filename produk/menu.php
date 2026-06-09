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
    <img src="https://lh3.googleusercontent.com/gps-cs/ACgwaOvrk66Mw6TuaNg3tG6p8G9hJq_wOTUoBmpbb3qtX0t9CN0D6K8ns6HxQUsk_xRrGiRBD__9n78mwhr3RZ7cwM3UINa2Jjzvzx2U1l8S2SP93wZa3ga4xfn1BY446aaj_CJ_6ACQYiN58RQ=w203-h304-k-no">
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
      style="background-image:url('https://lh3.googleusercontent.com/gps-cs-s/APNQkAFSe4AwjgtAeBeNDNTkjvX2RCppfb3NaGWXpY-6JlAiafOOR7RPHdz55qrxED-6cbOSDbJBUkSaJvh57u0ONwlBQ3WLceBcOdESS4xhkT4IUPpvAY1SYvmDQ1egYDWLDkebPFVvP9ScW50v=w203-h360-k-no')">
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
      style="background-image:url('https://lh3.googleusercontent.com/gps-cs/ACgwaOs97VK3bhHWSSJjZ_Kd54xMSn1NZlWoPK-2Im2c0FVR99bVRrzveW42i0UFxCbkq0O3JU2TT8WMLodIXTSbpcUd8mlWKn1vRCq_rTSLcWm94awWdlDvV38l7y57TdyAJVGgFmxZc22MK1Sf=w203-h270-k-no')">
      </div>

      <h3>Trio Cake</h3>
      <p>Chocolate indulgence cake, klepon cake, red velvet cake</p>
    </div>

    <div class="card fade">
      <div class="card-bg"
      style="background-image:url('https://lh3.googleusercontent.com/gps-cs/ACgwaOuw_xb_C00uZMq4DtdJ8N72egFCWZf_I2zjj3254lR9dbD1f9hvZdwoQRTJTBCJMXHg9_c9E2jgPN3iSC5iNmcI0V9CxytjMghupFWvZKW091bqicz5LtmOAjC2xSqrNcSxyB5UL4lEc9Zi=w203-h451-k-no')">
      </div>

      <h3>Signature Latte</h3>
      <p>Dibuat dengan biji kopi pilihan dan susu premium</p>
    </div>

    <div class="card fade">
      <div class="card-bg"
      style="background-image:url('https://lh3.googleusercontent.com/gps-cs/ACgwaOvtRJSm48PI33kYPz5Dh6vwwL1nw8MHAvAtHoqJq6nJ-b5lCobai9y7qAZs_5moRepj__aGqPFPZjPDJngkwq2RQR3XghBp_JcKoPZ3F9KcM5towlHZDV4oHBRXlAshgkvcHnDXH89bbEQG=w203-h360-k-no')">
      </div>

      <h3>Donat</h3>
      <p>Donat homemade dengan bahan premium</p>
    </div>

    <div class="card fade">
      <div class="card-bg"
      style="background-image:url('https://lh3.googleusercontent.com/gps-cs-s/APNQkAGTNAW5PNuDjb41NNHDB-bXNPs1EtKt7XHXoRW4iD52Wqkdl5vFbOK98l-0P1mAOSbGX1AQ3xUx2V_MWDBb_LIoeGDGh6e4gc_0DRjQV3DXtVCwO5rTsLNrrEE5UjCLiMWHGkb7quY1xwU=w203-h270-k-no')">
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
