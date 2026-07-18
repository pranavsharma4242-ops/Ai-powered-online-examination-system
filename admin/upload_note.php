<?php
session_start();
include '../includes/config.php';
include '../includes/auth_admin.php';

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = trim($_POST['subject']);
    $topic = trim($_POST['topic']);
    $resource_type = $_POST['resource_type'] ?? 'note';
    $note_type = $_POST['note_type'];
    $material_format = $_POST['material_format'] ?? 'pdf';
    $difficulty_level = $_POST['difficulty_level'] ?? 'beginner';
    $price = ($note_type === 'premium') ? (float) ($_POST['price'] ?? 0.00) : 0.00;
    $rating = (float) ($_POST['rating'] ?? 4.0);
    $youtube_link = trim($_POST['youtube_link'] ?? '');
    $youtube_link = $youtube_link !== '' ? $youtube_link : null;

    $allowedLevels = ['beginner', 'intermediate', 'advanced'];
    if (!in_array($difficulty_level, $allowedLevels, true)) {
        $difficulty_level = 'beginner';
    }

    $allowedResources = ['note', 'video'];
    if (!in_array($resource_type, $allowedResources, true)) {
        $resource_type = 'note';
    }

    $allowedFormats = ['pdf', 'handwritten'];
    if (!in_array($material_format, $allowedFormats, true)) {
        $material_format = 'pdf';
    }

    $rating = max(0.0, min(5.0, round($rating, 1)));

    $uploaded_by = $_SESSION['email'];
    $role = $_SESSION['role'];

    $pdf_file = $_FILES['note_file'] ?? null;
    $image_file = $_FILES['cover_image'];

    $pdf_name = $pdf_file['name'] ?? '';
    $pdf_tmp = $pdf_file['tmp_name'] ?? '';
    $pdf_ext = $pdf_name !== '' ? strtolower(pathinfo($pdf_name, PATHINFO_EXTENSION)) : '';
    $requiresPdf = $resource_type === 'note';
    $new_pdf = '';
    $pdf_path = '';

    if ($youtube_link !== null && !filter_var($youtube_link, FILTER_VALIDATE_URL)) {
        $msg = "Please enter a valid YouTube link.";
    } elseif ($requiresPdf && $pdf_ext !== 'pdf') {
        $msg = "Only PDF files are allowed.";
    } elseif ($resource_type === 'video' && $youtube_link === null) {
        $msg = "Video entries need a YouTube link.";
    } else {
        if ($pdf_ext === 'pdf') {
            $new_pdf = uniqid() . ".pdf";
            $pdf_path = "../uploads/" . $new_pdf;
        }

        $new_img = null;
        $img_path = null;

        if (!empty($image_file['name'])) {
            $image_name = $image_file['name'];
            $image_tmp = $image_file['tmp_name'];
            $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
            $allowed_image_ext = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($image_ext, $allowed_image_ext, true)) {
                $msg = "Only JPG, PNG, or WebP images are allowed for the cover.";
            } else {
                $new_img = uniqid() . "." . $image_ext;
                $img_path = "../image_path/" . $new_img;

                if (!move_uploaded_file($image_tmp, $img_path)) {
                    $msg = "Failed to upload the cover image.";
                }
            }
        }

        if ($msg === "") {
            if ($pdf_path !== '' && !move_uploaded_file($pdf_tmp, $pdf_path)) {
                $msg = "Failed to upload the PDF file.";
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO notes_final
                    (subject, topic, filename, uploaded_by, role, resource_type, note_type, material_format, price, rating, image_path, youtube_link, difficulty_level)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->bind_param(
                    "ssssssssddsss",
                    $subject,
                    $topic,
                    $new_pdf,
                    $uploaded_by,
                    $role,
                    $resource_type,
                    $note_type,
                    $material_format,
                    $price,
                    $rating,
                    $new_img,
                    $youtube_link,
                    $difficulty_level
                );

                if ($stmt->execute()) {
                    $msg = "Note uploaded successfully.";
                } else {
                    $msg = "Database error: " . $stmt->error;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upload Note | StudyHub Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --blue-deep: #0b3a75;
      --blue-strong: #1457b3;
      --blue-primary: #1f6fe0;
      --blue-pale: #eaf2fe;
      --blue-bg: #f5f9ff;
      --white: #ffffff;
      --ink: #1c2530;
      --soft-gray: #6b7688;
      --border: #e3ebf7;
      --green: #1f9d63;
      --green-soft: #eaf8f1;
      --shadow: 0 18px 42px rgba(11, 58, 117, 0.1);
    }

    body {
      background:
        radial-gradient(circle at top left, rgba(91, 155, 239, 0.18), transparent 30%),
        linear-gradient(180deg, var(--blue-bg) 0%, #edf4ff 100%);
      color: var(--ink);
      font-family: "Segoe UI", sans-serif;
      font-size: 15px;
    }

    .loader-overlay {
      position: fixed;
      inset: 0;
      background: rgba(245, 249, 255, 0.82);
      z-index: 9999;
      display: none;
      justify-content: center;
      align-items: center;
    }

    .loader {
      width: 48px;
      aspect-ratio: 1;
      border-radius: 50%;
      border: 7px solid;
      border-color: var(--blue-primary) transparent;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }

    .upload-card {
      border: 1px solid var(--border);
      border-radius: 28px;
      background: rgba(255, 255, 255, 0.96);
      box-shadow: var(--shadow);
      overflow: hidden;
    }

    .hero-strip {
      background: linear-gradient(135deg, var(--blue-deep), var(--blue-primary));
      color: var(--white);
      padding: 1.45rem;
      border-radius: 22px;
      margin-bottom: 1.5rem;
    }

    .hero-strip h3 {
      font-size: clamp(1.55rem, 2.3vw, 2rem);
      font-weight: 800;
    }

    .hero-strip p {
      margin-bottom: 0;
      color: rgba(255, 255, 255, 0.82);
    }

    .form-label {
      color: var(--ink);
      font-weight: 700;
      font-size: 0.95rem;
    }

    .form-control,
    .form-select {
      min-height: 50px;
      border-radius: 16px;
      border-color: var(--border);
      font-size: 0.95rem;
      box-shadow: none !important;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: var(--blue-primary);
    }

    .helper-box {
      border: 1px solid var(--border);
      border-radius: 20px;
      background: var(--blue-bg);
      padding: 1rem;
      color: var(--soft-gray);
      font-size: 0.92rem;
    }

    .btn {
      min-height: 48px;
      border-radius: 16px;
      font-weight: 700;
    }

    .btn-success {
      background: var(--green);
      border-color: var(--green);
    }

    .btn-success:hover {
      background: #178551;
      border-color: #178551;
    }

    @media (max-width: 1399px) {
      body {
        font-size: 14px;
      }
    }
  </style>
</head>
<body>
  <div class="loader-overlay" id="admin-loader">
    <div class="loader"></div>
  </div>

  <div class="container py-4 py-lg-5">
    <div class="row justify-content-center">
      <div class="col-12 col-xl-10">
        <div class="card upload-card p-4 p-md-5">
          <div class="row g-4 align-items-start">
            <div class="col-lg-7">
              <div class="hero-strip">
                <h3 class="mb-2">Upload New Note</h3>
                <p>Add PDFs, cover images, video links, pricing, and difficulty details using the updated StudyHub admin style.</p>
              </div>

              <?php if (!empty($msg)): ?>
                <div class="alert alert-info text-center"><?php echo htmlspecialchars($msg); ?></div>
              <?php endif; ?>

              <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                  <label class="form-label">Subject</label>
                  <input type="text" name="subject" class="form-control" placeholder="e.g., Computer Networks" required>
                </div>

                <div class="mb-3">
                  <label class="form-label">Topic</label>
                  <input type="text" name="topic" class="form-control" placeholder="e.g., OSI Layers" required>
                </div>

                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Resource Type</label>
                    <select name="resource_type" id="resourceType" class="form-select" onchange="toggleResourceFields()" required>
                      <option value="note">Note</option>
                      <option value="video">Video Lesson</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Note Type</label>
                    <select name="note_type" id="noteType" class="form-select" onchange="togglePriceInput()" required>
                      <option value="free">Free Note</option>
                      <option value="premium">Premium Note</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Difficulty Level</label>
                    <select name="difficulty_level" class="form-select" required>
                      <option value="beginner">Beginner</option>
                      <option value="intermediate">Intermediate</option>
                      <option value="advanced">Advanced</option>
                    </select>
                  </div>
                </div>

                <div class="row g-3 mt-0">
                  <div class="col-md-6">
                    <label class="form-label">Notes Format</label>
                    <select name="material_format" class="form-select" required>
                      <option value="pdf">PDF</option>
                      <option value="handwritten">Handwritten</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Admin Rating</label>
                    <input type="number" name="rating" class="form-control" min="0" max="5" step="0.1" value="4.0" required>
                  </div>
                </div>

                <div class="mb-3 mt-3 d-none" id="priceContainer">
                  <label class="form-label">Price (INR)</label>
                  <input type="number" name="price" class="form-control" placeholder="e.g., 50" min="1" step="0.01">
                </div>

                <div class="mb-3" id="pdfUploadBlock">
                  <label class="form-label">Upload PDF File</label>
                  <input type="file" name="note_file" id="noteFile" class="form-control" accept="application/pdf" required>
                  <small class="text-muted">Required for note items. Optional for video items.</small>
                </div>

                <div class="mb-3">
                  <label class="form-label">Upload Cover Image</label>
                  <input type="file" name="cover_image" class="form-control" accept="image/*">
                  <small class="text-muted">Optional. JPG, PNG, or WebP works best.</small>
                </div>

                <div class="mb-3">
                  <label class="form-label">YouTube Link</label>
                  <input type="url" name="youtube_link" class="form-control" placeholder="https://www.youtube.com/watch?v=...">
                  <small class="text-muted">Required for video lessons. Optional for normal notes.</small>
                </div>

                <div class="d-flex flex-column flex-sm-row gap-3 mt-4">
                  <a href="dashboard.php" class="admin-load btn btn-outline-secondary w-100">Back</a>
                  <button type="submit" class="btn btn-success w-100">Upload Note</button>
                </div>
              </form>
            </div>

            <div class="col-lg-5">
              <div class="helper-box">
                <h5 class="fw-bold mb-3" style="color: var(--blue-deep);">Admin Tips</h5>
                <p class="mb-2">Free notes appear in the open student section right away.</p>
                <p class="mb-2">Premium notes use pricing and locked-access styling automatically.</p>
                <p class="mb-2">Difficulty controls badge colors for beginner, intermediate, and advanced cards.</p>
                <p class="mb-0">If you add a YouTube link, the lesson also becomes available in the student video tabs.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    function togglePriceInput() {
      const noteType = document.getElementById('noteType').value;
      const priceContainer = document.getElementById('priceContainer');
      priceContainer.classList.toggle('d-none', noteType !== 'premium');
    }

    function toggleResourceFields() {
      const resourceType = document.getElementById('resourceType').value;
      const noteFile = document.getElementById('noteFile');
      const pdfUploadBlock = document.getElementById('pdfUploadBlock');

      if (resourceType === 'video') {
        pdfUploadBlock.querySelector('small').textContent = 'Optional PDF support file for the video lesson.';
        noteFile.required = false;
      } else {
        pdfUploadBlock.querySelector('small').textContent = 'Required for note items. Optional for video items.';
        noteFile.required = true;
      }
    }

    window.addEventListener('pageshow', function () {
      const loader = document.getElementById('admin-loader');
      if (loader) {
        loader.style.display = 'none';
      }
    });

    document.addEventListener('DOMContentLoaded', function () {
      togglePriceInput();
      toggleResourceFields();

      document.querySelectorAll('a.admin-load').forEach(function (link) {
        link.addEventListener('click', function (e) {
          e.preventDefault();
          const loader = document.getElementById('admin-loader');
          if (loader) {
            loader.style.display = 'flex';
          }
          setTimeout(() => {
            window.location.href = this.href;
          }, 500);
        });
      });
    });
  </script>
</body>
</html>
