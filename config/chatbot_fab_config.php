<?php
/* ============================================================
   KONFIGURASI IKON CHATBOT FLOATING BUTTON (Yola AI)
   ------------------------------------------------------------
   Ubah nilai CHATBOT_FAB_ICON_MODE buat ganti tampilan ikon
   tombol chatbot mengambang tanpa perlu utak-atik chatbot_fab.php.

   Pilihan mode:
     'default' => ikon sparkle gradient ala Gemini (bawaan, tidak
                  perlu file gambar apa-apa)
     'gambar'  => pakai logo/gambar sendiri sebagai ikon tombol.
                  Isi path gambarnya di CHATBOT_FAB_IMAGE
                  (path relatif dari folder root project CAFE2,
                  contoh: 'assets/img/logo/chatbot-icon.png')
   ============================================================ */

define('CHATBOT_FAB_ICON_MODE', 'gambar'); // 'default' atau 'gambar'
define('CHATBOT_FAB_IMAGE', 'assets/img/logo/yola-ai-icon.png');
