<?php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'You must be logged in to vote.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$item_type = $_POST['item_type'] ?? 'post'; // 'post' or 'comment'
$item_id = $_POST['item_id'] ?? $_POST['post_id'] ?? null; // fallback to post_id for backwards compatibility
$type = $_POST['type'] ?? null; // 'upvote' or 'downvote'

if (!$item_id || !in_array($type, ['upvote', 'downvote']) || !in_array($item_type, ['post', 'comment'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
    exit();
}

$table_target = $item_type === 'post' ? 'community_posts' : 'community_comments';
$table_reactions = $item_type === 'post' ? 'community_reactions' : 'community_comment_reactions';
$fk_column = $item_type === 'post' ? 'post_id' : 'comment_id';

// Check if item exists
$stmt = $conn->prepare("SELECT id, upvotes, downvotes FROM $table_target WHERE id = ?");
$stmt->bind_param('i', $item_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    echo json_encode(['success' => false, 'error' => ucfirst($item_type) . ' not found.']);
    exit();
}

// Check if user already reacted
$stmt = $conn->prepare("SELECT reaction_type FROM $table_reactions WHERE user_id = ? AND $fk_column = ?");
$stmt->bind_param('ii', $user_id, $item_id);
$stmt->execute();
$existing_reaction = $stmt->get_result()->fetch_assoc();

$db_upvote_adjustment = 0;
$db_downvote_adjustment = 0;
$final_user_reaction = null;

if ($existing_reaction) {
    if ($existing_reaction['reaction_type'] === $type) {
        // Toggle OFF (e.g. they clicked upvote again to remove it)
        $stmt_del = $conn->prepare("DELETE FROM $table_reactions WHERE user_id = ? AND $fk_column = ?");
        $stmt_del->bind_param('ii', $user_id, $item_id);
        $stmt_del->execute();
        
        if ($type === 'upvote') $db_upvote_adjustment = -1;
        if ($type === 'downvote') $db_downvote_adjustment = -1;
        
        $final_user_reaction = null;
    } else {
        // Switch Vote (e.g. from downvote to upvote)
        $stmt_upd = $conn->prepare("UPDATE $table_reactions SET reaction_type = ? WHERE user_id = ? AND $fk_column = ?");
        $stmt_upd->bind_param('sii', $type, $user_id, $item_id);
        $stmt_upd->execute();
        
        if ($type === 'upvote') {
            $db_upvote_adjustment = 1;
            $db_downvote_adjustment = -1;
        } else {
            $db_upvote_adjustment = -1;
            $db_downvote_adjustment = 1;
        }
        
        $final_user_reaction = $type;
    }
} else {
    // New Vote
    $stmt_ins = $conn->prepare("INSERT INTO $table_reactions (user_id, $fk_column, reaction_type) VALUES (?, ?, ?)");
    $stmt_ins->bind_param('iis', $user_id, $item_id, $type);
    $stmt_ins->execute();
    
    if ($type === 'upvote') $db_upvote_adjustment = 1;
    if ($type === 'downvote') $db_downvote_adjustment = 1;
    
    $final_user_reaction = $type;
}

// Apply changes to the target table
$new_upvotes = $item['upvotes'] + $db_upvote_adjustment;
$new_downvotes = $item['downvotes'] + $db_downvote_adjustment;

// Ensure they don't go below 0
$new_upvotes = max(0, $new_upvotes);
$new_downvotes = max(0, $new_downvotes);

$stmt_update_item = $conn->prepare("UPDATE $table_target SET upvotes = ?, downvotes = ? WHERE id = ?");
$stmt_update_item->bind_param('iii', $new_upvotes, $new_downvotes, $item_id);
$stmt_update_item->execute();

echo json_encode([
    'success' => true, 
    'new_upvotes' => $new_upvotes,
    'user_reaction' => $final_user_reaction
]);
