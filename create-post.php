<?php
// create-post.php - FIXED VERSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';
require_once 'database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = 'Please login to create a post';
    $_SESSION['message_type'] = 'danger';
    header('Location: login.php');
    exit();
}

$user = [
    'id' => $_SESSION['user_id'],
    'name' => $_SESSION['user_name'],
    'email' => $_SESSION['user_email'],
    'role' => $_SESSION['user_role']
];

$pageTitle = 'Create Post';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_post'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? ''); // This will now contain HTML
    $category = $_POST['category'] ?? '';
    
    // Validate
    $errors = [];
    
    if (empty($title)) {
        $errors[] = 'Title is required';
    } elseif (strlen($title) > 200) {
        $errors[] = 'Title must be less than 200 characters';
    }
    
    // Strip tags to check actual text length (rough approximation for validation)
    $cleanContent = strip_tags($content);
    if (empty($cleanContent)) {
        $errors[] = 'Content is required';
    } elseif (strlen($cleanContent) < 50) {
        $errors[] = 'Content should be at least 50 characters';
    }
    
    if (empty($category)) {
        $errors[] = 'Category is required';
    }
    
    // Handle image upload
    $imageUrl = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if (in_array($_FILES['image']['type'], $allowedTypes)) {
            if ($_FILES['image']['size'] <= $maxSize) {
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
                $uploadPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    $imageUrl = $uploadPath;
                } else {
                    $errors[] = 'Failed to upload image';
                }
            } else {
                $errors[] = 'Image size must be less than 2MB';
            }
        } else {
            $errors[] = 'Invalid image format. Only JPG, PNG, GIF allowed';
        }
    } elseif ($_FILES['image']['error'] != 4) { // Error 4 means no file uploaded
        $errors[] = 'File upload error: ' . $_FILES['image']['error'];
    }
    
    if (empty($errors)) {
        // Handle video upload
        $videoUrl = null;
        if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
            $allowedTypes = ['video/mp4', 'video/webm', 'video/ogg'];
            $maxSize = 50 * 1024 * 1024; // 50MB
            
            if (in_array($_FILES['video']['type'], $allowedTypes)) {
                if ($_FILES['video']['size'] <= $maxSize) {
                    $uploadDir = 'uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $fileName = uniqid() . '_vid_' . basename($_FILES['video']['name']);
                    $uploadPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['video']['tmp_name'], $uploadPath)) {
                        $videoUrl = $uploadPath;
                    } else {
                        $errors[] = 'Failed to upload video';
                    }
                } else {
                    $errors[] = 'Video size must be less than 50MB';
                }
            } else {
                $errors[] = 'Invalid video format. Only MP4, WebM, OGG allowed';
            }
        } elseif ($_FILES['video']['error'] != 4) { // Error 4 means no file uploaded
            $errors[] = 'Video upload error: ' . $_FILES['video']['error'];
        }
    }
    
    // URL Fallbacks
    if (empty($imageUrl) && !empty($_POST['image_url'])) {
        $imageUrl = trim($_POST['image_url']);
    }
    if (empty($videoUrl) && !empty($_POST['video_url'])) {
        $videoUrl = trim($_POST['video_url']);
    }
    
    if (empty($errors)) {
        // FIXED: Pass correct parameters including author_id and video_url
        $postId = createPost($title, $content, $category, $user['id'], $imageUrl, $videoUrl);
        if ($postId) {
            $_SESSION['message'] = 'Post created successfully!' . 
                                  ($user['role'] !== 'admin' ? ' It will be reviewed by admin.' : '');
            $_SESSION['message_type'] = 'success';
            header('Location: dashboard.php');
            exit();
        } else {
            $errors[] = 'Failed to create post. Please try again.';
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['message'] = implode('<br>', $errors);
        $_SESSION['message_type'] = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - IUB News Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Quill Rich Text Editor Theme -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        :root {
            --iub-primary: #003366;
            --iub-secondary: #CC9933;
            --iub-accent: #004080;
            --iub-light: #F5F7FA;
            --iub-dark: #1A2B3C;
            --iub-success: #28A745;
            --iub-danger: #DC3545;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, var(--iub-primary) 0%, var(--iub-accent) 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 15px rgba(0, 51, 102, 0.1);
            margin-bottom: 30px;
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            color: white;
        }
        
        .logo-icon {
            background: rgba(255, 255, 255, 0.2);
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .logo-text h2 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 2px;
        }
        
        .logo-text p {
            font-size: 11px;
            opacity: 0.9;
        }
        
        .nav-links {
            display: flex;
            gap: 25px;
            align-items: center;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            transition: opacity 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .nav-links a:hover {
            opacity: 0.8;
        }
        
        /* Main Content */
        .create-post-main {
            padding: 20px;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .page-header h1 {
            color: var(--iub-primary);
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
        }
        
        .page-header h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--iub-secondary);
        }
        
        .page-header p {
            color: #666;
            font-size: 18px;
            margin-top: 20px;
        }
        
        /* Messages */
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid transparent;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        
        /* Form */
        .create-post-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 8px 30px rgba(0, 51, 102, 0.08);
            border: 1px solid #E0E6ED;
            margin-bottom: 40px;
            position: relative;
        }
        
        .create-post-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--iub-primary), var(--iub-secondary));
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 16px;
        }
        
        .form-group input[type="text"],
        .form-group select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #E0E6ED;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .form-group input[type="text"]:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--iub-primary);
            box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
        }
        
        /* Quill Editor overrides */
        .ql-toolbar {
            border-radius: 8px 8px 0 0;
            border-color: #E0E6ED !important;
            background: #f8f9fa;
        }
        .ql-container {
            border-radius: 0 0 8px 8px;
            border-color: #E0E6ED !important;
            font-family: inherit;
            font-size: 16px;
        }
        .ql-editor {
            min-height: 250px;
        }
        .ql-editor:focus {
            box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
        }

        input[type="file"] {
            padding: 12px;
            border: 2px dashed #E0E6ED;
            border-radius: 8px;
            background: var(--iub-light);
            transition: all 0.3s ease;
            cursor: pointer;
            width: 100%;
        }
        
        input[type="file"]::file-selector-button {
            background: var(--iub-primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            margin-right: 15px;
            font-weight: 600;
        }
        
        /* Form Actions */
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 25px;
            border-top: 1px solid #E0E6ED;
            margin-top: 30px;
        }
        
        .btn-outline,
        .btn-primary {
            padding: 14px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: none;
        }
        
        .btn-outline {
            background: white;
            color: var(--iub-primary);
            border: 2px solid var(--iub-primary);
        }
        
        .btn-outline:hover {
            background: var(--iub-light);
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background: var(--iub-primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--iub-accent);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 51, 102, 0.2);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 15px;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .page-header h1 {
                font-size: 28px;
            }
            
            .create-post-card {
                padding: 25px;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 15px;
            }
            
            .btn-outline,
            .btn-primary {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="index.php" class="logo">
                <div class="logo-icon">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div class="logo-text">
                    <h2>Create Post</h2>
                    <p>IUB News Portal</p>
                </div>
            </a>
            
            <nav class="nav-links">
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="create-post-main">
        <!-- Display Messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type'] ?? 'info'; ?>">
                <i class="fas <?php echo ($_SESSION['message_type'] ?? '') == 'danger' ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
                <?php echo $_SESSION['message']; ?>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>
        
        <div class="page-header">
            <h1>Create New Post</h1>
            <p>Share news, events, or announcements with the IUB community</p>
        </div>
        
        <div class="create-post-card">
            <form method="POST" action="" enctype="multipart/form-data" id="postForm">
                <div class="form-group">
                    <label for="title">Post Title *</label>
                    <input type="text" id="title" name="title" 
                           value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                           placeholder="Enter a descriptive title" 
                           maxlength="200" required>
                </div>
                
                <div class="form-group">
                    <label for="category">Category *</label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="Academics" <?php echo ($_POST['category'] ?? '') == 'Academics' ? 'selected' : ''; ?>>Academics</option>
                        <option value="Research" <?php echo ($_POST['category'] ?? '') == 'Research' ? 'selected' : ''; ?>>Research</option>
                        <option value="Events" <?php echo ($_POST['category'] ?? '') == 'Events' ? 'selected' : ''; ?>>Events</option>
                        <option value="Student Life" <?php echo ($_POST['category'] ?? '') == 'Student Life' ? 'selected' : ''; ?>>Student Life</option>
                        <option value="Announcements" <?php echo ($_POST['category'] ?? '') == 'Announcements' ? 'selected' : ''; ?>>Announcements</option>
                        <option value="Sports" <?php echo ($_POST['category'] ?? '') == 'Sports' ? 'selected' : ''; ?>>Sports</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="image">Featured Image</label>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <input type="file" id="image" name="image" accept="image/*">
                        <div style="text-align: center; color: #999; font-size: 0.8rem;">— OR PASTE URL —</div>
                        <input type="text" name="image_url" placeholder="https://example.com/image.jpg" class="form-control">
                    </div>
                    <small style="color: #666; font-size: 11px;">Max 2MB if uploading.</small>
                </div>

                <div class="form-group">
                    <label for="video">Featured Video</label>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <input type="file" id="video" name="video" accept="video/mp4,video/webm,video/ogg">
                        <div style="text-align: center; color: #999; font-size: 0.8rem;">— OR PASTE URL —</div>
                        <input type="text" name="video_url" placeholder="https://example.com/video.mp4" class="form-control">
                    </div>
                    <small style="color: #666; font-size: 11px;">Max 50MB if uploading.</small>
                </div>
                
                <div class="form-group">
                    <label for="editor-container">Content *</label>
                    <!-- Helper field to store the HTML content -->
                    <input type="hidden" name="content" id="content">
                    <!-- Quill Editor Container -->
                    <div id="editor-container"><?php echo isset($_POST['content']) ? $_POST['content'] : ''; ?></div>
                </div>
                
                <div class="form-actions">
                    <a href="dashboard.php" class="btn-outline">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <button type="submit" class="btn-primary" name="create_post">
                        <i class="fas fa-paper-plane"></i> Submit for Review
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- Quill Library -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <script>
    // Initialize Quill editor
    var quill = new Quill('#editor-container', {
        theme: 'snow',
        placeholder: 'Write your post content here...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                ['link', 'blockquote'],
                [{ 'color': [] }, { 'background': [] }],
                ['clean']
            ]
        }
    });

    // Form validation
    document.getElementById('postForm').addEventListener('submit', function(e) {
        const title = document.getElementById('title').value.trim();
        const category = document.getElementById('category').value;
        
        // Get content from Quill
        const editorContent = document.querySelector('.ql-editor').innerHTML;
        const textContent = quill.getText().trim();
        
        // Set hidden input value to the HTML content
        document.getElementById('content').value = editorContent;
        
        if (!title) {
            e.preventDefault();
            alert('Please enter a post title');
            document.getElementById('title').focus();
            return false;
        }
        
        if (!category) {
            e.preventDefault();
            alert('Please select a category');
            document.getElementById('category').focus();
            return false;
        }
        
        if (textContent.length < 50) {
            e.preventDefault();
            alert('Content should be at least 50 characters long');
            quill.focus();
            return false;
        }
        
        return true;
    });
    </script>
</body>
</html>