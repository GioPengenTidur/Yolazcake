<?php
require_once '../config/koneksi.php';
include 'success_overlay.php';

if (isset($_GET['id'])) {

    $id = (int) $_GET['id'];

    $cek = mysqli_query($conn, "SELECT nama_pemesan FROM booking WHERE id_booking = $id");
    $booking = $cek ? mysqli_fetch_assoc($cek) : null;
    $nama_pemesan = $booking['nama_pemesan'] ?? 'Booking';

    $query = "DELETE FROM booking WHERE id_booking = $id";

    if (mysqli_query($conn, $query)) {

        tampilkan_sukses([
            'proses_judul' => 'Menghapus Booking…',
            'proses_sub'   => 'Sedang memproses penghapusan data reservasi',
            'sukses_judul' => 'Booking Berhasil Dihapus!',
            'sukses_sub'   => 'Booking atas nama "'.htmlspecialchars($nama_pemesan).'" telah dihapus dari data',
            'redirect'     => 'admin_booking.php',
            'tombol_label' => 'Lanjutkan ke Data Booking',
        ]);
        exit;

    } else {

        echo "Gagal menghapus data: " . mysqli_error($conn);

    }

} else {

    header("Location: admin_booking.php");
    exit();

}
?>