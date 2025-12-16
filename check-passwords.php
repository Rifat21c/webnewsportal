<?php
// check-passwords.php - Find the correct password
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only allow from localhost
if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1', 'localhost'])) {
    die('Access denied. Local only.');
}

require_once 'config.php';

echo '<!DOCTYPE html>
<html>
<head>
    <title>Check Passwords - IUB News Portal</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        h2 { color: #003366; }
        .user { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        input { padding: 8px; margin: 5px; }
        button { background: #003366; color: white; border: none; padding: 10px 15px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Password Checker</h2>';

// Common passwords to try
$common_passwords = [
    'password123',
    'admin123',
    '123456',
    'password',
    '2222029',
    '2222028',
    'iub123',
    'iub@123',
    'test123',
    'admin@iub',
    'faculty123',
    'student123'
];

// Get all users
$sql = "SELECT id, name, email, password FROM users";
$result = mysqli_query($conn, $sql);

$found_passwords = [];

while ($user = mysqli_fetch_assoc($result)) {
    echo '<div class="user">';
    echo '<h3>' . htmlspecialchars($user['name']) . ' (' . htmlspecialchars($user['email']) . ')</h3>';
    
    foreach ($common_passwords as $test_password) {
        if (password_verify($test_password, $user['password'])) {
            echo '<p class="success">✓ Password found: <strong>' . htmlspecialchars($test_password) . '</strong></p>';
            $found_passwords[$user['email']] = $test_password;
            break;
        }
    }
    
    if (!isset($found_passwords[$user['email']])) {
        echo '<p class="error">✗ Password not found in common list</p>';
    }
    
    echo '</div>';
}

echo '<hr><h3>Found Passwords Summary:</h3>';
if (!empty($found_passwords)) {
    echo '<ul>';
    foreach ($found_passwords as $email => $password) {
        echo '<li><strong>' . htmlspecialchars($email) . '</strong>: ' . htmlspecialchars($password) . '</li>';
    }
    echo '</ul>';
}

echo '</div></body></html>';
?>