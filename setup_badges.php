<?php
require_once 'includes/db.php';

$queries = [
    "ALTER TABLE users ADD COLUMN xp INT DEFAULT 0",
    "CREATE TABLE IF NOT EXISTS badges (
        id INT PRIMARY KEY AUTO_INCREMENT, 
        name VARCHAR(100), 
        description TEXT, 
        icon_svg TEXT, 
        required_xp INT
    )",
    "CREATE TABLE IF NOT EXISTS user_badges (
        user_id INT, 
        badge_id INT, 
        awarded_at DATETIME DEFAULT CURRENT_TIMESTAMP, 
        PRIMARY KEY (user_id, badge_id)
    )"
];

foreach ($queries as $q) {
    if ($conn->query($q) === TRUE) {
        echo "Successfully executed: " . explode(' ', $q)[0] . "\n";
    } else {
        echo "Error: " . $conn->error . "\n";
    }
}
