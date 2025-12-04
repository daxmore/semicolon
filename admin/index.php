<?php
require_once '../includes/db.php';

// Fetch statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_books = $conn->query("SELECT COUNT(*) as count FROM books")->fetch_assoc()['count'];
$total_papers = $conn->query("SELECT COUNT(*) as count FROM papers")->fetch_assoc()['count'];
$total_videos = $conn->query("SELECT COUNT(*) as count FROM videos")->fetch_assoc()['count'];
$pending_requests = 0;
$result = $conn->query("SELECT COUNT(*) as count FROM material_requests WHERE status = 'pending'");
if ($result) {
    $pending_requests = $result->fetch_assoc()['count'];
}

// Fetch recent content
$recent_books = $conn->query("SELECT id, title, author, created_at FROM books ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$recent_papers = $conn->query("SELECT id, title, subject, year, created_at FROM papers ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$recent_videos = $conn->query("SELECT id, title, created_at FROM videos ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$recent_users = $conn->query("SELECT id, username, role, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
    <div class="bg-white p-6 rounded-2xl border border-zinc-200 hover:shadow-lg transition">
        <div class="flex items-center gap-4 mb-4">
            <div class="p-3 bg-indigo-100 rounded-xl text-indigo-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <span class="text-sm font-medium text-zinc-500">Total Users</span>
        </div>
        <p class="text-3xl font-bold text-zinc-900"><?php echo $total_users; ?></p>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-zinc-200 hover:shadow-lg transition">
        <div class="flex items-center gap-4 mb-4">
            <div class="p-3 bg-teal-100 rounded-xl text-teal-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <span class="text-sm font-medium text-zinc-500">Books</span>
        </div>
        <p class="text-3xl font-bold text-zinc-900"><?php echo $total_books; ?></p>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-zinc-200 hover:shadow-lg transition">
        <div class="flex items-center gap-4 mb-4">
            <div class="p-3 bg-blue-100 rounded-xl text-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <span class="text-sm font-medium text-zinc-500">Papers</span>
        </div>
        <p class="text-3xl font-bold text-zinc-900"><?php echo $total_papers; ?></p>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-zinc-200 hover:shadow-lg transition">
        <div class="flex items-center gap-4 mb-4">
            <div class="p-3 bg-rose-100 rounded-xl text-rose-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <span class="text-sm font-medium text-zinc-500">Videos</span>
        </div>
        <p class="text-3xl font-bold text-zinc-900"><?php echo $total_videos; ?></p>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-zinc-200 hover:shadow-lg transition">
        <div class="flex items-center gap-4 mb-4">
            <div class="p-3 bg-amber-100 rounded-xl text-amber-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <span class="text-sm font-medium text-zinc-500">Requests</span>
        </div>
        <p class="text-3xl font-bold text-zinc-900"><?php echo $pending_requests; ?></p>
    </div>
</div>

<!-- Quick Actions -->
<h2 class="text-lg font-bold text-zinc-900 mb-4">Quick Actions</h2>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <a href="books.php?add=1" class="group p-6 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white hover:shadow-xl transition relative overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff08_1px,transparent_1px),linear-gradient(to_bottom,#ffffff08_1px,transparent_1px)] bg-[size:24px_24px]"></div>
        <div class="relative">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
            </div>
            <h3 class="font-bold text-lg mb-1">Add New Book</h3>
            <p class="text-white/80 text-sm">Upload PDF or add external link</p>
        </div>
    </a>
    
    <a href="papers.php?add=1" class="group p-6 rounded-2xl bg-gradient-to-br from-teal-500 to-emerald-600 text-white hover:shadow-xl transition relative overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff08_1px,transparent_1px),linear-gradient(to_bottom,#ffffff08_1px,transparent_1px)] bg-[size:24px_24px]"></div>
        <div class="relative">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
            </div>
            <h3 class="font-bold text-lg mb-1">Add New Paper</h3>
            <p class="text-white/80 text-sm">Upload exam papers or research</p>
        </div>
    </a>
    
    <a href="videos.php?add=1" class="group p-6 rounded-2xl bg-gradient-to-br from-rose-500 to-pink-600 text-white hover:shadow-xl transition relative overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff08_1px,transparent_1px),linear-gradient(to_bottom,#ffffff08_1px,transparent_1px)] bg-[size:24px_24px]"></div>
        <div class="relative">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
            </div>
            <h3 class="font-bold text-lg mb-1">Add New Video</h3>
            <p class="text-white/80 text-sm">Embed YouTube tutorials</p>
        </div>
    </a>
</div>

<!-- Recent Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Recent Books -->
    <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-zinc-100 flex items-center justify-between">
            <h3 class="font-bold text-zinc-900">Recent Books</h3>
            <a href="books.php" class="text-sm text-indigo-600 hover:text-indigo-700">View All →</a>
        </div>
        <div class="divide-y divide-zinc-100">
            <?php if (empty($recent_books)): ?>
                <div class="p-6 text-center text-zinc-500">No books yet</div>
            <?php else: ?>
                <?php foreach ($recent_books as $book): ?>
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-zinc-50 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-zinc-900 text-sm"><?php echo htmlspecialchars($book['title']); ?></p>
                                <p class="text-xs text-zinc-500"><?php echo htmlspecialchars($book['author']); ?></p>
                            </div>
                        </div>
                        <span class="text-xs text-zinc-400"><?php echo date('M d', strtotime($book['created_at'])); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Papers -->
    <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-zinc-100 flex items-center justify-between">
            <h3 class="font-bold text-zinc-900">Recent Papers</h3>
            <a href="papers.php" class="text-sm text-teal-600 hover:text-teal-700">View All →</a>
        </div>
        <div class="divide-y divide-zinc-100">
            <?php if (empty($recent_papers)): ?>
                <div class="p-6 text-center text-zinc-500">No papers yet</div>
            <?php else: ?>
                <?php foreach ($recent_papers as $paper): ?>
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-zinc-50 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-teal-100 rounded-lg flex items-center justify-center text-teal-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-zinc-900 text-sm"><?php echo htmlspecialchars($paper['title']); ?></p>
                                <p class="text-xs text-zinc-500"><?php echo htmlspecialchars($paper['subject']); ?> • <?php echo $paper['year']; ?></p>
                            </div>
                        </div>
                        <span class="text-xs text-zinc-400"><?php echo date('M d', strtotime($paper['created_at'])); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Recent Videos & Users -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Recent Videos -->
    <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-zinc-100 flex items-center justify-between">
            <h3 class="font-bold text-zinc-900">Recent Videos</h3>
            <a href="videos.php" class="text-sm text-rose-600 hover:text-rose-700">View All →</a>
        </div>
        <div class="divide-y divide-zinc-100">
            <?php if (empty($recent_videos)): ?>
                <div class="p-6 text-center text-zinc-500">No videos yet</div>
            <?php else: ?>
                <?php foreach ($recent_videos as $video): ?>
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-zinc-50 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-rose-100 rounded-lg flex items-center justify-center text-rose-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="font-medium text-zinc-900 text-sm"><?php echo htmlspecialchars($video['title']); ?></p>
                        </div>
                        <span class="text-xs text-zinc-400"><?php echo date('M d', strtotime($video['created_at'])); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Users -->
    <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-zinc-100 flex items-center justify-between">
            <h3 class="font-bold text-zinc-900">Recent Users</h3>
            <a href="users.php" class="text-sm text-indigo-600 hover:text-indigo-700">View All →</a>
        </div>
        <div class="divide-y divide-zinc-100">
            <?php if (empty($recent_users)): ?>
                <div class="p-6 text-center text-zinc-500">No users yet</div>
            <?php else: ?>
                <?php foreach ($recent_users as $user): ?>
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-zinc-50 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold text-sm">
                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                            </div>
                            <div>
                                <p class="font-medium text-zinc-900 text-sm"><?php echo htmlspecialchars($user['username']); ?></p>
                                <span class="text-xs px-2 py-0.5 rounded-full <?php echo $user['role'] === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-zinc-100 text-zinc-600'; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </div>
                        </div>
                        <span class="text-xs text-zinc-400"><?php echo date('M d', strtotime($user['created_at'])); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>