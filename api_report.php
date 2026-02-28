<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit();
}

$target_type = $_POST['target_type'] ?? '';
$target_id = isset($_POST['target_id']) ? (int)$_POST['target_id'] : 0;
$reason = trim($_POST['reason'] ?? '');

if (!in_array($target_type, ['post', 'comment']) || !$target_id || empty($reason)) {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit();
}

$stmt = $conn->prepare("INSERT INTO community_reports (target_type, target_id, user_id, reason) VALUES (?, ?, ?, ?)");
$stmt->bind_param('siis', $target_type, $target_id, $_SESSION['user_id'], $reason);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
