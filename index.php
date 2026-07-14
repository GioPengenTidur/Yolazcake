<?php
session_start();

// Belum login di sesi ini? Cek dulu apakah ada cookie "Ingat saya" yang
// masih valid. TIDAK langsung login-kan — cuma disiapkan datanya, lalu
// user diminta konfirmasi lewat popup ("Lanjutkan sebagai ... / Ganti Akun")
// supaya kalau device dipakai orang lain, tidak otomatis nyelonong masuk.
if (!isset($_SESSION['username'])) {
    require_once __DIR__ . '/config/koneksi.php';
    require_once __DIR__ . '/config/remember_me_helper.php';
    $__yzRememberedUser = remember_me_check($conn);
    if ($__yzRememberedUser) {
        $_SESSION['pending_remember_user'] = $__yzRememberedUser;
    } else {
        unset($_SESSION['pending_remember_user']);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="assets/css/lucide-icons.css">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>YOLAZCAKE Sintang - Cafe • Bakery • Boutique</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/landing.css">
<link rel="stylesheet" href="css/responsive.css">
<link rel="stylesheet" href="css/intro-animation.css">
</head>
<body>

<?php if (isset($_SESSION['pending_remember_user'])):
    $__yzRU = $_SESSION['pending_remember_user'];
    $__yzRUName = htmlspecialchars($__yzRU['username'], ENT_QUOTES);
    $__yzRUPhoto = $__yzRU['foto_profil'] ?? null;
    $__yzRUInitial = strtoupper(substr($__yzRU['username'], 0, 1));
?>
<style>
  .yz-rm-overlay {
    position: fixed; inset: 0; z-index: 99999;
    display: flex; align-items: center; justify-content: center;
    background: rgba(10,6,4,0.72);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    padding: 20px;
    animation: yzRmFadeIn 0.35s ease both;
  }
  @keyframes yzRmFadeIn { from { opacity:0; } to { opacity:1; } }
  @keyframes yzRmPopIn {
    from { opacity:0; transform: translateY(14px) scale(0.96); }
    to   { opacity:1; transform: translateY(0) scale(1); }
  }
  .yz-rm-card {
    width: 100%; max-width: 380px;
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.14);
    border-radius: 24px;
    padding: 36px 30px 28px;
    text-align: center;
    box-shadow: 0 30px 90px rgba(0,0,0,0.5), 0 0 50px rgba(212,175,55,0.14);
    animation: yzRmPopIn 0.4s cubic-bezier(.2,.8,.2,1) both;
  }
  .yz-rm-avatar {
    width: 78px; height: 78px; margin: 0 auto 18px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Playfair Display', serif; font-size: 1.9em; font-weight: 700;
    color: #1e0e0a;
    background: linear-gradient(135deg, #D4AF37, #b8922a);
    box-shadow: 0 0 0 3px rgba(212,175,55,0.22), 0 10px 30px rgba(212,175,55,0.25);
    overflow: hidden;
  }
  .yz-rm-avatar img { width:100%; height:100%; object-fit:cover; }
  .yz-rm-eyebrow {
    font-size: 0.7em; letter-spacing: 2px; text-transform: uppercase;
    color: rgba(255,255,255,0.42); margin-bottom: 8px;
  }
  .yz-rm-title {
    font-family: 'Playfair Display', serif; font-size: 1.35em; font-weight: 600;
    color: #fff; margin-bottom: 6px;
  }
  .yz-rm-title span { color: #D4AF37; }
  .yz-rm-sub {
    font-size: 0.86em; color: rgba(255,255,255,0.55);
    line-height: 1.6; margin-bottom: 26px;
  }
  .yz-rm-btn {
    width: 100%; display: flex; align-items: center; justify-content: center; gap: 9px;
    padding: 13px 20px; border-radius: 14px;
    text-decoration: none; font-family: 'Inter', sans-serif; font-weight: 600; font-size: 0.92em;
    cursor: pointer; border: none; transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
  }
  .yz-rm-btn svg { width: 17px; height: 17px; flex-shrink: 0; }
  .yz-rm-btn-primary {
    background: linear-gradient(135deg, #D4AF37, #b8922a);
    color: #1e0e0a;
    box-shadow: 0 10px 26px rgba(212,175,55,0.28);
    margin-bottom: 10px;
  }
  .yz-rm-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 14px 32px rgba(212,175,55,0.38); }
  .yz-rm-btn-secondary {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.16);
    color: rgba(255,255,255,0.75);
  }
  .yz-rm-btn-secondary:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.28); }
</style>
<div class="yz-rm-overlay" role="dialog" aria-modal="true" aria-labelledby="yzRmTitle">
  <div class="yz-rm-card">
    <div class="yz-rm-avatar">
      <?php if ($__yzRUPhoto): ?>
        <img src="<?= htmlspecialchars($__yzRUPhoto, ENT_QUOTES) ?>" alt="<?= $__yzRUName ?>">
      <?php else: ?>
        <?= $__yzRUInitial ?>
      <?php endif; ?>
    </div>
    <div class="yz-rm-eyebrow">Selamat Datang Kembali</div>
    <div class="yz-rm-title" id="yzRmTitle">Masuk sebagai <span><?= $__yzRUName ?></span>?</div>
    <p class="yz-rm-sub">Perangkat ini punya sesi tersimpan dari login sebelumnya. Lanjutkan, atau ganti ke akun lain.</p>
    <a href="auth/confirm_remember.php" class="yz-rm-btn yz-rm-btn-primary">
      <i data-lucide="log-in"></i> Lanjutkan sebagai <?= $__yzRUName ?>
    </a>
    <a href="auth/switch_account.php" class="yz-rm-btn yz-rm-btn-secondary">
      <i data-lucide="repeat"></i> Ganti Akun
    </a>
  </div>
</div>
<?php endif; ?>

<?php
// ==================== ENTRY ANIMATION ====================
// Overlay ini hanya boleh tampil jika user SUDAH LOGIN dan memulai
// sesi baru di tab/browser (bukan sekadar pindah halaman, refresh,
// atau pindah tab lalu kembali). Logikanya dijalankan lewat
// sessionStorage di script inline tepat di bawah ini:
//  - Tamu (belum login)          -> overlay langsung disembunyikan, tanpa animasi.
//  - Login, sesi tab baru        -> overlay tampil dengan animasi premium.
//  - Login, masih di tab yg sama (pindah halaman/refresh/ganti tab lalu balik)
//                                 -> overlay disembunyikan langsung (flag sudah tercatat).
//  - Logout                      -> overlay disembunyikan, dan flag direset supaya
//                                    saat login lagi animasi muncul kembali.
$__yzLoggedIn = isset($_SESSION['username']) ? 'true' : 'false';
$__yzUsername = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username'], ENT_QUOTES) : '';
?>
<div id="yzIntro" class="yz-intro" aria-hidden="true">
  <div class="yz-intro-panel yz-intro-panel-left"></div>
  <div class="yz-intro-panel yz-intro-panel-right"></div>
  <div class="yz-intro-particles">
    <span></span><span></span><span></span><span></span>
    <span></span><span></span><span></span><span></span>
  </div>
  <div class="yz-intro-content">
    <div class="yz-intro-ring">
      <svg class="yz-intro-ring-svg" viewBox="0 0 140 140">
        <circle class="yz-intro-ring-track" cx="70" cy="70" r="70"></circle>
        <circle class="yz-intro-ring-progress" cx="70" cy="70" r="70"></circle>
      </svg>
      <img src="assets/img/Yolazcake.png" alt="YOLAZCAKE" class="yz-intro-logo">
    </div>
    <h1 class="yz-intro-title">
      <span>Y</span><span>O</span><span>L</span><span>A</span><span>Z</span><span>C</span><span>A</span><span>K</span><span>E</span>
    </h1>
    <p class="yz-intro-tagline">Cafe &bull; Bakery &bull; Boutique</p>
    <div class="yz-intro-line"></div>
    <p class="yz-intro-welcome">Selamat datang kembali<?php echo $__yzUsername ? ', ' . $__yzUsername : ''; ?></p>
  </div>
</div>
<script>
(function () {
  var isLoggedIn = <?php echo $__yzLoggedIn; ?>;
  var KEY = 'yz_intro_played_v1';
  var overlay = document.getElementById('yzIntro');
  var root = document.documentElement;

  function safeGet(k) { try { return sessionStorage.getItem(k); } catch (e) { return null; } }
  function safeSet(k, v) { try { sessionStorage.setItem(k, v); } catch (e) {} }
  function safeRemove(k) { try { sessionStorage.removeItem(k); } catch (e) {} }

  if (!isLoggedIn) {
    // Tamu: jangan pernah animasikan, dan reset flag supaya begitu
    // user login nanti (di tab yg sama), animasi tetap muncul segar.
    safeRemove(KEY);
    overlay.className = 'yz-intro yz-intro-skip';
    return;
  }

  if (safeGet(KEY) === '1') {
    // Sudah login & sudah pernah diputar di sesi tab ini
    // (pindah halaman / refresh / ganti tab lalu balik) -> jangan ulangi.
    overlay.className = 'yz-intro yz-intro-skip';
    return;
  }

  // Sesi tab baru + sudah login -> putar animasi masuk premium.
  root.classList.add('yz-lock');

  var HOLD_MS = 2400;   // lama animasi konten ditampilkan
  var EXIT_MS = 1000;   // lama transisi tirai terbuka

  setTimeout(function () {
    overlay.classList.add('yz-intro-exit');
  }, HOLD_MS);

  setTimeout(function () {
    overlay.classList.add('yz-intro-hidden');
    root.classList.remove('yz-lock');
    safeSet(KEY, '1');
  }, HOLD_MS + EXIT_MS);
})();
</script>

<nav id="yzNav">
  <!-- LEFT: LOGO + NAMA -->
  <div class="nav-left">
    <img src="assets/img/Yolazcake.png" alt="YOLAZCAKE Logo">
    <h2>YOLAZCAKE</h2>
  </div>

  <!-- CENTER MENU -->
<ul class="main-nav">
  <li class="active" onclick="window.location.href='index.php'">Home</li>
  <li onclick="window.location.href='produk/menu.php'">Menu</li>
  <li onclick="window.location.href='gallery.php'">Gallery</li>
  <li onclick="window.location.href='about.php'">About</li>
  <li onclick="window.location.href='contact.php'">Contact</li>
</ul>

  <!-- RIGHT -->
  <div class="nav-right">

<?php if(isset($_SESSION['username'])){ ?>

<div class="account-dropdown">

<button class="account-btn">
<i data-lucide="user" class="lucide-ic"></i> <?php echo htmlspecialchars($_SESSION['username']); ?> ▼
</button>

<div class="account-menu">

<a href="<?php echo (isset($_SESSION['role']) && $_SESSION['role']==='admin') ? 'dashboard_awal.php' : 'member/member.php'; ?>">
<?php echo (isset($_SESSION['role']) && $_SESSION['role']==='admin') ? 'Dashboard' : 'Member'; ?>
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

<div class="hamburger" onclick="toggleMenu()" id="hamburger"><i data-lucide="menu" class="lucide-ic"></i></div>
<div class="dark-btn" onclick="toggleDark()"><i data-lucide="moon" class="lucide-ic"></i></div>

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

  <p onclick="window.location.href='ulasan/tempat.php'">
    <i data-lucide="star" class="lucide-ic lucide-fill"></i> Rating Tempat & Makanan
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

<!-- ==================== HERO SECTION ==================== -->
<section class="hero-section" id="home">
  <!-- Cinematic Gradient Overlay -->
  <div class="hero-overlay">
    <div class="hero-gradient-1"></div>
    <div class="hero-gradient-2"></div>
    <div class="hero-gradient-3"></div>
  </div>

  <!-- Animated Particles -->
  <div class="particles">
    <div class="particle" style="left: 20%; top: 30%;"></div>
    <div class="particle" style="left: 35%; top: 40%;"></div>
    <div class="particle" style="left: 50%; top: 50%;"></div>
    <div class="particle" style="left: 65%; top: 60%;"></div>
    <div class="particle" style="left: 80%; top: 70%;"></div>
  </div>

  <!-- Floating Orbs -->
  <div class="floating-orb orb-1"></div>
  <div class="floating-orb orb-2"></div>

  <div class="hero-container">
    <div class="hero-content-wrapper">
      <!-- Content -->
      <div class="hero-content fade-up">
        <div class="hero-badge">
          <span class="sparkle-icon"><i data-lucide="sparkle" class="lucide-ic"></i></span>
          <span>SINTANG'S PREMIER DESTINATION</span>
        </div>

        <h1 class="hero-title">
          <span class="title-line">Where Every</span>
          <span class="title-line">Moment Becomes</span>
          <span class="title-line title-gradient">Extraordinary</span>
          <span class="title-underline"></span>
        </h1>

        <p class="hero-description">
          Experience the perfect blend of artisan bakery, specialty coffee,
          exquisite desserts, and curated women's fashion at Sintang's
          premier lifestyle destination.
        </p>

        <div class="hero-buttons">
          <a href="produk/menu.php" class="btn-primary">
            <span>Explore Menu</span>
            <svg class="arrow-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
          </a>
          <a href="#gallery-section" class="btn-secondary">
            <svg class="play-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <polygon points="5 3 19 12 5 21 5 3"/>
            </svg>
            <span>Watch Story</span>
          </a>
        </div>

        <!-- Trust Badges -->
        <div class="trust-badges">
          <div class="trust-badge fade-up" style="animation-delay: 1s;">
            <p class="badge-value">4.9</p>
            <p class="badge-label">Rating</p>
          </div>
          <div class="trust-badge fade-up" style="animation-delay: 1.1s;">
            <p class="badge-value">10K+</p>
            <p class="badge-label">Happy Guests</p>
          </div>
          <div class="trust-badge fade-up" style="animation-delay: 1.2s;">
            <p class="badge-value">2</p>
            <p class="badge-label">Years of Excellence</p>
          </div>
        </div>
      </div>

      <!-- Hero Image Collage -->
      <div class="hero-images fade-in-right">
        <div class="hero-image-container">
          <!-- Main Hero Image -->
          <div class="hero-image-main">
            <img src="https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=600&h=750&fit=crop" 
                 alt="Specialty coffee experience at YOLAZCAKE">
            <div class="image-overlay">
              <div class="glass-card">
                <p class="glass-title">Artisan Excellence</p>
                <p class="glass-subtitle">Crafted with passion since 2024</p>
              </div>
            </div>
          </div>

          <!-- Secondary Image -->
          <div class="hero-image-secondary">
            <img src="https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=400&h=500&fit=crop" 
                 alt="Artisan pastries and cakes">
          </div>

          <!-- Tertiary Image -->
          <div class="hero-image-tertiary">
            <img src="https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=200&h=200&fit=crop" 
                 alt="Premium latte art">
          </div>

          <!-- Floating Badge -->
          <div class="floating-badge">
            <span class="badge-est">Est.</span>
            <span class="badge-year">2024</span>
          </div>

          <!-- Decorative Frame -->
          <div class="decorative-frame"></div>

          <!-- Accent Line -->
          <div class="accent-line"></div>
        </div>
      </div>
    </div>
  </div>

</section>

<!-- ==================== STATISTICS SECTION ==================== -->
<section class="statistics-section">
  <!-- Sparkle dots like menu.php -->
  <span class="stat-sparkle" style="width:6px;height:6px;left:10%;top:20%;background:rgba(212,175,55,0.6);position:absolute;border-radius:50%;animation:floatDot 7s linear infinite;"></span>
  <span class="stat-sparkle" style="width:4px;height:4px;left:25%;top:60%;background:rgba(232,160,191,0.5);position:absolute;border-radius:50%;animation:floatDot 5s linear infinite 1s;"></span>
  <span class="stat-sparkle" style="width:5px;height:5px;left:60%;top:30%;background:rgba(212,175,55,0.5);position:absolute;border-radius:50%;animation:floatDot 8s linear infinite 2s;"></span>
  <span class="stat-sparkle" style="width:3px;height:3px;left:80%;top:70%;background:rgba(255,228,181,0.6);position:absolute;border-radius:50%;animation:floatDot 6s linear infinite 0.5s;"></span>
  <div class="stats-bg-pattern"></div>
  <!-- Floating ambient orbs, same signature purple treatment as about.php & status.php -->
  <div class="stats-orb stats-orb-1"></div>
  <div class="stats-orb stats-orb-2"></div>
  <div class="stats-orb stats-orb-3"></div>
  <div class="stats-container">
    <div class="stats-grid">
      <div class="stat-item fade-up">
        <div class="stat-icon">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 8h1a4 4 0 1 1 0 8h-1"/>
            <path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/>
            <line x1="6" x2="6" y1="2" y2="4"/>
            <line x1="10" x2="10" y1="2" y2="4"/>
            <line x1="14" x2="14" y1="2" y2="4"/>
          </svg>
        </div>
        <div class="stat-value" data-target="15000">0</div>
        <span class="stat-suffix">+</span>
        <p class="stat-label">Cups Served Monthly</p>
      </div>

      <div class="stat-item fade-up" style="animation-delay: 0.1s;">
        <div class="stat-icon">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M8.21 13.89 7 23l5-3 5 3-1.21-9.11"/>
            <path d="M12 2a3 3 0 0 0-3 3v1h6V5a3 3 0 0 0-3-3Z"/>
            <path d="M9 6h6l1 7H8l1-7z"/>
          </svg>
        </div>
        <div class="stat-value" data-target="50">0</div>
        <span class="stat-suffix">+</span>
        <p class="stat-label">Signature Recipes</p>
      </div>

      <div class="stat-item fade-up" style="animation-delay: 0.2s;">
        <div class="stat-icon">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
        </div>
        <div class="stat-value" data-target="25000">0</div>
        <span class="stat-suffix">+</span>
        <p class="stat-label">Happy Customers</p>
      </div>

      <div class="stat-item fade-up" style="animation-delay: 0.3s;">
        <div class="stat-icon">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="8" r="6"/>
            <path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>
          </svg>
        </div>
        <div class="stat-value" data-target="8">0</div>
        <span class="stat-suffix"></span>
        <p class="stat-label">Years of Excellence</p>
      </div>
    </div>
  </div>
</section>

<!-- ==================== BEST SELLERS SECTION ==================== -->
<section class="best-sellers-section" id="menu-section">
  <!-- Floating particles like menu.php #Product -->
  <div id="bestSellersParticles" style="position:absolute;inset:0;pointer-events:none;z-index:1;overflow:hidden;"></div>
  <div class="section-container">
    <div class="section-header fade-up">
      <span class="section-badge">Our Collection</span>
      <h2 class="section-title">Best Sellers</h2>
      <p class="section-description">
        Discover our most loved creations, handcrafted with premium ingredients 
        and perfected through years of artisan expertise.
      </p>
    </div>

    <div class="products-grid">
      <div class="product-card fade-up">
        <div class="product-image">
          <img src="assets/img/doksli/Trio_Cake.png" 
               alt="Trio Cake">
          <span class="product-badge">Best Seller</span>
        </div>
        <div class="product-info">
          <div class="product-rating">
            <svg class="star-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
            <span>4.9</span>
          </div>
          <p class="product-category">Cake</p>
          <h3 class="product-name">Trio Cake</h3>
          <p class="product-price">Rp 28.000 - Rp 30.000</p>
        </div>
      </div>

      <div class="product-card fade-up" style="animation-delay: 0.1s;">
        <div class="product-image">
          <img src="assets/img/doksli/Cheesecake_Ubi_Ungu.png" 
               alt="Cheesecake">
          <span class="product-badge badge-popular">Popular</span>
        </div>
        <div class="product-info">
          <div class="product-rating">
            <svg class="star-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
            <span>4.8</span>
          </div>
          <p class="product-category">Dessert</p>
          <h3 class="product-name">Cheesecake Ubi Ungu</h3>
          <p class="product-price">Rp 30.000</p>
        </div>
      </div>

      <div class="product-card fade-up" style="animation-delay: 0.2s;">
        <div class="product-image">
          <img src="assets/img/doksli/Bolu_Gulung_Ketan_Hitam.png" 
               alt="Bolu Gulung Ketan Hitam">
          <span class="product-badge badge-chef">Chef's Choice</span>
        </div>
        <div class="product-info">
          <div class="product-rating">
            <svg class="star-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
            <span>4.9</span>
          </div>
          <p class="product-category">Cake</p>
          <h3 class="product-name">Bolu Gulung Ketan Hitam</h3>
          <p class="product-price">Rp 35.000</p>
        </div>
      </div>

      <div class="product-card fade-up" style="animation-delay: 0.3s;">
        <div class="product-image">
          <img src="assets/img/doksli/Doughnut.jpeg" 
               alt="Chocolate Truffle">
          <span class="product-badge badge-premium">Premium</span>
        </div>
        <div class="product-info">
          <div class="product-rating">
            <svg class="star-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
            <span>5.0</span>
          </div>
          <p class="product-category">Pastry</p>
          <h3 class="product-name">Donat</h3>
          <p class="product-price">Rp 40.000 / Box</p>
        </div>
      </div>
    </div>

    <div class="section-cta fade-up">
      <a href="produk/menu.php" class="btn-outline">View Full Menu</a>
    </div>
  </div>
</section>

<!-- ==================== COFFEE & BAKERY SECTION ==================== -->
<section class="coffee-bakery-section" id="coffee-section">
  <div class="cb-bg-pattern"></div>
  <div class="section-container">
    <div class="cb-grid">
      <!-- Coffee Section -->
      <div class="cb-column fade-in-left">
        <div class="cb-header">
          <div class="cb-icon coffee-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17 8h1a4 4 0 1 1 0 8h-1"/>
              <path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/>
              <line x1="6" x2="6" y1="2" y2="4"/>
              <line x1="10" x2="10" y1="2" y2="4"/>
              <line x1="14" x2="14" y1="2" y2="4"/>
            </svg>
          </div>
          <div>
            <span class="cb-label">Specialty</span>
            <h3 class="cb-title">Coffee</h3>
          </div>
        </div>
        
        <p class="cb-description">
          Single-origin beans sourced from the finest estates, roasted to perfection 
          by our in-house roasters.
        </p>

        <div class="cb-items">
          <div class="cb-item">
            <div class="cb-item-image">
              <img src="assets/img/doksli/Buttersotch_Sea_Salt_Latte.png" 
                   alt="Buttersotch Sea Salt Latte">
            </div>
            <div class="cb-item-info">
              <div class="cb-item-header">
                <h4 class="cb-item-name">Buttersotch Sea Salt Latte</h4>
                <span class="cb-item-price">Rp 28.000</span>
              </div>
              <p class="cb-item-desc">Creamy butterscotch balanced with a delicate touch of sea salt</p>
            </div>
          </div>

          <div class="cb-item">
            <div class="cb-item-image">
              <img src="assets/img/doksli/Brown_Sugar_Coffee_Latte.jpeg" 
                   alt="Iced Brown Sugar Coffee Latte">
            </div>
            <div class="cb-item-info">
              <div class="cb-item-header">
                <h4 class="cb-item-name">Brown Sugar Coffee Latte</h4>
                <span class="cb-item-price">Rp 28.000</span>
              </div>
              <p class="cb-item-desc">Bold espresso balanced with sweet brown sugar and fresh milk</p>
            </div>
          </div>

          <div class="cb-item">
            <div class="cb-item-image">
              <img src="assets/img/doksli/Cappuccino.png" 
                   alt="Cappuccino">
            </div>
            <div class="cb-item-info">
              <div class="cb-item-header">
                <h4 class="cb-item-name">Cappuccino</h4>
                <span class="cb-item-price">Rp 28.000</span>
              </div>
              <p class="cb-item-desc">A perfect balance of espresso, steamed milk, and soft foam</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Bakery Section -->
      <div class="cb-column fade-in-right">
        <div class="cb-header">
          <div class="cb-icon bakery-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
            </svg>
          </div>
          <div>
            <span class="cb-label">Artisan</span>
            <h3 class="cb-title">Bakery</h3>
          </div>
        </div>
        
        <p class="cb-description">
          Fresh-baked daily using traditional European techniques and the finest 
          imported ingredients.
        </p>

        <div class="cb-items">
          <div class="cb-item">
            <div class="cb-item-image">
              <img src="assets/img/doksli/Classic_Chocolate_Doughnut.jpeg" 
                   alt="Donut Coklat Klasik">
            </div>
            <div class="cb-item-info">
              <div class="cb-item-header">
                <h4 class="cb-item-name">Donut Cokelat Klasik</h4>
                <span class="cb-item-price">Rp 8.000</span>
              </div>
              <p class="cb-item-desc">Soft, freshly baked donut combined with a generous dusting of chocolate glaze, creating a classic flavor that stands the test of time</p>
            </div>
          </div>

          <div class="cb-item">
            <div class="cb-item-image">
              <img src="assets/img/doksli/Red_Bean_Bread.png" 
                   alt="Roti Kacang Merah">
            </div>
            <div class="cb-item-info">
              <div class="cb-item-header">
                <h4 class="cb-item-name">Roti Kacang Merah</h4>
                <span class="cb-item-price">Rp 15.000</span>
              </div>
              <p class="cb-item-desc">Soft, freshly baked bread filled with sweet red bean paste, finished with a delightful texture in every bite</p>
            </div>
          </div>

          <div class="cb-item">
            <div class="cb-item-image">
              <img src="assets/img/doksli/Cheese_Milk_Bread.png" 
                   alt="Roti Keju Susu">
            </div>
            <div class="cb-item-info">
              <div class="cb-item-header">
                <h4 class="cb-item-name">Roti Keju Susu</h4>
                <span class="cb-item-price">Rp 15.000</span>
              </div>
              <p class="cb-item-desc">Soft, freshly baked bread combined with cheese and premium milk, creating a savory, sweet, and delicious taste</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Featured Image -->
    <div class="cb-featured fade-up">
      <div class="cb-featured-image">
        <img src="https://images.unsplash.com/photo-1445116572660-236099ec97a0?w=1200&h=500&fit=crop" 
             alt="YOLAZCAKE cafe interior">
        <div class="cb-featured-overlay">
          <h4>Experience the Art of Coffee & Baking</h4>
          <p>Where every cup tells a story and every bite is a masterpiece</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ==================== BOUTIQUE SECTION ==================== -->
<section class="boutique-section" id="boutique-section">
  <div class="section-container">
    <div class="boutique-grid">
      <!-- Content -->
      <div class="boutique-content fade-in-left">
        <div class="boutique-badge">
          <span class="sparkle-icon"><i data-lucide="sparkle" class="lucide-ic"></i></span>
          <span>Fashion Boutique</span>
        </div>
        
        <h2 class="boutique-title">
          <span>Curated Style for the</span>
          <span class="title-accent">Modern Woman</span>
        </h2>
        
        <p class="boutique-description">
          Discover our carefully selected collection of women's fashion, 
          featuring timeless pieces and contemporary designs from both local 
          artisans and international brands.
        </p>

        <div class="boutique-features">
          <div class="feature-item">
            <div class="feature-dot"></div>
            <span>Exclusive designer collaborations</span>
          </div>
          <div class="feature-item">
            <div class="feature-dot"></div>
            <span>Sustainable fashion choices</span>
          </div>
          <div class="feature-item">
            <div class="feature-dot"></div>
            <span>Personal styling services</span>
          </div>
        </div>

        <a href="gallery.php" class="btn-primary">
          <span>Explore Collection</span>
          <svg class="arrow-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
        </a>
      </div>

      <!-- Fashion Grid -->
      <div class="boutique-images fade-in-right">
        <div class="fashion-item fashion-large">
          <img src="assets/img/doksli/Boutique_Collection.jpeg" 
               alt="Boutique Collection">
          <div class="fashion-overlay">
            <p class="fashion-category">Boutique Collection</p>
            <h4 class="fashion-name">Dresses, Jackets, Shirts, Pants, Skirts, Tops</h4>
            <p class="fashion-price">From Rp 45.000</p>
          </div>
        </div>

        <div class="fashion-item fashion-small">
          <img src="assets/img/doksli/Jacket_Crop_Casual.png" 
               alt="Jacket Crop Casual">
          <div class="fashion-overlay">
            <p class="fashion-category">Jackets</p>
            <h4 class="fashion-name">Jaket Crop Casual</h4>
            <p class="fashion-price">From Rp 60.000</p>
          </div>
        </div>

        <div class="fashion-item fashion-small">
          <img src="assets/img/doksli/Celana.jpeg" 
               alt="Celana Jeans">
          <div class="fashion-overlay">
            <p class="fashion-category">Celana</p>
            <h4 class="fashion-name">Celana Jeans</h4>
            <p class="fashion-price">From Rp 50.000</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ==================== TESTIMONIALS SECTION ==================== -->
<section class="testimonials-section">
  <!-- Sparkle dots like menu.php -->
  <span style="width:5px;height:5px;left:5%;top:25%;background:rgba(212,175,55,0.5);position:absolute;border-radius:50%;animation:floatDot 7s linear infinite;pointer-events:none;z-index:1;"></span>
  <span style="width:3px;height:3px;left:15%;top:65%;background:rgba(255,228,181,0.4);position:absolute;border-radius:50%;animation:floatDot 5s linear infinite 2s;pointer-events:none;z-index:1;"></span>
  <span style="width:6px;height:6px;left:75%;top:20%;background:rgba(212,175,55,0.4);position:absolute;border-radius:50%;animation:floatDot 9s linear infinite 1s;pointer-events:none;z-index:1;"></span>
  <span style="width:4px;height:4px;left:90%;top:55%;background:rgba(232,160,191,0.5);position:absolute;border-radius:50%;animation:floatDot 6s linear infinite 0.5s;pointer-events:none;z-index:1;"></span>
  <div class="testimonials-bg">
    <div class="testimonials-orb orb-left"></div>
    <div class="testimonials-orb orb-right"></div>
  </div>

  <div class="section-container">
    <div class="section-header fade-up">
      <span class="section-badge">Contoh Testimonials/Dummy</span>
      <h2 class="section-title">What Our Guests Say</h2>
    </div>

    <div class="testimonials-carousel">
      <div class="testimonial-card active" data-index="0">
        <svg class="quote-icon" width="48" height="48" viewBox="0 0 24 24" fill="currentColor" opacity="0.3">
          <path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V21z"/>
          <path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/>
        </svg>
        
        <div class="testimonial-stars">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>

        <p class="testimonial-text">
          "YOLAZCAKE has become my go-to spot in Sintang. The red velvet cake is absolutely divine, and the coffee rivals any specialty shop I've visited in Jakarta. A true hidden gem!"
        </p>

        <div class="testimonial-author">
          <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&h=100&fit=crop&crop=face" 
               alt="Amelia Chen">
          <div>
            <p class="author-name">Amelia Chen</p>
            <p class="author-role">Food Blogger</p>
          </div>
        </div>
      </div>

      <div class="testimonial-card" data-index="1">
        <svg class="quote-icon" width="48" height="48" viewBox="0 0 24 24" fill="currentColor" opacity="0.3">
          <path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V21z"/>
          <path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/>
        </svg>
        
        <div class="testimonial-stars">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>

        <p class="testimonial-text">
          "Not only is the food exceptional, but the boutique section is a dream! I've found unique pieces here that I couldn't find anywhere else. The styling advice is top-notch."
        </p>

        <div class="testimonial-author">
          <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&h=100&fit=crop&crop=face" 
               alt="Sarah Wijaya">
          <div>
            <p class="author-name">Sarah Wijaya</p>
            <p class="author-role">Fashion Enthusiast</p>
          </div>
        </div>
      </div>

      <div class="testimonial-card" data-index="2">
        <svg class="quote-icon" width="48" height="48" viewBox="0 0 24 24" fill="currentColor" opacity="0.3">
          <path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V21z"/>
          <path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/>
        </svg>
        
        <div class="testimonial-stars">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>

        <p class="testimonial-text">
          "I've ordered custom cakes for multiple events, and YOLAZCAKE never disappoints. Their attention to detail and flavor combinations are truly world-class. Highly recommended!"
        </p>

        <div class="testimonial-author">
          <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&h=100&fit=crop&crop=face" 
               alt="Diana Putri">
          <div>
            <p class="author-name">Diana Putri</p>
            <p class="author-role">Event Planner</p>
          </div>
        </div>
      </div>

      <div class="testimonial-card" data-index="3">
        <svg class="quote-icon" width="48" height="48" viewBox="0 0 24 24" fill="currentColor" opacity="0.3">
          <path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V21z"/>
          <path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/>
        </svg>
        
        <div class="testimonial-stars">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>

        <p class="testimonial-text">
          "The ambiance here is Instagram-perfect, but it's the quality that keeps me coming back. Every pastry is a work of art, and the cold brew is simply addictive."
        </p>

        <div class="testimonial-author">
          <img src="https://images.unsplash.com/photo-1517841905240-472988babdf9?w=100&h=100&fit=crop&crop=face" 
               alt="Michelle Tan">
          <div>
            <p class="author-name">Michelle Tan</p>
            <p class="author-role">Interior Designer</p>
          </div>
        </div>
      </div>
    </div>

    <div class="testimonials-nav">
      <button class="nav-btn prev-btn" onclick="prevTestimonial()" aria-label="Previous testimonial">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <polyline points="15 18 9 12 15 6"/>
        </svg>
        <span>Prev</span>
      </button>

      <div class="testimonials-dots">
        <button class="dot active" data-index="0" aria-label="Go to testimonial 1"></button>
        <button class="dot" data-index="1" aria-label="Go to testimonial 2"></button>
        <button class="dot" data-index="2" aria-label="Go to testimonial 3"></button>
        <button class="dot" data-index="3" aria-label="Go to testimonial 4"></button>
      </div>

      <button class="nav-btn next-btn" onclick="nextTestimonial()" aria-label="Next testimonial">
        <span>Next</span>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <polyline points="9 18 15 12 9 6"/>
        </svg>
      </button>
    </div>
  </div>
</section>

<!-- ==================== GALLERY SECTION ==================== -->
<section class="gallery-section" id="gallery-section">
  <div class="section-container">
    <div class="section-header fade-up">
      <span class="section-badge">Gallery</span>
      <h2 class="section-title">Moments at YOLAZCAKE</h2>
      <p class="section-description">
        A visual journey through our space, creations, and the experiences we craft.
      </p>
    </div>

    <div class="gallery-grid">
      <div class="gallery-item item-large fade-up" onclick="openLightbox(0)">
        <img src="https://images.unsplash.com/photo-1554118811-1e0d58224f24?w=600&h=600&fit=crop" 
             alt="Cafe interior with warm lighting">
        <div class="gallery-overlay">
          <span>View</span>
        </div>
      </div>

      <div class="gallery-item fade-up" onclick="openLightbox(1)">
        <img src="assets/img/galeri/img_6a531079a414e9.90158121.png" 
             alt="Artisan coffee preparation">
        <div class="gallery-overlay">
          <span>View</span>
        </div>
      </div>

      <div class="gallery-item fade-up" onclick="openLightbox(2)">
        <img src="https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=400&h=400&fit=crop" 
             alt="Delicious pastries display">
        <div class="gallery-overlay">
          <span>View</span>
        </div>
      </div>

      <div class="gallery-item item-tall fade-up" onclick="openLightbox(3)">
        <img src="https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=400&h=600&fit=crop" 
             alt="Signature layered cake">
        <div class="gallery-overlay">
          <span>View</span>
        </div>
      </div>

      <div class="gallery-item fade-up" onclick="openLightbox(4)">
        <img src="https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=400&h=400&fit=crop" 
             alt="Latte art">
        <div class="gallery-overlay">
          <span>View</span>
        </div>
      </div>

      <div class="gallery-item item-wide fade-up" onclick="openLightbox(5)">
        <img src="https://images.unsplash.com/photo-1464349095431-e9a21285b5f3?w=600&h=400&fit=crop" 
             alt="Elegant dessert presentation">
        <div class="gallery-overlay">
          <span>View</span>
        </div>
      </div>

      <div class="gallery-item fade-up" onclick="openLightbox(6)">
        <img src="https://images.unsplash.com/photo-1587080413959-06b859fb107d?w=400&h=400&fit=crop" 
             alt="Fashion boutique corner">
        <div class="gallery-overlay">
          <span>View</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Lightbox -->
  <div class="lightbox" id="lightbox" onclick="closeLightbox()">
    <button class="lightbox-close" onclick="closeLightbox()" aria-label="Close lightbox">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="18" y1="6" x2="6" y2="18"/>
        <line x1="6" y1="6" x2="18" y2="18"/>
      </svg>
    </button>
    <img src="" alt="" id="lightbox-image" onclick="event.stopPropagation()">
  </div>
</section>

<!-- ==================== ABOUT PREVIEW SECTION ==================== -->
<section class="about-preview-section">
  <div class="about-preview-bg-pattern"></div>
  <div class="section-container">
    <div class="about-preview-grid">
      <!-- Content -->
      <div class="about-preview-content fade-in-left">
        <span class="section-badge">About Us</span>
        <h2 class="section-title">Cerita di Balik YOLAZCAKE</h2>
        <p class="about-preview-description">
          Dari passion seorang pemilik yang mencintai baking dan fashion, lahir YOLAZCAKE Sintang. Kami ingin menciptakan ruang di mana orang bisa menikmati rasa enak sekaligus tampil cantik.
        </p>
        
        <div class="about-preview-features">
          <div class="feature-item">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2a10 10 0 1 0 10 10H12z"/>
            </svg>
            <span>Passion-Driven Since 2024</span>
          </div>
          <div class="feature-item">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <span>Community-Focused</span>
          </div>
          <div class="feature-item">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 12l2 2 4-4"/>
              <path d="M12 2a10 10 0 1 0 10 10H12z"/>
            </svg>
            <span>Premium Quality</span>
          </div>
        </div>

        <a href="about.php" class="btn-primary about-preview-cta">
          <span>Pelajari Lebih Lanjut</span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
        </a>
      </div>

      <!-- Image -->
      <div class="about-preview-image fade-in-right">
        <img src="https://images.unsplash.com/photo-1556740738-b6a63e27c4df?w=500&h=600&fit=crop" 
             alt="Tim YOLAZCAKE Sintang">
        <div class="image-badge">
          <p class="badge-title">Passion Meets</p>
          <p class="badge-subtitle">Creativity</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ==================== FOOTER ==================== -->
<footer class="landing-footer">
  <div class="footer-top-border"></div>
  
  <div class="footer-container">
    <div class="footer-grid">
      <!-- Brand -->
      <div class="footer-brand">
        <a href="index.php" class="footer-logo">
          YOLAZ<span class="logo-accent">CAKE</span>
        </a>
        <p class="footer-description">
          A premium lifestyle destination in Sintang, combining artisan bakery, 
          specialty coffee, exquisite desserts, and curated women's fashion.
        </p>
        <div class="footer-social">
          <a href="#" class="footer-social-btn" aria-label="Instagram">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="20" height="20" x="2" y="2" rx="5" ry="5"/>
              <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
              <line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>
            </svg>
          </a>
          <a href="#" class="footer-social-btn" aria-label="Facebook">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
            </svg>
          </a>
        </div>
      </div>

      <!-- Links -->
      <div class="footer-links-group">
        <div class="footer-links">
          <h4 class="footer-links-title">Explore</h4>
          <ul>
            <li><a href="produk/menu.php">Menu</a></li>
            <li><a href="#coffee-section">Coffee</a></li>
            <li><a href="#boutique-section">Boutique</a></li>
            <li><a href="gallery.php">Gallery</a></li>
          </ul>
        </div>

        <div class="footer-links">
          <h4 class="footer-links-title">Info</h4>
          <ul>
            <li><a href="about.php">About Us</a></li>
            <li><a href="#">Careers</a></li>
            <li><a href="#">Press</a></li>
            <li><a href="contact.php">Contact</a></li>
          </ul>
        </div>

        <div class="footer-links">
          <h4 class="footer-links-title">Legal</h4>
          <ul>
            <li><a href="#">Privacy Policy</a></li>
            <li><a href="#">Terms of Service</a></li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Bottom Bar -->
    <div class="footer-bottom">
      <p class="footer-copyright">
        © <?php echo date('Y'); ?> YOLAZCAKE Sintang. All rights reserved.
      </p>
      <button class="back-to-top" onclick="scrollToTop()">
        Back to top
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="12" y1="19" x2="12" y2="5"/>
          <polyline points="5 12 12 5 19 12"/>
        </svg>
      </button>
    </div>
  </div>

  <!-- Large Brand Watermark -->
  <div class="footer-watermark">YOLAZCAKE</div>
</footer>

<script src="js/style.js"></script>
<script src="js/landing.js"></script>

<?php include 'status_fab.php'; ?>
<?php include 'rating_fab.php'; ?>
<?php include 'chatbot_fab.php'; ?>


<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide){lucide.createIcons();}</script>
</body>
</html>
