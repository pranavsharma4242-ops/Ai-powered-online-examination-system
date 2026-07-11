


<?php
session_start();

if (!isset($_SESSION['email'])) {
    die("Session expired. Please try again from the beginning.");
}
$email = $_SESSION['email'] ?? ''; // ✅ Fix here

include 'includes/config.php';


$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_otp = $_POST['otp'];
    $email = $_SESSION['email'];  // Make sure this is set when OTP was sent

    $current_time = date("Y-m-d H:i:s");

    // Check if OTP matches and not expired
    $query = $conn->query("SELECT * FROM users WHERE email = '$email' AND otp = '$user_otp' AND otp_expire >= '$current_time'");

    if ($query->num_rows == 1) {
        $_SESSION['verified'] = true;
        header("Location: reset-password.php");
        exit();
    } else {
        $msg = "Invalid or expired OTP!";
    }
    
}
?>








<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Verify OTP | NoteMarket</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/login.css">

  <style>
    .form-box {
      background: #fff;
      border: 1px solid #e0e0e0;
      border-radius: 10px;
      padding: 2rem;
    }

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
    #loader p {
      margin-top: 10px;
      font-weight: 500;
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

    /* #loader2 {
  transition: all 0.3s ease-in-out;
    } */
#otp-loader {
  z-index: 9999;
}



  </style>
</head>
<body>

<!-- Loader -->
<div id="loader">
  <i class="fa-solid fa-spinner fa-spin loader-icon"></i>
  <p>Verifying OTP...</p>
</div>

<!-- OTP Loader -->
<div id="otp-loader" class="d-none position-fixed top-0 start-0 w-100 h-100 bg-white bg-opacity-90 d-flex flex-column justify-content-center align-items-center z-3">
  <i class="fa-solid fa-spinner fa-spin text-primary" style="font-size: 2.5rem;"></i>
  <p class="mt-3 fw-semibold text-primary">Sending OTP... Please wait</p>
</div>




<!-- Dark Mode Toggle -->
<div class="text-end p-3">
  <button class="btn btn-outline-dark btn-sm" id="toggleDark">🌙 Dark Mode</button>
</div>

<!-- OTP Form -->
<div class="container hero">
  <div class="form-box mx-auto p-4 shadow rounded" style="max-width: 450px;">
    <div class="text-center mb-4">
      <div class="logo fs-3 fw-bold text-primary">📚 NoteMarket</div>
      <h2 class="mb-1">OTP Verification</h2>
      <p class="text-muted">We sent a code to <strong><?php echo htmlspecialchars($email); ?></strong></p>
    </div>

    <!-- ✅ Show OTP resend success or error alert -->
    <?php if (isset($_SESSION['otp_resent'])): ?>
      <div class="alert alert-info alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['otp_resent']; unset($_SESSION['otp_resent']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- ✅ Show OTP match fail alert -->
    <?php if ($msg): ?>
      <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <?php echo $msg; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- OTP Verification Form -->
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Enter OTP</label>
        <input type="text" name="otp" class="form-control" required placeholder="6-digit code">
      </div>
      <button type="submit" class="btn btn-primary w-100">Verify OTP</button>
    </form>

    <!-- Resend OTP Form -->
    <form method="POST" action="resend.php" class="text-center mt-3">
      <button type="submit" class="btn btn-link">🔁 Resend OTP</button>
    </form>
  </div>
</div>


<!-- Scripts -->
<script>
  // Show loader
  const form = document.querySelector('form');
  form.addEventListener('submit', () => {
    document.getElementById('loader').style.display = 'flex';
  });

  // Dark mode toggle
  const toggleDark = document.getElementById('toggleDark');
  toggleDark.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('dark-mode', document.body.classList.contains('dark-mode'));
  });

  // Load saved theme
  if (localStorage.getItem('dark-mode') === 'true') {
    document.body.classList.add('dark-mode');
  }
</script>

<script>
  const resendForm = document.querySelector('form[action="resend.php"]');
  if (resendForm) {
    resendForm.addEventListener('submit', function () {
      document.getElementById('otp-loader').classList.remove('d-none');
    });
  }
</script>





</body>
</html>
