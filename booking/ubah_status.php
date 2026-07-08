<?php
session_start();
require_once __DIR__.'/../config/staff_guard.php';
require_staff_login();
require_once __DIR__.'/../config/csrf_helper.php';
csrf_verify_or_die($_GET['csrf'] ?? null, 'admin_booking.php');
require_once '../config/koneksi.php';
include 'success_overlay.php';

if (isset($_GET['id']) && isset($_GET['status'])) {

    $id = (int) $_GET['id'];
    $status = $_GET['status'];

    $allowed_status = ['Dikonfirmasi', 'Dibatalkan'];

    if (in_array($status, $allowed_status)) {

        $stmt = $conn->prepare("SELECT nama_pemesan FROM booking WHERE id_booking = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $nama_pemesan = $booking['nama_pemesan'] ?? 'Booking';

        $stmt = $conn->prepare("UPDATE booking SET status = ? WHERE id_booking = ?");
        $stmt->bind_param("si", $status, $id);

        if ($stmt->execute()) {
            $stmt->close();

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