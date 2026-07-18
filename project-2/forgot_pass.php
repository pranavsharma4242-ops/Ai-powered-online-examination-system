<?php
session_start(); // ✅ ADD THIS LINE AT THE TOP

include 'includes/config.php';
require 'includes/sendOTPEmail.php'; // include your email sending logic

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    $query = $conn->query("SELECT * FROM users WHERE email = '$email'");

    if ($query->num_rows > 0) {
        $otp = rand(100000, 999999);
        
        // ✅ Save to SESSION
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $email;

        // ✅ Store OTP in DB with expiry
        $conn->query("UPDATE users SET otp = '$otp', otp_expire = NOW() + INTERVAL 5 MINUTE WHERE email = '$email'");

        // ✅ Send OTP
        if (sendOTPEmail($email, $otp)) {
            header("Location: verify-otp.php");
            exit();
        } else {
            $msg = "❌ Failed to send OTP. Please try again.";
        }
    } else {
        $msg = "❌ Email not found!";
    }
}
?>







<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password | NoteMarket</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Prevent page from being cached -->
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />


  <!-- Bootstrap & Font Awesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <!-- Custom CSS -->
  <!-- <link rel="stylesheet" href="assets/log.css"> -->

  <style>
    #loader {
      position: fixed;
      z-index: 9999;
      background: rgba(255, 255, 255, 0.9);
      inset: 0;
      display: none;
      align-items: center;
      justify-content: center;
      flex-direction: column;
    }
    .loader-icon {
      font-size: 2rem;
      color: #3f51b5;
      animation: spin 1s linear infinite;
    }
    @keyframes spin {
      0% { transform: rotate(0); }
      100% { transform: rotate(360deg); }
    }
  </style>
</head>

<body>

<!-- 🔄 Page Loader -->
<div id="loader">
  <i class="fa-solid fa-spinner fa-spin loader-icon"></i>
  <p class="mt-3">Sending request...</p>
</div>

<!-- 🔐 Forgot Password Form -->
<div class="container hero py-5">
  <div class="form-box mx-auto shadow-lg rounded-4 p-4 p-md-5 bg-white" style="max-width: 480px;">
    
    <!-- Logo and heading -->
    <div class="text-center mb-4">
      <div class="logo fs-3 fw-bold text-primary">📚 NoteMarket</div>
      <h2 class="mb-1">Forgot Password</h2>
      <p class="text-muted small">Enter your registered email address</p>
    </div>

    <!-- Success/Error Message -->
    <?php if ($msg): ?>
      <div class="alert alert-info"><?php echo $msg; ?></div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" required placeholder="your@email.com">
      </div>

      <button type="submit" class="btn btn-primary w-100">Send OTP</button>

      <p class="mt-3 text-center">
        <a href="login.php" class="load-effect text-decoration-none">← Back to Login</a>
      </p>
    </form>

  </div>
</div>


<!-- 🔁 Loader JS -->
<script>
  const form = document.querySelector("form");
  const loader = document.getElementById("loader");

  form.addEventListener("submit", function () {
    loader.style.display = "flex";
  });

  // Also show loader for any link with .load-effect
  document.querySelectorAll('.load-effect').forEach(link => {
    link.addEventListener('click', function () {
      loader.style.display = 'flex';
    });
  });




  
 window.addEventListener("pageshow", function (event) {
    const loader = document.getElementById("loader");
    if (loader) {
      loader.style.display = "none";
    }
  });

  // Also hide loader on load (safety fallback)
  window.addEventListener("load", function () {
    const loader = document.getElementById("loader");
    if (loader) {
      loader.style.display = "none";
    }
  });

</script>

</body>
</html>
