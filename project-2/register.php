<?php
include 'includes/config.php';

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    $role     = $_POST['role'];

    // check empty fields
    if (empty($name) || empty($email) || empty($password) || empty($confirm) || empty($role)) {
        $msg = "All fields are required!";
    }
    // check email format
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Invalid email format!";
    }
    // Validate role
    elseif (!in_array($role, ['admin', 'student'])) {
        $msg = "Invalid role selected!";
    }
  
    elseif ($password !== $confirm) {
        $msg = "Passwords do not match!";
    } else {
      

        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Check for duplicate email
        $check = $conn->query("SELECT * FROM users WHERE email='$email'");
        if ($check->num_rows > 0) {
            $msg = "Email already registered!";
        } else {
      


            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hash, $role);
            if ($stmt->execute()) {
                $msg = "Registered successfully! <a href='login.php' class='load-effect'>Login here</a>.";
            } else {
                $msg = "Something went wrong!";
            }
        }
    }
}


?>








<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register | NoteMarket</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- âœ… Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- âœ… Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <!-- âœ… Custom Styles -->
  <link rel="stylesheet" href="assets/log2.css">


</head>
<body>

<!-- ðŸ”„ Page Loader -->
<div id="loader">
   <i class="fa-solid fa-spinner fa-spin loader-icon"></i>
  <p>Please wait...</p>
</div>

<div class="container-fluid hero">
  <div class="container">
    <div class="row align-items-center">
      <!-- LEFT SIDE -->
      <div class="col-md-6 text-center text-md-start hero-text">
        <div class="logo">NoteHub</div>
        <h1 class="mb-3">Smart Learning Starts Here</h1>
        <p class="mb-4">Access free and premium handwritten notes for Programming, Networking, and more. Simplify your learning and succeed faster with quality study material.</p>
        <img src="https://live.staticflickr.com/65535/50216459188_fd371aa854.jpg" class="img-fluid" alt="E-learning">
      </div>

      <!-- RIGHT SIDE - Form -->
<div class="col-md-6">
  <div class="form-box mx-auto">
    <h2 class="mb-3">Create Your Account</h2>
    <?php if ($msg): ?>
      <div class="alert alert-info"><?php echo $msg; ?></div>
    <?php endif; ?>

    <form method="POST" id="registerForm">
      <div class="mb-3">
        <label>Full Name</label>
        <input type="text" name="name" class="form-control" required placeholder="Your full name">
      </div>

      <div class="mb-3">
        <label>Email address</label>
        <input type="email" name="email" class="form-control" required placeholder="Enter your email">
      </div>

      <div class="mb-3">
        <label>Password</label>
        <div class="input-group">
          <input type="password" name="password" class="form-control" id="password" required placeholder="Create password">
          <button class="btn btn-outline-secondary" type="button" id="togglePassword">
            <i class="fa-solid fa-eye"></i>
          </button>
        </div>
      </div>

      <div class="mb-3">
        <label>Confirm Password</label>
        <div class="input-group">
          <input type="password" name="confirm_password" class="form-control" id="confirmPassword" required placeholder="Re-type password">
          <button class="btn btn-outline-secondary" type="button" id="toggleConfirm">
            <i class="fa-solid fa-eye"></i>
          </button>
        </div>
      </div>

      <!-- ðŸ‘‡ Account Type Dropdown -->
    <div class="mb-3">
  <label>Account Type</label>
  <select name="role" class="form-control" required>
    <option value="">-- Select Role --</option>
    <option value="student">Student</option>
    <option value="admin" disabled>Admin (Only for system use)</option>
  </select>
</div>


      <button type="submit" class="btn btn-primary w-100">Register</button>
    </form>

    <p class="mt-3 text-center">
      Already have an account? <a href="login.php" class="load-effect">Login here</a>
    </p>
  </div>
</div>

<!-- âœ… JS -->
<script>
  // Toggle password visibility
  document.getElementById('togglePassword').onclick = function () {
    const input = document.getElementById('password');
    input.type = input.type === 'password' ? 'text' : 'password';
    this.querySelector('i').classList.toggle('fa-eye');
    this.querySelector('i').classList.toggle('fa-eye-slash');
  };

  document.getElementById('toggleConfirm').onclick = function () {
    const input = document.getElementById('confirmPassword');
    input.type = input.type === 'password' ? 'text' : 'password';
    this.querySelector('i').classList.toggle('fa-eye');
    this.querySelector('i').classList.toggle('fa-eye-slash');
  };

  // Show loader on form submit


  // Form submit loader
  document.getElementById('registerForm')?.addEventListener('submit', function () {
    document.getElementById('loader').style.display = 'flex';
  });

  // Link click loader
  document.querySelectorAll('a.load-effect').forEach(link => {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      document.getElementById('loader').style.display = 'flex';
      setTimeout(() => {
        window.location.href = this.href;
      }, 1500);
    });
  });

  window.addEventListener("pageshow", function () {
  const loader = document.getElementById('loader');
  if (loader) loader.style.display = 'none';
});

</script>

</body>
</html>
