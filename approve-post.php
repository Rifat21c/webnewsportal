<?php
// approve-post.php
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

if (updatePostStatus($postId, 'approved')) {
    $_SESSION['message'] = 'Post approved successfully!';
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = 'Error approving post.';
    $_SESSION['message_type'] = 'danger';
}

header('Location: admin-dashboard.php');
exit();
?>