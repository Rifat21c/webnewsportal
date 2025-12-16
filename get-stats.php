<?php
// get-stats.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$stats = getStatistics();
header('Content-Type: application/json');
echo json_encode($stats);
?>