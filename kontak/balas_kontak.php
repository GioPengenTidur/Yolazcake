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

$id      = (int) ($_POST['id'] ?? 0);
$balasan = trim($_POST['balasan'] ?? '');

if ($id <= 0) {
    respond_json(false, 'Pesan tidak valid.');
}
if ($balasan === '') {
    respond_json(false, 'Isi balasan tidak boleh kosong.');
}

$stmt = $conn->prepare("SELECT * FROM kontak WHERE id_kontak=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$row) {
    respond_json(false, 'Pesan tidak ditemukan.');
}
if (empty($row['email'])) {
    respond_json(false, 'Pelanggan ini tidak mencantumkan alamat email, balasan tidak bisa dikirim.');
}

require_once '../config/mailer.php';
$subjek = $row['subjek'] ?: '(Tanpa Subjek)';
$hasil  = kirimEmailBalasanKontak($row['email'], $row['nama'], $subjek, $row['pesan'], $balasan);

if (!$hasil['success']) {
    respond_json(false, $hasil['message']);
}

$stmt = $conn->prepare("UPDATE kontak SET status='Dibalas', balasan=?, dibalas_at=NOW() WHERE id_kontak=?");
$stmt->bind_param("si", $balasan, $id);
$stmt->execute();
$stmt->close();

respond_json(true, 'Balasan berhasil dikirim ke email pelanggan.', [
    'redirect' => 'data_kontak.php?ok=balas',
]);
