<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', '0');

require_once '../config/koneksi.php';
require_once '../config/member_helper.php';
require_once '../config/gamifikasi_helper.php';

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu.']);
    exit;
}

try {
    $member = get_current_member($conn);
    if (!$member) {
        echo json_encode(['success' => false, 'message' => 'Kamu belum jadi member.']);
        exit;
    }

    $id_member = (int) $member['id_member'];
    $action = $_REQUEST['action'] ?? 'list';

    if ($action === 'mark_read') {
        gamif_tandai_semua_dibaca($conn, $id_member);
        echo json_encode(['success' => true]);
        exit;
    }

    $notif = gamif_daftar_notifikasi($conn, $id_member, 12);
    $belumDibaca = gamif_jumlah_notif_belum_dibaca($conn, $id_member);

    $out = array_map(function ($n) {
        return [
            'tipe'       => $n['tipe'],
            'judul'      => $n['judul'],
            'pesan'      => $n['pesan'],
            'link'       => $n['link'],
            'is_read'    => (bool) $n['is_read'],
            'waktu'      => date('d M Y, H:i', strtotime($n['created_at'])),
        ];
    }, $notif);

    echo json_encode(['success' => true, 'notifikasi' => $out, 'belum_dibaca' => $belumDibaca]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
