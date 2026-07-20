<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "notemarket";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Keep the local database aligned with the app fields used by the UI.
$youtubeColumn = $conn->query("SHOW COLUMNS FROM notes_final LIKE 'youtube_link'");
if ($youtubeColumn && $youtubeColumn->num_rows === 0) {
    $conn->query("ALTER TABLE notes_final ADD COLUMN youtube_link VARCHAR(255) DEFAULT NULL AFTER image_path");
}

$difficultyColumn = $conn->query("SHOW COLUMNS FROM notes_final LIKE 'difficulty_level'");
if ($difficultyColumn && $difficultyColumn->num_rows === 0) {
    $conn->query("ALTER TABLE notes_final ADD COLUMN difficulty_level ENUM('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner' AFTER youtube_link");
}

$resourceTypeColumn = $conn->query("SHOW COLUMNS FROM notes_final LIKE 'resource_type'");
if ($resourceTypeColumn && $resourceTypeColumn->num_rows === 0) {
    $conn->query("ALTER TABLE notes_final ADD COLUMN resource_type ENUM('note','video') NOT NULL DEFAULT 'note' AFTER role");
}

$materialFormatColumn = $conn->query("SHOW COLUMNS FROM notes_final LIKE 'material_format'");
if ($materialFormatColumn && $materialFormatColumn->num_rows === 0) {
    $conn->query("ALTER TABLE notes_final ADD COLUMN material_format ENUM('pdf','handwritten') NOT NULL DEFAULT 'pdf' AFTER note_type");
}

$ratingColumn = $conn->query("SHOW COLUMNS FROM notes_final LIKE 'rating'");
if ($ratingColumn && $ratingColumn->num_rows === 0) {
    $conn->query("ALTER TABLE notes_final ADD COLUMN rating DECIMAL(3,1) NOT NULL DEFAULT 4.0 AFTER price");
}

$blockedColumn = $conn->query("SHOW COLUMNS FROM users LIKE 'is_blocked'");
if ($blockedColumn && $blockedColumn->num_rows === 0) {
    $conn->query("ALTER TABLE users ADD COLUMN is_blocked TINYINT(1) NOT NULL DEFAULT 0 AFTER role");
}
?>
