<?php
include '../config/koneksi.php';

$query = mysqli_query($conn,"
SELECT p.*, m.nama
FROM pemesanan p
JOIN member m ON p.id_member = m.id_member
ORDER BY p.id_pemesanan DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Data Pemesanan</title>
</head>
<body>

<h2>Data Pemesanan</h2>

<a href="tambah_pemesanan.php">
Tambah Pemesanan
</a>

<br><br>

<table border="1" cellpadding="10">

<tr>
    <th>No</th>
    <th>Member</th>
    <th>Tanggal</th>
    <th>Total Harga</th>
    <th>Status</th>
    <th>Aksi</th>
</tr>

<?php
$no = 1;

while($data = mysqli_fetch_assoc($query)){
?>

<tr>

<td><?= $no++; ?></td>

<td><?= $data['nama']; ?></td>

<td><?= $data['tanggal']; ?></td>

<td>
Rp <?= number_format($data['total_harga'],0,',','.'); ?>
</td>

<td><?= $data['status']; ?></td>

<td>

<a href="detail_pemesanan.php?id=<?= $data['id_pemesanan']; ?>">
Detail
</a>

<a href="edit_pemesanan.php?id=<?= $data['id_pemesanan']; ?>">
Edit
</a>

<a href="hapus_pemesanan.php?id=<?= $data['id_pemesanan']; ?>"
onclick="return confirm('Yakin hapus?')">
Hapus
</a>

</td>

</tr>

<?php } ?>

</table>

</body>
</html>