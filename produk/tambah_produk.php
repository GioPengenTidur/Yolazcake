<?php
include '../config/koneksi.php';

if(isset($_POST['simpan'])){

    $nama_produk = $_POST['nama_produk'];
    $harga = $_POST['harga'];
    $deskripsi = $_POST['deskripsi'];
    $stok = $_POST['stok'];

    $namaFoto = $_FILES['foto']['name'];
    $tmpFoto = $_FILES['foto']['tmp_name'];

    move_uploaded_file(
        $tmpFoto,
        "../assets/img/produk/".$namaFoto
    );

    $query = mysqli_query($conn,"
        INSERT INTO produk
        (nama_produk,harga,deskripsi,foto,stok)
        VALUES
        (
            '$nama_produk',
            '$harga',
            '$deskripsi',
            '$namaFoto',
            '$stok'
        )
    ");

    if($query){
        echo "<script>
                alert('Produk berhasil ditambahkan');
                window.location='menu.php';
              </script>";
    }else{
        echo mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Produk</title>
</head>
<body>

<h2>Tambah Produk</h2>

<form method="POST" enctype="multipart/form-data">

    <label>Nama Produk</label><br>
    <input type="text" name="nama_produk" required>
    <br><br>

    <label>Harga</label><br>
    <input type="number" name="harga" required>
    <br><br>

    <label>Deskripsi</label><br>
    <textarea name="deskripsi" rows="4"></textarea>
    <br><br>

    <label>Stok</label><br>
    <input type="number" name="stok" required>
    <br><br>

    <label>Foto</label><br>
    <input type="file" name="foto" required>
    <br><br>

    <button type="submit" name="simpan">
        Simpan Produk
    </button>

</form>

</body>
</html>