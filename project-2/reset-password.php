<?php
include 'includes/config.php';
session_start();

$msg = "";

if (!isset($_SESSION['verified']) || $_SESSION['verified'] !== true) {
    die("Access denied. Please verify OTP first.");
}

$email = $_SESSION['email'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pass1 = $_POST['password'];
    $pass2 = $_POST['confirm_password'];

    if ($pass1 !== $pass2) {
        $msg = "Passwords do not match.";
    } else {
        $hash = password_hash($pass1, PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password='$hash' WHERE email='$email'");
        $conn->query("DELETE FROM password_resets WHERE email='$email'");
        $msg = "Password changed successfully. You can log in now.";

        session_unset();
        session_destroy();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password | StudyHub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/log2.css">
</head>
<body>
<div id="loader">
  <i class="fa-solid fa-spinner fa-spin loader-icon"></i>
  <p>Resetting your password...</p>
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
          <h2>Reset Password</h2>
          <p class="section-copy mb-0">Create a new password for <strong><?php echo htmlspecialchars($email); ?></strong></p>
        </div>

        <?php if ($msg): ?>
          <div class="alert alert-info">
            <?php echo htmlspecialchars($msg); ?>
            <?php if (strpos($msg, 'log in now') !== false): ?>
              <div class="mt-2"><a href="login.php">Go to Login</a></div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <form method="POST">
          <div class="mb-3">
            <label class="form-label">New Password</label>
            <div class="input-group">
              <input type="password" name="password" id="password" class="form-control" required placeholder="New password">
              <button class="btn btn-outline-secondary" type="button" id="togglePassword"><i class="fa-solid fa-eye"></i></button>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <div class="input-group">
              <input type="password" name="confirm_password" id="confirmPassword" class="form-control" required placeholder="Re-type password">
              <button class="btn btn-outline-secondary" type="button" id="toggleConfirm"><i class="fa-solid fa-eye"></i></button>
            </div>
          </div>

          <button type="submit" class="btn btn-primary w-100">Reset Password</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  document.getElementById('togglePassword')?.addEventListener('click', function () {
    const input = document.getElementById('password');
    input.type = input.type === 'password' ? 'text' : 'password';
    const icon = this.querySelector('i');
    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
  });

  document.getElementById('toggleConfirm')?.addEventListener('click', function () {
    const input = document.getElementById('confirmPassword');
    input.type = input.type === 'password' ? 'text' : 'password';
    const icon = this.querySelector('i');
    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
  });

  document.querySelector('form')?.addEventListener('submit', function () {
    document.getElementById('loader').style.display = 'flex';
  });

  window.addEventListener('pageshow', function () {
    const loader = document.getElementById('loader');
    if (loader) {
      loader.style.display = 'none';
    }
  });
</script>
</body>
</html>
