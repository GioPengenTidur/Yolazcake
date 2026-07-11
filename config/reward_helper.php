<?php
/**
 * reward_helper.php
 * -----------------------------------------------------------------------
 * Fungsi untuk fitur klaim reward milestone poin member YOLAZCAKE.
 * Reward ditukar pakai poin (poin member dikurangi sesuai syarat reward).
 * Butuh tabel reward_klaim dari database/migration_reward_dan_bonus_badge.sql.
 * Selalu require_once bareng config/koneksi.php, member_helper.php &
 * gamifikasi_helper.php (butuh gamif_kirim_notifikasi).
 */

const REWARD_MILESTONES = [
    ['poin' => 100, 'icon' => 'gift',      'nama' => 'Diskon 5%'],
    ['poin' => 200, 'icon' => 'coffee',    'nama' => 'Gratis Kopi'],
    ['poin' => 250, 'icon' => 'croissant', 'nama' => 'Gratis Croissant'],
    ['poin' => 500, 'icon' => 'cake',      'nama' => 'Gratis Cake'],
];

/** Bikin kode redeem unik buat ditunjukkin ke kasir saat ambil reward. */
function reward_buat_kode(): string
{
    return 'YLZ-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
}

/**
 * Proses klaim reward: cek poin cukup, kurangi poin, catat riwayat poin +
 * klaim, kirim notifikasi in-app. Poin dicek ulang di query UPDATE (bukan
 * cuma dari variabel session) supaya aman dari race condition / klaim dobel.
 * Return ['ok'=>bool, 'pesan'=>string, 'kode'=>string|null].
 */
function reward_klaim_proses(mysqli $conn, array $member, int $poin_reward, string $nama_reward): array
{
    $id_member     = (int) $member['id_member'];
    $poin_saat_ini = (int) $member['poin'];

    if ($poin_saat_ini < $poin_reward) {
        return ['ok' => false, 'pesan' => 'Poin kamu belum cukup untuk reward ini.', 'kode' => null];
    }

    $kode = reward_buat_kode();

    $conn->begin_transaction();
    try {
        // Kurangi poin, dijaga kondisi poin >= syarat biar gak minus kalau
        // ada klaim/transfer lain yang keburu jalan duluan.
        $stmt = $conn->prepare("UPDATE member SET poin = poin - ? WHERE id_member=? AND poin >= ?");
        $stmt->bind_param("iii", $poin_reward, $id_member, $poin_reward);
        $stmt->execute();
        if ($stmt->affected_rows < 1) {
            throw new Exception('Poin tidak cukup saat diproses.');
        }
        $stmt->close();

        $stmt = $conn->prepare(
            "INSERT INTO reward_klaim (id_member, nama_reward, poin_terpakai, kode_redeem) VALUES (?,?,?,?)"
        );
        $stmt->bind_param("isis", $id_member, $nama_reward, $poin_reward, $kode);
        $stmt->execute();
        $stmt->close();

        $ket   = 'Klaim reward "' . $nama_reward . '" (kode ' . $kode . ')';
        $jenis = 'Keluar';
        $stmt  = $conn->prepare("INSERT INTO riwayat_poin (id_member, jenis, poin, keterangan) VALUES (?,?,?,?)");
        $stmt->bind_param("isis", $id_member, $jenis, $poin_reward, $ket);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        return ['ok' => false, 'pesan' => 'Gagal klaim reward, coba lagi ya.', 'kode' => null];
    }

    try {
        gamif_kirim_notifikasi(
            $conn,
            $id_member,
            'klaim_reward',
            'Reward Diklaim! 🎁',
            'Kamu berhasil klaim "' . $nama_reward . '". Tunjukkan kode ' . $kode . ' ke kasir buat ambil rewardnya.',
            '../member/klaim_reward.php'
        );
    } catch (Throwable $e) {
        // Notifikasi opsional -- klaim reward tetap sah walau notif gagal terkirim.
    }

    return ['ok' => true, 'pesan' => 'Reward "' . $nama_reward . '" berhasil diklaim!', 'kode' => $kode];
}

/** Riwayat klaim reward milik member, terbaru duluan. */
function reward_riwayat_klaim(mysqli $conn, int $id_member, int $limit = 20): array
{
    $limit = max(1, min(50, $limit));
    $stmt  = $conn->prepare("SELECT * FROM reward_klaim WHERE id_member=? ORDER BY created_at DESC LIMIT $limit");
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
