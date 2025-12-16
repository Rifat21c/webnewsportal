<?php
// reject-post.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: admin-dashboard.php');
    exit();
}

$postId = intval($_GET['id']);

if (updatePostStatus($postId, 'rejected')) {
    $_SESSION['message'] = 'Post rejected successfully!';
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = 'Error rejecting post.';
    $_SESSION['message_type'] = 'danger';
}

header('Location: admin-dashboard.php');
exit();
?>