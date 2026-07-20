<?php
session_start();
include '../includes/config.php';
include '../includes/auth_admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit();
}

$studentId = (int) ($_POST['student_id'] ?? 0);
$targetStatus = $_POST['target_status'] ?? '';
$isBlocked = $targetStatus === 'blocked' ? 1 : 0;

if ($studentId <= 0 || !in_array($targetStatus, ['active', 'blocked'], true)) {
    header('Location: dashboard.php');
    exit();
}

$stmt = $conn->prepare("UPDATE users SET is_blocked = ? WHERE id = ? AND role = 'student'");
$stmt->bind_param('ii', $isBlocked, $studentId);
$stmt->execute();

header('Location: dashboard.php');
exit();
?>
