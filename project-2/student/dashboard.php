<?php
include '../includes/config.php';
include '../includes/auth_student.php';

$noteCards = [];
$videoCards = [];
$notifications = [];
$stats = [
    'notes' => 0,
    'videos' => 0,
    'free' => 0,
    'premium' => 0
];

$statsResult = $conn->query("
    SELECT
        SUM(CASE WHEN resource_type = 'note' THEN 1 ELSE 0 END) AS note_count,
        SUM(CASE WHEN resource_type = 'video' THEN 1 ELSE 0 END) AS video_count,
        SUM(CASE WHEN note_type = 'free' THEN 1 ELSE 0 END) AS free_count,
        SUM(CASE WHEN note_type = 'premium' THEN 1 ELSE 0 END) AS premium_count
    FROM notes_final
");

if ($statsResult && $statsResult->num_rows === 1) {
    $row = $statsResult->fetch_assoc();
    $stats['notes'] = (int) ($row['note_count'] ?? 0);
    $stats['videos'] = (int) ($row['video_count'] ?? 0);
    $stats['free'] = (int) ($row['free_count'] ?? 0);
    $stats['premium'] = (int) ($row['premium_count'] ?? 0);
}

$notesResult = $conn->query("
    SELECT * FROM notes_final
    WHERE resource_type = 'note'
    ORDER BY upload_date DESC
    LIMIT 5
");
if ($notesResult) {
    while ($row = $notesResult->fetch_assoc()) {
        $noteCards[] = $row;
    }
}

$videosResult = $conn->query("
    SELECT * FROM notes_final
    WHERE resource_type = 'video'
    ORDER BY upload_date DESC
    LIMIT 5
");
if ($videosResult) {
    while ($row = $videosResult->fetch_assoc()) {
        $videoCards[] = $row;
    }
}

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

        $meta = ($row['subject'] ?? 'Study Material') . ' - ' . ($row['topic'] ?? 'New update');
        $level = ucfirst($row['difficulty_level'] ?? 'beginner');
        $notifications[] = [
            'title' => $title,
            'message' => $meta . ' (' . $level . ')',
            'time' => $row['upload_date'] ?? null
        ];
    }
}

function noteThumbnail(array $row): string
{
    $imagePath = $row['image_path'] ?? '';
    if (!empty($imagePath) && file_exists("../image_path/" . $imagePath)) {
        return "../image_path/" . $imagePath;
    }
    return 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop&w=900&q=80';
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
  <title>Student Dashboard | StudyHub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root {
      --blue-deep: #0b3a75;
      --blue-primary: #1f6fe0;
      --blue-pale: #eaf2fe;
      --blue-bg: #f5f9ff;
      --white: #ffffff;
      --ink: #1c2530;
      --soft-gray: #6b7688;
      --border: #e3ebf7;
      --green: #1f9d63;
      --amber: #e08a00;
      --indigo: #4b3fd6;
      --gold: #f5b400;
      --shadow: 0 2px 6px rgba(20,87,179,0.06), 0 10px 28px rgba(20,87,179,0.08);
    }

    * { box-sizing: border-box; }

    body {
      margin: 0;
      background: linear-gradient(180deg, var(--blue-bg) 0%, #eef4fd 100%);
      color: var(--ink);
      font-family: "Segoe UI", sans-serif;
      font-size: 16px;
      overflow-x: hidden;
    }

    .dashboard-layout { display: flex; min-height: 100vh; }

    .sidebar {
      width:230px;background:var(--white);border-right:1px solid var(--line);
    padding:26px 18px;display:flex;flex-direction:column;gap:6px;position:sticky;top:0;height:100vh;
    }

     .brand {
      display: flex;
      align-items: center;
      gap: .75rem;
      color: var(--blue-deep);
      font-size: 1.45rem;
      font-weight: 500;
      margin-bottom: 1.6rem;
    } 


    .brand-icon {
      width: 36px;
      height: 36px;
      border-radius: 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(155deg, var(--blue-deep), var(--blue-primary));
      color: var(--white);
    }

    .side-link {
      display: flex;
      align-items: center;
      gap: .8rem;
      padding: .82rem .9rem;
      border-radius: 15px;
      text-decoration: none;
      color: var(--soft-gray);
      font-weight: 600;
      font-size: .95rem;
      margin-bottom: .42rem;
    }

    .side-link.active,
    .side-link:hover {
      background: linear-gradient(135deg, var(--blue-primary), #1457b3);
      color: var(--white);
    }

    .main-area {
      flex: 1;
      padding: 1.2rem 1.35rem 1.8rem;
    }

    .topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: 1.1rem;
    }

    .search-shell,
    .notify-btn,
    .profile-pill,
    .hero-card,
    .info-card,
    .content-card {
      background: rgba(255,255,255,.96);
      border: 1px solid var(--border);
      box-shadow: var(--shadow);
    }

    .search-shell {
      display: flex;
      align-items: center;
      gap: .7rem;
      width: min(420px, 100%);
      border-radius: 18px;
      padding: .76rem 1rem;
    }

    .search-shell input {
      width: 100%;
      border: 0;
      outline: 0;
      background: transparent;
      color: var(--ink);
      font-size: .94rem;
    }

    .search-shell i { color: var(--blue-primary); }

    .top-actions {
      display: flex;
      align-items: center;
      gap: .75rem;
    }

    .notify-wrap {
      position: relative;
    }

    .notify-btn {
      width: 44px;
      height: 44px;
      border-radius: 16px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
      color: var(--blue-deep);
      position: relative;
      cursor: pointer;
      border: 0;
    }

    .notify-dot {
      position: absolute;
      top: 8px;
      right: 9px;
      width: 8px;
      height: 8px;
      border-radius: 999px;
      background: #e0483f;
    }

    .notify-panel {
      position: absolute;
      top: calc(100% + 12px);
      right: 0;
      width: min(360px, calc(100vw - 2rem));
      background: rgba(255,255,255,.98);
      border: 1px solid var(--border);
      border-radius: 22px;
      box-shadow: 0 24px 50px rgba(20,87,179,0.16);
      padding: 1rem;
      opacity: 0;
      visibility: hidden;
      transform: translateY(-8px);
      transition: opacity .18s ease, transform .18s ease, visibility .18s ease;
      z-index: 30;
    }

    .notify-wrap.open .notify-panel {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }

    .notify-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: .75rem;
      margin-bottom: .8rem;
    }

    .notify-head h3 {
      margin: 0;
      font-size: 1.05rem;
      font-weight: 800;
      color: #0f1726;
    }

    .notify-badge {
      min-width: 28px;
      height: 28px;
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: var(--blue-pale);
      color: var(--blue-primary);
      font-size: .82rem;
      font-weight: 800;
      padding: 0 .55rem;
    }

    .notify-list {
      display: flex;
      flex-direction: column;
      gap: .7rem;
    }

    .notify-item {
      padding: .9rem 1rem;
      border-radius: 18px;
      background: #f8fbff;
      border: 1px solid #e9f0fb;
    }

    .notify-item strong {
      display: block;
      font-size: 1rem;
      color: #101827;
      margin-bottom: .2rem;
    }

    .notify-item p {
      margin: 0;
      color: var(--soft-gray);
      line-height: 1.4;
      font-size: .95rem;
    }

    .notify-time {
      display: inline-block;
      margin-top: .42rem;
      color: var(--blue-primary);
      font-size: .82rem;
      font-weight: 700;
    }

    .notify-empty {
      padding: 1rem;
      border-radius: 18px;
      background: #f8fbff;
      color: var(--soft-gray);
      text-align: center;
    }

    .profile-pill {
      display: flex;
      align-items: center;
      gap: .65rem;
      border-radius: 999px;
      padding: .35rem .85rem .35rem .35rem;
      text-decoration: none;
      color: var(--ink);
      font-weight: 700;
      font-size: .93rem;
    }

    .avatar-circle {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: var(--blue-pale);
      color: var(--blue-primary);
      font-weight: 800;
    }

    .hero-card {
      background: linear-gradient(135deg, var(--blue-deep), var(--blue-primary));
      color: var(--white);
      border-radius: 26px;
      padding: 1.45rem 1.65rem;
      margin-bottom: 1.1rem;
    }

    .hero-card{
    background:linear-gradient(90deg,#27468d,#4b78df);
    color:#fff;
    border-radius:20px;
    padding:35px 45px;

    display:flex;
    justify-content:space-between;
    align-items:center;

    box-shadow:0 15px 35px rgba(0,0,0,.15);
}

/* Left Side */

.hero-left h1{
    margin:0;
    font-size:24px;      /* Smaller */
    font-weight:700;
}

.hero-left p{
    margin-top:8px;
    font-size:14px;      /* Smaller */
    color:rgba(255,255,255,.85);
}

/* Right Side */

.hero-stats{
    display:flex;
    gap:45px;
}

.hero-stat{
    text-align:center;
}

.hero-stat h2{
    margin:0;
    font-size:24px;      /* Smaller numbers */
    font-weight:700;
}

.hero-stat span{
    display:block;
    margin-top:4px;
    font-size:13px;      /* Smaller labels */
    color:rgba(255,255,255,.85);
}

/* Card Padding */

 .hero-card{
    padding:28px 40px;
}
    .info-grid {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: .9rem;
      margin-bottom: 1.15rem;
    }

    .info-card {
      border-radius: 20px;
      padding: .95rem;
      display: flex;
      align-items: center;
      gap: .85rem;
    }

    .icon-soft {
      width: 48px;
      height: 48px;
      border-radius: 15px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: var(--blue-pale);
      color: var(--blue-primary);
      font-size: 1.1rem;
      flex-shrink: 0;
    }

    .info-card h6 {
      font-size: .98rem;
      margin-bottom: .2rem;
      color: var(--ink);
    }

    .info-card p {
      margin: 0;
      font-size: .85rem;
      color: var(--soft-gray);
    }

    .pill-row {
      display: flex;
      flex-wrap: wrap;
      gap: .65rem;
      margin-bottom: 1rem;
    }

    .pill-filter {
      border: 1px solid var(--border);
      border-radius: 999px;
      background: rgba(255,255,255,.9);
      color: var(--soft-gray);
      text-decoration: none;
      font-size: .88rem;
      font-weight: 600;
      padding: .58rem .9rem;
    }

    .pill-filter.active {
      background: var(--blue-primary);
      border-color: var(--blue-primary);
      color: var(--white);
    }

    .section-head {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1rem;
      margin: 1.45rem 0 .85rem;
      padding-bottom: .5rem;
      border-bottom: 1px solid var(--border);
    }

    .section-head h2 {
      font-size: 1.5rem;
      margin: 0;
    }

    .section-tag {
      display: inline-flex;
      align-items: center;
      border-radius: 999px;
      padding: .34rem .72rem;
      font-size: .72rem;
      font-weight: 800;
      margin-left: .55rem;
    }

    .tag-notes { background: #eaf8f1; color: var(--green); }
    .tag-videos { background: #efedff; color: var(--indigo); }

    .card-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 1rem;

    }
  
    .content-card {
      overflow: hidden;
      border-radius: 22px;
    }

    .thumb-box {
      height: 190px;
      position: relative;
      overflow: hidden;
      background: linear-gradient(135deg, var(--blue-bg), var(--blue-pale));
    }

    .thumb-box img,
    .thumb-box iframe {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border: 0;
    }

    .badge-top,
    .badge-bottom {
      position: absolute;
      border-radius: 999px;
      padding: .32rem .64rem;
      font-size: .7rem;
      font-weight: 700;
    }

    .badge-top {
      top: 12px;
      right: 12px;
      background: rgba(255,255,255,.92);
      color: var(--blue-primary);
    }

    .badge-bottom {
      left: 12px;
      bottom: 12px;
      background: var(--blue-pale);
      color: var(--blue-deep);
    }

    .card-body-custom {
      padding: .9rem 1rem 1rem;
    }

    .card-body-custom h5 {
      font-size: 1.04rem;
      line-height: 1.35;
      margin-bottom: .35rem;
      color: var(--ink);
    }

    .card-body-custom p {
      font-size: .85rem;
      color: var(--soft-gray);
      margin-bottom: .35rem;
    }

    .meta-row {
      display: flex;
      justify-content: space-between;
      gap: .75rem;
      font-size: .81rem;
      color: var(--soft-gray);
      margin: .68rem 0;
    }

    .rating { color: var(--gold); font-weight: 700; }

    /* .btn-main {
      width: 100%;
      border-radius: 13px;
      padding: .72rem .9rem;
      font-size: .91rem;
      font-weight: 700;
      background: var(--blue-primary);
      color: var(--white);
      border: 0;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: .42rem;
    }   */

    .btn-main.video { background: var(--indigo); }
//card notes 
    /* :root{
  --blue-700:#1457b3;
  --blue-600:#1f6fe0;
  --blue-100:#eaf2fe;
  --blue-50:#f5f9ff;
  --ink:#1c2530;
  --ink-soft:#6b7688;
  --line:#e3ebf7;
  --green:#1f9d63;
  --green-bg:#e6f7ee;
  --amber:#e08a00;
  --amber-bg:#fff3e0;
  --red:#c94b3f;
  --red-bg:#fdeceb;
  
} */

/* Section header */
.section-head{
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-bottom:18px;
}
.section-head h2{
  font-size:22px;
  font-weight:700;
  margin:0;
  display:flex;
  align-items:center;
  gap:12px;
}
.section-tag{
  font-size:11px;
  font-weight:700;
  letter-spacing:.3px;
  padding:4px 12px;
  border-radius:20px;
}
.section-tag.tag-notes{
  background:var(--green-bg);
  color:var(--green);
}
.section-head a{
  font-size:13.5px;
  font-weight:600;
  color:var(--blue-700);
}

/* Grid */
.card-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(280px,1fr));
  gap:20px;
}

/* Card */
.content-card{
  background:#fff;
  border:1px solid var(--line);
  border-radius:16px;
  overflow:hidden;
  box-shadow:0 2px 6px rgba(20,87,179,0.06), 0 10px 28px rgba(20,87,179,0.06);
  display:flex;
  flex-direction:column;
}

/* Thumbnail */
.thumb-box{
  height:150px;
  background:linear-gradient(135deg,var(--blue-100),var(--blue-50));
  display:flex;
  align-items:center;
  justify-content:center;
  overflow:hidden;
}
.thumb-box img{
  width:100%;
  height:100%;
  object-fit:contain;
}


/* Body */
.card-body-custom{
  padding:18px 20px 20px;
  display:flex;
  flex-direction:column;
  gap:10px;
}

/* Tag row (subject + level) */
.card-tags{
  display:flex;
  justify-content:space-between;
  align-items:center;
}
.badge-subject{
  font-size:11.5px;
  font-weight:600;
  color:var(--blue-700);
  background:var(--blue-100);
  padding:4px 10px;
  border-radius:6px;
}
.badge-level{
  font-size:10.5px;
  font-weight:900;
  padding:4px 10px;
  border-radius:40px;
}
.level-beginner{ background:rgba(230,247,238,0.95); color:var(--green); }
.level-intermediate{ background:rgba(255,243,224,0.95);  color:var(--amber); }
.level-advanced{ background:rgba(253,236,235,0.95); color:var(--red); }
.badge-free{ background:rgba(230,247,238,0.95); color:var(--green); }
.badge-premium{ background:rgba(234,242,254,0.95); color:var(--blue-700); }
.badge-subject{ background:rgba(234,242,254,0.95); color:var(--green);}

/* Title + description */
.card-body-custom h5{
  font-size:16.5px;
  font-weight:700;
  margin:0;
  color:var(--ink);
  line-height:1.35;
}
.card-body-custom p{
  font-size:13px;
  color:var(--ink-soft);
  margin:0;
  line-height:1.5;
}

/* Meta row (rating + downloads) */
.meta-row{
  display:flex;
  justify-content:space-between;
  align-items:center;
  font-size:13px;
  color:var(--ink-soft);
}
.rating{
  color:var(--ink);
  font-weight:600;
}
.rating i{ color:#f5b400; }
.rating-count{
  font-weight:400;
  color:var(--ink-soft);
}
.downloads{
  display:flex;
  align-items:center;
  gap:4px;
  font-size:12.5px;
}

/* Button */
.btn-main{
  margin-top:6px;
  background: var(--indigo);
  color:#fff !important;
  text-align:center;
  padding:12px 14px;
  border-radius:10px;
  font-size:14px;
  font-weight:600;
  text-decoration:none;
  display:flex;
  align-items:center;
  justify-content:center;
  gap:8px;
  transition:.15s;
}
.btn-main:hover{
  background:var(--blue-deep);
  color:#fff;
}

    @media (max-width: 1399px) {
      body { font-size: 13.5px; }
    }

    @media (max-width: 1199px) {
      .info-grid,
      .card-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }

    @media (max-width: 991px) {
      .dashboard-layout { display: block !important; }
      .sidebar {
        position: static;
        width: 100%;
        min-width: 0;
        height: auto;
      }
      .topbar { flex-direction: column; align-items: stretch; }
      .search-shell { width: 100%; }
      .notify-panel { right: auto; left: 0; width: 100%; }
    }

    @media (max-width: 767px) {
      .hero-stats,
      .info-grid,
      .card-grid {
        grid-template-columns: 1fr;
      }
      .main-area { padding: 1rem; }
    }
    .hero{
    background:linear-gradient(120deg,var(--blue-900),var(--blue-600));
    border-radius:20px;padding:32px 36px;color:#fff;display:flex;justify-content:space-between;
    align-items:center;margin-bottom:32px;overflow:hidden;position:relative;
  }

    body.dark-mode {
      background: linear-gradient(180deg, #0f1724 0%, #121d2d 100%);
      color: #e6edf8;
    }

    body.dark-mode .sidebar,
    body.dark-mode .search-shell,
    body.dark-mode .notify-btn,
    body.dark-mode .profile-pill,
    body.dark-mode .info-card,
    body.dark-mode .content-card,
    body.dark-mode .notify-panel {
      background: rgba(18,30,47,.96);
      border-color: #24344d;
      box-shadow: 0 18px 34px rgba(0,0,0,.28);
    }

    body.dark-mode .brand,
    body.dark-mode .profile-pill,
    body.dark-mode .notify-btn,
    body.dark-mode .notify-head h3,
    body.dark-mode .notify-item strong,
    body.dark-mode .section-head h2,
    body.dark-mode .content-card h5,
    body.dark-mode .info-card h6 {
      color: #e6edf8;
    }

    body.dark-mode .side-link,
    body.dark-mode .search-shell input,
    body.dark-mode .content-card p,
    body.dark-mode .meta-row,
    body.dark-mode .info-card p,
    body.dark-mode .notify-item p,
    body.dark-mode .notify-empty {
      color: #a7b8d0;
    }

    body.dark-mode .avatar-circle,
    body.dark-mode .notify-badge {
      background: #1d3353;
      color: #87b5ff;
    }

    body.dark-mode .info-card,
    body.dark-mode .content-card,
    body.dark-mode .notify-item,
    body.dark-mode .notify-empty,
    body.dark-mode .section-head {
      border-color: #24344d;
    }

    body.dark-mode .notify-item,
    body.dark-mode .notify-empty {
      background: #162336;
    }

    body.dark-mode .section-head a {
      color: #87b5ff !important;
    }

    .notify-btn.is-disabled {
      opacity: .55;
      cursor: default;
    }

    @media (max-width: 1199px) {
      .main-area { padding: 1rem 1rem 1.4rem; }
      .search-shell { width: min(100%, 320px); }
    }

    @media (max-width: 991px) {
      .dashboard-layout { display: block !important; }
      .sidebar {
        position: static;
        width: 100%;
        min-width: 0;
        height: auto;
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: wrap;
        gap: .65rem;
        align-items: stretch;
        padding: 1rem;
        border-right: 0;
        border-bottom: 1px solid var(--border);
      }
      .brand {
        width: 100%;
        justify-content: center;
        margin-bottom: .2rem;
      }
      .side-link {
        flex: 1 1 calc(50% - .65rem);
        justify-content: center;
        text-align: center;
        margin-bottom: 0;
        min-height: 48px;
      }
      .main-area { padding: 1rem; }
      .topbar { gap: .85rem; }
      .search-shell { width: 100%; }
    }

    @media (max-width: 767px) {
      .sidebar {
        gap: .55rem;
        padding: .9rem;
      }
      .brand {
        justify-content: flex-start;
      }
      .side-link {
        flex-basis: 100%;
        justify-content: flex-start;
        text-align: left;
      }
      .top-actions {
        width: 100%;
        justify-content: space-between;
      }
      .profile-pill {
        flex: 1;
        justify-content: center;
      }
      .hero-card {
        padding: 1.1rem 1rem;
        border-radius: 22px;
      }
      .hero-left h1 {
        font-size: 1.45rem;
      }
      .section-head {
        flex-direction: column;
        align-items: flex-start;
        gap: .45rem;
      }
    }

  </style>
</head>
<body>
  <div class="dashboard-layout d-lg-flex">
    <aside class="sidebar d-flex flex-column">
      <div class="brand">
        <span class="brand-icon"><i class="bi bi-journal-text"></i></span>
        <span>StudyHub</span>
      </div>

      <a href="dashboard.php" class="side-link active"><i class="bi bi-grid"></i> Dashboard</a>
      <a href="notes.php?type=free" class="side-link"><i class="bi bi-file-earmark-text"></i> My Notes</a>
      <a href="video_lessons.php" class="side-link"><i class="bi bi-camera-video"></i> Video Lessons</a>
      <a href="profile.php" class="side-link"><i class="bi bi-person"></i> My Profile</a>
      <a href="premium.php" class="side-link"><i class="bi bi-gem"></i> Premium</a>
      <a href="settings.php" class="side-link"><i class="bi bi-gear"></i> Settings</a>
      <a href="logout.php" class="side-link"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </aside>

    <main class="main-area">
      <div class="topbar">
        <div class="search-shell">
          <i class="bi bi-search"></i>
          <input type="text" placeholder="Search notes, subjects or videos...">
        </div>
        <div class="top-actions">
          <div class="notify-wrap" id="notifyWrap">
            <button type="button" class="notify-btn" id="notifyToggle" aria-label="Open notifications" aria-expanded="false">
              <i class="bi bi-bell"></i>
              <?php if (!empty($notifications)): ?><span class="notify-dot"></span><?php endif; ?>
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
          <a href="profile.php" class="profile-pill">
            <span class="avatar-circle"><?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?></span>
            <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>
          </a>
        </div>
      </div>

      <!-- <section class="hero-card">
        <h1 class="fw-bold">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
        <p>Track notes, videos, and premium access in one place.</p> -->
        <!-- <div class="hero-stats">
          <div class="hero-stat"><span><?php echo $stats['notes']; ?></span><span>Notes</span></div>
          <div class="hero-stat"><span><?php echo $stats['videos']; ?></span><span>Videos</span></div>
          <div class="hero-stat"><span><?php echo $stats['free']; ?></span><span>Free</span></div>
        </div> -->
        <section class="hero-card">

    <div class="hero-left">
        <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?> 👋</h1>
        <p>Track notes, videos, and premium access in one place.</p>
    </div>

    <div class="hero-stats">

        <div class="hero-stat">
            <h2><?php echo $stats['notes']; ?></h2>
            <span>Notes</span>
        </div>

        <div class="hero-stat">
            <h2><?php echo $stats['videos']; ?></h2>
            <span>Videos</span>
        </div>

        <div class="hero-stat">
            <h2><?php echo $stats['free']; ?></h2>
            <span>Free</span>
        </div>

    </div>

</section>
      

      <section class="info-grid">
        <div class="info-card">
          <span class="icon-soft"><i class="bi bi-file-earmark-text"></i></span>
          <div>
            <h6 class="fw-bold">Free & Premium Notes</h6>
            <p>Subject-wise PDFs</p>
          </div>
        </div>
        <div class="info-card">
          <span class="icon-soft"><i class="bi bi-play-btn"></i></span>
          <div>
            <h6 class="fw-bold">Embedded Video Lessons</h6>
            <p>YouTube-linked lectures</p>
          </div>
        </div>
        <div class="info-card">
          <span class="icon-soft"><i class="bi bi-bell"></i></span>
          <div>
            <h6 class="fw-bold">Smart Notifications</h6>
            <p>New uploads quickly</p>
          </div>
        </div>
        <div class="info-card">
          <span class="icon-soft"><i class="bi bi-stars"></i></span>
          <div>
            <h6 class="fw-bold">Ratings & Feedback</h6>
            <p>Admin-set ratings</p>
          </div>
        </div>
      </section>

      <!-- <div class="pill-row">
        <a class="pill-filter active" href="dashboard.php">All Subjects</a>
        <a class="pill-filter" href="notes.php?type=free&search=DSA">DSA</a>
        <a class="pill-filter" href="notes.php?type=free&search=DBMS">DBMS</a>
        <a class="pill-filter" href="notes.php?type=free&search=Web">Web Development</a>
        <a class="pill-filter" href="notes.php?type=free&search=Operating">Operating Systems</a>
        <span class="pill-filter">Beginner</span>
        <span class="pill-filter">Intermediate</span>
        <span class="pill-filter">Advanced</span>
      </div> -->

      <section>
        <div class="section-head">
          <h2>Top 5 Notes <span class="section-tag tag-notes">OPEN ACCESS</span></h2>
          <a href="notes.php?type=free" class="text-decoration-none">View all</a>
        </div>
        <div class="card-grid">
          <?php if (!empty($noteCards)): ?>
            <?php foreach ($noteCards as $note): ?>
             <article class="content-card">
  <div class="thumb-box">
    <img src="<?php echo htmlspecialchars(noteThumbnail($note)); ?>" alt="Note thumbnail">
  </div>
  <div class="card-body-custom">
    <div class="card-tags">
      <span class="badge-subject"><?php echo htmlspecialchars($note['subject']); ?></span>
      <span class="badge-level level-<?php echo strtolower($note['difficulty_level'] ?? 'beginner'); ?>"><?php echo ucfirst($note['difficulty_level'] ?? 'beginner'); ?></span>
    </div>
    <h5 class="fw-bold"><?php echo htmlspecialchars($note['topic']); ?></h5>
    <p><?php echo ucfirst($note['material_format'] ?? 'pdf'); ?> · <?php echo $note['note_type'] === 'premium' ? 'Premium' : 'Free'; ?></p>
    <div class="meta-row">
      <span class="rating"><i class="bi bi-star-fill me-1"></i><?php echo number_format((float) ($note['rating'] ?? 4), 1); ?> <span class="rating-count">(<?php echo (int)($note['rating_count'] ?? 0); ?>)</span></span>
      <span><?php echo date("d M Y", strtotime($note['upload_date'])); ?></span>
    </div>
    <a href="../uploads/<?php echo htmlspecialchars($note['filename']); ?>" target="_blank" class="btn-main"><i class="bi bi-download"></i> Download PDF</a>
  </div>
</article>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </section>

      <section>
        <div class="section-head">
          <h2>Top 5 Videos <span class="section-tag tag-videos">VIDEO LESSONS</span></h2>
          <a href="video_lessons.php" class="text-decoration-none">View all</a>
        </div>
        <div class="card-grid">
          <?php if (!empty($videoCards)): ?>
            <?php foreach ($videoCards as $video): ?>
              <article class="content-card">
                <div class="thumb-box">
                  <?php if (!empty($video['youtube_link']) && preg_match('/(?:v=|youtu\.be\/|embed\/)([A-Za-z0-9_-]{11})/', $video['youtube_link'], $matches)): ?>
                    <iframe src="https://www.youtube.com/embed/<?php echo htmlspecialchars($matches[1]); ?>" title="Video lesson" allowfullscreen></iframe>
                  <?php else: ?>
                    <img src="<?php echo htmlspecialchars(noteThumbnail($video)); ?>" alt="Video thumbnail">
                  <?php endif; ?>
                  <span class="badge-top"><?php echo ucfirst($video['difficulty_level'] ?? 'beginner'); ?></span>
                  <span class="badge-bottom"><?php echo htmlspecialchars($video['subject']); ?></span>
                </div>
                <div class="card-body-custom">
                  <h5 class="fw-bold"><?php echo htmlspecialchars($video['topic']); ?></h5>
                  <p><?php echo ucfirst($video['material_format'] ?? 'pdf'); ?> support</p>
                  <div class="meta-row">
                    <span class="rating"><i class="bi bi-star-fill me-1"></i><?php echo number_format((float) ($video['rating'] ?? 4), 1); ?></span>
                    <span><?php echo date("d M Y", strtotime($video['upload_date'])); ?></span>
                  </div>
                  <a href="<?php echo htmlspecialchars($video['youtube_link'] ?? '#'); ?>" target="_blank" class="btn-main video"><i class="bi bi-play-btn"></i> Watch Video</a>
                </div>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </section>
    </main>
  </div>
  <script>
    const darkModeKey = 'studyhubDarkMode';
    const notificationsKey = 'studyhubNotificationsEnabled';
    const notifyWrap = document.getElementById('notifyWrap');
    const notifyToggle = document.getElementById('notifyToggle');
    const notifyDot = document.getElementById('notifyDot');

    if (localStorage.getItem(darkModeKey) === '1') {
      document.body.classList.add('dark-mode');
    }

    function applyNotificationsState(enabled) {
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

    applyNotificationsState(localStorage.getItem(notificationsKey) !== '0');

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
