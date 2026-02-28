<?php
// api_comments.php
session_start();
require_once 'includes/db.php';
require_once 'includes/comment_renderer.php';

header('Content-Type: application/json');

$post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

if (!$post_id) {
    echo json_encode(['success' => false, 'error' => 'Missing post ID']);
    exit();
}

$is_admin = false;
if (isset($_SESSION['user_id'])) {
    $stmt_role = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt_role->bind_param("i", $_SESSION['user_id']);
    $stmt_role->execute();
    $role_res = $stmt_role->get_result()->fetch_assoc();
    if ($role_res && $role_res['role'] === 'admin') $is_admin = true;
}

// Fetch who made the post
$stmt = $conn->prepare("SELECT user_id FROM community_posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post_res = $stmt->get_result()->fetch_assoc();
$post_user_id = $post_res ? $post_res['user_id'] : 0;

// Fetch all comments (same logic as community_post_detail.php so tree builds correctly)
$comments_stmt = $conn->prepare("SELECT cc.*, u.username, u.avatar_url FROM community_comments cc JOIN users u ON cc.user_id = u.id WHERE cc.post_id = ? ORDER BY cc.is_accepted DESC, cc.upvotes DESC, cc.created_at ASC");
$comments_stmt->bind_param('i', $post_id);
$comments_stmt->execute();
$all_comments = $comments_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch reactions
$comment_reactions = [];
if (isset($_SESSION['user_id']) && !empty($all_comments)) {
    $comment_ids = array_column($all_comments, 'id');
    $placeholders = str_repeat('?,', count($comment_ids) - 1) . '?';
    $types = str_repeat('i', count($comment_ids)) . 'i'; 
    $params = $comment_ids;
    array_unshift($params, $_SESSION['user_id']);
    
    $react_sql = "SELECT comment_id, reaction_type FROM community_comment_reactions WHERE user_id = ? AND comment_id IN ($placeholders)";
    $react_stmt = $conn->prepare($react_sql);
    $react_stmt->bind_param($types, ...$params);
    $react_stmt->execute();
    $reaction_res = $react_stmt->get_result();
    while ($row = $reaction_res->fetch_assoc()) {
        $comment_reactions[$row['comment_id']] = $row['reaction_type']; 
    }
}

// Organize into tree
$comments_by_parent = [];
foreach ($all_comments as $cmt) {
    $parent = $cmt['parent_id'] ?: 0;
    if (!isset($comments_by_parent[$parent])) {
        $comments_by_parent[$parent] = [];
    }
    $comments_by_parent[$parent][] = $cmt;
}

$top_level = $comments_by_parent[0] ?? [];
$render_slice = array_slice($top_level, $offset, $limit);
$has_more = count($top_level) > ($offset + $limit);

$comments_by_parent[0] = $render_slice;

ob_start();
render_comments(0, $comments_by_parent, $post_user_id, 0, $comment_reactions, $is_admin, $post_id, $_SESSION['user_id'] ?? 0);
$html = ob_get_clean();

echo json_encode([
    'success' => true,
    'html' => $html,
    'has_more' => $has_more
]);
