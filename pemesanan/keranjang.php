<?php
session_start();
include '../config/koneksi.php';

if(isset($_POST['tambah'])){

    $id_produk = $_POST['id_produk'];
    $jumlah = $_POST['jumlah'];

    $_SESSION['keranjang'][$id_produk] =
        ($_SESSION['keranjang'][$id_produk] ?? 0) + $jumlah;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Keranjang</title>
</head>
<body>

<h2>Keranjang Belanja</h2>

<?php

$total = 0;

if(!empty($_SESSION['keranjang'])){

    foreach($_SESSION['keranjang'] as $id_produk => $jumlah){

        $q = mysqli_query($conn,"SELECT * FROM produk WHERE id_produk='$id_produk'");
        $p = mysqli_fetch_assoc($q);

        $subtotal = $p['harga'] * $jumlah;
        $total += $subtotal;

        echo "
        <p>
            {$p['nama_produk']} -
            {$jumlah} x Rp ".number_format($p['harga'])."
            = Rp ".number_format($subtotal)."
        </p>
        ";
    }

    echo "<h3>Total : Rp ".number_format($total)."</h3>";

}else{
    echo "Keranjang kosong";
}

?>

<br><br>

<a href="menu.php">Pilih Makanan Lagi</a>
<br><br>
<a href="checkout.php">Checkout</a>

</body>
</html>