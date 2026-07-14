<?php
/**
 * export_excel.php
 * Export "Laporan Penjualan" (isi tabel data_pemesanan.php) ke file Excel
 * (.xlsx) asli -- dibangun manual lewat config/xlsx_helper.php, tanpa
 * dependency composer, konsisten dengan gaya vendoring PHPMailer di
 * project ini.
 *
 * Filter opsional lewat query string (dikirim dari form filter di
 * data_pemesanan.php): tanggal_mulai, tanggal_akhir, status.
 */
session_start();
require_once __DIR__.'/../config/staff_guard.php';
require_staff_login();
require_once __DIR__.'/../config/xlsx_helper.php';
include '../config/koneksi.php';

$tanggal_mulai = trim($_GET['tanggal_mulai'] ?? '');
$tanggal_akhir = trim($_GET['tanggal_akhir'] ?? '');
$status        = trim($_GET['status'] ?? '');

$where  = [];
$types  = '';
$params = [];

if ($tanggal_mulai !== '') {
    $where[]  = 'DATE(tanggal) >= ?';
    $types   .= 's';
    $params[] = $tanggal_mulai;
}
if ($tanggal_akhir !== '') {
    $where[]  = 'DATE(tanggal) <= ?';
    $types   .= 's';
    $params[] = $tanggal_akhir;
}
$statusValid = ['Menunggu', 'Diproses', 'Siap Diambil', 'Selesai', 'Dibatalkan'];
if ($status !== '' && in_array($status, $statusValid, true)) {
    $where[]  = 'status_pesanan = ?';
    $types   .= 's';
    $params[] = $status;
}

$sql = "SELECT * FROM pemesanan";
if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY tanggal ASC";

$stmt = $conn->prepare($sql);
if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$semua  = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── Susun keterangan periode utk judul laporan ──
if ($tanggal_mulai !== '' && $tanggal_akhir !== '') {
    $periode = 'Periode: '.date('d M Y', strtotime($tanggal_mulai)).' – '.date('d M Y', strtotime($tanggal_akhir));
} elseif ($tanggal_mulai !== '') {
    $periode = 'Periode: Sejak '.date('d M Y', strtotime($tanggal_mulai));
} elseif ($tanggal_akhir !== '') {
    $periode = 'Periode: Sampai '.date('d M Y', strtotime($tanggal_akhir));
} else {
    $periode = 'Periode: Semua Data';
}
if ($status !== '' && in_array($status, $statusValid, true)) {
    $periode .= '  |  Status: '.$status;
}

$totalPesanan = count($semua);
$totalRevenue = array_sum(array_column($semua, 'total_harga'));
$totalDiskon  = array_sum(array_column($semua, 'diskon_nominal'));

$xlsx = new SimpleXlsxWriter();

// Lebar kolom: No, Kode, Nama, No HP, Tanggal, Total, Diskon, Metode, Bayar, Status
$xlsx->setColWidth(1, 5);
$xlsx->setColWidth(2, 18);
$xlsx->setColWidth(3, 24);
$xlsx->setColWidth(4, 16);
$xlsx->setColWidth(5, 18);
$xlsx->setColWidth(6, 16);
$xlsx->setColWidth(7, 14);
$xlsx->setColWidth(8, 14);
$xlsx->setColWidth(9, 14);
$xlsx->setColWidth(10, 16);

// Baris 1: judul band ungu-emas
$xlsx->addRow([
    ['value' => 'LAPORAN PENJUALAN — YOLAZCAKE SINTANG', 'style' => SimpleXlsxWriter::STYLE_TITLE],
    ['value' => '', 'style' => SimpleXlsxWriter::STYLE_TITLE],
    ['value' => '', 'style' => SimpleXlsxWriter::STYLE_TITLE],
    ['value' => '', 'style' => SimpleXlsxWriter::STYLE_TITLE],
    ['value' => '', 'style' => SimpleXlsxWriter::STYLE_TITLE],
    ['value' => '', 'style' => SimpleXlsxWriter::STYLE_TITLE],
    ['value' => '', 'style' => SimpleXlsxWriter::STYLE_TITLE],
    ['value' => '', 'style' => SimpleXlsxWriter::STYLE_TITLE],
    ['value' => '', 'style' => SimpleXlsxWriter::STYLE_TITLE],
    ['value' => '', 'style' => SimpleXlsxWriter::STYLE_TITLE],
]);
$xlsx->mergeCells('A1:J1');

// Baris 2: subjudul periode + waktu export
$xlsx->addRow([
    ['value' => $periode.'   •   Diekspor: '.date('d M Y, H:i').' WIB', 'style' => SimpleXlsxWriter::STYLE_SUBTITLE],
]);
$xlsx->mergeCells('A2:J2');

// Baris 3: kosong
$xlsx->addRow([['value' => '']]);

// Baris 4: header tabel
$headers = ['No', 'Kode Pesanan', 'Nama Pemesan', 'No HP', 'Tanggal', 'Total Harga', 'Diskon', 'Metode Bayar', 'Status Bayar', 'Status Pesanan'];
$headerRow = [];
foreach ($headers as $h) {
    $headerRow[] = ['value' => $h, 'style' => SimpleXlsxWriter::STYLE_HEADER];
}
$xlsx->addRow($headerRow);

// Baris data
$no = 1;
foreach ($semua as $row) {
    $isAlt      = ($no % 2 === 0);
    $cellStyle  = $isAlt ? SimpleXlsxWriter::STYLE_CELL_ALT : SimpleXlsxWriter::STYLE_CELL;
    $moneyStyle = $isAlt ? SimpleXlsxWriter::STYLE_CURRENCY_ALT : SimpleXlsxWriter::STYLE_CURRENCY;

    $xlsx->addRow([
        ['value' => $no, 'style' => $cellStyle],
        ['value' => (string)$row['kode_pesanan'], 'style' => $cellStyle, 'type' => 'text'],
        ['value' => (string)$row['nama_pemesan'], 'style' => $cellStyle, 'type' => 'text'],
        ['value' => (string)($row['no_hp'] ?? '-'), 'style' => $cellStyle, 'type' => 'text'],
        ['value' => date('d/m/Y H:i', strtotime($row['tanggal'])), 'style' => $cellStyle],
        ['value' => (float)$row['total_harga'], 'style' => $moneyStyle],
        ['value' => (float)($row['diskon_nominal'] ?? 0), 'style' => $moneyStyle],
        ['value' => (string)$row['metode_pembayaran'], 'style' => $cellStyle],
        ['value' => (string)$row['status_pembayaran'], 'style' => $cellStyle],
        ['value' => (string)$row['status_pesanan'], 'style' => $cellStyle],
    ]);
    $no++;
}

// Baris kosong pemisah + ringkasan total
$xlsx->addRow([['value' => '']]);

$xlsx->addRow([
    ['value' => ''], ['value' => ''], ['value' => ''],
    ['value' => ''], ['value' => 'Total Pesanan', 'style' => SimpleXlsxWriter::STYLE_TOTAL_LABEL],
    ['value' => $totalPesanan, 'style' => SimpleXlsxWriter::STYLE_TOTAL_VALUE],
]);

$xlsx->addRow([
    ['value' => ''], ['value' => ''], ['value' => ''],
    ['value' => ''], ['value' => 'Total Diskon', 'style' => SimpleXlsxWriter::STYLE_TOTAL_LABEL],
    ['value' => $totalDiskon, 'style' => SimpleXlsxWriter::STYLE_TOTAL_VALUE],
]);

$xlsx->addRow([
    ['value' => ''], ['value' => ''], ['value' => ''],
    ['value' => ''], ['value' => 'Total Pendapatan', 'style' => SimpleXlsxWriter::STYLE_TOTAL_LABEL],
    ['value' => $totalRevenue, 'style' => SimpleXlsxWriter::STYLE_TOTAL_VALUE],
]);

$tmpPath = tempnam(sys_get_temp_dir(), 'yolazcake_xlsx_');
$xlsx->save($tmpPath);

$filename = 'Laporan_Penjualan_YOLAZCAKE_'.date('Ymd_His').'.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Content-Length: '.filesize($tmpPath));
header('Cache-Control: max-age=0');
readfile($tmpPath);
unlink($tmpPath);
exit;
