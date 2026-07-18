<?php

include '../includes/config.php';
include '../includes/auth_admin.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | NoteMarket</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f5f7fa;
      font-family: 'Segoe UI', sans-serif;
    }
    .card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    .dashboard-header {
      font-size: 1.75rem;
      font-weight: 600;
    }
    .navbar-brand {
      font-weight: bold;
    }

  .table th, .table td {
    vertical-align: middle;
  }
  
  @media (max-width: 576px) {
    .table td, .table th {
      font-size: 0.85rem;
      white-space: nowrap;
    }

    .table img {
      width: 50px;
    }

    .btn-sm {
      font-size: 0.75rem;
      padding: 4px 6px;
    }

    .badge {
      font-size: 0.75rem;
    }

    .card-title {
      font-size: 1.1rem;
    }
  }

  /* Full-screen loader wrapper */
.loader-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(255, 255, 255, 0.8);
  z-index: 9999;
  display: none; /* default hidden */
  justify-content: center;
  align-items: center;
}

/* Spinner design */
.loader {
  width: 50px;
  aspect-ratio: 1;
  border-radius: 50%;
  border: 8px solid;
  border-color: #000 #0000;
  animation: l1 1s infinite linear;
}

@keyframes l1 {
  to {
    transform: rotate(180deg); /* .5turn = 180deg */
  }
}


</style>

</head>
<body>

<div class="loader-overlay" id="admin-loader">
  <div class="loader"></div>
</div>


<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top">
  <div class="container">
    <!-- Logo/Brand -->
    <a class="navbar-brand fw-bold" href="#">📚 NoteMarket Admin</a>

    <!-- Hamburger Toggler -->
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon fs-3"></span>
    </button>

    <!-- Collapsible Menu -->
    <div class="collapse navbar-collapse justify-content-end" id="adminNavbar">
      <ul class="navbar-nav align-items-lg-center gap-2 mt-3 mt-lg-0">
        <li class="nav-item">
          <a href="upload_note.php" class="admin-load btn btn-outline-light w-100 w-lg-auto">
            <i class="fas fa-upload me-1"></i> Upload Note
          </a>
        </li>
        <li class="nav-item">
          <a href="logout.php" class="admin-load btn btn-outline-danger w-100 w-lg-auto">
            <i class="fas fa-sign-out-alt me-1"></i> Logout
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>



<!-- Dashboard Content -->
<div class="container my-5">
  <!-- Header -->
  <div class="text-center mb-4">
    <h2 class="fw-bold">👋 Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> (Admin)</h2>
    <p class="text-muted">Here's a quick look at the latest notes uploaded.</p>
  </div>

  <!-- Notes Table Card -->
  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="card-title mb-4">📝 Recently Uploaded Notes</h5>

      <!-- Responsive Table -->
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle text-center">
          <thead class="table-dark">
            <tr>
              <th>📘 Subject</th>
              <th>🧾 Topic</th>
              <th>🖼 Cover</th>
              <th>💰 Price</th>
              <th>📎 File</th>
              <th>📅 Date</th>
              <th>⚙️ Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $result = $conn->query("SELECT * FROM notes_final ORDER BY upload_date DESC LIMIT 5");
            if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                $coverImg = $row['image_path']
                  ? "<a href='../image_path/{$row['image_path']}' target='_blank'>
                      <img src='../image_path/{$row['image_path']}' alt='cover' class='img-fluid rounded shadow-sm' style='width: 70px; height: auto;'>
                    </a>"
                  : "<span class='text-muted small'>No Image</span>";

                $price = ($row['price'] > 0)
                  ? "<span class='badge bg-warning text-dark'>₹" . number_format($row['price'], 2) . "</span>"
                  : "<span class='badge bg-success'>Free</span>";

                echo "<tr>
                        <td class='text-break'>" . htmlspecialchars($row['subject']) . "</td>
                        <td class='text-break'>" . htmlspecialchars($row['topic']) . "</td>
                        <td>$coverImg</td>
                        <td>$price</td>
                        <td>
                          <a href='../uploads/{$row['filename']}' target='_blank' class='admin-load btn btn-sm btn-primary'>
                            <i class='bi bi-file-earmark-pdf'></i> View
                          </a>
                        </td>
                        <td class='small'>" . date("d M Y", strtotime($row['upload_date'])) . "</td>
                        <td>
                          <form method='POST' action='delete_note.php' onsubmit=\"return confirm('Are you sure you want to delete this note?');\">
                            <input type='hidden' name='note_id' value='{$row['id']}'>
                            <input type='hidden' name='filename' value='{$row['filename']}'>
                            <button type='submit' class='btn btn-sm btn-danger'>
                              <i class='bi bi-trash'></i> Delete
                            </button>
                          </form>
                        </td>
                      </tr>";
              }
            } else {
              echo "<tr><td colspan='7' class='text-muted'>No notes uploaded yet.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
  // Hide loader on page load (including back)
  window.addEventListener('pageshow', function () {
    const loader = document.getElementById('admin-loader');
    if (loader) loader.style.display = 'none';
  });

  // Show loader on internal admin links (optional)
  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('a.admin-load').forEach(link => {
      link.addEventListener('click', function (e) {
        e.preventDefault();
        const loader = document.getElementById('admin-loader');
        if (loader) loader.style.display = 'flex';

        setTimeout(() => {
          window.location.href = this.href;
        }, 1200); // adjust delay if needed
      });
    });
  });
</script>




<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
