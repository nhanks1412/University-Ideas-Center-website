<?php
// Load PHPMailer library
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * General email sending function for the entire system
 * @param string $toEmail Recipient email
 * @param string $subject Email subject
 * @param string $bodyContent Email content (Supports HTML tags)
 * @return boolean Send status (success or failure)
 */
function sendEmailNotification($toEmail, $subject, $bodyContent) {
    $mail = new PHPMailer(true);

    try {
        // Set up Google SMTP Server
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        
        $mail->Username   = 'kiendepzai2710@gmail.com';        
        $mail->Password   = 'icni spjm oqoo yudt';                
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->CharSet    = 'UTF-8';

        // Configure sender & recipient
        $mail->setFrom('kiendepzai2710@gmail.com', 'University Ideas Center'); // Same email as Username above
        $mail->addAddress($toEmail);

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $bodyContent;

        // Send email
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Boss can uncomment the line below to see detailed errors if sending fails
        // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return false;
    }
}
?>