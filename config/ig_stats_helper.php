<?php
/**
 * ig_stats_helper.php
 * 
 * Statistik Instagram (followers/following/posts) di-update MANUAL oleh
 * admin lewat instagram/ig_stats.php (tidak tarik data live dari IG --
 * supaya tidak pernah error/down karena API/rate-limit/perubahan pihak
 * ketiga). Halaman publik (contact.php) tinggal baca 1 baris ini, jadi
 * begitu admin ubah angkanya, halaman publik ikut berubah otomatis
 * tanpa perlu edit kode.
 */

function ambil_ig_stats(mysqli $conn): array {
    $default = ['id' => 1, 'followers' => 0, 'following' => 0, 'posts' => 0, 'updated_by' => null, 'updated_at' => null];

    $res = $conn->query("SELECT * FROM ig_stats WHERE id = 1 LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) {
        return $row;
    }
    return $default;
}

/**
 * Format angka gaya Instagram: 950 -> "950", 1200 -> "1,2 rb", 25000 -> "25 rb",
 * 1500000 -> "1,5 jt". Dibulatkan 1 angka desimal, koma dihilangkan kalau .0.
 */
function format_angka_ig(int $n): string {
    if ($n < 1000) {
        return (string) $n;
    }
    if ($n < 1000000) {
        $val = $n / 1000;
        $suffix = 'rb';
    } else {
        $val = $n / 1000000;
        $suffix = 'jt';
    }
    $formatted = rtrim(rtrim(number_format($val, 1, ',', ''), '0'), ',');
    return $formatted . ' ' . $suffix;
}
