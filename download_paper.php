<?php
session_start();

// Redirect admin users to the admin dashboard if logged in
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: admin/index.php');
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}
require_once 'includes/db.php';
require_once 'includes/functions.php';

$paper_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if ($paper_id) {
    // Log the download
    $sql = "INSERT INTO paper_downloads (paper_id, user_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $paper_id, $user_id);
    $stmt->execute();

    // Get the file path
    $sql = "SELECT file_path FROM papers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $paper_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $file_path = $row['file_path'];

        // Force download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        readfile($file_path);
        exit;
    }
}

header('Location: papers.php');
exit;
?>