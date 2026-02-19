<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    error_log("courses.php: Redirecting to login.php - user_id: " . ($_SESSION['user_id'] ?? 'unset') . ", role: " . ($_SESSION['role'] ?? 'unset'));
    header("Location: login.php");
    exit;
}

// Fetch all courses
try {
    $stmt = $pdo->prepare("SELECT c.*, t.username as teacher_name FROM courses c JOIN teachers t ON c.teacher_id = t.id");
    $stmt->execute();
    $courses = $stmt->fetchAll();
    error_log("courses.php: Fetched " . count($courses) . " courses");
} catch (Exception $e) {
    error_log("courses.php: Query error - " . $e->getMessage());
    $courses = [];
    $_SESSION['error'] = "Error fetching courses. Please try again.";
}

// Fetch enrolled course IDs
$stmt = $pdo->prepare("SELECT course_id FROM enrollments WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$enrolled_courses = array_column($stmt->fetchAll(), 'course_id');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Courses</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" onerror="console.error('Failed to load Tailwind CDN')">
    <link rel="stylesheet" href="css/styles.css" onerror="console.error('Failed to load styles.css')">
    <script src="js/script.js" defer></script>
    <script>
        console.log('courses.php: styles.css loaded');
    </script>
</head>
<body style="background-color: #F5F5F5;">
    <!-- Navigation Bar -->
    <nav style="background-color: #8B4513; color: #FFFFFF;" class="fixed w-full top-0 shadow-lg">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="text-2xl font-bold">eLearning</div>
            <div class="hidden md:flex space-x-4 items-center">
                <a href="index.html" class="nav-link" style="color: #FFFFFF;">Home</a>
                <a href="dashboard.php" class="nav-link" style="color: #FFFFFF;">Dashboard</a>
                <a href="logout.php" class="nav-link" style="color: #FFFFFF;">Logout</a>
            </div>
            <button id="menu-toggle" class="md:hidden focus:outline-none focus-visible:ring-2 focus-visible:ring-white rounded-md p-1" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="mobile-menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
        <div id="mobile-menu" class="hidden" style="background-color: #8B4513;">
            <a href="index.html" class="nav-link block px-4 py-2" style="color: #FFFFFF;">Home</a>
            <a href="dashboard.php" class="nav-link block px-4 py-2" style="color: #FFFFFF;">Dashboard</a>
            <a href="logout.php" class="nav-link block px-4 py-2" style="color: #FFFFFF;">Logout</a>
        </div>
    </nav>

    <!-- Main Content -->
    <section class="pt-20 pb-12">
        <div class="container mx-auto px-4">
            <h1 class="text-3xl font-bold mb-6">Browse Courses</h1>
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if (empty($courses)): ?>
                <p class="text-gray-600 text-center">No courses available at the moment. Please check back later.</p>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($courses as $course): ?>
                        <div class="card">
                            <img src="images/placeholder.jpeg" alt="Placeholder" class="w-full h-40 object-cover">
                            <div class="p-4">
                                <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($course['title']); ?></h3>
                                <p class="text-gray-600 mb-2"><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . (strlen($course['description']) > 100 ? '...' : ''); ?></p>
                                <p class="text-gray-600 mb-2">Teacher: <?php echo htmlspecialchars($course['teacher_name']); ?></p>
                                <?php if (in_array($course['id'], $enrolled_courses)): ?>
                                    <a href="course.php?id=<?php echo $course['id']; ?>" class="enroll-btn">View Course</a>
                                <?php else: ?>
                                    <a href="enroll.php?id=<?php echo $course['id']; ?>" class="enroll-btn">Enroll Now</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer style="background-color: #8B4513; color: white; width: 100vw; margin: 0; padding: 0;">
        <div class="footer-content" style="padding: 2rem 1rem;">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">eLearning Platform</h3>
                    <p>Empowering education through accessible online courses.</p>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                    <ul>
                        <li><a href="index.html" class="hover:text-light-brown-200">Home</a></li>
                        <li><a href="signup.php" class="hover:text-light-brown-200">Signup</a></li>
                        <li><a href="login.php" class="hover:text-light-brown-200">Login</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Contact</h3>
                    <p>Email: info@elearning.com</p>
                    <p>Follow us:</p>
                    <div class="flex space-x-4 mt-2">
                        <a href="#" class="hover:text-light-brown-200">Twitter</a>
                        <a href="#" class="hover:text-light-brown-200">Facebook</a>
                    </div>
                </div>
            </div>
            <div class="mt-8 text-center">
                <p>© 2025 eLearning Platform. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>