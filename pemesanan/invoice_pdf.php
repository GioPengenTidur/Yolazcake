<?php
/**
 * invoice_pdf.php
 * Cetak invoice/struk pesanan sebagai PDF asli (bukan sekadar print-preview
 * browser), dipakai dari 2 tempat:
 *   1. Halaman sukses checkout pelanggan (proses_pemesanan.php) -- akses
 *      tanpa login, tapi WAJIB menyertakan ?kode=<kode_pesanan> yang cocok
 *      persis dengan punya pesanan itu, supaya orang lain tidak bisa
 *      mengunduh invoice pesanan orang lain hanya dengan menebak id.
 *   2. Halaman admin (data_pemesanan.php / detail_pemesanan.php) -- staff
 *      yang sudah login (admin/kasir) boleh cetak invoice pesanan mana pun
 *      tanpa perlu tahu kode_pesanan-nya.
 */
session_start();
include '../config/koneksi.php';
require_once '../config/invoice_pdf_helper.php';

$id_pemesanan = (int)($_GET['id'] ?? 0);
$kodeParam    = trim($_GET['kode'] ?? '');

if ($id_pemesanan <= 0) {
    http_response_code(400);
    die('Permintaan tidak valid.');
}

$stmt = $conn->prepare("SELECT * FROM pemesanan WHERE id_pemesanan = ?");
$stmt->bind_param("i", $id_pemesanan);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    http_response_code(404);
    die('Data pemesanan tidak ditemukan.');
}

// ── Otorisasi akses ──
$role      = $_SESSION['role'] ?? null;
$isStaff   = isset($_SESSION['username']) && in_array($role, ['admin', 'kasir'], true);
$kodeMatch = ($kodeParam !== '' && hash_equals((string)$data['kode_pesanan'], $kodeParam));

if (!$isStaff && !$kodeMatch) {
    http_response_code(403);
    die('Anda tidak berhak mengakses invoice ini.');
}

// ── Ambil item pesanan ──
$stmtItems = $conn->prepare(
    "SELECT dp.*, p.nama_produk
     FROM detail_pemesanan dp
     LEFT JOIN produk p ON p.id_produk = dp.id_produk
     WHERE dp.id_pemesanan = ?"
);
$stmtItems->bind_param("i", $id_pemesanan);
$stmtItems->execute();
$items = $stmtItems->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtItems->close();

// ── Susun PDF ──
$pdf = new YolazInvoicePDF();
$pdf->kodePesanan = (string)$data['kode_pesanan'];
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 26);
$pdf->AddPage();

// Dua kolom info: kiri = pemesan, kanan = pembayaran
$yStart = $pdf->GetY();

$pdf->SetXY(12, $yStart);
$pdf->sectionLabel('Informasi Pemesan');
$pdf->SetX(12);
$pdf->infoRow('Nama', (string)$data['nama_pemesan']);
$pdf->SetX(12);
$pdf->infoRow('No. HP', (string)($data['no_hp'] ?? '-'));
$pdf->SetX(12);
$pdf->infoRow('Meja', (string)($data['nomor_meja'] ?? '-'));
$pdf->SetX(12);
$pdf->infoRow('Tanggal', date('d M Y, H:i', strtotime($data['tanggal'])).' WIB');

$yAfterLeft = $pdf->GetY();

$pdf->SetXY(107, $yStart);
$pdf->sectionLabel('Informasi Pembayaran');
$pdf->SetX(107);
$pdf->infoRow('Metode', (string)$data['metode_pembayaran']);

$pdf->SetX(107);
$pdf->SetFont('Helvetica', '', 9);
$pdf->SetTextColor(90, 90, 90);
$pdf->Cell(28, 5.5, 'Status Bayar', 0, 0);
$statusBayarWarna = $data['status_pembayaran'] === 'Lunas' ? YolazInvoicePDF::GREEN : ($data['status_pembayaran'] === 'Gagal' ? [220, 70, 70] : [230, 160, 40]);
$pdf->statusBadge((string)$data['status_pembayaran'], $statusBayarWarna);
$pdf->Ln(7);

$pdf->SetX(107);
$pdf->SetFont('Helvetica', '', 9);
$pdf->SetTextColor(90, 90, 90);
$pdf->Cell(28, 5.5, 'Status Pesanan', 0, 0);
$statusPesananWarna = match ($data['status_pesanan']) {
    'Selesai'      => YolazInvoicePDF::GREEN,
    'Dibatalkan'   => [220, 70, 70],
    'Siap Diambil' => [140, 90, 220],
    'Diproses'     => [80, 130, 230],
    default        => [230, 160, 40],
};
$pdf->statusBadge((string)$data['status_pesanan'], $statusPesananWarna);
$pdf->Ln(7);

if (!empty($data['kode_promo'])) {
    $pdf->SetX(107);
    $pdf->infoRow('Kode Promo', (string)$data['kode_promo']);
}

$yAfterRight = $pdf->GetY();
$pdf->SetY(max($yAfterLeft, $yAfterRight) + 4);

// ── Tabel item ──
$pdf->SetX(12);
$pdf->itemsTableHeader();
$no = 0;
foreach ($items as $item) {
    $pdf->SetX(12);
    $harga = $item['jumlah'] > 0 ? ((float)$item['subtotal'] / (int)$item['jumlah']) : 0;
    $pdf->itemsRow(
        (string)($item['nama_produk'] ?? '(Produk telah dihapus)'),
        (int)$item['jumlah'],
        $harga,
        (float)$item['subtotal'],
        $no % 2 === 1
    );
    $no++;
}

$pdf->Ln(4);

// ── Kotak total ──
$subtotal = (float)$data['total_harga'] + (float)($data['diskon_nominal'] ?? 0);
if ((float)($data['diskon_nominal'] ?? 0) > 0) {
    $pdf->totalLine('Subtotal', $pdf->rupiah($subtotal));
    $pdf->totalLine('Diskon ('.($data['kode_promo'] ?? '').')', '-'.$pdf->rupiah((float)$data['diskon_nominal']));
}
$pdf->Ln(1);
$pdf->totalLine('TOTAL BAYAR', $pdf->rupiah((float)$data['total_harga']), true, true);

$filename = 'Invoice_'.$data['kode_pesanan'].'.pdf';
$pdf->Output('D', $filename);
exit;
