<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $notifs = get_notifications($user_id);
    echo json_encode($notifs);
} elseif ($method === 'POST') {
    // Mark as read
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['id'])) {
        mark_notification_read($input['id'], $user_id);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Missing ID']);
    }
}
?>
