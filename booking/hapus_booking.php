<?php
session_start();
require_once __DIR__.'/../config/staff_guard.php';
require_staff_login();
require_once __DIR__.'/../config/csrf_helper.php';
csrf_verify_or_die($_GET['csrf'] ?? null, 'admin_booking.php');
require_once '../config/koneksi.php';
include 'success_overlay.php';

if (isset($_GET['id'])) {

    $id = (int) $_GET['id'];

    $stmt = $conn->prepare("SELECT nama_pemesan FROM booking WHERE id_booking = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $nama_pemesan = $booking['nama_pemesan'] ?? 'Booking';

    $stmt = $conn->prepare("DELETE FROM booking WHERE id_booking = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $stmt->close();

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