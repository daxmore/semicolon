<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

require_once 'includes/db.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Save or Update Snippet
    if ($action === 'create' || $action === 'update') {
        $title = trim($_POST['title'] ?? 'Untitled Snippet');
        $html_code = $_POST['html_code'] ?? '';
        $css_code = $_POST['css_code'] ?? '';
        $js_code = $_POST['js_code'] ?? '';
        $is_public = isset($_POST['is_public']) ? (int)$_POST['is_public'] : 1;
        
        if ($action === 'create') {
            $stmt = $conn->prepare("INSERT INTO code_snippets (user_id, title, html_code, css_code, js_code, is_public) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssi", $user_id, $title, $html_code, $css_code, $js_code, $is_public);
            
            if ($stmt->execute()) {
                $new_id = $conn->insert_id;
                echo json_encode(['success' => true, 'id' => $new_id]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error']);
            }
        } 
        else if ($action === 'update') {
            $snippet_id = (int)($_POST['id'] ?? 0);
            
            // Verify ownership
            $check_stmt = $conn->prepare("SELECT id FROM code_snippets WHERE id = ? AND user_id = ?");
            $check_stmt->bind_param("ii", $snippet_id, $user_id);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows === 0) {
                echo json_encode(['success' => false, 'message' => 'Permission denied']);
                exit();
            }
            
            $stmt = $conn->prepare("UPDATE code_snippets SET title = ?, html_code = ?, css_code = ?, js_code = ?, is_public = ? WHERE id = ?");
            $stmt->bind_param("ssssii", $title, $html_code, $css_code, $js_code, $is_public, $snippet_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database update error']);
            }
        }
    }
    // Handle Delete
    else if ($action === 'delete') {
        $snippet_id = (int)($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM code_snippets WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $snippet_id, $user_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Delete failed']);
        }
    }
}
?>
