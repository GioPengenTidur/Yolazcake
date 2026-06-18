<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['keranjang']) || empty($_SESSION['keranjang'])) {
    die("Keranjang kosong");
}

$nama_pemesan = $_SESSION['nama_pemesan'] ?? '';
$no_hp = $_SESSION['no_hp'] ?? '';

$id_booking = $_SESSION['id_booking'] ?? null;
$id_booking_sql = $id_booking ? "'$id_booking'" : "NULL";

$tanggal = date('Y-m-d H:i:s');

$total_harga = 0;

foreach ($_SESSION['keranjang'] as $id_produk => $jumlah) {

    $q = mysqli_query($conn,
        "SELECT harga
         FROM produk
         WHERE id_produk='$id_produk'");

    $p = mysqli_fetch_assoc($q);

    $total_harga += ($p['harga'] * $jumlah);
}

$kode_pesanan = "ORD" . date("YmdHis");

mysqli_query($conn,"
INSERT INTO pemesanan
(
kode_pesanan,
id_member,
id_booking,
tanggal,
total_harga,
nama_pemesan,
no_hp,
metode_pembayaran,
status_pembayaran,
status_pesanan
)
VALUES
(
'$kode_pesanan',
NULL,
$id_booking_sql,
'$tanggal',
'$total_harga',
'$nama_pemesan',
'$no_hp',
'QRIS',
'Lunas',
'Menunggu'
)
");

$id_pemesanan = mysqli_insert_id($conn);

foreach ($_SESSION['keranjang'] as $id_produk => $jumlah) {

    $q = mysqli_query($conn,
        "SELECT harga
         FROM produk
         WHERE id_produk='$id_produk'");

    $p = mysqli_fetch_assoc($q);

    $subtotal = $p['harga'] * $jumlah;

    mysqli_query($conn,"
    INSERT INTO detail_pemesanan
    (
    id_pemesanan,
    id_produk,
    jumlah,
    subtotal
    )
    VALUES
    (
    '$id_pemesanan',
    '$id_produk',
    '$jumlah',
    '$subtotal'
    )
    ");
}

unset($_SESSION['keranjang']);

echo "
<h2>Pesanan Berhasil</h2>
<p>Kode Pesanan : $kode_pesanan</p>
<a href='detail_pemesanan.php?id=$id_pemesanan'>
Lihat Detail Pesanan
</a>
";
?>