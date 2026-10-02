<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

function sendOTPEmail($email, $otp) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sagniksaha847@gmail.com';       
        $mail->Password   = "wjxktpolcdwadsul";               
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
  
        $mail->setFrom('sagniksaha847@gmail.com', 'NoteMarket');
        $mail->addAddress($email);


        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for NoteMarket';
        $mail->Body    = "<h2>Your OTP is <strong>$otp</strong></h2><p>This OTP is valid for 10 minutes.</p>";

        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}
?>
