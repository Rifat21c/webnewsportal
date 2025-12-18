<?php
// reset_admin.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

$email = 'admin@iub.edu.bd';
$new_pass = 'admin12';
$hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);

echo "<h2>Admin Password Reset Tool</h2>";

// Check if user exists
$check_sql = "SELECT id FROM users WHERE email = '$email'";
$result = mysqli_query($conn, $check_sql);

if (mysqli_num_rows($result) > 0) {
    // Update existing user
    $sql = "UPDATE users SET password = '$hashed_password', role = 'admin' WHERE email = '$email'";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color:green'>✅ Password for <strong>$email</strong> has been reset to <strong>$new_pass</strong>.</p>";
    } else {
        echo "<p style='color:red'>❌ Error updating password: " . mysqli_error($conn) . "</p>";
    }
} else {
    // Create the user if it doesn't exist
    $sql = "INSERT INTO users (name, email, password, role, status) VALUES ('Admin User', '$email', '$hashed_password', 'admin', 'active')";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color:green'>✅ Admin user created with email <strong>$email</strong> and password <strong>$new_pass</strong>.</p>";
    } else {
        echo "<p style='color:red'>❌ Error creating admin user: " . mysqli_error($conn) . "</p>";
    }
}

echo "<br><a href='login.php'>Go to Login Page</a>";
?>
