<?php
session_start();
include '../config/koneksi.php';
require_once '../config/qr_helper.php';

$id_pemesanan = (int)($_POST['id_pemesanan'] ?? 0);
$kode_pesanan = trim($_POST['kode_pesanan'] ?? '');
$jumlah_orang = (int)($_POST['jumlah_orang'] ?? 0);

if ($id_pemesanan <= 0 || $kode_pesanan === '' || $jumlah_orang < 2 || $jumlah_orang > 10) {
    die('Data tidak valid. <a href="menuu.php" style="color:#D4AF37;">Kembali</a>');
}

// Pastikan pesanan ini memang ada & kode cocok (jangan sampai orang bikin
// split bill buat id_pemesanan milik orang lain dengan asal tebak ID).
$stmt = $conn->prepare("SELECT id_pemesanan, total_harga, kode_pesanan FROM pemesanan WHERE id_pemesanan = ? AND kode_pesanan = ?");
$stmt->bind_param("is", $id_pemesanan, $kode_pesanan);
$stmt->execute();
$pesanan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pesanan) {
    die('Pesanan tidak ditemukan. <a href="menuu.php" style="color:#D4AF37;">Kembali</a>');
}

// Kalau sudah pernah dibuatkan split bill sebelumnya, langsung arahkan ke
// yang lama saja daripada bikin duplikat.
$stmtCek = $conn->prepare("SELECT token FROM split_bill WHERE id_pemesanan = ? ORDER BY id_split DESC LIMIT 1");
$stmtCek->bind_param("i", $id_pemesanan);
$stmtCek->execute();
$existing = $stmtCek->get_result()->fetch_assoc();
$stmtCek->close();

if ($existing) {
    include 'success_overlay.php';
    tampilkan_sukses([
        'proses_judul'   => 'Membuka Split Bill…',
        'proses_sub'     => 'Link patungan untuk pesanan ini sudah pernah dibuat',
        'sukses_judul'   => 'Split Bill Ditemukan!',
        'sukses_sub'     => 'Mengarahkan ke halaman patungan yang sudah ada',
        'redirect'       => 'split_bill.php?token='.urlencode($existing['token']),
        'tombol_label'   => 'Buka Split Bill',
        'delay_sukses'   => 900,
        'delay_redirect' => 1800,
    ]);
    exit;
}

$nominal_per_orang = round($pesanan['total_harga'] / $jumlah_orang, 2);
$token = buat_token();

$conn->begin_transaction();
try {
    $stmtIns = $conn->prepare("INSERT INTO split_bill (id_pemesanan, token, jumlah_orang, nominal_per_orang) VALUES (?, ?, ?, ?)");
    $stmtIns->bind_param("isid", $id_pemesanan, $token, $jumlah_orang, $nominal_per_orang);
    $stmtIns->execute();
    $id_split = mysqli_insert_id($conn);
    $stmtIns->close();

    $stmtPeserta = $conn->prepare("INSERT INTO split_bill_bayar (id_split, nama_peserta) VALUES (?, ?)");
    for ($i = 1; $i <= $jumlah_orang; $i++) {
        $nama = "Peserta $i";
        $stmtPeserta->bind_param("is", $id_split, $nama);
        $stmtPeserta->execute();
    }
    $stmtPeserta->close();

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    die('Gagal membuat split bill: '.htmlspecialchars($e->getMessage()));
}

include 'success_overlay.php';
tampilkan_sukses([
    'proses_judul' => 'Membuat Split Bill…',
    'proses_sub'   => 'Membagi tagihan untuk '.$jumlah_orang.' orang',
    'sukses_judul' => 'Split Bill Berhasil Dibuat!',
    'sukses_sub'   => 'Bagikan link ini ke teman-temanmu supaya semua bisa tandai bagiannya sendiri',
    'redirect'     => 'split_bill.php?token='.urlencode($token),
    'tombol_label' => 'Buka Halaman Split Bill',
]);
exit;
