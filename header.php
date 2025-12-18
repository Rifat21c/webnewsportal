<?php
// header.php - Professional News Portal Header
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isLoggedIn();
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
?>
<!-- Styles are assumed to be loaded by the parent page or we can include them here if strict -->
<!-- Professional News Header -->
<header class="site-header">
    <!-- Top Bar: Date & Socials -->
    <div class="header-main">
        <div class="container header-container">
            <a href="index.php" class="brand-logo">
                <img src="uploads/iub_logo.png" alt="IUB Logo" class="logo-img">
                <div class="brand-text">
                    <h1>IUB News Portal</h1>
                    <span class="brand-tagline">The Voice of Independent University</span>
                </div>
            </a>
            <div class="header-actions">
                 <?php if ($isLoggedIn): ?>
                    <div class="user-greeting">Hello, <?php echo htmlspecialchars($userName); ?></div>
                    <a href="logout.php" class="btn btn-outline btn-sm">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary btn-sm">Sign In</a>
                    <a href="register.php" class="btn btn-outline btn-sm">Subscribe</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Navigation Bar (Black Strip) -->
    <nav class="main-navbar">
        <div class="container nav-content">
            <button class="mobile-menu-toggle">
                <i class="fas fa-bars"></i> Menu
            </button>

            <div class="nav-links">
                <a href="index.php" class="nav-item">Home</a>
                <a href="index.php#trending" class="nav-item">Trending</a>
                <a href="search.php?cat=Academics" class="nav-item">Academics</a>
                <a href="search.php?cat=Student Life" class="nav-item">Student Life</a>
                <a href="search.php?cat=Events" class="nav-item">Events</a>
                <a href="search.php?cat=Sports" class="nav-item">Sports</a>
                
                <?php if ($isLoggedIn): ?>
                    <div class="nav-separator"></div>
                    <a href="dashboard.php" class="nav-item">Dashboard</a>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <a href="admin-dashboard.php" class="nav-item text-warning">Admin Panel</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="nav-search-container">
                <form action="search.php" method="GET" class="search-form-inline" style="display: flex; align-items: center; border: 1px solid var(--border-light); padding: 5px 10px; border-radius: 20px;">
                    <input type="text" name="q" placeholder="Search..." style="border: none; outline: none; font-size: 0.9rem; width: 150px;">
                    <button type="submit" style="background: none; border: none; cursor: pointer; color: var(--brand-dark);"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>
    </nav>
</header>
<script>
    // Simple Mobile Menu Logic if not already present
    if(document.querySelector('.mobile-menu-toggle')) {
        document.querySelector('.mobile-menu-toggle').addEventListener('click', function() {
            document.querySelector('.nav-links').classList.toggle('active');
        });
    }
</script>