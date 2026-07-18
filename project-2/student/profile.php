<?php
// student/profile.php

// Step 1: Start session and protect page
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Step 2: Include database configuration
include '../includes/config.php';  // Ensure this file sets up $conn as your DB connection

// Step 3: Fetch current user data from database using MySQLi
// Adjust the selected columns to match your 'users' table structure
$user_id = $_SESSION['user_id'];
// Example: if your table columns are 'name', 'email', and 'reg_date', update accordingly
$sql = "SELECT name, email /*, reg_date */ FROM users WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
} else {
    // Handle prepare error
    die('Database query error: ' . $conn->error);
}

// If user not found, force logout
if (!$user) {
    header("Location: logout.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Profile | NoteMarket</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  
  <!-- Step 4: Navbar with back and logout -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="dashboard.php">⬅️ Dashboard</a>
    <ul class="navbar-nav ms-auto">
      <li class="nav-item">
        <a href="logout.php" class="btn btn-danger btn-sm px-3 py-1 logout-btn">
          🔓 Logout
        </a>
      </li>
    </ul>
  </div>
</nav>


  <!-- Step 5: Profile content -->
  <div class="container mt-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <h2 class="card-title">My Profile</h2>
        <table class="table mt-4">
          <tr><th>Name:</th><td><?= htmlspecialchars($user['name']); ?></td></tr>
          <tr><th>Email:</th><td><?= htmlspecialchars($user['email']); ?></td></tr>
          <?php /*
          <tr><th>Member Since:</th>
              <td><?= date('F j, Y', strtotime($user['reg_date'])); ?></td>
          </tr>
          */ ?>
        </table>
 
        
      </div>
    </div>
  </div>

  


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- <script>
      // Loader logic when clicking a link
  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('a.load-effect').forEach(link => {
      link.addEventListener('click', function (e) {
        e.preventDefault();
        document.getElementById('loader').style.display = 'flex';

        setTimeout(() => {
          window.location.href = this.href;
        }, 1500);
      });
    });
  });
  </script> -->
</body>
</html>
