<?php
// config.php - IUB News Portal Configuration
ob_start();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// Authentication Functions
function isLoggedIn() {
    // Ensure session is started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function getUser() {
    if (!isLoggedIn()) return null;
    
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['user_member_name'] ?? $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'student'
    ];
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        $_SESSION['message'] = 'Access denied. Admin privileges required.';
        $_SESSION['message_type'] = 'danger';
        header('Location: index.php');
        exit();
    }
}

// Auto-logout after inactivity (30 minutes)
function checkSessionTimeout() {
    $timeout = 1800; // 30 minutes in seconds
    
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
        session_unset();
        session_destroy();
        header('Location: login.php?timeout=1');
        exit();
    }
    $_SESSION['LAST_ACTIVITY'] = time();
}

// Check session timeout only if user is logged in
if (isLoggedIn()) {
    checkSessionTimeout();
}
