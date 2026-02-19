<?php
session_start();
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($role == 'user') {
        // Student login
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = 'user';
            error_log("login.php: Student {$user['id']} logged in, redirecting to dashboard.php");
            header("Location: dashboard.php");
            exit;
        } else {
            $_SESSION['error'] = "Invalid username or password.";
            error_log("login.php: Student login failed for username: $username");
        }
    } elseif ($role == 'teacher') {
        // Teacher login
        $stmt = $pdo->prepare("SELECT id, username, password FROM teachers WHERE username = ?");
        $stmt->execute([$username]);
        $teacher = $stmt->fetch();
        if ($teacher && password_verify($password, $teacher['password'])) {
            $_SESSION['user_id'] = $teacher['id'];
            $_SESSION['role'] = 'teacher';
            error_log("login.php: Teacher {$teacher['id']} logged in, redirecting to teacher_dashboard.php");
            header("Location: teacher_dashboard.php");
            exit;
        } else {
            $_SESSION['error'] = "Invalid username or password.";
            error_log("login.php: Teacher login failed for username: $username");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - eLearning Platform</title>
    <!-- Tailwind CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" onerror="console.error('Failed to load Tailwind CDN')">
    <!-- Local styles.css -->
    <link rel="stylesheet" href="css/styles.css" onerror="console.error('Failed to load styles.css')">
    <script src="js/script.js" defer></script>
    <script>
        console.log('login.php: styles.css loaded');
    </script>
    <style>
        /* Fallback inline styles */
        .input-field {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 0.25rem;
            margin-top: 0.25rem;
        }
        .enroll-btn {
            background-color: #8B4513;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            text-align: center;
            display: inline-block;
        }
        .enroll-btn:hover {
            background-color: #A0522D;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 0.75rem;
            border-radius: 0.25rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body style="background-color: #F5F5F5;">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-white text-gray-800 p-2 z-[100] rounded shadow border-2 border-gray-800">Skip to content</a>
    <!-- Navigation Bar -->
    <nav style="background-color: #8B4513; color: #FFFFFF;" class="fixed w-full top-0 shadow-lg">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="text-2xl font-bold">eLearning</div>
            <div class="hidden md:flex space-x-4 items-center">
                <a href="index.html" class="nav-link" style="color: #FFFFFF;">Home</a>
                <a href="login.php" class="nav-link" style="color: #FFFFFF;">Login</a>
                <a href="signup.php" class="nav-link" style="color: #FFFFFF;">Signup</a>
            </div>
            <button id="menu-toggle" class="md:hidden focus:outline-none focus-visible:ring-2 focus-visible:ring-white rounded-md p-1" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="mobile-menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
        <div id="mobile-menu" class="hidden" style="background-color: #8B4513;">
            <a href="index.html" class="nav-link block px-4 py-2" style="color: #FFFFFF;">Home</a>
            <a href="login.php" class="nav-link block px-4 py-2" style="color: #FFFFFF;">Login</a>
            <a href="signup.php" class="nav-link block px-4 py-2" style="color: #FFFFFF;">Signup</a>
        </div>
    </nav>

    <!-- Login Form -->
    <main id="main-content">
    <section class="pt-20 pb-12">
        <div class="container mx-auto px-4">
            <h1 class="text-3xl font-bold mb-6 text-center">Login</h1>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <form action="login.php" method="POST" class="max-w-md mx-auto">
                <div class="mb-4">
                    <label for="username" class="block text-gray-700">Username</label>
                    <input type="text" id="username" name="username" class="input-field" required>
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-gray-700">Password</label>
                    <input type="password" id="password" name="password" class="input-field" required>
                </div>
                <div class="mb-4">
                    <label for="role" class="block text-gray-700">Role</label>
                    <select id="role" name="role" class="input-field" required>
                        <option value="user">Student</option>
                        <option value="teacher">Teacher</option>
                    </select>
                </div>
                <button type="submit" class="enroll-btn w-full">Login</button>
            </form>
            <p class="mt-4 text-center">Don't have an account? <a href="signup.php" class="text-blue-600 hover:underline">Sign up</a></p>
        </div>
    </section>

    </main>

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