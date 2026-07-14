<?php
/**
 * member_helper.php
 * Menghubungkan akun login (tabel `users`, session) dengan data member
 * loyalti (tabel `member`) supaya poin & data member selalu sinkron
 * dengan database, bukan angka statis di halaman.
 *
 * ATURAN MEMBER:
 * User TIDAK langsung jadi member begitu daftar akun / login. Member baru
 * didaftarkan otomatis setelah akun tersebut menyentuh minimal
 * MEMBER_MIN_VISITS transaksi (gabungan booking yang dikonfirmasi/selesai
 * + pesanan online yang tidak dibatalkan). Admin tetap bisa menambahkan
 * member manual kapan saja lewat panel admin (untuk pelanggan walk-in).
 * $member berisi seluruh kolom tabel `member` untuk user yang sedang
 * login, ATAU null kalau belum layak/belum jadi member. Kalau null,
 * pakai get_visit_count() untuk tahu progres menuju member.
 */

// Minimal transaksi (booking + pemesanan) sebelum otomatis jadi member.
define('MEMBER_MIN_VISITS', 5);

/**
 * Hitung total "kedatangan" akun ini: booking yang berstatus
 * Dikonfirmasi/Selesai, ditambah pemesanan online yang tidak dibatalkan.
 * Dihitung berdasarkan id_user (akun login), bukan tebak nama/no HP.
 */
function get_visit_count(mysqli $conn, int $id_user): int {
    $totalBooking = 0;
    $stmtB = $conn->prepare(
        "SELECT COUNT(*) AS t FROM booking
         WHERE id_user = ? AND status IN ('Dikonfirmasi','Selesai')"
    );
    $stmtB->bind_param("i", $id_user);
    $stmtB->execute();
    $rowB = $stmtB->get_result()->fetch_assoc();
    $stmtB->close();
    $totalBooking = (int) ($rowB['t'] ?? 0);

    $stmtP = $conn->prepare(
        "SELECT COUNT(*) AS t FROM pemesanan
         WHERE id_user = ? AND status_pesanan != 'Dibatalkan'"
    );
    $stmtP->bind_param("i", $id_user);
    $stmtP->execute();
    $rowP = $stmtP->get_result()->fetch_assoc();
    $stmtP->close();
    $totalPemesanan = (int) ($rowP['t'] ?? 0);

    return $totalBooking + $totalPemesanan;
}

/**
 * Ambil baris member untuk user yang sedang login KALAU sudah jadi
 * member (baik lewat pendaftaran otomatis sebelumnya, atau ditambahkan
 * manual oleh admin). Kalau belum pernah jadi member DAN belum menyentuh
 * MEMBER_MIN_VISITS transaksi, otomatis daftarkan sebagai member baru.
 * Kalau belum jadi member DAN belum cukup transaksi, kembalikan null
 * (halaman pemanggil wajib menangani kondisi ini, lihat member/member.php).
 */
function get_current_member(mysqli $conn) {
    if (!isset($_SESSION['username'])) {
        return null;
    }

    $username = $_SESSION['username'];
    $email    = $_SESSION['email'] ?? null;
    $id_user  = $_SESSION['user_id'] ?? null;

    // Kalau session lama belum menyimpan email/id, ambil dari tabel users.
    if (!$email || !$id_user) {
        $stmtU = $conn->prepare("SELECT id, email FROM users WHERE username = ? LIMIT 1");
        $stmtU->bind_param("s", $username);
        $stmtU->execute();
        $rowU = $stmtU->get_result()->fetch_assoc();
        $stmtU->close();
        if ($rowU) {
            if (!empty($rowU['email'])) {
                $email = $rowU['email'];
                $_SESSION['email'] = $email;
            }
            if (!empty($rowU['id'])) {
                $id_user = (int) $rowU['id'];
                $_SESSION['user_id'] = $id_user;
            }
        }
    }

    // 1) Cara paling akurat: cocokkan lewat id_user.
    if ($id_user) {
        $stmt = $conn->prepare("SELECT * FROM member WHERE id_user = ? LIMIT 1");
        $stmt->bind_param("i", $id_user);
        $stmt->execute();
        $member = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($member) {
            return $member;
        }
    }

    // 2) Fallback lama: cocokkan lewat email, lalu nama (untuk member yang
    //    sempat terdaftar sebelum kolom id_user ada). Kalau ketemu, tautkan
    //    id_user-nya sekalian supaya request berikutnya lebih cepat & akurat.
    $member = null;

    if ($email) {
        $stmt = $conn->prepare("SELECT * FROM member WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $member = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    if (!$member) {
        $stmt = $conn->prepare("SELECT * FROM member WHERE nama = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $member = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    if ($member) {
        if ($id_user && empty($member['id_user'])) {
            $stmtLink = $conn->prepare("UPDATE member SET id_user = ? WHERE id_member = ?");
            $stmtLink->bind_param("ii", $id_user, $member['id_member']);
            $stmtLink->execute();
            $stmtLink->close();
            $member['id_user'] = $id_user;
        }
        return $member;
    }

    // 3) Belum pernah jadi member sama sekali. Tanpa id_user, riwayat
    //    transaksi tidak bisa dihitung, jadi anggap belum layak.
    if (!$id_user) {
        return null;
    }

    $jumlahTransaksi = get_visit_count($conn, $id_user);
    if ($jumlahTransaksi < MEMBER_MIN_VISITS) {
        // Belum menyentuh syarat minimal -> BELUM jadi member.
        return null;
    }

    // Sudah menyentuh syarat -> daftarkan otomatis sebagai member baru,
    // poin mulai dari 0, langsung ditautkan ke id_user.
    $emailToSave = $email ?? '';
    $stmt = $conn->prepare(
        "INSERT INTO member (id_user, nama, email, no_hp, alamat, poin) VALUES (?, ?, ?, '', '', 0)"
    );
    $stmt->bind_param("iss", $id_user, $username, $emailToSave);
    $stmt->execute();
    $newId = $stmt->insert_id;
    $stmt->close();

    $stmt2 = $conn->prepare("SELECT * FROM member WHERE id_member = ?");
    $stmt2->bind_param("i", $newId);
    $stmt2->execute();
    $member = $stmt2->get_result()->fetch_assoc();
    $stmt2->close();

    return $member;
}

/**
 * Hitung tier/level member berdasarkan poin yang SEBENARNYA ada di DB.
 * Batas tier disesuaikan dengan reward yang sudah ada di halaman member:
 * 100 = Diskon 5%, 200 = Gratis Kopi, 250 = Gratis Croissant, 500 = Gratis Cake.
 */
function get_member_tier(int $poin): array {
    $tiers = [
        ['name' => 'Bronze',   'min' => 0],
        ['name' => 'Silver',   'min' => 100],
        ['name' => 'Gold',     'min' => 250],
        ['name' => 'Platinum', 'min' => 500],
    ];

    $current = $tiers[0];
    $next    = null;

    foreach ($tiers as $i => $t) {
        if ($poin >= $t['min']) {
            $current = $t;
            $next    = $tiers[$i + 1] ?? null;
        }
    }

    if ($next) {
        $sisa = max(0, $next['min'] - $poin);
        $pct  = (int) round((($poin - $current['min']) / ($next['min'] - $current['min'])) * 100);
    } else {
        $sisa = 0;
        $pct  = 100;
    }

    return [
        'name'      => $current['name'],
        'next_name' => $next['name'] ?? null,
        'next_poin' => $next['min'] ?? null,
        'sisa'      => $sisa,
        'pct'       => max(0, min(100, $pct)),
    ];
}

/**
 * Total belanja di keranjang (session) saat ini, dipakai untuk memvalidasi
 * syarat minimal belanja sebuah promo sebelum promo boleh diklaim.
 */
function get_cart_total(mysqli $conn): float {
    if (empty($_SESSION['keranjang']) || !is_array($_SESSION['keranjang'])) {
        return 0;
    }

    $total = 0;
    foreach ($_SESSION['keranjang'] as $id_produk => $jumlah) {
        $id_produk = (int) $id_produk;
        $jumlah    = (int) $jumlah;
        if ($jumlah <= 0) continue;

        $stmt = $conn->prepare("SELECT harga FROM produk WHERE id_produk = ?");
        $stmt->bind_param("i", $id_produk);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row) {
            $total += ((float) $row['harga']) * $jumlah;
        }
    }

    return $total;
}
