<?php
session_start();
include '../includes/config.php';
include '../includes/auth_admin.php';

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = $_POST['subject'];
    $topic = $_POST['topic'];
    $note_type = $_POST['note_type'];
    $price = ($note_type === 'premium') ? ($_POST['price'] ?? 0.00) : 0.00;

    $uploaded_by = $_SESSION['email'];
    $role = $_SESSION['role'];

    $pdf_file = $_FILES['note_file'];
    $image_file = $_FILES['cover_image'];

    // PDF Handling
    $pdf_name = $pdf_file['name'];
    $pdf_tmp = $pdf_file['tmp_name'];
    $pdf_ext = strtolower(pathinfo($pdf_name, PATHINFO_EXTENSION));

    if ($pdf_ext !== 'pdf') {
        $msg = "❌ Only PDF files are allowed!";
    } else {
        // Generate unique file name for PDF
        $new_pdf = uniqid() . ".pdf";
        $pdf_path = "../uploads/" . $new_pdf;

        // Default image path if no image uploaded
        $new_img = null;
        $img_path = null;

        // If image uploaded
        if (!empty($image_file['name'])) {
            $image_name = $image_file['name'];
            $image_tmp = $image_file['tmp_name'];
            $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
            $allowed_image_ext = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($image_ext, $allowed_image_ext)) {
                $msg = "❌ Only JPG, PNG, or WebP images allowed for cover!";
            } else {
                $new_img = uniqid() . "." . $image_ext;
                $img_path = "../image_path/" . $new_img;

                // Move image first
                if (!move_uploaded_file($image_tmp, $img_path)) {
                    $msg = "❌ Failed to upload cover image!";
                    return;
                }
            }
        }

        // Move PDF file
        if (!move_uploaded_file($pdf_tmp, $pdf_path)) {
            $msg = "❌ Failed to upload PDF!";
        } else {
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO notes_final 
                (subject, topic, filename, uploaded_by, role, note_type, price, image_path) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssds", $subject, $topic, $new_pdf, $uploaded_by, $role, $note_type, $price, $new_img);

            if ($stmt->execute()) {
                $msg = "✅ Note uploaded successfully!";
            } else {
                $msg = "❌ Database error: " . $stmt->error;
            }
        }
    }
}
?>




<!DOCTYPE html>
<html>
<head>
  <title>Upload Note | Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
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



<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6">
      <div class="card shadow rounded-4 p-4 border-0">
        <h3 class="text-center mb-4 text-primary fw-bold">📤 Upload New Note</h3>

        <?php if (!empty($msg)): ?>
          <div class="alert alert-info text-center"><?php echo $msg; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
          <!-- Subject -->
          <div class="mb-3">
            <label class="form-label fw-semibold">📘 Subject</label>
            <input type="text" name="subject" class="form-control rounded-3" placeholder="e.g., Computer Networks" required>
          </div>

          <!-- Topic -->
          <div class="mb-3">
            <label class="form-label fw-semibold">🧾 Topic</label>
            <input type="text" name="topic" class="form-control rounded-3" placeholder="e.g., OSI Layers" required>
          </div>

          <!-- Note Type -->
          <div class="mb-3">
            <label class="form-label fw-semibold">🔖 Note Type</label>
            <select name="note_type" id="noteType" class="form-select rounded-3" onchange="togglePriceInput()" required>
              <option value="free">📂 Free Note</option>
              <option value="premium">💎 Premium Note</option>
            </select>
          </div>

          <!-- Price Field (shown only for premium) -->
          <div class="mb-3 d-none" id="priceContainer">
            <label class="form-label fw-semibold">💰 Price (in ₹)</label>
            <input type="number" name="price" class="form-control rounded-3" placeholder="e.g., 50" min="1">
          </div>

          <!-- PDF Upload -->
          <div class="mb-3">
            <label class="form-label fw-semibold">📄 Upload PDF File</label>
            <input type="file" name="note_file" class="form-control rounded-3" accept="application/pdf" required>
            <small class="text-muted">Only PDF files allowed. Max 2MB recommended.</small>
          </div>

          <!-- Cover Image Upload -->
          <div class="mb-3">
            <label class="form-label fw-semibold">🖼 Upload Cover Image (optional)</label>
            <input type="file" name="cover_image" class="form-control rounded-3" accept="image/*" >
            <small class="text-muted">JPG/PNG. Suggested size: 400x200px</small>
          </div>

          <!-- Buttons -->
          <div class="d-flex flex-column flex-sm-row gap-3 mt-4">
            <a href="dashboard.php" class="admin-load btn btn-outline-secondary w-100">← Back</a>
            <button type="submit" class="admin-load btn btn-success w-100">✅ Upload Note</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript to show/hide price -->
<script>
  function togglePriceInput() {
    const noteType = document.getElementById('noteType').value;
    const priceContainer = document.getElementById('priceContainer');
    priceContainer.classList.toggle('d-none', noteType !== 'premium');
  }
</script>


<!-- JS to toggle price input -->
<script>
  function togglePriceInput() {
    const noteType = document.getElementById('noteType').value;
    const priceContainer = document.getElementById('priceContainer');
    priceContainer.classList.toggle('d-none', noteType !== 'premium');
  }
</script>


<!-- JavaScript -->
<script>
function togglePriceInput() {
  const noteType = document.getElementById('noteType').value;
  const priceContainer = document.getElementById('priceContainer');
  
  if (noteType === 'premium') {
    priceContainer.classList.remove('d-none');
  } else {
    priceContainer.classList.add('d-none');
  }
}
</script>

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



</body>
</html>
