<?php
include '../config/koneksi.php';

if(isset($_POST['simpan'])){

    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $no_hp = $_POST['no_hp'];
    $alamat = $_POST['alamat'];

    mysqli_query($conn,"
        INSERT INTO member
        (nama,email,no_hp,alamat,poin)
        VALUES
        (
            '$nama',
            '$email',
            '$no_hp',
            '$alamat',
            0
        )
    ");

    echo "
    <script>
    alert('Member berhasil ditambahkan');
    window.location='data_member.php';
    </script>
    ";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Member</title>
</head>
<body>

<h2>Tambah Member</h2>

<form method="POST">

    <label>Nama</label><br>
    <input type="text" name="nama" required>
    <br><br>

    <label>Email</label><br>
    <input type="email" name="email" required>
    <br><br>

    <label>No HP</label><br>
    <input type="text" name="no_hp" required>
    <br><br>

    <label>Alamat</label><br>
    <textarea name="alamat"></textarea>
    <br><br>

    <button type="submit" name="simpan">
        Simpan Member
    </button>

</form>

</body>
</html>