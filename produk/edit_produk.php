<?php
include '../config/koneksi.php';

$id = $_GET['id'];

$data = mysqli_query($conn, "SELECT * FROM produk WHERE id_produk='$id'");
$produk = mysqli_fetch_assoc($data);

if(isset($_POST['update'])){

    $nama_produk = $_POST['nama_produk'];
    $harga = $_POST['harga'];
    $deskripsi = $_POST['deskripsi'];
    $stok = $_POST['stok'];

    if($_FILES['foto']['name'] != ''){

        $foto = $_FILES['foto']['name'];
        $tmp = $_FILES['foto']['tmp_name'];

        move_uploaded_file(
            $tmp,
            "../assets/img/produk/".$foto
        );

        mysqli_query($conn,"
            UPDATE produk
            SET
            nama_produk='$nama_produk',
            harga='$harga',
            deskripsi='$deskripsi',
            stok='$stok',
            foto='$foto'
            WHERE id_produk='$id'
        ");

    }else{

        mysqli_query($conn,"
            UPDATE produk
            SET
            nama_produk='$nama_produk',
            harga='$harga',
            deskripsi='$deskripsi',
            stok='$stok'
            WHERE id_produk='$id'
        ");

    }

    echo "
    <script>
        alert('Produk berhasil diupdate');
        window.location='data_produk.php';
    </script>
    ";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Produk</title>
</head>
<body>

<h2>Edit Produk</h2>

<form method="POST" enctype="multipart/form-data">

    <label>Nama Produk</label><br>
    <input type="text"
           name="nama_produk"
           value="<?= $produk['nama_produk']; ?>"
           required>
    <br><br>

    <label>Harga</label><br>
    <input type="number"
           name="harga"
           value="<?= $produk['harga']; ?>"
           required>
    <br><br>

    <label>Deskripsi</label><br>
    <textarea name="deskripsi"><?= $produk['deskripsi']; ?></textarea>
    <br><br>

    <label>Stok</label><br>
    <input type="number"
           name="stok"
           value="<?= $produk['stok']; ?>"
           required>
    <br><br>

    <p>Foto Saat Ini:</p>

    <img src="../assets/img/produk/<?= $produk['foto']; ?>"
         width="150">

    <br><br>

    <label>Ganti Foto (Opsional)</label><br>
    <input type="file" name="foto">

    <br><br>

    <button type="submit" name="update">
        Update Produk
    </button>

</form>

</body>
</html>