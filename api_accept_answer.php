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

$comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;

if (!$comment_id || !$post_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit();
}

// Ensure the logged-in user is the author of the post
$stmt = $conn->prepare("SELECT user_id FROM community_posts WHERE id = ?");
$stmt->bind_param('i', $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post || $post['user_id'] != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized. Only post author can accept answers.']);
    exit();
}

// Toggle the is_accepted status of the comment
$stmt_comment = $conn->prepare("SELECT is_accepted FROM community_comments WHERE id = ? AND post_id = ?");
$stmt_comment->bind_param('ii', $comment_id, $post_id);
$stmt_comment->execute();
$comment = $stmt_comment->get_result()->fetch_assoc();

if (!$comment) {
    echo json_encode(['success' => false, 'error' => 'Comment not found']);
    exit();
}

$new_status = $comment['is_accepted'] ? 0 : 1;

// Update the comment
$update_stmt = $conn->prepare("UPDATE community_comments SET is_accepted = ? WHERE id = ?");
$update_stmt->bind_param('ii', $new_status, $comment_id);
if ($update_stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'is_accepted' => $new_status
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
