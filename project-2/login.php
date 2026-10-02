<?php
session_start();
include 'includes/config.php';

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (!empty($user['is_blocked'])) {
            $msg = "Your account has been blocked by the admin.";
        } elseif (password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['email']   = $user['email'];

  
            if ($user['role'] === 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: student/dashboard.php");
            }
            exit();
        } else {
            $msg = " Invalid password!";
        }
    } else {
        $msg = "User not found!";
    }
}
?>








<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login | NoteMarket</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">


  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />

  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  
  <link rel="stylesheet" href="assets/log2.css">
</head>
<body>


<!-- <div class="text-end p-2">
  <button class="btn btn-sm btn-outline-dark" id="toggleDark">ðŸŒ™ Dark Mode</button>
</div> -->


<!--  Loader -->
<div id="loader">
  <i class="fa-solid fa-spinner fa-spin loader-icon"></i>
  <p>Please wait...</p>
</div>

<!-- Page Content -->
<div class="container-fluid hero">
  <div class="container">
    <div class="row align-items-center">
      
      <!-- Left Side -->
      <div class="col-md-6 text-center text-md-start hero-text">
        <div class="logo"> NoteMarket</div>
        <h1 class="mb-1">Smart Learning Starts Here</h1>
        <p class="mb-3">Access free and premium handwritten notes for Programming, Networking, and more. Simplify your learning and succeed faster with quality study material.</p>
        <img src="https://live.staticflickr.com/65535/50216459188_fd371aa854.jpg" class="img-fluid" alt="E-learning">
      </div>

      <!-- Right Side - Login Form -->
      <div class="col-md-6">
        <div class="form-box mx-auto">
          <h2 class="mb-3">Login to Continue</h2>

          <?php if ($msg): ?>
            <div class="alert alert-warning"><?php echo $msg; ?></div>
          <?php endif; ?>

               <form method="POST">
            <div class="mb-3">
              <label>Email address</label>
              <input type="email" name="email" class="form-control" required placeholder="Enter your email">
            </div>

            <div class="mb-3">
              <label>Password</label>
              <div class="input-group">
                <input type="password" name="password" class="form-control" id="password" required placeholder="Your password">
                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                  <i class="fa-solid fa-eye"></i>
                </button>
              </div>
            </div>

            <p class="mt-2 text-end">
           <a href="forgot_pass.php" class="text-decoration-none load-effect">Forgot Password?</a>
             </p>


            <button type="submit" class="btn btn-primary w-100">Login</button>
          </form>
          <p class="mt-3 text-center">
            Don't have an account? <a href="register.php" class="load-effect">Register now</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>






<script>
  const togglePassword = document.getElementById('togglePassword');
  const passwordInput = document.getElementById('password');
  togglePassword.addEventListener('click', function () {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    const icon = this.querySelector('i');
    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
  });



  // Loader on link click
  document.querySelectorAll('a.load-effect').forEach(link => {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      document.getElementById('loader').style.display = 'flex';
      setTimeout(() => {
        window.location.href = this.href;
      }, 800); 
    });
  });

  // Loader on form submit
  const form = document.querySelector('form');
  form?.addEventListener('submit', function () {
    document.getElementById('loader').style.display = 'flex';
  })




// Hide loader on page load

  window.addEventListener("pageshow", function (event) {
    const loader = document.getElementById("loader");
    if (loader) {
      loader.style.display = "none";
    }
  });


  
  window.addEventListener("load", function () {
    const loader = document.getElementById("loader");
    if (loader) {
      loader.style.display = "none";
    }
  });







</script>

</body>
</html>
