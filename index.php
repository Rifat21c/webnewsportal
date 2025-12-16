<?php
// index.php - Homepage
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';
require_once 'database.php';

$pageTitle = 'Home - IUB News Portal';
$isLoggedIn = isLoggedIn();
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

// Get trending posts (Fetch 4 for Hero section: 1 Main + 3 Side)
$trendingPosts = getTrendingPosts(4);
$trendingIds = array_column($trendingPosts, 'id');

// Get featured post (first trending) and side posts
$featuredPost = !empty($trendingPosts) ? $trendingPosts[0] : null;
$sidePosts = !empty($trendingPosts) ? array_slice($trendingPosts, 1) : [];

// Get recent posts (fetch more to allow for filtering)
$allRecentPosts = getPosts(12, 'approved');

// Filter out trending posts from recent to avoid duplicates
$recentPosts = array_filter($allRecentPosts, function($post) use ($trendingIds) {
    return !in_array($post['id'], $trendingIds);
});

// Take top 8 unique recent posts
$recentPosts = array_slice($recentPosts, 0, 8);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Oswald:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="news_styles.css">
</head>
<body>
    <?php require_once 'header.php'; ?>

    <main class="container main-content-area uppercase-headers">
        <!-- Welocme Alert -->
        <?php if ($isLoggedIn): ?>
        <div class="alert-box mb-4">
            <strong>Welcome back!</strong> You are logged in as <?php echo htmlspecialchars($_SESSION['user_role']); ?>.
        </div>
        <?php endif; ?>

        <!-- Hero Section (Trending) -->
        <section id="trending" class="section-block">
            <div class="section-header">
                <h2 class="section-title"><span class="title-icon"><i class="fas fa-bolt"></i></span> Top Stories</h2>
            </div>
            
            <?php if (!$featuredPost): ?>
                <div class="empty-state">
                    <div class="icon"><i class="fas fa-newspaper"></i></div>
                    <h3>No trending posts yet</h3>
                </div>
            <?php else: ?>
                <div class="news-hero-section">
                    <!-- Main Featured Story -->
                    <a href="post-detail.php?id=<?php echo $featuredPost['id']; ?>" class="news-hero-card">
                        <div class="hero-image">
                            <img src="<?php echo $featuredPost['image_url'] ?: 'https://images.unsplash.com/photo-1588681664899-f142ff2dc9b1?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'; ?>" 
                                 alt="<?php echo htmlspecialchars($featuredPost['title']); ?>" loading="lazy">
                        </div>
                        <div class="hero-overlay">
                            <span class="hero-category"><?php echo htmlspecialchars($featuredPost['category']); ?></span>
                            <h3 class="hero-title"><?php echo htmlspecialchars($featuredPost['title']); ?></h3>
                            <div class="hero-meta">
                                <span><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($featuredPost['author_name']); ?></span>
                                <span><i class="fas fa-clock"></i> <?php echo formatDate($featuredPost['created_at']); ?></span>
                            </div>
                        </div>
                    </a>

                    <!-- Side Stories -->
                    <div class="news-hero-side">
                        <?php foreach ($sidePosts as $post): ?>
                            <a href="post-detail.php?id=<?php echo $post['id']; ?>" class="small-hero-card">
                                <div class="small-hero-image">
                                    <img src="<?php echo $post['image_url'] ?: 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'; ?>" 
                                         alt="<?php echo htmlspecialchars($post['title']); ?>" loading="lazy">
                                </div>
                                <div class="small-hero-content">
                                    <span class="small-hero-category"><?php echo htmlspecialchars($post['category']); ?></span>
                                    <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                                    <div class="small-hero-meta">
                                        <i class="far fa-clock"></i> <?php echo formatDate($post['created_at']); ?>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </section>

        <!-- Recent News Section -->
        <section id="recent" class="section-block">
            <div class="section-header">
                <h2 class="section-title"><span class="title-icon"><i class="fas fa-clock"></i></span> Latest News</h2>
            </div>
            
            <?php if (empty($recentPosts)): ?>
                <div class="empty-state">
                    <h3>No recent updates</h3>
                </div>
            <?php else: ?>
                <div class="news-list-view">
                    <?php foreach ($recentPosts as $post): ?>
                        <a href="post-detail.php?id=<?php echo $post['id']; ?>" class="news-card-horizontal">
                            <div class="news-horizontal-image">
                                <img src="<?php echo $post['image_url'] ?: 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'; ?>" 
                                     alt="<?php echo htmlspecialchars($post['title']); ?>" loading="lazy">
                            </div>
                            <div class="news-horizontal-content">
                                <span class="news-category-tag"><?php echo htmlspecialchars($post['category']); ?></span>
                                <h3 class="news-horizontal-title">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </h3>
                                <p class="news-excerpt">
                                    <?php echo substr(strip_tags($post['content']), 0, 180) . '...'; ?>
                                </p>
                                <div class="news-meta">
                                    <div class="news-meta-item">
                                        <i class="fas fa-user-circle"></i> 
                                        <?php echo htmlspecialchars($post['author_name']); ?>
                                    </div>
                                    <div class="news-meta-item">
                                        <i class="fas fa-calendar-alt"></i> 
                                        <?php echo formatDate($post['created_at']); ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-info">
                    <div class="footer-logo">
                        <div class="logo-icon">IUB</div>
                        <div class="footer-logo-text">
                            <h3>IUB News Portal</h3>
                            <p>Independent University, Bangladesh</p>
                        </div>
                    </div>
                </div>
                
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <a href="index.php">Home</a>
                    <a href="#trending">Trending</a>
                    <a href="#recent">News</a>
                </div>
                
                <div class="footer-links">
                    <h4>Contact</h4>
                    <a href="mailto:info@iub.edu.bd">info@iub.edu.bd</a>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 Independent University, Bangladesh.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile Menu Toggle
        document.querySelector('.mobile-menu-toggle').addEventListener('click', function() {
            document.querySelector('.nav-links').classList.toggle('active');
        });
    </script>
</body>
</html>