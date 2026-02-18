<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $teacher_id = $_SESSION['user_id'];

    // Insert course
    $stmt = $pdo->prepare("INSERT INTO courses (teacher_id, title, description) VALUES (?, ?, ?)");
    $stmt->execute([$teacher_id, $title, $description]);
    $_SESSION['message'] = "Course created successfully!";
    header("Location: teacher_dashboard.php");
    exit;
}
?>