<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// 1. Requirement: User must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("Invalid request.");
}

// 2. Requirement: User must be PRO
if (!is_pro_user($user_id)) {
    $_SESSION['toasts'][] = [
        'type' => 'info', 
        'title' => 'Pro Feature', 
        'message' => 'Downloads are exclusive to Pro members. Upgrade now to unlock!'
    ];
    header('Location: pricing.php');
    exit();
}

// 3. Find the resource
$resource = null;

// Search in books
$stmt = $conn->prepare("SELECT id, title, private_path FROM books WHERE token = ?");
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $resource = $row;
    $type = 'book';
}

// Search in papers if not found in books
if (!$resource) {
    $stmt = $conn->prepare("SELECT id, title, private_path FROM papers WHERE token = ?");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $resource = $row;
        $type = 'paper';
    }
}

if (!$resource) {
    die("Resource not found.");
}

$file_path = $resource['private_path'];

// 4. Serve the file
if (filter_var($file_path, FILTER_VALIDATE_URL)) {
    // If it's a URL (e.g. S3), redirect to it for download
    header("Location: " . $file_path);
    exit();
} else {
    // Local file
    $real_path = realpath($file_path);
    if ($real_path && file_exists($real_path)) {
        // Increment download count or log
        record_download($user_id, $type, $resource['id']);
        
        $mime_type = mime_content_type($real_path);
        $filename = preg_replace('/[^A-Za-z0-9_\-.]/', '_', $resource['title']) . '.pdf';
        
        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($real_path));
        header('Pragma: no-cache');
        header('Expires: 0');
        
        readfile($real_path);
        exit();
    } else {
        die("File not found on server.");
    }
}
?>
