<?php
$conn = new mysqli('localhost', 'root', '', 'semicolon_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if column exists
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'email'");
if ($result->num_rows == 0) {
    if ($conn->query("ALTER TABLE users ADD COLUMN email VARCHAR(255) AFTER username")) {
        echo "Successfully added 'email' column.\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "'email' column already exists.\n";
}

// Ensure security_question and security_answer are also there (just in case)
foreach (['security_question', 'security_answer'] as $col) {
    $result = $conn->query("SHOW COLUMNS FROM users LIKE '$col'");
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE users ADD COLUMN $col VARCHAR(255)");
        echo "Successfully added '$col' column.\n";
    }
}

$conn->close();
?>
