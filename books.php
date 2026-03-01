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

$selected_category = $_GET['category'] ?? null;
$search_query = trim($_GET['q'] ?? '');

$books = get_books($selected_category, null, $search_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books - Semicolon</title>
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
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        Library Books
                    </h1>
                    <p class="text-sm text-zinc-500 mt-1">Browse our collection of digital textbooks and references</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-8 bg-[#FAFAFA] dark:bg-zinc-950">
        <div class="container mx-auto px-6 max-w-7xl">
            <!-- 3 Column Grid (like Community) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <!-- Left Sidebar (Topics) -->
                <div class="lg:col-span-3 hidden lg:block">
                    <div class="sticky top-20">
                        <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-3 px-3">Filter by Category</h3>
                        <div class="space-y-0.5 max-h-[75vh] overflow-y-auto custom-scrollbar pr-2">
                            <a href="books.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition <?php echo !$selected_category ? 'bg-indigo-50 text-indigo-700' : 'text-zinc-600 hover:bg-zinc-100/80 hover:text-zinc-900'; ?>">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 <?php echo !$selected_category ? 'bg-indigo-100/50 text-indigo-600' : 'text-zinc-400 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100'; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" /></svg>
                                </div>
                                All Books
                            </a>
                            <?php foreach ($categories as $cat): ?>
                                <a href="books.php?category=<?php echo urlencode($cat); ?>" 
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition group <?php echo ($selected_category === $cat) ? 'bg-indigo-50 text-indigo-700' : 'text-zinc-600 hover:bg-zinc-100/80 hover:text-zinc-900'; ?>">
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 <?php echo ($selected_category === $cat) ? 'bg-indigo-100/50 text-indigo-600' : 'text-zinc-400 group-hover:text-zinc-500 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100'; ?>">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <?php echo $category_icons[$cat] ?? '<path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/>'; ?>
                                        </svg>
                                    </div>
                                    <span class="truncate"><?php echo htmlspecialchars($cat); ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Middle Content Column -->
                <div class="lg:col-span-9 flex flex-col gap-4">
                    
                    <!-- Search Bar -->
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-3 shadow-sm flex items-center gap-3">
                        <form action="books.php" method="GET" class="flex-1 relative mb-0">
                            <?php if ($selected_category): ?>
                                <input type="hidden" name="category" value="<?php echo htmlspecialchars($selected_category); ?>">
                            <?php endif; ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" name="q" placeholder="Search books by title, author, or description..." value="<?php echo htmlspecialchars($search_query); ?>" class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-full pl-10 pr-4 py-2.5 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-indigo-500 transition">
                        </form>
                    </div>

                    <!-- Selected Category Header -->
                    <div class="flex items-center justify-between text-sm text-zinc-500 mb-2 px-1">
                        <span><?php echo $selected_category ? htmlspecialchars($selected_category) . ' Books' : 'All Books'; ?></span>
                        <span class="font-medium text-zinc-900 dark:text-white bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 px-2 py-0.5 rounded-md"><?php echo count($books); ?> Results</span>
                    </div>
                    <?php if (empty($books)): ?>
                        <div class="text-center py-20 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-100 dark:border-zinc-800 shadow-sm">
                            <div class="w-20 h-20 bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <p class="text-xl text-zinc-500 mb-2">No books found</p>
                            <p class="text-zinc-400">Try selecting a different subject filter</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($books as $book): ?>
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-100 dark:border-zinc-800 hover:border-indigo-200 hover:shadow-xl transition-all duration-300 overflow-hidden group">
                            <!-- Cover/Header -->
                            <div class="h-32 bg-gradient-to-br from-indigo-500 to-purple-600 p-6 relative overflow-hidden">
                                <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff08_1px,transparent_1px),linear-gradient(to_bottom,#ffffff08_1px,transparent_1px)] bg-[size:24px_24px]"></div>
                                <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-white/10 rounded-full"></div>
                                <span class="relative z-10 inline-flex items-center px-3 py-1 bg-white/20 backdrop-blur rounded-full text-xs font-medium text-white">
                                    <?php echo htmlspecialchars($book['category']); ?>
                                </span>
                            </div>
                            
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-xs font-medium text-zinc-400 uppercase tracking-wider"><?php echo htmlspecialchars($book['difficulty'] ?? 'General'); ?></span>
                                </div>
                                
                                <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-2 line-clamp-2 group-hover:text-indigo-600 transition">
                                    <?php echo htmlspecialchars($book['title']); ?>
                                </h3>
                                <p class="text-sm text-indigo-600 font-medium mb-3">
                                    by <?php echo htmlspecialchars($book['author']); ?>
                                </p>
                                <p class="text-zinc-500 text-sm line-clamp-2 mb-6">
                                    <?php echo htmlspecialchars($book['description']); ?>
                                </p>
                                
                                <div class="flex gap-3">
                                    <a href="view.php?token=<?php echo $book['token']; ?>" 
                                       class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl text-center transition">
                                        Read Now
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