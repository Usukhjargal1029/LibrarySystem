<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendMail($to, $subject, $message) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'usuhjargal02@gmail.com';             // Your Gmail
        $mail->Password   = 'xghimeqpouyhjjib';                   // App password (no spaces)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender and recipient
        $mail->setFrom('usuhjargal02@gmail.com', 'LibrarySystem');
        $mail->addAddress($to); // Send to the provided email address
$mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message); // Plain text alternative for non-HTML email clients

        // Send email
        $mail->send();
        return true; // Success
    } catch (Exception $e) {
        // Error sending email
        return false; // Failure
    }
}
