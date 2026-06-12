<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Promo Berhasil - YOLAZCAKE Sintang</title>
  <link rel="stylesheet" href="css/style.css">

  <style>

    .promo-success{
      min-height:100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:120px 20px 60px;
    }

    .promo-box{
      max-width:700px;
      width:100%;
      text-align:center;
      padding:50px 35px;
      border-radius:30px;
      background:rgba(255,255,255,0.9);
      backdrop-filter:blur(12px);
      box-shadow:0 10px 30px rgba(0,0,0,0.1);
    }

    body.dark .promo-box{
      background:rgba(30,30,30,0.9);
    }

    .promo-icon{
      font-size:80px;
      margin-bottom:20px;
      animation:pop 1s ease;
    }

    @keyframes pop{
      0%{
        transform:scale(0.5);
        opacity:0;
      }

      100%{
        transform:scale(1);
        opacity:1;
      }
    }

    .promo-box h1{
      font-size:42px;
      margin-bottom:15px;
    }

    .promo-box p{
      font-size:18px;
      line-height:1.7;
      margin-bottom:18px;
    }

    .promo-code{
      margin:30px auto;
      padding:18px;
      border-radius:18px;
      font-size:28px;
      font-weight:bold;
      letter-spacing:3px;
      width:fit-content;
      background:#ffb6c1;
      color:#000;
    }

    .promo-btns{
      display:flex;
      gap:15px;
      justify-content:center;
      flex-wrap:wrap;
      margin-top:25px;
    }

    .promo-btns button{
      padding:14px 28px;
      border:none;
      border-radius:14px;
      cursor:pointer;
      font-size:16px;
      transition:0.3s;
    }

    .promo-btns button:hover{
      transform:translateY(-3px);
    }

  </style>

</head>
<body>

<!-- NAVBAR -->
<nav>

  <div class="nav-left">
    <img src="https://lh3.googleusercontent.com/gps-cs/ACgwaOvrk66Mw6TuaNg3tG6p8G9hJq_wOTUoBmpbb3qtX0t9CN0D6K8ns6HxQUsk_xRrGiRBD__9n78mwhr3RZ7cwM3UINa2Jjzvzx2U1l8S2SP93wZa3ga4xfn1BY446aaj_CJ_6ACQYiN58RQ=w203-h304-k-no">
    <h2>YOLAZCAKE</h2>
  </div>

  <ul class="main-nav">
    <li onclick="window.location.href='index.php'">Home</li>
    <li onclick="window.location.href='produk/menu.php'">Menu</li>
    <li onclick="window.location.href='gallery.php'">Gallery</li>
    <li onclick="window.location.href='about.php'">About</li>
    <li onclick="window.location.href='contact.php'">Contact</li>
  </ul>

  <div class="nav-right">

 <?php if(isset($_SESSION['username'])){ ?>

<div class="account-dropdown">

<button class="account-btn">
👤 <?php echo htmlspecialchars($_SESSION['username']); ?> ▼
</button>

<div class="account-menu">

<a href="member/member.php">
Member Area
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

<!-- PROMO SUCCESS -->
<section class="promo-success">

  <div class="promo-box fade">

    <div class="promo-icon">
      🎉
    </div>

    <h1>Promo Berhasil Diambil!</h1>

    <p>
      Selamat! Kamu berhasil mendapatkan promo spesial dari YOLAZCAKE Sintang.
    </p>

    <p>
      Tunjukkan kode promo berikut saat melakukan pembayaran di kasir.
    </p>

    <div class="promo-code">
      YOLA25
    </div>

    <p>
      Diskon berlaku untuk pembelian minimal Rp150.000 dan hanya berlaku di akhir pekan.
    </p>

    <div class="promo-btns">

      <button onclick="window.location.href='produk/menu.php#Product'">
        Lihat Produk
      </button>

      <button onclick="window.location.href='index.php'">
        Kembali ke Home
      </button>

    </div>

  </div>

</section>

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