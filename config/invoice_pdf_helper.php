<?php
/**
 * invoice_pdf_helper.php
 * -----------------------------------------------------------------------
 * Generator PDF invoice/struk pemesanan, dibangun di atas FPDF (vendor/fpdf,
 * di-vendor manual persis seperti PHPMailer di project ini -- tanpa
 * composer). Tampilannya disesuaikan seaslinya mungkin dengan tema
 * YOLAZCAKE: band ungu tua + emas, tabel item, kotak total.
 *
 * Catatan: FPDF hanya mendukung encoding Latin-1 (Windows-1252) untuk font
 * bawaan (Helvetica/Times/Courier), jadi semua teks dari database (UTF-8)
 * dikonversi dulu lewat yolaz_pdf_txt().
 */

require_once __DIR__.'/../vendor/fpdf/fpdf.php';

/** Konversi string UTF-8 (dari MySQL) ke Latin-1 supaya aman dicetak FPDF. */
function yolaz_pdf_txt(?string $s): string
{
    $s = (string)($s ?? '');
    $converted = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $s);
    return $converted !== false ? $converted : mb_convert_encoding($s, 'ISO-8859-1', 'UTF-8');
}

class YolazInvoicePDF extends FPDF
{
    // Palet warna brand YOLAZCAKE
    const GOLD        = [212, 175, 55];
    const GOLD_DARK    = [184, 134, 11];
    const PURPLE_DARK = [30, 14, 58];
    const PURPLE      = [45, 21, 96];
    const PINK        = [238, 42, 123];
    const GREEN       = [46, 160, 110];
    const GREY        = [110, 110, 110];
    const LIGHT_TINT  = [243, 238, 250];

    public string $kodePesanan = '';

    public function Header(): void
    {
        // Band ungu tua penuh lebar di atas
        $this->SetFillColor(...self::PURPLE_DARK);
        $this->Rect(0, 0, 210, 34, 'F');

        // Aksen garis emas tipis di bawah band
        $this->SetFillColor(...self::GOLD);
        $this->Rect(0, 34, 210, 1.2, 'F');

        // Nama brand
        $this->SetXY(12, 8);
        $this->SetTextColor(...self::GOLD);
        $this->SetFont('Times', 'B', 22);
        $this->Cell(0, 10, 'YOLAZCAKE', 0, 1);

        $this->SetXY(12, 19);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Helvetica', '', 9);
        $this->Cell(0, 5, yolaz_pdf_txt('Cafe . Bakery . Boutique - Sintang, Kalimantan Barat'), 0, 1);

        // Label invoice + kode pesanan, rata kanan
        $this->SetXY(120, 9);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Helvetica', 'B', 13);
        $this->Cell(78, 6, 'INVOICE / STRUK PESANAN', 0, 1, 'R');

        $this->SetXY(120, 17);
        $this->SetTextColor(...self::GOLD);
        $this->SetFont('Helvetica', 'B', 12);
        $this->Cell(78, 6, yolaz_pdf_txt($this->kodePesanan), 0, 1, 'R');

        $this->SetY(42);
    }

    public function Footer(): void
    {
        $this->SetY(-22);
        $this->SetDrawColor(...self::GOLD);
        $this->SetLineWidth(0.3);
        $this->Line(12, $this->GetY(), 198, $this->GetY());

        $this->SetY(-18);
        $this->SetTextColor(...self::GREY);
        $this->SetFont('Helvetica', '', 8);
        $this->Cell(0, 4, yolaz_pdf_txt('Terima kasih telah berbelanja di YOLAZCAKE Sintang! Struk ini sah tanpa tanda tangan.'), 0, 1, 'C');
        $this->Cell(0, 4, yolaz_pdf_txt('Jl. Lintas Melawi, Ladang, Sintang, Kalimantan Barat  -  WA: 0815-7815-7888'), 0, 1, 'C');
        $this->SetFont('Helvetica', '', 7);
        $this->Cell(0, 4, 'Halaman '.$this->PageNo().'/{nb}', 0, 0, 'C');
    }

    /** Judul kecil section, mis. "INFORMASI PEMESAN" */
    public function sectionLabel(string $label): void
    {
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->SetTextColor(...self::PURPLE);
        $this->Cell(0, 5, yolaz_pdf_txt(mb_strtoupper($label)), 0, 1);
        $this->SetDrawColor(220, 210, 235);
        $this->SetLineWidth(0.2);
        $this->Line($this->GetX(), $this->GetY(), $this->GetX() + 85, $this->GetY());
        $this->Ln(2);
    }

    /** Satu baris label:value di dalam kolom info, lebar kolom 85mm total */
    public function infoRow(string $label, string $value): void
    {
        $this->SetFont('Helvetica', '', 9);
        $this->SetTextColor(90, 90, 90);
        $this->Cell(28, 5.5, yolaz_pdf_txt($label), 0, 0);
        $this->SetFont('Helvetica', 'B', 9);
        $this->SetTextColor(30, 30, 30);
        $this->Cell(57, 5.5, yolaz_pdf_txt($value), 0, 1);
    }

    public function rupiah(float $n): string
    {
        return 'Rp ' . number_format($n, 0, ',', '.');
    }

    /** Header tabel item */
    public function itemsTableHeader(): void
    {
        $this->SetFillColor(...self::GOLD);
        $this->SetTextColor(30, 14, 58);
        $this->SetFont('Helvetica', 'B', 9);
        $this->Cell(86, 8, '  Produk', 1, 0, 'L', true);
        $this->Cell(20, 8, 'Qty', 1, 0, 'C', true);
        $this->Cell(35, 8, 'Harga Satuan', 1, 0, 'R', true);
        $this->Cell(45, 8, 'Subtotal  ', 1, 1, 'R', true);
    }

    public function itemsRow(string $nama, int $qty, float $harga, float $subtotal, bool $alt): void
    {
        $this->ensureRowFits(7);
        $this->SetFont('Helvetica', '', 9);
        $this->SetTextColor(40, 40, 40);
        $this->SetFillColor(...self::LIGHT_TINT);
        $fill = $alt;
        $namaSafe = $this->fitText(yolaz_pdf_txt($nama), 82);
        $this->Cell(86, 7, '  '.$namaSafe, 1, 0, 'L', $fill);
        $this->Cell(20, 7, (string)$qty, 1, 0, 'C', $fill);
        $this->Cell(35, 7, $this->rupiah($harga), 1, 0, 'R', $fill);
        $this->Cell(45, 7, $this->rupiah($subtotal).'  ', 1, 1, 'R', $fill);
    }

    /**
     * Kalau baris berikutnya bakal kepotong margin bawah, FPDF (auto page
     * break) baru pecah halaman TEPAT saat Cell() dipanggil -- jadi header
     * kolom tabel perlu dicetak ulang manual di halaman baru, karena FPDF
     * tidak tahu baris ini bagian dari tabel yang sama.
     */
    private function ensureRowFits(float $rowHeight): void
    {
        if ($this->GetY() + $rowHeight > $this->PageBreakTrigger) {
            $this->AddPage();
            $this->SetX(12);
            $this->itemsTableHeader();
        }
    }

    /** Potong teks dengan "..." kalau lebih lebar dari $maxWidth (mm) pada font aktif. */
    private function fitText(string $text, float $maxWidth): string
    {
        if ($this->GetStringWidth($text) <= $maxWidth) {
            return $text;
        }
        while ($text !== '' && $this->GetStringWidth($text.'...') > $maxWidth) {
            $text = mb_substr($text, 0, -1);
        }
        return $text.'...';
    }

    /** Baris ringkasan angka di kotak total (kanan bawah tabel) */
    public function totalLine(string $label, string $value, bool $bold = false, bool $highlight = false): void
    {
        $this->SetX(120);
        if ($highlight) {
            $this->SetFillColor(...self::GOLD);
            $this->SetTextColor(...self::PURPLE_DARK);
        } else {
            $this->SetFillColor(255, 255, 255);
            $this->SetTextColor(40, 40, 40);
        }
        $this->SetFont('Helvetica', $bold ? 'B' : '', $highlight ? 11 : 9.5);
        $this->Cell(46, $highlight ? 9 : 7, yolaz_pdf_txt($label), $highlight ? 0 : 0, 0, 'L', $highlight);
        $this->Cell(40, $highlight ? 9 : 7, yolaz_pdf_txt($value), $highlight ? 0 : 0, 1, 'R', $highlight);
    }

    /** Badge status kecil berwarna, dipakai untuk status bayar/pesanan */
    public function statusBadge(string $text, array $rgb): void
    {
        $w = $this->GetStringWidth(yolaz_pdf_txt($text)) + 6;
        $this->SetFillColor(...$rgb);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Helvetica', 'B', 8);
        $this->Cell($w, 5.5, yolaz_pdf_txt($text), 0, 0, 'C', true);
    }
}
