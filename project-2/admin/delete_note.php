<?php
session_start();
include '../includes/config.php';
include '../includes/auth_admin.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $note_id = $_POST['note_id'];
    $filename = $_POST['filename'];

    // Delete from database
    $stmt = $conn->prepare("DELETE FROM notes_final WHERE id = ?");
    $stmt->bind_param("i", $note_id);
    
    if ($stmt->execute()) {
        // Delete file from uploads folder
        $file_path = "../uploads/" . $filename;
        if (file_exists($file_path)) {
            unlink($file_path);
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
