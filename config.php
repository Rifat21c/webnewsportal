<?php
// config.php - IUB News Portal Configuration

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'iub_news_portal';

// Override for production (InfinityFree)
// We check if the host contains 'localhost' or '127.0.0.1' to keep it local.
// If NOT local, we assume production.
if (strpos($_SERVER['HTTP_HOST'], 'localhost') === false && strpos($_SERVER['HTTP_HOST'], '127.0.0.1') === false) {
    $db_host = 'sql100.infinityfree.com';
    $db_user = 'if0_40705823';
    $db_pass = 'n6K9HNj5Hp';
    $db_name = 'if0_40705823_newsportal'; // Corrected database name
}

// Error reporting (development only - disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect to database with error handling
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die('<div style="text-align: center; padding: 50px; font-family: Arial, sans-serif;">
            <h2 style="color: #003366;">Database Connection Error</h2>
            <p>The system is currently undergoing maintenance. Please try again later.</p>
            <p style="color: #666; font-size: 12px;">Error: ' . mysqli_connect_error() . '</p>
        </div>');
}

mysqli_set_charset($conn, "utf8mb4");

// Timezone setting
date_default_timezone_set('Asia/Dhaka');
