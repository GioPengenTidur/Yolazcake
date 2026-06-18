<?php
include '../config/koneksi.php';

$id = $_GET['id'];

$query = mysqli_query(
    $conn,
    "SELECT * FROM member WHERE id_member='$id'"
);

$member = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Member</title>
</head>
<body>

<h2>Detail Member</h2>

<table border="1" cellpadding="10">

<tr>
    <td>Nama</td>
    <td><?= $member['nama']; ?></td>
</tr>

<tr>
    <td>Email</td>
    <td><?= $member['email']; ?></td>
</tr>

<tr>
    <td>No HP</td>
    <td><?= $member['no_hp']; ?></td>
</tr>

<tr>
    <td>Alamat</td>
    <td><?= $member['alamat']; ?></td>
</tr>

<tr>
    <td>Poin</td>
    <td><?= $member['poin']; ?></td>
</tr>

</table>

<br>

<a href="data_member.php">
    Kembali
</a>

</body>
</html>