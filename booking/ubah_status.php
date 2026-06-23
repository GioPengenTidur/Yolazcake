<?php
require_once '../config/koneksi.php';

if (isset($_GET['id']) && isset($_GET['status'])) {

    $id = (int) $_GET['id'];
    $status = mysqli_real_escape_string($conn, $_GET['status']);

    $allowed_status = ['Dikonfirmasi', 'Dibatalkan'];

    if (in_array($status, $allowed_status)) {

        $query = "UPDATE booking
                  SET status = '$status'
                  WHERE id_booking = $id";

        if (mysqli_query($conn, $query)) {

            header("Location: admin_booking.php");
            exit();

        } else {

            echo "Gagal mengubah status: " . mysqli_error($conn);

        }

    } else {

        echo "Status tidak valid.";

    }

} else {

    header("Location: admin_booking.php");
    exit();

}
?>