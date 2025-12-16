<?php
// migration_video.php - Add video_url to posts table
require_once 'config.php';

// $conn is already available from config.php

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if column exists
$checkSql = "SHOW COLUMNS FROM posts LIKE 'video_url'";
$checkResult = mysqli_query($conn, $checkSql);

if (mysqli_num_rows($checkResult) == 0) {
    // Add column
    $sql = "ALTER TABLE posts ADD COLUMN video_url VARCHAR(255) DEFAULT NULL AFTER image_url";
    if (mysqli_query($conn, $sql)) {
        echo "Successfully added 'video_url' column to 'posts' table.\n";
    } else {
        echo "Error creating column: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "Column 'video_url' already exists.\n";
}

// Do not close $conn here as it might be used if this script was included elsewhere, 
// though for a standalone script it doesn't matter much.
?>
