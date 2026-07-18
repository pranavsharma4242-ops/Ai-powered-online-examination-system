<?php
include '../includes/config.php';
include '../includes/auth_student.php'; // Ensure only students can access
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Dashboard | NoteMarket</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap CSS + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Custom Styling -->
  <style>
    body {
      background: #f4f6f9;
      font-family: 'Segoe UI', sans-serif;
    }
    .navbar-brand {
      font-weight: bold;
      font-size: 1.4rem;
      letter-spacing: 1px;
    }
    .dashboard-heading {
      font-size: 1.8rem;
      font-weight: 600;
      margin-bottom: 10px;
    }
    /* .card {
      border: none;
      border-radius: 12px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card:hover {
      transform: translateY(180px);
      box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    }
    .card-title {
      font-size: 1.3rem;
      font-weight: 600;
    } */
    .btn-sm i {
      margin-right: 4px;
    }

    /* HTML: <div class="loader"></div> */
/* HTML: <div class="loader"></div> */
     /* Fullscreen overlay */
.loader-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.8);
  z-index: 9999;
  display: none;
  justify-content: center;
  align-items: center;
}

/* Spinner styling */
.spinner {
  width: 60px;
  height: 60px;
  border: 6px solid #add8e6;
  border-top: 6px solid #007bff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

/* Animation */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
.hover-shadow:hover {
  transform: translateY(-4px);
  transition: all 0.3s ease-in-out;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.card-title {
  font-weight: 600;
}

.dashboard-heading {
  font-size: 1.8rem;
}

@media (max-width: 576px) {
  .dashboard-heading {
    font-size: 1.5rem;
  }
}



  </style>
</head>
<body>
  <!-- ✅ Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
  <div class="container">
    <!-- Logo -->
    <a class="navbar-brand fw-bold fs-4" href="dashboard.php">
      📚 NoteMarket
    </a>

    <!-- Mobile Toggle -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
      aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Menu Items -->
    <div class="collapse navbar-collapse" id="mainNavbar">
      <ul class="navbar-nav ms-auto align-items-center text-center">
        <!-- Profile -->
        <li class="nav-item mx-1 my-2 my-lg-0">
          <a class="load-effect btn btn-warning text-dark btn-sm px-4 fw-semibold w-100 w-lg-auto" href="profile.php">
            <i class="bi bi-person-circle me-1"></i> 
            <?php echo htmlspecialchars($_SESSION['name']); ?>
          </a>
        </li>
        <!-- Logout -->
        <li class="nav-item mx-1 my-2 my-lg-0">
          <a class="load-effect btn btn-danger btn-sm px-4 logout-btn fw-semibold w-100 w-lg-auto" href="logout.php">
            <i class="bi bi-box-arrow-right me-1"></i> Logout
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- ✅ Main Dashboard Content -->
<div class="container my-5">
  <div class="text-center mb-4">
    <h2 class="dashboard-heading fw-bold text-primary">
      👋 Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>
    </h2>
    <p class="text-muted">Select a category to explore handwritten study notes.</p>
  </div>

  <!-- Notes Section -->
  <div class="row g-4">
    <!-- Free Notes -->
    <div class="col-md-6">
      <a href="notes.php?type=free" class=" load-effect text-decoration-none">
        <div class="card h-100 shadow-sm border-0 hover-shadow transition">
          <div class="card-body">
            <h5 class="card-title text-primary">📘 Free Notes</h5>
            <p class="card-text text-muted">Browse and download freely available handwritten notes from students.</p>
          </div>
        </div>
      </a>
    </div>

    <!-- Premium Notes -->
    <div class="col-md-6">
      <a href="notes.php?type=premium" class="load-effect text-decoration-none">
        <div class="card h-100 bg-light shadow-sm border-0 hover-shadow transition">
          <div class="card-body">
            <h5 class="card-title text-dark">💎 Premium Notes</h5>
            <p class="card-text text-muted">Unlock high-quality premium notes. (Paid feature coming soon)</p>
          </div>
        </div>
      </a>
    </div>
  </div>
</div>


<div class="loader-overlay" id="loader">
  <div class="spinner"></div>
</div>

<!-- Responsive Dashboard Navbar -->


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Hide loader on page load (even after back/forward)
  window.addEventListener('pageshow', function () {
    const loader = document.getElementById('loader');
    if (loader) {
      loader.style.display = 'none';
    }
  });

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
</script>



</body>
</html>
