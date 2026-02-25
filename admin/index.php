<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db.php';

// Fetch statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_books = $conn->query("SELECT COUNT(*) as count FROM books")->fetch_assoc()['count'];
$total_papers = $conn->query("SELECT COUNT(*) as count FROM papers")->fetch_assoc()['count'];
$total_videos = $conn->query("SELECT COUNT(*) as count FROM videos")->fetch_assoc()['count'];
$total_posts = $conn->query("SELECT COUNT(*) as count FROM community_posts")->fetch_assoc()['count'];

$pending_requests = 0;
$result = $conn->query("SELECT COUNT(*) as count FROM material_requests WHERE status = 'pending'");
if ($result) {
    $pending_requests = $result->fetch_assoc()['count'];
}

// Fetch recent content
$recent_books = $conn->query("SELECT id, title, author, created_at FROM books ORDER BY created_at DESC LIMIT 4")->fetch_all(MYSQLI_ASSOC);
$recent_papers = $conn->query("SELECT id, title, subject, year, created_at FROM papers ORDER BY created_at DESC LIMIT 4")->fetch_all(MYSQLI_ASSOC);
$recent_videos = $conn->query("SELECT id, title, created_at FROM videos ORDER BY created_at DESC LIMIT 4")->fetch_all(MYSQLI_ASSOC);
$recent_users = $conn->query("SELECT id, username, role, avatar_url, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$recent_posts = $conn->query("SELECT cp.id, cp.title, cp.created_at, u.username FROM community_posts cp LEFT JOIN users u ON cp.user_id = u.id ORDER BY cp.created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>

<!-- Welcome & System Status -->
<div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-8">
    <div>
        <h2 class="text-2xl font-bold text-zinc-900">Welcome Back, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>!</h2>
        <p class="text-zinc-500">Here's what's happening on <span class="font-semibold text-indigo-600">Semicolon</span> today.</p>
    </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6 mb-8">
    <div class="group bg-white p-6 rounded-3xl border border-zinc-200 hover:border-indigo-500 hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-300">
        <div class="p-3 bg-indigo-50 rounded-2xl text-indigo-600 w-fit mb-4 group-hover:scale-110 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </div>
        <p class="text-sm font-semibold text-zinc-400 uppercase tracking-wider mb-1">Users</p>
        <p class="text-3xl font-black text-zinc-900"><?php echo number_format($total_users); ?></p>
    </div>

    <div class="group bg-white p-6 rounded-3xl border border-zinc-200 hover:border-teal-500 hover:shadow-xl hover:shadow-teal-500/5 transition-all duration-300">
        <div class="p-3 bg-teal-50 rounded-2xl text-teal-600 w-fit mb-4 group-hover:scale-110 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        </div>
        <p class="text-sm font-semibold text-zinc-400 uppercase tracking-wider mb-1">Books</p>
        <p class="text-3xl font-black text-zinc-900"><?php echo number_format($total_books); ?></p>
    </div>

    <div class="group bg-white p-6 rounded-3xl border border-zinc-200 hover:border-blue-500 hover:shadow-xl hover:shadow-blue-500/5 transition-all duration-300">
        <div class="p-3 bg-blue-50 rounded-2xl text-blue-600 w-fit mb-4 group-hover:scale-110 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>
        <p class="text-sm font-semibold text-zinc-400 uppercase tracking-wider mb-1">Papers</p>
        <p class="text-3xl font-black text-zinc-900"><?php echo number_format($total_papers); ?></p>
    </div>

    <div class="group bg-white p-6 rounded-3xl border border-zinc-200 hover:border-rose-500 hover:shadow-xl hover:shadow-rose-500/5 transition-all duration-300">
        <div class="p-3 bg-rose-50 rounded-2xl text-rose-600 w-fit mb-4 group-hover:scale-110 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <p class="text-sm font-semibold text-zinc-400 uppercase tracking-wider mb-1">Videos</p>
        <p class="text-3xl font-black text-zinc-900"><?php echo number_format($total_videos); ?></p>
    </div>

    <div class="group bg-white p-6 rounded-3xl border border-zinc-200 hover:border-amber-500 hover:shadow-xl hover:shadow-amber-500/5 transition-all duration-300">
        <div class="p-3 bg-amber-50 rounded-2xl text-amber-600 w-fit mb-4 group-hover:scale-110 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
            </svg>
        </div>
        <p class="text-sm font-semibold text-zinc-400 uppercase tracking-wider mb-1">Posts</p>
        <p class="text-3xl font-black text-zinc-900"><?php echo number_format($total_posts); ?></p>
    </div>

    <div class="group bg-white p-6 rounded-3xl border border-zinc-200 hover:border-yellow-500 hover:shadow-xl hover:shadow-yellow-500/5 transition-all duration-300">
        <div class="p-3 bg-yellow-50 rounded-2xl text-yellow-600 w-fit mb-4 group-hover:scale-110 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <p class="text-sm font-semibold text-zinc-400 uppercase tracking-wider mb-1">Requests</p>
        <p class="text-3xl font-black text-zinc-900"><?php echo number_format($pending_requests); ?></p>
    </div>
</div>

<!-- Quick Action HUB -->
<div class="mb-10">
    <div class="flex items-center gap-2 mb-6">
        <div class="w-1.5 h-6 bg-indigo-600 rounded-full"></div>
        <h2 class="text-xl font-bold text-zinc-900">Creation HUB</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="books.php?add=1" class="group relative p-8 rounded-[2rem] bg-indigo-600 text-white overflow-hidden shadow-xl shadow-indigo-600/20 hover:-translate-y-1 transition-all duration-300">
            <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:scale-125 transition-transform duration-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <div class="relative z-10">
                <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2">New Book</h3>
                <p class="text-indigo-100/80 text-sm leading-relaxed">Expand the library with new textbooks.</p>
            </div>
        </a>

        <a href="papers.php?add=1" class="group relative p-8 rounded-[2rem] bg-teal-600 text-white overflow-hidden shadow-xl shadow-teal-600/20 hover:-translate-y-1 transition-all duration-300">
            <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:scale-125 transition-transform duration-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div class="relative z-10">
                <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2">New Paper</h3>
                <p class="text-teal-100/80 text-sm leading-relaxed">Add research or examination material.</p>
            </div>
        </a>

        <a href="videos.php?add=1" class="group relative p-8 rounded-[2rem] bg-rose-600 text-white overflow-hidden shadow-xl shadow-rose-600/20 hover:-translate-y-1 transition-all duration-300">
            <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:scale-125 transition-transform duration-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                </svg>
            </div>
            <div class="relative z-10">
                <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2">New Video</h3>
                <p class="text-rose-100/80 text-sm leading-relaxed">Embed fresh video tutorials easily.</p>
            </div>
        </a>
    </div>
</div>

<!-- Unified Content Stream -->
<div class="bg-white rounded-[2.5rem] border border-zinc-200 shadow-sm overflow-hidden mb-8">
    <div class="px-8 py-6 border-b border-zinc-100 flex items-center justify-between bg-zinc-50/50">
        <h3 class="text-lg font-bold text-zinc-900 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            Recent Resources
        </h3>
    </div>
    <div class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Recent Books Sub-grid -->
            <div class="space-y-3">
                <p class="px-4 text-xs font-bold text-zinc-400 uppercase tracking-widest">Latest Books</p>
                <?php foreach ($recent_books as $book): ?>
                <div class="flex items-center justify-between p-4 bg-zinc-50 rounded-2xl border border-transparent hover:border-indigo-100 hover:bg-white transition-all group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center font-bold text-sm group-hover:bg-indigo-600 group-hover:text-white transition-colors">B</div>
                        <div>
                            <p class="font-bold text-zinc-900 text-sm line-clamp-1"><?php echo htmlspecialchars($book['title']); ?></p>
                            <p class="text-xs text-zinc-500"><?php echo htmlspecialchars($book['author']); ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <!-- Recent Papers Sub-grid -->
            <div class="space-y-3">
                <p class="px-4 text-xs font-bold text-zinc-400 uppercase tracking-widest">Latest Papers</p>
                <?php foreach ($recent_papers as $paper): ?>
                <div class="flex items-center justify-between p-4 bg-zinc-50 rounded-2xl border border-transparent hover:border-teal-100 hover:bg-white transition-all group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-teal-100 text-teal-600 rounded-xl flex items-center justify-center font-bold text-sm group-hover:bg-teal-600 group-hover:text-white transition-colors">P</div>
                        <div>
                            <p class="font-bold text-zinc-900 text-sm line-clamp-1"><?php echo htmlspecialchars($paper['title']); ?></p>
                            <p class="text-xs text-zinc-500"><?php echo htmlspecialchars($paper['subject']); ?> • <?php echo $paper['year']; ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <!-- Recent Videos Sub-grid -->
            <div class="space-y-3">
                <p class="px-4 text-xs font-bold text-zinc-400 uppercase tracking-widest">Latest Videos</p>
                <?php foreach ($recent_videos as $video): ?>
                <div class="flex items-center justify-between p-4 bg-zinc-50 rounded-2xl border border-transparent hover:border-rose-100 hover:bg-white transition-all group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center font-bold text-sm group-hover:bg-rose-600 group-hover:text-white transition-colors">V</div>
                        <div>
                            <p class="font-bold text-zinc-900 text-sm line-clamp-1"><?php echo htmlspecialchars($video['title']); ?></p>
                            <p class="text-xs text-zinc-500">Video Lesson</p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="px-8 py-4 bg-zinc-50 border-t border-zinc-100 flex justify-center gap-6">
        <a href="books.php" class="text-sm font-bold text-indigo-600 hover:text-indigo-700 transition">Manage Books →</a>
        <a href="papers.php" class="text-sm font-bold text-teal-600 hover:text-teal-700 transition">Manage Papers →</a>
        <a href="videos.php" class="text-sm font-bold text-rose-600 hover:text-rose-700 transition">Manage Videos →</a>
    </div>
</div>

<!-- Main Activity Layout -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    <!-- Left Column: Primary Content -->
    <div class="lg:col-span-8 space-y-8">

        <!-- Community Moderation Feed -->
        <div class="bg-white rounded-[2.5rem] border border-zinc-200 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-zinc-100 bg-amber-50/30 flex items-center justify-between">
                <h3 class="text-lg font-bold text-zinc-900 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                    </svg>
                    Community Activity
                </h3>
                <a href="community.php" class="text-xs font-bold text-amber-600 uppercase tracking-widest hover:underline">Moderate</a>
            </div>
            <div class="divide-y divide-zinc-100">
                <?php foreach ($recent_posts as $post): ?>
                <div class="px-8 py-5 flex items-center justify-between hover:bg-zinc-50 transition drop-shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-zinc-100 rounded-full flex items-center justify-center font-bold text-xs text-zinc-500 border-2 border-white shadow-sm">
                            <?php echo strtoupper(substr($post['username'] ?? 'A', 0, 1)); ?>
                        </div>
                        <div>
                            <p class="font-bold text-zinc-900 text-sm"><?php echo htmlspecialchars($post['title']); ?></p>
                            <p class="text-xs text-zinc-500">By <span class="font-semibold text-zinc-700"><?php echo htmlspecialchars($post['username'] ?? 'Anonymous'); ?></span> • <?php echo date('M d, h:ia', strtotime($post['created_at'])); ?></p>
                        </div>
                    </div>
                    <a href="../community_post_detail.php?id=<?php echo $post['id']; ?>" target="_blank" class="p-2 text-zinc-400 hover:text-indigo-600 transition" aria-label="View post">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>

        </div>
        <!-- Recent Search History (Placeholder) -->
        <div class="bg-indigo-600 rounded-[2.5rem] p-8 text-white">
            <h3 class="font-bold mb-4">Management Tip</h3>
            <p class="text-indigo-100 text-sm leading-relaxed mb-6">Regularly check community posts to ensure they follow guidelines and provide value to members.</p>
            <div class="h-px bg-white/20 mb-6"></div>
            <a href="community.php" class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest hover:gap-3 transition-all">Moderate Community →</a>
        </div>
    </div>

    <!-- Right Column: Secondary Info -->
    <div class="lg:col-span-4 space-y-8">
        <!-- New Arrivals (Users) -->
        <div class="bg-white rounded-[2.5rem] border border-zinc-200 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-zinc-100 bg-zinc-50/50">
                <h3 class="font-bold text-zinc-900">New Members</h3>
            </div>
            <div class="p-6 space-y-4">
                <?php foreach ($recent_users as $user): ?>
                    <div class="flex items-center gap-4 group">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 border-2 border-white shadow-sm flex items-center justify-center overflow-hidden shrink-0 group-hover:border-indigo-200 transition-colors">
                            <?php if (!empty($user['avatar_url'])): ?>
                                <img src="../<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="" class="w-full h-full object-cover">
                            <?php else: ?>
                                <span class="font-black text-indigo-600"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-zinc-900 text-sm truncate"><?php echo htmlspecialchars($user['username']); ?></p>
                            <p class="text-xs text-zinc-500"><?php echo $user['role'] === 'admin' ? '🛡️ Admin' : '👤 Member'; ?></p>
                        </div>
                        <div class="text-[10px] font-bold text-zinc-300 uppercase"><?php echo date('M d', strtotime($user['created_at'])); ?></div>
                    </div>
                <?php endforeach; ?>
                <a href="users.php" class="block w-full text-center py-3 bg-zinc-900 text-white rounded-2xl text-xs font-bold hover:bg-zinc-800 transition shadow-lg shadow-zinc-900/10">Manage Users</a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
