<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Install PHPMailer mirip kayak install php-jwt
// pakai ini composer require phpmailer/phpmailer di terminal

require '../vendor/autoload.php';

// Fungsi generate OTP 6 digit
function generateOTP($length = 6)
{
    return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

// Simpan OTP ke tempat penyimpanan, misal session atau database
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email tidak valid']);
        exit;
    }

    $otp = generateOTP();
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_email'] = $email;
    $_SESSION['otp_time'] = time();

    $mail = new PHPMailer(true);

    try {
        // Server settings (contoh pakai SMTP Gmail)
        $mail->isSMTP();
        $mail->Host = 'smtp-relay.brevo.com';
        $mail->SMTPAuth = true;
        $mail->Username = '8de16c001@smtp-brevo.com';     // ganti email pengirim
        $mail->Password = 'SIbh0dWEnOgmay8k';        // ganti password aplikasi / app password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('wahabinasrul@gmail.com', 'Edulink');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Kode OTP Anda';
        $mail->Body = "<p>Kode OTP Anda adalah: <b>$otp</b></p><p>Jangan beritahu siapa pun kode ini.</p>";

        $mail->send();

        echo json_encode(['success' => true, 'message' => 'OTP sudah dikirim ke email']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Gagal kirim email: ' . $mail->ErrorInfo]);
    }
}