<?php
session_start();

include 'includes/config.php';
require 'includes/sendOTPEmail.php';

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    $query = $conn->query("SELECT * FROM users WHERE email = '$email'");

    if ($query->num_rows > 0) {
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $email;

        $conn->query("UPDATE users SET otp = '$otp', otp_expire = NOW() + INTERVAL 5 MINUTE WHERE email = '$email'");

        if (sendOTPEmail($email, $otp)) {
            header("Location: verify-otp.php");
            exit();
        } else {
            $msg = "Failed to send OTP. Please try again.";
        }
    } else {
        $msg = "Email not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password | StudyHub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/log2.css">
</head>
<body>
<div id="loader">
  <i class="fa-solid fa-spinner fa-spin loader-icon"></i>
  <p>Sending request...</p>
</div>

<div class="container auth-shell">
  <div class="row justify-content-center w-100">
    <div class="col-12 col-md-10 col-lg-8 col-xl-6">
      <div class="form-box mx-auto">
        <div class="text-center mb-4">
          <div class="logo justify-content-center">
            <span class="logo-mark"><i class="fa-solid fa-book"></i></span>
            <span>StudyHub</span>
          </div>
          <h2>Forgot Password</h2>
          <p class="section-copy mb-0">Enter your registered email and we’ll send a one-time verification code.</p>
        </div>

        <?php if ($msg): ?>
          <div class="alert alert-info"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <form method="POST">
          <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" required placeholder="your@email.com">
          </div>

          <button type="submit" class="btn btn-primary w-100">Send OTP</button>

          <p class="auth-links mt-3 text-center mb-0">
            <a href="login.php" class="load-effect">Back to Login</a>
          </p>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  const form = document.querySelector('form');
  const loader = document.getElementById('loader');

  form?.addEventListener('submit', function () {
    loader.style.display = 'flex';
  });

  document.querySelectorAll('.load-effect').forEach((link) => {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      loader.style.display = 'flex';
      setTimeout(() => {
        window.location.href = this.href;
      }, 350);
    });
  });

  window.addEventListener('pageshow', function () {
    if (loader) {
      loader.style.display = 'none';
    }
  });

  window.addEventListener('load', function () {
    if (loader) {
      loader.style.display = 'none';
    }
  });
</script>
</body>
</html>
