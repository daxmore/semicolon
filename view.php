<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$token = $_GET['token'] ?? '';
$is_raw = isset($_GET['raw']) && $_GET['raw'] === 'true';

if (empty($token)) {
    die("Invalid request.");
}

// Find resource by token
$resource = null;
$type = '';

// Check books
$stmt = $conn->prepare("SELECT * FROM books WHERE token = ?");
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $resource = $row;
    $type = 'book';
}

// Check papers
if (!$resource) {
    $stmt = $conn->prepare("SELECT * FROM papers WHERE token = ?");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $resource = $row;
        $type = 'paper';
    }
}

// Check videos
if (!$resource) {
    $stmt = $conn->prepare("SELECT * FROM videos WHERE token = ?");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $resource = $row;
        $type = 'video';
    }
}

if (!$resource) {
    die("Resource not found or invalid token.");
}

// Handle RAW file serving (for iframe)
if ($is_raw && $type !== 'video') {
    $file_path = $resource['private_path'];
    
    if (filter_var($file_path, FILTER_VALIDATE_URL)) {
        header("Location: " . $file_path);
        exit();
    } else {
        $real_path = realpath($file_path);
        if ($real_path && file_exists($real_path)) {
            $mime_type = mime_content_type($real_path);
            header('Content-Type: ' . $mime_type);
            header('Content-Disposition: inline; filename="' . basename($real_path) . '"');
            header('Content-Length: ' . filesize($real_path));
            readfile($real_path);
            exit();
        } else {
            die("File not found on server.");
        }
    }
}

// Record History (only on main view)
if (!$is_raw) {
    record_view($_SESSION['user_id'], $type, $resource['id']);
}

// Fetch Reaction Stats
$stats = get_reaction_stats($type, $resource['id']);
$user_id = $_SESSION['user_id'];

// Check user's reaction
$stmt = $conn->prepare("SELECT is_helpful FROM reactions WHERE user_id = ? AND resource_type = ? AND resource_id = ?");
$stmt->bind_param('isi', $user_id, $type, $resource['id']);
$stmt->execute();
$user_reaction = $stmt->get_result()->fetch_assoc();
$is_helpful = $user_reaction ? $user_reaction['is_helpful'] : null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($resource['title']); ?> - Semicolon Viewer</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        body, html { margin: 0; padding: 0; height: 100%; overflow: hidden; background: #0f172a; }
        .viewer-grid { display: grid; grid-template-rows: auto 1fr; height: 100%; }
        iframe { width: 100%; height: 100%; border: none; }
    </style>
</head>
<body>
    <div class="viewer-grid">
        <!-- Toolbar -->
        <div class="bg-gray-900 text-white px-4 py-3 flex items-center justify-between shadow-md z-10">
            <div class="flex items-center gap-4">
                <a href="javascript:history.back()" class="text-gray-400 hover:text-white transition flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Back
                </a>
                <h1 class="font-semibold text-lg truncate max-w-md"><?php echo htmlspecialchars($resource['title']); ?></h1>
                <span class="text-xs bg-gray-700 px-2 py-1 rounded text-gray-300 uppercase"><?php echo $type; ?></span>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 bg-gray-800 rounded-lg p-1">
                    <button onclick="toggleReaction(1)" id="btn-helpful" 
                        class="flex items-center gap-1 px-3 py-1.5 rounded transition <?php echo ($is_helpful === 1) ? 'bg-green-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-700'; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z" />
                        </svg>
                        <span class="text-xs font-bold" id="count-helpful"><?php echo $stats['helpful']; ?></span>
                    </button>
                    <button onclick="toggleReaction(0)" id="btn-not-helpful"
                        class="flex items-center gap-1 px-3 py-1.5 rounded transition <?php echo ($is_helpful === 0) ? 'bg-red-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-700'; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M18 9.5a1.5 1.5 0 11-3 0v-6a1.5 1.5 0 013 0v6zM14 9.667v-5.43a2 2 0 00-1.105-1.79l-.05-.025A4 4 0 0011.055 2H5.64a2 2 0 00-1.962 1.608l-1.2 6A2 2 0 004.44 12H8v4a2 2 0 002 2 1 1 0 001-1v-.667a4 4 0 01.8-2.4l1.4-1.866a4 4 0 00.8-2.4z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="bg-black relative w-full h-full">
            <?php if ($type === 'video'): 
                $video_url = $resource['youtube_url'];
                $is_iframe = (strpos(trim($video_url), '<iframe') === 0);
                
                if ($is_iframe):
                    echo $video_url;
                else:
                    $video_id = get_youtube_id($video_url);
            ?>
                <iframe src="https://www.youtube.com/embed/<?php echo $video_id; ?>?autoplay=1" allowfullscreen></iframe>
            <?php endif; ?>
            <?php else: ?>
                <iframe src="view.php?token=<?php echo $token; ?>&raw=true"></iframe>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const resourceType = '<?php echo $type; ?>';
        const resourceId = <?php echo $resource['id']; ?>;

        function toggleReaction(isHelpful) {
            fetch('api/reaction.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    resource_type: resourceType,
                    resource_id: resourceId,
                    is_helpful: isHelpful
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update UI
                    document.getElementById('count-helpful').innerText = data.stats.helpful;
                    
                    const btnHelpful = document.getElementById('btn-helpful');
                    const btnNotHelpful = document.getElementById('btn-not-helpful');

                    if (isHelpful === 1) {
                        btnHelpful.classList.remove('text-gray-400', 'hover:text-white', 'hover:bg-gray-700');
                        btnHelpful.classList.add('bg-green-600', 'text-white');
                        
                        btnNotHelpful.classList.remove('bg-red-600', 'text-white');
                        btnNotHelpful.classList.add('text-gray-400', 'hover:text-white', 'hover:bg-gray-700');
                    } else {
                        btnNotHelpful.classList.remove('text-gray-400', 'hover:text-white', 'hover:bg-gray-700');
                        btnNotHelpful.classList.add('bg-red-600', 'text-white');

                        btnHelpful.classList.remove('bg-green-600', 'text-white');
                        btnHelpful.classList.add('text-gray-400', 'hover:text-white', 'hover:bg-gray-700');
                    }
                }
            })
            .catch(err => console.error('Error:', err));
        }
    </script>
</body>
</html>
