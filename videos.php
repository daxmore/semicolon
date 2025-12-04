<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: admin/index.php');
    exit();
}
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Fetch videos with optional search
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM videos WHERE 1=1";
if ($search) {
    $sql .= " AND (title LIKE ? OR description LIKE ?)";
}
$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if ($search) {
    $searchParam = '%' . $search . '%';
    $stmt->bind_param('ss', $searchParam, $searchParam);
}
$stmt->execute();
$videos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videos - Semicolon</title>
    <link href="assets/css/index.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="antialiased bg-[#FAFAFA]">
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="relative pt-10 pb-6 overflow-hidden">
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-rose-200/30 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-1/4 w-80 h-80 bg-pink-200/20 rounded-full blur-3xl"></div>
        </div>
        
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-rose-600"><?php echo count($videos); ?> Videos Available</span>
                    </div>
                    <h1 class="text-4xl font-bold text-zinc-900">Video <span class="bg-gradient-to-r from-rose-600 to-pink-600 bg-clip-text text-transparent">Tutorials</span></h1>
                    <p class="text-zinc-500 mt-2">Learn with our curated video tutorials</p>
                </div>
            </div>
            
            <!-- Search Bar -->
            <div class="mt-8 bg-white rounded-2xl border border-zinc-100 p-4">
                <form action="videos.php" method="get" class="flex items-center gap-4">
                    <div class="flex items-center gap-2 text-sm text-zinc-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search videos by title or description..."
                           class="flex-1 px-4 py-2 bg-zinc-50 border-0 rounded-xl text-zinc-700 placeholder-zinc-400 focus:ring-2 focus:ring-rose-500 focus:bg-white transition">
                    <button type="submit" class="px-6 py-2 bg-rose-600 text-white rounded-xl font-medium hover:bg-rose-700 transition">
                        Search
                    </button>
                    <?php if ($search): ?>
                        <a href="videos.php" class="px-4 py-2 text-zinc-500 hover:text-zinc-700 transition">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </section>

    <!-- Videos Grid -->
    <section class="pb-20">
        <div class="container mx-auto px-6">
            <?php if (empty($videos)): ?>
                <div class="text-center py-20">
                    <div class="w-20 h-20 bg-zinc-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-xl text-zinc-500 mb-2">No videos found</p>
                    <?php if ($search): ?>
                        <p class="text-zinc-400">Try a different search term</p>
                    <?php else: ?>
                        <p class="text-zinc-400">Check back later for new content</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($videos as $video): 
                        $youtube_id = get_youtube_id($video['youtube_url']);
                    ?>
                        <div class="bg-white rounded-2xl border border-zinc-100 hover:border-rose-200 hover:shadow-xl transition-all duration-300 overflow-hidden">
                            <!-- YouTube Embed -->
                            <div class="aspect-video bg-zinc-900">
                                <?php if ($youtube_id): ?>
                                    <iframe 
                                        src="https://www.youtube.com/embed/<?php echo htmlspecialchars($youtube_id); ?>" 
                                        title="<?php echo htmlspecialchars($video['title']); ?>"
                                        class="w-full h-full"
                                        frameborder="0" 
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                        allowfullscreen>
                                    </iframe>
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-rose-500 to-pink-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="p-5">
                                <h3 class="font-bold text-zinc-900 mb-2 line-clamp-2">
                                    <?php echo htmlspecialchars($video['title']); ?>
                                </h3>
                                <p class="text-zinc-500 text-sm line-clamp-2">
                                    <?php echo htmlspecialchars($video['description']); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>