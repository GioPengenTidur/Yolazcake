<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__.'/../config/csrf_helper.php';
include '../config/koneksi.php';

$role = $_SESSION['role'] ?? 'pengunjung';
if (!isset($_SESSION['username']) || !in_array($role, ['admin', 'kasir'], true)) {
    http_response_code(403);
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

csrf_verify_json($_POST['csrf'] ?? null);

$tipe = $_POST['tipe'] ?? '';

if ($tipe === 'pesanan') {
    $id     = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    $opts   = ['Menunggu', 'Diproses', 'Siap Diambil', 'Selesai', 'Dibatalkan'];
    if ($id <= 0 || !in_array($status, $opts, true)) {
        http_response_code(400);
        echo json_encode(['error' => 'data tidak valid']);
        exit;
    }
    $stmt = $conn->prepare("UPDATE pemesanan SET status_pesanan = ? WHERE id_pemesanan = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();

    // Kalau pesanan sudah Selesai/Dibatalkan dan pesanan itu terikat ke
    // meja tertentu, bebaskan mejanya lagi jadi Tersedia (kecuali memang
    // ada pesanan lain yang masih aktif di meja yang sama).
    if (in_array($status, ['Selesai', 'Dibatalkan'], true)) {
        $stmtMeja = $conn->prepare("SELECT nomor_meja FROM pemesanan WHERE id_pemesanan = ?");
        $stmtMeja->bind_param("i", $id);
        $stmtMeja->execute();
        $rowMeja = $stmtMeja->get_result()->fetch_assoc();
        $stmtMeja->close();

        if (!empty($rowMeja['nomor_meja'])) {
            $nomor = $rowMeja['nomor_meja'];
            $stmtCekLain = $conn->prepare("SELECT COUNT(*) c FROM pemesanan WHERE nomor_meja = ? AND status_pesanan IN ('Menunggu','Diproses','Siap Diambil')");
            $stmtCekLain->bind_param("s", $nomor);
            $stmtCekLain->execute();
            $masihAktif = (int)$stmtCekLain->get_result()->fetch_assoc()['c'];
            $stmtCekLain->close();

            if ($masihAktif === 0) {
                $stmtFree = $conn->prepare("UPDATE meja SET status='Tersedia' WHERE nomor_meja = ? AND status='Terisi'");
                $stmtFree->bind_param("s", $nomor);
                $stmtFree->execute();
                $stmtFree->close();
            }
        }
    }

    echo json_encode(['ok' => true]);
    exit;
}

if ($tipe === 'meja') {
    $id     = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    $opts   = ['Tersedia', 'Terisi', 'Dipesan', 'Tidak Aktif'];
    if ($id <= 0 || !in_array($status, $opts, true)) {
        http_response_code(400);
        echo json_encode(['error' => 'data tidak valid']);
        exit;
    }
    $stmt = $conn->prepare("UPDATE meja SET status = ? WHERE id_meja = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'tipe tidak dikenal']);
