<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $course_id = $_GET['id'];
    // Delete enrollments first
    $stmt = $pdo->prepare("DELETE FROM enrollments WHERE course_id = ?");
    $stmt->execute([$course_id]);
    // Delete course
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$course_id, $_SESSION['user_id']]);
    $_SESSION['message'] = "Course deleted successfully!";
} else {
    $_SESSION['error'] = "Invalid course ID.";
}

header("Location: teacher_dashboard.php");
exit;
?>