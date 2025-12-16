<?php
// reset-password.php - Simple password reset
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (!str_ends_with($email, '@iub.edu.bd')) {
        $error = 'Please use an IUB email address (@iub.edu.bd)';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Check if user exists
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        if ($user) {
            // Update existing user
            $sql = "UPDATE users SET password = ? WHERE email = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $email);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "Password updated successfully!";
            } else {
                $error = "Error updating password: " . mysqli_error($conn);
            }
        } else {
            // Create new user
            $email_parts = explode('@', $email);
            $username = $email_parts[0];
            $name = preg_replace('/[0-9]+/', '', $username);
            $name = empty($name) ? 'IUB User' : ucfirst($name);
            
            $role = 'student';
            if (stripos($email, 'admin') !== false || stripos($email, '2222029') !== false) {
                $role = 'admin';
            } elseif (stripos($email, 'faculty') !== false) {
                $role = 'faculty';
            }
            
            $sql = "INSERT INTO users (name, email, password, role, status, created_at) 
                    VALUES (?, ?, ?, ?, 'active', NOW())";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $hashed_password, $role);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "Account created successfully! Role: " . $role;
            } else {
                $error = "Error creating account: " . mysqli_error($conn);
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
    <title>Reset Password - IUB News Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 500px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #003366; margin-bottom: 20px; text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #003366; color: white; border: none; padding: 12px; width: 100%; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        button:hover { background: #004080; }
        .message { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .back-link { text-align: center; margin-top: 20px; }
        .back-link a { color: #003366; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-key"></i> Reset Password / Create Account</h2>
        
        <?php if ($message): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">IUB Email Address</label>
                <input type="email" id="email" name="email" placeholder="example@iub.edu.bd" required>
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password (min. 6 characters)</label>
                <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
            </div>
            
            <button type="submit">
                <i class="fas fa-sync-alt"></i> Reset Password / Create Account
            </button>
        </form>
        
        <div class="back-link">
            <a href="login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
        </div>
    </div>
</body>
</html>