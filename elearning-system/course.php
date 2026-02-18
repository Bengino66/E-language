<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    error_log("course.php: Redirecting to login.php - user_id: " . ($_SESSION['user_id'] ?? 'unset') . ", role: " . ($_SESSION['role'] ?? 'unset'));
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No course selected.";
    error_log("course.php: No course ID provided");
    header("Location: dashboard.php");
    exit;
}

$course_id = $_GET['id'];

// Check if course exists
$stmt = $pdo->prepare("SELECT c.*, t.username as teacher_name FROM courses c JOIN teachers t ON c.teacher_id = t.id WHERE c.id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    $_SESSION['error'] = "Course not found.";
    error_log("course.php: Course ID $course_id not found");
    header("Location: dashboard.php");
    exit;
}

// Check if user is enrolled
$stmt = $pdo->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
$stmt->execute([$_SESSION['user_id'], $course_id]);
if (!$stmt->fetch()) {
    $_SESSION['error'] = "You are not enrolled in this course.";
    error_log("course.php: User {$_SESSION['user_id']} not enrolled in course $course_id");
    header("Location: courses.php");
    exit;
}

// Fetch resources
try {
    $stmt = $pdo->prepare("SELECT * FROM resources WHERE course_id = ?");
    $stmt->execute([$course_id]);
    $resources = $stmt->fetchAll();
    error_log("course.php: Fetched " . count($resources) . " resources for course $course_id");
} catch (Exception $e) {
    error_log("course.php: Resource query error - " . $e->getMessage());
    $_SESSION['error'] = "Error fetching resources.";
    $resources = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course - <?= htmlspecialchars($course['title']) ?></title>
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Local Styles -->
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* Fallback Styles */
        .video-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
        }
        .video-container video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        a.btn {
            display: inline-block;
            background-color: #8B4513;
            color: #FFFFFF !important;
            padding: 0.5rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s, transform 0.2s;
        }
        a.btn:hover {
            background-color: #FCD34D;
            color: #8B4513 !important;
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-amber-900 text-white fixed w-full top-0 shadow-lg z-50">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="text-2xl font-bold">eLearning</div>
            <div class="hidden md:flex space-x-4 items-center">
                <a href="index.php" class="nav-link hover:text-yellow-300">Home</a>
                <a href="dashboard.php" class="nav-link hover:text-yellow-300">Dashboard</a>
                <a href="logout.php" class="nav-link hover:text-yellow-300">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-24 pb-12 min-h-screen">
        <div class="container mx-auto px-4">
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($course['title']) ?></h1>
                <p class="text-gray-600 mb-1">Teacher: <?= htmlspecialchars($course['teacher_name']) ?></p>
                <p class="text-gray-700 mt-4"><?= htmlspecialchars($course['description']) ?></p>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                    <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <h2 class="text-2xl font-bold text-gray-800 mb-6">Course Resources</h2>
            
            <?php if (empty($resources)): ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <p class="text-yellow-700">No resources available yet.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($resources as $resource): 
                        $type = $resource['type'] ?? 'file';
                        $web_path = 'Uploads/' . basename($resource['file_path']);
                        $absolute_path = $_SERVER['DOCUMENT_ROOT'] . '/elearning-system/' . $web_path;
                        // Validate file existence
                        if ($type !== 'url' && !file_exists($absolute_path)) {
                            error_log("course.php: Resource file not found - ID: {$resource['id']}, Path: $absolute_path");
                            continue;
                        }
                    ?>
                        <div class="card">
                            <?php if ($type === 'video'): ?>
                                <div class="video-container bg-black">
                                    <video controls>
                                        <source src="<?= htmlspecialchars($web_path) ?>" type="video/mp4">
                                        Your browser doesn't support videos.
                                    </video>
                                </div>
                            <?php elseif ($type === 'image'): ?>
                                <img src="<?= htmlspecialchars($web_path) ?>" alt="<?= htmlspecialchars($resource['title']) ?>" class="w-full">
                            <?php else: ?>
                                <div class="p-6 flex justify-center">
                                    <img src="/elearning-system/images/<?= htmlspecialchars($type) ?>-icon.png" alt="<?= htmlspecialchars($type) ?> icon" class="icon-img">
                                </div>
                            <?php endif; ?>
                            
                            <div class="resource-info">
                                <h3 class="mb-2"><?= htmlspecialchars($resource['title']) ?></h3>
                                <div class="flex justify-between items-center">
                                    <span class="type"><?= ucfirst($type) ?></span>
                                    <a href="<?= htmlspecialchars($web_path) ?>" 
                                       class="btn" 
                                       download>
                                       Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-amber-900 text-white py-8">
        <div class="container mx-auto px-4 text-center">
            <p>© <?= date('Y') ?> eLearning Platform. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>