<?php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/functions.php';

$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

// Fuzzy search using LIKE for now (Classic PHP/MySQL)
// In a real production env with more data, we'd use FULLTEXT or a search engine.
// We search across books, papers, and videos.

$results = [];

// Books
$sql = "SELECT id, title, 'book' as type, slug, token FROM books WHERE title LIKE ? OR author LIKE ? OR subject LIKE ? LIMIT 5";
$searchTerm = "%" . $query . "%";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $results[] = $row;
}

// Papers
$sql = "SELECT id, title, 'paper' as type, slug, token FROM papers WHERE title LIKE ? OR subject LIKE ? LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $searchTerm, $searchTerm);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $results[] = $row;
}

// Videos
$sql = "SELECT id, title, 'video' as type, slug, token FROM videos WHERE title LIKE ? OR description LIKE ? LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $searchTerm, $searchTerm);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $results[] = $row;
}

echo json_encode($results);
?>
