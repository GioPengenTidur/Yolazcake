<?php
/**
 * Helper validasi & penyimpanan file upload gambar.
 * Dipakai oleh modul produk, galeri, dan menu_foto supaya konsisten
 * (validasi tipe file, ukuran, dan nama file aman dari path traversal).
 *
 * @param array  $file        Elemen dari $_FILES['nama_input']
 * @param string $targetDir   Folder tujuan (relatif dari file pemanggil), mis. "../assets/img/produk/"
 * @param int    $maxSizeByte Ukuran maksimum file dalam byte (default 5MB)
 * @return array ['success' => bool, 'filename' => string|null, 'error' => string|null]
 */
function upload_gambar(array $file, string $targetDir, int $maxSizeByte = 5 * 1024 * 1024): array {
    $allowedExt  = ['jpg', 'jpeg', 'png', 'webp'];
    $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];

    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'filename' => null, 'error' => 'Upload gagal (kode error: ' . ($file['error'] ?? 'tidak diketahui') . ').'];
    }

    if ($file['size'] > $maxSizeByte) {
        return ['success' => false, 'filename' => null, 'error' => 'Ukuran file melebihi batas maksimum ' . round($maxSizeByte / 1024 / 1024) . 'MB.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return ['success' => false, 'filename' => null, 'error' => 'Ekstensi file tidak diizinkan. Gunakan: ' . implode(', ', $allowedExt) . '.'];
    }

    // Verifikasi tipe MIME asli file (bukan hanya nama ekstensi) supaya file
    // berbahaya (mis. .php yang di-rename jadi .jpg) tetap tertolak.
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $realMime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($realMime, $allowedMime, true)) {
        return ['success' => false, 'filename' => null, 'error' => 'File bukan gambar yang valid.'];
    }

    // Nama file baru yang aman & unik agar tidak menimpa file lain / path traversal.
    $namaBaru = uniqid('img_', true) . '.' . $ext;

    if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], rtrim($targetDir, '/') . '/' . $namaBaru)) {
        return ['success' => false, 'filename' => null, 'error' => 'Gagal menyimpan file ke server.'];
    }

    return ['success' => true, 'filename' => $namaBaru, 'error' => null];
}
