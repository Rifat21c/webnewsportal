<?php
// post-detail.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'database.php';
// header.php is included later via getHeader()


// Check if post ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$postId = intval($_GET['id']);
$post = getPostById($postId);

// If post doesn't exist, redirect to homepage
if (!$post) {
    $_SESSION['message'] = 'Post not found or has been removed.';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit();
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_comment'])) {
    if (!isLoggedIn()) {
        $_SESSION['message'] = 'You must be logged in to comment.';
        $_SESSION['message_type'] = 'error';
    } else {
        $comment = trim($_POST['comment'] ?? '');
        
        if (empty($comment)) {
            $_SESSION['message'] = 'Comment cannot be empty.';
            $_SESSION['message_type'] = 'error';
        } elseif (strlen($comment) > 1000) {
            $_SESSION['message'] = 'Comment is too long (max 1000 characters).';
            $_SESSION['message_type'] = 'error';
        } else {
            $userId = $_SESSION['user_id'];
            if (addComment($postId, $userId, $comment)) {
                $_SESSION['message'] = 'Comment added successfully!';
                $_SESSION['message_type'] = 'success';
                // Refresh the page to show new comment
                header("Location: post-detail.php?id=$postId");
                exit();
            }
        }
    }
}

// Handle comment deletion (admin only)
if (isset($_GET['delete_comment']) && isAdmin()) {
    $commentId = intval($_GET['delete_comment']);
    if (deleteComment($commentId)) {
        $_SESSION['message'] = 'Comment deleted successfully.';
        $_SESSION['message_type'] = 'success';
        header("Location: post-detail.php?id=$postId");
        exit();
    }
}

// Get comments for this post
$comments = getComments($postId);

// Get related posts
$relatedPosts = getRelatedPosts($postId, $post['category'], 3);

$pageTitle = htmlspecialchars($post['title']);
getHeader($pageTitle);
?>

<!-- Post Detail -->
<main class="post-detail-main">
    <div class="container">
        <!-- Post Content -->
        <article class="post-content">
            <!-- Post Header -->
                <div class="post-header">
                <div class="post-meta">
                    <span class="category-tag"><?php echo htmlspecialchars($post['category']); ?></span>
                    <span class="post-status status-<?php echo $post['status']; ?>">
                        <?php echo ucfirst($post['status']); ?>
                    </span>
                </div>
                
                <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                
                <div class="post-author-info">
                    <div class="author-details">
                        <span class="author-name">By <?php echo htmlspecialchars($post['author_name']); ?></span>
                        <span class="author-role">(<?php echo ucfirst($post['author_role']); ?>)</span>
                    </div>
                    <div class="post-stats">
                        <span class="post-date">
                            <i class="far fa-calendar"></i> 
                            <?php echo formatDate($post['created_at']); ?>
                        </span>
                        <span class="post-views">
                            <i class="far fa-eye"></i> 
                            <?php echo $post['views']; ?> views
                        </span>
                        <?php if (isLoggedIn()): 
                            $isBookmarked = isBookmarked($_SESSION['user_id'], $post['id']);
                        ?>
                            <button id="bookmark-btn" class="btn btn-sm <?php echo $isBookmarked ? 'btn-primary' : 'btn-outline'; ?>" 
                                    data-id="<?php echo $post['id']; ?>"
                                    onclick="toggleBookmark(<?php echo $post['id']; ?>)">
                                <i class="<?php echo $isBookmarked ? 'fas' : 'far'; ?> fa-bookmark"></i>
                                <?php echo $isBookmarked ? 'Bookmarked' : 'Bookmark'; ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Post Image -->
            <?php if (!empty($post['image_url'])): ?>
                <div class="post-image-container">
                    <img src="<?php echo htmlspecialchars($post['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($post['title']); ?>" 
                         class="post-image">
                </div>
            <?php endif; ?>

            <!-- Post Video -->
            <?php if (!empty($post['video_url'])): ?>
                <div class="post-video-container" style="margin-bottom: 25px; border-radius: 10px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                    <video controls style="width: 100%; display: block;">
                        <source src="<?php echo htmlspecialchars($post['video_url']); ?>" type="video/mp4">
                        <source src="<?php echo htmlspecialchars($post['video_url']); ?>" type="video/webm">
                        Your browser does not support the video tag.
                    </video>
                </div>
            <?php endif; ?>

            <!-- Post Body -->
            <div class="post-body">
                <?php 
                // Display content allowing HTML (from Rich Text Editor)
                // Note: In a production environment, you should use HTMLPurifier here to prevent XSS
                echo $post['content']; 
                ?>
            </div>

            <!-- Post Actions -->
            <div class="post-actions">
                <?php if (isAdmin()): ?>
                    <div class="admin-actions">
                        <?php if ($post['status'] == 'pending'): ?>
                            <a href="approve-post.php?id=<?php echo $post['id']; ?>" class="btn-success">
                                <i class="fas fa-check"></i> Approve Post
                            </a>
                            <a href="reject-post.php?id=<?php echo $post['id']; ?>" class="btn-danger">
                                <i class="fas fa-times"></i> Reject Post
                            </a>
                        <?php elseif ($post['status'] == 'rejected'): ?>
                            <a href="approve-post.php?id=<?php echo $post['id']; ?>" class="btn-success">
                                <i class="fas fa-check"></i> Approve Post
                            </a>
                        <?php endif; ?>
                        
                        <a href="delete-post.php?id=<?php echo $post['id']; ?>" class="btn-danger" onclick="return confirm('Are you sure you want to permanently delete this post?');">
                            <i class="fas fa-trash"></i> Delete Post
                        </a>
                    </div>
                <?php endif; ?>
                
                <?php if (isLoggedIn() && $_SESSION['user_id'] == $post['author_id']): ?>
                    <a href="edit-post.php?id=<?php echo $post['id']; ?>" class="btn-outline">
                        <i class="fas fa-edit"></i> Edit Post
                    </a>
                <?php endif; ?>
            </div>
        </article>

        <!-- Comments Section -->
        <section class="comments-section">
            <div class="comments-header">
                <h3>
                    <i class="far fa-comments"></i> 
                    Comments 
                    <span class="comments-count">(<?php echo count($comments); ?>)</span>
                </h3>
            </div>

            <!-- Add Comment Form -->
            <?php if (isLoggedIn()): ?>
                <div class="add-comment-form">
                    <form method="POST" action="">
                        <div class="form-group">
                            <textarea name="comment" id="comment" 
                                      placeholder="Add your comment..." 
                                      rows="4" maxlength="1000" required></textarea>
                        </div>
                        <button type="submit" name="add_comment" class="btn-primary">
                            <i class="fas fa-paper-plane"></i> Post Comment
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="login-prompt">
                    <p>
                        <a href="login.php">Login</a> to join the discussion and leave a comment.
                    </p>
                </div>
            <?php endif; ?>

            <!-- Comments List -->
            <div class="comments-list">
                <?php if (empty($comments)): ?>
                    <div class="empty-comments">
                        <i class="far fa-comment-dots"></i>
                        <h4>No comments yet</h4>
                        <p>Be the first to comment on this post!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-item" id="comment-<?php echo $comment['id']; ?>">
                            <div class="comment-header">
                                <div class="comment-author">
                                    <strong><?php echo htmlspecialchars($comment['user_name']); ?></strong>
                                    <span class="comment-role">(<?php echo ucfirst($comment['user_role']); ?>)</span>
                                </div>
                                <div class="comment-meta">
                                    <span class="comment-date">
                                        <?php echo formatDate($comment['created_at']); ?>
                                    </span>
                                    <?php if (isAdmin()): ?>
                                        <a href="?id=<?php echo $postId; ?>&delete_comment=<?php echo $comment['id']; ?>" 
                                           class="delete-comment" 
                                           onclick="return confirm('Are you sure you want to delete this comment?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="comment-content">
                                <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Related Posts -->
        <?php if (!empty($relatedPosts)): ?>
            <section class="related-posts">
                <h3>
                    <i class="fas fa-link"></i> 
                    Related Posts
                </h3>
                <div class="related-grid">
                    <?php foreach ($relatedPosts as $related): ?>
                        <div class="related-card">
                            <div class="related-content">
                                <div class="related-meta">
                                    <span class="category-tag small"><?php echo htmlspecialchars($related['category']); ?></span>
                                    <span class="date"><?php echo formatDate($related['created_at']); ?></span>
                                </div>
                                <h4>
                                    <a href="post-detail.php?id=<?php echo $related['id']; ?>">
                                        <?php echo htmlspecialchars($related['title']); ?>
                                    </a>
                                </h4>
                                <p><?php echo htmlspecialchars(substr($related['content'], 0, 100)) . '...'; ?></p>
                                <div class="related-footer">
                                    <span class="author">By <?php echo htmlspecialchars($related['author_name']); ?></span>
                                    <span class="views"><i class="far fa-eye"></i> <?php echo $related['views']; ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</main>

<?php getFooter(); ?>