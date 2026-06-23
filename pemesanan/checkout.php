<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
</head>

<style>

    body{
        place-content: center;
        
    }

    h2{
        font-family: 'Playfair Display', Georgia, serif;
        font-size:2em;
        color: #6D4C41;
        place-self: center;
    }

    button{
        place-self: center;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1.25rem 2.5rem;
        border-radius: 9999px;
        border: none;
        background-color: #6d4c41;
        color: white;
        font-size: 0.875rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        text-decoration: none;
        box-shadow: 0 12px 40px rgba(92, 64, 51, 0.2);
        transition: all var(--transition-normal);
        position: relative;
        overflow: hidden;
    }

    #formCheckout{
        place-self: center;
        place-content: center;
    }

    form{
        display: flex;
        justify-content: center;
        flex-direction: column;
        place-content: center;
        color: #6D4C41;
        gap: 1px;
    }

    input{
        padding: 50px;
        margin: 1px;
    }

</style>

<body>

<h2>Checkout</h2>

<<<<<<< HEAD
<div id="formCheckout">
    <form action="qris.php" method="POST">

        <label>Nama Pemesan</label><br>
        <input type="text" name="nama_pemesan" required><br><br>

        <label>No HP</label><br>
        <input type="text" name="no_hp" required><br><br>
=======
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
>>>>>>> c4c85f022b51778d6bad8baab6e446c624b08bae

        <button type="submit">
            Lanjut Pembayaran
        </button>

        <input type="hidden"
        name="id_booking"
        value="<?= $_SESSION['id_booking'] ?? '' ?>">

    </form>
</div>


</body>
</html>