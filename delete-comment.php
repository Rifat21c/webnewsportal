<?php
require_once 'database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Check if comment ID and post ID are provided
if (!isset($_GET['id']) || !isset($_GET['post_id'])) {
    header('Location: index.php');
    exit();
}

$commentId = intval($_GET['id']);
$postId = intval($_GET['post_id']);
$user = getUser();

// In a real application, you would check if user owns the comment or is admin
if (isAdmin()) {
    deleteComment($commentId);
    
    $_SESSION['message'] = 'Comment deleted successfully!';
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = 'You are not authorized to delete this comment.';
    $_SESSION['message_type'] = 'danger';
}

// Redirect back to post
header("Location: post-detail.php?id=$postId");
exit();
?>