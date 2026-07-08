<?php
/**
 * cerebras_config_example.php
 * -----------------------------------------------------------------------
 * Template config buat DevBot Project Scanner (tools internal), pakai
 * Cerebras API (bukan Gemini). Copy file ini jadi
 * "cerebras_config_CAFE_config.php" di folder yang sama, lalu isi API key
 * kamu sendiri di bawah.
 *
 * Cara dapat API key: daftar gratis di https://cloud.cerebras.ai/ ->
 * bagian API Keys -> Create API Key.
 */

define('CEREBRAS_API_KEY', 'GANTI_DENGAN_API_KEY_KAMU_DI_SINI');

// Model yang dipakai. llama-3.3-70b: seimbang antara kualitas & kecepatan.
// Alternatif lain di akun Cerebras kamu: 'llama3.1-8b' (lebih cepat/ringan),
// 'qwen-3-32b', 'gpt-oss-120b' (lebih pintar tapi lebih lambat).
define('CEREBRAS_MODEL', 'gpt-oss-120b');
