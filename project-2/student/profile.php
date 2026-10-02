<?php
include '../includes/config.php';
include '../includes/auth_student.php';

$userId = (int) $_SESSION['user_id'];
$user = ['name' => $_SESSION['name'] ?? 'Student', 'email' => $_SESSION['email'] ?? ''];
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
if ($stmt) {
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows === 1) {
        $user = $res->fetch_assoc();
    }
}

$statsRow = ['notes_read' => 0, 'videos_watched' => 0];
$statsResult = $conn->query("
    SELECT
      SUM(CASE WHEN resource_type = 'note' THEN 1 ELSE 0 END) AS notes_read,
      SUM(CASE WHEN resource_type = 'video' THEN 1 ELSE 0 END) AS videos_watched
    FROM notes_final
");
if ($statsResult && $statsResult->num_rows === 1) {
    $statsRow = $statsResult->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Profile | StudyHub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root { --blue-deep:#0b3a75; --blue-primary:#1f6fe0; --blue-strong:#1457b3; --blue-pale:#eaf2fe; --blue-bg:#f5f9ff; --white:#fff; --ink:#1c2530; --soft-gray:#6b7688; --border:#e3ebf7; --shadow:0 16px 34px rgba(11,58,117,.08); }
    body { margin:0; background:linear-gradient(180deg,var(--blue-bg) 0%,#eef4fd 100%); color:var(--ink); font-family:"Segoe UI",sans-serif; font-size:14px; }
    .layout { min-height:100vh; }
    .sidebar { width:230px; min-width:230px; background:rgba(255,255,255,.96); border-right:1px solid var(--border); padding:1.15rem 1rem; position:sticky; top:0; height:100vh; }
    .brand { display:flex; align-items:center; gap:.75rem; color:var(--blue-deep); font-size:1.45rem; font-weight:800; margin-bottom:1.6rem; }
    .brand-icon { width:38px; height:38px; border-radius:12px; display:inline-flex; align-items:center; justify-content:center; background:linear-gradient(135deg,var(--blue-deep),var(--blue-primary)); color:var(--white); }
    .side-link { display:flex; align-items:center; gap:.8rem; padding:.82rem .9rem; border-radius:15px; text-decoration:none; color:var(--soft-gray); font-weight:600; font-size:.95rem; margin-bottom:.42rem; }
    .side-link.active,.side-link:hover { background:linear-gradient(135deg,var(--blue-primary),var(--blue-strong)); color:var(--white); }
    .promo-box { margin-top:auto; padding:1rem; border:1px solid var(--border); border-radius:18px; background:linear-gradient(180deg,#f8fbff 0%,#f1f6ff 100%); }
    .promo-box p { color:var(--soft-gray); font-size:.87rem; margin-bottom:.8rem; }
    .promo-box .btn { border-radius:12px; font-size:.88rem; font-weight:700; }
    .main { flex:1; padding:1.2rem 1.35rem 1.8rem; }
    .topbar { display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:1rem; }
    .search-shell,.notify-btn,.profile-pill,.profile-card,.stat-box,.input-box { background:rgba(255,255,255,.96); border:1px solid var(--border); box-shadow:var(--shadow); }
    .search-shell { display:flex; align-items:center; gap:.65rem; width:min(350px,100%); border-radius:16px; padding:.72rem .95rem; }
    .search-shell input { width:100%; border:0; outline:0; background:transparent; font-size:.92rem; }
    .search-shell i { color:var(--blue-primary); }
    .top-actions { display:flex; align-items:center; gap:.7rem; }
    .notify-btn { width:42px; height:42px; border-radius:14px; display:inline-flex; align-items:center; justify-content:center; color:var(--blue-deep); text-decoration:none; position:relative; }
    .notify-btn::after { content:""; position:absolute; top:8px; right:9px; width:7px; height:7px; border-radius:50%; background:#e0483f; }
    .profile-pill { display:flex; align-items:center; gap:.6rem; border-radius:999px; padding:.35rem .8rem .35rem .35rem; color:var(--ink); text-decoration:none; font-weight:700; font-size:.92rem; }
    .avatar { width:34px; height:34px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; background:var(--blue-pale); color:var(--blue-primary); font-weight:800; }
    .profile-card { border-radius:22px; padding:1.4rem; }
    .profile-head { display:flex; align-items:center; gap:1rem; padding-bottom:1rem; border-bottom:1px solid var(--border); margin-bottom:1rem; }
    .avatar-lg { width:52px; height:52px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; background:var(--blue-pale); color:var(--blue-primary); font-size:1.8rem; font-weight:800; }
    .profile-head h2 { font-size:1.9rem; margin-bottom:.2rem; font-weight:800; }
    .profile-head p { margin:0; color:var(--soft-gray); font-size:.9rem; }
    .stats-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.8rem; margin-bottom:1rem; }
    .stat-box { border-radius:16px; padding:.95rem; text-align:center; }
    .stat-box strong { display:block; color:var(--blue-deep); font-size:1.6rem; line-height:1; margin-bottom:.15rem; }
    .stat-box span { color:var(--soft-gray); font-size:.82rem; }
    .section-title { font-size:1.15rem; font-weight:800; margin:1rem 0 .8rem; }
    .detail-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.8rem; }
    .form-row { display:flex; flex-direction:column; gap:.38rem; }
    .form-row label { color:var(--soft-gray); font-size:.84rem; }
    .input-box { border-radius:12px; padding:.82rem .9rem; min-height:46px; display:flex; align-items:center; font-size:.92rem; }
    @media (max-width:1199px) { .main { padding:1rem 1rem 1.4rem; } .search-shell { width:min(100%,320px); } }
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
      .sidebar { gap:.55rem; padding:.9rem; }
      .brand { justify-content:flex-start; }
      .side-link { flex-basis:100%; justify-content:flex-start; text-align:left; }
      .topbar { flex-direction:column; align-items:stretch; }
      .top-actions { width:100%; justify-content:space-between; }
      .profile-pill { flex:1; justify-content:center; }
      .search-shell { width:100%; }
      .profile-head { flex-direction:column; align-items:flex-start; }
      .stats-grid, .detail-grid { grid-template-columns:1fr; }
      .main { padding:1rem; }
    }
    body.dark-mode { background:linear-gradient(180deg,#0f1724 0%,#121d2d 100%); color:#e6edf8; }
    body.dark-mode .sidebar,
    body.dark-mode .search-shell,
    body.dark-mode .notify-btn,
    body.dark-mode .profile-pill,
    body.dark-mode .profile-card,
    body.dark-mode .stat-box,
    body.dark-mode .input-box,
    body.dark-mode .promo-box { background:rgba(18,30,47,.96); border-color:#24344d; box-shadow:0 18px 34px rgba(0,0,0,.28); }
    body.dark-mode .brand,
    body.dark-mode .profile-pill,
    body.dark-mode .profile-head h2,
    body.dark-mode .notify-btn,
    body.dark-mode .section-title,
    body.dark-mode .stat-box strong { color:#e6edf8; }
    body.dark-mode .side-link,
    body.dark-mode .search-shell input,
    body.dark-mode .promo-box p,
    body.dark-mode .profile-head p,
    body.dark-mode .stat-box span,
    body.dark-mode .form-row label,
    body.dark-mode .input-box { color:#a7b8d0; }
    body.dark-mode .profile-head,
    body.dark-mode .promo-box { border-color:#24344d; }
    body.dark-mode .avatar,
    body.dark-mode .avatar-lg { background:#1d3353; color:#87b5ff; }
  </style>
</head>
<body>
  <div class="layout d-lg-flex">
    <aside class="sidebar d-flex flex-column">
      <div class="brand"><span class="brand-icon"><i class="bi bi-journal-text"></i></span><span>StudyHub</span></div>
      <a href="dashboard.php" class="side-link"><i class="bi bi-grid"></i> Dashboard</a>
      <a href="notes.php?type=free" class="side-link"><i class="bi bi-file-earmark-text"></i> My Notes</a>
      <a href="video_lessons.php" class="side-link"><i class="bi bi-camera-video"></i> Video Lessons</a>
      <a href="profile.php" class="side-link active"><i class="bi bi-person"></i> My Profile</a>
      <a href="premium.php" class="side-link"><i class="bi bi-gem"></i> Premium</a>
      <a href="settings.php" class="side-link"><i class="bi bi-gear"></i> Settings</a>
      <a href="logout.php" class="side-link"><i class="bi bi-box-arrow-right"></i> Logout</a>
      <div class="promo-box mt-auto"><p><strong>Free plan</strong> Upgrade to unlock all premium notes across subjects.</p><a href="premium.php" class="btn btn-primary w-100">Upgrade to Premium</a></div>
    </aside>

    <main class="main">
      <div class="topbar">
        <!-- <div class="search-shell"><i class="bi bi-search"></i><input type="text" placeholder="Search notes, subjects or videos..."></div> -->
        <div class="top-actions">
          <a href="video_lessons.php" class="notify-btn"><i class="bi bi-bell"></i></a>
          <!-- <a href="profile.php" class="profile-pill"><span class="avatar"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></span><span><?php echo htmlspecialchars($user['name']); ?></span></a> -->
        </div>
      </div>

      <section class="profile-card">
        <div class="profile-head">
          <span class="avatar-lg"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></span>
          <div>
            <h2><?php echo htmlspecialchars($user['name']); ?></h2>
            <p>StudyHub student account</p>
          </div>
        </div>

        <div class="stats-grid">
          <div class="stat-box"><strong><?php echo (int) ($statsRow['notes_read'] ?? 0); ?></strong><span>Notes read</span></div>
          <div class="stat-box"><strong><?php echo (int) ($statsRow['videos_watched'] ?? 0); ?></strong><span>Videos watched</span></div>
        </div>

        <div class="section-title">Account details</div>
        <div class="detail-grid">
          <div class="form-row"><label>Full name</label><div class="input-box"><?php echo htmlspecialchars($user['name']); ?></div></div>
          <div class="form-row"><label>Email</label><div class="input-box"><?php echo htmlspecialchars($user['email']); ?></div></div>
        </div>
      </section>
    </main>
  </div>
  <script>
    if (localStorage.getItem('studyhubDarkMode') === '1') {
      document.body.classList.add('dark-mode');
    }
  </script>
</body>
</html>
