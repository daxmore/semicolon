<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

require_once 'includes/db.php';
require_once 'includes/functions.php';

$post_id = $_GET['id'] ?? null;
if (!$post_id) {
    header('Location: community.php');
    exit();
}

// Fetch the post
$stmt = $conn->prepare("SELECT cp.*, u.username FROM community_posts cp JOIN users u ON cp.user_id = u.id WHERE cp.id = ?");
$stmt->bind_param('i', $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    echo "Post not found.";
    exit();
}

// Track User History
$stmt_history = $conn->prepare("INSERT INTO user_history (user_id, resource_type, resource_id) VALUES (?, 'post', ?)");
$stmt_history->bind_param('ii', $_SESSION['user_id'], $post_id);
$stmt_history->execute();

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // Check Admin powers
    $is_admin = false;
    $stmt_role = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt_role->bind_param("i", $_SESSION['user_id']);
    $stmt_role->execute();
    $role_res = $stmt_role->get_result()->fetch_assoc();
    if ($role_res && $role_res['role'] === 'admin') {
        $is_admin = true;
    }

    if ($action === 'post_comment' && isset($_POST['comment_content'])) {
        $content = trim($_POST['comment_content']);
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        
        if (!empty($content)) {
            $stmt_comment = $conn->prepare("INSERT INTO community_comments (post_id, user_id, parent_id, content) VALUES (?, ?, ?, ?)");
            $stmt_comment->bind_param('iiis', $post_id, $_SESSION['user_id'], $parent_id, $content);
            if ($stmt_comment->execute()) {
                
                // Determine who to notify
                $target_user_id = $post['user_id'];
                $notif_title = "New comment on your post";
                
                // If it's a reply to a comment, notify the comment author instead
                if ($parent_id) {
                    $stmt_parent = $conn->prepare("SELECT user_id FROM community_comments WHERE id = ?");
                    $stmt_parent->bind_param('i', $parent_id);
                    $stmt_parent->execute();
                    $parent_comment = $stmt_parent->get_result()->fetch_assoc();
                    if ($parent_comment) {
                        $target_user_id = $parent_comment['user_id'];
                        $notif_title = "New reply to your comment";
                    }
                }

                // Create Notification
                if ($target_user_id != $_SESSION['user_id']) {
                    $notif_message = $_SESSION['username'] . " replied: '" . substr(htmlspecialchars($content), 0, 50) . "...'";
                    $notif_link = "community_post_detail.php?id=" . $post_id . "#comments";
                    $notif_type = "new_comment";
                    
                    $stmt_notif = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)");
                    $stmt_notif->bind_param('issss', $target_user_id, $notif_type, $notif_title, $notif_message, $notif_link);
                    $stmt_notif->execute();
                }

                header("Location: community_post_detail.php?id=" . $post_id . "#comments");
                exit();
            }
        }
    } 
    elseif ($action === 'delete_post' && $is_admin) {
        $stmt_del = $conn->prepare("DELETE FROM community_posts WHERE id = ?");
        $stmt_del->bind_param("i", $post_id);
        $stmt_del->execute();
        header('Location: community.php');
        exit();
    }
    elseif ($action === 'delete_comment' && $is_admin && isset($_POST['comment_id'])) {
        $comment_id_to_del = (int)$_POST['comment_id'];
        $stmt_del = $conn->prepare("DELETE FROM community_comments WHERE id = ? AND post_id = ?");
        $stmt_del->bind_param("ii", $comment_id_to_del, $post_id);
        $stmt_del->execute();
        header("Location: community_post_detail.php?id=" . $post_id . "#comments");
        exit();
    }
}

// Fetch user role for rendering UI
$is_admin = false;
$stmt_role = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt_role->bind_param("i", $_SESSION['user_id']);
$stmt_role->execute();
$role_res = $stmt_role->get_result()->fetch_assoc();
if ($role_res && $role_res['role'] === 'admin') {
    $is_admin = true;
}

// Fetch comments
$comments_stmt = $conn->prepare("SELECT cc.*, u.username, u.avatar_url FROM community_comments cc JOIN users u ON cc.user_id = u.id WHERE cc.post_id = ? ORDER BY cc.upvotes DESC, cc.created_at ASC");
$comments_stmt->bind_param('i', $post_id);
$comments_stmt->execute();
$all_comments = $comments_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch user's comment reactions
$comment_reactions = [];
if (!empty($all_comments)) {
    $comment_ids = array_column($all_comments, 'id');
    $placeholders = str_repeat('?,', count($comment_ids) - 1) . '?';
    $types = str_repeat('i', count($comment_ids)) . 'i'; 
    $params = $comment_ids;
    array_unshift($params, $_SESSION['user_id']);
    
    $react_sql = "SELECT comment_id, reaction_type FROM community_comment_reactions WHERE user_id = ? AND comment_id IN ($placeholders)";
    $react_stmt = $conn->prepare($react_sql);
    
    // PHP 8+ syntax for array unpacking
    $react_stmt->bind_param($types, ...$params);
    
    $react_stmt->execute();
    $reaction_res = $react_stmt->get_result();
    while ($row = $reaction_res->fetch_assoc()) {
        $comment_reactions[$row['comment_id']] = $row['reaction_type']; 
    }
}

// Organize comments into a tree
$comments_by_parent = [];
foreach ($all_comments as $cmt) {
    $parent = $cmt['parent_id'] ?: 0;
    if (!isset($comments_by_parent[$parent])) {
        $comments_by_parent[$parent] = [];
    }
    $comments_by_parent[$parent][] = $cmt;
}

// Recursive function to render comments
function render_comments($parent_id, $comments_by_parent, $post_user_id, $depth = 0, $comment_reactions, $is_admin, $post_id) {
    if (!isset($comments_by_parent[$parent_id])) {
        return;
    }

    foreach ($comments_by_parent[$parent_id] as $comment) {
        $margin_class = $depth > 0 ? 'ml-8 md:ml-12 border-l-2 border-zinc-100 pl-4 mt-4' : 'bg-white border border-zinc-100 rounded-2xl p-6 shadow-sm mt-6';
        
        $has_upvoted = isset($comment_reactions[$comment['id']]) && $comment_reactions[$comment['id']] === 'upvote';
        $has_downvoted = isset($comment_reactions[$comment['id']]) && $comment_reactions[$comment['id']] === 'downvote';
        ?>
        <div class="<?php echo $margin_class; ?> relative group" id="comment-<?php echo $comment['id']; ?>">
            
            <?php if ($is_admin): ?>
                <form action="" method="POST" class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                    <input type="hidden" name="action" value="delete_comment">
                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                    <button type="submit" onclick="return confirm('Delete this comment permanently?')" class="text-xs text-red-500 hover:text-red-700 font-bold bg-red-50 hover:bg-red-100 px-2 py-1 rounded">Delete</button>
                </form>
            <?php endif; ?>

            <div class="flex gap-4">
                <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-bold text-sm md:text-lg flex-shrink-0 overflow-hidden">
                    <?php if (!empty($comment['avatar_url'])): ?>
                        <img src="<?php echo htmlspecialchars($comment['avatar_url']); ?>" alt="Avatar" class="w-full h-full object-cover">
                    <?php else: ?>
                        <?php echo strtoupper(substr($comment['username'], 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        <span class="font-bold text-zinc-900"><?php echo htmlspecialchars($comment['username']); ?></span>
                        <?php if ($comment['user_id'] === $post_user_id): ?>
                            <span class="text-[10px] md:text-xs font-bold px-2 py-0.5 bg-amber-100 text-amber-700 rounded-md">OP</span>
                        <?php endif; ?>
                        <span class="text-xs text-zinc-400"><?php echo date('M j, Y, g:i a', strtotime($comment['created_at'])); ?></span>
                    </div>
                    
                    <p class="text-zinc-700 text-sm md:text-base whitespace-pre-line leading-relaxed mb-3">
                        <?php echo htmlspecialchars($comment['content']); ?>
                    </p>
                    
                    <!-- Comment Actions (Vote & Reply) -->
                    <div class="flex items-center gap-4">
                        <div class="flex items-center bg-zinc-50 border border-zinc-200 rounded-full overflow-hidden">
                            <button onclick="handleCommentVote(<?php echo $comment['id']; ?>, 'upvote')" id="cmt-upvote-<?php echo $comment['id']; ?>" class="px-2 py-1 hover:bg-zinc-200 transition <?php echo $has_upvoted ? 'text-amber-500 bg-amber-50' : 'text-zinc-500'; ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 <?php echo !$has_upvoted ? 'group-hover:text-amber-500 transition-colors' : ''; ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" /></svg>
                            </button>
                            <span id="cmt-count-<?php echo $comment['id']; ?>" class="text-xs font-bold text-zinc-700 px-2 border-x border-zinc-200"><?php echo number_format($comment['upvotes'] - $comment['downvotes']); ?></span>
                            <button onclick="handleCommentVote(<?php echo $comment['id']; ?>, 'downvote')" id="cmt-downvote-<?php echo $comment['id']; ?>" class="px-2 py-1 hover:bg-zinc-200 transition <?php echo $has_downvoted ? 'text-blue-500 bg-blue-50' : 'text-zinc-500'; ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform rotate-180 <?php echo !$has_downvoted ? 'group-hover:text-blue-500 transition-colors' : ''; ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" /></svg>
                            </button>
                        </div>
                        <button onclick="toggleReplyForm(<?php echo $comment['id']; ?>)" class="text-xs font-bold text-zinc-500 hover:text-zinc-900 transition flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                            </svg>
                            Reply
                        </button>
                    </div>

                    <!-- Hidden Reply Form -->
                    <div id="reply-form-<?php echo $comment['id']; ?>" class="hidden mt-4">
                        <form action="" method="POST">
                            <input type="hidden" name="action" value="post_comment">
                            <input type="hidden" name="parent_id" value="<?php echo $comment['id']; ?>">
                            <textarea name="comment_content" rows="3" required placeholder="Write a reply..." class="w-full px-4 py-2 text-sm bg-white border border-zinc-200 rounded-xl text-zinc-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition resize-y mb-2"></textarea>
                            <div class="flex justify-end gap-2">
                                <button type="button" onclick="toggleReplyForm(<?php echo $comment['id']; ?>)" class="px-4 py-1.5 text-xs text-zinc-500 hover:text-zinc-900 font-medium transition">Cancel</button>
                                <button type="submit" class="px-4 py-1.5 bg-zinc-900 hover:bg-zinc-800 text-white font-medium rounded-lg text-xs transition shadow-sm">Post Reply</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>

            <!-- Recursively render children -->
            <?php render_comments($comment['id'], $comments_by_parent, $post_user_id, $depth + 1, $comment_reactions, $is_admin, $post_id); ?>
            
        </div>
        <?php
    }
}

// Check if current user has already reacted to the POST
$reaction_stmt = $conn->prepare("SELECT reaction_type FROM community_reactions WHERE user_id = ? AND post_id = ?");
$reaction_stmt->bind_param('ii', $_SESSION['user_id'], $post_id);
$reaction_stmt->execute();
$user_reaction = $reaction_stmt->get_result()->fetch_assoc();
$has_upvoted = ($user_reaction && $user_reaction['reaction_type'] === 'upvote');
$has_downvoted = ($user_reaction && $user_reaction['reaction_type'] === 'downvote');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - Semicolon</title>
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

    <div class="container mx-auto px-6 py-10 max-w-4xl">
        
        <a href="community.php" class="text-sm font-medium text-amber-600 hover:text-amber-700 mb-6 inline-block">&larr; Back to Feed</a>

        <!-- Main Post -->
        <div class="bg-white border border-zinc-100 rounded-2xl p-6 md:p-8 shadow-sm mb-8 flex gap-6 md:gap-8 relative overflow-hidden">
            
            <!-- Voting Column -->
            <div class="flex flex-col items-center gap-2 flex-shrink-0 w-12">
                <button onclick="handleVote(<?php echo $post['id']; ?>, 'upvote')" 
                        class="p-2 rounded-full hover:bg-zinc-100 transition group <?php echo $has_upvoted ? 'text-amber-500' : 'text-zinc-400'; ?>"
                        id="btn-upvote">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 <?php echo !$has_upvoted ? 'group-hover:text-amber-500 transition-colors' : ''; ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                    </svg>
                </button>
                <span id="vote-count" class="font-bold text-lg text-zinc-900 mx-2"><?php echo number_format($post['upvotes']); ?></span>
                
                <button onclick="handleVote(<?php echo $post['id']; ?>, 'downvote')" 
                        class="p-2 rounded-full hover:bg-zinc-100 transition group <?php echo $has_downvoted ? 'text-blue-500' : 'text-zinc-400'; ?>"
                        id="btn-downvote">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 transform rotate-180 <?php echo !$has_downvoted ? 'group-hover:text-blue-500 transition-colors' : ''; ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                    </svg>
                </button>
            </div>

            <!-- Post Content -->
                <div class="flex-1 min-w-0 pr-4">
                <div class="flex items-center gap-3 mb-4 flex-wrap">
                    <span class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium bg-amber-50 text-amber-700">
                        <?php echo htmlspecialchars($post['category']); ?>
                    </span>
                    <span class="text-zinc-500 font-medium">Posted by <?php echo htmlspecialchars($post['username']); ?></span>
                    <span class="text-sm text-zinc-400"><?php echo date('F j, Y, g:i a', strtotime($post['created_at'])); ?></span>
                </div>

                <h1 class="text-2xl md:text-3xl font-bold text-zinc-900 mb-6 leading-tight">
                    <?php echo htmlspecialchars($post['title']); ?>
                </h1>

                <div class="prose prose-zinc max-w-none mb-6">
                    <p class="text-lg text-zinc-700 whitespace-pre-line leading-relaxed"><?php echo htmlspecialchars($post['description']); ?></p>
                </div>

                <?php if (!empty($post['image_url'])): ?>
                    <div class="rounded-xl overflow-hidden border border-zinc-100 mb-6 bg-zinc-50">
                        <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="Attached media" class="w-full h-auto object-contain max-h-[500px]">
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($is_admin): ?>
                <form action="" method="POST" class="absolute top-4 right-4">
                    <input type="hidden" name="action" value="delete_post">
                    <button type="submit" onclick="return confirm('Delete this entire post? This will destroy all comments too.')" class="px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 rounded-lg text-sm font-bold transition flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete Post
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Comments Section -->
        <h2 id="comments" class="text-2xl font-bold text-zinc-900 mb-6"><?php echo count($all_comments); ?> Comments</h2>

        <!-- Leave a comment -->
        <div class="bg-white border border-zinc-100 rounded-2xl p-6 shadow-sm mb-8">
            <h3 class="font-bold text-zinc-900 mb-4">Post an Answer or Reply</h3>
            <form action="" method="POST">
                <input type="hidden" name="action" value="post_comment">
                <input type="hidden" name="parent_id" value="">
                <textarea name="comment_content" rows="4" required placeholder="What are your thoughts?" 
                          class="w-full px-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl text-zinc-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition resize-y mb-4"></textarea>
                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-zinc-900 hover:bg-zinc-800 text-white font-medium rounded-xl transition shadow-sm hover:shadow-md">Post Comment</button>
                </div>
            </form>
        </div>

        <!-- Comments List -->
        <?php if (empty($all_comments)): ?>
            <div class="text-center py-10 bg-white border border-dashed border-zinc-200 rounded-2xl">
                <p class="text-zinc-500">No comments yet. Be the first to share your thoughts!</p>
            </div>
        <?php else: ?>
            <div class="space-y-2 mb-12">
                <?php render_comments(0, $comments_by_parent, $post['user_id'], 0, $comment_reactions, $is_admin, $post_id); ?>
            </div>
        <?php endif; ?>

    </div>

    <!-- Voting & Reply Script -->
    <script>
    function toggleReplyForm(commentId) {
        const form = document.getElementById(`reply-form-${commentId}`);
        if (form.classList.contains('hidden')) {
            form.classList.remove('hidden');
            form.querySelector('textarea').focus();
        } else {
            form.classList.add('hidden');
        }
    }

    function handleVote(postId, type) {
        fetch('api_reaction.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `item_id=${postId}&type=${type}&item_type=post`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('vote-count').innerText = data.new_upvotes;
                
                document.getElementById('btn-upvote').classList.remove('text-amber-500');
                document.getElementById('btn-upvote').classList.add('text-zinc-400');
                document.getElementById('btn-downvote').classList.remove('text-blue-500');
                document.getElementById('btn-downvote').classList.add('text-zinc-400');
                
                if (data.user_reaction === 'upvote') {
                    document.getElementById('btn-upvote').classList.add('text-amber-500');
                    document.getElementById('btn-upvote').classList.remove('text-zinc-400');
                } else if (data.user_reaction === 'downvote') {
                    document.getElementById('btn-downvote').classList.add('text-blue-500');
                    document.getElementById('btn-downvote').classList.remove('text-zinc-400');
                }
            } else { alert(data.error || 'An error occurred while voting.'); }
        })
        .catch(error => console.error('Error:', error));
    }

    function handleCommentVote(commentId, type) {
        fetch('api_reaction.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `item_id=${commentId}&type=${type}&item_type=comment`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const countEl = document.getElementById(`cmt-count-${commentId}`);
                if(countEl) countEl.innerText = data.new_upvotes; // Note: simplified representation, assumes upvotes field used for score
                
                const upBtn = document.getElementById(`cmt-upvote-${commentId}`);
                const downBtn = document.getElementById(`cmt-downvote-${commentId}`);
                
                if(!upBtn || !downBtn) return;
                
                upBtn.className = 'px-2 py-1 hover:bg-zinc-200 transition text-zinc-500';
                downBtn.className = 'px-2 py-1 hover:bg-zinc-200 transition text-zinc-500';
                
                if (data.user_reaction === 'upvote') {
                    upBtn.className = 'px-2 py-1 transition text-amber-500 bg-amber-50 rounded-l-full';
                } else if (data.user_reaction === 'downvote') {
                    downBtn.className = 'px-2 py-1 transition text-blue-500 bg-blue-50 rounded-r-full';
                }
            } else { alert(data.error || 'An error occurred.'); }
        })
        .catch(error => console.error('Error:', error));
    }
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
