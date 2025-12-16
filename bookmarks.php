<?php
// bookmarks.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';
require_once 'database.php';

// Check login
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$pageTitle = 'My Bookmarks';
getHeader($pageTitle);

$bookmarks = getUserBookmarks($_SESSION['user_id']);
?>

<main class="container page-content">
    <div class="page-header d-flex justify-between align-center mb-4">
        <div>
            <h1><i class="fas fa-bookmark text-primary"></i> My Bookmarks</h1>
            <p class="text-muted">Posts you have saved for later.</p>
        </div>
        <div class="header-actions">
            <a href="index.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>

    <?php if (empty($bookmarks)): ?>
        <div class="card p-5 text-center empty-state">
            <div class="text-muted mb-3"><i class="far fa-bookmark fa-3x"></i></div>
            <h3>No bookmarks yet</h3>
            <p class="mb-4">When you see a post you like, click the bookmark icon to save it here.</p>
            <a href="index.php" class="btn btn-primary">Find Posts to Read</a>
        </div>
    <?php else: ?>
        <div class="posts-grid">
            <?php foreach ($bookmarks as $post): ?>
                <div class="card card-hover h-100">
                    <div class="card-image-wrapper">
                        <span class="card-category"><?php echo htmlspecialchars($post['category']); ?></span>
                        <img src="<?php echo $post['image_url'] ?: 'https://images.unsplash.com/photo-1588681664899-f142ff2dc9b1?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'; ?>" 
                             alt="<?php echo htmlspecialchars($post['title']); ?>" loading="lazy">
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">
                            <a href="post-detail.php?id=<?php echo $post['id']; ?>">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </h3>
                        
                        <div class="card-meta">
                            <div class="card-meta-item">
                                <i class="fas fa-user-circle"></i> 
                                <?php echo htmlspecialchars($post['author_name']); ?>
                            </div>
                            <div class="card-meta-item">
                                <i class="fas fa-clock"></i> 
                                Bookmarked <?php echo formatDate($post['bookmarked_at']); ?>
                            </div>
                        </div>
                        
                        <div class="card-actions mt-3">
                            <button onclick="toggleBookmark(<?php echo $post['id']; ?>); this.closest('.card').remove();" class="btn btn-sm btn-outline text-danger">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<script>
    // Re-use toggle logic but optimized for list removal
    // script.js is loaded in header
</script>

<?php getFooter(); ?>
