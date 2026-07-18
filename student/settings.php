<?php
include '../includes/config.php';
include '../includes/auth_student.php';

$notifications = [];
$notificationsResult = $conn->query("
    SELECT subject, topic, resource_type, note_type, difficulty_level, upload_date
    FROM notes_final
    ORDER BY upload_date DESC
    LIMIT 4
");

if ($notificationsResult) {
    while ($row = $notificationsResult->fetch_assoc()) {
        $title = 'New note added';
        if (($row['resource_type'] ?? 'note') === 'video') {
            $title = 'Video added';
        } elseif (($row['note_type'] ?? 'free') === 'premium') {
            $title = 'Premium note added';
        }

        $notifications[] = [
            'title' => $title,
            'message' => ($row['subject'] ?? 'Study Material') . ' - ' . ($row['topic'] ?? 'New update') . ' (' . ucfirst($row['difficulty_level'] ?? 'beginner') . ')',
            'time' => $row['upload_date'] ?? null
        ];
    }
}

function timeAgo(?string $datetime): string
{
    if (empty($datetime)) {
        return 'Recently';
    }

    $timestamp = strtotime($datetime);
    if ($timestamp === false) {
        return 'Recently';
    }

    $diff = time() - $timestamp;
    if ($diff < 60) {
        return 'Just now';
    }
    if ($diff < 3600) {
        return floor($diff / 60) . ' min ago';
    }
    if ($diff < 86400) {
        return floor($diff / 3600) . ' hr ago';
    }
    if ($diff < 604800) {
        return floor($diff / 86400) . ' day' . (floor($diff / 86400) > 1 ? 's' : '') . ' ago';
    }

    return date('d M Y', $timestamp);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Settings | StudyHub</title>
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
    .search-shell,.notify-btn,.profile-pill,.settings-card { background:rgba(255,255,255,.96); border:1px solid var(--border); box-shadow:var(--shadow); }
    .search-shell { display:flex; align-items:center; gap:.65rem; width:min(360px,100%); border-radius:16px; padding:.72rem .95rem; }
    .search-shell input { width:100%; border:0; outline:0; background:transparent; font-size:.92rem; } .search-shell i { color:var(--blue-primary); }
    .top-actions { display:flex; align-items:center; gap:.7rem; }
    .notify-wrap { position:relative; }
    .notify-btn { width:42px; height:42px; border-radius:14px; display:inline-flex; align-items:center; justify-content:center; color:var(--blue-deep); text-decoration:none; position:relative; border:0; cursor:pointer; }
    .notify-dot { position:absolute; top:8px; right:9px; width:7px; height:7px; border-radius:50%; background:#e0483f; }
    .notify-btn.is-disabled { opacity:.55; cursor:default; }
    .notify-panel { position:absolute; top:calc(100% + 12px); right:0; width:min(360px,calc(100vw - 2rem)); background:rgba(255,255,255,.98); border:1px solid var(--border); border-radius:22px; box-shadow:0 24px 50px rgba(20,87,179,.16); padding:1rem; opacity:0; visibility:hidden; transform:translateY(-8px); transition:opacity .18s ease, transform .18s ease, visibility .18s ease; z-index:30; }
    .notify-wrap.open .notify-panel { opacity:1; visibility:visible; transform:translateY(0); }
    .notify-head { display:flex; align-items:center; justify-content:space-between; gap:.75rem; margin-bottom:.8rem; }
    .notify-head h3 { margin:0; font-size:1.05rem; font-weight:800; color:#0f1726; }
    .notify-badge { min-width:28px; height:28px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center; background:var(--blue-pale); color:var(--blue-primary); font-size:.82rem; font-weight:800; padding:0 .55rem; }
    .notify-list { display:flex; flex-direction:column; gap:.7rem; }
    .notify-item { padding:.9rem 1rem; border-radius:18px; background:#f8fbff; border:1px solid #e9f0fb; }
    .notify-item strong { display:block; font-size:1rem; color:#101827; margin-bottom:.2rem; }
    .notify-item p { margin:0; color:var(--soft-gray); line-height:1.4; font-size:.95rem; }
    .notify-time { display:inline-block; margin-top:.42rem; color:var(--blue-primary); font-size:.82rem; font-weight:700; }
    .notify-empty { padding:1rem; border-radius:18px; background:#f8fbff; color:var(--soft-gray); text-align:center; }
    .profile-pill { display:flex; align-items:center; gap:.6rem; border-radius:999px; padding:.35rem .8rem .35rem .35rem; color:var(--ink); text-decoration:none; font-weight:700; font-size:.92rem; }
    .avatar { width:34px; height:34px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; background:var(--blue-pale); color:var(--blue-primary); font-weight:800; }
    .section-head { display:flex; align-items:center; gap:1rem; margin:1rem 0 1rem; padding-bottom:.45rem; border-bottom:1px solid var(--border); }
    .section-head h2 { font-size:1.45rem; margin:0; }
    .settings-card { width:min(470px,100%); border-radius:22px; overflow:hidden; }
    .setting-row { display:flex; justify-content:space-between; gap:1rem; align-items:center; padding:1rem 1.15rem; border-bottom:1px solid var(--border); }
    .setting-row:last-child { border-bottom:0; }
    .setting-row h5 { font-size:1rem; font-weight:800; margin-bottom:.22rem; }
    .setting-row p { font-size:.84rem; color:var(--soft-gray); margin:0; }
    .toggle { width:36px; height:22px; border-radius:999px; background:#dfe7f5; position:relative; flex-shrink:0; border:0; cursor:pointer; }
    .toggle::after { content:""; position:absolute; top:3px; left:3px; width:16px; height:16px; border-radius:50%; background:#fff; box-shadow:0 1px 2px rgba(0,0,0,.15); }
    .toggle.on { background:#4a79dc; }
    .toggle.on::after { left:17px; }
    .toggle-static { cursor:default; }
    body.dark-mode { background:linear-gradient(180deg,#0f1724 0%,#121d2d 100%); color:#e6edf8; }
    body.dark-mode .sidebar,
    body.dark-mode .search-shell,
    body.dark-mode .notify-btn,
    body.dark-mode .profile-pill,
    body.dark-mode .settings-card,
    body.dark-mode .notify-panel,
    body.dark-mode .notify-item,
    body.dark-mode .notify-empty,
    body.dark-mode .promo-box { background:rgba(18,30,47,.96); border-color:#24344d; box-shadow:0 18px 34px rgba(0,0,0,.28); }
    body.dark-mode .brand,
    body.dark-mode .section-head h2,
    body.dark-mode .setting-row h5,
    body.dark-mode .profile-pill,
    body.dark-mode .notify-head h3,
    body.dark-mode .notify-item strong,
    body.dark-mode .notify-btn { color:#e6edf8; }
    body.dark-mode .side-link { color:#9fb0c9; }
    body.dark-mode .side-link.active,
    body.dark-mode .side-link:hover { color:#fff; }
    body.dark-mode .search-shell input,
    body.dark-mode .setting-row p,
    body.dark-mode .promo-box p,
    body.dark-mode .notify-item p,
    body.dark-mode .notify-empty { color:#a7b8d0; }
    body.dark-mode .avatar { background:#1d3353; color:#87b5ff; }
    body.dark-mode .section-head,
    body.dark-mode .setting-row { border-color:#24344d; }
    body.dark-mode .notify-badge { background:#1d3353; color:#87b5ff; }
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
      .main { padding:1rem; }
      .notify-panel { right:auto; left:0; width:100%; }
      .section-head { margin-top:.4rem; }
      .setting-row { padding:.95rem 1rem; }
    }
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
      <a href="premium.php" class="side-link"><i class="bi bi-gem"></i> Premium</a>
      <a href="settings.php" class="side-link active"><i class="bi bi-gear"></i> Settings</a>
      <a href="logout.php" class="side-link"><i class="bi bi-box-arrow-right"></i> Logout</a>
      <div class="promo-box mt-auto"><p><strong>Free plan</strong> Upgrade to unlock all premium notes across subjects.</p><a href="premium.php" class="btn btn-primary w-100">Upgrade to Premium</a></div>
    </aside>

    <main class="main">
      <div class="topbar">
        <div class="search-shell"><i class="bi bi-search"></i><input type="text" placeholder="Search notes, subjects or videos..."></div>
        <div class="top-actions">
          <div class="notify-wrap" id="notifyWrap">
            <button type="button" class="notify-btn" id="notifyToggle" aria-label="Open notifications" aria-expanded="false">
              <i class="bi bi-bell"></i>
              <?php if (!empty($notifications)): ?><span class="notify-dot" id="notifyDot"></span><?php endif; ?>
            </button>
            <div class="notify-panel" id="notifyPanel">
              <div class="notify-head">
                <h3>What's New</h3>
                <span class="notify-badge"><?php echo count($notifications); ?></span>
              </div>
              <div class="notify-list">
                <?php if (!empty($notifications)): ?>
                  <?php foreach ($notifications as $notification): ?>
                    <article class="notify-item">
                      <strong><?php echo htmlspecialchars($notification['title']); ?></strong>
                      <p><?php echo htmlspecialchars($notification['message']); ?></p>
                      <span class="notify-time"><?php echo htmlspecialchars(timeAgo($notification['time'])); ?></span>
                    </article>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="notify-empty">No new updates yet.</div>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <a href="profile.php" class="profile-pill"><span class="avatar"><?php echo strtoupper(substr($_SESSION['name'],0,1)); ?></span><span><?php echo htmlspecialchars($_SESSION['name']); ?></span></a>
        </div>
      </div>

      <section class="section-head"><h2>Settings</h2></section>
      <div class="settings-card">
        <div class="setting-row"><div><h5>Dark mode</h5><p>Switch the interface to a darker theme</p></div><button type="button" class="toggle" id="darkModeToggle" aria-label="Toggle dark mode" aria-pressed="false"></button></div>
        <div class="setting-row"><div><h5>New content notifications</h5><p>Get notified when new notes or videos are added</p></div><button type="button" class="toggle on" id="notificationsToggle" aria-label="Toggle notifications" aria-pressed="true"></button></div>
        <div class="setting-row"><div><h5>Premium expiry reminders</h5><p>Reminder emails before your plan expires</p></div><span class="toggle on toggle-static" title="Coming soon"></span></div>
        <div class="setting-row"><div><h5>Weekly progress summary</h5><p>A recap of notes read and videos watched</p></div><span class="toggle toggle-static" title="Coming soon"></span></div>
      </div>
    </main>
  </div>
  <script>
    const darkModeKey = 'studyhubDarkMode';
    const notificationsKey = 'studyhubNotificationsEnabled';
    const body = document.body;
    const darkModeToggle = document.getElementById('darkModeToggle');
    const notificationsToggle = document.getElementById('notificationsToggle');
    const notifyWrap = document.getElementById('notifyWrap');
    const notifyToggle = document.getElementById('notifyToggle');
    const notifyDot = document.getElementById('notifyDot');

    function setToggleState(element, enabled) {
      if (!element) {
        return;
      }
      element.classList.toggle('on', enabled);
      element.setAttribute('aria-pressed', enabled ? 'true' : 'false');
    }

    function applyDarkMode(enabled) {
      body.classList.toggle('dark-mode', enabled);
      setToggleState(darkModeToggle, enabled);
      localStorage.setItem(darkModeKey, enabled ? '1' : '0');
    }

    function applyNotifications(enabled) {
      setToggleState(notificationsToggle, enabled);
      localStorage.setItem(notificationsKey, enabled ? '1' : '0');

      if (notifyDot) {
        notifyDot.style.display = enabled ? 'block' : 'none';
      }

      if (notifyToggle) {
        notifyToggle.classList.toggle('is-disabled', !enabled);
        notifyToggle.setAttribute('aria-disabled', enabled ? 'false' : 'true');
      }

      if (!enabled && notifyWrap) {
        notifyWrap.classList.remove('open');
        notifyToggle.setAttribute('aria-expanded', 'false');
      }
    }

    const savedDarkMode = localStorage.getItem(darkModeKey) === '1';
    const savedNotifications = localStorage.getItem(notificationsKey);
    applyDarkMode(savedDarkMode);
    applyNotifications(savedNotifications !== '0');

    if (darkModeToggle) {
      darkModeToggle.addEventListener('click', function () {
        applyDarkMode(!body.classList.contains('dark-mode'));
      });
    }

    if (notificationsToggle) {
      notificationsToggle.addEventListener('click', function () {
        const isEnabled = notificationsToggle.classList.contains('on');
        applyNotifications(!isEnabled);
      });
    }

    if (notifyWrap && notifyToggle) {
      notifyToggle.addEventListener('click', function (event) {
        if (localStorage.getItem(notificationsKey) === '0') {
          return;
        }

        event.stopPropagation();
        const isOpen = notifyWrap.classList.toggle('open');
        notifyToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      });

      document.addEventListener('click', function (event) {
        if (!notifyWrap.contains(event.target)) {
          notifyWrap.classList.remove('open');
          notifyToggle.setAttribute('aria-expanded', 'false');
        }
      });

      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
          notifyWrap.classList.remove('open');
          notifyToggle.setAttribute('aria-expanded', 'false');
        }
      });
    }
  </script>
</body>
</html>
