<?php
include '../config/koneksi.php';

$id = $_GET['id'];

$pemesanan = mysqli_query($conn,"
SELECT p.*,m.nama
FROM pemesanan p
JOIN member m ON p.id_member=m.id_member
WHERE p.id_pemesanan='$id'
");

$data = mysqli_fetch_assoc($pemesanan);
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
<td>Member</td>
<td><?= $data['nama']; ?></td>
</tr>

<tr>
<td>Tanggal</td>
<td><?= $data['tanggal']; ?></td>
</tr>

<tr>
<td>Total Harga</td>
<td>
Rp <?= number_format($data['total_harga'],0,',','.'); ?>
</td>
</tr>

<tr>
<td>Status</td>
<td><?= $data['status']; ?></td>
</tr>

</table>

<br>

<a href="data_pemesanan.php">
Kembali
</a>

</body>
</html>