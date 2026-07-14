<?php
/**
 * ulasan_helper.php
 * -----------------------------------------------------------------------
 * Kumpulan fungsi untuk fitur Rating/Ulasan:
 *  - Ulasan Produk   (tabel `ulasan_produk`)  -> dipakai di menu.php,
 *    lihat_produk.php (publik) & produk/detail_produk.php (admin).
 *  - Ulasan Tempat   (tabel `ulasan_tempat`)  -> rating umum "enak &
 *    nyamannya tempat + makanan", dipakai di popup setelah checkout
 *    dan halaman ulasan/tempat.php.
 */
function get_ringkasan_rating_produk(mysqli $conn, int $id_produk): array {
    $stmt = $conn->prepare(
        "SELECT COALESCE(AVG(rating),0) AS avg_rating, COUNT(*) AS jumlah
         FROM ulasan_produk WHERE id_produk = ?"
    );
    $stmt->bind_param("i", $id_produk);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return [
        'avg'    => round((float)($row['avg_rating'] ?? 0), 1),
        'jumlah' => (int)($row['jumlah'] ?? 0),
    ];
}

/**
 * Ringkasan rating untuk banyak produk sekaligus (dipakai di menu.php
 * supaya tidak query satu-satu per kartu produk).
 * Return: [id_produk => ['avg' => float, 'jumlah' => int]]
 */
function get_ringkasan_rating_produk_batch(mysqli $conn, array $id_produk_list): array {
    $hasil = [];
    if (empty($id_produk_list)) return $hasil;

    $placeholders = implode(',', array_fill(0, count($id_produk_list), '?'));
    $types        = str_repeat('i', count($id_produk_list));

    $stmt = $conn->prepare(
        "SELECT id_produk, AVG(rating) AS avg_rating, COUNT(*) AS jumlah
         FROM ulasan_produk WHERE id_produk IN ($placeholders)
         GROUP BY id_produk"
    );
    $stmt->bind_param($types, ...$id_produk_list);
    $stmt->execute();
    $q = $stmt->get_result();
    while ($r = $q->fetch_assoc()) {
        $hasil[(int)$r['id_produk']] = [
            'avg'    => round((float)$r['avg_rating'], 1),
            'jumlah' => (int)$r['jumlah'],
        ];
    }
    $stmt->close();
    return $hasil;
}

/**
 * Ambil daftar ulasan produk (terbaru dulu).
 */
function get_ulasan_produk(mysqli $conn, int $id_produk): array {
    $stmt = $conn->prepare(
        "SELECT * FROM ulasan_produk WHERE id_produk = ? ORDER BY created_at DESC"
    );
    $stmt->bind_param("i", $id_produk);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

/**
 * Simpan ulasan produk baru. Return: ['ok'=>bool, 'pesan'=>string]
 */
function simpan_ulasan_produk(mysqli $conn, int $id_produk, ?int $id_user, string $nama, int $rating, string $komentar): array {
    if ($rating < 1 || $rating > 5) {
        return ['ok' => false, 'pesan' => 'Rating harus antara 1 sampai 5.'];
    }
    $nama = trim($nama) !== '' ? trim($nama) : 'Pelanggan';
    $komentar = trim($komentar);

    $cekProduk = $conn->prepare("SELECT id_produk FROM produk WHERE id_produk = ?");
    $cekProduk->bind_param("i", $id_produk);
    $cekProduk->execute();
    if (!$cekProduk->get_result()->fetch_assoc()) {
        $cekProduk->close();
        return ['ok' => false, 'pesan' => 'Produk tidak ditemukan.'];
    }
    $cekProduk->close();

    $stmt = $conn->prepare(
        "INSERT INTO ulasan_produk (id_produk, id_user, nama_reviewer, rating, komentar)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("iisis", $id_produk, $id_user, $nama, $rating, $komentar);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok
        ? ['ok' => true, 'pesan' => 'Terima kasih atas ulasan Anda!']
        : ['ok' => false, 'pesan' => 'Gagal menyimpan ulasan, silakan coba lagi.'];
}

/**
 * Ringkasan rating tempat (rata-rata makanan & tempat + jumlah ulasan).
 */
function get_ringkasan_rating_tempat(mysqli $conn): array {
    $row = $conn->query(
        "SELECT COALESCE(AVG(rating_makanan),0) AS avg_makanan,
                COALESCE(AVG(rating_tempat),0)  AS avg_tempat,
                COUNT(*) AS jumlah
         FROM ulasan_tempat"
    )->fetch_assoc();
    return [
        'avg_makanan' => round((float)$row['avg_makanan'], 1),
        'avg_tempat'  => round((float)$row['avg_tempat'], 1),
        'jumlah'      => (int)$row['jumlah'],
    ];
}

/**
 * Ambil daftar ulasan tempat (terbaru dulu), dibatasi $limit (0 = semua).
 */
function get_ulasan_tempat(mysqli $conn, int $limit = 0): array {
    $sql = "SELECT * FROM ulasan_tempat ORDER BY created_at DESC";
    if ($limit > 0) $sql .= " LIMIT " . (int)$limit;
    $rows = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    return $rows;
}

/**
 * Simpan ulasan tempat baru (dipakai popup setelah checkout & halaman
 * ulasan/tempat.php). Return: ['ok'=>bool, 'pesan'=>string]
 */
function simpan_ulasan_tempat(mysqli $conn, ?int $id_user, ?int $id_pemesanan, string $nama, int $rating_makanan, int $rating_tempat, string $komentar): array {
    if ($rating_makanan < 1 || $rating_makanan > 5 || $rating_tempat < 1 || $rating_tempat > 5) {
        return ['ok' => false, 'pesan' => 'Rating harus antara 1 sampai 5.'];
    }
    $nama = trim($nama) !== '' ? trim($nama) : 'Pelanggan';
    $komentar = trim($komentar);

    $stmt = $conn->prepare(
        "INSERT INTO ulasan_tempat (id_user, id_pemesanan, nama_reviewer, rating_makanan, rating_tempat, komentar)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("iisiis", $id_user, $id_pemesanan, $nama, $rating_makanan, $rating_tempat, $komentar);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok
        ? ['ok' => true, 'pesan' => 'Terima kasih atas ulasan Anda!']
        : ['ok' => false, 'pesan' => 'Gagal menyimpan ulasan, silakan coba lagi.'];
}

/**
 * Render bintang statis (tampilan saja, bukan input) sebagai string HTML.
 * $rating boleh desimal (mis. 4.3) -> dibulatkan ke bawah untuk bintang penuh.
 */
function render_bintang(float $rating, string $size = '1em'): string {
    $penuh = (int) floor($rating + 0.25); // pembulatan ramah (>= .75 dibulatkan ke atas oleh caller jika perlu)
    $penuh = max(0, min(5, $penuh));
    $html = '<span style="font-size:'.$size.';letter-spacing:1px;">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= $penuh
            ? '<span style="color:#D4AF37;"><i data-lucide="star" class="lucide-ic lucide-fill"></i></span>'
            : '<span style="color:rgba(255,255,255,.25);"><i data-lucide="star" class="lucide-ic lucide-fill"></i></span>';
    }
    $html .= '</span>';
    return $html;
}

/**
 * Logika kapan popup "beri ulasan tempat & makanan" boleh ditampilkan
 * setelah pemesanan berhasil.
 *
 * Aturan:
 *  - Ditampilkan sekali per "siklus": begitu ditampilkan (baik user isi
 *    ataupun klik Batal), langsung ditandai supaya TIDAK muncul lagi
 *    untuk pesanan berikutnya di sesi login yang sama.
 *  - Reset otomatis kalau user logout lalu login lagi (session baru).
 *  - Reset juga otomatis kalau sudah lewat 24 jam sejak terakhir kali
 *    ditampilkan (meski belum logout).
 *
 * Dipanggil di halaman proses_pemesanan.php SETELAH session_start().
 */
function boleh_tampilkan_popup_ulasan_tempat(): bool {
    if (!isset($_SESSION['ulasan_popup_shown_at'])) {
        return true;
    }
    $selisih = time() - (int) $_SESSION['ulasan_popup_shown_at'];
    return $selisih >= 86400; // 24 jam
}

/**
 * Tandai popup sudah ditampilkan (dipanggil begitu popup akan dirender,
 * sebelum tahu user akan isi atau Batal).
 */
function tandai_popup_ulasan_tempat_tampil(): void {
    $_SESSION['ulasan_popup_shown_at'] = time();
}
