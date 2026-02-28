<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

$url = $_GET['url'] ?? '';
$post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;

if (!filter_var($url, FILTER_VALIDATE_URL) || !$post_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit();
}

// Check if it already has a preview in the DB
$stmt = $conn->prepare("SELECT link_preview_json FROM community_posts WHERE id = ?");
$stmt->bind_param('i', $post_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if ($row && !empty($row['link_preview_json'])) {
    // Already populated, return the existing JSON
    echo $row['link_preview_json'];
    // Assuming the DB stores `{ "success": true, "title": "...", ... }`
    exit();
}

// Fetch open graph tags natively
$context = stream_context_create([
    'http' => [
        'user_agent' => 'Semicolon/1.0',
        'timeout' => 5
    ]
]);

$html = @file_get_contents($url, false, $context);
if (!$html) {
    echo json_encode(['success' => false, 'error' => 'Could not fetch URL']);
    exit();
}

libxml_use_internal_errors(true);
$doc = new DOMDocument();
@$doc->loadHTML($html);

$title = '';
$description = '';
$image = '';

$tags = $doc->getElementsByTagName('meta');
foreach ($tags as $tag) {
    $property = $tag->getAttribute('property');
    $name = $tag->getAttribute('name');
    $content = $tag->getAttribute('content');

    if ($property === 'og:title' || $name === 'twitter:title' || $name === 'title') {
        if (!$title) $title = $content;
    }
    if ($property === 'og:description' || $name === 'twitter:description' || $name === 'description') {
        if (!$description) $description = $content;
    }
    if ($property === 'og:image' || $name === 'twitter:image') {
        if (!$image) $image = $content;
    }
}

// Fallback to <title>
if (!$title) {
    $titleTags = $doc->getElementsByTagName('title');
    if ($titleTags->length > 0) {
        $title = $titleTags->item(0)->textContent;
    }
}

if ($title) {
    // Ensure image is an absolute URL if relative
    if ($image && !filter_var($image, FILTER_VALIDATE_URL)) {
        $parsed = parse_url($url);
        $base = $parsed['scheme'] . '://' . $parsed['host'];
        if (strpos($image, '/') === 0) {
            $image = $base . $image;
        } else {
            $image = $base . '/' . $image;
        }
    }

    $preview_data = [
        'success' => true,
        'title' => htmlspecialchars_decode($title),
        'description' => htmlspecialchars_decode($description),
        'image' => $image,
        'url' => $url
    ];
    $json_val = json_encode($preview_data);
    
    // Save to DB for caching
    $upd = $conn->prepare("UPDATE community_posts SET link_preview_json = ? WHERE id = ?");
    $upd->bind_param('si', $json_val, $post_id);
    $upd->execute();
    
    echo $json_val;
} else {
    echo json_encode(['success' => false, 'error' => 'No title found']);
}
