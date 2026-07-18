<?php
session_start();
include 'includes/config.php';
include 'includes/sendOTPEmail.php';  // ✅ Include the PHPMailer function

if (!isset($_SESSION['email'])) {
    header("Location: forgot_pass.php");
    exit();
}

$email = $_SESSION['email'];
$otp = rand(100000, 999999);
$expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

// Update OTP in the database
$conn->query("UPDATE users SET otp = '$otp', otp_expire = '$expiry' WHERE email = '$email'");

// Send email
if (sendOTPEmail($email, $otp)) {
    $_SESSION['otp_resent'] = "✅ New OTP sent to your email.";
} else {
    $_SESSION['otp_resent'] = "❌ Failed to send OTP email.";
}

header("Location: verify-otp.php");
exit();
?>
