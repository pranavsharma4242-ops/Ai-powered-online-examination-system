<?php
include '../includes/config.php';
include '../includes/auth_student.php';

$type = $_GET['type'] ?? 'free';
$type = ($type === 'premium') ? 'premium' : 'free'; // sanitize

$stmt = $conn->prepare("SELECT * FROM notes_final WHERE note_type = ? ORDER BY upload_date DESC");
$stmt->bind_param("s", $type);
$stmt->execute();
$result = $stmt->get_result();
?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo ucfirst($type); ?> Notes | NoteMarket</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body {
      background-color: #f5f7fa;
    }
    .note-card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.note-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.1);
}

.card-title {
  font-size: 1.1rem;
  font-weight: 600;
}

.btn-success {
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

.btn-success:hover {
  background-color: #198754;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

    
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand fw-semibold fs-4" href="dashboard.php">
      📚 NoteMarket
    </a>

    <!-- Hamburger for mobile -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
      aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Menu Items -->
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ms-auto text-center">
        <li class="nav-item mx-1 my-2 my-lg-0">
          <a class="btn btn-outline-light btn-sm px-4 w-100 w-lg-auto" href="dashboard.php">
            <i class="bi bi-house-door"></i> Home
          </a>
        </li>
        <li class="nav-item mx-1 my-2 my-lg-0">
          <a class="btn btn-warning text-dark btn-sm px-4 w-100 w-lg-auto" href="profile.php">
            <i class="bi bi-person-circle"></i> 
            <?php echo htmlspecialchars($_SESSION['name']); ?>
          </a>
        </li>
        <li class="nav-item mx-1 my-2 my-lg-0">
          <a class="btn btn-danger btn-sm px-4 logout-btn w-100 w-lg-auto" href="logout.php">
            <i class="bi bi-box-arrow-right"></i> Logout
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>





<!-- Mobile & Tablet Optimized Search Bar -->
<div class="container mt-4">
  <form action="notes.php" method="GET" class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-7">
      <div class="input-group shadow-sm">
        <input type="text" name="search" class="form-control form-control-lg" placeholder="🔍 Search by subject or topic..." required>
        <button class="btn btn-primary" type="submit">
          <i class="bi bi-search"></i> Search
        </button>
      </div>
    </div>
  </form>
</div>



<!-- Page Content -->
<div class="container py-5">
  <h3 class="mb-4 text-primary"><?php echo ucfirst($type); ?> Notes</h3>

  <?php if ($result->num_rows > 0): ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
      <?php while ($row = $result->fetch_assoc()): 
        // File & Image Handling
        $file_path = "../uploads/" . $row['filename'];
        $file_size = file_exists($file_path) ? round(filesize($file_path) / 1048576, 2) . ' MB' : 'N/A';

        $image_file = "../image_path/" . $row['image_path'];
        $image_path = (!empty($row['image_path']) && file_exists($image_file)) 
                      ? $image_file 
                      : 'assets/pdf-cover.png';
      ?>
        <div class="col">
          <div class="card h-100 note-card border-0 shadow-sm rounded-4 overflow-hidden">
            
            <!-- Card Image -->
            <img src="<?php echo $image_path; ?>" class="card-img-top img-fluid" alt="Note Image"
                 style="height: 180px; object-fit: cover;">

            <div class="card-body d-flex flex-column">
              <h5 class="card-title text-primary fw-semibold">
                📘 <?php echo htmlspecialchars($row['subject']); ?>
              </h5>
              <p class="mb-1"><strong>📌 Topic:</strong> <?php echo htmlspecialchars($row['topic']); ?></p>
              <p class="text-muted small mb-1">
                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($row['uploaded_by']); ?>
              </p>
              <p class="text-muted small mb-1">
                <i class="bi bi-calendar-event"></i> <?php echo date("d M Y", strtotime($row['upload_date'])); ?>
              </p>
              <p class="text-muted small mb-3">
                <i class="bi bi-file-earmark-pdf"></i> File Size: <?php echo $file_size; ?>
              </p>

              <!-- Download Button -->
              <a href="<?php echo $file_path; ?>" target="_blank" class="btn btn-success mt-auto w-100">
                <i class="bi bi-download"></i> Download PDF
              </a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="alert alert-warning text-center rounded-4 shadow-sm">
      No <?php echo $type; ?> notes available yet.
    </div>
  <?php endif; ?>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
