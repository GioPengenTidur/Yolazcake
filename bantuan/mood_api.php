<?php
/**
 * mood_api.php
 * -----------------------------------------------------------------------
 * Endpoint buat fitur "Cocokin Mood Kamu" (bantuan/mood_menu.php).
 * User jawab 2 pertanyaan singkat (mood rasa + konteks momen), Yola AI
 * kasih 2-3 rekomendasi menu SPESIFIK dari data produk asli di database
 * (bukan ngarang), sesuai mood & konteks yang dipilih.
 */

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/koneksi.php';
require_once '../config/groq_config.php';

$input   = json_decode(file_get_contents('php://input'), true);
$mood    = trim($input['mood'] ?? '');
$konteks = trim($input['konteks'] ?? '');

if ($mood === '' || $konteks === '') {
    echo json_encode(['error' => 'Jawaban belum lengkap.']);
    exit();
}

/* ── Ambil data menu asli (stok > 0 saja) biar rekomendasi akurat ── */
$menu_text = '';
$q = mysqli_query($conn, "
    SELECT k.nama_kategori, p.nama_produk, p.harga, p.deskripsi, p.stok
    FROM produk p
    LEFT JOIN kategori k ON k.id_kategori = p.id_kategori
    WHERE p.stok > 0
    ORDER BY k.nama_kategori, p.nama_produk
");
$menu_by_kategori = [];
if ($q) {
    while ($row = mysqli_fetch_assoc($q)) {
        $kat = $row['nama_kategori'] ?? 'Lainnya';
        $menu_by_kategori[$kat][] = '- ' . $row['nama_produk'] . ' — Rp' . number_format($row['harga'], 0, ',', '.')
            . ($row['deskripsi'] ? ' (' . $row['deskripsi'] . ')' : '');
    }
}
foreach ($menu_by_kategori as $kat => $items) {
    $menu_text .= "\n{$kat}:\n" . implode("\n", $items) . "\n";
}
if ($menu_text === '') {
    echo json_encode(['error' => 'Menu belum tersedia saat ini.']);
    exit();
}

$system_instruction = <<<PROMPT
Kamu adalah "Yola", asisten AI ramah dari YOLAZCAKE, sebuah cafe & bakery di Sintang, Kalimantan Barat.
Tugas kamu: kasih rekomendasi menu SPESIFIK dari DAFTAR MENU ASLI di bawah, yang paling cocok sama
mood rasa & konteks momen yang disebutkan user.

GAYA BICARA:
- Santai, ramah, bahasa gaul Indonesia sehari-hari, boleh emoji secukupnya (maks 2-3)
- To the point, tidak bertele-tele

FORMAT JAWABAN (WAJIB):
1. Kasih 2-3 rekomendasi menu, HARUS persis nama produk yang ada di DAFTAR MENU ASLI (jangan mengarang produk baru)
2. Untuk tiap rekomendasi, kasih 1 kalimat singkat kenapa itu cocok sama mood & konteksnya
3. Tutup dengan 1 kalimat ramah/ajakan santai

DAFTAR MENU ASLI (HANYA boleh rekomendasiin dari sini):
{$menu_text}

BATASAN:
- JANGAN merekomendasikan produk yang tidak ada di daftar di atas.
- Kalau menurutmu tidak ada menu yang benar-benar pas, tetap pilih yang paling mendekati dari daftar, jangan bilang "tidak ada".
PROMPT;

$userMessage = "Mood rasa yang aku pengen: {$mood}. Lagi buat momen: {$konteks}.";

$payload = [
    'model' => GROQ_MODEL,
    'messages' => [
        ['role' => 'system', 'content' => $system_instruction],
        ['role' => 'user', 'content' => $userMessage],
    ],
    'temperature' => 0.7,
    'max_tokens' => 400,
];

$url = 'https://api.groq.com/openai/v1/chat/completions';

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . GROQ_API_KEY,
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

$rekomendasi = $data['choices'][0]['message']['content'] ?? null;

if ($rekomendasi === null) {
    echo json_encode(['error' => 'AI tidak memberikan balasan. Coba lagi ya.']);
    exit();
}

echo json_encode(['rekomendasi' => $rekomendasi]);
