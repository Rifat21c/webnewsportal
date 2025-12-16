<?php
// dashboard.php - Professional User Dashboard
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';
require_once 'database.php';

// Auth Check
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user = [
    'id' => $_SESSION['user_id'],
    'name' => $_SESSION['user_name'],
    'email' => $_SESSION['user_email'],
    'role' => $_SESSION['user_role']
];

$pageTitle = 'Dashboard';
$userStats = getUserStatistics($user['id']);
$userPosts = getUserPosts($user['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - IUB News</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Dashboard Specific Styles matching new Theme */
        .dashboard-container {
            margin-top: 2rem;
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 3rem;
        }

        /* Sidebar Profile */
        .profile-sidebar {
            background: white;
            border: 1px solid var(--border-light);
            padding: 2rem;
            text-align: center;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: var(--brand-gray);
            border-radius: 50%;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: var(--text-secondary);
        }
        .profile-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
            font-weight: 700;
        }
        .profile-role {
            font-family: 'Oswald', sans-serif;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
            background: var(--brand-dark);
            color: white;
            padding: 4px 10px;
            display: inline-block;
            margin-bottom: 1.5rem;
        }
        .profile-nav a {
            display: block;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-light);
            color: var(--text-secondary);
            font-weight: 500;
            text-align: left;
        }
        .profile-nav a:hover {
            color: var(--brand-red);
            padding-left: 5px; /* slight movement */
        }
        .profile-nav a:last-child { border: none; }

        /* Stats Row */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        .stat-item {
            border-left: 4px solid var(--brand-dark); /* Editorial Line */
            padding-left: 1rem;
        }
        .stat-value {
            font-family: 'Oswald', sans-serif;
            font-size: 2.5rem;
            font-weight: 500;
            line-height: 1;
            margin-bottom: 0.25rem;
        }
        .stat-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            color: var(--text-secondary);
            letter-spacing: 0.5px;
        }

        /* Content Area */
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-bottom: 2px solid var(--brand-dark);
            padding-bottom: 1rem;
        }
        .content-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            margin: 0;
        }

        /* Modern Table */
        .table-editorial {
            width: 100%;
            border-collapse: collapse;
        }
        .table-editorial th {
            text-align: left;
            font-family: 'Oswald', sans-serif;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
            border-bottom: 2px solid var(--border-light);
            padding: 1rem 0;
            color: var(--text-secondary);
        }
        .table-editorial td {
            padding: 1.5rem 0;
            border-bottom: 1px solid var(--border-light);
            font-size: 0.95rem;
        }
        .status-dot {
            height: 8px; width: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }
        .status-approved { background-color: #16a34a; }
        .status-pending { background-color: #ca8a04; }
        .status-rejected { background-color: #dc2626; }

        @media (max-width: 900px) {
            .dashboard-container { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
    <?php require_once 'header.php'; ?>

    <main class="container dashboard-container">
        <!-- Sidebar -->
        <aside class="profile-sidebar">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
            </div>
            <h2 class="profile-name"><?php echo htmlspecialchars($user['name']); ?></h2>
            <div class="profile-role"><?php echo htmlspecialchars($user['role']); ?></div>
            
            <nav class="profile-nav">
                <a href="create-post.php"><i class="fas fa-pen-nib"></i> Write New Story</a>
                <a href="profile.php"><i class="fas fa-cog"></i> Account Settings</a>
                <?php if ($user['role'] === 'admin'): ?>
                    <a href="admin-dashboard.php" style="color: var(--brand-red);"><i class="fas fa-crown"></i> Admin Control</a>
                <?php endif; ?>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="dashboard-content">
            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value"><?php echo $userStats['user_posts']; ?></div>
                    <div class="stat-label">Total Stories</div>
                </div>
                <div class="stat-item" style="border-color: #16a34a;">
                    <div class="stat-value"><?php echo $userStats['approved_posts']; ?></div>
                    <div class="stat-label">Published</div>
                </div>
                <div class="stat-item" style="border-color: #ca8a04;">
                    <div class="stat-value"><?php echo $userStats['pending_posts']; ?></div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-item" style="border-color: var(--text-secondary);">
                    <div class="stat-value"><?php echo $userStats['user_views']; ?></div>
                    <div class="stat-label">Total Reads</div>
                </div>
            </div>

            <!-- Posts List -->
            <div class="content-header">
                <h3 class="content-title">My Stories</h3>
                <a href="create-post.php" class="btn btn-primary">New Story</a>
            </div>

            <?php if (empty($userPosts)): ?>
                <div style="text-align: center; padding: 4rem; background: var(--brand-gray);">
                    <i class="fas fa-newspaper" style="font-size: 3rem; color: var(--border-strong); margin-bottom: 1rem;"></i>
                    <h3>No stories found</h3>
                    <p style="color: var(--text-secondary);">Start your journey as a reporter today.</p>
                </div>
            <?php else: ?>
                <table class="table-editorial">
                    <thead>
                        <tr>
                            <th width="50%">Headline</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Reach</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($userPosts as $post): ?>
                        <tr>
                            <td>
                                <a href="post-detail.php?id=<?php echo $post['id']; ?>" style="font-family: 'Playfair Display', serif; font-weight: 700; font-size: 1.1rem; color: var(--brand-dark);">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                                <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 4px;">
                                    <?php echo htmlspecialchars(substr(strip_tags($post['category']), 0, 50)); ?>
                                </div>
                            </td>
                            <td>
                                <span class="status-dot status-<?php echo $post['status']; ?>"></span>
                                <span style="text-transform: uppercase; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary);">
                                    <?php echo $post['status']; ?>
                                </span>
                            </td>
                            <td style="font-family: 'Oswald', sans-serif; color: var(--text-secondary);">
                                <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                            </td>
                            <td style="font-weight: 600;">
                                <?php echo $post['views']; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>

    <footer style="margin-top: 4rem; padding: 2rem; text-align: center; border-top: 1px solid var(--border-light); font-size: 0.85rem; color: var(--text-secondary);">
        &copy; 2025 Independent University, Bangladesh.
    </footer>
</body>
</html>