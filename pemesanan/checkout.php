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

<form action="qris.php" method="POST">

    <label>Nama Pemesan</label><br>
    <input type="text" name="nama_pemesan" required><br><br>

    <label>No HP</label><br>
    <input type="text" name="no_hp" required><br><br>

    <button type="submit">
        Lanjut Pembayaran
    </button>

    <input type="hidden"
       name="id_booking"
       value="<?= $_SESSION['id_booking'] ?? '' ?>">

</form>

</body>
</html>