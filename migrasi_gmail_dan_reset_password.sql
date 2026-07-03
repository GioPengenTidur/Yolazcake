-- =========================================================
-- Migrasi tambahan: Login/Daftar pakai Gmail + Lupa Password (OTP)
-- Jalankan file ini SETELAH database `yolazcake_login` sudah ada
-- (tidak mengubah / menghapus data lama, hanya menambah kolom baru).
-- =========================================================

ALTER TABLE `users`
  ADD COLUMN `email` VARCHAR(100) DEFAULT NULL AFTER `username`,
  ADD COLUMN `reset_otp` VARCHAR(6) DEFAULT NULL AFTER `role`,
  ADD COLUMN `reset_otp_expires_at` DATETIME DEFAULT NULL AFTER `reset_otp`;

ALTER TABLE `users`
  ADD UNIQUE KEY `email` (`email`);
