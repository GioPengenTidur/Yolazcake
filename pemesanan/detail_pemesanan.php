<?php
include '../config/koneksi.php';

$id_pemesanan = $_GET['id'] ?? 0;

$pemesanan = mysqli_query($conn,"
SELECT *
FROM pemesanan
WHERE id_pemesanan='$id_pemesanan'
");

$data = mysqli_fetch_assoc($pemesanan);

if(!$data){
    die("Data pemesanan tidak ditemukan");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Detail Pemesanan</title>
</head>
<body>

<h2>Detail Pemesanan</h2>

<table border="1" cellpadding="10">

<tr>
<td>Nama Pemesan</td>
<td><?= $data['nama_pemesan']; ?></td>
</tr>

<tr>
<td>No HP</td>
<td><?= $data['no_hp']; ?></td>
</tr>

<tr>
<td>Tanggal</td>
<td><?= $data['tanggal']; ?></td>
</tr>

<tr>
<td>Total Harga</td>
<td>Rp <?= number_format($data['total_harga'],0,',','.'); ?></td>
</tr>

<tr>
<td>Metode Pembayaran</td>
<td><?= $data['metode_pembayaran']; ?></td>
</tr>

<tr>
<td>Status Pembayaran</td>
<td><?= $data['status_pembayaran']; ?></td>
</tr>

<tr>
<td>Status Pesanan</td>
<td><?= $data['status_pesanan']; ?></td>
</tr>

</table>

<br>

<a href="data_pemesanan.php">
Kembali
</a>

</body>
</html>