<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    error_log("enroll.php: Redirecting to login.php - user_id: " . ($_SESSION['user_id'] ?? 'unset') . ", role: " . ($_SESSION['role'] ?? 'unset'));
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No course selected.";
    error_log("enroll.php: No course ID provided");
    header("Location: courses.php");
    exit;
}

$course_id = $_GET['id'];

// Check if course exists
$stmt = $pdo->prepare("SELECT id FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    $_SESSION['error'] = "Course not found.";
    error_log("enroll.php: Course ID $course_id not found");
    header("Location: courses.php");
    exit;
}

// Check if already enrolled
$stmt = $pdo->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
$stmt->execute([$_SESSION['user_id'], $course_id]);
if ($stmt->fetch()) {
    $_SESSION['error'] = "You are already enrolled in this course.";
    error_log("enroll.php: User {$_SESSION['user_id']} already enrolled in course $course_id");
    header("Location: courses.php");
    exit;
}

// Enroll user
try {
    $stmt = $pdo->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $course_id]);
    $_SESSION['message'] = "Successfully enrolled in the course.";
    error_log("enroll.php: User {$_SESSION['user_id']} enrolled in course $course_id");
} catch (Exception $e) {
    $_SESSION['error'] = "Enrollment failed. Please try again.";
    error_log("enroll.php: Enrollment failed for user {$_SESSION['user_id']}, course $course_id - " . $e->getMessage());
}
header("Location: dashboard.php");
exit;
?>