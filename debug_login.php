<?php
// debug_login.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

echo "<h2>Debug Login Test</h2>";

require_once 'config.php';

$test_email = 'admin@iub.edu.bd';
$test_pass = '1234'; // We'll test this password

echo "<p>Testing login for: <strong>$test_email</strong></p>";

// 1. Check User exists
$sql = "SELECT * FROM users WHERE email = '$test_email'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

if ($user) {
    echo "<p style='color:green'>✅ User found: " . $user['name'] . " (ID: " . $user['id'] . ")</p>";
    echo "<p>Stored Password Hash: " . substr($user['password'], 0, 10) . "...</p>";
    
    // 2. Verify Password
    if (password_verify($test_pass, $user['password'])) {
        echo "<p style='color:green'>✅ Password '1234' matches!</p>";
        
        // 3. Test Session
        $_SESSION['debug_user'] = $user['id'];
        echo "<p>Session 'debug_user' set. <a href='debug_check_session.php'>Click here to check if session persists</a></p>";
    } else {
        echo "<p style='color:red'>❌ Password '1234' does NOT match.</p>";
        echo "<p>We will attempt to RESET the password to '1234' now...</p>";
        
        // Force reset password
        $new_hash = password_hash('1234', PASSWORD_DEFAULT);
        $update = mysqli_query($conn, "UPDATE users SET password = '$new_hash' WHERE email = '$test_email'");
        
        if ($update) {
            echo "<p style='color:blue'>ℹ️ Password has been reset to '1234'. Try logging in normally now.</p>";
        } else {
            echo "<p style='color:red'>❌ Failed to reset password: " . mysqli_error($conn) . "</p>";
        }
    }
} else {
    echo "<p style='color:red'>❌ User NOT found. The database import might be incomplete.</p>";
    
    // Attempt to create admin
    echo "<p>Attempting to create admin user...</p>";
    $pass = password_hash('1234', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (name, email, password, role) VALUES ('Admin', '$test_email', '$pass', 'admin')";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color:green'>✅ Admin user created! Try logging in now.</p>";
    } else {
        echo "<p style='color:red'>❌ Failed to create user: " . mysqli_error($conn) . "</p>";
    }
}
?>
