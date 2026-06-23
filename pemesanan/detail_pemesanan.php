<?php
include '../config/koneksi.php';

$id_pemesanan = $_GET['id'] ?? 0;

$pemesanan = mysqli_query($conn,"
SELECT *
FROM pemesanan
WHERE id_pemesanan='$id_pemesanan'
");

$data = mysqli_fetch_assoc($pemesanan);

if(!$data){
    die("Data pemesanan tidak ditemukan");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Pemesanan</title>

    <style>
        body{
            font-family: Arial, sans-serif;
            margin:40px;
        }

        h2{
            color:#333;
        }

        table{
            border-collapse: collapse;
            width: 600px;
        }

        td{
            border:1px solid #ddd;
            padding:12px;
        }

        td:first-child{
            font-weight:bold;
            width:220px;
            background:#f5f5f5;
        }

        .btn{
            display:inline-block;
            margin-top:20px;
            padding:10px 20px;
            background:#8B4513;
            color:white;
            text-decoration:none;
            border-radius:5px;
        }

        .btn:hover{
            background:#6d3410;
        }
    </style>
</head>
<body>

<h2>Detail Pemesanan</h2>

<table>

<tr>
    <td>Kode Pesanan</td>
    <td><?= $data['kode_pesanan']; ?></td>
</tr>

<tr>
    <td>Nama Pemesan</td>
    <td><?= $data['nama_pemesan']; ?></td>
</tr>

<tr>
    <td>No HP</td>
    <td><?= $data['no_hp']; ?></td>
</tr>

<?php if(!empty($data['nomor_meja'])){ ?>
<tr>
    <td>Nomor Meja</td>
    <td><?= $data['nomor_meja']; ?></td>
</tr>
<?php } ?>

<tr>
    <td>Tanggal Pemesanan</td>
    <td><?= $data['tanggal']; ?></td>
</tr>

<tr>
    <td>Total Harga</td>
    <td>
        Rp <?= number_format($data['total_harga'],0,',','.'); ?>
    </td>
</tr>

<tr>
    <td>Metode Pembayaran</td>
    <td><?= $data['metode_pembayaran']; ?></td>
</tr>

<tr>
    <td>Status Pembayaran</td>
    <td><?= $data['status_pembayaran']; ?></td>
</tr>

<tr>
    <td>Status Pesanan</td>
    <td><?= $data['status_pesanan']; ?></td>
</tr>

</table>

<a class="btn" href="../index.php">
    Kembali ke Beranda
</a>

</body>
</html>