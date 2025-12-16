<?php
session_start();
require_once 'config.php';

echo "<h2>Testing User Authentication</h2>";

// Logout first
session_destroy();
session_start();

// Login as your user
$email = '2222029@iub.edu.bd';
$password = 'yourpassword';

$sql = "SELECT * FROM users WHERE email = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user) {
    echo "<p>Found user with ID: " . $user['id'] . "</p>";
    echo "<p>Database password: " . $user['password'] . "</p>";
    
    if ($password === $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        
        echo "<h3 style='color: green;'>✅ Login successful!</h3>";
        echo "<p>Session user_id: " . $_SESSION['user_id'] . " (should be 4)</p>";
        echo "<p><a href='create-post.php'>Click here to test post creation</a></p>";
    } else {
        echo "<h3 style='color: red;'>❌ Password mismatch!</h3>";
        echo "<p>Your password is: '$password'</p>";
        echo "<p>Database has: '" . $user['password'] . "'</p>";
    }
} else {
    echo "<h3 style='color: red;'>❌ User not found!</h3>";
}
?>