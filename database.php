<?php
// database.php - Database Functions (Complete Version)
require_once 'config.php';

// ========== POST FUNCTIONS ==========

// Get all posts (admin function)
function getAllPosts() {
    global $conn;
    $sql = "SELECT p.*, u.name as author_name 
            FROM posts p 
            LEFT JOIN users u ON p.author_id = u.id 
            ORDER BY p.created_at DESC";
    
    $result = mysqli_query($conn, $sql);
    $posts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    return $posts;
}

// Get statistics for admin dashboard
function getStatistics() {
    global $conn;
    $stats = [
        'total_posts' => 0,
        'pending_posts' => 0,
        'approved_posts' => 0,
        'rejected_posts' => 0,
        'total_views' => 0
    ];
    
    // Get counts
    $sql = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(views) as total_views
        FROM posts";
    
    $result = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($result)) {
        $stats['total_posts'] = $row['total'] ?? 0;
        $stats['pending_posts'] = $row['pending'] ?? 0;
        $stats['approved_posts'] = $row['approved'] ?? 0;
        $stats['rejected_posts'] = $row['rejected'] ?? 0;
        $stats['total_views'] = $row['total_views'] ?? 0;
    }
    
    return $stats;
}

// Update post status (approve/reject)
function updatePostStatus($postId, $status) {
    global $conn;
    $sql = "UPDATE posts SET status = ?, updated_at = NOW() WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $postId);
    return mysqli_stmt_execute($stmt);
}

// Delete post (Admin function)
function deletePost($postId) {
    global $conn;
    
    // First delete associated bookmarks
    $sql = "DELETE FROM bookmarks WHERE post_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $postId);
    mysqli_stmt_execute($stmt);

    // Then delete associated comments
    $sql = "DELETE FROM comments WHERE post_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $postId);
    mysqli_stmt_execute($stmt);
    
    // Finally delete the post
    $sql = "DELETE FROM posts WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $postId);
    return mysqli_stmt_execute($stmt);
}

// Get post by ID
function getPostById($id) {
    global $conn;
    $sql = "SELECT p.*, u.name as author_name, u.role as author_role 
            FROM posts p 
            LEFT JOIN users u ON p.author_id = u.id 
            WHERE p.id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Create post (FIXED VERSION)
// Create post (FIXED VERSION)
function createPost($title, $content, $category, $authorId, $imageUrl = null, $videoUrl = null) {
    global $conn;
    
    // Start session if not started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Determine status based on user role
    $status = 'pending';
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        $status = 'approved';
    }
    
    $sql = "INSERT INTO posts (title, content, category, author_id, image_url, video_url, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssisss", $title, $content, $category, $authorId, $imageUrl, $videoUrl, $status);
    
    if (mysqli_stmt_execute($stmt)) {
        $postId = mysqli_insert_id($conn);
        
        $_SESSION['message'] = 'Post created successfully!' . 
                              ($status === 'pending' ? ' It will be reviewed by admin.' : '');
        $_SESSION['message_type'] = 'success';
        return $postId;
    } else {
        $_SESSION['message'] = 'Error creating post: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'danger';
        return false;
    }
}

// Get all posts with optional status filter
function getPosts($limit = 10, $status = 'approved') {
    global $conn;
    $sql = "SELECT p.*, u.name as author_name 
            FROM posts p 
            LEFT JOIN users u ON p.author_id = u.id 
            WHERE p.status = ? 
            ORDER BY p.created_at DESC 
            LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $posts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    return $posts;
}

// Get posts by category
function getPostsByCategory($category, $limit = 10) {
    global $conn;
    $status = 'approved';
    $sql = "SELECT p.*, u.name as author_name 
            FROM posts p 
            LEFT JOIN users u ON p.author_id = u.id 
            WHERE p.status = ? AND p.category = ?
            ORDER BY p.created_at DESC 
            LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", $status, $category, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $posts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    return $posts;
}

// Search Posts
function searchPosts($query) {
    global $conn;
    $searchTerm = "%" . $query . "%";
    $status = 'approved';
    
    $sql = "SELECT p.*, u.name as author_name 
            FROM posts p 
            LEFT JOIN users u ON p.author_id = u.id 
            WHERE p.status = ? AND (p.title LIKE ? OR p.content LIKE ? OR p.category LIKE ?)
            ORDER BY p.created_at DESC";
            
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $status, $searchTerm, $searchTerm, $searchTerm);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $posts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    return $posts;
}

// Get user's posts
function getUserPosts($userId) {
    global $conn;
    $sql = "SELECT p.*, u.name as author_name 
            FROM posts p 
            LEFT JOIN users u ON p.author_id = u.id 
            WHERE p.author_id = ? 
            ORDER BY p.created_at DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $posts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    return $posts;
}

// Get user statistics
function getUserStatistics($userId) {
    global $conn;
    $stats = [
        'user_posts' => 0,
        'approved_posts' => 0,
        'pending_posts' => 0,
        'user_views' => 0
    ];
    
    // Total posts
    $sql = "SELECT COUNT(*) as count FROM posts WHERE author_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $stats['user_posts'] = $row['count'] ?? 0;
    
    // Approved posts
    $sql = "SELECT COUNT(*) as count FROM posts WHERE author_id = ? AND status = 'approved'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $stats['approved_posts'] = $row['count'] ?? 0;
    
    // Pending posts
    $sql = "SELECT COUNT(*) as count FROM posts WHERE author_id = ? AND status = 'pending'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $stats['pending_posts'] = $row['count'] ?? 0;
    
    // Total views
    $sql = "SELECT SUM(views) as total FROM posts WHERE author_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $stats['user_views'] = $row['total'] ?? 0;
    
    return $stats;
}

// Get trending posts
function getTrendingPosts($limit = 6) {
    global $conn;
    $sql = "SELECT p.*, u.name as author_name 
            FROM posts p 
            LEFT JOIN users u ON p.author_id = u.id 
            WHERE p.status = 'approved' 
            ORDER BY p.views DESC 
            LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $posts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    return $posts;
}

// Format date
function formatDate($dateString) {
    $date = new DateTime($dateString);
    $now = new DateTime();
    $interval = $date->diff($now);
    
    if ($interval->y > 0) return $interval->y . ' year(s) ago';
    if ($interval->m > 0) return $interval->m . ' month(s) ago';
    if ($interval->d > 0) {
        if ($interval->d == 1) return 'Yesterday';
        if ($interval->d < 7) return $interval->d . ' days ago';
        return $date->format('M j, Y');
    }
    if ($interval->h > 0) return $interval->h . ' hour(s) ago';
    if ($interval->i > 0) return $interval->i . ' minute(s) ago';
    return 'Just now';
}

// Get user by ID
function getUserById($userId) {
    global $conn;
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Get user by email
function getUserByEmail($email) {
    global $conn;
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Register new user
function registerUser($name, $email, $password, $role = 'student') {
    global $conn;
    
    // Check if email already exists
    if (getUserByEmail($email)) {
        return ['success' => false, 'message' => 'Email already registered'];
    }
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $status = 'active'; 
    
    $sql = "INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $hashedPassword, $role, $status);
    
    if (mysqli_stmt_execute($stmt)) {
        return ['success' => true, 'user_id' => mysqli_insert_id($conn)];
    } else {
        return ['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)];
    }
}

// Update user profile
function updateUser($userId, $name, $password = null) {
    global $conn;
    
    if ($password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET name = ?, password = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $name, $hashedPassword, $userId);
    } else {
        $sql = "UPDATE users SET name = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $name, $userId);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        // Update session name if successful
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
            $_SESSION['user_name'] = $name;
        }
        return true;
    }
    return false;
}

// Get all users (admin only)
function getAllUsers() {
    global $conn;
    $sql = "SELECT id, name, email, role, status, created_at FROM users ORDER BY created_at DESC";
    $result = mysqli_query($conn, $sql);
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    return $users;
}

// ========== DELETE FUNCTIONS ==========

// Delete post
// function deletePost removed (replaced by improved version at top of file)

// ========== MISC FUNCTIONS ==========

// Initialize database with sample data if empty
function initializeDatabase() {
    global $conn;
    
    // Check if posts table has data
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM posts");
    $row = mysqli_fetch_assoc($result);
    
    if ($row['count'] == 0) {
        // Insert sample posts
        $samplePosts = [
            ['IUB Launches New Computer Science Program', 'The Computer Science department has launched a new program focusing on AI and Machine Learning.', 'Academics', 1, null, 'approved', 1250],
            ['Annual Sports Fest 2024', 'Registration is now open for the annual sports festival.', 'Sports', 2, null, 'approved', 980],
            ['Research Symposium Announcement', 'Join us for a research symposium on climate change.', 'Research', 1, null, 'approved', 750],
            ['Campus WiFi Upgrade', 'Enjoy faster internet across campus with our new WiFi network.', 'Announcements', 1, null, 'approved', 1120],
            ['Student Council Election Results', 'Results for the 2024 student council elections announced.', 'Student Life', 3, null, 'approved', 890],
            ['Library Extended Hours', 'Library will remain open until 10 PM during exams.', 'Announcements', 1, null, 'pending', 0],
            ['Workshop on Entrepreneurship', 'Learn how to start your own startup.', 'Events', 2, null, 'pending', 0]
        ];
        
        foreach ($samplePosts as $post) {
            $sql = "INSERT INTO posts (title, content, category, author_id, image_url, status, views, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 30) DAY))";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssissi", $post[0], $post[1], $post[2], $post[3], $post[4], $post[5], $post[6]);
            mysqli_stmt_execute($stmt);
        }
        
        return true;
    }
    
    return false;
}

// Initialize database on first run (uncomment to use)
// initializeDatabase();

// ========== COMMENT FUNCTIONS ==========

// Add a comment
function addComment($postId, $userId, $content) {
    global $conn;
    $sql = "INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iis", $postId, $userId, $content);
    return mysqli_stmt_execute($stmt);
}

// Get comments for a post
function getComments($postId) {
    global $conn;
    $sql = "SELECT c.*, u.name as user_name, u.role as user_role 
            FROM comments c 
            LEFT JOIN users u ON c.user_id = u.id 
            WHERE c.post_id = ? 
            ORDER BY c.created_at DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $postId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $comments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $comments[] = $row;
    }
    return $comments;
}

// Delete a comment (admin only)
function deleteComment($commentId) {
    global $conn;
    $sql = "DELETE FROM comments WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $commentId);
    return mysqli_stmt_execute($stmt);
}

// Get related posts
function getRelatedPosts($currentPostId, $category, $limit = 3) {
    global $conn;
    $sql = "SELECT p.*, u.name as author_name 
            FROM posts p 
            LEFT JOIN users u ON p.author_id = u.id 
            WHERE p.category = ? AND p.id != ? AND p.status = 'approved'
            ORDER BY p.created_at DESC 
            LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sii", $category, $currentPostId, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $posts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    return $posts;
}

// ========== BOOKMARK FUNCTIONS ==========

// Toggle bookmark
function toggleBookmark($userId, $postId) {
    global $conn;
    
    // Check if already bookmarked
    $sql = "SELECT id FROM bookmarks WHERE user_id = ? AND post_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $userId, $postId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        // Remove bookmark
        $sql = "DELETE FROM bookmarks WHERE user_id = ? AND post_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $userId, $postId);
        mysqli_stmt_execute($stmt);
        return false; // Not bookmarked anymore
    } else {
        // Add bookmark
        $sql = "INSERT INTO bookmarks (user_id, post_id, created_at) VALUES (?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $userId, $postId);
        mysqli_stmt_execute($stmt);
        return true; // Bookmarked
    }
}

// Check if bookmarked
function isBookmarked($userId, $postId) {
    global $conn;
    $sql = "SELECT id FROM bookmarks WHERE user_id = ? AND post_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $userId, $postId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_num_rows($result) > 0;
}

// Get user bookmarks
function getUserBookmarks($userId) {
    global $conn;
    $sql = "SELECT p.*, u.name as author_name, b.created_at as bookmarked_at
            FROM bookmarks b
            JOIN posts p ON b.post_id = p.id
            LEFT JOIN users u ON p.author_id = u.id
            WHERE b.user_id = ?
            ORDER BY b.created_at DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $bookmarks = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $bookmarks[] = $row;
    }
    return $bookmarks;
}

// ========== EVENT FUNCTIONS ==========

// Get all events
function getAllEvents($limit = 50) {
    global $conn;
    $sql = "SELECT * FROM events ORDER BY event_date ASC LIMIT ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $events = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $events[] = $row;
    }
    return $events;
}

// Get upcoming events
function getUpcomingEvents($limit = 5) {
    global $conn;
    $sql = "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $events = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $events[] = $row;
    }
    return $events;
}

// Add event
function addEvent($title, $description, $date, $location) {
    global $conn;
    $sql = "INSERT INTO events (title, description, event_date, location, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $title, $description, $date, $location);
    return mysqli_stmt_execute($stmt);
}

// Delete event
function deleteEvent($id) {
    global $conn;
    $sql = "DELETE FROM events WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    return mysqli_stmt_execute($stmt);
}

// ========== TEMPLATE FUNCTIONS ==========
// ... existing template functions ...
// Template Functions
function getHeader($title = '') {
    global $pageTitle;
    if (!empty($title)) {
        $pageTitle = $title;
    }
    
    // Output standard HTML Head
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($pageTitle ?? 'IUB News Portal'); ?></title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <!-- Preload Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Oswald:wght@400;500;600&family=Playfair+Display:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        
        <!-- Main Styles -->
        <link rel="stylesheet" href="styles.css">
        <link rel="stylesheet" href="news_styles.css">
    </head>
    <body class="bg-white text-gray-900">
    <?php
    include 'header.php';
}

// Function to get footer
function getFooter() {
    include 'footer.php';
}
?>