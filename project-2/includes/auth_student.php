<?php
session_start();
if (!isset($conn)) {
    include __DIR__ . '/config.php';
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$studentId = (int) $_SESSION['user_id'];
$statusStmt = $conn->prepare("SELECT is_blocked FROM users WHERE id = ? AND role = 'student'");
if (!$statusStmt) {
    session_unset();
    session_destroy();
    header("Location: ../login.php");
    exit();
}

$statusStmt->bind_param("i", $studentId);
$statusStmt->execute();
$statusResult = $statusStmt->get_result();
$student = $statusResult ? $statusResult->fetch_assoc() : null;

if (!$student || !empty($student['is_blocked'])) {
    session_unset();
    session_destroy();
    header("Location: ../login.php?blocked=1");
    exit();
}
?>
