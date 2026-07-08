<?php
/**
 * gamifikasi_helper.php
 * -----------------------------------------------------------------------
 * Kumpulan fungsi untuk fitur gamifikasi member YOLAZCAKE:
 *   - Kado Poin   : transfer poin antar member + notifikasi penerima
 *   - Streak      : checkin harian berturut-turut
 *   - Badge       : lencana yang dibuka otomatis dari streak
 *   - Notifikasi  : notifikasi in-app sederhana untuk member
 *
 * Butuh tabel dari database/migration_gamifikasi.sql sudah diimport.
 * Selalu require_once bareng config/koneksi.php & config/member_helper.php.
 */

// Daftar badge streak yang bisa diraih member. Urut dari syarat terkecil.
const GAMIF_BADGE_LIST = [
    ['kode' => 'streak_3',  'hari' => 3,  'nama' => 'Pelanggan Setia',  'icon' => 'flame',   'desc' => '3 hari checkin berturut-turut'],
    ['kode' => 'streak_7',  'hari' => 7,  'nama' => 'Sahabat YOLAZCAKE', 'icon' => 'zap',      'desc' => '7 hari checkin berturut-turut'],
    ['kode' => 'streak_14', 'hari' => 14, 'nama' => 'Penggemar Berat',   'icon' => 'award',    'desc' => '14 hari checkin berturut-turut'],
    ['kode' => 'streak_30', 'hari' => 30, 'nama' => 'Legenda Cafe',      'icon' => 'crown',    'desc' => '30 hari checkin berturut-turut'],
];

/* =========================================================================
 * NOTIFIKASI
 * ========================================================================= */

/** Kirim satu notifikasi in-app ke member. */
function gamif_kirim_notifikasi(mysqli $conn, int $id_member, string $tipe, string $judul, string $pesan, ?string $link = null): void
{
    $stmt = $conn->prepare(
        "INSERT INTO notifikasi_member (id_member, tipe, judul, pesan, link) VALUES (?,?,?,?,?)"
    );
    $stmt->bind_param("issss", $id_member, $tipe, $judul, $pesan, $link);
    $stmt->execute();
    $stmt->close();
}

/** Jumlah notifikasi yang belum dibaca milik member. */
function gamif_jumlah_notif_belum_dibaca(mysqli $conn, int $id_member): int
{
    $stmt = $conn->prepare("SELECT COUNT(*) AS t FROM notifikasi_member WHERE id_member=? AND is_read=0");
    $stmt->bind_param("i", $id_member);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['t'] ?? 0);
}

/** Daftar notifikasi terbaru milik member (default 10). */
function gamif_daftar_notifikasi(mysqli $conn, int $id_member, int $limit = 10): array
{
    $limit = max(1, min(50, $limit));
    $stmt = $conn->prepare("SELECT * FROM notifikasi_member WHERE id_member=? ORDER BY created_at DESC LIMIT $limit");
    $stmt->bind_param("i", $id_member);
    $stmt->execute();
    $res = $stmt->get_result();
    $out = [];
    while ($row = $res->fetch_assoc()) {
        $out[] = $row;
    }
    $stmt->close();
    return $out;
}

/** Tandai semua notifikasi member sebagai sudah dibaca. */
function gamif_tandai_semua_dibaca(mysqli $conn, int $id_member): void
{
    $stmt = $conn->prepare("UPDATE notifikasi_member SET is_read=1 WHERE id_member=? AND is_read=0");
    $stmt->bind_param("i", $id_member);
    $stmt->execute();
    $stmt->close();
}

/* =========================================================================
 * KADO POIN
 * ========================================================================= */

/**
 * Cari member tujuan kado poin berdasarkan kata kunci (username / email / no hp),
 * tidak termasuk diri sendiri. Dipakai untuk pencarian di form kado poin.
 */
function gamif_cari_member_tujuan(mysqli $conn, string $keyword, int $id_member_pengirim): array
{
    $like = '%' . $keyword . '%';
    $stmt = $conn->prepare(
        "SELECT m.id_member, m.nama, m.email, m.no_hp, u.username
         FROM member m
         LEFT JOIN users u ON u.id = m.id_user
         WHERE m.id_member != ?
           AND (u.username LIKE ? OR m.email LIKE ? OR m.no_hp LIKE ? OR m.nama LIKE ?)
         ORDER BY m.nama LIMIT 8"
    );
    $stmt->bind_param("issss", $id_member_pengirim, $like, $like, $like, $like);
    $stmt->execute();
    $res = $stmt->get_result();
    $out = [];
    while ($row = $res->fetch_assoc()) {
        $out[] = $row;
    }
    $stmt->close();
    return $out;
}

/**
 * Proses transfer poin dari satu member ke member lain (kado poin).
 * Mengembalikan ['ok' => bool, 'pesan' => string].
 */
function gamif_transfer_poin(mysqli $conn, array $pengirim, int $id_penerima, int $poin, string $pesan_kado): array
{
    $id_pengirim = (int) $pengirim['id_member'];
    $poin_pengirim = (int) $pengirim['poin'];

    if ($poin <= 0) {
        return ['ok' => false, 'pesan' => 'Jumlah poin kado harus lebih dari 0.'];
    }
    if ($id_penerima === $id_pengirim) {
        return ['ok' => false, 'pesan' => 'Tidak bisa mengirim kado poin ke diri sendiri.'];
    }
    if ($poin > $poin_pengirim) {
        return ['ok' => false, 'pesan' => 'Poin kamu tidak cukup untuk mengirim kado sebesar itu.'];
    }

    $stmtCek = $conn->prepare("SELECT id_member, nama FROM member WHERE id_member=?");
    $stmtCek->bind_param("i", $id_penerima);
    $stmtCek->execute();
    $penerima = $stmtCek->get_result()->fetch_assoc();
    $stmtCek->close();
    if (!$penerima) {
        return ['ok' => false, 'pesan' => 'Member tujuan tidak ditemukan.'];
    }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("UPDATE member SET poin = poin - ? WHERE id_member=? AND poin >= ?");
        $stmt->bind_param("iii", $poin, $id_pengirim, $poin);
        $stmt->execute();
        if ($stmt->affected_rows < 1) {
            throw new Exception('Poin tidak cukup saat diproses.');
        }
        $stmt->close();

        $stmt = $conn->prepare("UPDATE member SET poin = poin + ? WHERE id_member=?");
        $stmt->bind_param("ii", $poin, $id_penerima);
        $stmt->execute();
        $stmt->close();

        $ketKeluar = 'Kado poin untuk ' . $penerima['nama'] . ($pesan_kado !== '' ? (' — "' . $pesan_kado . '"') : '');
        $jenisKeluar = 'Keluar';
        $stmt = $conn->prepare("INSERT INTO riwayat_poin (id_member, jenis, poin, keterangan) VALUES (?,?,?,?)");
        $stmt->bind_param("isis", $id_pengirim, $jenisKeluar, $poin, $ketKeluar);
        $stmt->execute();
        $stmt->close();

        $ketMasuk = 'Kado poin dari ' . $pengirim['nama'] . ($pesan_kado !== '' ? (' — "' . $pesan_kado . '"') : '');
        $jenisMasuk = 'Masuk';
        $stmt = $conn->prepare("INSERT INTO riwayat_poin (id_member, jenis, poin, keterangan) VALUES (?,?,?,?)");
        $stmt->bind_param("isis", $id_penerima, $jenisMasuk, $poin, $ketMasuk);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO poin_transfer (id_pengirim, id_penerima, poin, pesan) VALUES (?,?,?,?)");
        $stmt->bind_param("iiis", $id_pengirim, $id_penerima, $poin, $pesan_kado);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        return ['ok' => false, 'pesan' => 'Gagal mengirim kado poin, coba lagi ya.'];
    }

    $judulNotif = "Kado Poin Diterima! 🎁";
    $pesanNotif = 'Kamu dapet kado ' . $poin . ' poin dari ' . $pengirim['nama'] . '!' .
                  ($pesan_kado !== '' ? (' Pesannya: "' . $pesan_kado . '"') : '');
    try {
        gamif_kirim_notifikasi($conn, $id_penerima, 'kado_poin', $judulNotif, $pesanNotif, '../member/member.php');
    } catch (Throwable $e) {
        // Poin tetap sudah pindah walau notifikasi gagal (mis. tabel notifikasi_member
        // belum diimport dari migration_gamifikasi.sql). Jangan gagalkan transfernya.
    }

    return ['ok' => true, 'pesan' => 'Kado ' . $poin . ' poin berhasil dikirim ke ' . $penerima['nama'] . '!'];
}

/* =========================================================================
 * STREAK & BADGE
 * ========================================================================= */

/** Cek apakah member sudah checkin hari ini. */
function gamif_sudah_checkin_hari_ini(mysqli $conn, int $id_member): bool
{
    $stmt = $conn->prepare("SELECT id_checkin FROM member_checkin WHERE id_member=? AND tanggal=CURDATE()");
    $stmt->bind_param("i", $id_member);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (bool) $row;
}

/**
 * Proses checkin harian: update streak, catat log, dan buka badge baru kalau syarat tercapai.
 * Mengembalikan ['ok'=>bool,'pesan'=>string,'streak'=>int,'badge_baru'=>array]
 */
function gamif_lakukan_checkin(mysqli $conn, array $member): array
{
    $id_member = (int) $member['id_member'];

    if (gamif_sudah_checkin_hari_ini($conn, $id_member)) {
        return ['ok' => false, 'pesan' => 'Kamu sudah checkin hari ini. Balik lagi besok ya!', 'streak' => (int) $member['streak_saat_ini'], 'badge_baru' => []];
    }

    $checkinTerakhir = $member['checkin_terakhir'] ?? null;
    $streakBaru = 1;
    if ($checkinTerakhir) {
        $selisihHari = (int) ((strtotime(date('Y-m-d')) - strtotime($checkinTerakhir)) / 86400);
        if ($selisihHari === 1) {
            // Checkin kemarin -> lanjutkan streak.
            $streakBaru = (int) $member['streak_saat_ini'] + 1;
        } elseif ($selisihHari === 0) {
            $streakBaru = (int) $member['streak_saat_ini'];
        }
        // Selisih > 1 hari => streak putus, mulai dari 1 lagi (nilai default di atas).
    }

    $streakTerbaik = max($streakBaru, (int) $member['streak_terbaik']);

    $stmt = $conn->prepare("INSERT INTO member_checkin (id_member, tanggal) VALUES (?, CURDATE())");
    $stmt->bind_param("i", $id_member);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare(
        "UPDATE member SET streak_saat_ini=?, streak_terbaik=?, checkin_terakhir=CURDATE() WHERE id_member=?"
    );
    $stmt->bind_param("iii", $streakBaru, $streakTerbaik, $id_member);
    $stmt->execute();
    $stmt->close();

    $badgeBaru = gamif_cek_dan_beri_badge($conn, $id_member, $streakBaru);

    return [
        'ok' => true,
        'pesan' => 'Checkin berhasil! Streak kamu sekarang ' . $streakBaru . ' hari.',
        'streak' => $streakBaru,
        'badge_baru' => $badgeBaru,
    ];
}

/** Cek badge streak yang baru terbuka, simpan ke member_badge, kirim notifikasi. Return list badge baru. */
function gamif_cek_dan_beri_badge(mysqli $conn, int $id_member, int $streak): array
{
    $badgeBaru = [];
    foreach (GAMIF_BADGE_LIST as $b) {
        if ($streak < $b['hari']) continue;

        try {
            $stmt = $conn->prepare("SELECT id_badge_diraih FROM member_badge WHERE id_member=? AND kode_badge=?");
            $stmt->bind_param("is", $id_member, $b['kode']);
            $stmt->execute();
            $ada = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($ada) continue;

            $stmt = $conn->prepare("INSERT INTO member_badge (id_member, kode_badge) VALUES (?, ?)");
            $stmt->bind_param("is", $id_member, $b['kode']);
            $stmt->execute();
            $stmt->close();

            gamif_kirim_notifikasi(
                $conn,
                $id_member,
                'badge_baru',
                "Badge Baru: " . $b['nama'] . "! 🏆",
                'Selamat! Kamu meraih badge "' . $b['nama'] . '" setelah checkin ' . $b['hari'] . ' hari berturut-turut.',
                '../member/streak.php'
            );

            $badgeBaru[] = $b;
        } catch (Throwable $e) {
            // Tabel member_badge / notifikasi_member kemungkinan belum diimport.
            // Lewati badge ini tanpa menggagalkan proses checkin.
            continue;
        }
    }
    return $badgeBaru;
}

/** Ambil ringkasan streak + badge (untuk dashboard member & halaman streak). */
function gamif_get_streak_info(mysqli $conn, array $member): array
{
    $id_member = (int) $member['id_member'];

    $stmt = $conn->prepare("SELECT kode_badge FROM member_badge WHERE id_member=?");
    $stmt->bind_param("i", $id_member);
    $stmt->execute();
    $res = $stmt->get_result();
    $milik = [];
    while ($row = $res->fetch_assoc()) {
        $milik[$row['kode_badge']] = true;
    }
    $stmt->close();

    $badges = [];
    foreach (GAMIF_BADGE_LIST as $b) {
        $b['unlocked'] = isset($milik[$b['kode']]);
        $badges[] = $b;
    }

    // Riwayat checkin 30 hari terakhir, untuk kalender titik-titik.
    $stmt = $conn->prepare(
        "SELECT tanggal FROM member_checkin WHERE id_member=? AND tanggal >= (CURDATE() - INTERVAL 29 DAY) ORDER BY tanggal"
    );
    $stmt->bind_param("i", $id_member);
    $stmt->execute();
    $res = $stmt->get_result();
    $tanggalCheckin = [];
    while ($row = $res->fetch_assoc()) {
        $tanggalCheckin[$row['tanggal']] = true;
    }
    $stmt->close();

    return [
        'streak_saat_ini' => (int) $member['streak_saat_ini'],
        'streak_terbaik'  => (int) $member['streak_terbaik'],
        'sudah_checkin'   => gamif_sudah_checkin_hari_ini($conn, $id_member),
        'badges'          => $badges,
        'tanggal_checkin' => $tanggalCheckin,
    ];
}
