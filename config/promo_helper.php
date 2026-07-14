<?php
/**
 * promo_helper.php
 * Validasi kode promo dan hitung nominal diskonnya terhadap subtotal
 * keranjang. Dipakai bersama di checkout.php, qris.php, dan
 * proses_pemesanan.php supaya aturan promo (aktif, tanggal berlaku,
 * min_belanja) selalu konsisten dan divalidasi ulang di server -- bukan
 * cuma percaya session/klien.
 */

function cek_promo(mysqli $conn, string $kode, float $subtotal): array {
    $kode = strtoupper(trim($kode));
    if ($kode === '') {
        return ['ok' => false, 'pesan' => 'Kode promo tidak boleh kosong.'];
    }

    $today = date('Y-m-d');
    $stmt = $conn->prepare(
        "SELECT * FROM promo WHERE kode_promo = ? AND status = 'Aktif'
         AND (tanggal_mulai IS NULL OR tanggal_mulai <= ?)
         AND (tanggal_selesai IS NULL OR tanggal_selesai >= ?)
         LIMIT 1"
    );
    $stmt->bind_param("sss", $kode, $today, $today);
    $stmt->execute();
    $promo = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$promo) {
        return ['ok' => false, 'pesan' => 'Kode promo tidak ditemukan atau sudah tidak berlaku.'];
    }

    $min_belanja = (float) ($promo['min_belanja'] ?? 0);
    if ($min_belanja > 0 && $subtotal < $min_belanja) {
        return [
            'ok'    => false,
            'pesan' => 'Minimal belanja untuk kode ini Rp'.number_format($min_belanja, 0, ',', '.').
                       ', belanjamu baru Rp'.number_format($subtotal, 0, ',', '.').'.',
        ];
    }

    $diskon_persen  = (int) ($promo['diskon_persen'] ?? 0);
    $diskon_nominal = round($subtotal * $diskon_persen / 100);
    // Jaga-jaga: diskon nggak boleh lebih besar dari subtotal.
    $diskon_nominal = min($diskon_nominal, $subtotal);

    return [
        'ok'             => true,
        'promo'          => $promo,
        'diskon_nominal' => $diskon_nominal,
    ];
}
