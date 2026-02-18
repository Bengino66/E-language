<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $enrollment_id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM enrollments WHERE id = ? AND user_id = ?");
    $stmt->execute([$enrollment_id, $_SESSION['user_id']]);
    $_SESSION['message'] = "Unenrolled successfully!";
} else {
    $_SESSION['error'] = "Invalid enrollment ID.";
}

header("Location: dashboard.php");
exit;
?>