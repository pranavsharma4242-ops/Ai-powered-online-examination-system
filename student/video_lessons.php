<?php
include '../includes/config.php';
include '../includes/auth_student.php';

$search = trim($_GET['search'] ?? '');
$level = trim($_GET['level'] ?? '');
$sql = "SELECT * FROM notes_final WHERE resource_type = 'video'";
$types = "";
$params = [];
if ($search !== '') {
    $sql .= " AND (subject LIKE ? OR topic LIKE ?)";
    $searchLike = "%" . $search . "%";
    $types .= "ss";
    $params[] = $searchLike;
    $params[] = $searchLike;
}
if (in_array($level, ['beginner', 'intermediate', 'advanced'], true)) {
    $sql .= " AND difficulty_level = ?";
    $types .= "s";
    $params[] = $level;
}
$sql .= " ORDER BY upload_date DESC";
$stmt = $conn->prepare($sql);
if ($types !== "") {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$videoCount = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Video Lessons | StudyHub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root { --blue-deep:#0b3a75; --blue-primary:#1f6fe0; --blue-strong:#1457b3; --blue-pale:#eaf2fe; --blue-bg:#f5f9ff; --white:#fff; --ink:#1c2530; --soft-gray:#6b7688; --border:#e3ebf7; --green:#1f9d63; --amber:#e08a00; --coral:#c94b3f; --indigo:#4b3fd6; --gold:#f5b400; --shadow:0 16px 34px rgba(11,58,117,.08); }
    body { margin:0; background:linear-gradient(180deg,var(--blue-bg) 0%,#eef4fd 100%); color:var(--ink); font-family:"Segoe UI",sans-serif; font-size:14px; }
    .layout { min-height:100vh; } .sidebar { width:230px; min-width:230px; background:rgba(255,255,255,.96); border-right:1px solid var(--border); padding:1.15rem 1rem; position:sticky; top:0; height:100vh; }
    .brand { display:flex; align-items:center; gap:.75rem; color:var(--blue-deep); font-size:1.45rem; font-weight:800; margin-bottom:1.6rem; }
    .brand-icon { width:38px; height:38px; border-radius:12px; display:inline-flex; align-items:center; justify-content:center; background:linear-gradient(135deg,var(--blue-deep),var(--blue-primary)); color:var(--white); }
    .side-link { display:flex; align-items:center; gap:.8rem; padding:.82rem .9rem; border-radius:15px; text-decoration:none; color:var(--soft-gray); font-weight:600; font-size:.95rem; margin-bottom:.42rem; }
    .side-link.active,.side-link:hover { background:linear-gradient(135deg,var(--blue-primary),var(--blue-strong)); color:var(--white); }
    .promo-box { margin-top:auto; padding:1rem; border:1px solid var(--border); border-radius:18px; background:linear-gradient(180deg,#f8fbff 0%,#f1f6ff 100%); }
    .promo-box p { color:var(--soft-gray); font-size:.87rem; margin-bottom:.8rem; } .promo-box .btn { border-radius:12px; font-size:.88rem; font-weight:700; }
    .main { flex:1; padding:1.2rem 1.35rem 1.8rem; }
    .topbar { display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:1rem; }
    .search-shell,.notify-btn,.profile-pill,.video-card { background:rgba(255,255,255,.96); border:1px solid var(--border); box-shadow:var(--shadow); }
    .search-shell { display:flex; align-items:center; gap:.65rem; width:min(360px,100%); border-radius:16px; padding:.72rem .95rem; }
    .search-shell input { width:100%; border:0; outline:0; background:transparent; font-size:.92rem; } .search-shell i { color:var(--blue-primary); }
    .top-actions { display:flex; align-items:center; gap:.7rem; }
    .notify-btn { width:42px; height:42px; border-radius:14px; display:inline-flex; align-items:center; justify-content:center; color:var(--blue-deep); text-decoration:none; position:relative; }
    .notify-btn::after { content:""; position:absolute; top:8px; right:9px; width:7px; height:7px; border-radius:50%; background:#e0483f; }
    .profile-pill { display:flex; align-items:center; gap:.6rem; border-radius:999px; padding:.35rem .8rem .35rem .35rem; color:var(--ink); text-decoration:none; font-weight:700; font-size:.92rem; }
    .avatar { width:34px; height:34px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; background:var(--blue-pale); color:var(--blue-primary); font-weight:800; }
    .section-head { display:flex; align-items:center; gap:1rem; margin:1rem 0 .9rem; padding-bottom:.45rem; border-bottom:1px solid var(--border); }
    .section-head h2 { font-size:1.45rem; margin:0; } .tag { display:inline-flex; align-items:center; border-radius:999px; padding:.34rem .68rem; font-size:.72rem; font-weight:800; background:#efedff; color:var(--indigo); }
    .pill-row { display:flex; flex-wrap:wrap; gap:.6rem; margin-bottom:1rem; }
    .pill { border:1px solid var(--border); background:rgba(255,255,255,.92); color:var(--soft-gray); text-decoration:none; padding:.55rem .88rem; border-radius:999px; font-size:.86rem; font-weight:600; }
    .pill.active { background:var(--indigo); color:var(--white); border-color:var(--indigo); }
    .cards { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:1rem; }
    .video-card { border-radius:22px; overflow:hidden; }
    .thumb { height:145px; position:relative; overflow:hidden; background:#1f2a48; }
    .thumb iframe,.thumb img { width:100%; height:100%; border:0; object-fit:cover; }
    .duration { position:absolute; right:10px; bottom:10px; background:rgba(20,21,33,.72); color:#fff; border-radius:7px; padding:.2rem .45rem; font-size:.66rem; font-weight:700; }
    .badge-row { display:flex; justify-content:space-between; gap:.45rem; margin-bottom:.7rem; }
    .badge-chip { display:inline-flex; align-items:center; border-radius:999px; padding:.28rem .55rem; font-size:.66rem; font-weight:700; }
    .badge-subject { background:var(--blue-pale); color:var(--blue-deep); } .badge-beginner { background:#eaf8f1; color:var(--green); } .badge-intermediate { background:#fff4df; color:var(--amber); } .badge-advanced { background:#feeeeb; color:var(--coral); }
    .body-card { padding:.85rem .95rem .95rem; }
    .body-card h5 { font-size:1rem; line-height:1.35; margin-bottom:.35rem; font-weight:800; }
    .desc { color:var(--soft-gray); font-size:.82rem; margin-bottom:.65rem; min-height:44px; }
    .meta { display:flex; justify-content:space-between; gap:.6rem; color:var(--soft-gray); font-size:.79rem; }
    .meta .stars { color:var(--gold); font-weight:700; } .meta a { color:var(--indigo); text-decoration:none; font-weight:700; }
    @media (max-width:1400px) { .cards { grid-template-columns:repeat(3,minmax(0,1fr)); } }
    @media (max-width:1199px) { .cards { grid-template-columns:repeat(2,minmax(0,1fr)); } .main { padding:1rem 1rem 1.4rem; } .search-shell { width:min(100%,320px); } }
    @media (max-width:991px) {
      .layout { display:block !important; }
      .sidebar { position:static; width:100%; min-width:0; height:auto; display:flex !important; flex-direction:row !important; flex-wrap:wrap; gap:.65rem; align-items:stretch; padding:1rem; border-right:0; border-bottom:1px solid var(--border); }
      .brand { width:100%; justify-content:center; margin-bottom:.2rem; }
      .side-link { flex:1 1 calc(50% - .65rem); justify-content:center; text-align:center; margin-bottom:0; min-height:48px; }
      .promo-box { width:100%; margin-top:.15rem; }
      .main { padding:1rem; }
      .search-shell { width:100%; }
    }
    @media (max-width:767px) {
      .cards { grid-template-columns:1fr; }
      .sidebar { gap:.55rem; padding:.9rem; }
      .brand { justify-content:flex-start; }
      .side-link { flex-basis:100%; justify-content:flex-start; text-align:left; }
      .topbar { flex-direction:column; align-items:stretch; }
      .top-actions { width:100%; justify-content:space-between; }
      .profile-pill { flex:1; justify-content:center; }
      .search-shell { width:100%; }
      .main { padding:1rem; }
      .section-head { flex-direction:column; align-items:flex-start; gap:.45rem; }
      .pill-row { overflow-x:auto; flex-wrap:nowrap; padding-bottom:.2rem; }
      .pill { white-space:nowrap; }
    }
    body.dark-mode { background:linear-gradient(180deg,#0f1724 0%,#121d2d 100%); color:#e6edf8; }
    body.dark-mode .sidebar,
    body.dark-mode .search-shell,
    body.dark-mode .notify-btn,
    body.dark-mode .profile-pill,
    body.dark-mode .video-card,
    body.dark-mode .pill,
    body.dark-mode .promo-box { background:rgba(18,30,47,.96); border-color:#24344d; box-shadow:0 18px 34px rgba(0,0,0,.28); }
    body.dark-mode .brand,
    body.dark-mode .profile-pill,
    body.dark-mode .body-card h5,
    body.dark-mode .section-head h2,
    body.dark-mode .notify-btn,
    body.dark-mode .meta a { color:#e6edf8; }
    body.dark-mode .side-link,
    body.dark-mode .search-shell input,
    body.dark-mode .promo-box p,
    body.dark-mode .desc,
    body.dark-mode .meta,
    body.dark-mode .pill { color:#a7b8d0; }
    body.dark-mode .section-head,
    body.dark-mode .promo-box,
    body.dark-mode .video-card,
    body.dark-mode .pill { border-color:#24344d; }
    body.dark-mode .avatar { background:#1d3353; color:#87b5ff; }
    body.dark-mode .thumb { background:#101b2a; }
    body.dark-mode .pill.active { color:#fff; }
  </style>
</head>
<body>
  <div class="layout d-lg-flex">
    <aside class="sidebar d-flex flex-column">
      <div class="brand"><span class="brand-icon"><i class="bi bi-journal-text"></i></span><span>StudyHub</span></div>
      <a href="dashboard.php" class="side-link"><i class="bi bi-grid"></i> Dashboard</a>
      <a href="notes.php?type=free" class="side-link"><i class="bi bi-file-earmark-text"></i> My Notes</a>
      <a href="video_lessons.php" class="side-link active"><i class="bi bi-camera-video"></i> Video Lessons</a>
      <a href="profile.php" class="side-link"><i class="bi bi-person"></i> My Profile</a>
      <a href="premium.php" class="side-link"><i class="bi bi-gem"></i> Premium</a>
      <a href="settings.php" class="side-link"><i class="bi bi-gear"></i> Settings</a>
      <a href="logout.php" class="side-link"><i class="bi bi-box-arrow-right"></i> Logout</a>
      <div class="promo-box mt-auto"><p><strong>Free plan</strong> Upgrade to unlock all premium notes across subjects.</p><a href="premium.php" class="btn btn-primary w-100">Upgrade to Premium</a></div>
    </aside>

    <main class="main">
      <div class="topbar">
        <div class="search-shell"><i class="bi bi-search"></i><input type="text" placeholder="Search notes, subjects or videos..."></div>
        <div class="top-actions">
          <a href="video_lessons.php" class="notify-btn"><i class="bi bi-bell"></i></a>
          <a href="profile.php" class="profile-pill"><span class="avatar"><?php echo strtoupper(substr($_SESSION['name'],0,1)); ?></span><span><?php echo htmlspecialchars($_SESSION['name']); ?></span></a>
        </div>
      </div>

      <section class="section-head"><h2>Video Lessons</h2><span class="tag">YOUTUBE-LINKED</span></section>
      <div class="pill-row">
        <a href="video_lessons.php" class="pill <?php echo $level === '' ? 'active' : ''; ?>">All Levels</a>
        <a href="video_lessons.php?level=beginner" class="pill <?php echo $level === 'beginner' ? 'active' : ''; ?>">Beginner</a>
        <a href="video_lessons.php?level=intermediate" class="pill <?php echo $level === 'intermediate' ? 'active' : ''; ?>">Intermediate</a>
        <a href="video_lessons.php?level=advanced" class="pill <?php echo $level === 'advanced' ? 'active' : ''; ?>">Advanced</a>
      </div>

      <?php if ($videoCount > 0): ?>
        <div class="cards">
          <?php while ($row = $result->fetch_assoc()): ?>
            <?php
            $embedUrl = '';
            if (!empty($row['youtube_link']) && preg_match('/(?:v=|youtu\.be\/|embed\/)([A-Za-z0-9_-]{11})/', $row['youtube_link'], $matches)) {
                $embedUrl = 'https://www.youtube.com/embed/' . $matches[1];
            }
            $imageFile = "../image_path/" . ($row['image_path'] ?? '');
            $imagePath = (!empty($row['image_path']) && file_exists($imageFile)) ? $imageFile : 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=900&q=80';
            $levelClass = match (strtolower($row['difficulty_level'] ?? 'beginner')) {
              'intermediate' => 'badge-intermediate',
              'advanced' => 'badge-advanced',
              default => 'badge-beginner',
            };
            $duration = strtoupper(substr((string) ($row['difficulty_level'] ?? 'B'), 0, 1)) . (18 + strlen($row['topic'])) . ':' . str_pad((string) (10 + strlen($row['subject'])), 2, '0', STR_PAD_LEFT);
            ?>
            <article class="video-card">
              <div class="thumb">
                <?php if ($embedUrl !== ''): ?><iframe src="<?php echo htmlspecialchars($embedUrl); ?>" title="Video lesson" allowfullscreen></iframe><?php else: ?><img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Video lesson"><?php endif; ?>
                <span class="duration"><?php echo $duration; ?></span>
              </div>
              <div class="body-card">
                <div class="badge-row">
                  <span class="badge-chip badge-subject"><?php echo htmlspecialchars($row['subject']); ?></span>
                  <span class="badge-chip <?php echo $levelClass; ?>"><?php echo ucfirst($row['difficulty_level'] ?? 'beginner'); ?></span>
                </div>
                <h5><?php echo htmlspecialchars($row['topic']); ?></h5>
                <p class="desc"><?php echo ucfirst($row['material_format'] ?? 'pdf'); ?> support for this lesson.</p>
                <div class="meta">
                  <span class="stars"><i class="bi bi-star-fill me-1"></i><?php echo number_format((float) ($row['rating'] ?? 4), 1); ?></span>
                  <a href="<?php echo htmlspecialchars($row['youtube_link'] ?? '#'); ?>" target="_blank">Watch on YouTube <i class="bi bi-arrow-up-right"></i></a>
                </div>
              </div>
            </article>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <div class="video-card p-4 text-center"><h4 class="fw-bold">No videos found</h4><p class="text-muted mb-0">Try another level or add lessons from admin.</p></div>
      <?php endif; ?>
    </main>
  </div>
  <script>
    if (localStorage.getItem('studyhubDarkMode') === '1') {
      document.body.classList.add('dark-mode');
    }
  </script>
</body>
</html>
