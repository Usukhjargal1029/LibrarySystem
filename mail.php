<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendMail($to, $subject, $message) {
    $mail = new PHPMailer(true);

    try {
        // Load credentials from environment
        $gmailUser = $_ENV['MAIL_USERNAME'] ?? getenv('MAIL_USERNAME');
        $gmailPass = $_ENV['MAIL_PASSWORD'] ?? getenv('MAIL_PASSWORD');

        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $gmailUser;
        $mail->Password   = $gmailPass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender and recipient
        $mail->setFrom($gmailUser, 'LibrarySystem');
        $mail->addAddress($to);

        // Content
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message);

        // Send email
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
