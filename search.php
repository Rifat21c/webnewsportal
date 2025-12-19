<?php
// search.php - Search Results Page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'database.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['cat']) ? trim($_GET['cat']) : '';
$searchResults = [];

if (!empty($query)) {
    $searchResults = searchPosts($query);
    $pageTitle = "Search: " . htmlspecialchars($query);
} elseif (!empty($category)) {
    $searchResults = getPostsByCategory($category, 20);
    $pageTitle = "Category: " . htmlspecialchars($category);
} else {
    $pageTitle = "Search - IUB News Portal";
}
getHeader($pageTitle);
?>

<main class="container main-content-area">
    <div class="search-header-block" style="margin-bottom: 3rem; border-bottom: 1px solid var(--border-light); padding-bottom: 2rem;">
        <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">
            <?php echo !empty($category) ? htmlspecialchars($category) : "Search Results"; ?>
        </h1>
        <form action="search.php" method="GET" class="search-page-form" style="display: flex; gap: 10px; max-width: 600px;">
            <input type="text" name="q" value="<?php echo htmlspecialchars($query); ?>" 
                   placeholder="Search news, topics, and more..." 
                   class="form-control" style="font-size: 1.1rem; padding: 12px;">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <?php if (empty($query) && empty($category)): ?>
        <div class="empty-state">
            <i class="fas fa-search" style="font-size: 3rem; color: var(--border-strong); margin-bottom: 1rem;"></i>
            <h3>Enter a keyword to search</h3>
        </div>
    <?php elseif (empty($searchResults)): ?>
        <div class="empty-state">
            <i class="far fa-frown" style="font-size: 3rem; color: var(--border-strong); margin-bottom: 1rem;"></i>
            <h3>No results found for "<?php echo htmlspecialchars($query); ?>"</h3>
            <p>Try checking your spelling or use different keywords.</p>
        </div>
    <?php else: ?>
        <div class="search-results-count" style="margin-bottom: 2rem; color: var(--text-secondary); font-family: 'Oswald'; text-transform: uppercase;">
            Found <?php echo count($searchResults); ?> stories
        </div>

        <div class="news-list-view">
            <?php foreach ($searchResults as $post): ?>
                <div class="news-card-horizontal">
                    <div class="news-horizontal-image">
                        <img src="<?php echo $post['image_url'] ?: 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'; ?>" 
                             alt="<?php echo htmlspecialchars($post['title']); ?>" loading="lazy">
                    </div>
                    <div class="news-horizontal-content">
                        <span class="category-tag-small" style="color: var(--brand-red); font-weight: 700; font-size: 0.8rem; text-transform: uppercase; display: block; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($post['category']); ?></span>
                        <h3 class="news-horizontal-title">
                            <a href="post-detail.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a>
                        </h3>
                        <div class="news-excerpt">
                            <?php echo htmlspecialchars(substr(strip_tags($post['content']), 0, 150)) . '...'; ?>
                        </div>
                        <div class="news-meta-item">
                            <?php echo formatDate($post['created_at']); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php getFooter(); ?>
