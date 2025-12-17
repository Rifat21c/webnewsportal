<?php
// fix_db.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Starting Database Fix...</h2>";

require_once 'config.php';

// 1. Test Connection
if ($conn) {
    echo "<p style='color:green'>✅ Database Connected Successfully!</p>";
    echo "<p>Host: $db_host | DB: $db_name</p>";
} else {
    echo "<p style='color:red'>❌ Connection Failed: " . mysqli_connect_error() . "</p>";
    exit;
}

// 2. Fix Users Table 'role' column
// Changing ENUM to VARCHAR to allow 'student', 'faculty', etc.
$sql = "ALTER TABLE users MODIFY COLUMN role VARCHAR(50) DEFAULT 'user'";
if (mysqli_query($conn, $sql)) {
    echo "<p style='color:green'>✅ 'users' table fixed (Role column updated to VARCHAR)</p>";
} else {
    echo "<p style='color:orange'>⚠️ Could not update 'users' table: " . mysqli_error($conn) . "</p>";
}

// 3. Check Admin User
$sql = "SELECT * FROM users WHERE email = 'admin@iub.edu.bd'";
$result = mysqli_query($conn, $sql);
if ($row = mysqli_fetch_assoc($result)) {
    echo "<p style='color:green'>✅ Admin user found: " . $row['email'] . "</p>";
} else {
    echo "<p style='color:red'>❌ Admin user NOT found.</p>";
}

// 4. Test Session
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['test_key'] = 'Session Works';
if (isset($_SESSION['test_key'])) {
    echo "<p style='color:green'>✅ Session system is working</p>";
} else {
    echo "<p style='color:red'>❌ Session system failed</p>";
}

echo "<h3>🎉 Fixes Complete. Try to Register/Login now.</h3>";
echo "<a href='index.php'>Go to Home</a>";
?>
