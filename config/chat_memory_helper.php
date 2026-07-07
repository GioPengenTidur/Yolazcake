<?php
/**
 * chat_memory_helper.php
 * -----------------------------------------------------------------------
 * Fungsi-fungsi pendukung untuk fitur "Riwayat Obrolan" + "Obrolan Baru"
 * dan "Memori Yola AI" (ingatan jangka panjang) di Pusat Bantuan.
 *
 * Cara identifikasi pemilik obrolan:
 *   - User yang SUDAH LOGIN -> dikenali lewat id_user ($_SESSION['user_id']).
 *   - User yang BELUM LOGIN (tamu) -> dikenali lewat cookie `yolaz_guest`
 *     (token acak, umur 1 tahun) supaya tetap punya riwayat & memori
 *     sendiri di browser yang sama walau belum bikin akun.
 *
 * Tabel yang dipakai (lihat database/migration_chat_riwayat_memori.sql):
 *   chat_sesi, chat_pesan, chat_memori
 */

/**
 * Ambil identitas pemilik obrolan saat ini: [id_user|null, guest_token|null].
 * Kalau belum login dan belum punya cookie guest, cookie baru otomatis
 * dibuat (umur 1 tahun) supaya riwayat & memorinya tersimpan.
 */
function chat_ambil_identitas(): array {
    $id_user = $_SESSION['user_id'] ?? null;
    $id_user = $id_user ? (int) $id_user : null;

    $guest_token = null;
    if (!$id_user) {
        if (!empty($_COOKIE['yolaz_guest'])) {
            $guest_token = preg_replace('/[^a-zA-Z0-9]/', '', $_COOKIE['yolaz_guest']);
        }
        if (!$guest_token) {
            $guest_token = bin2hex(random_bytes(20));
            // httponly=false karena tidak dibaca lewat JS, cukup dikirim otomatis oleh browser
            setcookie('yolaz_guest', $guest_token, time() + 60 * 60 * 24 * 365, '/');
            $_COOKIE['yolaz_guest'] = $guest_token;
        }
    }

    return [$id_user, $guest_token];
}

/** Bagian WHERE + parameter buat cocokkan baris milik id_user ATAU guest_token ini. */
function chat_kondisi_pemilik(?int $id_user, ?string $guest_token): array {
    if ($id_user) {
        return ['id_user = ?', 'i', $id_user];
    }
    return ['guest_token = ?', 's', $guest_token];
}

/** Buat sesi obrolan baru, judul diambil dari potongan pesan pertama user. */
function chat_buat_sesi(mysqli $conn, ?int $id_user, ?string $guest_token, string $pesanPertama): int {
    $judul = mb_substr(trim($pesanPertama), 0, 60);
    if ($judul === '') $judul = 'Obrolan baru';
    if (mb_strlen(trim($pesanPertama)) > 60) $judul .= '…';

    $stmt = $conn->prepare(
        "INSERT INTO chat_sesi (id_user, guest_token, judul) VALUES (?, ?, ?)"
    );
    $stmt->bind_param("iss", $id_user, $guest_token, $judul);
    $stmt->execute();
    $id = (int) $stmt->insert_id;
    $stmt->close();
    return $id;
}

/** Pastikan id_sesi ini memang milik id_user/guest_token yang sedang chat. */
function chat_sesi_valid(mysqli $conn, int $id_sesi, ?int $id_user, ?string $guest_token): bool {
    [$kondisi, $tipe, $nilai] = chat_kondisi_pemilik($id_user, $guest_token);
    $stmt = $conn->prepare("SELECT id_sesi FROM chat_sesi WHERE id_sesi = ? AND {$kondisi} LIMIT 1");
    $stmt->bind_param("i{$tipe}", $id_sesi, $nilai);
    $stmt->execute();
    $ok = (bool) $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $ok;
}

/** Simpan satu pesan (user/bot) ke sebuah sesi, sekalian update jam terakhir sesi. */
function chat_simpan_pesan(mysqli $conn, int $id_sesi, string $peran, string $isi): void {
    $stmt = $conn->prepare("INSERT INTO chat_pesan (id_sesi, peran, isi) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $id_sesi, $peran, $isi);
    $stmt->execute();
    $stmt->close();

    $stmt2 = $conn->prepare("UPDATE chat_sesi SET diperbarui_pada = NOW() WHERE id_sesi = ?");
    $stmt2->bind_param("i", $id_sesi);
    $stmt2->execute();
    $stmt2->close();
}

/** Daftar sesi obrolan milik user/tamu ini, terbaru duluan. */
function chat_daftar_sesi(mysqli $conn, ?int $id_user, ?string $guest_token, int $limit = 30): array {
    [$kondisi, $tipe, $nilai] = chat_kondisi_pemilik($id_user, $guest_token);
    $stmt = $conn->prepare(
        "SELECT id_sesi, judul, diperbarui_pada FROM chat_sesi
         WHERE {$kondisi} ORDER BY diperbarui_pada DESC LIMIT {$limit}"
    );
    $stmt->bind_param($tipe, $nilai);
    $stmt->execute();
    $res = $stmt->get_result();
    $out = [];
    while ($row = $res->fetch_assoc()) $out[] = $row;
    $stmt->close();
    return $out;
}

/** Semua pesan dalam satu sesi, urut dari yang paling lama. */
function chat_ambil_pesan_sesi(mysqli $conn, int $id_sesi): array {
    $stmt = $conn->prepare("SELECT peran, isi FROM chat_pesan WHERE id_sesi = ? ORDER BY id_pesan ASC");
    $stmt->bind_param("i", $id_sesi);
    $stmt->execute();
    $res = $stmt->get_result();
    $out = [];
    while ($row = $res->fetch_assoc()) $out[] = $row;
    $stmt->close();
    return $out;
}

/** Hapus satu sesi obrolan (beserta pesan-pesannya) milik user/tamu ini. */
function chat_hapus_sesi(mysqli $conn, int $id_sesi, ?int $id_user, ?string $guest_token): bool {
    if (!chat_sesi_valid($conn, $id_sesi, $id_user, $guest_token)) return false;
    $stmt = $conn->prepare("DELETE FROM chat_pesan WHERE id_sesi = ?");
    $stmt->bind_param("i", $id_sesi);
    $stmt->execute();
    $stmt->close();
    $stmt2 = $conn->prepare("DELETE FROM chat_sesi WHERE id_sesi = ?");
    $stmt2->bind_param("i", $id_sesi);
    $stmt2->execute();
    $stmt2->close();
    return true;
}

/** Ambil ringkasan memori jangka panjang user/tamu ini (string kosong kalau belum ada). */
function chat_ambil_memori(mysqli $conn, ?int $id_user, ?string $guest_token): string {
    [$kondisi, $tipe, $nilai] = chat_kondisi_pemilik($id_user, $guest_token);
    $stmt = $conn->prepare("SELECT ringkasan FROM chat_memori WHERE {$kondisi} LIMIT 1");
    $stmt->bind_param($tipe, $nilai);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row['ringkasan'] ?? '';
}

/** Simpan/perbarui ringkasan memori (upsert manual, karena kolomnya nullable ganda). */
function chat_simpan_memori(mysqli $conn, ?int $id_user, ?string $guest_token, string $ringkasan): void {
    $ringkasan = mb_substr(trim($ringkasan), 0, 2000); // batasi biar prompt tidak membengkak
    if ($ringkasan === '') return;

    [$kondisi, $tipe, $nilai] = chat_kondisi_pemilik($id_user, $guest_token);
    $stmt = $conn->prepare("SELECT id_memori FROM chat_memori WHERE {$kondisi} LIMIT 1");
    $stmt->bind_param($tipe, $nilai);
    $stmt->execute();
    $ada = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($ada) {
        $stmtU = $conn->prepare("UPDATE chat_memori SET ringkasan = ? WHERE id_memori = ?");
        $stmtU->bind_param("si", $ringkasan, $ada['id_memori']);
        $stmtU->execute();
        $stmtU->close();
    } else {
        $stmtI = $conn->prepare("INSERT INTO chat_memori (id_user, guest_token, ringkasan) VALUES (?, ?, ?)");
        $stmtI->bind_param("iss", $id_user, $guest_token, $ringkasan);
        $stmtI->execute();
        $stmtI->close();
    }
}
