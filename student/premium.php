<?php
include '../includes/config.php';
include '../includes/auth_student.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Premium | StudyHub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root { --blue-deep:#0b3a75; --blue-primary:#1f6fe0; --blue-strong:#1457b3; --blue-pale:#eaf2fe; --blue-bg:#f5f9ff; --white:#fff; --ink:#1c2530; --soft-gray:#6b7688; --border:#e3ebf7; --shadow:0 16px 34px rgba(11,58,117,.08); }
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
    .search-shell,.notify-btn,.profile-pill,.plan-card { background:rgba(255,255,255,.96); border:1px solid var(--border); box-shadow:var(--shadow); }
    .search-shell { display:flex; align-items:center; gap:.65rem; width:min(360px,100%); border-radius:16px; padding:.72rem .95rem; }
    .search-shell input { width:100%; border:0; outline:0; background:transparent; font-size:.92rem; } .search-shell i { color:var(--blue-primary); }
    .top-actions { display:flex; align-items:center; gap:.7rem; }
    .notify-btn { width:42px; height:42px; border-radius:14px; display:inline-flex; align-items:center; justify-content:center; color:var(--blue-deep); text-decoration:none; position:relative; }
    .notify-btn::after { content:""; position:absolute; top:8px; right:9px; width:7px; height:7px; border-radius:50%; background:#e0483f; }
    .profile-pill { display:flex; align-items:center; gap:.6rem; border-radius:999px; padding:.35rem .8rem .35rem .35rem; color:var(--ink); text-decoration:none; font-weight:700; font-size:.92rem; }
    .avatar { width:34px; height:34px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; background:var(--blue-pale); color:var(--blue-primary); font-weight:800; }
    .section-head { display:flex; align-items:center; gap:1rem; margin:1rem 0 1rem; padding-bottom:.45rem; border-bottom:1px solid var(--border); }
    .section-head h2 { font-size:1.45rem; margin:0; }
    .plans { display:grid; grid-template-columns:repeat(2,minmax(0,260px)); gap:1rem; }
    .plan-card { border-radius:22px; padding:1.35rem 1.3rem; position:relative; }
    .plan-card.featured { border-color:var(--blue-primary); }
    .popular { position:absolute; top:-11px; left:18px; background:var(--blue-primary); color:#fff; border-radius:999px; padding:.24rem .58rem; font-size:.67rem; font-weight:800; }
    .plan-card h3 { font-size:1.35rem; font-weight:800; margin-bottom:.55rem; }
    .price { font-size:2rem; color:var(--blue-deep); font-weight:800; line-height:1; }
    .price span { font-size:.95rem; color:var(--soft-gray); font-weight:600; }
    .features { list-style:none; padding:0; margin:1rem 0 1.2rem; }
    .features li { font-size:.88rem; color:var(--ink); margin-bottom:.55rem; }
    .features li.muted { color:#9aa6ba; }
    .btn-main { min-height:42px; border-radius:12px; font-size:.88rem; font-weight:700; }
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
      .plans { grid-template-columns:1fr; }
      .sidebar { gap:.55rem; padding:.9rem; }
      .brand { justify-content:flex-start; }
      .side-link { flex-basis:100%; justify-content:flex-start; text-align:left; }
      .topbar { flex-direction:column; align-items:stretch; }
      .top-actions { width:100%; justify-content:space-between; }
      .profile-pill { flex:1; justify-content:center; }
      .search-shell { width:100%; }
      .main { padding:1rem; }
      .section-head { margin-top:.4rem; }
    }
    body.dark-mode { background:linear-gradient(180deg,#0f1724 0%,#121d2d 100%); color:#e6edf8; }
    body.dark-mode .sidebar,
    body.dark-mode .search-shell,
    body.dark-mode .notify-btn,
    body.dark-mode .profile-pill,
    body.dark-mode .plan-card,
    body.dark-mode .promo-box { background:rgba(18,30,47,.96); border-color:#24344d; box-shadow:0 18px 34px rgba(0,0,0,.28); }
    body.dark-mode .brand,
    body.dark-mode .profile-pill,
    body.dark-mode .section-head h2,
    body.dark-mode .plan-card h3,
    body.dark-mode .notify-btn,
    body.dark-mode .price { color:#e6edf8; }
    body.dark-mode .side-link,
    body.dark-mode .search-shell input,
    body.dark-mode .promo-box p,
    body.dark-mode .price span,
    body.dark-mode .features li { color:#a7b8d0; }
    body.dark-mode .features li.muted { color:#70829f; }
    body.dark-mode .section-head,
    body.dark-mode .promo-box { border-color:#24344d; }
    body.dark-mode .avatar { background:#1d3353; color:#87b5ff; }
  </style>
</head>
<body>
  <div class="layout d-lg-flex">
    <aside class="sidebar d-flex flex-column">
      <div class="brand"><span class="brand-icon"><i class="bi bi-journal-text"></i></span><span>StudyHub</span></div>
      <a href="dashboard.php" class="side-link"><i class="bi bi-grid"></i> Dashboard</a>
      <a href="notes.php?type=free" class="side-link"><i class="bi bi-file-earmark-text"></i> My Notes</a>
      <a href="video_lessons.php" class="side-link"><i class="bi bi-camera-video"></i> Video Lessons</a>
      <a href="profile.php" class="side-link"><i class="bi bi-person"></i> My Profile</a>
      <a href="premium.php" class="side-link active"><i class="bi bi-gem"></i> Premium</a>
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

      <section class="section-head"><h2>Choose your plan</h2></section>
      <div class="plans">
        <div class="plan-card">
          <h3>Free</h3>
          <div class="price">₹0<span>/forever</span></div>
          <ul class="features">
            <li>✓ All free notes across subjects</li>
            <li>✓ All embedded video lessons</li>
            <li>✓ Ratings & feedback</li>
            <li class="muted">X Premium notes</li>
            <li class="muted">X Downloadable offline PDFs</li>
          </ul>
          <button class="btn btn-light w-100 btn-main">Current plan</button>
        </div>
        <div class="plan-card featured">
          <span class="popular">Most popular</span>
          <h3>Premium</h3>
          <div class="price">₹199<span>/month</span></div>
          <ul class="features">
            <li>✓ Everything in Free</li>
            <li>✓ All premium notes, unlocked instantly</li>
            <li>✓ Priority notifications for new uploads</li>
            <li>✓ Downloadable offline PDFs</li>
            <li>✓ Ad-free experience</li>
          </ul>
          <button class="btn btn-primary w-100 btn-main">Upgrade now</button>
        </div>
      </div>
    </main>
  </div>
  <script>
    if (localStorage.getItem('studyhubDarkMode') === '1') {
      document.body.classList.add('dark-mode');
    }
  </script>
</body>
</html>
