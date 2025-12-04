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

$subjects = get_distinct_values('subject');
$subject = $_GET['subject'] ?? null;
$books = get_books($subject);
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
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-indigo-200/30 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-1/4 w-80 h-80 bg-purple-200/20 rounded-full blur-3xl"></div>
        </div>
        
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-indigo-600"><?php echo count($books); ?> Books Available</span>
                    </div>
                    <h1 class="text-4xl font-bold text-zinc-900">Library <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Books</span></h1>
                    <p class="text-zinc-500 mt-2">Browse our collection of digital textbooks and references</p>
                </div>
            </div>
            
            <!-- Filter Bar -->
            <div class="mt-8 bg-white rounded-2xl border border-zinc-100 p-4">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2 text-sm text-zinc-500 mr-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filter:
                    </div>
                    <a href="books.php" class="px-4 py-2 rounded-full text-sm font-medium transition <?php echo !$subject ? 'bg-indigo-600 text-white' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200'; ?>">
                        All
                    </a>
                    <?php foreach ($subjects as $s): ?>
                        <a href="books.php?subject=<?php echo urlencode($s); ?>" 
                           class="px-4 py-2 rounded-full text-sm font-medium transition <?php echo ($subject === $s) ? 'bg-indigo-600 text-white' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200'; ?>">
                            <?php echo htmlspecialchars($s); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Books Grid -->
    <section class="pb-20">
        <div class="container mx-auto px-6">
            <?php if (empty($books)): ?>
                <div class="text-center py-20">
                    <div class="w-20 h-20 bg-zinc-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <p class="text-xl text-zinc-500 mb-2">No books found</p>
                    <p class="text-zinc-400">Try selecting a different subject filter</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($books as $book): ?>
                        <div class="bg-white rounded-2xl border border-zinc-100 hover:border-indigo-200 hover:shadow-xl transition-all duration-300 overflow-hidden group">
                            <!-- Cover/Header -->
                            <div class="h-32 bg-gradient-to-br from-indigo-500 to-purple-600 p-6 relative overflow-hidden">
                                <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff08_1px,transparent_1px),linear-gradient(to_bottom,#ffffff08_1px,transparent_1px)] bg-[size:24px_24px]"></div>
                                <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-white/10 rounded-full"></div>
                                <span class="relative z-10 inline-flex items-center px-3 py-1 bg-white/20 backdrop-blur rounded-full text-xs font-medium text-white">
                                    <?php echo htmlspecialchars($book['subject']); ?>
                                </span>
                            </div>
                            
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-xs font-medium text-zinc-400 uppercase tracking-wider"><?php echo htmlspecialchars($book['difficulty'] ?? 'General'); ?></span>
                                </div>
                                
                                <h3 class="text-lg font-bold text-zinc-900 mb-2 line-clamp-2 group-hover:text-indigo-600 transition">
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
                                       class="py-2.5 px-4 border border-zinc-200 hover:border-zinc-300 text-zinc-700 text-sm font-medium rounded-xl transition flex items-center gap-2">
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
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>