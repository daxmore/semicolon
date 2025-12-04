<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

// Redirect admin users to the admin dashboard
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: admin/index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user = get_user_by_id($user_id);
if (!$user) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
}

// Fetch some data for the dashboard
$total_papers = get_count('papers');
$total_books = get_count('books');
$total_videos = get_count('videos');
$history = get_user_history($user_id, 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Semicolon</title>
    <link href="assets/css/index.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FAFAFA',
                        secondary: '#F4F4F5',
                        accent: '#6366F1',
                    },
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

    <!-- Dashboard Hero -->
    <section class="relative pt-24 pb-16 overflow-hidden">
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-indigo-200/30 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-1/4 w-80 h-80 bg-teal-200/30 rounded-full blur-3xl"></div>
        </div>

        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex flex-col md:flex-row items-center gap-5 text-center md:text-left">
                    <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-2xl font-bold text-white shadow-lg shadow-indigo-500/25">
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </div>
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-zinc-900">Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</h1>
                        <p class="text-zinc-500 flex items-center justify-center md:justify-start gap-2 mt-1">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            <?php echo ucfirst($user['role'] ?? 'User'); ?> Account
                        </p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="auth/logout.php" class="px-5 py-2.5 border border-zinc-200 rounded-xl text-zinc-700 hover:bg-zinc-50 transition font-medium">
                        Logout
                    </a>
                    <a href="pricing.php" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition font-medium">
                        Upgrade to Pro
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-8">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Total Books -->
                <div class="bg-white rounded-2xl p-6 border border-zinc-100 hover:shadow-lg hover:border-indigo-200 transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-zinc-500 text-sm font-medium">Total Books</p>
                            <p class="text-4xl font-bold text-zinc-900 mt-1"><?php echo $total_books; ?></p>
                        </div>
                        <div class="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                    </div>
                    <a href="books.php" class="inline-flex items-center gap-1 text-sm text-indigo-600 font-medium mt-4 hover:gap-2 transition-all">
                        Browse Books
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>

                <!-- Total Papers -->
                <div class="bg-white rounded-2xl p-6 border border-zinc-100 hover:shadow-lg hover:border-teal-200 transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-zinc-500 text-sm font-medium">Total Papers</p>
                            <p class="text-4xl font-bold text-zinc-900 mt-1"><?php echo $total_papers; ?></p>
                        </div>
                        <div class="w-14 h-14 bg-teal-100 rounded-2xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                    <a href="papers.php" class="inline-flex items-center gap-1 text-sm text-teal-600 font-medium mt-4 hover:gap-2 transition-all">
                        Browse Papers
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>

                <!-- Total Videos -->
                <div class="bg-white rounded-2xl p-6 border border-zinc-100 hover:shadow-lg hover:border-rose-200 transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-zinc-500 text-sm font-medium">Total Videos</p>
                            <p class="text-4xl font-bold text-zinc-900 mt-1"><?php echo $total_videos; ?></p>
                        </div>
                        <div class="w-14 h-14 bg-rose-100 rounded-2xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <a href="videos.php" class="inline-flex items-center gap-1 text-sm text-rose-600 font-medium mt-4 hover:gap-2 transition-all">
                        Watch Videos
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-8 pb-20">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Recent Activity -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl border border-zinc-100 overflow-hidden">
                        <div class="p-6 border-b border-zinc-100">
                            <h2 class="text-xl font-bold text-zinc-900 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Recent Activity
                            </h2>
                        </div>
                        
                        <?php if (empty($history)): ?>
                            <div class="p-12 text-center">
                                <div class="w-16 h-16 bg-zinc-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <p class="text-zinc-500 mb-2">No activity yet</p>
                                <a href="books.php" class="text-indigo-600 hover:text-indigo-700 font-medium text-sm">Start exploring resources →</a>
                            </div>
                        <?php else: ?>
                            <div class="divide-y divide-zinc-100">
                                <?php foreach ($history as $item): ?>
                                    <a href="view.php?token=<?php echo $item['token'] ?? ''; ?>" class="flex items-center gap-4 p-4 hover:bg-zinc-50 transition">
                                        <?php 
                                        $iconBg = $item['resource_type'] === 'book' ? 'bg-indigo-100 text-indigo-600' : 
                                                  ($item['resource_type'] === 'paper' ? 'bg-teal-100 text-teal-600' : 'bg-rose-100 text-rose-600');
                                        ?>
                                        <div class="w-10 h-10 <?php echo $iconBg; ?> rounded-xl flex items-center justify-center flex-shrink-0">
                                            <?php if ($item['resource_type'] === 'book'): ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                </svg>
                                            <?php elseif ($item['resource_type'] === 'paper'): ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            <?php else: ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-zinc-900 truncate"><?php echo htmlspecialchars($item['title'] ?? 'Unknown Resource'); ?></p>
                                            <p class="text-sm text-zinc-500"><?php echo ucfirst($item['resource_type']); ?> • <?php echo date('M d, Y', strtotime($item['viewed_at'])); ?></p>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Links -->
                    <div class="bg-white rounded-2xl border border-zinc-100 p-6">
                        <h3 class="text-lg font-bold text-zinc-900 mb-4">Quick Links</h3>
                        <div class="space-y-3">
                            <a href="books.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-indigo-50 transition group">
                                <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-zinc-900">Books</p>
                                    <p class="text-xs text-zinc-500">Browse library</p>
                                </div>
                            </a>
                            <a href="papers.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-teal-50 transition group">
                                <div class="w-10 h-10 bg-teal-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-zinc-900">Papers</p>
                                    <p class="text-xs text-zinc-500">Research & exams</p>
                                </div>
                            </a>
                            <a href="videos.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-rose-50 transition group">
                                <div class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-zinc-900">Videos</p>
                                    <p class="text-xs text-zinc-500">Tutorials</p>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Account Status -->
                    <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl p-6 text-white">
                        <h3 class="font-bold mb-4 text-white">Account Status</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-indigo-200">Plan</span>
                                <span class="font-medium">Free Tier</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-indigo-200">Member Since</span>
                                <span class="font-medium"><?php echo date('M Y', strtotime($user['created_at'] ?? 'now')); ?></span>
                            </div>
                        </div>
                        <a href="pricing.php" class="block w-full text-center py-3 bg-white text-indigo-600 font-semibold rounded-xl mt-4 hover:bg-indigo-50 transition">
                            Upgrade to Pro
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>