<?php
// reset-all.php
require_once 'config.php';

echo "Resetting ALL passwords to 'password123'...<br>";

// Create a valid bcrypt hash for 'password123'
$hash = password_hash('password123', PASSWORD_BCRYPT);

echo "Hash: $hash<br><br>";

// Update all users
$sql = "UPDATE users SET password = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $hash);

if (mysqli_stmt_execute($stmt)) {
    echo "SUCCESS! All passwords reset.<br><br>";
    
    // Show all users
    $result = mysqli_query($conn, "SELECT * FROM users");
    while ($row = mysqli_fetch_assoc($result)) {
        echo "ID: {$row['id']} - {$row['name']} ({$row['email']}) - Role: {$row['role']}<br>";
        echo "Login with password: <strong>password123</strong><br><br>";
    }
    
    echo "<a href='login.php' style='padding: 10px 20px; background: #003366; color: white; text-decoration: none; border-radius: 5px;'>GO TO LOGIN PAGE</a>";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>