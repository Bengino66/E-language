<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.php");
    exit;
}

// Fetch teacher info
$stmt = $pdo->prepare("SELECT username FROM teachers WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$teacher = $stmt->fetch();

// Fetch teacher's courses
$stmt = $pdo->prepare("SELECT c.*, (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) as student_count FROM courses c WHERE c.teacher_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$courses = $stmt->fetchAll();

// Fetch resources
$stmt = $pdo->prepare("SELECT r.*, c.title as course_title FROM resources r JOIN courses c ON r.course_id = c.id WHERE r.teacher_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$resources = $stmt->fetchAll();

// Analytics
$total_courses = count($courses);
$total_students = array_sum(array_column($courses, 'student_count'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/script.js" defer></script>
    <script>
        console.log('teacher_dashboard.php: styles.css loaded');
    </script>
</head>
<body style="background-color: #F5F5F5;">
    <!-- Navigation Bar -->
    <nav style="background-color: #8B4513; color: #FFFFFF;" class="fixed w-full top-0 shadow-lg">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="text-2xl font-bold">eLearning</div>
            <div class="hidden md:flex space-x-4 items-center">
                <a href="index.html" class="nav-link" style="color: #FFFFFF;">Home</a>
                <a href="teacher_dashboard.php" class="nav-link" style="color: #FFFFFF;">Dashboard</a>
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
            <a href="teacher_dashboard.php" class="nav-link block px-4 py-2" style="color: #FFFFFF;">Dashboard</a>
            <a href="logout.php" class="nav-link block px-4 py-2" style="color: #FFFFFF;">Logout</a>
        </div>
    </nav>

    <!-- Main Content -->
    <section class="pt-20 pb-12">
        <div class="container mx-auto px-4">
            <h1 class="welcome-message">Welcome, <?php echo htmlspecialchars($teacher['username']); ?>!</h1>
            <div class="mb-8 bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-2xl font-semibold mb-4">Analytics</h2>
                <p>Total Courses: <?php echo $total_courses; ?></p>
                <p>Total Students Enrolled: <?php echo $total_students; ?></p>
                <p>Total Resources Posted: <?php echo count($resources); ?></p>
            </div>
            <div class="mb-8">
                <h2 class="text-2xl font-semibold mb-4">Post Resource</h2>
                <form method="POST" action="post_resource.php" enctype="multipart/form-data" class="form-card" onsubmit="return validateResourceForm()">
                    <select name="course_id" required>
                        <option value="">Select Course</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="title" placeholder="Resource Title" required>
                    <textarea name="description" placeholder="Resource Description"></textarea>
                    <select name="resource_type" required>
                        <option value="document">Document (PDF, Word)</option>
                        <option value="image">Image (JPEG, PNG)</option>
                        <option value="video">Video (MP4, WebM)</option>
                        <option value="url">URL/Link</option>
                        <option value="other">Other</option>
                    </select>
                    <input type="file" name="file" id="resource-file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.mp4,.webm">
                    <input type="url" name="url" id="resource-url" placeholder="Enter URL (if applicable)">
                    <button type="submit">Post Resource</button>
                </form>
            </div>
            <div class="mb-8">
                <h2 class="text-2xl font-semibold mb-4">Create New Course</h2>
                <form method="POST" action="create_course.php" class="form-card" onsubmit="return validateCourseForm()">
                    <input type="text" name="title" placeholder="Course Title" required>
                    <textarea name="description" placeholder="Course Description" required></textarea>
                    <button type="submit">Create Course</button>
                </form>
            </div>
            <h2 class="text-2xl font-semibold mb-4">Your Courses</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($courses as $course): ?>
                    <div class="card">
                        <img src="images/placeholder.jpeg" alt="Placeholder" class="w-full h-40 object-cover">
                        <div class="p-4">
                            <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($course['title']); ?></h3>
                            <p class="text-gray-600 mb-2"><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . (strlen($course['description']) > 100 ? '...' : ''); ?></p>
                            <p class="text-gray-600 mb-2">Students: <?php echo $course['student_count']; ?></p>
                            <a href="edit_course.php?id=<?php echo $course['id']; ?>" class="enroll-btn">Edit Course</a>
                            <a href="delete_course.php?id=<?php echo $course['id']; ?>" class="action-btn danger mt-2" onclick="return confirm('Are you sure you want to delete this course?');">Delete Course</a>
                            <a href="view_students.php?id=<?php echo $course['id']; ?>" class="action-btn mt-2">View Students</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <h2 class="text-2xl font-semibold mb-4 mt-8">Your Resources</h2>
            <div>
                <?php if (empty($resources)): ?>
                    <p>No resources posted yet.</p>
                <?php else: ?>
                    <?php foreach ($resources as $resource): ?>
                        <div class="resource-card">
                            <div class="resource-icon <?php echo htmlspecialchars($resource['resource_type']); ?>"></div>
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold"><?php echo htmlspecialchars($resource['title']); ?></h4>
                                <p class="text-gray-600">Course: <?php echo htmlspecialchars($resource['course_title']); ?></p>
                                <p class="text-gray-600"><?php echo htmlspecialchars(substr($resource['description'], 0, 100)) . (strlen($resource['description']) > 100 ? '...' : ''); ?></p>
                                <p class="text-gray-600">Type: <?php echo ucfirst($resource['resource_type']); ?></p>
                                <?php if ($resource['resource_type'] == 'url'): ?>
                                    <a href="<?php echo htmlspecialchars($resource['file_path']); ?>" target="_blank" class="text-blue-500 hover:underline">View Resource</a>
                                <?php else: ?>
                                    <a href="Uploads/<?php echo htmlspecialchars($resource['file_path']); ?>" target="_blank" class="text-blue-500 hover:underline">Download/View</a>
                                <?php endif; ?>
                            </div>
                            <div class="ml-4">
                                <a href="edit_resource.php?id=<?php echo $resource['id']; ?>" class="text-blue-500 hover:underline">Edit</a>
                                <a href="delete_resource.php?id=<?php echo $resource['id']; ?>" class="text-red-500 hover:underline ml-2" onclick="return confirm('Are you sure you want to delete this resource?');">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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