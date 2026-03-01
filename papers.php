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

$years = [];
$year_result = $conn->query("SELECT DISTINCT year FROM papers ORDER BY year DESC");
while ($row = $year_result->fetch_assoc()) {
    $years[] = $row['year'];
}

$selected_category = $_GET['category'] ?? null;
$year = $_GET['year'] ?? null;
$search_query = trim($_GET['q'] ?? '');

$papers = get_papers($selected_category, $year, $search_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Papers - Semicolon</title>
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
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Research Papers
                    </h1>
                    <p class="text-sm text-zinc-500 mt-1">Access exam papers and research documents</p>
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
                            <a href="papers.php<?php echo $year ? '?year='.$year : ''; ?>" 
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition <?php echo !$selected_category ? 'bg-teal-50 text-teal-700' : 'text-zinc-600 hover:bg-zinc-100/80 hover:text-zinc-900'; ?>">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 <?php echo !$selected_category ? 'bg-teal-100/50 text-teal-600' : 'text-zinc-400 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100'; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" /></svg>
                                </div>
                                All Papers
                            </a>
                            <?php foreach ($categories as $cat): ?>
                                <a href="papers.php?category=<?php echo urlencode($cat); ?><?php echo $year ? '&year='.$year : ''; ?>" 
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition group <?php echo ($selected_category === $cat) ? 'bg-teal-50 text-teal-700' : 'text-zinc-600 hover:bg-zinc-100/80 hover:text-zinc-900'; ?>">
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 <?php echo ($selected_category === $cat) ? 'bg-teal-100/50 text-teal-600' : 'text-zinc-400 group-hover:text-zinc-500 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100'; ?>">
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
                            <select onchange="window.location.href='papers.php?<?php echo $selected_category ? 'category='.urlencode($selected_category).'&' : ''; ?>year='+this.value"
                                    class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm font-medium text-zinc-700 dark:text-zinc-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition shadow-sm">
                                <option value="">All Years</option>
                                <?php foreach ($years as $y): ?>
                                    <option value="<?php echo $y; ?>" <?php echo ($year == $y) ? 'selected' : ''; ?>><?php echo $y; ?></option>
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
                        <form action="papers.php" method="GET" class="flex-1 relative mb-0">
                            <?php if ($selected_category): ?>
                                <input type="hidden" name="category" value="<?php echo htmlspecialchars($selected_category); ?>">
                            <?php endif; ?>
                            <?php if ($year): ?>
                                <input type="hidden" name="year" value="<?php echo htmlspecialchars($year); ?>">
                            <?php endif; ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" name="q" placeholder="Search papers by title or subject..." value="<?php echo htmlspecialchars($search_query); ?>" class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-full pl-10 pr-4 py-2.5 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-teal-500 transition">
                        </form>
                    </div>

                    <!-- Selected Category Header -->
                    <div class="flex items-center justify-between text-sm text-zinc-500 mb-2 px-1">
                        <span><?php echo $selected_category ? htmlspecialchars($selected_category) . ' Papers' : 'All Papers'; ?><?php echo $year ? " ($year)" : ""; ?></span>
                        <span class="font-medium text-zinc-900 dark:text-white bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 px-2 py-0.5 rounded-md"><?php echo count($papers); ?> Results</span>
                    </div>
                    <?php if (empty($papers)): ?>
                        <div class="text-center py-20 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-100 dark:border-zinc-800 shadow-sm">
                            <div class="w-20 h-20 bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <p class="text-xl text-zinc-500 mb-2">No papers found</p>
                            <p class="text-zinc-400">Try selecting a different filter</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($papers as $paper): ?>
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-100 dark:border-zinc-800 hover:border-teal-200 hover:shadow-xl transition-all duration-300 overflow-hidden group">
                            <!-- Header -->
                            <div class="h-28 bg-gradient-to-br from-teal-500 to-emerald-600 p-6 relative overflow-hidden">
                                <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff08_1px,transparent_1px),linear-gradient(to_bottom,#ffffff08_1px,transparent_1px)] bg-[size:24px_24px]"></div>
                                <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-white/10 rounded-full"></div>
                                <div class="relative z-10 flex items-center justify-between">
                                    <span class="inline-flex items-center px-3 py-1 bg-white/20 backdrop-blur rounded-full text-xs font-medium text-white">
                                        <?php echo htmlspecialchars($paper['category'] ?? $paper['subject']); ?>
                                    </span>
                                    <span class="text-white/80 text-sm font-mono"><?php echo htmlspecialchars($paper['year']); ?></span>
                                </div>
                            </div>
                            
                            <div class="p-6">
                                <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-4 line-clamp-2 group-hover:text-teal-600 transition">
                                    <?php echo htmlspecialchars($paper['title']); ?>
                                </h3>
                                
                                <div class="flex gap-3">
                                    <a href="view.php?token=<?php echo $paper['token']; ?>" 
                                       class="flex-1 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold rounded-xl text-center transition">
                                        View Paper
                                    </a>
                                    <a href="pricing.php" 
                                       class="py-2.5 px-4 border border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 text-zinc-700 dark:text-zinc-300 text-sm font-medium rounded-xl transition flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </a>
                                </div>
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