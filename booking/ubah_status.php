<?php
require_once '../config/koneksi.php';
include 'success_overlay.php';

if (isset($_GET['id']) && isset($_GET['status'])) {

    $id = (int) $_GET['id'];
    $status = mysqli_real_escape_string($conn, $_GET['status']);

    $allowed_status = ['Dikonfirmasi', 'Dibatalkan'];

    if (in_array($status, $allowed_status)) {

        $cek = mysqli_query($conn, "SELECT nama_pemesan FROM booking WHERE id_booking = $id");
        $booking = $cek ? mysqli_fetch_assoc($cek) : null;
        $nama_pemesan = $booking['nama_pemesan'] ?? 'Booking';

        $query = "UPDATE booking
                  SET status = '$status'
                  WHERE id_booking = $id";

        if (mysqli_query($conn, $query)) {

            $judulSukses = ($status === 'Dikonfirmasi')
                ? 'Booking Berhasil Dikonfirmasi!'
                : 'Booking Berhasil Dibatalkan!';

            tampilkan_sukses([
                'proses_judul' => 'Memperbarui Status Booking…',
                'proses_sub'   => 'Sedang menyimpan perubahan status reservasi',
                'sukses_judul' => $judulSukses,
                'sukses_sub'   => 'Booking atas nama "'.htmlspecialchars($nama_pemesan).'" kini berstatus '.htmlspecialchars($status),
                'redirect'     => 'admin_booking.php',
                'tombol_label' => 'Lanjutkan ke Data Booking',
            ]);
            exit;

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