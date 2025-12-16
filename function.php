<?php
// functions.php
function createPost($title, $content, $category, $authorId, $imageUrl = null) {
    global $conn;
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['message'] = 'You must be logged in to create a post';
        $_SESSION['message_type'] = 'danger';
        return false;
    }
    
    // Use the logged-in user's ID
    $authorId = $_SESSION['user_id'];
    
    // Verify user exists in database
    $check_sql = "SELECT id FROM users WHERE id = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "i", $authorId);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    
    if (mysqli_stmt_num_rows($check_stmt) === 0) {
        $_SESSION['message'] = 'Error: Your user account was not found.';
        $_SESSION['message_type'] = 'danger';
        return false;
    }
    
    // Create the post
    $sql = "INSERT INTO posts (title, content, category, author_id, image_url) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssis", $title, $content, $category, $authorId, $imageUrl);
    
    if (mysqli_stmt_execute($stmt)) {
        return mysqli_insert_id($conn);
    } else {
        $_SESSION['message'] = 'Error creating post: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'danger';
        return false;
    }
}

function updatePostStatus($postId, $status) {
    global $conn;
    $sql = "UPDATE posts SET status = ? WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $postId);
    return mysqli_stmt_execute($stmt);
}

function deletePost($postId) {
    global $conn;
    $sql = "DELETE FROM posts WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $postId);
    return mysqli_stmt_execute($stmt);
}
?>