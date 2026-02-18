<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    error_log("post_resource.php: Redirecting to login.php - user_id: " . ($_SESSION['user_id'] ?? 'unset') . ", role: " . ($_SESSION['role'] ?? 'unset'));
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = $_POST['course_id'] ?? '';
    $title = $_POST['title'] ?? '';
    $type = $_POST['type'] ?? 'file';
    $file = $_FILES['file'] ?? null;

    // Validate inputs
    if (empty($course_id) || empty($title) || (!$file && $type != 'url')) {
        $_SESSION['error'] = "All fields are required.";
        error_log("post_resource.php: Missing required fields - course_id: $course_id, title: $title, file: " . ($file ? 'provided' : 'missing'));
        header("Location: teacher_dashboard.php");
        exit;
    }

    // Validate course exists and belongs to teacher
    $stmt = $pdo->prepare("SELECT id FROM courses WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$course_id, $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        $_SESSION['error'] = "Invalid course or unauthorized.";
        error_log("post_resource.php: Invalid course ID $course_id or unauthorized for teacher {$_SESSION['user_id']}");
        header("Location: teacher_dashboard.php");
        exit;
    }

    // Validate file type
    $allowed_types = [
        'pdf' => ['application/pdf'],
        'image' => ['image/jpeg', 'image/png'],
        'video' => ['video/mp4', 'video/mpeg', 'video/webm'], // Added webm for broader video support
        'file' => [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel', // Added for XLS
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' // Added for XLSX
        ]
    ];
    $file_path = '';

    if ($type != 'url') {
        if (!$file || $file['error'] != UPLOAD_ERR_OK) {
            $_SESSION['error'] = "File upload error: " . ($file['error'] ?? 'no file');
            error_log("post_resource.php: File upload error - code: " . ($file['error'] ?? 'no file'));
            header("Location: teacher_dashboard.php");
            exit;
        }

        $file_mime = mime_content_type($file['tmp_name']);
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $valid_type = false;

        foreach ($allowed_types as $t => $mimes) {
            if (in_array($file_mime, $mimes) || ($t == 'file' && in_array($file_ext, ['doc', 'docx', 'xls', 'xlsx']))) {
                $type = $t;
                $valid_type = true;
                break;
            }
        }

        if (!$valid_type) {
            $_SESSION['error'] = "Unsupported file type: $file_ext (MIME: $file_mime)";
            error_log("post_resource.php: Unsupported file type - mime: $file_mime, extension: $file_ext");
            header("Location: teacher_dashboard.php");
            exit;
        }

        // Handle file upload
        $upload_dir = 'Uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true); // Changed to 0755 for better permissions
        }
        $file_name = uniqid() . '.' . $file_ext;
        $file_path = $upload_dir . $file_name;

        // Check if file already exists and log for debugging
        if (file_exists($file_path)) {
            $_SESSION['error'] = "File already exists.";
            error_log("post_resource.php: File already exists - $file_path");
            header("Location: teacher_dashboard.php");
            exit;
        }

        // Move file and verify
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            $_SESSION['error'] = "Failed to upload file.";
            error_log("post_resource.php: Failed to move uploaded file to $file_path");
            header("Location: teacher_dashboard.php");
            exit;
        }

        // Verify file was written correctly
        if (!file_exists($file_path)) {
            $_SESSION['error'] = "File was not saved correctly.";
            error_log("post_resource.php: File not found after upload - $file_path");
            header("Location: teacher_dashboard.php");
            exit;
        }
    } else {
        if (!filter_var($title, FILTER_VALIDATE_URL)) {
            $_SESSION['error'] = "Invalid URL.";
            error_log("post_resource.php: Invalid URL - $title");
            header("Location: teacher_dashboard.php");
            exit;
        }
        $file_path = $title;
    }

    // Insert resource into database
    try {
        $stmt = $pdo->prepare("INSERT INTO resources (course_id, title, type, file_path) VALUES (?, ?, ?, ?)");
        $stmt->execute([$course_id, $title, $type, $file_path]);
        $_SESSION['message'] = "Resource added successfully.";
        error_log("post_resource.php: Resource added - course_id: $course_id, title: $title, type: $type, file_path: $file_path");
    } catch (Exception $e) {
        $_SESSION['error'] = "Error adding resource: " . $e->getMessage();
        error_log("post_resource.php: Database error - " . $e->getMessage());
        header("Location: teacher_dashboard.php");
        exit;
    }

    header("Location: teacher_dashboard.php");
    exit;
}
?>