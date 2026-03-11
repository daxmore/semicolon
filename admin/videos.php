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
        $token = generate_token();

        $stmt = $conn->prepare("INSERT INTO videos (title, description, youtube_url, slug, token) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $title, $description, $youtube_url, $slug, $token);
        $stmt->execute();
        
        if (!isset($_SESSION['toasts'])) $_SESSION['toasts'] = [];
        $_SESSION['toasts'][] = ['type' => 'success', 'title' => 'Video Added', 'message' => "Successfully added: {$title}"];
    } elseif ($action === 'delete') {
        $id = $_POST['id'];

        $stmt = $conn->prepare("DELETE FROM videos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        if (!isset($_SESSION['toasts'])) $_SESSION['toasts'] = [];
        $_SESSION['toasts'][] = ['type' => 'info', 'title' => 'Video Deleted', 'message' => "The video was removed."];
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
<div class="flex justify-between items-center mb-6 px-4 sm:px-0">
    <p class="text-zinc-500 dark:text-zinc-400">Manage your video tutorials</p>
    <?php if (!$showForm): ?>
        <a href="videos.php?add=1" class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 text-white rounded-xl font-medium hover:bg-rose-700 transition shadow-lg shadow-rose-600/20">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add New Video
        </a>
    <?php else: ?>
        <a href="videos.php" class="inline-flex items-center gap-2 px-4 py-2 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 rounded-xl font-medium hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
            Cancel
        </a>
    <?php endif; ?>
</div>

<?php if ($showForm): ?>
<!-- Add Form -->
<div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 mb-8 shadow-sm max-w-2xl mx-auto sm:mx-0">
    <h2 class="text-lg font-bold text-zinc-900 dark:text-white mb-6">Add New Video</h2>
    <form action="videos.php" method="post" class="space-y-4">
        <input type="hidden" name="action" value="add">
        <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Title</label>
            <input type="text" name="title" required class="w-full px-4 py-2 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl text-zinc-900 dark:text-white focus:ring-2 focus:ring-rose-500 outline-none transition">
        </div>
        <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Description</label>
            <textarea name="description" rows="3" class="w-full px-4 py-2 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl text-zinc-900 dark:text-white focus:ring-2 focus:ring-rose-500 outline-none transition"></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">YouTube Embed Code (Iframe)</label>
            <textarea name="youtube_url" required rows="4" placeholder='<iframe width="560" height="315" src="https://www.youtube.com/embed/..." ...></iframe>' class="w-full px-4 py-2 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl text-zinc-900 dark:text-white font-mono text-xs focus:ring-2 focus:ring-rose-500 outline-none transition"></textarea>
            <p class="text-xs text-zinc-500 dark:text-zinc-500 mt-1">Paste the complete iframe code from YouTube.</p>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2 bg-rose-600 text-white rounded-xl font-medium hover:bg-rose-700 transition shadow-lg shadow-rose-600/20">
                Add Video
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- Videos Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 px-4 sm:px-0">
    <?php if (empty($videos)): ?>
        <div class="col-span-full bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 p-16 text-center shadow-sm">
            <div class="w-16 h-16 bg-zinc-100 dark:bg-zinc-800 rounded-2xl flex items-center justify-center mx-auto mb-4 text-zinc-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 022 2z" /></svg>
            </div>
            <p class="text-zinc-500 dark:text-zinc-400 font-medium">No videos found. Add your first video!</p>
        </div>
    <?php else: ?>
        <?php foreach ($videos as $video): 
            $video_content = $video['youtube_url'];
            $is_iframe = (preg_match('/<iframe/i', $video_content));
            $youtube_id = $is_iframe ? false : get_youtube_id($video_content);
        ?>
            <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 overflow-hidden group shadow-sm hover:shadow-xl transition-all duration-300">
                <div class="aspect-video bg-zinc-100 dark:bg-zinc-950 flex items-center justify-center overflow-hidden border-b border-zinc-200 dark:border-zinc-800">
                    <?php if ($is_iframe): ?>
                        <div class="w-full h-full [&_iframe]:w-full [&_iframe]:h-full object-cover">
                            <?php echo $video_content; ?>
                        </div>
                    <?php elseif ($youtube_id): ?>
                        <iframe 
                            src="https://www.youtube.com/embed/<?php echo htmlspecialchars($youtube_id); ?>" 
                            title="<?php echo htmlspecialchars($video['title']); ?>"
                            class="w-full h-full object-cover"
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                        </iframe>
                    <?php else: ?>
                        <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-rose-500/10 to-pink-600/10 text-rose-500/40">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-[10px] font-bold uppercase tracking-widest">Invalid Stream</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="p-5">
                    <h3 class="font-bold text-zinc-900 dark:text-white mb-2 line-clamp-1 group-hover:text-rose-600 transition-colors"><?php echo htmlspecialchars($video['title']); ?></h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 line-clamp-2 mb-6 h-8"><?php echo htmlspecialchars($video['description']); ?></p>
                    <div class="flex items-center gap-2">
                        <a href="edit_video.php?id=<?php echo $video['id']; ?>" class="flex-grow py-2.5 bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 rounded-xl font-bold text-xs text-center hover:bg-zinc-200 dark:hover:bg-zinc-700 transition">
                            Edit
                        </a>
                        <form action="videos.php" method="post" onsubmit="return confirm('Delete this video?');" class="flex-grow">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $video['id']; ?>">
                            <button type="submit" class="w-full py-2.5 bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 rounded-xl font-bold text-xs hover:bg-rose-100 dark:hover:bg-rose-900/40 transition">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>