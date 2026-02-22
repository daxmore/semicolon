<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Handle Create and Delete operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $youtube_url = $_POST['youtube_url'];
        
        $slug = generate_slug($title);
        $slug .= '-' . substr(md5(uniqid()), 0, 5);

        $stmt = $conn->prepare("INSERT INTO videos (title, description, youtube_url, slug) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $description, $youtube_url, $slug);
        $stmt->execute();
    } elseif ($action === 'delete') {
        $id = $_POST['id'];

        $stmt = $conn->prepare("DELETE FROM videos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    header("Location: videos.php");
    exit();
}

// Fetch all videos
$result = $conn->query("SELECT * FROM videos ORDER BY created_at DESC");
$videos = $result->fetch_all(MYSQLI_ASSOC);

$showForm = isset($_GET['add']);

include 'header.php';
?>

<!-- Header with Add Button -->
<div class="flex justify-between items-center mb-6">
    <p class="text-zinc-500">Manage your video tutorials</p>
    <?php if (!$showForm): ?>
        <a href="videos.php?add=1" class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 text-white rounded-xl font-medium hover:bg-rose-700 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add New Video
        </a>
    <?php else: ?>
        <a href="videos.php" class="inline-flex items-center gap-2 px-4 py-2 border border-zinc-200 text-zinc-700 rounded-xl font-medium hover:bg-zinc-50 transition">
            Cancel
        </a>
    <?php endif; ?>
</div>

<?php if ($showForm): ?>
<!-- Add Form -->
<div class="bg-white p-6 rounded-2xl border border-zinc-200 mb-8">
    <h2 class="text-lg font-bold text-zinc-900 mb-6">Add New Video</h2>
    <form action="videos.php" method="post" class="space-y-4">
        <input type="hidden" name="action" value="add">
        <div>
            <label class="block text-sm font-medium text-zinc-700 mb-1">Title</label>
            <input type="text" name="title" required class="w-full px-4 py-2 border border-zinc-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-zinc-700 mb-1">Description</label>
            <textarea name="description" rows="3" class="w-full px-4 py-2 border border-zinc-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent"></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-zinc-700 mb-1">YouTube Embed Code (Iframe)</label>
            <textarea name="youtube_url" required rows="4" placeholder='<iframe width="560" height="315" src="https://www.youtube.com/embed/..." ...></iframe>' class="w-full px-4 py-2 border border-zinc-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent"></textarea>
            <p class="text-xs text-zinc-500 mt-1">Paste the complete iframe code from YouTube.</p>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2 bg-rose-600 text-white rounded-xl font-medium hover:bg-rose-700 transition">
                Add Video
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- Videos Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (empty($videos)): ?>
        <div class="col-span-full bg-white rounded-2xl border border-zinc-200 p-12 text-center text-zinc-500">
            No videos found. Add your first video!
        </div>
    <?php else: ?>
        <?php foreach ($videos as $video): 
            $video_content = $video['youtube_url'];
            $is_iframe = (strpos(trim($video_content), '<iframe') === 0);
            $youtube_id = $is_iframe ? false : get_youtube_id($video_content);
        ?>
            <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden group">
                <div class="aspect-video bg-zinc-900 flex items-center justify-center overflow-hidden">
                    <?php if ($is_iframe): ?>
                        <div class="w-full h-full [&_iframe]:w-full [&_iframe]:h-full">
                            <?php echo $video_content; ?>
                        </div>
                    <?php elseif ($youtube_id): ?>
                        <iframe 
                            src="https://www.youtube.com/embed/<?php echo htmlspecialchars($youtube_id); ?>" 
                            title="<?php echo htmlspecialchars($video['title']); ?>"
                            class="w-full h-full"
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                        </iframe>
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-rose-500 to-pink-600 text-white/50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-zinc-900 mb-1 line-clamp-1"><?php echo htmlspecialchars($video['title']); ?></h3>
                    <p class="text-sm text-zinc-500 line-clamp-2 mb-4"><?php echo htmlspecialchars($video['description']); ?></p>
                    <form action="videos.php" method="post" onsubmit="return confirm('Delete this video?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $video['id']; ?>">
                        <button type="submit" class="w-full py-2 bg-red-100 text-red-600 rounded-xl font-medium hover:bg-red-200 transition">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>