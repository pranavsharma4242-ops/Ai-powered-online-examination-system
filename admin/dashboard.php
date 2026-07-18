<?php
require '../includes/config.php';
require '../includes/auth_admin.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$stats = [
    'total' => 0,
    'free' => 0,
    'premium' => 0,
    'videos' => 0,
    'handwritten' => 0
];

$statsQuery = $conn->query("
    SELECT
        COUNT(*) AS total_notes,
        SUM(CASE WHEN note_type = 'free' THEN 1 ELSE 0 END) AS free_notes,
        SUM(CASE WHEN note_type = 'premium' THEN 1 ELSE 0 END) AS premium_notes,
        SUM(CASE WHEN resource_type = 'video' THEN 1 ELSE 0 END) AS video_notes,
        SUM(CASE WHEN material_format = 'handwritten' THEN 1 ELSE 0 END) AS handwritten_notes
    FROM notes_final
");

if ($statsQuery && $statsQuery->num_rows === 1) {
    $row = $statsQuery->fetch_assoc();
    $stats['total'] = (int) ($row['total_notes'] ?? 0);
    $stats['free'] = (int) ($row['free_notes'] ?? 0);
    $stats['premium'] = (int) ($row['premium_notes'] ?? 0);
    $stats['videos'] = (int) ($row['video_notes'] ?? 0);
    $stats['handwritten'] = (int) ($row['handwritten_notes'] ?? 0);
}

$latestNotes = $conn->query("SELECT * FROM notes_final WHERE resource_type = 'note' ORDER BY upload_date DESC LIMIT 5");
$latestVideos = $conn->query("SELECT * FROM notes_final WHERE resource_type = 'video' ORDER BY upload_date DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | StudyHub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root {
      --blue-deep: #0b3a75;
      --blue-strong: #1457b3;
      --blue-primary: #1f6fe0;
      --blue-light: #5b9bef;
      --blue-pale: #eaf2fe;
      --blue-bg: #f5f9ff;
      --white: #ffffff;
      --ink: #1c2530;
      --soft-gray: #6b7688;
      --border: #e3ebf7;
      --green: #1f9d63;
      --green-soft: #eaf8f1;
      --amber: #e08a00;
      --amber-soft: #fff4df;
      --coral: #c94b3f;
      --coral-soft: #feeeeb;
      --indigo: #4b3fd6;
      --indigo-soft: #efedff;
      --shadow: 0 18px 40px rgba(11, 58, 117, 0.1);
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
      background: rgba(245, 249, 255, 0.8);
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

    .admin-nav {
      background: rgba(255, 255, 255, 0.92);
      border-bottom: 1px solid var(--border);
      backdrop-filter: blur(18px);
    }

    .navbar-brand {
      color: var(--blue-deep) !important;
      font-size: 1.45rem;
      font-weight: 800;
    }

    .hero-card,
    .stat-card,
    .database-card,
    .table-card {
      border: 1px solid var(--border);
      border-radius: 24px;
      background: rgba(255, 255, 255, 0.96);
      box-shadow: var(--shadow);
    }

    .hero-card {
      background: linear-gradient(135deg, var(--blue-deep), var(--blue-primary));
      color: var(--white);
      padding: 1.8rem;
      overflow: hidden;
    }

    .hero-card h1 {
      font-size: clamp(1.8rem, 2.6vw, 2.3rem);
      font-weight: 800;
      margin-bottom: 0.55rem;
    }

    .hero-card p {
      color: rgba(255, 255, 255, 0.82);
      max-width: 620px;
      margin-bottom: 0;
    }

    .hero-actions .btn {
      border-radius: 14px;
      font-weight: 700;
      min-height: 46px;
      padding-inline: 1rem;
    }

    .stat-card,
    .database-card {
      padding: 1.1rem;
      height: 100%;
    }

    .stat-icon,
    .db-icon {
      width: 50px;
      height: 50px;
      border-radius: 16px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      margin-bottom: 0.85rem;
    }

    .stat-icon {
      background: var(--blue-pale);
      color: var(--blue-primary);
    }

    .db-icon {
      background: var(--indigo-soft);
      color: var(--indigo);
    }

    .stat-card h3 {
      font-size: 1.7rem;
      font-weight: 800;
      margin-bottom: 0.2rem;
    }

    .stat-card p,
    .database-card p {
      color: var(--soft-gray);
      margin-bottom: 0;
      font-size: 0.92rem;
    }

    .status-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      border-radius: 999px;
      padding: 0.44rem 0.75rem;
      font-size: 0.78rem;
      font-weight: 800;
    }

    .status-live {
      background: var(--green-soft);
      color: var(--green);
    }

    .status-schema {
      background: var(--blue-pale);
      color: var(--blue-strong);
    }

    .table-card {
      padding: 1.2rem;
    }

    .table-card h5 {
      color: var(--blue-deep);
      font-size: 1.15rem;
      font-weight: 800;
    }

    .table thead th {
      background: var(--blue-deep);
      color: var(--white);
      border: 0;
      font-size: 0.9rem;
      white-space: nowrap;
    }

    .table td {
      vertical-align: middle;
      color: var(--ink);
      font-size: 0.92rem;
    }

    .table tbody tr:hover {
      background: rgba(234, 242, 254, 0.55);
    }

    .cover-thumb {
      width: 70px;
      height: 54px;
      object-fit: cover;
      border-radius: 12px;
      border: 1px solid var(--border);
    }

    .type-badge,
    .price-badge,
    .db-chip {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      border-radius: 999px;
      padding: 0.35rem 0.7rem;
      font-size: 0.75rem;
      font-weight: 700;
    }

    .type-free {
      background: var(--green-soft);
      color: var(--green);
    }

    .type-premium {
      background: rgba(91, 155, 239, 0.18);
      color: var(--blue-light);
    }

    .price-badge {
      background: var(--amber-soft);
      color: var(--amber);
    }

    .db-chip {
      background: var(--blue-bg);
      color: var(--soft-gray);
      border: 1px solid var(--border);
    }

    .action-btn {
      border-radius: 12px;
      font-size: 0.86rem;
      font-weight: 700;
    }

    @media (max-width: 1399px) {
      body {
        font-size: 14px;
      }
    }

    @media (max-width: 576px) {
      .table td,
      .table th {
        font-size: 0.84rem;
        white-space: nowrap;
      }

      .cover-thumb {
        width: 54px;
        height: 42px;
      }
    }
  </style>
</head>
<body>
<div class="loader-overlay" id="admin-loader">
  <div class="loader"></div>
</div>

<nav class="navbar navbar-expand-lg admin-nav sticky-top">
  <div class="container py-2">
    <a class="navbar-brand" href="dashboard.php"><i class="bi bi-grid-1x2-fill me-2"></i>StudyHub Admin</a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="adminNavbar">
      <div class="d-flex flex-column flex-lg-row gap-2 mt-3 mt-lg-0">
        <a href="upload_note.php" class="admin-load btn btn-primary">Upload Note</a>
        <a href="logout.php" class="admin-load btn btn-outline-danger">Logout</a>
      </div>
    </div>
  </div>
</nav>

<main class="container my-4 my-lg-5">
  <section class="hero-card mb-4">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <h1>Admin Portal</h1>
        <p>Manage StudyHub notes, premium content, and linked video lessons with the new dashboard palette and a clearer database overview.</p>
      </div>
      <div class="col-lg-4">
        <div class="hero-actions d-flex flex-wrap gap-2 justify-content-lg-end">
          <span class="status-pill status-live"><i class="bi bi-check-circle-fill"></i> Database Connected</span>
          <span class="status-pill status-schema"><i class="bi bi-database-fill"></i> notemarket</span>
        </div>
      </div>
    </div>
  </section>

  <section class="row g-4 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="stat-card">
        <div class="stat-icon"><i class="bi bi-journal-text"></i></div>
        <h3><?php echo $stats['total']; ?></h3>
        <p>Total uploaded notes</p>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="stat-card">
        <div class="stat-icon"><i class="bi bi-unlock"></i></div>
        <h3><?php echo $stats['free']; ?></h3>
        <p>Free notes available</p>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="stat-card">
        <div class="stat-icon"><i class="bi bi-gem"></i></div>
        <h3><?php echo $stats['premium']; ?></h3>
        <p>Premium notes listed</p>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="stat-card">
        <div class="stat-icon"><i class="bi bi-play-btn"></i></div>
        <h3><?php echo $stats['videos']; ?></h3>
        <p>Separate video lessons</p>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="stat-card">
        <div class="stat-icon"><i class="bi bi-pencil-square"></i></div>
        <h3><?php echo $stats['handwritten']; ?></h3>
        <p>Handwritten notes</p>
      </div>
    </div>
  </section>

  <section class="row g-4 mb-4">
    <div class="col-lg-7">
      <div class="database-card">
        <div class="db-icon"><i class="bi bi-database-fill-check"></i></div>
        <h5 class="fw-bold mb-2">Database Status</h5>
        <p class="mb-3">The admin portal is using the existing `notemarket` MySQL database and the `notes_final` table already supports free notes, premium pricing, cover images, YouTube links, and difficulty levels.</p>
        <div class="d-flex flex-wrap gap-2">
          <span class="db-chip"><i class="bi bi-table"></i> Table: notes_final</span>
          <span class="db-chip"><i class="bi bi-collection"></i> Table: users</span>
          <span class="db-chip"><i class="bi bi-key"></i> Auth enabled</span>
          <span class="db-chip"><i class="bi bi-shield-check"></i> Admin protected</span>
        </div>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="database-card">
        <div class="db-icon"><i class="bi bi-person-badge"></i></div>
        <h5 class="fw-bold mb-2">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h5>
        <p class="mb-3">Use the upload panel to add new content. Student-facing sections will automatically reflect notes, pricing, thumbnails, and video metadata.</p>
        <div class="d-grid gap-2">
          <a href="upload_note.php" class="admin-load btn btn-primary">Open Upload Panel</a>
          <a href="../student/dashboard.php" class="btn btn-outline-secondary">Preview Student Dashboard</a>
        </div>
      </div>
    </div>
  </section>

  <section class="table-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
      <div>
        <h5 class="mb-1">Top 5 Recent Notes</h5>
        <p class="mb-0 text-muted">Latest note entries with rating, file type, and proper note-only data.</p>
      </div>
      <span class="db-chip"><i class="bi bi-clock-history"></i> Notes only</span>
    </div>

    <div class="table-responsive">
      <table class="table align-middle text-center mb-0">
        <thead>
          <tr>
            <th>Subject</th>
            <th>Topic</th>
            <th>Cover</th>
            <th>Type</th>
            <th>Format</th>
            <th>Rating</th>
            <th>Price</th>
            <th>File</th>
            <th>Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($latestNotes && $latestNotes->num_rows > 0): ?>
            <?php while ($row = $latestNotes->fetch_assoc()): ?>
              <?php
              $hasImage = !empty($row['image_path']) && file_exists("../image_path/" . $row['image_path']);
              $coverPath = $hasImage ? "../image_path/" . $row['image_path'] : 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop&w=400&q=80';
              $typeClass = $row['note_type'] === 'premium' ? 'type-premium' : 'type-free';
              ?>
              <tr>
                <td class="text-break fw-semibold"><?php echo htmlspecialchars($row['subject']); ?></td>
                <td class="text-break"><?php echo htmlspecialchars($row['topic']); ?></td>
                <td>
                  <a href="<?php echo htmlspecialchars($coverPath); ?>" target="_blank">
                    <img src="<?php echo htmlspecialchars($coverPath); ?>" alt="cover" class="cover-thumb">
                  </a>
                </td>
                <td>
                  <span class="type-badge <?php echo $typeClass; ?>">
                    <?php echo ucfirst($row['note_type']); ?>
                  </span>
                </td>
                <td><?php echo ucfirst($row['material_format'] ?? 'pdf'); ?></td>
                <td><span class="price-badge"><?php echo number_format((float) ($row['rating'] ?? 4), 1); ?> / 5</span></td>
                <td>
                  <?php if ((float) $row['price'] > 0): ?>
                    <span class="price-badge">Rs <?php echo number_format((float) $row['price'], 2); ?></span>
                  <?php else: ?>
                    <span class="type-badge type-free">Free</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="../uploads/<?php echo htmlspecialchars($row['filename']); ?>" target="_blank" class="btn btn-sm btn-primary action-btn">
                    <i class="bi bi-file-earmark-pdf"></i> View
                  </a>
                </td>
                <td class="small"><?php echo date("d M Y", strtotime($row['upload_date'])); ?></td>
                <td>
                  <div class="d-flex flex-column gap-2">
                    <a href="edit_note.php?id=<?php echo (int) $row['id']; ?>" class="btn btn-sm btn-outline-primary action-btn">
                      <i class="bi bi-pencil-square"></i> Modify
                    </a>
                    <form method="POST" action="delete_note.php" onsubmit="return confirm('Are you sure you want to delete this note?');">
                      <input type="hidden" name="note_id" value="<?php echo (int) $row['id']; ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger action-btn w-100">
                        <i class="bi bi-trash"></i> Delete
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="10" class="text-muted py-4">No notes uploaded yet.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="table-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
      <div>
        <h5 class="mb-1">Top 5 Recent Videos</h5>
        <p class="mb-0 text-muted">Latest video lesson entries shown separately from notes.</p>
      </div>
      <span class="db-chip"><i class="bi bi-camera-video"></i> Videos only</span>
    </div>

    <div class="table-responsive">
      <table class="table align-middle text-center mb-0">
        <thead>
          <tr>
            <th>Subject</th>
            <th>Topic</th>
            <th>Cover</th>
            <th>Level</th>
            <th>Format</th>
            <th>Rating</th>
            <th>Video</th>
            <th>Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($latestVideos && $latestVideos->num_rows > 0): ?>
            <?php while ($row = $latestVideos->fetch_assoc()): ?>
              <?php
              $hasImage = !empty($row['image_path']) && file_exists("../image_path/" . $row['image_path']);
              $coverPath = $hasImage ? "../image_path/" . $row['image_path'] : 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop&w=400&q=80';
              ?>
              <tr>
                <td class="text-break fw-semibold"><?php echo htmlspecialchars($row['subject']); ?></td>
                <td class="text-break"><?php echo htmlspecialchars($row['topic']); ?></td>
                <td>
                  <a href="<?php echo htmlspecialchars($coverPath); ?>" target="_blank">
                    <img src="<?php echo htmlspecialchars($coverPath); ?>" alt="cover" class="cover-thumb">
                  </a>
                </td>
                <td><?php echo ucfirst($row['difficulty_level'] ?? 'beginner'); ?></td>
                <td><?php echo ucfirst($row['material_format'] ?? 'pdf'); ?></td>
                <td><span class="price-badge"><?php echo number_format((float) ($row['rating'] ?? 4), 1); ?> / 5</span></td>
                <td>
                  <?php if (!empty($row['youtube_link'])): ?>
                    <a href="<?php echo htmlspecialchars($row['youtube_link']); ?>" target="_blank" class="btn btn-sm btn-primary action-btn">
                      <i class="bi bi-play-btn"></i> Open
                    </a>
                  <?php else: ?>
                    <span class="type-badge type-free">No Link</span>
                  <?php endif; ?>
                </td>
                <td class="small"><?php echo date("d M Y", strtotime($row['upload_date'])); ?></td>
                <td>
                  <div class="d-flex flex-column gap-2">
                    <a href="edit_note.php?id=<?php echo (int) $row['id']; ?>" class="btn btn-sm btn-outline-primary action-btn">
                      <i class="bi bi-pencil-square"></i> Modify
                    </a>
                    <form method="POST" action="delete_note.php" onsubmit="return confirm('Are you sure you want to delete this video lesson?');">
                      <input type="hidden" name="note_id" value="<?php echo (int) $row['id']; ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger action-btn w-100">
                        <i class="bi bi-trash"></i> Delete
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="9" class="text-muted py-4">No videos uploaded yet.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</main>

<script>
  window.addEventListener('pageshow', function () {
    const loader = document.getElementById('admin-loader');
    if (loader) {
      loader.style.display = 'none';
    }
  });

  document.addEventListener('DOMContentLoaded', function () {
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
