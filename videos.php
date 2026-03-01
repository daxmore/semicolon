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

$categories = get_system_categories();
$category_icons = get_category_icons();

// Fetch distinctive years
$years = [];
$year_result = $conn->query("SELECT DISTINCT YEAR(created_at) as year FROM videos ORDER BY year DESC");
while ($row = $year_result->fetch_assoc()) {
    if ($row['year']) $years[] = $row['year'];
}

$selected_category = $_GET['category'] ?? null;
$year = $_GET['year'] ?? null;
$search_query = trim($_GET['q'] ?? '');

$videos = get_videos($selected_category, $year, $search_query);
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
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <script src="/Semicolon/assets/js/theme.js"></script>
</head>
<body class="antialiased bg-[#FAFAFA] dark:bg-zinc-950 dark:text-zinc-200">
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="relative pt-6 pb-4 bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
        <div class="container mx-auto px-6 max-w-7xl">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Video Tutorials
                    </h1>
                    <p class="text-sm text-zinc-500 mt-1">Learn with our curated video tutorials</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-8 bg-[#FAFAFA] dark:bg-zinc-950">
        <div class="container mx-auto px-6 max-w-7xl">
            <!-- 3 Column Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <!-- Left Sidebar (Topics & Filters) -->
                <div class="lg:col-span-3 hidden lg:block">
                    <div class="sticky top-20">
                        <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-3 px-3">Filter by Category</h3>
                        <div class="space-y-0.5 max-h-[50vh] overflow-y-auto custom-scrollbar pr-2 mb-6">
                            <a href="videos.php<?php echo $year ? '?year='.$year : ''; ?>" 
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition <?php echo !$selected_category ? 'bg-rose-50 text-rose-700' : 'text-zinc-600 hover:bg-zinc-100/80 hover:text-zinc-900'; ?>">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 <?php echo !$selected_category ? 'bg-rose-100/50 text-rose-600' : 'text-zinc-400 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100'; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" /></svg>
                                </div>
                                All Videos
                            </a>
                            <?php foreach ($categories as $cat): ?>
                                <a href="videos.php?category=<?php echo urlencode($cat); ?><?php echo $year ? '&year='.$year : ''; ?>" 
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition group <?php echo ($selected_category === $cat) ? 'bg-rose-50 text-rose-700' : 'text-zinc-600 hover:bg-zinc-100/80 hover:text-zinc-900'; ?>">
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 <?php echo ($selected_category === $cat) ? 'bg-rose-100/50 text-rose-600' : 'text-zinc-400 group-hover:text-zinc-500 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100'; ?>">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <?php echo $category_icons[$cat] ?? '<path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/>'; ?>
                                        </svg>
                                    </div>
                                    <span class="truncate"><?php echo htmlspecialchars($cat); ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <!-- Year Filter (Inside Sidebar) -->
                        <?php if (!empty($years)): ?>
                        <div class="px-3 pb-4">
                            <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-3">Filter by Year</h3>
                            <select onchange="window.location.href='videos.php?<?php echo $selected_category ? 'category='.urlencode($selected_category).'&' : ''; ?>year='+this.value"
                                    class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm font-medium text-zinc-700 dark:text-zinc-300 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 outline-none transition shadow-sm">
                                <option value="">All Years</option>
                                <?php foreach ($years as $y): ?>
                                    <option value="<?php echo htmlspecialchars($y); ?>" <?php echo ($year == $y) ? 'selected' : ''; ?>><?php echo htmlspecialchars($y); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Middle Content Column -->
                <div class="lg:col-span-9 flex flex-col gap-4">
                    
                    <!-- Search Bar -->
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-3 shadow-sm flex items-center gap-3">
                        <form action="videos.php" method="GET" class="flex-1 relative mb-0">
                            <?php if ($selected_category): ?>
                                <input type="hidden" name="category" value="<?php echo htmlspecialchars($selected_category); ?>">
                            <?php endif; ?>
                            <?php if ($year): ?>
                                <input type="hidden" name="year" value="<?php echo htmlspecialchars($year); ?>">
                            <?php endif; ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" name="q" placeholder="Search videos by title or description..." value="<?php echo htmlspecialchars($search_query); ?>" class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-full pl-10 pr-4 py-2.5 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-rose-500 transition">
                        </form>
                    </div>

                    <!-- Selected Category Header -->
                    <div class="flex items-center justify-between text-sm text-zinc-500 mb-2 px-1">
                        <span><?php echo $selected_category ? htmlspecialchars($selected_category) . ' Videos' : 'All Videos'; ?><?php echo $year ? " ($year)" : ""; ?></span>
                        <span class="font-medium text-zinc-900 dark:text-white bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 px-2 py-0.5 rounded-md"><?php echo count($videos); ?> Results</span>
                    </div>
                    <?php if (empty($videos)): ?>
                        <div class="text-center py-20 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-100 dark:border-zinc-800 shadow-sm">
                            <div class="w-20 h-20 bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-xl text-zinc-500 mb-2">No videos found</p>
                            <?php if ($year): ?>
                                <p class="text-zinc-400">Try selecting a different year</p>
                            <?php else: ?>
                                <p class="text-zinc-400">Check back later for new content</p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($videos as $video): 
                        $video_content = $video['youtube_url'];
                        $is_iframe = (strpos(trim($video_content), '<iframe') === 0);
                        $youtube_id = $is_iframe ? false : get_youtube_id($video_content);
                    ?>
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-100 dark:border-zinc-800 hover:border-rose-200 hover:shadow-xl transition-all duration-300 overflow-hidden">
                            <!-- YouTube Embed -->
                            <div class="aspect-video bg-zinc-900 dark:bg-zinc-950 flex items-center justify-center overflow-hidden">
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
                                <div class="mb-2">
                                    <span class="inline-flex items-center px-2 py-0.5 bg-rose-100/50 text-rose-700 border border-rose-200/60 rounded text-[10px] font-bold uppercase tracking-wider">
                                        <?php echo htmlspecialchars($video['category']); ?>
                                    </span>
                                </div>
                                <h3 class="font-bold text-zinc-900 dark:text-white mb-2 line-clamp-2">
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
            </div>
        </div>
    </section>

    <style>
    /* Required to override default scrollbar width for the long category list */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #E4E4E7;
        border-radius: 20px;
    }
    </style>

    <?php include 'includes/footer.php'; ?>
</body>
</html>