<?php
session_start();
include '../includes/config.php';
include '../includes/auth_admin.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $note_id = (int) ($_POST['note_id'] ?? 0);

    $fetchStmt = $conn->prepare("SELECT filename, image_path FROM notes_final WHERE id = ?");
    $fetchStmt->bind_param("i", $note_id);
    $fetchStmt->execute();
    $noteResult = $fetchStmt->get_result();
    $note = $noteResult ? $noteResult->fetch_assoc() : null;

    // Delete from database
    $stmt = $conn->prepare("DELETE FROM notes_final WHERE id = ?");
    $stmt->bind_param("i", $note_id);
    
    if ($stmt->execute()) {
        // Delete file from uploads folder
        $file_path = "../uploads/" . ($note['filename'] ?? '');
        if (!empty($note['filename']) && file_exists($file_path)) {
            unlink($file_path);
        }
        $image_path = "../image_path/" . ($note['image_path'] ?? '');
        if (!empty($note['image_path']) && file_exists($image_path)) {
            unlink($image_path);
        }
        header("Location: dashboard.php?msg=deleted");
        exit();
    } else {
        echo "❌ Failed to delete note.";
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>
