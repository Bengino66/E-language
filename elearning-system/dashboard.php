<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    error_log("dashboard.php: Redirecting to login.php - user_id: " . ($_SESSION['user_id'] ?? 'unset') . ", role: " . ($_SESSION['role'] ?? 'unset'));
    header("Location: login.php");
    exit;
}

// Fetch user info
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Fetch enrolled courses
$stmt = $pdo->prepare("SELECT e.id, e.course_id, c.title, c.description FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE e.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$enrollments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/script.js" defer></script>
    <script>
        console.log('dashboard.php: styles.css loaded');
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
            <h1 class="welcome-message">Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold">Your Enrolled Courses</h2>
                <a href="courses.php" class="enroll-btn">Browse Courses</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($enrollments as $enrollment): ?>
                    <div class="card">
                        <img src="images/placeholder.jpeg" alt="Placeholder" class="w-full h-40 object-cover">
                        <div class="p-4">
                            <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($enrollment['title']); ?></h3>
                            <p class="text-gray-600 mb-2"><?php echo htmlspecialchars(substr($enrollment['description'], 0, 100)) . (strlen($enrollment['description']) > 100 ? '...' : ''); ?></p>
                            <div class="progress-bar">
                                <div class="progress-bar-fill" style="width: 0%;" data-progress="0"></div>
                            </div>
                            <a href="course.php?id=<?php echo $enrollment['course_id']; ?>" class="enroll-btn mt-2">View Course</a>
                            <a href="unenroll.php?id=<?php echo $enrollment['id']; ?>" class="action-btn danger mt-2" onclick="return confirm('Are you sure you want to unenroll?');">Unenroll</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
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