<?php
// delete-post.php
session_start();
require_once 'database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if user is admin
if ($_SESSION['user_role'] !== 'admin') {
    $_SESSION['message'] = 'Unauthorized access.';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit();
}

if (isset($_GET['id'])) {
    $postId = intval($_GET['id']);
    
    if (deletePost($postId)) {
        $_SESSION['message'] = 'Post deleted successfully.';
        $_SESSION['message_type'] = 'success';
        header('Location: dashboard.php'); // Redirect to dashboard after delete
    } else {
        $_SESSION['message'] = 'Error deleting post.';
        $_SESSION['message_type'] = 'error';
        header("Location: post-detail.php?id=$postId");
    }
} else {
    header('Location: index.php');
}
exit();
?>