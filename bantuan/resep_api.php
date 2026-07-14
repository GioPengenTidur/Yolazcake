<?php
/**
 * resep_api.php
 * -----------------------------------------------------------------------
 * Endpoint buat fitur "Resep Kreasi Yola AI" (bantuan/resep_ai.php).
 * User kasih daftar bahan yang mereka punya, Yola AI kasih 1-2 ide kreasi
 * kue/minuman sederhana ala YOLAZCAKE yang bisa dibuat dari bahan itu.
 */

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/groq_config.php';

$input = json_decode(file_get_contents('php://input'), true);
$bahan = trim($input['bahan'] ?? '');

if ($bahan === '') {
    echo json_encode(['error' => 'Bahannya kosong, isi dulu ya.']);
    exit();
}

// Batas wajar biar tidak disalahgunakan buat spam token
if (mb_strlen($bahan) > 400) {
    $bahan = mb_substr($bahan, 0, 400);
}

$system_instruction = <<<PROMPT
Kamu adalah "Yola", asisten AI ramah dari YOLAZCAKE, sebuah cafe & bakery di Sintang, Kalimantan Barat.
Tugas kamu sekarang: kasih ide KREASI KUE/DESSERT/MINUMAN SEDERHANA ala rumahan yang bisa dibuat dari
bahan-bahan yang disebutkan user, walau bahannya seadanya/sisa.

GAYA BICARA:
- Santai, ramah, bahasa gaul Indonesia sehari-hari, boleh emoji secukupnya (maks 2-3)
- To the point, tidak bertele-tele

FORMAT JAWABAN (WAJIB, ikuti urutan ini):
1. Sebutkan 1-2 nama kreasi yang cocok dari bahan yang disebutkan
2. Untuk tiap kreasi: sebutkan bahan tambahan sederhana yang mungkin perlu ditambah (kalau ada), lalu langkah-langkah singkat (3-6 langkah, bernomor)
3. Tutup dengan satu kalimat ramah, boleh selipkan ajakan santai buat mampir YOLAZCAKE kalau lagi males masak sendiri

BATASAN PENTING:
- HANYA kasih ide kue/dessert/minuman/camilan manis simpel ala cafe & bakery — JANGAN kasih resep masakan berat/lauk utama.
- Kalau bahan yang disebutkan sama sekali tidak nyambung buat bikin kue/dessert/minuman (misal cuma sebutin barang non-makanan), bilang jujur santai bahwa bahannya kurang pas, dan saranin bahan dasar simpel yang biasanya ada di rumah (telur, tepung, gula, susu).
- Jangan mengarang klaim berlebihan (misal "dijamin selalu berhasil 100%"), tetap realistis.
PROMPT;

$payload = [
    'model' => GROQ_MODEL,
    'messages' => [
        ['role' => 'system', 'content' => $system_instruction],
        ['role' => 'user', 'content' => "Bahan yang aku punya: {$bahan}"],
    ],
    'temperature' => 0.85,
    'max_tokens' => 500,
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

$resep = $data['choices'][0]['message']['content'] ?? null;

if ($resep === null) {
    echo json_encode(['error' => 'AI tidak memberikan balasan. Coba lagi ya.']);
    exit();
}

echo json_encode(['resep' => $resep]);
