<?php
// profile.php - Edit Profile
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';
require_once 'database.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$pageTitle = 'Edit Profile';
$success_msg = '';
$error_msg = '';

$user = getUserById($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($name)) {
        $error_msg = 'Name cannot be empty';
    } elseif (!empty($password) && strlen($password) < 6) {
        $error_msg = 'Password must be at least 6 characters';
    } elseif (!empty($password) && $password !== $confirm_password) {
        $error_msg = 'Passwords do not match';
    } else {
        // Update user
        if (updateUser($_SESSION['user_id'], $name, !empty($password) ? $password : null)) {
            $success_msg = 'Profile updated successfully!';
            // Refresh user data
            $user = getUserById($_SESSION['user_id']);
        } else {
            $error_msg = 'Error updating profile. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - IUB News Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container nav-container">
            <a href="index.php" class="logo">
                <div class="logo-icon">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div class="logo-text">
                    <h1>IUB News Portal</h1>
                </div>
            </a>
            
            <div class="nav-links">
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <a href="admin-dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <?php else: ?>
                    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-outline btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container" style="max-width: 600px; margin-top: 4rem; margin-bottom: 4rem;">
        <div class="card p-5">
            <div class="text-center mb-4">
                <div class="avatar-circle mb-3 mx-auto" style="width: 80px; height: 80px; font-size: 2rem; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                    <i class="fas fa-user"></i>
                </div>
                <h2 class="mb-2">Edit Profile</h2>
                <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>

            <?php if ($success_msg): ?>
                <div class="alert alert-success animate-fade-in">
                    <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
                <div class="alert alert-danger animate-fade-in">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group mb-4">
                    <label for="name" class="form-label">Full Name</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label for="password" class="form-label">New Password <span class="text-muted fw-normal">(Leave blank to keep current)</span></label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Minimum 6 characters">
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm new password">
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="<?php echo $_SESSION['user_role'] === 'admin' ? 'admin-dashboard.php' : 'dashboard.php'; ?>" class="btn btn-outline btn-block text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer mt-auto">
        <div class="container text-center">
            <p>&copy; 2025 Independent University, Bangladesh. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
