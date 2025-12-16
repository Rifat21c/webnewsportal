<?php
// setup_features.php
require_once 'config.php';

function createTables() {
    global $conn;

    // Events Table
    $eventsSql = "CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        event_date DATETIME NOT NULL,
        location VARCHAR(255),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";

    if (mysqli_query($conn, $eventsSql)) {
        echo "Events table created successfully.<br>";
    } else {
        echo "Error creating events table: " . mysqli_error($conn) . "<br>";
    }

    // Bookmarks Table
    $bookmarksSql = "CREATE TABLE IF NOT EXISTS bookmarks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        post_id INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_bookmark (user_id, post_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
    )";

    if (mysqli_query($conn, $bookmarksSql)) {
        echo "Bookmarks table created successfully.<br>";
    } else {
        echo "Error creating bookmarks table: " . mysqli_error($conn) . "<br>";
    }
}

createTables();
?>
