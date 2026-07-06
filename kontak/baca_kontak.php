<?php
session_start();
header('Content-Type: application/json');

function respond_json($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit();
}

if (!isset($_SESSION['username'])) {
    respond_json(false, 'Sesi login tidak ditemukan, silakan login ulang.');
}

require_once '../config/koneksi.php';

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    respond_json(false, 'Pesan tidak valid.');
}

$stmt = $conn->prepare("SELECT * FROM kontak WHERE id_kontak=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$row) {
    respond_json(false, 'Pesan tidak ditemukan.');
}

$emailSent = false;
$message   = 'Pesan ini sudah pernah dibaca sebelumnya.';

// Hanya tandai dibaca + kirim notifikasi otomatis saat transisi pertama kali dari "Belum Dibaca"
if ($row['status'] === 'Belum Dibaca') {
    $stmt = $conn->prepare("UPDATE kontak SET status='Sudah Dibaca' WHERE id_kontak=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    if (!empty($row['email'])) {
        require_once '../config/mailer.php';
        $subjek = $row['subjek'] ?: '(Tanpa Subjek)';
        $hasil  = kirimEmailNotifikasiDibaca($row['email'], $row['nama'], $subjek, $row['pesan']);
        $emailSent = $hasil['success'];
        $message   = $hasil['message'];
    } else {
        $message = 'Pelanggan tidak mencantumkan alamat email, notifikasi otomatis tidak dikirim.';
    }
}

respond_json(true, $message, [
    'status'     => 'Sudah Dibaca',
    'email_sent' => $emailSent,
]);
