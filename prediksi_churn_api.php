<?php
/**
 * prediksi_churn_api.php
 * Endpoint buat fitur "Prediksi Member Berisiko" (prediksi_churn.php).
 * Ambil data member yang berisiko churn dari database, kirim ke Gemini
 * buat dianalisis & dikasih saran tindakan konkret ke admin.
 *
 * Request  (GET, dipanggil dari halaman admin, butuh login role admin)
 */

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(['error' => 'Akses ditolak.']);
    exit();
}

require_once 'config/koneksi.php';
require_once 'config/mistral_config.php';

$ambangHariBerisiko = 30;

$sql = "
    SELECT
        m.nama, m.poin,
        GREATEST(
            COALESCE((SELECT MAX(p.tanggal) FROM pemesanan p WHERE p.id_member = m.id_member), '1970-01-01 00:00:00'),
            COALESCE((SELECT MAX(b.created_at) FROM booking b WHERE b.id_member = m.id_member), '1970-01-01 00:00:00')
        ) AS terakhir_aktif,
        (SELECT COUNT(*) FROM pemesanan p2 WHERE p2.id_member = m.id_member) AS total_pesanan,
        (SELECT COUNT(*) FROM booking b2 WHERE b2.id_member = m.id_member) AS total_booking
    FROM member m
";
$res = mysqli_query($conn, $sql);

$daftarBerisiko = [];
$totalMember = 0;
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $totalMember++;
        $terakhir = $row['terakhir_aktif'];
        $belumPernah = ($terakhir === '1970-01-01 00:00:00');
        $hariSejak = $belumPernah ? null : (int) floor((time() - strtotime($terakhir)) / 86400);
        $totalTransaksi = (int) $row['total_pesanan'] + (int) $row['total_booking'];

        if ($belumPernah || $hariSejak >= $ambangHariBerisiko) {
            $daftarBerisiko[] = [
                'nama'      => $row['nama'],
                'poin'      => (int) $row['poin'],
                'hari'      => $belumPernah ? 'belum pernah transaksi sama sekali' : "{$hariSejak} hari sejak transaksi terakhir",
                'transaksi' => $totalTransaksi,
            ];
        }
    }
}

if (empty($daftarBerisiko)) {
    echo json_encode(['insight' => "Mantap! 🎉 Gak ada member yang berisiko churn saat ini (ambang {$ambangHariBerisiko} hari). Semua member masih aktif bertransaksi."]);
    exit();
}

// Batasi maksimal 25 member biar prompt tidak membengkak
$dikirimKeAI = array_slice($daftarBerisiko, 0, 25);
$data_text = '';
foreach ($dikirimKeAI as $mb) {
    $data_text .= "- {$mb['nama']}: {$mb['hari']}, total transaksi seumur hidup {$mb['transaksi']}x, poin saat ini {$mb['poin']}\n";
}
$totalBerisikoCount = count($daftarBerisiko);

$system_instruction = <<<PROMPT
Kamu adalah "Yola", asisten AI analitik untuk admin YOLAZCAKE, sebuah cafe & bakery di Sintang, Kalimantan Barat.
Tugas kamu: analisis daftar member yang berisiko churn (jarang/belum pernah transaksi lagi) di bawah ini,
dan kasih insight + saran tindakan konkret buat admin.

GAYA BICARA:
- To the point, profesional tapi tetap ramah, bahasa Indonesia
- Jangan bertele-tele, fokus ke insight yang actionable

FORMAT JAWABAN (WAJIB, ikuti urutan ini):
1. Ringkasan singkat kondisi (berapa total member berisiko, pola yang kelihatan kalau ada)
2. Kelompokkan member jadi 2-3 kategori prioritas berdasar data yang ada (misal: "prioritas tinggi" untuk yang dulunya sering transaksi tapi sekarang lama tidak, vs "belum pernah dicoba" untuk yang belum pernah transaksi sama sekali)
3. Saran tindakan konkret per kategori (misal: kirim promo khusus, WhatsApp reminder, dsb) — maksimal 3-4 saran total, jangan generic banget
4. Tutup dengan 1 kalimat motivasi singkat buat admin

DATA MEMBER BERISIKO (total keseluruhan ada {$totalMember} member, yang berisiko ada {$totalBerisikoCount}):
{$data_text}

BATASAN:
- Jangan mengarang data yang tidak ada di atas (jangan sebut nominal rupiah spesifik kalau tidak dikasih).
- Fokus ke insight & saran, bukan cuma mengulang data mentah.
PROMPT;

$payload = [
    'model' => MISTRAL_MODEL,
    'messages' => [
        ['role' => 'system', 'content' => $system_instruction],
        ['role' => 'user', 'content' => 'Tolong analisis data member berisiko churn di atas dan kasih saran tindakan buat aku sebagai admin.'],
    ],
    'temperature' => 0.5,
    'max_tokens' => 550,
];

$url = 'https://api.mistral.ai/v1/chat/completions';

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . MISTRAL_API_KEY,
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 25,
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

$insight = $data['choices'][0]['message']['content'] ?? null;

if ($insight === null) {
    echo json_encode(['error' => 'AI tidak memberikan balasan. Coba lagi ya.']);
    exit();
}

echo json_encode(['insight' => $insight]);
