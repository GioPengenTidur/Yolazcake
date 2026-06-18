<?php
include '../config/koneksi.php';

$query = mysqli_query($conn, "SELECT * FROM produk ORDER BY id_produk DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Produk</title>

    <style>
        table{
            border-collapse: collapse;
            width: 100%;
        }

        th,td{
            border:1px solid black;
            padding:10px;
            text-align:center;
        }

        img{
            border-radius:5px;
        }

        .btn{
            padding:8px 12px;
            text-decoration:none;
            border-radius:5px;
        }

        .tambah{
            background:green;
            color:white;
        }

        .edit{
            background:orange;
            color:white;
        }

        .hapus{
            background:red;
            color:white;
        }

        .detail{
            background:blue;
            color:white;
        }
    </style>
</head>
<body>

<h2>Data Produk</h2>

<p>
    <a href="tambah_produk.php" class="btn tambah">
        + Tambah Produk
    </a>
</p>

<table>

<tr>
    <th>No</th>
    <th>Foto</th>
    <th>Nama Produk</th>
    <th>Harga</th>
    <th>Stok</th>
    <th>Aksi</th>
</tr>

<?php
$no = 1;

while($data = mysqli_fetch_assoc($query)){
?>

<tr>

    <td><?= $no++; ?></td>

    <td>
        <img
        src="../assets/img/produk/<?= $data['foto']; ?>"
        width="100">
    </td>

    <td><?= $data['nama_produk']; ?></td>

    <td>
        Rp <?= number_format($data['harga'],0,',','.'); ?>
    </td>

    <td><?= $data['stok']; ?></td>

    <td>

        <a
        href="detail_produk.php?id=<?= $data['id_produk']; ?>"
        class="btn detail">
        Detail
        </a>

        <a
        href="edit_produk.php?id=<?= $data['id_produk']; ?>"
        class="btn edit">
        Edit
        </a>

        <a
        href="hapus.php?id=<?= $data['id_produk']; ?>"
        class="btn hapus"
        onclick="return confirm('Yakin ingin menghapus produk ini?')">
        Hapus
        </a>

    </td>

</tr>

<?php } ?>

</table>

</body>
</html>