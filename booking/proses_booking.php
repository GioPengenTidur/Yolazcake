<?php
require_once '../config/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nama_pemesan = mysqli_real_escape_string($conn, $_POST['nama_pemesan']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $tanggal_booking = $_POST['tanggal_booking'];

    // Validasi tanggal booking
    date_default_timezone_set('Asia/Jakarta');

    $tanggal_input = date('Y-m-d', strtotime($tanggal_booking));
    $tanggal_hari_ini = date('Y-m-d');

    if ($tanggal_input < $tanggal_hari_ini) {
        echo "<script>
                alert('Tanggal booking tidak boleh kurang dari hari ini!');
                window.history.back();
              </script>";
        exit();
    }

    $jam_booking = $_POST['jam_booking'];

    // Validasi jam operasional YOLAZCAKE (08:00 - 22:00)
    if ($jam_booking < '08:00' || $jam_booking > '22:00') {
        echo "<script>
                alert('Booking hanya dapat dilakukan pada jam operasional YOLAZCAKE (08:00 - 22:00).');
                window.history.back();
              </script>";
        exit();
    }

    // Validasi kapasitas booking (maksimal 5 booking per jam)
    $cek_booking = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM booking
         WHERE tanggal_booking = '$tanggal_booking'
         AND jam_booking = '$jam_booking'
         AND status != 'Dibatalkan'"
    );

    $hasil = mysqli_fetch_assoc($cek_booking);

    if ($hasil['total'] >= 5) {
        echo "<script>
                alert('Maaf, slot booking pada tanggal dan jam tersebut sudah penuh.');
                window.history.back();
              </script>";
        exit();
    }

    $jumlah_orang = $_POST['jumlah_orang'];
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);

    $query = "INSERT INTO booking (
                    nama_pemesan,
                    no_hp,
                    tanggal_booking,
                    jam_booking,
                    jumlah_orang,
                    catatan
                ) VALUES (
                    '$nama_pemesan',
                    '$no_hp',
                    '$tanggal_booking',
                    '$jam_booking',
                    '$jumlah_orang',
                    '$catatan'
                )";

   if (mysqli_query($conn, $query)) {

    $id_booking = mysqli_insert_id($conn);

    echo "<script>
            if(confirm('Booking berhasil! Ingin pesan makanan sekarang?')){
                window.location.href='../pemesanan/menu.php?id_booking=$id_booking';
            }else{
                window.location.href='booking.php';
            }
          </script>";

} else {
    echo "Error: " . mysqli_error($conn);
}

} else {
    header("Location: booking.php");
    exit();
}
?>