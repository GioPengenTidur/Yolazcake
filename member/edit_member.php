<?php
include '../config/koneksi.php';

$id = $_GET['id'];

$query = mysqli_query(
    $conn,
    "SELECT * FROM member WHERE id_member='$id'"
);

$member = mysqli_fetch_assoc($query);

if(isset($_POST['update'])){

    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $no_hp = $_POST['no_hp'];
    $alamat = $_POST['alamat'];
    $poin = $_POST['poin'];

    mysqli_query($conn,"
        UPDATE member
        SET
        nama='$nama',
        email='$email',
        no_hp='$no_hp',
        alamat='$alamat',
        poin='$poin'
        WHERE id_member='$id'
    ");

    echo "
    <script>
    alert('Data member berhasil diupdate');
    window.location='data_member.php';
    </script>
    ";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Member</title>
</head>
<body>

<h2>Edit Member</h2>

<form method="POST">

    <label>Nama</label><br>
    <input type="text"
           name="nama"
           value="<?= $member['nama']; ?>"
           required>
    <br><br>

    <label>Email</label><br>
    <input type="email"
           name="email"
           value="<?= $member['email']; ?>"
           required>
    <br><br>

    <label>No HP</label><br>
    <input type="text"
           name="no_hp"
           value="<?= $member['no_hp']; ?>"
           required>
    <br><br>

    <label>Alamat</label><br>
    <textarea name="alamat"><?= $member['alamat']; ?></textarea>
    <br><br>

    <label>Poin</label><br>
    <input type="number"
           name="poin"
           value="<?= $member['poin']; ?>"
           required>
    <br><br>

    <button type="submit" name="update">
        Update Member
    </button>

</form>

</body>
</html>