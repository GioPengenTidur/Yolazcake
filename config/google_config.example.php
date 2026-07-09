<?php
/**
 * Konfigurasi Google Sign-In (OAuth 2.0).
 *
 * CARA MENGISI:
 * 1. Buka https://console.cloud.google.com/apis/credentials
 * 2. Buat project baru (atau pakai yang sudah ada).
 * 3. Klik "Create Credentials" -> "OAuth client ID".
 *    - Application type: Web application
 *    - Authorized redirect URIs, isi persis:
 *      http://localhost/YOLAZCAKE/CAFE2/auth/google_callback.php
 *      (sesuaikan path kalau folder project-mu bukan "YOLAZCAKE/CAFE2")
 * 4. Google akan menampilkan Client ID & Client Secret. Salin ke bawah ini.
 * 5. Copy file ini jadi "google_config.php" (tanpa .example) di folder yang sama,
 *    lalu isi nilainya. File google_config.php TIDAK BOLEH di-commit/di-share
 *    karena berisi rahasia.
 */

define('GOOGLE_CLIENT_ID', 'isi_client_id_kamu.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'isi_client_secret_kamu');
define('GOOGLE_REDIRECT_URI', 'http://localhost/YOLAZCAKE/CAFE2/auth/google_callback.php');
