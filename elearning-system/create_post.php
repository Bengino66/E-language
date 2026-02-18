<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $course_id = !empty($_POST['course_id']) ? $_POST['course_id'] : null;

    // Insert post
    $stmt = $pdo->prepare("INSERT INTO posts (teacher_id, course_id, title, content) VALUES (?, ?, ?, ?)");
    $stmt->execute([$teacher_id, $course_id, $title, $content]);
    $post_id = $pdo->lastInsertId();

    // Handle file uploads
    if (!empty($_FILES['files']['name'][0])) {
        $upload_dir = 'uploads/';
        foreach ($_FILES['files']['name'] as $key => $name) {
            if ($_FILES['files']['error'][$key] == 0) {
                $tmp_name = $_FILES['files']['tmp_name'][$key];
                $file_path = $upload_dir . basename($name);
                $file_type = $_FILES['files']['type'][$key];
                move_uploaded_file($tmp_name, $file_path);
                $fileStmt = $pdo->prepare("INSERT INTO files (post_id, file_path, file_type) VALUES (?, ?, ?)");
                $fileStmt->execute([$post_id, $file_path, $file_type]);
            }
        }
    }

    $_SESSION['message'] = "Post created successfully!";
    header("Location: teacher_dashboard.php");
    exit;
}

// Fetch teacher's courses
$courseStmt = $pdo->prepare("SELECT * FROM courses WHERE teacher_id = ?");
$courseStmt->execute([$teacher_id]);
$courses = $courseStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/script.js"></script>
</head>
<body class="bg-white">
    <!-- Navigation Bar -->
    <nav class="bg-brown-800 text-white fixed w-full top-0 z-50 shadow-lg">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="text-2xl font-bold">eLearning</div>
            <div class="hidden md:flex space-x-6 items-center">
                <a href="index.html" class="hover:bg-light-brown-200 px-3 py-2 rounded transition">Home</a>
                <a href="teacher_dashboard.php" class="hover:bg-light-brown-200 px-3 py-2 rounded transition">Dashboard</a>
                <a href="create_course.php" class="hover:bg-light-brown-200 px-3 py-2 rounded transition">Create Course</a>
                <a href="create_post.php" class="hover:bg-light-brown-200 px-3 py-2 rounded transition">Create Post</a>
                <a href="logout.php" class="hover:bg-light-brown-200 px-3 py-2 rounded transition">Logout</a>
            </div>
            <button id="menu-toggle" class="md:hidden focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
        <div id="mobile-menu" class="hidden md:hidden bg-brown-800">
            <a href="index.html" class="block px-4 py-2 hover:bg-light-brown-200">Home</a>
            <a href="teacher_dashboard.php" class="block px-4 py-2 hover:bg-light-brown-200">Dashboard</a>
            <a href="create_course.php" class="block px-4 py-2 hover:bg-light-brown-200">Create Course</a>
            <a href="create_post.php" class="block px-4 py-2 hover:bg-light-brown-200">Create Post</a>
            <a href="logout.php" class="block px-4 py-2 hover:bg-light-brown-200">Logout</a>
        </div>
    </nav>

    <div class="container mx-auto px-4 pt-24 pb-12">
        <h1 class="text-3xl font-bold mb-8">Create Post</h1>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" class="max-w-lg mx-auto">
            <input type="text" name="title" placeholder="Post Title" class="w-full px-4 py-2 mb-4 border border-brown-800 rounded" required>
            <textarea name="content" placeholder="Post Content" class="w-full px-4 py-2 mb-4 border border-brown-800 rounded" rows="5" required></textarea>
            <select name="course_id" class="w-full px-4 py-2 mb-4 border border-brown-800 rounded">
                <option value="">Select a Course (Optional)</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['title']); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="file" name="files[]" multiple class="w-full mb-4">
            <button type="submit" class="w-full bg-brown-800 text-white py-2 rounded hover:bg-light-brown-200 transition">Create Post</button>
        </form>
    </div>
</body>
</html>