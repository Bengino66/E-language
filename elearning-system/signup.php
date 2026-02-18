<?php
session_start();
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];
    $role = $_POST['role'];

    $table = $role == 'teacher' ? 'teachers' : 'users';
    try {
        $stmt = $pdo->prepare("INSERT INTO $table (username, password, email) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password, $email]);
        $_SESSION['message'] = "Signup successful! Please login.";
        header("Location: login.php");
        exit;
    } catch (PDOException $e) {
        $error = "Error: Username or email already exists.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/script.js" defer></script>
    <script>
        console.log('styles.css loaded');
    </script>
    <style>
        .form-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding-top: 100px;
            min-height: auto;
            z-index: 10;
        }
        .form-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.2);
            padding: 1.5rem;
            max-width: 320px;
            width: 100%;
            margin: 1rem auto;
            margin-top: 20px;
        }
        .form-card input,
        .form-card select {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 2px solid #8B4513;
            border-radius: 8px;
            font-size: 1rem;
        }
        .form-card button {
            width: 100%;
            padding: 0.75rem;
            background-color: #8B4513;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
        }
        .form-card button:hover {
            background-color: #D2B48C;
        }
        .alert {
            padding: 0.75rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body style="background-color: #F5F5F5;">
    <!-- Navigation Bar -->
    <nav style="background-color: #8B4513; color: #FFFFFF;" class="fixed w-full top-0 shadow-lg">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="text-2xl font-bold">eLearning</div>
            <div class="hidden md:flex space-x-4 items-center">
                <a href="index.html" class="nav-link" style="color: #FFFFFF;">Home</a>
                <a href="signup.php" class="nav-link" style="color: #FFFFFF;">Signup</a>
                <a href="login.php" class="nav-link" style="color: #FFFFFF;">Login</a>
            </div>
            <button id="menu-toggle" class="md:hidden focus:outline-none focus-visible:ring-2 focus-visible:ring-white rounded-md p-1" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="mobile-menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
        <div id="mobile-menu" class="hidden" style="background-color: #8B4513;">
            <a href="index.html" class="nav-link block px-4 py-2" style="color: #FFFFFF;">Home</a>
            <a href="signup.php" class="nav-link block px-4 py-2" style="color: #FFFFFF;">Signup</a>
            <a href="login.php" class="nav-link block px-4 py-2" style="color: #FFFFFF;">Login</a>
        </div>
    </nav>

    <div class="form-container">
        <div class="form-card">
            <h1 class="text-2xl font-bold text-center mb-4">Signup</h1>
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
            <?php endif; ?>
            <form method="POST" onsubmit="return validateSignupForm()">
                <input type="text" id="username" name="username" placeholder="Username" required>
                <input type="password" id="password" name="password" placeholder="Password" required>
                <input type="email" id="email" name="email" placeholder="Email" required>
                <select name="role" required>
                    <option value="user">Student</option>
                    <option value="teacher">Teacher</option>
                </select>
                <button type="submit">Signup</button>
            </form>
        </div>
    </div>

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