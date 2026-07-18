<?php
session_start();

if (!isset($_SESSION['email'])) {
    die("Session expired. Please try again from the beginning.");
}

$email = $_SESSION['email'] ?? '';
include 'includes/config.php';

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_otp = $_POST['otp'];
    $email = $_SESSION['email'];
    $current_time = date("Y-m-d H:i:s");

    $query = $conn->query("SELECT * FROM users WHERE email = '$email' AND otp = '$user_otp' AND otp_expire >= '$current_time'");

    if ($query->num_rows == 1) {
        $_SESSION['verified'] = true;
        header("Location: reset-password.php");
        exit();
    } else {
        $msg = "Invalid or expired OTP.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Verify OTP | StudyHub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/log.css">
</head>
<body>
<div id="loader">
  <i class="fa-solid fa-spinner fa-spin loader-icon"></i>
  <p>Verifying OTP...</p>
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
          <h2>OTP Verification</h2>
          <p class="section-copy mb-0">We sent a code to <strong><?php echo htmlspecialchars($email); ?></strong></p>
        </div>

        <?php if (isset($_SESSION['otp_resent'])): ?>
          <div class="alert alert-info">
            <?php echo htmlspecialchars($_SESSION['otp_resent']); unset($_SESSION['otp_resent']); ?>
          </div>
        <?php endif; ?>

        <?php if ($msg): ?>
          <div class="alert alert-warning"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <form method="POST" id="verifyForm">
          <div class="mb-3">
            <label class="form-label">Enter OTP</label>
            <input type="text" name="otp" class="form-control" required placeholder="6-digit code">
          </div>
          <button type="submit" class="btn btn-primary w-100">Verify OTP</button>
        </form>

        <form method="POST" action="resend.php" class="text-center mt-3" id="resendForm">
          <button type="submit" class="btn-linkish border-0 bg-transparent">Resend OTP</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  const loader = document.getElementById('loader');

  document.getElementById('verifyForm')?.addEventListener('submit', function () {
    loader.style.display = 'flex';
  });

  document.getElementById('resendForm')?.addEventListener('submit', function () {
    loader.style.display = 'flex';
  });

  window.addEventListener('pageshow', function () {
    if (loader) {
      loader.style.display = 'none';
    }
  });
</script>
</body>
</html>
