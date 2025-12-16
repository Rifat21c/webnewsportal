<?php
// admin-dashboard.php - Professional Admin Control Room
require_once 'database.php';
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin Check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['message'] = 'Access denied. Editorial privileges required.';
    $_SESSION['message_type'] = 'danger';
    header('Location: login.php');
    exit();
}

$pageTitle = 'Editorial Dashboard';
$allPosts = getAllPosts();
$stats = getStatistics();

// Metrics
$pendingCount = 0; $approvedCount = 0; $rejectedCount = 0;
foreach ($allPosts as $post) {
    if ($post['status'] === 'pending') $pendingCount++;
    elseif ($post['status'] === 'approved') $approvedCount++;
    elseif ($post['status'] === 'rejected') $rejectedCount++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editorial Control - IUB News</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Admin Specific Styling */
        .admin-layout {
            margin-top: 2rem;
            display: grid;
            grid-template-columns: 240px 1fr;
            gap: 2rem;
        }
        
        /* Stats Ribbon */
        .stats-ribbon {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1px;
            background: var(--border-light);
            margin-bottom: 2rem;
            border: 1px solid var(--border-light);
        }
        .ribbon-item {
            background: white;
            padding: 1.5rem;
            text-align: center;
        }
        .ribbon-val {
            font-family: 'Oswald', sans-serif;
            font-size: 2.5rem;
            font-weight: 500;
        }
        .ribbon-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-secondary);
        }
        
        /* Tabs as Navigation */
        .admin-tabs {
            border-bottom: 2px solid var(--brand-dark);
            margin-bottom: 2rem;
        }
        .tab-btn {
            background: none;
            border: none;
            font-family: 'Oswald', sans-serif;
            font-size: 1rem;
            text-transform: uppercase;
            padding: 1rem 1.5rem;
            cursor: pointer;
            color: var(--text-secondary);
            font-weight: 500;
        }
        .tab-btn.active {
            color: var(--brand-dark);
            background: var(--brand-gray);
            font-weight: 700;
        }
        .tab-count {
            background: #e5e7eb;
            padding: 2px 6px;
            font-size: 0.75rem;
            border-radius: 4px;
            margin-left: 6px;
        }
        
        /* Table Styles */
        .table-admin {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }
        .table-admin th {
            text-align: left;
            padding: 1rem;
            background: var(--brand-gray);
            text-transform: uppercase;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .table-admin td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-light);
        }
        .table-admin tr:hover { background: #fafafa; }
        
        .post-title-cell {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--brand-dark);
            display: block;
        }
        .post-meta-cell {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        
        .btn-action {
            width: 32px; height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 5px;
            transition: all 0.2s;
        }
        .btn-approve { background: #dcfce7; color: #166534; }
        .btn-approve:hover { background: #166534; color: white; }
        
        .btn-reject { background: #fee2e2; color: #991b1b; }
        .btn-reject:hover { background: #991b1b; color: white; }
    </style>
</head>
<body>
    <?php require_once 'header.php'; ?>

    <main class="container admin-layout">
        <!-- Sidebar Navigation -->
        <aside>
            <div style="margin-bottom: 2rem;">
                <h4 style="font-family: 'Oswald'; text-transform: uppercase; margin-bottom: 1rem;">Editorial Tools</h4>
                <a href="#" class="btn btn-primary" style="width: 100%; text-align: center;">New Article</a>
            </div>
            
            <div class="stat-item" style="margin-bottom: 1rem;">
                <div style="font-size: 0.8rem; color: var(--text-secondary);">LOGGED IN AS</div>
                <div style="font-weight: 700;"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
            </div>
            
            <hr style="border: 0; border-top: 1px solid var(--border-light); margin: 1.5rem 0;">
            
            <a href="dashboard.php" style="display: block; margin-bottom: 0.5rem; color: var(--text-secondary);"><i class="fas fa-arrow-left"></i> User Dashboard</a>
            <a href="logout.php" style="display: block; color: var(--text-secondary);"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
        </aside>

        <!-- Main Workspace -->
        <div>
            <!-- Header -->
            <div style="margin-bottom: 2rem;">
                <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Editorial Dashboard</h1>
                <p style="color: var(--text-secondary);">Overview of publication activity and content moderation.</p>
            </div>

            <!-- Stats Ribbon -->
            <div class="stats-ribbon">
                <div class="ribbon-item">
                    <div class="ribbon-val"><?php echo $stats['total_posts']; ?></div>
                    <div class="ribbon-label">Total Articles</div>
                </div>
                <div class="ribbon-item">
                    <div class="ribbon-val" style="color: #ca8a04;"><?php echo $stats['pending_posts']; ?></div>
                    <div class="ribbon-label">Pending Review</div>
                </div>
                <div class="ribbon-item">
                    <div class="ribbon-val" style="color: #16a34a;"><?php echo $stats['approved_posts']; ?></div>
                    <div class="ribbon-label">Published</div>
                </div>
                <div class="ribbon-item">
                    <div class="ribbon-val" style="color: #dc2626;"><?php echo $stats['rejected_posts']; ?></div>
                    <div class="ribbon-label">Rejected</div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="admin-tabs">
                <button class="tab-btn active" onclick="switchTab('pending')">Pending <span class="tab-count"><?php echo $pendingCount; ?></span></button>
                <button class="tab-btn" onclick="switchTab('approved')">Published <span class="tab-count"><?php echo $approvedCount; ?></span></button>
                <button class="tab-btn" onclick="switchTab('rejected')">Rejected</button>
                <button class="tab-btn" onclick="switchTab('all')">All Archives</button>
            </div>

            <!-- Content Panes -->
            <div id="pending" class="tab-pane">
                <!-- Using a function or just standard PHP to list posts -->
                <table class="table-admin">
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th>Author</th>
                            <th>Submitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $hasPending = false;
                        foreach ($allPosts as $post): 
                            if($post['status'] !== 'pending') continue;
                            $hasPending = true;
                        ?>
                        <tr>
                            <td>
                                <a href="post-detail.php?id=<?php echo $post['id']; ?>" class="post-title-cell"><?php echo htmlspecialchars($post['title']); ?></a>
                                <span class="category-cat"><?php echo htmlspecialchars($post['category']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                            <td><?php echo date('M d, H:i', strtotime($post['created_at'])); ?></td>
                            <td>
                                <a href="approve-post.php?id=<?php echo $post['id']; ?>" class="btn-action btn-approve" title="Approve"><i class="fas fa-check"></i></a>
                                <a href="reject-post.php?id=<?php echo $post['id']; ?>" class="btn-action btn-reject" title="Reject"><i class="fas fa-times"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(!$hasPending): ?>
                            <tr><td colspan="4" style="text-align:center; padding: 2rem;">No pending articles. Good job!</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Approved Pane (Hidden by Default) -->
            <div id="approved" class="tab-pane" style="display: none;">
                 <table class="table-admin">
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th>Author</th>
                            <th>Published</th>
                            <th>Views</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allPosts as $post): 
                            if($post['status'] !== 'approved') continue;
                        ?>
                        <tr>
                            <td>
                                <a href="post-detail.php?id=<?php echo $post['id']; ?>" class="post-title-cell"><?php echo htmlspecialchars($post['title']); ?></a>
                            </td>
                            <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                            <td><?php echo date('M d', strtotime($post['created_at'])); ?></td>
                            <td><?php echo $post['views']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div id="rejected" class="tab-pane" style="display: none;">
                <p>Rejected posts archive...</p>
                 <table class="table-admin">
                    <tbody>
                     <?php foreach ($allPosts as $post): 
                            if($post['status'] !== 'rejected') continue;
                        ?>
                        <tr>
                             <td>
                                <a href="post-detail.php?id=<?php echo $post['id']; ?>" class="post-title-cell"><?php echo htmlspecialchars($post['title']); ?></a>
                            </td>
                            <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                             <td>
                                <a href="approve-post.php?id=<?php echo $post['id']; ?>" class="btn-action btn-approve" title="Re-Approve"><i class="fas fa-undo"></i></a>
                             </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                 </table>
            </div>

             <div id="all" class="tab-pane" style="display: none;">
                <!-- Full list logic identical to above but all -->
                 <p>Full archives...</p>
            </div>

        </div>
    </main>
    
    <script>
        function switchTab(tabName) {
            // Hide all
            document.querySelectorAll('.tab-pane').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            
            // Show selected
            document.getElementById(tabName).style.display = 'block';
            event.currentTarget.classList.add('active');
        }
    </script>
</body>
</html>