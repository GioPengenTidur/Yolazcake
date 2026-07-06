<?php
/**
 * Helper untuk mengirim email OTP reset password lewat Gmail SMTP.
 * Memakai library PHPMailer (sudah disertakan di /vendor/phpmailer,
 * tidak perlu composer install).
 */

require_once __DIR__ . '/mail_config.php';
require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Kirim kode OTP reset password ke email tujuan.
 *
 * @param string $toEmail   Email tujuan (gmail milik user)
 * @param string $toName    Nama / username user (untuk sapaan di email)
 * @param string $otp       Kode OTP 6 digit
 * @return array ['success' => bool, 'message' => string]
 */
function kirimEmailOtpResetPassword(string $toEmail, string $toName, string $otp): array
{
    $mail = new PHPMailer(true);

    try {
        // ── Konfigurasi SMTP ──
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->SMTPDebug = 2;
$mail->Debugoutput = function($str, $level) {
    file_put_contents(__DIR__ . '/../mail_debug.log', $str . "\n", FILE_APPEND);
};

        // ── Pengirim & penerima ──
        $mail->setFrom(MAIL_USERNAME, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        // ── Isi email ──
        $mail->isHTML(true);
        $mail->Subject = 'Kode OTP Reset Password - YOLAZCAKE Sintang';
        $mail->Body    = buatTemplateEmailOtp($toName, $otp);
        $mail->AltBody  = "Halo $toName,\n\nKode OTP untuk reset password akun YOLAZCAKE Sintang kamu adalah: $otp\n"
                         . "Kode ini berlaku selama " . OTP_EXPIRE_MINUTES . " menit. Jangan berikan kode ini ke siapa pun.\n\n"
                         . "Jika kamu tidak meminta reset password, abaikan email ini.";

        $mail->send();
        return ['success' => true, 'message' => 'Email OTP berhasil dikirim.'];

    } catch (PHPMailerException $e) {
        return [
            'success' => false,
            'message' => 'Gagal mengirim email OTP. Coba lagi beberapa saat lagi.',
            'debug'   => $mail->ErrorInfo,
        ];
    }
}

/**
 * Kirim notifikasi otomatis "pesan sudah diterima" ke pelanggan,
 * dipicu saat admin membuka (baca) pesan kontak untuk pertama kali.
 *
 * @param string $toEmail   Email tujuan (email pelanggan)
 * @param string $toName    Nama pelanggan
 * @param string $subjek    Subjek pesan asli dari pelanggan
 * @param string $pesanAsli Isi pesan asli dari pelanggan
 * @return array ['success' => bool, 'message' => string]
 */
function kirimEmailNotifikasiDibaca(string $toEmail, string $toName, string $subjek, string $pesanAsli): array
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(MAIL_USERNAME, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'Pesan Anda Telah Kami Terima - YOLAZCAKE Sintang';
        $mail->Body    = buatTemplateEmailNotifikasiDibaca($toName, $subjek, $pesanAsli);
        $mail->AltBody = "Yth. Pelanggan {$toName},\n\nPesan Anda dengan subjek \"{$subjek}\" telah kami terima dan sedang ditinjau oleh tim kami. "
                        . "Kami akan segera menghubungi Anda kembali.\n\nIni adalah notifikasi otomatis, mohon tidak membalas email ini.\n\n"
                        . "Terima kasih telah menghubungi YOLAZCAKE Sintang.";

        $mail->send();
        return ['success' => true, 'message' => 'Notifikasi otomatis berhasil dikirim ke email pelanggan.'];

    } catch (PHPMailerException $e) {
        return [
            'success' => false,
            'message' => 'Gagal mengirim notifikasi otomatis ke email pelanggan.',
            'debug'   => $mail->ErrorInfo,
        ];
    }
}

/**
 * Kirim balasan yang diketik sendiri oleh admin ke email pelanggan.
 *
 * @param string $toEmail      Email tujuan (email pelanggan)
 * @param string $toName       Nama pelanggan
 * @param string $subjek       Subjek pesan asli dari pelanggan
 * @param string $pesanAsli    Isi pesan asli dari pelanggan
 * @param string $balasanAdmin Isi balasan yang diketik admin
 * @return array ['success' => bool, 'message' => string]
 */
function kirimEmailBalasanKontak(string $toEmail, string $toName, string $subjek, string $pesanAsli, string $balasanAdmin): array
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(MAIL_USERNAME, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'Balasan Pesan Anda - YOLAZCAKE Sintang';
        $mail->Body    = buatTemplateEmailBalasan($toName, $subjek, $pesanAsli, $balasanAdmin);
        $mail->AltBody = "Yth. Pelanggan {$toName},\n\nTerima kasih telah menghubungi kami dengan subjek \"{$subjek}\". "
                        . "Berikut balasan dari tim kami:\n\n{$balasanAdmin}\n\n"
                        . "Jika masih ada pertanyaan, silakan hubungi kami kembali melalui website YOLAZCAKE Sintang.";

        $mail->send();
        return ['success' => true, 'message' => 'Balasan berhasil dikirim ke email pelanggan.'];

    } catch (PHPMailerException $e) {
        return [
            'success' => false,
            'message' => 'Gagal mengirim balasan ke email pelanggan. Coba lagi beberapa saat lagi.',
            'debug'   => $mail->ErrorInfo,
        ];
    }
}

/**
 * Template HTML sederhana untuk email OTP, mengikuti tema warna
 * emas/coklat YOLAZCAKE supaya konsisten dengan tampilan web.
 */
function buatTemplateEmailOtp(string $toName, string $otp): string
{
    $namaAman = htmlspecialchars($toName);
    $otpAman  = htmlspecialchars($otp);
    $menit    = OTP_EXPIRE_MINUTES;

    return <<<HTML
    <div style="font-family: Arial, sans-serif; background:#2b1a11; padding:32px 16px;">
      <div style="max-width:420px; margin:0 auto; background:#ffffff; border-radius:16px; overflow:hidden;">
        <div style="background:linear-gradient(135deg,#D4AF37,#b8860b); padding:20px; text-align:center;">
          <h1 style="margin:0; color:#1e0e0a; font-size:20px;">YOLAZCAKE Sintang</h1>
        </div>
        <div style="padding:28px 24px; color:#333;">
          <p style="margin:0 0 12px;">Halo <strong>{$namaAman}</strong>,</p>
          <p style="margin:0 0 20px;">Kami menerima permintaan reset password untuk akun kamu. Gunakan kode OTP di bawah ini untuk melanjutkan:</p>
          <div style="text-align:center; margin:24px 0;">
            <span style="display:inline-block; padding:14px 28px; background:#FFF3E0; border:2px dashed #D4AF37; border-radius:12px; font-size:28px; font-weight:bold; letter-spacing:8px; color:#6D4C41;">{$otpAman}</span>
          </div>
          <p style="margin:0 0 8px; font-size:14px; color:#666;">Kode ini berlaku selama <strong>{$menit} menit</strong>.</p>
          <p style="margin:0; font-size:13px; color:#999;">Jika kamu tidak meminta reset password, abaikan saja email ini. Jangan bagikan kode ini ke siapa pun, termasuk pihak yang mengaku dari YOLAZCAKE.</p>
        </div>
      </div>
    </div>
    HTML;
}

/**
 * Template HTML untuk notifikasi otomatis "pesan sudah diterima".
 */
function buatTemplateEmailNotifikasiDibaca(string $toName, string $subjek, string $pesanAsli): string
{
    $namaAman   = htmlspecialchars($toName);
    $subjekAman = htmlspecialchars($subjek);
    $pesanAman  = nl2br(htmlspecialchars($pesanAsli));

    return <<<HTML
    <div style="font-family: Arial, sans-serif; background:#2b1a11; padding:32px 16px;">
      <div style="max-width:460px; margin:0 auto; background:#ffffff; border-radius:16px; overflow:hidden;">
        <div style="background:linear-gradient(135deg,#D4AF37,#b8860b); padding:20px; text-align:center;">
          <h1 style="margin:0; color:#1e0e0a; font-size:20px;">YOLAZCAKE Sintang</h1>
        </div>
        <div style="padding:28px 24px; color:#333;">
          <p style="margin:0 0 12px;">Yth. Pelanggan <strong>{$namaAman}</strong>,</p>
          <p style="margin:0 0 16px;">Pesan Anda dengan subjek <strong>&quot;{$subjekAman}&quot;</strong> telah kami terima dan sedang ditinjau oleh tim kami. Kami akan segera menghubungi Anda kembali.</p>
          <div style="background:#FFF3E0; border-left:4px solid #D4AF37; border-radius:8px; padding:14px 16px; margin:0 0 16px; font-size:13px; color:#6D4C41;">
            <strong>Pesan Anda:</strong><br>{$pesanAman}
          </div>
          <p style="margin:0; font-size:13px; color:#999;">Ini adalah notifikasi otomatis, mohon tidak membalas email ini. Terima kasih telah menghubungi YOLAZCAKE Sintang.</p>
        </div>
      </div>
    </div>
    HTML;
}

/**
 * Template HTML untuk email balasan yang diketik sendiri oleh admin.
 */
function buatTemplateEmailBalasan(string $toName, string $subjek, string $pesanAsli, string $balasanAdmin): string
{
    $namaAman    = htmlspecialchars($toName);
    $subjekAman  = htmlspecialchars($subjek);
    $pesanAman   = nl2br(htmlspecialchars($pesanAsli));
    $balasanAman = nl2br(htmlspecialchars($balasanAdmin));

    return <<<HTML
    <div style="font-family: Arial, sans-serif; background:#2b1a11; padding:32px 16px;">
      <div style="max-width:460px; margin:0 auto; background:#ffffff; border-radius:16px; overflow:hidden;">
        <div style="background:linear-gradient(135deg,#D4AF37,#b8860b); padding:20px; text-align:center;">
          <h1 style="margin:0; color:#1e0e0a; font-size:20px;">YOLAZCAKE Sintang</h1>
        </div>
        <div style="padding:28px 24px; color:#333;">
          <p style="margin:0 0 12px;">Yth. Pelanggan <strong>{$namaAman}</strong>,</p>
          <p style="margin:0 0 16px;">Terima kasih telah menghubungi kami dengan subjek <strong>&quot;{$subjekAman}&quot;</strong>. Berikut balasan dari tim kami:</p>
          <div style="background:#F0FDF4; border-left:4px solid #10B981; border-radius:8px; padding:14px 16px; margin:0 0 16px; font-size:14px; color:#065F46;">
            {$balasanAman}
          </div>
          <details style="margin:0 0 16px;">
            <summary style="cursor:pointer; font-size:12px; color:#999;">Lihat pesan asli Anda</summary>
            <div style="margin-top:8px; font-size:12px; color:#777;">{$pesanAman}</div>
          </details>
          <p style="margin:0; font-size:13px; color:#999;">Jika masih ada pertanyaan, silakan hubungi kami kembali melalui website YOLAZCAKE Sintang.</p>
        </div>
      </div>
    </div>
    HTML;
}
