<?php
/**
 * xlsx_helper.php
 * -----------------------------------------------------------------------
 * Generator file .xlsx (Excel) TANPA dependency composer / PhpSpreadsheet
 * -- cukup pakai ZipArchive bawaan PHP untuk merakit paket OOXML secara
 * manual (sheet XML, styles XML, dsb). Ditulis khusus untuk kebutuhan
 * "Export Laporan Penjualan" di data_pemesanan.php, jadi API-nya sengaja
 * sederhana (bukan library spreadsheet umum), supaya mudah dirawat.
 *
 * Style yang didukung: header bar (emas), judul band (ungu tua), baris
 * data selang-seling, format Rupiah untuk kolom uang, dan baris ringkasan
 * total di bagian bawah.
 */

class SimpleXlsxWriter
{
    /** @var array<int,array> baris-baris sheet, tiap baris = array of cell defs */
    private array $rows = [];
    /** @var array<int,float> lebar kolom (index 1-based -> width) */
    private array $colWidths = [];
    private array $merges = [];

    // Style ids tetap (fixed), didefinisikan langsung di styles.xml
    const STYLE_TITLE      = 1; // band ungu, teks emas besar, bold
    const STYLE_SUBTITLE   = 2; // teks abu italic
    const STYLE_HEADER     = 3; // header tabel: fill emas, teks putih bold, border
    const STYLE_CELL       = 4; // sel data biasa, border tipis
    const STYLE_CELL_ALT   = 5; // sel data baris genap (tint ungu muda), border tipis
    const STYLE_CURRENCY   = 6; // format Rupiah, border tipis
    const STYLE_CURRENCY_ALT = 7; // format Rupiah, tint, border tipis
    const STYLE_TOTAL_LABEL = 8; // label ringkasan, bold
    const STYLE_TOTAL_VALUE = 9; // nilai ringkasan, bold + currency, fill emas muda

    /**
     * Tambah satu baris. $cells = list asosiatif ['value'=>..,'style'=>STYLE_*]
     */
    public function addRow(array $cells): int
    {
        $this->rows[] = $cells;
        return count($this->rows); // nomor baris (1-based) yang baru saja ditambahkan
    }

    public function setColWidth(int $colIndex1Based, float $width): void
    {
        $this->colWidths[$colIndex1Based] = $width;
    }

    public function mergeCells(string $range): void
    {
        $this->merges[] = $range;
    }

    private static function colLetter(int $index1Based): string
    {
        $letter = '';
        while ($index1Based > 0) {
            $mod = ($index1Based - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $index1Based = intdiv($index1Based - 1, 26);
        }
        return $letter;
    }

    private static function xmlEscape(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function buildSheetXml(): string
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';

        if (!empty($this->colWidths)) {
            $xml .= '<cols>';
            foreach ($this->colWidths as $idx => $w) {
                $xml .= '<col min="'.$idx.'" max="'.$idx.'" width="'.$w.'" customWidth="1"/>';
            }
            $xml .= '</cols>';
        }

        $xml .= '<sheetData>';
        foreach ($this->rows as $rowIndex0 => $cells) {
            $rowNum = $rowIndex0 + 1;
            $xml .= '<row r="'.$rowNum.'">';
            foreach ($cells as $colIndex0 => $cell) {
                $colNum = $colIndex0 + 1;
                $ref    = self::colLetter($colNum).$rowNum;
                $style  = isset($cell['style']) ? ' s="'.(int)$cell['style'].'"' : '';
                $value  = $cell['value'] ?? '';
                $forceText = ($cell['type'] ?? null) === 'text';

                if (!$forceText && is_numeric($value) && $value !== '') {
                    $xml .= '<c r="'.$ref.'"'.$style.'><v>'.(0 + $value).'</v></c>';
                } else {
                    $xml .= '<c r="'.$ref.'"'.$style.' t="inlineStr"><is><t xml:space="preserve">'.self::xmlEscape((string)$value).'</t></is></c>';
                }
            }
            $xml .= '</row>';
        }
        $xml .= '</sheetData>';

        if (!empty($this->merges)) {
            $xml .= '<mergeCells count="'.count($this->merges).'">';
            foreach ($this->merges as $range) {
                $xml .= '<mergeCell ref="'.$range.'"/>';
            }
            $xml .= '</mergeCells>';
        }

        $xml .= '</worksheet>';
        return $xml;
    }

    private function stylesXml(): string
    {
        // Palet warna disamakan dengan tema YOLAZCAKE: emas #D4AF37, ungu tua #2D1560
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <numFmts count="1">
    <numFmt numFmtId="164" formatCode="&quot;Rp&quot; #,##0"/>
  </numFmts>
  <fonts count="7">
    <font><sz val="11"/><name val="Calibri"/></font>
    <font><sz val="16"/><b/><color rgb="FFD4AF37"/><name val="Calibri"/></font>
    <font><sz val="10"/><i/><color rgb="FF6E6E6E"/><name val="Calibri"/></font>
    <font><sz val="10"/><b/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>
    <font><sz val="10"/><name val="Calibri"/></font>
    <font><sz val="10"/><b/><color rgb="FF1E0E3A"/><name val="Calibri"/></font>
    <font><sz val="11"/><b/><color rgb="FF1E0E3A"/><name val="Calibri"/></font>
  </fonts>
  <fills count="6">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF2D1560"/><bgColor rgb="FF2D1560"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFD4AF37"/><bgColor rgb="FFD4AF37"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFF3EEFA"/><bgColor rgb="FFF3EEFA"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFFCEEB5"/><bgColor rgb="FFFCEEB5"/></patternFill></fill>
  </fills>
  <borders count="2">
    <border><left/><right/><top/><bottom/><diagonal/></border>
    <border>
      <left style="thin"><color rgb="FFD9D2E9"/></left>
      <right style="thin"><color rgb="FFD9D2E9"/></right>
      <top style="thin"><color rgb="FFD9D2E9"/></top>
      <bottom style="thin"><color rgb="FFD9D2E9"/></bottom>
    </border>
  </borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="10">
    <xf numFmtId="0"   fontId="0" fillId="0" borderId="0" xfId="0"/>                                                        <!-- 0 default -->
    <xf numFmtId="0"   fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment vertical="center" horizontal="left"/></xf> <!-- 1 STYLE_TITLE -->
    <xf numFmtId="0"   fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>                                          <!-- 2 STYLE_SUBTITLE -->
    <xf numFmtId="0"   fontId="3" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" horizontal="center" wrapText="1"/></xf> <!-- 3 STYLE_HEADER -->
    <xf numFmtId="0"   fontId="4" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf> <!-- 4 STYLE_CELL -->
    <xf numFmtId="0"   fontId="4" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf> <!-- 5 STYLE_CELL_ALT -->
    <xf numFmtId="164" fontId="4" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" horizontal="right"/></xf> <!-- 6 STYLE_CURRENCY -->
    <xf numFmtId="164" fontId="4" fillId="4" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" horizontal="right"/></xf> <!-- 7 STYLE_CURRENCY_ALT -->
    <xf numFmtId="0"   fontId="5" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf> <!-- 8 STYLE_TOTAL_LABEL -->
    <xf numFmtId="164" fontId="6" fillId="5" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf> <!-- 9 STYLE_TOTAL_VALUE -->
  </cellXfs>
  <cellStyles count="1">
    <cellStyle name="Normal" xfId="0" builtinId="0"/>
  </cellStyles>
</styleSheet>
XML;
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
    }

    private function rootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="Laporan Penjualan" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>';
    }

    private function workbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    /**
     * Rakit file .xlsx dan simpan ke path tujuan (bisa langsung path php://output
     * kalau mau streaming, tapi ZipArchive butuh file fisik -- jadi kita simpan
     * ke temp file dulu lalu baca ulang isinya di pemanggil).
     */
    public function save(string $path): bool
    {
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return false;
        }
        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->rootRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelsXml());
        $zip->addFromString('xl/styles.xml', $this->stylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->buildSheetXml());
        $zip->close();
        return true;
    }
}
