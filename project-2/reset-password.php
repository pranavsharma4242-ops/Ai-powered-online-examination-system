<?php
include 'includes/config.php';
session_start();

$msg = "";

if (!isset($_SESSION['verified']) || $_SESSION['verified'] !== true) {
  die("Access denied. Please verify OTP first.");
}

$email = $_SESSION['email'];  // Now email is secure from session

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $pass1 = $_POST['password'];
  $pass2 = $_POST['confirm_password'];

  if ($pass1 !== $pass2) {
    $msg = "Passwords do not match!";
  } else {
    $hash = password_hash($pass1, PASSWORD_DEFAULT);
    $conn->query("UPDATE users SET password='$hash' WHERE email='$email'");
    $conn->query("DELETE FROM password_resets WHERE email='$email'");
    $msg = "✅ Password changed successfully! <a href='login.php'>Login now</a>";

    // Destroy session to prevent reuse
    session_unset();
    session_destroy();
  }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password | NoteMarket</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/log.css">

  <style>
    #loader {
      position: fixed;
      top: 0; left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(255, 255, 255, 0.9);
      display: none;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      z-index: 9999;
    }
    .loader-icon {
      font-size: 48px;
      color: #3f51b5;
    }
    .dark-mode {
      background-color: #121212 !important;
      color: #fff !important;
    }
    .dark-mode .form-box {
      background-color: #1e1e1e;
    }
    .dark-mode input {
      background-color: #333;
      color: #fff;
      border: 1px solid #555;
    }
  </style>
</head>
<body>

<!-- Loader -->
<div id="loader">
  <i class="fa-solid fa-spinner fa-spin loader-icon"></i>
  <p>Resetting your password...</p>
</div>

<!-- Dark Mode Toggle -->

<!-- Reset Password Form -->
<div class="container hero">
  <div class="form-box mx-auto">
    <div class="text-center mb-4">
      <div class="logo fs-3 fw-bold text-primary">📚 NoteMarket</div>
      <h2 class="mb-1">Reset Password</h2>
      <p class="text-muted">Enter a new password for <strong><?php echo htmlspecialchars($email); ?></strong></p>
    </div>

    <?php if ($msg): ?>
      <div class="alert alert-info"><?php echo $msg; ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label>New Password</label>
        <div class="input-group">
          <input type="password" name="password" id="password" class="form-control" required placeholder="New password">
          <button class="btn btn-outline-secondary" type="button" id="togglePassword"><i class="fa-solid fa-eye"></i></button>
        </div>
      </div>

      <div class="mb-3">
        <label>Confirm Password</label>
        <div class="input-group">
          <input type="password" name="confirm_password" id="confirmPassword" class="form-control" required placeholder="Re-type password">
          <button class="btn btn-outline-secondary" type="button" id="toggleConfirm"><i class="fa-solid fa-eye"></i></button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100">Reset Password</button>
    </form>
  </div>
</div>

<!-- JS -->
<script>
  // Password toggle
  document.getElementById('togglePassword').addEventListener('click', () => {
    let input = document.getElementById('password');
    let type = input.type === 'password' ? 'text' : 'password';
    input.type = type;
    this.querySelector('i').classList.toggle('fa-eye');
    this.querySelector('i').classList.toggle('fa-eye-slash');
  });

  document.getElementById('toggleConfirm').addEventListener('click', () => {
    let input = document.getElementById('confirmPassword');
    let type = input.type === 'password' ? 'text' : 'password';
    input.type = type;
    this.querySelector('i').classList.toggle('fa-eye');
    this.querySelector('i').classList.toggle('fa-eye-slash');
  });

  // Loader
  const form = document.querySelector('form');
  form.addEventListener('submit', () => {
    document.getElementById('loader').style.display = 'flex';
  });

  // Dark Mode
  const toggleDark = document.getElementById('toggleDark');
  toggleDark.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('dark-mode', document.body.classList.contains('dark-mode'));
  });

  window.addEventListener("pageshow", function () {
  const loader = document.getElementById('loader');
  if (loader) loader.style.display = 'none';
});
</script>

</body>
</html>
