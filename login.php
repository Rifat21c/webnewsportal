<?php
// login.php professional refactor
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';
require_once 'database.php';

$pageTitle = 'Sign In';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Login Logic
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (substr($email, -11) !== '@iub.edu.bd') {
        $error = 'Please use your IUB email address (@iub.edu.bd)';
    } else {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        
        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['logged_in'] = true;
                header('Location: index.php');
                exit();
            } else {
                $error = 'Incorrect password. Please try again.';
            }
        } else {
            $error = 'Account not found. Verify your email is correct.';
        }
    }
    // If we reach here after a POST but without a redirect or error, set a fallback
    if (empty($error)) {
        $error = "Unable to process login. Please try again or create a new account.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - IUB News Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="news_styles.css">
    <style>
        .auth-wrapper {
            max-width: 450px;
            margin: 4rem auto;
            border: 1px solid var(--border-color);
            padding: 2.5rem;
            background: white;
            /* Flat news style, no radius, no shadow */
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
            <h2 class="auth-title">Log in</h2>
            <p class="auth-subtitle">Welcome back to IUB News</p>

            <?php if ($error): ?>
                <div style="background: var(--danger); color: white; padding: 1rem; margin-bottom: 1.5rem; font-weight: 500;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="user@iub.edu.bd" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group" style="margin-bottom: 2rem;">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; border-radius: 0; padding: 1rem;">LOG IN</button>
            </form>

            <div class="text-center" style="margin-top: 1.5rem; font-size: 0.9rem;">
                <p>Don't have an account? <a href="register.php" style="font-weight: 700;">Subscribe Now</a></p>
                <a href="#" style="color: var(--text-muted);">Forgot your password?</a>
            </div>
        </div>
    </main>

    <!-- Simple footer for auth pages -->
    <footer style="text-align: center; padding: 2rem; color: var(--text-muted); font-size: 0.8rem; border-top: 1px solid var(--border-color); margin-top: auto;">
        &copy; 2025 Independent University, Bangladesh. All rights reserved.
    </footer>
</body>
</html>