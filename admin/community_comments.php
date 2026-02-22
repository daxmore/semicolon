<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db.php';
require_once '../includes/functions.php';

$post_id = $_GET['post_id'] ?? null;

if (!$post_id) {
    header('Location: community.php');
    exit();
}

// Handle Actions
if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'delete_comment' && isset($_POST['comment_id'])) {
        $comment_id = (int)$_POST['comment_id'];
        $stmt_delete = $conn->prepare("DELETE FROM community_comments WHERE id = ?");
        $stmt_delete->bind_param("i", $comment_id);
        $stmt_delete->execute();
    }
    
    header('Location: community_comments.php?post_id=' . $post_id);
    exit();
}

// Fetch Post Info
$stmt_post = $conn->prepare("SELECT title FROM community_posts WHERE id = ?");
$stmt_post->bind_param("i", $post_id);
$stmt_post->execute();
$post = $stmt_post->get_result()->fetch_assoc();

if (!$post) {
    header('Location: community.php');
    exit();
}

// Fetch Comments
$sql = "SELECT cc.*, u.username, u.avatar_url 
        FROM community_comments cc 
        LEFT JOIN users u ON cc.user_id = u.id 
        WHERE cc.post_id = ? 
        ORDER BY cc.created_at DESC";
$stmt_comments = $conn->prepare($sql);
$stmt_comments->bind_param("i", $post_id);
$stmt_comments->execute();
$comments = $stmt_comments->get_result()->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>

<!-- Header with count -->
<div class="flex justify-between items-center mb-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-zinc-500 mb-2">
            <a href="community.php" class="hover:text-amber-600 transition">Community Moderation</a>
            <span>&rarr;</span>
            <span class="text-zinc-900 font-medium truncate max-w-[200px]"><?php echo htmlspecialchars($post['title']); ?></span>
        </div>
        <h2 class="text-xl font-bold text-zinc-900 mb-1">Comment Moderation</h2>
        <p class="text-zinc-500">Manage all comments on this specific post</p>
    </div>
    <div class="text-sm text-zinc-500">
        Total Comments: <span class="font-bold text-amber-600"><?php echo count($comments); ?></span>
    </div>
</div>

<!-- Comments Table -->
<div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden">
    <div class="p-4 border-b border-zinc-100">
        <input type="text" id="searchInput" onkeyup="searchComments()" placeholder="Search comments by content or author..." 
            class="w-full px-4 py-2 bg-zinc-50 border-0 rounded-xl focus:ring-2 focus:ring-amber-500">
    </div>
    <div class="overflow-x-auto">
        <table id="commentsTable" class="w-full">
            <thead>
                <tr class="bg-zinc-50 border-b border-zinc-200 text-xs uppercase text-zinc-500 font-semibold">
                    <th class="px-6 py-4 text-left">Comment / Reply Content</th>
                    <th class="px-6 py-4 text-left">Author</th>
                    <th class="px-6 py-4 text-center">Stats</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                <?php if (empty($comments)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-zinc-500">No community comments found on this post.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <tr class="hover:bg-zinc-50 transition">
                            <!-- Comment Content -->
                            <td class="px-6 py-4 max-w-lg">
                                <?php if ($comment['parent_id']): ?>
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] bg-zinc-100 text-zinc-500 font-bold mb-1 uppercase tracking-wide">Reply</span>
                                <?php endif; ?>
                                <p class="text-sm text-zinc-700 whitespace-pre-wrap leading-relaxed line-clamp-3">
                                    <?php echo htmlspecialchars($comment['content']); ?>
                                </p>
                                <span class="text-[10px] text-zinc-400 mt-2 block"><?php echo date('M d, Y h:ia', strtotime($comment['created_at'])); ?></span>
                            </td>
                            
                            <!-- Author -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 font-bold text-xs overflow-hidden">
                                        <?php if (!empty($comment['avatar_url'])): ?>
                                            <img src="../<?php echo htmlspecialchars($comment['avatar_url']); ?>" alt="Avatar" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <?php echo strtoupper(substr($comment['username'] ?? 'U', 0, 1)); ?>
                                        <?php endif; ?>
                                    </div>
                                    <span class="font-medium text-zinc-900 text-sm"><?php echo htmlspecialchars($comment['username'] ?? 'Anonymous'); ?></span>
                                </div>
                            </td>
                            
                            <!-- Stats -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-4 text-xs font-medium text-zinc-500">
                                    <span class="flex items-center gap-1" title="Upvotes">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        <?php echo $comment['upvotes']; ?>
                                    </span>
                                    <span class="flex items-center gap-1" title="Downvotes">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-rose-500 transform rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        <?php echo $comment['downvotes']; ?>
                                    </span>
                                </div>
                            </td>
                            
                            <!-- Actions -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Delete -->
                                    <button type="button" 
                                        onclick="showDeleteModal(<?php echo $comment['id']; ?>)"
                                        class="p-2 text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition" title="Delete Comment">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-xl p-6 relative">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center text-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-zinc-900">Delete Comment</h3>
                    <p class="text-sm text-zinc-500">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-zinc-600 mb-6">Are you sure you want to permanently delete this comment? Any child replies pointing to it will also be deleted.</p>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-zinc-600 hover:text-zinc-900 font-medium transition">
                    Cancel
                </button>
                <form id="deleteForm" action="community_comments.php?post_id=<?php echo $post_id; ?>" method="POST">
                    <input type="hidden" name="comment_id" id="deleteCommentId">
                    <input type="hidden" name="action" value="delete_comment">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition">
                        Delete Comment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showDeleteModal(commentId) {
    document.getElementById('deleteCommentId').value = commentId;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
});

function searchComments() {
    var input = document.getElementById("searchInput");
    var filter = input.value.toUpperCase();
    var table = document.getElementById("commentsTable");
    var tr = table.getElementsByTagName("tr");

    for (var i = 1; i < tr.length; i++) {
        var tdContent = tr[i].getElementsByTagName("td")[0];
        var tdAuthor = tr[i].getElementsByTagName("td")[1];
        if (tdContent || tdAuthor) {
            var txtValueContent = tdContent.textContent || tdContent.innerText;
            var txtValueAuthor = tdAuthor.textContent || tdAuthor.innerText;
            if (txtValueContent.toUpperCase().indexOf(filter) > -1 || txtValueAuthor.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}
</script>

<?php include 'footer.php'; ?>
