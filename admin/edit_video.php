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
<div class="mb-8 px-4 sm:px-0">
    <a href="videos.php" class="inline-flex items-center gap-2 text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 font-medium transition group">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Videos
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 px-4 sm:px-0">
    <!-- Edit Form -->
    <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 p-8 shadow-xl">
        <h2 class="text-xl font-black text-zinc-900 dark:text-white mb-8 tracking-tight">Edit Video Details</h2>
        
        <form action="" method="post" class="space-y-6">
            <input type="hidden" name="action" value="update">
            
            <div>
                <label class="block text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest mb-2 px-1">Title</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($video['title']); ?>" required
                    class="w-full px-5 py-3.5 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl text-zinc-900 dark:text-white focus:ring-2 focus:ring-rose-500 outline-none transition shadow-sm">
            </div>
            
            <div>
                <label class="block text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest mb-2 px-1">Description</label>
                <textarea name="description" rows="4"
                    class="w-full px-5 py-3.5 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl text-zinc-900 dark:text-white focus:ring-2 focus:ring-rose-500 outline-none transition shadow-sm"><?php echo htmlspecialchars($video['description']); ?></textarea>
            </div>
            
            <div>
                <label class="block text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest mb-2 px-1">YouTube URL / Embed Code</label>
                <input type="text" name="youtube_url" value="<?php echo htmlspecialchars($video['youtube_url']); ?>" required
                    class="w-full px-5 py-3.5 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl text-zinc-900 dark:text-white font-mono text-sm focus:ring-2 focus:ring-rose-500 outline-none transition shadow-sm">
                <p class="text-[10px] text-zinc-400 dark:text-zinc-500 mt-2 px-1">Supports full iframe tags or clean YouTube links.</p>
            </div>
            
            <div class="flex items-center justify-end gap-10 pt-8 mt-4 border-t border-zinc-100 dark:border-zinc-800">
                <a href="videos.php" class="text-xs font-black uppercase text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition">
                    Discard Changes
                </a>
                <button type="submit" class="px-8 py-4 bg-rose-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-rose-700 transition shadow-lg shadow-rose-600/20 active:scale-[0.98]">
                    Update Video
                </button>
            </div>
        </form>
    </div>
    
    <!-- Video Preview -->
    <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 p-8 shadow-xl flex flex-col">
        <h2 class="text-xl font-black text-zinc-900 dark:text-white mb-8 tracking-tight">Live Preview</h2>
        <div class="aspect-video bg-zinc-100 dark:bg-zinc-950 rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800 shadow-inner flex-grow flex items-center justify-center">
            <?php if ($youtube_id || strpos($video['youtube_url'], '<iframe') !== false): ?>
                <div class="w-full h-full [&_iframe]:w-full [&_iframe]:h-full object-cover">
                    <?php 
                    if (strpos($video['youtube_url'], '<iframe') !== false) {
                        echo $video['youtube_url'];
                    } else {
                        ?>
                        <iframe 
                            src="https://www.youtube.com/embed/<?php echo htmlspecialchars($youtube_id); ?>" 
                            title="<?php echo htmlspecialchars($video['title']); ?>"
                            class="w-full h-full"
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                        </iframe>
                        <?php
                    }
                    ?>
                </div>
            <?php else: ?>
                <div class="flex flex-col items-center justify-center text-zinc-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 022 2z" /></svg>
                    <p class="font-bold text-xs uppercase tracking-widest">No Stream Detected</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
