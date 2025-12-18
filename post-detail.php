<?php
// post-detail.php - Professional CNN/BBC Style
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'database.php';

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
        $commentText = trim($_POST['comment'] ?? '');
        
        if (empty($commentText)) {
            $_SESSION['message'] = 'Comment cannot be empty.';
            $_SESSION['message_type'] = 'error';
        } else {
            $userId = $_SESSION['user_id'];
            if (addComment($postId, $userId, $commentText)) {
                $_SESSION['message'] = 'Comment added successfully!';
                $_SESSION['message_type'] = 'success';
                header("Location: post-detail.php?id=$postId#comments");
                exit();
            }
        }
    }
}

// Get Data for Sidebar
$trendingPosts = getTrendingPosts(5);
$academicsPosts = getPostsByCategory('Academics', 3);
$comments = getComments($postId);

$pageTitle = htmlspecialchars($post['title']);
getHeader($pageTitle);
?>

<main class="container">
    <div class="article-container">
        <!-- Main Article Column -->
        <article class="article-main">
            <header class="article-header">
                <span class="article-category"><?php echo htmlspecialchars($post['category']); ?></span>
                <h1 class="article-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                
                <div class="article-meta">
                    <div class="author-block">
                        <div style="width: 40px; height: 40px; background: #eee; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                            <?php echo substr($post['author_name'], 0, 1); ?>
                        </div>
                        <div>
                            <div class="author-name-text">By <?php echo htmlspecialchars($post['author_name']); ?></div>
                            <div style="font-size: 0.8rem; opacity: 0.8;"><?php echo ucfirst($post['author_role']); ?></div>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div>Published <?php echo formatDate($post['created_at']); ?></div>
                        <div style="font-size: 0.85rem;"><i class="far fa-eye"></i> <?php echo $post['views']; ?> views</div>
                    </div>
                </div>
            </header>

            <!-- Media Section -->
            <div class="article-media">
                <?php if (!empty($post['video_url'])): ?>
                    <video controls style="width: 100%; border-radius: 4px;">
                        <source src="<?php echo htmlspecialchars($post['video_url']); ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                <?php elseif (!empty($post['image_url'])): ?>
                    <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                <?php endif; ?>
            </div>

            <!-- Content Section -->
            <div class="article-body">
                <?php echo $post['content']; // HTML allowed from Quill editor ?>
            </div>

            <!-- Bookmark Action -->
            <?php if (isLoggedIn()): 
                $isBookmarked = isBookmarked($_SESSION['user_id'], $post['id']);
            ?>
                <div style="margin-top: 3rem; padding: 2rem; background: var(--brand-gray); border-radius: 8px; text-align: center;">
                    <button class="btn <?php echo $isBookmarked ? 'btn-primary' : 'btn-outline'; ?>" onclick="toggleBookmark(<?php echo $post['id']; ?>)">
                        <i class="<?php echo $isBookmarked ? 'fas' : 'far'; ?> fa-bookmark"></i>
                        <?php echo $isBookmarked ? 'Saved to Bookmarks' : 'Save for Later'; ?>
                    </button>
                    <script>
                    function toggleBookmark(postId) {
                        fetch('toggle-bookmark.php?id=' + postId)
                        .then(response => response.json())
                        .then(data => {
                            if(data.success) {
                                location.reload();
                            }
                        });
                    }
                    </script>
                </div>
            <?php endif; ?>

            <!-- Comments -->
            <section class="comments-container" id="comments">
                <h2 class="sidebar-title">Conversation (<?php echo count($comments); ?>)</h2>
                
                <?php if (isLoggedIn()): ?>
                    <div class="comment-box">
                        <form method="POST">
                            <textarea name="comment" class="comment-input" placeholder="What are your thoughts?" required></textarea>
                            <button type="submit" name="add_comment" class="btn btn-primary">Post Comment</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="comment-box" style="text-align: center;">
                        <p><a href="login.php" style="color: var(--brand-red); font-weight: 700;">Sign In</a> to join the conversation.</p>
                    </div>
                <?php endif; ?>

                <div class="comments-list">
                    <?php if (empty($comments)): ?>
                        <p style="color: #888; text-align: center;">No comments yet. Start the conversation!</p>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-card">
                                <div>
                                    <span class="comment-user"><?php echo htmlspecialchars($comment['user_name']); ?></span>
                                    <span class="comment-date"><?php echo formatDate($comment['created_at']); ?></span>
                                </div>
                                <div class="comment-text">
                                    <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </article>

        <!-- Sidebar -->
        <aside class="article-sidebar">
            <div class="sidebar-section">
                <h3 class="sidebar-title">Trending Now</h3>
                <?php foreach ($trendingPosts as $tPost): ?>
                    <a href="post-detail.php?id=<?php echo $tPost['id']; ?>" class="sidebar-item">
                        <img src="<?php echo $tPost['image_url'] ?: 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=200'; ?>" class="sidebar-img">
                        <div class="sidebar-content">
                            <span style="color: var(--brand-red); font-size: 0.7rem; font-weight: 800; text-transform: uppercase;"><?php echo htmlspecialchars($tPost['category']); ?></span>
                            <h4><?php echo htmlspecialchars($tPost['title']); ?></h4>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="sidebar-section">
                <h3 class="sidebar-title">Higher Education</h3>
                <?php foreach ($academicsPosts as $aPost): ?>
                    <a href="post-detail.php?id=<?php echo $aPost['id']; ?>" class="sidebar-item">
                        <div class="sidebar-content">
                            <h4 style="margin-bottom: 5px;"><?php echo htmlspecialchars($aPost['title']); ?></h4>
                            <span class="comment-date"><?php echo formatDate($aPost['created_at']); ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <div style="background: var(--brand-gray); padding: 2rem; text-align: center; border-radius: 4px;">
                <h4 style="font-family: 'Oswald'; margin-bottom: 1rem;">GET THE NEWSLETTER</h4>
                <p style="font-size: 0.9rem; margin-bottom: 1rem;">The latest IUB stories delivered to your inbox.</p>
                <a href="register.php" class="btn btn-primary" style="width: 100%;">Sign Up</a>
            </div>
        </aside>
    </div>
</main>

<?php getFooter(); ?>