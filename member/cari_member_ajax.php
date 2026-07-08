<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', '0'); // jangan biarkan warning PHP bocor & merusak output JSON

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

    $q = trim($_GET['q'] ?? '');
    if (mb_strlen($q) < 2) {
        echo json_encode(['success' => true, 'hasil' => []]);
        exit;
    }

    $hasil = gamif_cari_member_tujuan($conn, $q, (int) $member['id_member']);

    $out = array_map(function ($r) {
        return [
            'id_member' => (int) $r['id_member'],
            'nama'      => $r['nama'],
            'label'     => $r['nama'] . ($r['username'] ? ' (@' . $r['username'] . ')' : ''),
        ];
    }, $hasil);

    echo json_encode(['success' => true, 'hasil' => $out]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
