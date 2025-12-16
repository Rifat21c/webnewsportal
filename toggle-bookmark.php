<?php
// toggle-bookmark.php
require_once 'config.php';
require_once 'database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Login required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$postId = $input['post_id'] ?? null;

if (!$postId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Post ID required']);
    exit;
}

try {
    $isBookmarked = toggleBookmark($_SESSION['user_id'], $postId);
    echo json_encode(['success' => true, 'is_bookmarked' => $isBookmarked]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
