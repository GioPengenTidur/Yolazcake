<?php
session_start();

if(isset($_POST['nama_pemesan'])){
    $_SESSION['nama_pemesan'] = $_POST['nama_pemesan'];
}

if(isset($_POST['no_hp'])){
    $_SESSION['no_hp'] = $_POST['no_hp'];
}

$_SESSION['id_booking'] = $_POST['id_booking'] ?? ($_SESSION['id_booking'] ?? null);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pembayaran QRIS</title>
</head>
<body>

<h2>Pembayaran QRIS</h2>

<p>Silakan scan QRIS berikut</p>

<img src="../assets/img/image.png" width="300">

<br><br>

<form action="proses_pemesanan.php" method="POST">
    <button type="submit" name="bayar">
        Saya Sudah Bayar
    </button>
</form>

</body>
</html>