<?php
/**
 * qr_helper.php
 * -----------------------------------------------------------------------
 * Helper kecil buat fitur QR Meja & Split Bill:
 *  - base_app_url()   -> menebak URL dasar aplikasi (skema+host+path
 *                        sampai folder CAFE2), dipakai untuk generate
 *                        link QR & link share split bill.
 *  - qr_image_url()   -> bikin URL gambar QR code (pakai layanan publik
 *                        api.qrserver.com, gratis & tanpa API key, jadi
 *                        nggak perlu install library QR di server XAMPP).
 *  - buat_token()      -> random token buat link split bill (susah ditebak).
 */

if (!function_exists('base_app_url')) {
    function base_app_url(): string {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

        // SCRIPT_NAME contoh: /CAFE2/meja/qr_meja.php -> ambil sampai /CAFE2
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $parts  = explode('/', trim($script, '/'));
        $root   = $parts[0] ?? 'CAFE2'; // folder project di htdocs

        return $scheme.'://'.$host.'/'.$root;
    }
}

if (!function_exists('qr_image_url')) {
    function qr_image_url(string $data, int $size = 260): string {
        return 'https://api.qrserver.com/v1/create-qr-code/?size='.$size.'x'.$size.'&data='.urlencode($data);
    }
}

if (!function_exists('buat_token')) {
    function buat_token(int $panjang = 20): string {
        try {
            return bin2hex(random_bytes($panjang));
        } catch (Exception $e) {
            return bin2hex(uniqid((string)mt_rand(), true));
        }
    }
}
