<?php
include '../config/koneksi.php';

$id = $_GET['id'];

$query = mysqli_query($conn,
"SELECT * FROM pemesanan WHERE id_pemesanan='$id'");

$data = mysqli_fetch_assoc($query);

if(isset($_POST['update'])){

    $status = $_POST['status'];

    mysqli_query($conn,"
    UPDATE pemesanan
    SET status='$status'
    WHERE id_pemesanan='$id'
    ");

    echo "
    <script>
    alert('Status berhasil diperbarui');
    window.location='data_pemesanan.php';
    </script>
    ";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Pemesanan</title>
</head>
<body>

<h2>Edit Status Pemesanan</h2>

<form method="POST">

<label>Status</label><br>

<select name="status">

<option value="Pending"
<?= $data['status']=='Pending'?'selected':'' ?>>
Pending
</option>

<option value="Diproses"
<?= $data['status']=='Diproses'?'selected':'' ?>>
Diproses
</option>

<option value="Selesai"
<?= $data['status']=='Selesai'?'selected':'' ?>>
Selesai
</option>

</select>

<br><br>

<button type="submit" name="update">
Update
</button>

</form>

</body>
</html>