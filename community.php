<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

require_once 'includes/db.php';
require_once 'includes/functions.php';

// Define the 15 categories exactly as they are in the database ENUM
$categories = [
    'Frontend', 'Backend', 'Full Stack', 'App Dev', 'Game Dev', 
    'UI/UX Design', 'Graphic Design', 'Video Editing', 'Motion Graphics', 
    'Data Science', 'AI & ML', 'Cybersecurity', 'DevOps', 
    'Cloud Computing', 'General Tech'
];

$category_icons = [
    'Frontend' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>',
    'Backend' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>',
    'Full Stack' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>',
    'App Dev' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>',
    'Game Dev' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 11h.01M11 15h.01M15 15h.01M11 11h.01M5 15h14M5 9h14M3 7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>',
    'UI/UX Design' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>',
    'Graphic Design' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>',
    'Video Editing' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>',
    'Motion Graphics' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'Data Science' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
    'AI & ML' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>',
    'Cybersecurity' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
    'DevOps' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
    'Cloud Computing' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>',
    'General Tech' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m14-6h2m-2 6h2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>'
];

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

    <!-- Adjusted Hero Section -->
    <section class="relative pt-6 pb-4 bg-white border-b border-zinc-200">
        <div class="container mx-auto px-6 max-w-7xl">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 flex items-center gap-2">
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
    <section class="py-8 bg-[#FAFAFA]">
        <div class="container mx-auto px-6 max-w-7xl">
            <!-- 3 Column Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <!-- Left Sidebar (Topics) -->
                <div class="lg:col-span-3 hidden lg:block">
                    <div class="sticky top-20">
                        <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-3 px-3">Discover Topics</h3>
                        <div class="space-y-0.5 max-h-[75vh] overflow-y-auto custom-scrollbar pr-2">
                            <a href="community.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition <?php echo !$selected_category ? 'bg-amber-50 text-amber-700' : 'text-zinc-600 hover:bg-zinc-100/80 hover:text-zinc-900'; ?>">
                                <div class="w-6 h-6 rounded-full bg-zinc-100 flex items-center justify-center flex-shrink-0 <?php echo !$selected_category ? 'bg-amber-100/50 text-amber-600' : 'text-zinc-400'; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" /></svg>
                                </div>
                                All Posts
                            </a>
                            <?php foreach ($categories as $cat): ?>
                                <a href="community.php?category=<?php echo urlencode($cat); ?>" 
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition group <?php echo ($selected_category === $cat) ? 'bg-amber-50 text-amber-700' : 'text-zinc-600 hover:bg-zinc-100/80 hover:text-zinc-900'; ?>">
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 <?php echo ($selected_category === $cat) ? 'bg-amber-100/50 text-amber-600' : 'text-zinc-400 group-hover:text-zinc-500 bg-zinc-50 border border-zinc-100'; ?>">
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
                    <div class="bg-white border border-zinc-200 rounded-xl p-3 shadow-sm flex items-center gap-3">
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
                            <input type="text" name="q" placeholder="Search community posts..." value="<?php echo htmlspecialchars($search_query); ?>" class="w-full bg-zinc-50 border border-zinc-200 rounded-full pl-10 pr-4 py-2.5 text-sm text-zinc-900 focus:outline-none focus:border-indigo-500 transition">
                        </form>

                        <a href="community_create.php<?php echo $selected_category ? '?category='.urlencode($selected_category) : ''; ?>" class="p-2 border border-dashed border-zinc-300 text-zinc-500 hover:border-indigo-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition flex items-center justify-center" title="Create Post">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                        </a>
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
                        <a href="<?php echo $build_url('hot'); ?>" class="flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-full transition <?php echo $sort === 'hot' ? 'font-bold text-zinc-900 bg-zinc-100' : 'font-semibold text-zinc-500 hover:bg-zinc-100/80 hover:text-zinc-700'; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" /><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z" /></svg>
                            Hot
                        </a>
                        <a href="<?php echo $build_url('top'); ?>" class="flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-full transition <?php echo $sort === 'top' ? 'font-bold text-zinc-900 bg-zinc-100' : 'font-semibold text-zinc-500 hover:bg-zinc-100/80 hover:text-zinc-700'; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 11l7-7 7 7M5 19l7-7 7 7" /></svg>
                            Top
                        </a>
                        <a href="<?php echo $build_url('new'); ?>" class="flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-full transition <?php echo $sort === 'new' ? 'font-bold text-zinc-900 bg-zinc-100' : 'font-semibold text-zinc-500 hover:bg-zinc-100/80 hover:text-zinc-700'; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            New
                        </a>
                    </div>

                    <!-- Post List -->
                    <?php if (empty($posts)): ?>
                        <div class="text-center py-16 bg-white border border-zinc-200 rounded-xl shadow-sm">
                            <div class="w-16 h-16 bg-zinc-50 border border-zinc-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-zinc-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                                </svg>
                            </div>
                            <p class="text-lg font-bold text-zinc-900 mb-1">No posts found</p>
                            <p class="text-sm text-zinc-500 mb-6">Looks like it's quiet here. Be the first to post!</p>
                            <a href="community_create.php<?php echo $selected_category ? '?category='.urlencode($selected_category) : ''; ?>" class="inline-block px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-full text-sm transition shadow-sm">Start a Conversation</a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($posts as $post): ?>
                                <article class="bg-white border border-zinc-200 rounded-xl hover:border-zinc-300 transition-colors duration-200 overflow-hidden shadow-sm flex">
                                    
                                    <!-- Desktop Upvote Sidebar -->
                                    <?php 
                                        $has_upvoted = isset($user_reactions[$post['id']]) && $user_reactions[$post['id']] === 'upvote';
                                        $has_downvoted = isset($user_reactions[$post['id']]) && $user_reactions[$post['id']] === 'downvote';
                                    ?>
                                    <div class="w-12 bg-zinc-50/50 flex flex-col items-center pt-3 pb-2 flex-shrink-0 border-r border-zinc-100 hidden sm:flex">
                                        <button onclick="handleListVote(<?php echo $post['id']; ?>, 'upvote')" id="list-upvote-<?php echo $post['id']; ?>" class="p-1 rounded transition group <?php echo $has_upvoted ? 'text-amber-500' : 'text-zinc-400 hover:bg-zinc-200'; ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 <?php echo $has_upvoted ? '' : 'group-hover:text-amber-500 transition-colors'; ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" /></svg>
                                        </button>
                                        <span id="list-count-<?php echo $post['id']; ?>" class="text-sm font-bold text-zinc-900 my-1"><?php echo number_format($post['upvotes']); ?></span>
                                        <button onclick="handleListVote(<?php echo $post['id']; ?>, 'downvote')" id="list-downvote-<?php echo $post['id']; ?>" class="p-1 rounded transition group <?php echo $has_downvoted ? 'text-blue-500' : 'text-zinc-400 hover:bg-zinc-200'; ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 transform rotate-180 <?php echo $has_downvoted ? '' : 'group-hover:text-blue-500 transition-colors'; ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" /></svg>
                                        </button>
                                    </div>

                                    <!-- Main Post Content -->
                                    <div class="flex-1 p-2 sm:p-4 min-w-0">
                                        
                                        <!-- Header Row (Category, User, Date) -->
                                        <div class="flex items-center gap-1.5 text-[13px] mb-2 px-2 sm:px-0">
                                            <a href="community.php?category=<?php echo urlencode($post['category']); ?>" class="font-bold text-zinc-900 hover:underline"><?php echo htmlspecialchars($post['category']); ?></a>
                                            <span class="text-zinc-400">•</span>
                                            <span class="text-zinc-500">Posted by</span>
                                            <span class="text-zinc-500 font-medium hover:underline hover:text-zinc-900 cursor-pointer"><?php echo htmlspecialchars($post['username']); ?></span>
                                            <span class="text-zinc-500"><?php echo date('M j', strtotime($post['created_at'])); ?></span>
                                        </div>
                                        
                                        <!-- Post Title -->
                                        <h3 class="text-lg font-semibold text-zinc-900 mb-1 leading-snug px-2 sm:px-0">
                                            <a href="community_post_detail.php?id=<?php echo $post['id']; ?>" class="hover:underline">
                                                <?php echo htmlspecialchars($post['title']); ?>
                                            </a>
                                        </h3>
                                        
                                        <!-- Text Snippet -->
                                        <p class="text-zinc-600 text-sm mb-3 line-clamp-3 px-2 sm:px-0 leading-relaxed">
                                            <?php echo htmlspecialchars($post['description']); ?>
                                        </p>

                                        <!-- Attached Image (if any) -->
                                        <?php if (!empty($post['image_url'])): ?>
                                            <a href="community_post_detail.php?id=<?php echo $post['id']; ?>" class="block mb-3 bg-zinc-50 overflow-hidden sm:rounded-lg border-y sm:border border-zinc-200/60 max-h-[500px] flex items-center justify-center -mx-2 sm:mx-0">
                                                <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="Post attached media" class="w-full h-full object-contain max-h-[500px]">
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
                                            <button class="flex items-center gap-1.5 px-2 py-1.5 text-xs font-semibold text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900 rounded-md transition">
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
                        <div class="bg-white border border-zinc-200 rounded-xl p-4 shadow-sm mb-4">
                            <div class="flex items-center gap-2 mb-3 px-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-bold text-zinc-900 text-sm">About Community</span>
                            </div>
                            <p class="text-[13px] text-zinc-600 mb-4 px-1 leading-relaxed">
                                Welcome to Semicolon Community! The best place to share code, ask questions, and collaborate with your peers on everything from Frontend to Data Science.
                            </p>
                            <div class="flex items-center gap-4 text-center px-2 py-3 border-y border-zinc-100 mb-4">
                                <div class="flex-1">
                                    <span class="block text-lg font-bold text-zinc-900">
                                        <?php
                                            $total_posts = $conn->query("SELECT COUNT(*) as c FROM community_posts")->fetch_assoc()['c'];
                                            echo number_format($total_posts);
                                        ?>
                                    </span>
                                    <span class="block text-[11px] font-medium text-zinc-500 uppercase tracking-wide mt-0.5">Posts</span>
                                </div>
                                <div class="w-px h-8 bg-zinc-200"></div>
                                <div class="flex-1">
                                    <span class="block text-lg font-bold text-zinc-900">
                                        <?php
                                            $total_users = $conn->query("SELECT COUNT(*) as c FROM users WHERE role != 'admin'")->fetch_assoc()['c'];
                                            echo number_format($total_users);
                                        ?>
                                    </span>
                                    <span class="block text-[11px] font-medium text-zinc-500 uppercase tracking-wide mt-0.5">Members</span>
                                </div>
                            </div>
                            <a href="community_create.php" class="block w-full py-2 bg-zinc-900 hover:bg-zinc-800 text-white font-medium text-sm text-center rounded-full transition shadow-sm">
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
