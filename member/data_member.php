<?php
include '../config/koneksi.php';

$query = mysqli_query($conn, "SELECT * FROM member ORDER BY id_member DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Member</title>

    <style>
        table{
            border-collapse: collapse;
            width:100%;
        }

        th,td{
            border:1px solid black;
            padding:10px;
            text-align:center;
        }

        .btn{
            padding:8px 12px;
            text-decoration:none;
            border-radius:5px;
            color:white;
        }

        .tambah{
            background:green;
        }

        .detail{
            background:blue;
        }

        .edit{
            background:orange;
        }

        .hapus{
            background:red;
        }
    </style>
</head>
<body>

<h2>Data Member</h2>

<p>
    <a href="tambah_member.php" class="btn tambah">
        + Tambah Member
    </a>
</p>

<table>

<tr>
    <th>No</th>
    <th>Nama</th>
    <th>Email</th>
    <th>No HP</th>
    <th>Poin</th>
    <th>Aksi</th>
</tr>

<?php
$no = 1;

while($data = mysqli_fetch_assoc($query)){
?>

<tr>

    <td><?= $no++; ?></td>

    <td><?= $data['nama']; ?></td>

    <td><?= $data['email']; ?></td>

    <td><?= $data['no_hp']; ?></td>

    <td><?= $data['poin']; ?></td>

    <td>

        <a href="detail_member.php?id=<?= $data['id_member']; ?>" class="btn detail">
            Detail
        </a>

        <a href="edit_member.php?id=<?= $data['id_member']; ?>" class="btn edit">
            Edit
        </a>

        <a href="hapus_member.php?id=<?= $data['id_member']; ?>"
           class="btn hapus"
           onclick="return confirm('Yakin ingin menghapus member ini?')">
            Hapus
        </a>

    </td>

</tr>

<?php } ?>

</table>

</body>
</html>