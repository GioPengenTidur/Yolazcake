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
