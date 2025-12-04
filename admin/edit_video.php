<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: videos.php');
    exit();
}

$sql = "SELECT * FROM videos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$video = $result->fetch_assoc();

if (!$video) {
    header('Location: videos.php');
    exit();
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $youtube_url = $_POST['youtube_url'];
    
    $sql = "UPDATE videos SET title = ?, description = ?, youtube_url = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssi', $title, $description, $youtube_url, $id);
    $stmt->execute();
    header('Location: videos.php');
    exit();
}

$youtube_id = get_youtube_id($video['youtube_url']);

include 'header.php';
?>

<!-- Back Link -->
<div class="mb-6">
    <a href="videos.php" class="inline-flex items-center gap-2 text-zinc-500 hover:text-zinc-700 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Videos
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Edit Form -->
    <div class="bg-white rounded-2xl border border-zinc-200 p-6">
        <h2 class="text-lg font-bold text-zinc-900 mb-6">Edit Video Details</h2>
        
        <form action="" method="post" class="space-y-6">
            <input type="hidden" name="action" value="update">
            
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-2">Title</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($video['title']); ?>" required
                    class="w-full px-4 py-3 border border-zinc-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-2">Description</label>
                <textarea name="description" rows="4"
                    class="w-full px-4 py-3 border border-zinc-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"><?php echo htmlspecialchars($video['description']); ?></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-2">YouTube URL</label>
                <input type="url" name="youtube_url" value="<?php echo htmlspecialchars($video['youtube_url']); ?>" required
                    class="w-full px-4 py-3 border border-zinc-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition">
                <p class="text-xs text-zinc-400 mt-1">Supports: youtube.com/watch?v=, youtu.be/, youtube.com/embed/</p>
            </div>
            
            <div class="flex items-center justify-end gap-4 pt-4 border-t border-zinc-100">
                <a href="videos.php" class="px-6 py-2.5 text-zinc-600 hover:text-zinc-800 font-medium transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-rose-600 text-white rounded-xl font-medium hover:bg-rose-700 transition">
                    Update Video
                </button>
            </div>
        </form>
    </div>
    
    <!-- Video Preview -->
    <div class="bg-white rounded-2xl border border-zinc-200 p-6">
        <h2 class="text-lg font-bold text-zinc-900 mb-6">Preview</h2>
        <div class="aspect-video bg-zinc-900 rounded-xl overflow-hidden">
            <?php if ($youtube_id): ?>
                <iframe 
                    src="https://www.youtube.com/embed/<?php echo htmlspecialchars($youtube_id); ?>" 
                    title="<?php echo htmlspecialchars($video['title']); ?>"
                    class="w-full h-full"
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen>
                </iframe>
            <?php else: ?>
                <div class="w-full h-full flex items-center justify-center text-zinc-500">
                    <p>No preview available</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
