<?php
include '../config/koneksi.php';

$query = mysqli_query($conn,
"SELECT * FROM pemesanan
ORDER BY id_pemesanan DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Pemesanan</title>

    <style>
        table{
            border-collapse: collapse;
            width: 100%;
        }

        th, td{
            border:1px solid #ccc;
            padding:10px;
            text-align:center;
        }

        th{
            background:#f4f4f4;
        }

        .btn{
            text-decoration:none;
            padding:5px 10px;
            border-radius:5px;
            color:white;
        }

        .detail{
            background:blue;
        }

        .hapus{
            background:red;
        }

        .status-menunggu{
            color:orange;
            font-weight:bold;
        }

        .status-diproses{
            color:blue;
            font-weight:bold;
        }

        .status-siap{
            color:purple;
            font-weight:bold;
        }

        .status-selesai{
            color:green;
            font-weight:bold;
        }

        .status-batal{
            color:red;
            font-weight:bold;
        }
    </style>

</head>
<body>

<h2>Data Pemesanan</h2>

<table>

<tr>
    <th>No</th>
    <th>Kode Pesanan</th>
    <th>Nama Pemesan</th>
    <th>Tanggal</th>
    <th>Total Harga</th>
    <th>Pembayaran</th>
    <th>Status</th>
    <th>Aksi</th>
</tr>

<?php
$no = 1;

while($data = mysqli_fetch_assoc($query)){
?>

<tr>

    <td><?= $no++; ?></td>

    <td><?= $data['kode_pesanan']; ?></td>

    <td><?= $data['nama_pemesan']; ?></td>

    <td><?= $data['tanggal']; ?></td>

    <td>
        Rp <?= number_format($data['total_harga'],0,',','.'); ?>
    </td>

    <td><?= $data['metode_pembayaran']; ?></td>

    <td>

        <?php

        if($data['status_pesanan']=='Menunggu'){
            echo "<span class='status-menunggu'>Menunggu</span>";
        }

        elseif($data['status_pesanan']=='Diproses'){
            echo "<span class='status-diproses'>Diproses</span>";
        }

        elseif($data['status_pesanan']=='Siap Diambil'){
            echo "<span class='status-siap'>Siap Diambil</span>";
        }

        elseif($data['status_pesanan']=='Selesai'){
            echo "<span class='status-selesai'>Selesai</span>";
        }

        else{
            echo "<span class='status-batal'>Dibatalkan</span>";
        }

        ?>

    </td>

    <td>

        <a class="btn detail"
        href="detail_pemesanan.php?id=<?= $data['id_pemesanan']; ?>">
        Detail
        </a>

        <a class="btn hapus"
        href="hapus_pemesanan.php?id=<?= $data['id_pemesanan']; ?>"
        onclick="return confirm('Yakin hapus data?')">
        Hapus
        </a>

    </td>

</tr>

<?php } ?>

</table>

</body>
</html>