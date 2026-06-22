<?php
session_start();
include '../config/koneksi.php';

if(isset($_POST['tambah'])){

    $id_produk = $_POST['id_produk'];
    $jumlah = $_POST['jumlah'];

    $_SESSION['keranjang'][$id_produk] =
        ($_SESSION['keranjang'][$id_produk] ?? 0) + $jumlah;
}

if(isset($_GET['hapus'])){

    $id_produk = $_GET['hapus'];

    unset($_SESSION['keranjang'][$id_produk]);

    header("Location: keranjang.php");
    exit();
}

if(isset($_GET['aksi'])){

    $id_produk = $_GET['id'];

    if($_GET['aksi'] == 'tambah'){
        $_SESSION['keranjang'][$id_produk]++;
    }

    if($_GET['aksi'] == 'kurang'){

        $_SESSION['keranjang'][$id_produk]--;

        if($_SESSION['keranjang'][$id_produk] <= 0){
            unset($_SESSION['keranjang'][$id_produk]);
        }
    }

    header("Location: keranjang.php");
    exit();
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

        $q = mysqli_query($conn,
            "SELECT * FROM produk WHERE id_produk='$id_produk'");

        $p = mysqli_fetch_assoc($q);

        $subtotal = $p['harga'] * $jumlah;
        $total += $subtotal;

        echo "
        <p>

        <b>{$p['nama_produk']}</b><br><br>

        <a href='keranjang.php?aksi=kurang&id=$id_produk'>➖</a>

        <b> $jumlah </b>

        <a href='keranjang.php?aksi=tambah&id=$id_produk'>➕</a>

        <br><br>

        Rp ".number_format($p['harga'])."
        x $jumlah

        = Rp ".number_format($subtotal)."

        <br><br>

        <a href='keranjang.php?hapus=$id_produk'
        onclick=\"return confirm('Hapus menu ini?')\">
        ❌ Hapus
        </a>

        </p>

        <hr>
        ";
    }

    echo "<h3>Total : Rp ".number_format($total)."</h3>";

}else{
    echo "Keranjang kosong";
}

?>

<br><br>

<a href="menuu.php">Pilih Makanan Lagi</a>

<br><br>

<a href="checkout.php">Checkout</a>

</body>
</html>