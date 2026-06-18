<?php
session_start();

include '../config/koneksi.php';

$query = mysqli_query($conn, "SELECT * FROM produk");

if(!$query){
    die(mysqli_error($conn));
}

echo "Jumlah Produk : ".mysqli_num_rows($query)."<br><br>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Menu Cafe</title>
</head>
<body>

<h2>Daftar Menu</h2>

<?php while($row = mysqli_fetch_assoc($query)) { ?>
    <div style="border:1px solid #ccc;padding:10px;margin:10px;">
        <h3><?= $row['nama_produk']; ?></h3>
        <p>Rp <?= number_format($row['harga']); ?></p>

        <form action="keranjang.php" method="POST">
            <input type="hidden" name="id_produk" value="<?= $row['id_produk']; ?>">
            <input type="number" name="jumlah" value="1" min="1">
            <button type="submit" name="tambah">Tambah Keranjang</button>
        </form>
    </div>
<?php } ?>

<a href="keranjang.php">Lihat Keranjang</a>

</body>
</html>