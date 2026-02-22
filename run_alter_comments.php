<?php
require_once 'includes/db.php';

echo "<h2>Database Changes Output:</h2><ul>";

// 1. Add parent_id to community_comments
$sql1 = "ALTER TABLE community_comments ADD COLUMN parent_id INT(11) NULL DEFAULT NULL AFTER post_id";
if ($conn->query($sql1)) {
    echo "<li>Added 'parent_id' to community_comments.</li>";
} else {
    echo "<li>Error adding 'parent_id': " . $conn->error . "</li>";
}

// 2. Add parent self-referencing foreign key
$sql2 = "ALTER TABLE community_comments ADD CONSTRAINT fk_comment_parent FOREIGN KEY (parent_id) REFERENCES community_comments(id) ON DELETE CASCADE";
if ($conn->query($sql2)) {
    echo "<li>Added foreign key for 'parent_id'.</li>";
} else {
    echo "<li>Error adding FK for 'parent_id': " . $conn->error . "</li>";
}

// 3. Add upvotes/downvotes to community_comments
$sql3 = "ALTER TABLE community_comments ADD COLUMN upvotes INT(11) DEFAULT 0 AFTER content, ADD COLUMN downvotes INT(11) DEFAULT 0 AFTER upvotes";
if ($conn->query($sql3)) {
    echo "<li>Added 'upvotes' and 'downvotes' to community_comments.</li>";
} else {
    echo "<li>Error adding vote columns: " . $conn->error . "</li>";
}

// 4. Create community_comment_reactions table
$sql4 = "CREATE TABLE IF NOT EXISTS community_comment_reactions (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    comment_id INT(11) NOT NULL,
    reaction_type ENUM('upvote', 'downvote') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_comment_reaction (user_id, comment_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES community_comments(id) ON DELETE CASCADE
)";
if ($conn->query($sql4)) {
    echo "<li>Created 'community_comment_reactions' table.</li>";
} else {
    echo "<li>Error creating reaction table: " . $conn->error . "</li>";
}

echo "</ul>";
?>
