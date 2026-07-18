<?php
session_start();
include '../includes/config.php';
include '../includes/auth_admin.php';

$noteId = (int) ($_GET['id'] ?? $_POST['note_id'] ?? 0);
if ($noteId <= 0) {
    header("Location: dashboard.php");
    exit;
}

$msg = "";
$note = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $subject = trim($_POST['subject']);
    $topic = trim($_POST['topic']);
    $resource_type = $_POST['resource_type'] ?? 'note';
    $note_type = $_POST['note_type'] ?? 'free';
    $material_format = $_POST['material_format'] ?? 'pdf';
    $difficulty_level = $_POST['difficulty_level'] ?? 'beginner';
    $price = ($note_type === 'premium') ? (float) ($_POST['price'] ?? 0) : 0;
    $rating = max(0.0, min(5.0, round((float) ($_POST['rating'] ?? 4.0), 1)));
    $youtube_link = trim($_POST['youtube_link'] ?? '');
    $youtube_link = $youtube_link !== '' ? $youtube_link : null;

    $fetchStmt = $conn->prepare("SELECT filename, image_path FROM notes_final WHERE id = ?");
    $fetchStmt->bind_param("i", $noteId);
    $fetchStmt->execute();
    $existingResult = $fetchStmt->get_result();
    $existing = $existingResult ? $existingResult->fetch_assoc() : null;

    if (!$existing) {
        header("Location: dashboard.php");
        exit;
    }

    $newFilename = $existing['filename'] ?? '';
    $newImage = $existing['image_path'] ?? null;

    if (!empty($_FILES['note_file']['name'])) {
        $pdfExt = strtolower(pathinfo($_FILES['note_file']['name'], PATHINFO_EXTENSION));
        if ($pdfExt !== 'pdf') {
            $msg = "Only PDF files are allowed.";
        } else {
            $newFilename = uniqid() . ".pdf";
            $pdfPath = "../uploads/" . $newFilename;
            if (!move_uploaded_file($_FILES['note_file']['tmp_name'], $pdfPath)) {
                $msg = "Failed to upload the PDF file.";
            } elseif (!empty($existing['filename']) && file_exists("../uploads/" . $existing['filename'])) {
                unlink("../uploads/" . $existing['filename']);
            }
        }
    }

    if ($msg === "" && !empty($_FILES['cover_image']['name'])) {
        $imageExt = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
        $allowedImageExt = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($imageExt, $allowedImageExt, true)) {
            $msg = "Only JPG, PNG, or WebP images are allowed for the cover.";
        } else {
            $newImage = uniqid() . "." . $imageExt;
            $imgPath = "../image_path/" . $newImage;
            if (!move_uploaded_file($_FILES['cover_image']['tmp_name'], $imgPath)) {
                $msg = "Failed to upload the cover image.";
            } elseif (!empty($existing['image_path']) && file_exists("../image_path/" . $existing['image_path'])) {
                unlink("../image_path/" . $existing['image_path']);
            }
        }
    }

    if ($msg === "") {
        $stmt = $conn->prepare("
            UPDATE notes_final
            SET subject = ?, topic = ?, filename = ?, resource_type = ?, note_type = ?, material_format = ?, price = ?, rating = ?, image_path = ?, youtube_link = ?, difficulty_level = ?
            WHERE id = ?
        ");
        $stmt->bind_param(
            "ssssssddsssi",
            $subject,
            $topic,
            $newFilename,
            $resource_type,
            $note_type,
            $material_format,
            $price,
            $rating,
            $newImage,
            $youtube_link,
            $difficulty_level,
            $noteId
        );
        if ($stmt->execute()) {
            header("Location: dashboard.php?msg=updated");
            exit;
        }
        $msg = "Database error: " . $stmt->error;
    }
}

$noteStmt = $conn->prepare("SELECT * FROM notes_final WHERE id = ?");
$noteStmt->bind_param("i", $noteId);
$noteStmt->execute();
$noteResult = $noteStmt->get_result();
$note = $noteResult ? $noteResult->fetch_assoc() : null;

if (!$note) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Note | StudyHub Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --blue-deep: #0b3a75; --blue-primary: #1f6fe0; --blue-bg: #f5f9ff; --border: #e3ebf7; --ink: #1c2530; --soft-gray: #6b7688;
    }
    body { background: linear-gradient(180deg, var(--blue-bg) 0%, #edf4ff 100%); font-family: "Segoe UI", sans-serif; color: var(--ink); }
    .edit-card { border: 1px solid var(--border); border-radius: 26px; background: rgba(255,255,255,.96); box-shadow: 0 18px 42px rgba(11,58,117,.1); }
    .hero-strip { background: linear-gradient(135deg, var(--blue-deep), var(--blue-primary)); color: #fff; border-radius: 20px; padding: 1.3rem; margin-bottom: 1.4rem; }
    .form-control, .form-select { min-height: 48px; border-radius: 14px; border-color: var(--border); }
    .btn { min-height: 46px; border-radius: 14px; font-weight: 700; }
  </style>
</head>
<body>
  <div class="container py-4 py-lg-5">
    <div class="row justify-content-center">
      <div class="col-12 col-xl-10">
        <div class="edit-card p-4 p-md-5">
          <div class="hero-strip">
            <h3 class="mb-2 fw-bold">Modify Note</h3>
            <p class="mb-0">Update note details, replace files, and keep the admin portal data correct.</p>
          </div>

          <?php if ($msg !== ""): ?>
            <div class="alert alert-info text-center"><?php echo htmlspecialchars($msg); ?></div>
          <?php endif; ?>

          <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="note_id" value="<?php echo (int) $note['id']; ?>">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Subject</label>
                <input type="text" name="subject" class="form-control" value="<?php echo htmlspecialchars($note['subject']); ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Topic</label>
                <input type="text" name="topic" class="form-control" value="<?php echo htmlspecialchars($note['topic']); ?>" required>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Resource Type</label>
                <select name="resource_type" class="form-select" required>
                  <option value="note" <?php echo ($note['resource_type'] ?? 'note') === 'note' ? 'selected' : ''; ?>>Note</option>
                  <option value="video" <?php echo ($note['resource_type'] ?? 'note') === 'video' ? 'selected' : ''; ?>>Video Lesson</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Note Type</label>
                <select name="note_type" class="form-select" required>
                  <option value="free" <?php echo $note['note_type'] === 'free' ? 'selected' : ''; ?>>Free</option>
                  <option value="premium" <?php echo $note['note_type'] === 'premium' ? 'selected' : ''; ?>>Premium</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Format</label>
                <select name="material_format" class="form-select" required>
                  <option value="pdf" <?php echo ($note['material_format'] ?? 'pdf') === 'pdf' ? 'selected' : ''; ?>>PDF</option>
                  <option value="handwritten" <?php echo ($note['material_format'] ?? 'pdf') === 'handwritten' ? 'selected' : ''; ?>>Handwritten</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Difficulty</label>
                <select name="difficulty_level" class="form-select" required>
                  <option value="beginner" <?php echo ($note['difficulty_level'] ?? 'beginner') === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                  <option value="intermediate" <?php echo ($note['difficulty_level'] ?? 'beginner') === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                  <option value="advanced" <?php echo ($note['difficulty_level'] ?? 'beginner') === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Rating</label>
                <input type="number" name="rating" class="form-control" min="0" max="5" step="0.1" value="<?php echo htmlspecialchars((string) ($note['rating'] ?? 4.0)); ?>" required>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Price</label>
                <input type="number" name="price" class="form-control" min="0" step="0.01" value="<?php echo htmlspecialchars((string) ($note['price'] ?? 0)); ?>">
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">YouTube Link</label>
                <input type="url" name="youtube_link" class="form-control" value="<?php echo htmlspecialchars((string) ($note['youtube_link'] ?? '')); ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Replace PDF (optional)</label>
                <input type="file" name="note_file" class="form-control" accept="application/pdf">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Replace Cover (optional)</label>
                <input type="file" name="cover_image" class="form-control" accept="image/*">
              </div>
            </div>

            <div class="d-flex flex-column flex-sm-row gap-3 mt-4">
              <a href="dashboard.php" class="btn btn-outline-secondary w-100">Back</a>
              <button type="submit" class="btn btn-primary w-100">Update Note</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
