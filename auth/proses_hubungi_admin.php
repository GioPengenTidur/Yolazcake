<?php
session_start();

header('Content-Type: application/json');

function respond_json($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit();
}

require_once '../config/koneksi.php';

$nama             = trim($_POST['nama'] ?? '');
$username_terkait = trim($_POST['username_terkait'] ?? '');
$email            = trim($_POST['email'] ?? '');
$no_hp            = trim($_POST['no_hp'] ?? '');
$masalah          = trim($_POST['masalah'] ?? '');
$pesan            = trim($_POST['pesan'] ?? '');

$masalahOptions = [
    'Lupa Password'          => 'Lupa Password',
    'Tidak Bisa Login'       => 'Tidak Bisa Login',
    'Email/Username Salah'   => 'Email/Username Salah',
    'Lainnya'                => 'Lainnya (Masalah Akun Lain)',
];

if ($nama === '') {
    respond_json(false, 'Nama wajib diisi.');
}
if ($email === '' && $no_hp === '') {
    respond_json(false, 'Isi minimal salah satu: email atau nomor WhatsApp, supaya admin bisa menghubungi Anda kembali.');
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond_json(false, 'Format email tidak valid.');
}
if (!array_key_exists($masalah, $masalahOptions)) {
    respond_json(false, 'Pilih kategori masalah terlebih dahulu.');
}
if ($pesan === '') {
    respond_json(false, 'Pesan wajib diisi, jelaskan kendala akun Anda.');
}

$subjek   = $masalahOptions[$masalah];
$kategori = 'Bantuan Akun';

$stmt = $conn->prepare(
    "INSERT INTO kontak (nama, email, no_hp, subjek, kategori, username_terkait, pesan)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$emailVal = $email !== '' ? $email : null;
$noHpVal  = $no_hp !== '' ? $no_hp : null;
$userVal  = $username_terkait !== '' ? $username_terkait : null;
$stmt->bind_param("sssssss", $nama, $emailVal, $noHpVal, $subjek, $kategori, $userVal, $pesan);

if ($stmt->execute()) {
    respond_json(true, 'Pesan berhasil dikirim ke admin. Kami akan segera membantu Anda.');
} else {
    respond_json(false, 'Gagal mengirim pesan, silakan coba lagi.');
}
