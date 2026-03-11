<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $resource_type = $input['resource_type'] ?? '';
    $resource_id = $input['resource_id'] ?? 0;
    $is_helpful = $input['is_helpful'] ?? 1; // 1 for helpful, 0 for not helpful (or just toggle)

    if (empty($resource_type) || empty($resource_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        exit();
    }

    $user_id = $_SESSION['user_id'];
    
    if (toggle_reaction($user_id, $resource_type, $resource_id, $is_helpful)) {
        // Fetch new state to pass back to frontend so UI knows if it was deleted
        $stmt_check = $conn->prepare("SELECT is_helpful FROM reactions WHERE user_id = ? AND resource_type = ? AND resource_id = ?");
        $stmt_check->bind_param('isi', $user_id, $resource_type, $resource_id);
        $stmt_check->execute();
        $user_reaction = $stmt_check->get_result()->fetch_assoc();
        
        $stats = get_reaction_stats($resource_type, $resource_id);
        echo json_encode(['success' => true, 'stats' => $stats, 'user_reaction' => $user_reaction ? $user_reaction['is_helpful'] : null]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
