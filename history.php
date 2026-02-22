<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

require_once 'includes/db.php';
require_once 'includes/functions.php';

$user_id = $_SESSION['user_id'];
$filter = $_GET['filter'] ?? 'all'; // all, book, paper, video, post

$valid_filters = ['all', 'book', 'paper', 'video', 'post'];
if (!in_array($filter, $valid_filters)) {
    $filter = 'all';
}

// Pagination logic
$limit = 15;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Build query
$count_sql = "SELECT COUNT(*) as c FROM user_history WHERE user_id = ?";
$data_sql = "SELECT uh.*, 
            COALESCE(b.title, p.title, v.title, cp.title) as title,
            COALESCE(b.slug, p.slug, v.slug, NULL) as slug,
            cp.id as post_id
            FROM user_history uh
            LEFT JOIN books b ON uh.resource_type = 'book' AND uh.resource_id = b.id
            LEFT JOIN papers p ON uh.resource_type = 'paper' AND uh.resource_id = p.id
            LEFT JOIN videos v ON uh.resource_type = 'video' AND uh.resource_id = v.id
            LEFT JOIN community_posts cp ON uh.resource_type = 'post' AND uh.resource_id = cp.id
            WHERE uh.user_id = ?";

if ($filter !== 'all') {
    $count_sql .= " AND resource_type = ?";
    $data_sql .= " AND resource_type = ?";
}

$data_sql .= " ORDER BY viewed_at DESC LIMIT ? OFFSET ?";

// Prepare counts
$stmt_c = $conn->prepare($count_sql);
if ($filter !== 'all') {
    $stmt_c->bind_param('is', $user_id, $filter);
} else {
    $stmt_c->bind_param('i', $user_id);
}
$stmt_c->execute();
$total_history = $stmt_c->get_result()->fetch_assoc()['c'];
$total_pages = ceil($total_history / $limit);

// Prepare data
$stmt_d = $conn->prepare($data_sql);
if ($filter !== 'all') {
    $stmt_d->bind_param('isii', $user_id, $filter, $limit, $offset);
} else {
    $stmt_d->bind_param('iii', $user_id, $limit, $offset);
}
$stmt_d->execute();
$history = $stmt_d->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My History - Semicolon</title>
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

    <section class="py-12">
        <div class="container mx-auto px-6 max-w-4xl">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
                <h1 class="text-3xl font-bold text-zinc-900 flex items-center gap-3">
                    <div class="p-2 bg-indigo-50 rounded-xl text-indigo-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    Activity History
                </h1>
            </div>

            <!-- Enhanced Filters -->
            <div class="flex overflow-x-auto pb-4 mb-4 gap-2 no-scrollbar border-b border-zinc-200">
                <a href="history.php?filter=all" class="px-5 py-2.5 rounded-full text-sm font-medium transition whitespace-nowrap <?php echo $filter === 'all' ? 'bg-zinc-900 text-white' : 'bg-white text-zinc-600 border border-zinc-200 hover:bg-zinc-50'; ?>">
                    All Activity
                </a>
                <a href="history.php?filter=book" class="px-5 py-2.5 rounded-full text-sm font-medium transition whitespace-nowrap flex items-center gap-2 <?php echo $filter === 'book' ? 'bg-indigo-600 text-white border border-indigo-600' : 'bg-white text-indigo-700 border border-indigo-100 hover:bg-indigo-50'; ?>">
                    📚 Books
                </a>
                <a href="history.php?filter=paper" class="px-5 py-2.5 rounded-full text-sm font-medium transition whitespace-nowrap flex items-center gap-2 <?php echo $filter === 'paper' ? 'bg-teal-600 text-white border border-teal-600' : 'bg-white text-teal-700 border border-teal-100 hover:bg-teal-50'; ?>">
                    📄 Papers
                </a>
                <a href="history.php?filter=video" class="px-5 py-2.5 rounded-full text-sm font-medium transition whitespace-nowrap flex items-center gap-2 <?php echo $filter === 'video' ? 'bg-rose-600 text-white border border-rose-600' : 'bg-white text-rose-700 border border-rose-100 hover:bg-rose-50'; ?>">
                    🎥 Videos
                </a>
                <a href="history.php?filter=post" class="px-5 py-2.5 rounded-full text-sm font-medium transition whitespace-nowrap flex items-center gap-2 <?php echo $filter === 'post' ? 'bg-amber-500 text-white border border-amber-500' : 'bg-white text-amber-600 border border-amber-100 hover:bg-amber-50'; ?>">
                    💬 Community Posts
                </a>
            </div>

            <!-- History List -->
            <div class="bg-white border border-zinc-100 rounded-2xl shadow-sm overflow-hidden">
                <?php if (empty($history)): ?>
                    <div class="p-16 text-center text-zinc-500">
                        <div class="w-20 h-20 bg-zinc-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-zinc-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <p class="text-xl font-medium text-zinc-900 mb-2">No activity found</p>
                        <p>No history matches this filter.</p>
                    </div>
                <?php else: ?>
                    <div class="divide-y divide-zinc-100">
                        <?php foreach ($history as $item): ?>
                            <?php 
                            // Determine Link and Icon
                            $iconBg = 'bg-zinc-100 text-zinc-600';
                            $iconSvg = '';
                            $url = '#';

                            if ($item['resource_type'] === 'book') {
                                $iconBg = 'bg-indigo-50 text-indigo-600';
                                $iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253" /></svg>';
                                $url = "view.php?slug=" . urlencode($item['slug'] ?? '') . "&type=book"; 
                            } elseif ($item['resource_type'] === 'paper') {
                                $iconBg = 'bg-teal-50 text-teal-600';
                                $iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>';
                                $url = "view.php?slug=" . urlencode($item['slug'] ?? '') . "&type=paper"; 
                            } elseif ($item['resource_type'] === 'video') {
                                $iconBg = 'bg-rose-50 text-rose-600';
                                $iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                                $url = "view.php?slug=" . urlencode($item['slug'] ?? '') . "&type=video"; 
                            } elseif ($item['resource_type'] === 'post') {
                                $iconBg = 'bg-amber-50 text-amber-500';
                                $iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" /></svg>';
                                $url = "community_post_detail.php?id=" . $item['post_id'];
                            }
                            ?>
                            
                            <a href="<?php echo $url; ?>" class="flex items-center gap-4 p-5 hover:bg-zinc-50 transition group">
                                <div class="w-12 h-12 rounded-2xl <?php echo $iconBg; ?> flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                    <?php echo $iconSvg; ?>
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">
                                        <?php echo htmlspecialchars($item['title'] ?? 'Deleted Resource'); ?>
                                    </h3>
                                    <p class="text-sm text-zinc-500 flex items-center gap-2 mt-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-600">
                                            <?php echo htmlspecialchars($item['resource_type']); ?>
                                        </span>
                                        •
                                        <?php echo date('M d, Y \a\t g:i a', strtotime($item['viewed_at'])); ?>
                                    </p>
                                </div>
                                
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-zinc-400 group-hover:text-zinc-600 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="mt-8 flex items-center justify-between">
                    <?php 
                        $filter_param = "&filter=" . urlencode($filter);
                    ?>
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1 . $filter_param; ?>" class="px-5 py-2.5 bg-white border border-zinc-200 text-zinc-700 font-medium rounded-xl hover:bg-zinc-50 transition shadow-sm">
                            &larr; Previous
                        </a>
                    <?php else: ?>
                        <div class="px-5 py-2.5 bg-zinc-50 border border-zinc-100 text-zinc-400 font-medium rounded-xl cursor-not-allowed">
                            &larr; Previous
                        </div>
                    <?php endif; ?>
                    
                    <span class="text-sm font-medium text-zinc-500">
                        Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                    </span>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1 . $filter_param; ?>" class="px-5 py-2.5 bg-white border border-zinc-200 text-zinc-700 font-medium rounded-xl hover:bg-zinc-50 transition shadow-sm">
                            Next &rarr;
                        </a>
                    <?php else: ?>
                        <div class="px-5 py-2.5 bg-zinc-50 border border-zinc-100 text-zinc-400 font-medium rounded-xl cursor-not-allowed">
                            Next &rarr;
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
