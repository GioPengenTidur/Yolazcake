<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Meja YOLAZCAKE</title>
</head>
<body>

    <h1>Booking Meja YOLAZCAKE</h1>

    <form action="proses_booking.php" method="POST">

        <p>
            <label>Nama Pemesan</label><br>
            <input type="text" name="nama_pemesan" required>
        </p>

        <p>
            <label>Nomor HP</label><br>
            <input type="text" name="no_hp" required>
        </p>

        <p>
            <label>Tanggal Booking</label><br>
            <input type="date" name="tanggal_booking" required>
        </p>

        <p>
            <label>Jam Booking</label><br>
            <input type="time" name="jam_booking" required>
        </p>

        <p>
            <label>Jumlah Orang</label><br>
            <input type="number" name="jumlah_orang" min="1" required>
        </p>

        <p>
            <label>Catatan</label><br>
            <textarea name="catatan" rows="4"></textarea>
        </p>

        <button type="submit">
            Booking Sekarang
        </button>

    </form>

</body>
</html>