<?php
/**
 * split_bill_ajax.php
 * -----------------------------------------------------------------------
 * Endpoint publik (nggak butuh login) buat halaman split_bill.php. Akses
 * dikontrol pakai `token` (susah ditebak, 40 karakter random) -- siapa
 * saja yang punya link bisa toggle status bayar & ganti nama peserta,
 * itu memang tujuannya (dipakai bareng-bareng di grup).
 */
session_start();
header('Content-Type: application/json');
include '../config/koneksi.php';

$token  = trim($_POST['token'] ?? $_GET['token'] ?? '');
$action = trim($_POST['action'] ?? $_GET['action'] ?? 'status');

if ($token === '') {
    http_response_code(400);
    echo json_encode(['error' => 'token kosong']);
    exit;
}

$stmtSplit = $conn->prepare("
    SELECT sb.id_split, sb.jumlah_orang, sb.nominal_per_orang, sb.id_pemesanan,
           p.kode_pesanan, p.total_harga, p.nama_pemesan, p.status_pesanan, p.nomor_meja
    FROM split_bill sb
    JOIN pemesanan p ON p.id_pemesanan = sb.id_pemesanan
    WHERE sb.token = ?
");
$stmtSplit->bind_param("s", $token);
$stmtSplit->execute();
$split = $stmtSplit->get_result()->fetch_assoc();
$stmtSplit->close();

if (!$split) {
    http_response_code(404);
    echo json_encode(['error' => 'split bill tidak ditemukan']);
    exit;
}
$id_split = (int)$split['id_split'];

if ($action === 'toggle') {
    $id_bayar = (int)($_POST['id_bayar'] ?? 0);
    $stmt = $conn->prepare("SELECT status_bayar FROM split_bill_bayar WHERE id_bayar = ? AND id_split = ?");
    $stmt->bind_param("ii", $id_bayar, $id_split);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row) {
        $baru = $row['status_bayar'] === 'Sudah' ? 'Belum' : 'Sudah';
        $stmtU = $conn->prepare("UPDATE split_bill_bayar SET status_bayar = ?, dibayar_at = IF(?='Sudah', NOW(), NULL) WHERE id_bayar = ? AND id_split = ?");
        $stmtU->bind_param("ssii", $baru, $baru, $id_bayar, $id_split);
        $stmtU->execute();
        $stmtU->close();
    }
} elseif ($action === 'rename') {
    $id_bayar = (int)($_POST['id_bayar'] ?? 0);
    $nama_baru = trim($_POST['nama'] ?? '');
    if ($nama_baru !== '' && mb_strlen($nama_baru) <= 60) {
        $stmtU = $conn->prepare("UPDATE split_bill_bayar SET nama_peserta = ? WHERE id_bayar = ? AND id_split = ?");
        $stmtU->bind_param("sii", $nama_baru, $id_bayar, $id_split);
        $stmtU->execute();
        $stmtU->close();
    }
}

// Selalu balikin snapshot lengkap (dipakai juga buat polling real-time).
$stmtPeserta = $conn->prepare("SELECT id_bayar, nama_peserta, status_bayar, dibayar_at FROM split_bill_bayar WHERE id_split = ? ORDER BY id_bayar ASC");
$stmtPeserta->bind_param("i", $id_split);
$stmtPeserta->execute();
$peserta = $stmtPeserta->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtPeserta->close();

$sudah = count(array_filter($peserta, fn($p) => $p['status_bayar'] === 'Sudah'));

echo json_encode([
    'kode_pesanan'      => $split['kode_pesanan'],
    'nama_pemesan'      => $split['nama_pemesan'],
    'nomor_meja'        => $split['nomor_meja'],
    'status_pesanan'    => $split['status_pesanan'],
    'total_harga'       => (float)$split['total_harga'],
    'jumlah_orang'      => (int)$split['jumlah_orang'],
    'nominal_per_orang' => (float)$split['nominal_per_orang'],
    'sudah_bayar'       => $sudah,
    'peserta'           => $peserta,
]);
