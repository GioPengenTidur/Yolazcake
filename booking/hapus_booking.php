<?php
require_once '../config/koneksi.php';

if (isset($_GET['id'])) {

    $id = (int) $_GET['id'];

    $query = "DELETE FROM booking WHERE id_booking = $id";

    if (mysqli_query($conn, $query)) {

        echo "<script>
                alert('Data booking berhasil dihapus.');
                window.location.href='admin_booking.php';
              </script>";

    } else {

        echo "Gagal menghapus data: " . mysqli_error($conn);

    }

} else {

    header("Location: admin_booking.php");
    exit();

}
?>