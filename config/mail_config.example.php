<?php
/**
 * Konfigurasi pengirim email (Gmail SMTP) untuk fitur OTP Lupa Password.
 *
 * CARA MENGISI:
 * 1. Buka akun Gmail yang mau dipakai untuk MENGIRIM email OTU (bukan email pelanggan).
 * 2. Aktifkan 2-Step Verification di akun Gmail tsb:
 *    https://myaccount.google.com/security
 * 3. Buat "App Password" (Sandi Aplikasi) khusus di:
 *    https://myaccount.google.com/apppasswords
 *    - Pilih app "Mail" / "Lainnya", beri nama bebas (mis. "YOLAZCAKE"),
 *      lalu Google akan menampilkan 16 karakter sandi aplikasi.
 *    - JANGAN pakai password Gmail biasa, harus App Password ini.
 * 4. Isi MAIL_USERNAME dengan alamat gmail pengirim,
 *    dan MAIL_PASSWORD dengan 16 karakter App Password (tanpa spasi).
 */

define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'isi_email_gmail_pengirim@gmail.com');
define('MAIL_PASSWORD', 'isi_app_password_16_karakter');
define('MAIL_FROM_NAME', 'YOLAZCAKE Sintang');

// Berapa lama (menit) kode OTP reset password berlaku
define('OTP_EXPIRE_MINUTES', 5);