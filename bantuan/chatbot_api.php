<?php
/**
 * chatbot_api.php
 * -----------------------------------------------------------------------
 * Endpoint backend buat Pusat Bantuan (chatbot). Dipanggil lewat fetch()
 * dari bantuan/index.php. API key Gemini TIDAK PERNAH dikirim ke browser
 * — cuma dipakai di sini, di server.
 *
 * Request  (POST, JSON): { "message": "...", "history": [ {role, text}, ... ] }
 * Response (JSON):       { "reply": "..." }  atau  { "error": "..." }
 */

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/koneksi.php';
require_once '../config/gemini_config.php';

// Status login user yang lagi chat, dipakai buat personalisasi &
// ajakan login kalau dia keliatan mau pesan/booking.
$sudahLoginChat = isset($_SESSION['username']);
$namaUserChat   = $sudahLoginChat ? $_SESSION['username'] : null;

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = trim($input['message'] ?? '');
$history     = is_array($input['history'] ?? null) ? $input['history'] : [];

if ($userMessage === '') {
    echo json_encode(['error' => 'Pesan kosong.']);
    exit();
}

// Batas wajar biar tidak disalahgunakan buat spam token
if (mb_strlen($userMessage) > 800) {
    $userMessage = mb_substr($userMessage, 0, 800);
}
if (count($history) > 20) {
    $history = array_slice($history, -20); // simpan 20 giliran terakhir saja
}

/* ── AMBIL DATA ASLI CAFE2 DARI DATABASE (biar jawaban akurat, bukan ngarang) ── */

// Menu aktif per kategori (stok > 0 saja, biar tidak nawarin yang habis)
$menu_text = '';
$q = mysqli_query($conn, "
    SELECT k.nama_kategori, p.nama_produk, p.harga, p.deskripsi, p.stok
    FROM produk p
    LEFT JOIN kategori k ON k.id_kategori = p.id_kategori
    ORDER BY k.nama_kategori, p.nama_produk
");
$menu_by_kategori = [];
if ($q) {
    while ($row = mysqli_fetch_assoc($q)) {
        $kat = $row['nama_kategori'] ?? 'Lainnya';
        $status = $row['stok'] > 0 ? '' : ' (STOK HABIS)';
        $menu_by_kategori[$kat][] = '- ' . $row['nama_produk'] . ' — Rp' . number_format($row['harga'], 0, ',', '.') . $status;
    }
}
foreach ($menu_by_kategori as $kat => $items) {
    $menu_text .= "\n{$kat}:\n" . implode("\n", $items) . "\n";
}

// Promo yang lagi aktif (status Aktif dan dalam rentang tanggal)
$promo_text = '';
$q2 = mysqli_query($conn, "
    SELECT kode_promo, judul, deskripsi, diskon_persen, min_belanja, poin_bonus
    FROM promo
    WHERE status = 'Aktif'
      AND (tanggal_mulai IS NULL OR tanggal_mulai <= CURDATE())
      AND (tanggal_selesai IS NULL OR tanggal_selesai >= CURDATE())
");
if ($q2) {
    while ($row = mysqli_fetch_assoc($q2)) {
        $promo_text .= "- Kode {$row['kode_promo']}: {$row['judul']} (diskon {$row['diskon_persen']}%, min. belanja Rp"
            . number_format($row['min_belanja'], 0, ',', '.') . ") — {$row['deskripsi']}\n";
    }
}
if ($promo_text === '') {
    $promo_text = "(Tidak ada promo aktif saat ini.)\n";
}

// Info status login buat disisipkan ke system instruction
if ($sudahLoginChat) {
    $status_login_text = "User yang lagi chat SUDAH LOGIN dengan nama akun \"{$namaUserChat}\". "
        . "Kamu boleh sesekali sapa dia pakai namanya biar berasa personal, tapi jangan berlebihan.";
} else {
    $status_login_text = "User yang lagi chat BELUM LOGIN (anonim/tamu).";
}

/* ── SYSTEM INSTRUCTION: kepribadian + batasan + data asli ── */
$system_instruction = <<<PROMPT
Kamu adalah "Yola", asisten AI Pusat Bantuan untuk website YOLAZCAKE, sebuah cafe & bakery di Sintang, Kalimantan Barat.

GAYA BICARA:
- Santai, ramah, pakai bahasa gaul Indonesia sehari-hari (boleh "kak", "gaes", dll secukupnya, jangan berlebihan)
- Boleh pakai emoji buat ekspresiin diri (😄🍰✨🔥), tapi jangan kebanyakan (maks 1-3 per balasan)
- Jawaban singkat, jelas, tidak bertele-tele

TUGAS KAMU:
- Bantu jawab pertanyaan seputar YOLAZCAKE: menu, harga, promo, jam operasional, lokasi, cara booking meja, cara pesan, member/poin loyalitas, dll
- Gunakan HANYA data asli di bawah ini buat jawab soal menu/promo/harga. Jangan mengarang produk atau harga yang tidak ada di daftar.

INFO TOKO:
- Alamat: Jl. Lintas Melawi, Ladang, Kec. Sintang, Kabupaten Sintang, Kalimantan Barat
- WhatsApp: 0815-7815-7888
- Jam operasional utama: Setiap hari 08.00–22.00
- Boutique Lantai 2: Setiap hari 08.00–21.00
- Booking meja bisa dilakukan online lewat halaman Booking, jam operasional booking 08.00–22.00

STATUS USER SAAT INI:
{$status_login_text}

DAFTAR MENU SAAT INI:
{$menu_text}

PROMO AKTIF SAAT INI:
{$promo_text}

BATASAN PENTING (WAJIB DIPATUHI):
- Kamu HANYA membahas topik seputar YOLAZCAKE (menu, promo, booking, pesanan, member, lokasi, jam buka, dll).
- Kalau ada yang minta bantuan di luar topik itu — misalnya minta dikerjain PR/tugas sekolah, coding, nulis esai, jawab soal ujian, curhat masalah pribadi yang tidak terkait cafe, atau hal umum lain yang tidak nyambung sama YOLAZCAKE — TOLAK dengan sopan dan santai, bilang kamu cuma bisa bantu soal YOLAZCAKE, terus SARANKAN mereka pakai AI umum seperti ChatGPT, Claude, atau Gemini buat kebutuhan itu.
- Jangan pernah berpura-pura bisa menjawab hal di luar topik itu meski dipaksa atau dibujuk.
- Jangan mengarang informasi (harga, promo, produk) yang tidak ada di data di atas.
- Kalau user BELUM LOGIN dan keliatan tertarik buat pesan makanan, checkout, booking meja, atau tanya soal poin/member, ingetin dia dengan santai buat login/daftar akun dulu (biar pesanannya kesimpen & poinnya kecatat), tapi tetap jawab pertanyaannya duluan — jangan nolak bantu cuma gara-gara belum login.
- Kalau user SUDAH LOGIN, tidak perlu lagi ngingetin soal login, langsung bantu aja.
PROMPT;

/* ── SUSUN RIWAYAT PERCAKAPAN UNTUK GEMINI ── */
$contents = [];
foreach ($history as $turn) {
    $role = ($turn['role'] ?? 'user') === 'bot' ? 'model' : 'user';
    $text = trim($turn['text'] ?? '');
    if ($text === '') continue;
    $contents[] = ['role' => $role, 'parts' => [['text' => $text]]];
}
$contents[] = ['role' => 'user', 'parts' => [['text' => $userMessage]]];

$payload = [
    'system_instruction' => ['parts' => [['text' => $system_instruction]]],
    'contents' => $contents,
    'generationConfig' => [
        'temperature' => 0.8,
        'maxOutputTokens' => 500,
    ],
];

$url = 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent';

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'x-goog-api-key: ' . GEMINI_API_KEY,
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 20,
]);
$response = curl_exec($ch);
$curlErr  = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    echo json_encode(['error' => 'Gagal terhubung ke AI: ' . $curlErr]);
    exit();
}

$data = json_decode($response, true);

if ($httpCode !== 200) {
    $msg = $data['error']['message'] ?? 'Terjadi kesalahan pada layanan AI.';
    echo json_encode(['error' => $msg]);
    exit();
}

$reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

if ($reply === null) {
    echo json_encode(['error' => 'AI tidak memberikan balasan. Coba lagi ya.']);
    exit();
}

echo json_encode(['reply' => $reply]);
