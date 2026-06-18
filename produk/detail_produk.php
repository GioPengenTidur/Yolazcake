<?php
include '../config/koneksi.php';

$id = $_GET['id'];

$query = mysqli_query(
    $conn,
    "SELECT * FROM produk WHERE id_produk='$id'"
);

$produk = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Produk</title>

    <style>
        body{
            font-family: Arial, sans-serif;
            padding:20px;
        }

        .card{
            max-width:700px;
            margin:auto;
            border:1px solid #ddd;
            padding:20px;
            border-radius:10px;
        }

        img{
            width:100%;
            max-width:350px;
            border-radius:10px;
        }

        .btn{
            display:inline-block;
            margin-top:15px;
            padding:10px 15px;
            text-decoration:none;
            background:#333;
            color:white;
            border-radius:5px;
        }
    </style>
</head>
<body>

<div class="card">

    <h2><?= $produk['nama_produk']; ?></h2>

    <img src="../assets/img/produk/<?= $produk['foto']; ?>">

    <h3>
        Rp <?= number_format($produk['harga'],0,',','.'); ?>
    </h3>

    <p>
        <strong>Stok:</strong>
        <?= $produk['stok']; ?>
    </p>

    <p>
        <strong>Deskripsi:</strong>
        <br>
        <?= $produk['deskripsi']; ?>
    </p>

    <a href="data_produk.php" class="btn">
        Kembali
    </a>

</div>

</body>
</html>