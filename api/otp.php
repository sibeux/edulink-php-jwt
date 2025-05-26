<?php
// Install PHPMailer mirip kayak install php-jwt
// pakai ini composer require phpmailer/phpmailer di terminal

require '../vendor/autoload.php';
include '../config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fungsi generate OTP 4 digit
function generateOTP($length = 4)
{
    return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

function sendOtpToDatabase($email, $otp, $db) {
    // Bersihkan data OTP yang sudah digunakan atau kadaluarsa
    $db->query("DELETE FROM otp WHERE is_used = true OR expires_at < NOW()");

    // Simpan atau update OTP baru
    $stmt = $db->prepare("
        INSERT INTO otp (id, email, code, created_at, expires_at, is_used)
        VALUES (NULL, ?, ?, NOW(), NOW() + INTERVAL 10 MINUTE, 'false')
        ON DUPLICATE KEY UPDATE
            code = VALUES(code),
            created_at = NOW(),
            expires_at = NOW() + INTERVAL 10 MINUTE,
            is_used = 'false'
    ");

    if ($stmt) {
        $stmt->bind_param('ss', $email, $otp);
        $stmt->execute();
        $stmt->close();
    } else {
        echo 'Could not prepare statement!';
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $name = $_POST['name'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email tidak valid']);
        exit;
    }

    $otp = generateOTP();

    $mail = new PHPMailer(true);

    try {
        // Server settings (contoh pakai SMTP Gmail)
        $mail->isSMTP();
        $mail->Host = 'smtp-relay.brevo.com';
        $mail->SMTPAuth = true;
        $mail->Username = '8de16c002@smtp-brevo.com';     // ganti email pengirim
        $mail->Password = 'OWRYxAqXHP2Dtvk0';        // ganti password aplikasi / app password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('admin@edulink.sibeux.my.id', 'Edulink');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Kode OTP Anda';
        $mail->Body = "<html xmlns=\"http://www.w3.org/1999/xhtml\"
    style=\"font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;\">

<head>
    <meta name=\"viewport\" content=\"width=device-width\" />
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
    <title>Edulink - Login OTP</title>
</head>

<body itemscope itemtype=\"http://schema.org/EmailMessage\"
    style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; line-height: 1.6em; background-color: #f6f6f6; margin: 0;\"
    bgcolor=\"#f6f6f6\">

    <table class=\"body-wrap\"
        style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; background-color: #f6f6f6; margin: 0;\"
        bgcolor=\"#f6f6f6\">
        <tr
            style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;\">
            <td style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0;\"
                valign=\"top\"></td>
            <td class=\"container\" width=\"600\"
                style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto;\"
                valign=\"top\">
                <div class=\"content\"
                    style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; max-width: 600px; display: block; margin: 0 auto; padding: 20px;\">
                    <table class=\"main\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\"
                        style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; border-radius: 3px; background-color: #fff; margin: 0; border: 1px solid #e9e9e9;\"
                        bgcolor=\"#fff\">
                        <tr
                            style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;\">
                            <td class=\"\"
                                style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 16px; vertical-align: top; color: #fff; font-weight: 500; text-align: center; border-radius: 3px 3px 0 0; background-color: #3A71FF; margin: 0; padding: 20px;\"
                                align=\"center\" bgcolor=\"#71b6f9\" valign=\"top\">
                                <a href=\"#\"> <img
                                        src=\"https://github.com/sibeux/edulink_learning_app/blob/master/assets/images/logos/logo_splash.png?raw=true\"
                                        height=\"20\" alt=\"logo\" /></a> <br />
                                <span style=\"margin-top: 10px;display: block;\">Login Verification</span>
                            </td>
                        </tr>
                        <tr
                            style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;\">
                            <td class=\"content-wrap\"
                                style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 20px;\"
                                valign=\"top\">
                                <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\"
                                    style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;\">
                                    <tr
                                        style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;\">
                                        <td class=\"content-block\"
                                            style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;\"
                                            valign=\"top\">
                                            Dear,
                                        </td>
                                    </tr>
                                    <tr
                                        style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;\">
                                        <td class=\"content-block\"
                                            style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;\"
                                            valign=\"top\">
                                            Percobaan login dengan nama: <strong
                                                style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;\">
                                                $name</strong><br />
                                            Berikut kode OTP untuk Login ke Edulink:
                                        </td>
                                    </tr>
                                    <!-- <tr style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;\">
                            <td class=\"content-block\"
                                style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;\"
                                valign=\"top\">
                                Silahkan klik link di bawah ini untuk me-reset password anda.
                            </td>
                        </tr> -->
                                    <tr
                                        style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;\">
                                        <td class=\"content-block\"
                                            style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;\"
                                            valign=\"top\">
                                            <p style=\"text-align:center\"><strong><span
                                                        style=\"font-size:28pt;color:#3A71FF\">$otp</span></strong></p>
                                        </td>
                                    </tr>
                                    <tr
                                        style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;\">
                                        <td class=\"content-block\"
                                            style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;\"
                                            valign=\"top\">
                                            Kode ini akan berlaku selama 10 menit. Jangan berikan kode ini kepada
                                            siapapun!</a>
                                        </td>
                                    </tr>
                                    <tr
                                        style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;\">
                                        <td class=\"content-block\"
                                            style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;\"
                                            valign=\"top\">
                                            <b><i>Abaikan jika Anda tidak melakukan percobaan login. Jika ada aktifitas
                                                    login yang mencurigakan, segera ubah password Anda atau hubungi
                                                    Customer Support kami</i></b> <a
                                                href=\"https://www.youtube.com/watch?v=dQw4w9WgXcQ&ab_channel=RickAstley\">support@edulink.id</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr
                                        style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;\">
                                        <td class=\"content-block\"
                                            style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;\"
                                            valign=\"top\">
                                            Best Regards,<br /><b>Edulink</b>.
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <div class=\"footer\"
                        style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; clear: both; color: #999; margin: 0; padding: 20px;\">
                        <table width=\"100%\"
                            style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;\">
                            <tr
                                style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;\">
                                <td class=\"aligncenter content-block\"
                                    style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 12px; vertical-align: top; color: #999; text-align: center; margin: 0; padding: 0 0 20px;\"
                                    align=\"center\" valign=\"top\">
                                    Email ini dikirim otomatis oleh system, membalas email ini tidak akan mendapat
                                    respon.<br />
                                    <a href=\"https://www.youtube.com/watch?v=dQw4w9WgXcQ&ab_channel=RickAstley\"
                                        style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 12px; color: #999; text-decoration: underline; margin: 0;\">
                                        www.edulink.id
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
            <td style=\"font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0;\"
                valign=\"top\"></td>
        </tr>
    </table>
</body>

</html>";

        $mail->send();

        echo json_encode(['success' => true, 'message' => 'OTP sudah dikirim ke email']);
        sendOtpToDatabase($email, $otp, $db);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Gagal kirim email: ' . $mail->ErrorInfo]);
    }
}