<?php
/**
 * chatbot_riwayat.php
 * -----------------------------------------------------------------------
 * Endpoint pendukung fitur "Riwayat Obrolan" & "Obrolan Baru" di Pusat
 * Bantuan (bantuan/index.php). Dipanggil lewat fetch() dari front-end.
 *
 * Request (GET):
 *   ?aksi=daftar                -> daftar sesi obrolan milik user/tamu ini
 *   ?aksi=ambil&id_sesi=X       -> semua pesan dalam satu sesi
 *   ?aksi=hapus&id_sesi=X       -> hapus satu sesi obrolan
 *
 * Semua respons JSON. Identitas pemilik (login/tamu) sama seperti yang
 * dipakai di chatbot_api.php, lihat config/chat_memory_helper.php.
 */

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/koneksi.php';
require_once '../config/chat_memory_helper.php';

[$idUserChat, $guestTokenChat] = chat_ambil_identitas();

$aksi = $_GET['aksi'] ?? '';

if ($aksi === 'daftar') {
    $daftar = chat_daftar_sesi($conn, $idUserChat, $guestTokenChat);
    echo json_encode(['daftar' => $daftar]);
    exit();
}

if ($aksi === 'ambil') {
    $idSesi = (int) ($_GET['id_sesi'] ?? 0);
    if ($idSesi <= 0 || !chat_sesi_valid($conn, $idSesi, $idUserChat, $guestTokenChat)) {
        echo json_encode(['error' => 'Sesi obrolan tidak ditemukan.']);
        exit();
    }
    $pesan = chat_ambil_pesan_sesi($conn, $idSesi);
    echo json_encode(['pesan' => $pesan]);
    exit();
}

if ($aksi === 'hapus') {
    $idSesi = (int) ($_GET['id_sesi'] ?? 0);
    $ok = $idSesi > 0 && chat_hapus_sesi($conn, $idSesi, $idUserChat, $guestTokenChat);
    echo json_encode(['sukses' => $ok]);
    exit();
}

echo json_encode(['error' => 'Aksi tidak dikenali.']);
