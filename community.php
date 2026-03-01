<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

require_once 'includes/db.php';
require_once 'includes/functions.php';

$categories = get_system_categories();
$category_icons = get_category_icons();

$selected_category = $_GET['category'] ?? null;
$sort = $_GET['sort'] ?? 'new';
$search_query = trim($_GET['q'] ?? '');

// Build query
$sql = "SELECT cp.*, u.username";

if ($sort === 'top') {
    $sql .= ", (SELECT COUNT(*) FROM user_history WHERE resource_type = 'post' AND resource_id = cp.id) as view_count";
} elseif ($sort === 'hot') {
    $sql .= ", (SELECT COUNT(*) FROM user_history WHERE resource_type = 'post' AND resource_id = cp.id AND viewed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as recent_view_count";
}

$sql .= " FROM community_posts cp
          JOIN users u ON cp.user_id = u.id
          WHERE 1=1";

$params = [];
$types = "";

if ($selected_category && in_array($selected_category, $categories)) {
    $sql .= " AND cp.category = ?";
    $types .= "s";
    $params[] = &$selected_category;
}

if (!empty($search_query)) {
    $sql .= " AND (cp.title LIKE ? OR cp.description LIKE ?)";
    $like_val1 = '%' . $search_query . '%';
    $like_val2 = '%' . $search_query . '%';
    $types .= "ss";
    $params[] = &$like_val1;
    $params[] = &$like_val2;
}

if ($sort === 'top') {
    $sql .= " ORDER BY view_count DESC, cp.created_at DESC";
} elseif ($sort === 'hot') {
    $sql .= " ORDER BY recent_view_count DESC, cp.created_at DESC";
} else {
    $sql .= " ORDER BY cp.created_at DESC";
}

$stmt = $conn->prepare($sql);
if ($types) {
    if (PHP_VERSION_ID >= 81000) {
        $dereferenced_params = array_map(function($p) { return $p; }, $params);
        $stmt->bind_param($types, ...$dereferenced_params);
    } else {
        $bind_names[] = $types;
        for ($i=0; $i<count($params); $i++) {
            $bind_name = 'bind' . $i;
            $$bind_name = $params[$i];
            $bind_names[] = &$$bind_name;
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);
    }
}
$stmt->execute();
$posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Pre-fetch user's reactions for all posts in the feed so we can shade the arrows correctly
$post_ids = [];
foreach ($posts as $post) {
    if ($post['id']) {
        $post_ids[] = $post['id'];
    }
}

$user_reactions = [];
if (!empty($post_ids)) {
    $placeholders = str_repeat('?,', count($post_ids) - 1) . '?';
    $types = str_repeat('i', count($post_ids)) . 'i'; 
    $params = $post_ids;
    array_unshift($params, $_SESSION['user_id']);
    
    $react_sql = "SELECT post_id, reaction_type FROM community_reactions WHERE user_id = ? AND post_id IN ($placeholders)";
    $react_stmt = $conn->prepare($react_sql);
    
    // PHP 8+ syntax for array unpacking
    $react_stmt->bind_param($types, ...$params);
    
    $react_stmt->execute();
    $reaction_res = $react_stmt->get_result();
    while ($row = $reaction_res->fetch_assoc()) {
        $user_reactions[$row['post_id']] = $row['reaction_type']; // 'upvote' or 'downvote'
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community - Semicolon</title>
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

    <!-- Adjusted Hero Section -->
    <section class="relative pt-6 pb-4 bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
        <div class="container mx-auto px-6 max-w-7xl">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                        </svg>
                        Community
                    </h1>
                    <p class="text-sm text-zinc-500 mt-1">Connect and share ideas with fellow developers</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-8 bg-[#FAFAFA] dark:bg-zinc-950">
        <div class="container mx-auto px-6 max-w-7xl">
            <!-- 3 Column Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <!-- Left Sidebar (Topics) -->
                <div class="lg:col-span-3 hidden lg:block">
                    <div class="sticky top-20">
                        <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-3 px-3">Discover Topics</h3>
                        <div class="space-y-0.5 max-h-[75vh] overflow-y-auto custom-scrollbar pr-2">
                            <a href="community.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition <?php echo !$selected_category ? 'bg-amber-50 text-amber-700' : 'text-zinc-600 hover:bg-zinc-100/80 hover:text-zinc-900'; ?>">
                                <div class="w-6 h-6 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center flex-shrink-0 <?php echo !$selected_category ? 'bg-amber-100/50 text-amber-600' : 'text-zinc-400'; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" /></svg>
                                </div>
                                All Posts
                            </a>
                            <a href="manage_posts.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100/80 hover:text-zinc-900 transition group">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 text-zinc-400 group-hover:text-zinc-500 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100 dark:border-zinc-800">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </div>
                                <span class="truncate">My Posts</span>
                            </a>
                            <?php foreach ($categories as $cat): ?>
                                <a href="community.php?category=<?php echo urlencode($cat); ?>" 
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition group <?php echo ($selected_category === $cat) ? 'bg-amber-50 text-amber-700' : 'text-zinc-600 hover:bg-zinc-100/80 hover:text-zinc-900'; ?>">
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 <?php echo ($selected_category === $cat) ? 'bg-amber-100/50 text-amber-600' : 'text-zinc-400 group-hover:text-zinc-500 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100'; ?>">
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

                <!-- Middle Feed Column -->
                <div class="lg:col-span-6 flex flex-col gap-4">
                    
                    <!-- Create Post Box (Reddit/Quora style) -->
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-3 shadow-sm flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0 overflow-hidden">
                            <?php 
                                // Fetch current user details for avatar
                                $stmt_u = $conn->prepare("SELECT username, avatar_url FROM users WHERE id = ?");
                                $stmt_u->bind_param("i", $_SESSION['user_id']);
                                $stmt_u->execute();
                                $current_user_data = $stmt_u->get_result()->fetch_assoc();
                                if (!empty($current_user_data['avatar_url'])) {
                                    echo '<img src="'.htmlspecialchars($current_user_data['avatar_url']).'" class="w-full h-full object-cover" alt="User">';
                                } else {
                                    echo strtoupper(substr($current_user_data['username'] ?? 'U', 0, 1));
                                }
                            ?>
                        </div>
                        
                        <form action="community.php" method="GET" class="flex-1 relative mb-0">
                            <?php if ($selected_category): ?>
                                <input type="hidden" name="category" value="<?php echo htmlspecialchars($selected_category); ?>">
                            <?php endif; ?>
                            <?php if ($sort !== 'new'): ?>
                                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                            <?php endif; ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" name="q" placeholder="Search community posts..." value="<?php echo htmlspecialchars($search_query); ?>" class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-full pl-10 pr-4 py-2.5 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-indigo-500 transition">
                        </form>
                    </div>
                    
                    <!-- Feed Sorting Options -->
                    <div class="flex items-center gap-2 px-1">
                        <?php
                        $build_url = function($new_sort) use ($selected_category, $search_query) {
                            $params = [];
                            if ($selected_category) $params['category'] = $selected_category;
                            if ($search_query) $params['q'] = $search_query;
                            $params['sort'] = $new_sort;
                            return 'community.php?' . http_build_query($params);
                        };
                        ?>
                        <a href="<?php echo $build_url('hot'); ?>" class="flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-full transition <?php echo $sort === 'hot' ? 'font-bold text-zinc-900 dark:text-white bg-zinc-100' : 'font-semibold text-zinc-500 hover:bg-zinc-100/80 hover:text-zinc-700'; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" /><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z" /></svg>
                            Hot
                        </a>
                        <a href="<?php echo $build_url('top'); ?>" class="flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-full transition <?php echo $sort === 'top' ? 'font-bold text-zinc-900 dark:text-white bg-zinc-100' : 'font-semibold text-zinc-500 hover:bg-zinc-100/80 hover:text-zinc-700'; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 11l7-7 7 7M5 19l7-7 7 7" /></svg>
                            Top
                        </a>
                        <a href="<?php echo $build_url('new'); ?>" class="flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-full transition <?php echo $sort === 'new' ? 'font-bold text-zinc-900 dark:text-white bg-zinc-100' : 'font-semibold text-zinc-500 hover:bg-zinc-100/80 hover:text-zinc-700'; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            New
                        </a>
                    </div>

                    <!-- Post List -->
                    <?php if (empty($posts)): ?>
                        <div class="text-center py-16 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm">
                            <div class="w-16 h-16 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100 dark:border-zinc-800 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-zinc-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                                </svg>
                            </div>
                            <p class="text-lg font-bold text-zinc-900 dark:text-white mb-1">No posts found</p>
                            <p class="text-sm text-zinc-500 mb-6">Looks like it's quiet here. Be the first to post!</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($posts as $post): ?>
                                <article class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl hover:border-zinc-300 transition-colors duration-200 overflow-hidden shadow-sm flex">
                                    
                                    <!-- Desktop Upvote Sidebar -->
                                    <?php 
                                        $has_upvoted = isset($user_reactions[$post['id']]) && $user_reactions[$post['id']] === 'upvote';
                                        $has_downvoted = isset($user_reactions[$post['id']]) && $user_reactions[$post['id']] === 'downvote';
                                    ?>
                                    <div class="w-12 bg-zinc-50/50 flex flex-col items-center pt-3 pb-2 flex-shrink-0 border-r border-zinc-100 dark:border-zinc-800 hidden sm:flex">
                                        <button onclick="handleListVote(<?php echo $post['id']; ?>, 'upvote')" id="list-upvote-<?php echo $post['id']; ?>" class="p-1 rounded transition group <?php echo $has_upvoted ? 'text-amber-500' : 'text-zinc-400 hover:bg-zinc-200'; ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 <?php echo $has_upvoted ? '' : 'group-hover:text-amber-500 transition-colors'; ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" /></svg>
                                        </button>
                                        <span id="list-count-<?php echo $post['id']; ?>" class="text-sm font-bold text-zinc-900 dark:text-white my-1"><?php echo number_format($post['upvotes']); ?></span>
                                        <button onclick="handleListVote(<?php echo $post['id']; ?>, 'downvote')" id="list-downvote-<?php echo $post['id']; ?>" class="p-1 rounded transition group <?php echo $has_downvoted ? 'text-blue-500' : 'text-zinc-400 hover:bg-zinc-200'; ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 transform rotate-180 <?php echo $has_downvoted ? '' : 'group-hover:text-blue-500 transition-colors'; ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" /></svg>
                                        </button>
                                    </div>

                                    <!-- Main Post Content -->
                                    <div class="flex-1 p-2 sm:p-4 min-w-0">
                                        
                                        <!-- Header Row (Category, User, Date) -->
                                        <div class="flex items-center gap-1.5 text-[13px] mb-2 px-2 sm:px-0">
                                            <a href="community.php?category=<?php echo urlencode($post['category']); ?>" class="font-bold text-zinc-900 dark:text-white hover:underline"><?php echo htmlspecialchars($post['category']); ?></a>
                                            <span class="text-zinc-400">•</span>
                                            <span class="text-zinc-500">Posted by</span>
                                            <span class="text-zinc-500 font-medium hover:underline hover:text-zinc-900 cursor-pointer"><?php echo htmlspecialchars($post['username']); ?></span>
                                            <span class="text-zinc-500"><?php echo date('M j', strtotime($post['created_at'])); ?></span>
                                        </div>
                                        
                                        <!-- Post Title -->
                                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-1 leading-snug px-2 sm:px-0">
                                            <a href="community_post_detail.php?id=<?php echo $post['id']; ?>" class="hover:underline">
                                                <?php echo htmlspecialchars($post['title']); ?>
                                            </a>
                                        </h3>
                                        
                                        <!-- Text Snippet -->
                                        <p class="text-zinc-600 dark:text-zinc-400 text-sm mb-3 line-clamp-3 px-2 sm:px-0 leading-relaxed">
                                            <?php echo htmlspecialchars($post['description']); ?>
                                        </p>

                                        <!-- Attached Image (if any) -->
                                        <?php if (!empty($post['image_url'])): ?>
                                            <a href="community_post_detail.php?id=<?php echo $post['id']; ?>" class="block mb-3 bg-zinc-50 dark:bg-zinc-800/50 overflow-hidden sm:rounded-lg border-y sm:border border-zinc-200/60 max-h-[500px] flex items-center justify-center -mx-2 sm:mx-0">
                                                <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="Post attached media" 
                                                     onerror="this.parentElement.style.display='none'; console.log('Image failed to load:', this.src);"
                                                     class="w-full h-full object-contain max-h-[500px]">
                                            </a>
                                        <?php endif; ?>
                                        
                                        <!-- Action Row -->
                                        <?php
                                            $comment_query = $conn->prepare("SELECT COUNT(*) as c FROM community_comments WHERE post_id = ?");
                                            $comment_query->bind_param('i', $post['id']);
                                            $comment_query->execute();
                                            $comment_count = $comment_query->get_result()->fetch_assoc()['c'];
                                        ?>
                                        <div class="flex items-center gap-1 mt-1 px-1 sm:px-0">
                                            <!-- Mobile Upvote Display -->
                                            <div class="flex sm:hidden items-center <?php echo $has_upvoted ? 'text-amber-500 border-amber-200 bg-amber-50' : 'text-zinc-500 border-zinc-200'; ?> mr-2 border rounded-full px-2 py-1 gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" /></svg>
                                                <span class="text-xs font-bold"><?php echo number_format($post['upvotes']); ?></span>
                                            </div>
                                            
                                            <!-- Mobile Downvote Button (Reusing existing layout structure if a downvote button was intended, or replacing the Upvote Display if needed. Based on the file, the mobile view only shows the net upvote numeric display and the up chevron. Thus no downvote arrow exists in the mobile action row yet. I'll inject the down arrow next to the upvote display). -->
                                            <button onclick="handleListVote(<?php echo $post['id']; ?>, 'downvote')" id="mobile-list-downvote-<?php echo $post['id']; ?>" class="flex sm:hidden items-center p-1 rounded transition group <?php echo $has_downvoted ? 'text-blue-500' : 'text-zinc-400 hover:text-blue-500'; ?> mr-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" /></svg>
                                            </button>

                                            <a href="community_post_detail.php?id=<?php echo $post['id']; ?>" class="flex items-center gap-1.5 px-2 py-1.5 text-xs font-semibold text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900 rounded-md transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                                </svg>
                                                <?php echo $comment_count; ?> Comments
                                            </a>
                                            <button onclick="sharePost(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars(addslashes($post['title'])); ?>')" class="flex items-center gap-1.5 px-2 py-1.5 text-xs font-semibold text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900 rounded-md transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                                </svg>
                                                Share
                                            </button>
                                        </div>
                                    </div>

                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right Sidebar (Stats / Trending) -->
                <div class="lg:col-span-3 hidden lg:block">
                    <div class="sticky top-20">
                        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-4 shadow-sm mb-4">
                            <div class="flex items-center gap-2 mb-3 px-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-bold text-zinc-900 dark:text-white text-sm">About Community</span>
                            </div>
                            <p class="text-[13px] text-zinc-600 dark:text-zinc-400 mb-4 px-1 leading-relaxed">
                                Welcome to Semicolon Community! The best place to share code, ask questions, and collaborate with your peers on everything from Frontend to Data Science.
                            </p>
                            <div class="flex items-center gap-4 text-center px-2 py-3 border-y border-zinc-100 dark:border-zinc-800 mb-4">
                                <div class="flex-1">
                                    <span class="block text-lg font-bold text-zinc-900 dark:text-white">
                                        <?php
                                            $total_posts = $conn->query("SELECT COUNT(*) as c FROM community_posts")->fetch_assoc()['c'];
                                            echo number_format($total_posts);
                                        ?>
                                    </span>
                                    <span class="block text-[11px] font-medium text-zinc-500 uppercase tracking-wide mt-0.5">Posts</span>
                                </div>
                                <div class="w-px h-8 bg-zinc-200"></div>
                                <div class="flex-1">
                                    <span class="block text-lg font-bold text-zinc-900 dark:text-white">
                                        <?php
                                            $total_users = $conn->query("SELECT COUNT(*) as c FROM users WHERE role != 'admin'")->fetch_assoc()['c'];
                                            echo number_format($total_users);
                                        ?>
                                    </span>
                                    <span class="block text-[11px] font-medium text-zinc-500 uppercase tracking-wide mt-0.5">Members</span>
                                </div>
                            </div>
                            <a href="community_create.php" class="block w-full py-2 bg-zinc-900 dark:bg-zinc-950 hover:bg-zinc-800 text-white font-medium text-sm text-center rounded-full transition shadow-sm">
                                Create Post
                            </a>
                        </div>
                        
                        <!-- Mini Links Footer -->
                        <div class="flex flex-wrap gap-x-3 gap-y-1.5 px-2 text-xs text-zinc-500 font-medium pt-2">
                            <a href="#" class="hover:underline">User Agreement</a>
                            <a href="#" class="hover:underline">Privacy Policy</a>
                            <a href="#" class="hover:underline">Moderator Code Of Conduct</a>
                            <a href="#" class="hover:underline">Semicolon © 2026</a>
                        </div>
                    </div>
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

    <script>
    function sharePost(postId, title) {
        const url = `${window.location.origin}/semicolon/community_post_detail.php?id=${postId}`;
        
        if (navigator.share) {
            navigator.share({
                title: title,
                url: url
            }).catch(err => {
                console.error('Error sharing:', err);
            });
        } else {
            // Fallback to clipboard
            navigator.clipboard.writeText(url).then(() => {
                showToast('Link copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy:', err);
            });
        }
    }

    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-5 right-5 bg-zinc-900 dark:bg-zinc-950 text-white px-6 py-3 rounded-xl shadow-2xl z-50 transform translate-y-20 transition-transform duration-300 font-medium text-sm flex items-center gap-2';
        toast.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            ${message}
        `;
        document.body.appendChild(toast);
        
        // Trigger animation
        setTimeout(() => toast.classList.remove('translate-y-20'), 10);
        
        // Fade out and remove
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    function handleListVote(postId, type) {
        fetch('api_reaction.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            // We pass item_type=post explicitly for the upcoming comment API rework
            body: `post_id=${postId}&type=${type}&item_type=post`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update Vote Count
                const countEl = document.getElementById(`list-count-${postId}`);
                if (countEl) countEl.innerText = data.new_upvotes;
                
                const upBtn = document.getElementById(`list-upvote-${postId}`);
                const downBtn = document.getElementById(`list-downvote-${postId}`);
                
                if (!upBtn || !downBtn) return;

                // Reset Button Colors
                upBtn.className = 'p-1 rounded transition group text-zinc-400 hover:bg-zinc-200';
                upBtn.querySelector('svg').className = 'h-6 w-6 group-hover:text-amber-500 transition-colors';
                
                downBtn.className = 'p-1 rounded transition group text-zinc-400 hover:bg-zinc-200';
                downBtn.querySelector('svg').className = 'h-6 w-6 group-hover:text-amber-500 transition-colors transform rotate-180';
                
                // Highlight active button based on new state
                if (data.user_reaction === 'upvote') {
                    upBtn.className = 'p-1 rounded transition group text-amber-500';
                    upBtn.querySelector('svg').className = 'h-6 w-6'; // remove hover effect when active
                } else if (data.user_reaction === 'downvote') {
                    downBtn.className = 'p-1 rounded transition group text-blue-500';
                    downBtn.querySelector('svg').className = 'h-6 w-6 transform rotate-180';
                }
            } else {
                alert(data.error || 'An error occurred while voting.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to process vote.');
        });
    }
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
