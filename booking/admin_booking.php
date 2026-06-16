<?php
require_once '../config/koneksi.php';

$query = mysqli_query($conn, "SELECT * FROM booking ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Booking</title>
</head>
<body>

<h1>Daftar Booking YOLAZCAKE</h1>

<table border="1" cellpadding="10" cellspacing="0">

    <tr>
        <th>ID</th>
        <th>Nama Pemesan</th>
        <th>No HP</th>
        <th>Tanggal</th>
        <th>Jam</th>
        <th>Jumlah Orang</th>
        <th>Catatan</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>

    <?php while($data = mysqli_fetch_assoc($query)) : ?>

    <tr>
        <td><?= $data['id_booking']; ?></td>
        <td><?= $data['nama_pemesan']; ?></td>
        <td><?= $data['no_hp']; ?></td>
        <td><?= $data['tanggal_booking']; ?></td>
        <td><?= $data['jam_booking']; ?></td>
        <td><?= $data['jumlah_orang']; ?></td>
        <td><?= $data['catatan']; ?></td>
        <td><?= $data['status']; ?></td>

        <td>
            <?php if ($data['status'] == 'Pending') : ?>

                <a href="ubah_status.php?id=<?= $data['id_booking']; ?>&status=Dikonfirmasi"
                   onclick="return confirm('Konfirmasi booking ini?')">
                    Konfirmasi
                </a>

                |

                <a href="ubah_status.php?id=<?= $data['id_booking']; ?>&status=Dibatalkan"
                   onclick="return confirm('Batalkan booking ini?')">
                    Batalkan
                </a>

<a href="hapus_booking.php?id=<?= $data['id_booking']; ?>"
   onclick="return confirm('Yakin ingin menghapus data booking ini?')">
    Hapus
</a>

            <?php else : ?>

                -

            <?php endif; ?>
        </td>

    </tr>

    <?php endwhile; ?>

</table>

</body>
</html>