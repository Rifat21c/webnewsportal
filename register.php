<?php
// register.php
require_once 'config.php';
require_once 'database.php';

$pageTitle = 'Create Account';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['fullName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'student';

    // Basic Validation
    if (substr(strtolower($email), -11) !== '@iub.edu.bd') {
        $error = 'Please use a valid IUB email address (@iub.edu.bd)';
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } else {
        // Assume registerUser function is in database.php or we implement it here
        // For safety, checking if registerUser exists
        if(function_exists('registerUser')) {
            $result = registerUser($name, $email, $password, $role);
            if ($result['success']) {
                // Auto Login
                if (session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['user_id'] = $result['user_id'];
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = $role;
                $_SESSION['logged_in'] = true;
                header('Location: index.php');
                exit();
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscribe - IUB News Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="news_styles.css">
    <style>
        .auth-wrapper {
            max-width: 500px;
            margin: 4rem auto;
            border: 1px solid var(--border-color);
            padding: 2.5rem;
            background: white;
        }
        .auth-title {
            font-family: var(--font-serif);
            font-size: 2rem;
            margin-bottom: 0.5rem;
            text-align: center;
        }
        .auth-subtitle {
            text-align: center;
            color: var(--text-muted);
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <?php require_once 'header.php'; ?>

    <main class="container">
        <div class="auth-wrapper">
            <h2 class="auth-title">Create Account</h2>
            <p class="auth-subtitle">Join the IUB News Community</p>

            <?php if ($error): ?>
                <div style="background: var(--danger); color: white; padding: 1rem; margin-bottom: 1.5rem; font-weight: 500;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="fullName" class="form-control" placeholder="John Doe" required value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">IUB Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="name@iub.edu.bd" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Min 6 characters" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control">
                        <option value="student">Student</option>
                        <option value="faculty">Faculty</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; border-radius: 0; padding: 1rem; margin-top: 1rem;">CREATE ACCOUNT</button>
            </form>

            <div class="text-center" style="margin-top: 1.5rem; font-size: 0.9rem;">
                <p>Already have an account? <a href="login.php" style="font-weight: 700;">Log In</a></p>
                <p class="text-muted" style="font-size: 0.8rem; margin-top: 1rem;">By creating an account, you agree to our Terms of Service.</p>
            </div>
        </div>
    </main>
    
    <footer style="text-align: center; padding: 2rem; color: var(--text-muted); font-size: 0.8rem; border-top: 1px solid var(--border-color);">
        &copy; 2025 Independent University, Bangladesh.
    </footer>
</body>
</html>