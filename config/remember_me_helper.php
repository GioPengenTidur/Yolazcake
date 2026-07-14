<?php

define('REMEMBER_ME_COOKIE', 'yolazcake_remember');
define('REMEMBER_ME_DAYS', 30);

/** Buat token baru, simpan hash-nya di DB, dan set cookie di browser. */
function remember_me_create(mysqli $conn, int $userId): void {
    $selector  = bin2hex(random_bytes(9));   // disimpan polos untuk lookup
    $validator = bin2hex(random_bytes(33));  // hanya hash-nya yang disimpan
    $hashed    = hash('sha256', $validator);
    $expiresAt = date('Y-m-d H:i:s', time() + (REMEMBER_ME_DAYS * 86400));

    $stmt = $conn->prepare("INSERT INTO remember_tokens (user_id, selector, hashed_validator, expires_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $userId, $selector, $hashed, $expiresAt);
    $stmt->execute();
    $stmt->close();

    setcookie(
        REMEMBER_ME_COOKIE,
        $selector . ':' . $validator,
        [
            'expires'  => time() + (REMEMBER_ME_DAYS * 86400),
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            // 'secure' => true, // aktifkan kalau sudah pakai HTTPS
        ]
    );
}

/**
 * Cek cookie remember-me di browser. Kalau valid, TIDAK langsung login-kan
 * user (tidak set $_SESSION['username']) — cuma memvalidasi & merotasi
 * token, lalu mengembalikan data user-nya. Pemanggil (index.php) yang
 * memutuskan mau langsung masuk atau munculkan konfirmasi "Lanjutkan
 * sebagai ... / Ganti Akun" dulu.
 *
 * Return: array user (id, username, role, email, foto_profil) kalau valid,
 * atau null kalau tidak ada cookie / cookie tidak valid.
 */
function remember_me_check(mysqli $conn): ?array {
    if (empty($_COOKIE[REMEMBER_ME_COOKIE])) {
        return null;
    }

    $parts = explode(':', $_COOKIE[REMEMBER_ME_COOKIE], 2);
    if (count($parts) !== 2) {
        remember_me_clear_cookie();
        return null;
    }

    [$selector, $validator] = $parts;

    $stmt = $conn->prepare("SELECT rt.id, rt.user_id, rt.hashed_validator, rt.expires_at,
                                    u.username, u.role, u.email, u.foto_profil
                             FROM remember_tokens rt
                             JOIN users u ON u.id = rt.user_id
                             WHERE rt.selector = ? LIMIT 1");
    $stmt->bind_param("s", $selector);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        remember_me_clear_cookie();
        return null;
    }

    // Token kedaluwarsa -> bersihkan dan anggap gagal.
    if (strtotime($row['expires_at']) < time()) {
        remember_me_delete_by_id($conn, $row['id']);
        remember_me_clear_cookie();
        return null;
    }

    // Validator tidak cocok -> kemungkinan cookie dicuri/dipalsukan.
    // Hapus SEMUA token milik user ini sebagai langkah pengamanan.
    if (!hash_equals($row['hashed_validator'], hash('sha256', $validator))) {
        remember_me_delete_all_for_user($conn, (int) $row['user_id']);
        remember_me_clear_cookie();
        return null;
    }

    // Valid -> rotasi token (token lama langsung tidak berlaku begitu dipakai).
    remember_me_delete_by_id($conn, $row['id']);
    remember_me_create($conn, (int) $row['user_id']);

    return [
        'id'          => (int) $row['user_id'],
        'username'    => $row['username'],
        'role'        => $row['role'] ?? 'pengunjung',
        'email'       => $row['email'],
        'foto_profil' => $row['foto_profil'],
    ];
}

/** Hapus cookie & semua token milik user yang sedang login (dipanggil saat logout). */
function remember_me_forget(mysqli $conn): void {
    if (!empty($_COOKIE[REMEMBER_ME_COOKIE])) {
        $parts = explode(':', $_COOKIE[REMEMBER_ME_COOKIE], 2);
        if (count($parts) === 2) {
            $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE selector = ?");
            $stmt->bind_param("s", $parts[0]);
            $stmt->execute();
            $stmt->close();
        }
    }
    remember_me_clear_cookie();
}

function remember_me_delete_by_id(mysqli $conn, int $id): void {
    $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

function remember_me_delete_all_for_user(mysqli $conn, int $userId): void {
    $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
}

function remember_me_clear_cookie(): void {
    setcookie(REMEMBER_ME_COOKIE, '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    unset($_COOKIE[REMEMBER_ME_COOKIE]);
}
