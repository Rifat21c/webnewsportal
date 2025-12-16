<?php
// fix-admin.php
require_once 'config.php';

echo "Fixing admin password...<br>";

// The password we want to set
$password = "admin123";

// Create a valid bcrypt hash
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "New hash: $hash<br>";

// Update the admin user
$sql = "UPDATE users SET password = ? WHERE email = 'admin@iub.edu.bd'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $hash);

if (mysqli_stmt_execute($stmt)) {
    echo "SUCCESS! Admin password updated.<br>";
    echo "You can now login with:<br>";
    echo "Email: admin@iub.edu.bd<br>";
    echo "Password: admin123<br>";
    echo "<br><a href='login.php'>Go to Login Page</a>";
} else {
    echo "ERROR: " . mysqli_error($conn);
}
?>