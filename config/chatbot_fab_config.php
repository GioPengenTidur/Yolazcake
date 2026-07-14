<?php
/* ============================================================
   KONFIGURASI IKON CHATBOT FLOATING BUTTON (Yola AI)

   Ubah nilai CHATBOT_FAB_ICON_MODE buat ganti tampilan ikon
   tombol chatbot mengambang tanpa perlu utak-atik chatbot_fab.php.

   Pilihan mode:
     'default' => ikon sparkle gradient ala Gemini (bawaan, tidak
                  perlu file gambar apa-apa)
     'gambar'  => pakai logo/gambar diam (PNG/JPG) sebagai ikon.
                  Isi path di CHATBOT_FAB_IMAGE
     'video'   => pakai video (mp4) sebagai ikon, diputar loop
                  otomatis tanpa suara & tanpa efek berdenyut.
                  Isi path di CHATBOT_FAB_VIDEO

   Catatan zoom ikon video:
   Besar/kecil "zoom" video di dalam lingkaran ikon diatur lewat
   variabel CHATBOT_FAB_VIDEO_ZOOM di bawah (1 = pas/normal,
   makin besar angkanya makin di-zoom in/dekat). Tinggal ubah
   angkanya, tidak perlu edit file lain.
   ============================================================ */

define('CHATBOT_FAB_ICON_MODE', 'video'); // 'default', 'gambar', atau 'video'
define('CHATBOT_FAB_IMAGE', 'assets/img/logo/yola-ai-icon.png');
define('CHATBOT_FAB_VIDEO', 'assets/video/YolazAI.mp4');
define('CHATBOT_FAB_VIDEO_ZOOM', 6.9); // 1 = normal, >1 = zoom in, <1 = zoom out
