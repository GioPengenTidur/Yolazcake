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
require_once '../config/chat_memory_helper.php';

// Status login user yang lagi chat, dipakai buat personalisasi &
// ajakan login kalau dia keliatan mau pesan/booking.
$sudahLoginChat = isset($_SESSION['username']);
$namaUserChat   = $sudahLoginChat ? $_SESSION['username'] : null;

// Identitas pemilik obrolan (id_user kalau login, guest_token kalau tamu)
// dipakai buat nyimpen & ngambil riwayat obrolan + memori jangka panjang.
[$idUserChat, $guestTokenChat] = chat_ambil_identitas();

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = trim($input['message'] ?? '');
$history     = is_array($input['history'] ?? null) ? $input['history'] : [];
$idSesi      = isset($input['id_sesi']) ? (int) $input['id_sesi'] : 0;

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

// Sesi obrolan ini valid dipakai kalau memang milik user/tamu yang sedang
// chat. Kalau belum ada / tidak valid / obrolan baru, sesi baru dibuat
// begitu pesan pertama terkirim.
if ($idSesi > 0 && !chat_sesi_valid($conn, $idSesi, $idUserChat, $guestTokenChat)) {
    $idSesi = 0;
}
if ($idSesi === 0) {
    $idSesi = chat_buat_sesi($conn, $idUserChat, $guestTokenChat, $userMessage);
}

// Memori jangka panjang: ringkasan hal-hal penting dari obrolan
// sebelumnya (preferensi, konteks, hal yang pernah ditanyakan), supaya
// Yola AI tetap "kenal" user ini walau baru mulai obrolan baru.
$memoriUser = chat_ambil_memori($conn, $idUserChat, $guestTokenChat);
$memori_text = $memoriUser !== ''
    ? $memoriUser
    : '(Belum ada memori tersimpan tentang user ini — ini kemungkinan obrolan pertama mereka.)';

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

CARA KAMU MIKIR (penting, ikuti selalu):
- Sebelum jawab, pahami dulu SEBENARNYA apa yang ditanyakan user, jangan asal tebak dari sepotong kata kunci.
- Kamu paham betul seluruh isi & fitur website YOLAZCAKE (lihat PROFIL WEBSITE di bawah) — jawab berdasarkan pemahaman itu, bukan asal generik.
- Jawaban HARUS berpijak pada data asli yang dikasih di system instruction ini (menu, promo, info toko, memori user). Kalau datanya tidak ada/tidak yakin, JUJUR bilang tidak tahu / sarankan tanya admin lewat WhatsApp, jangan mengarang.
- Kalau pertanyaan user kompleks atau bercabang-cabang, tetap fokus ke inti pertanyaannya, jawab step-by-step secukupnya, dan jangan melenceng bahas hal lain yang tidak diminta.
- Tetap pada topik YOLAZCAKE walau user coba mancing ke arah lain (lihat BATASAN di bawah).

GAYA BICARA:
- Santai, ramah, pakai bahasa gaul Indonesia sehari-hari (boleh "kak", "gaes", dll secukupnya, jangan berlebihan)
- Boleh pakai emoji buat ekspresiin diri (😄🍰✨🔥), tapi jangan kebanyakan (maks 1-3 per balasan)
- Jawaban singkat, jelas, tidak bertele-tele

TUGAS KAMU:
- Bantu jawab pertanyaan seputar YOLAZCAKE: menu, harga, promo, jam operasional, lokasi, cara booking meja, cara pesan, member/poin loyalitas, dll
- Gunakan HANYA data asli di bawah ini buat jawab soal menu/promo/harga. Jangan mengarang produk atau harga yang tidak ada di daftar.

PROFIL WEBSITE YOLAZCAKE (fitur-fitur yang tersedia, biar kamu paham konteksnya kalau user nanya "gimana caranya..."):
- Beranda (index.php): highlight promo, galeri foto, produk unggulan.
- Menu (produk/menu.php): daftar semua produk per kategori, ada filter kategori & keranjang.
- Booking meja (booking/booking.php): user pilih meja & jadwal, status booking bisa dipantau, perlu login biar tersimpan ke akun.
- Riwayat pesanan & poin: user yang login bisa lihat riwayat pesanan dan poin loyalitas di halaman akun/member.
- Member & poin loyalitas: tier Bronze/Silver/Gold/Platinum berdasar poin (0/100/250/500 poin), didapat otomatis dari transaksi (booking terkonfirmasi/selesai + pesanan yang tidak dibatalkan).
- Promo (promo.php): daftar promo aktif dengan kode & syarat min. belanja, bisa diklaim saat checkout.
- Galeri (gallery.php): foto suasana cafe & produk.
- Rating & ulasan: user yang login bisa kasih rating/ulasan tempat & produk lewat tombol mengambang di kanan-bawah.
- Kontak & hubungi admin (about.php / auth/hubungi_admin.php): form kontak, WhatsApp, dan alamat.
- Akun: registrasi/login pakai username atau email, ada fitur lupa password lewat OTP email.

INFO TOKO:
- Alamat: Jl. Lintas Melawi, Ladang, Kec. Sintang, Kabupaten Sintang, Kalimantan Barat
- WhatsApp: 0815-7815-7888
- Jam operasional utama: Setiap hari 08.00–22.00
- Boutique Lantai 2: Setiap hari 08.00–21.00
- Booking meja bisa dilakukan online lewat halaman Booking, jam operasional booking 08.00–22.00

STATUS USER SAAT INI:
{$status_login_text}

MEMORI TENTANG USER INI (hal-hal penting dari obrolan sebelumnya, termasuk obrolan yang beda sesi — pakai ini biar kamu "kenal" user ini, tapi jangan sebut-sebut kata "memori"/"database" ke user, cukup pakai secara natural kayak kamu emang inget):
{$memori_text}

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

/* ── SIMPAN PESAN INI KE RIWAYAT SESI ── */
chat_simpan_pesan($conn, $idSesi, 'user', $userMessage);
chat_simpan_pesan($conn, $idSesi, 'bot', $reply);

/* ── PERBARUI MEMORI JANGKA PANJANG ──
   Panggilan ringan & cepat ke Gemini buat merangkum ulang hal-hal penting
   tentang user (preferensi, nama, topik yang sering ditanya, dll) supaya
   kepake lagi walau user mulai obrolan baru / sesi baru nanti. Kalau
   gagal/timeout, diamkan saja — tidak boleh sampai gagalin balasan utama. */
$memoryPrompt = <<<MEMPROMPT
Ringkasan memori lama tentang user ini:
{$memori_text}

Cuplikan obrolan barusan:
User: {$userMessage}
Yola: {$reply}

Tugas kamu: perbarui ringkasan memori tentang USER ini (bukan tentang Yola/YOLAZCAKE secara umum) dalam bentuk poin-poin singkat bahasa Indonesia — misalnya nama panggilan, preferensi menu/rasa, kebiasaan booking, hal yang pernah ditanyakan, atau konteks personal relevan lain yang disebut sendiri oleh user. Gabungkan info lama yang masih relevan dengan info baru, buang yang tidak penting/basi. Maksimal 6 poin singkat. Kalau memang tidak ada info baru/lama yang layak diingat, balas persis: (kosong)
MEMPROMPT;

$memPayload = [
    'contents' => [['role' => 'user', 'parts' => [['text' => $memoryPrompt]]]],
    'generationConfig' => ['temperature' => 0.3, 'maxOutputTokens' => 220],
];
$chMem = curl_init($url);
curl_setopt_array($chMem, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'x-goog-api-key: ' . GEMINI_API_KEY],
    CURLOPT_POSTFIELDS => json_encode($memPayload),
    CURLOPT_TIMEOUT => 10,
]);
$memResponse = curl_exec($chMem);
curl_close($chMem);
if ($memResponse !== false) {
    $memData = json_decode($memResponse, true);
    $ringkasanBaru = trim($memData['candidates'][0]['content']['parts'][0]['text'] ?? '');
    if ($ringkasanBaru !== '' && stripos($ringkasanBaru, '(kosong)') === false) {
        chat_simpan_memori($conn, $idUserChat, $guestTokenChat, $ringkasanBaru);
    }
}

echo json_encode(['reply' => $reply, 'id_sesi' => $idSesi]);
