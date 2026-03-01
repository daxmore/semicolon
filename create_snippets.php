<?php
require 'includes/db.php';
$sql = "CREATE TABLE IF NOT EXISTS code_snippets (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    user_id INT, 
    title VARCHAR(255), 
    description TEXT, 
    html_code TEXT, 
    css_code TEXT, 
    js_code TEXT, 
    is_public TINYINT(1) DEFAULT 1, 
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP, 
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
if($conn->query($sql)) {
    echo "Table created successfully!";
} else {
    echo "Error: ". $conn->error;
}
?>
