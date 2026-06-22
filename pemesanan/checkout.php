<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
</head>
<body>

<h2>Checkout</h2>

<h3>Detail Pesanan</h3>

<table border="1" cellpadding="10" cellspacing="0">

<tr>
    <th>Produk</th>
    <th>Jumlah</th>
    <th>Harga</th>
    <th>Subtotal</th>
</tr>

<?php

include '../config/koneksi.php';

$total = 0;

foreach($_SESSION['keranjang'] as $id_produk => $jumlah){

    $query = mysqli_query($conn,
        "SELECT nama_produk, harga
         FROM produk
         WHERE id_produk='$id_produk'");

    $produk = mysqli_fetch_assoc($query);

    $subtotal = $produk['harga'] * $jumlah;
    $total += $subtotal;
?>

<tr>
    <td><?= $produk['nama_produk']; ?></td>
    <td><?= $jumlah; ?></td>
    <td>Rp <?= number_format($produk['harga'],0,',','.'); ?></td>
    <td>Rp <?= number_format($subtotal,0,',','.'); ?></td>
</tr>

<?php } ?>

<tr>
    <td colspan="3"><b>Total</b></td>
    <td>
        <b>
            Rp <?= number_format($total,0,',','.'); ?>
        </b>
    </td>
</tr>

</table>

<br><br>

<form action="qris.php" method="POST">

    <?php if(!isset($_SESSION['id_booking'])) { ?>

<label>Nama Pemesan</label><br>
<input type="text" name="nama_pemesan" required><br><br>

<label>No HP</label><br>
<input type="text" name="no_hp" required><br><br>

<?php } ?>

    <button type="submit">
        Lanjut Pembayaran
    </button>

    <input type="hidden"
       name="id_booking"
       value="<?= $_SESSION['id_booking'] ?? '' ?>">

</form>

</body>
</html>