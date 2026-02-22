<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Handle Actions
if (isset($_POST['action'])) {
    $post_id = $_POST['post_id'];
    $action = $_POST['action'];

    if ($action === 'delete') {
        // Delete post (Comment deletions will cascade if foreign keys are set, otherwise delete manually)
        $stmt_delete_comments = $conn->prepare("DELETE FROM community_comments WHERE post_id = ?");
        $stmt_delete_comments->bind_param("i", $post_id);
        $stmt_delete_comments->execute();

        $stmt_delete_reactions = $conn->prepare("DELETE FROM community_reactions WHERE post_id = ?");
        $stmt_delete_reactions->bind_param("i", $post_id);
        $stmt_delete_reactions->execute();

        $stmt = $conn->prepare("DELETE FROM community_posts WHERE id = ?");
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
    }
    
    header('Location: community.php');
    exit();
}

// Fetch Posts
$sql = "SELECT cp.*, u.username, u.avatar_url, 
               (SELECT COUNT(*) FROM community_comments cc WHERE cc.post_id = cp.id) as comment_count 
        FROM community_posts cp 
        LEFT JOIN users u ON cp.user_id = u.id 
        ORDER BY cp.created_at DESC";
$result = $conn->query($sql);
$posts = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

include 'header.php';
?>

<!-- Header with count -->
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-xl font-bold text-zinc-900 mb-1">Community Moderation</h2>
        <p class="text-zinc-500">Manage all community discussions</p>
    </div>
    <div class="text-sm text-zinc-500">
        Total Posts: <span class="font-bold text-amber-600"><?php echo count($posts); ?></span>
    </div>
</div>

<!-- Posts Table -->
<div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden">
    <div class="p-4 border-b border-zinc-100">
        <input type="text" id="searchInput" onkeyup="searchPosts()" placeholder="Search discussions by title or author..." 
            class="w-full px-4 py-2 bg-zinc-50 border-0 rounded-xl focus:ring-2 focus:ring-amber-500">
    </div>
    <div class="overflow-x-auto">
        <table id="postsTable" class="w-full">
            <thead>
                <tr class="bg-zinc-50 border-b border-zinc-200 text-xs uppercase text-zinc-500 font-semibold">
                    <th class="px-6 py-4 text-left">Post</th>
                    <th class="px-6 py-4 text-left">Author</th>
                    <th class="px-6 py-4 text-left">Category</th>
                    <th class="px-6 py-4 text-center">Stats</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                <?php if (empty($posts)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-zinc-500">No community posts found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <tr class="hover:bg-zinc-50 transition">
                            <!-- Post Info -->
                            <td class="px-6 py-4 max-w-md">
                                <div class="flex items-start gap-3">
                                    <?php if (!empty($post['image_url'])): ?>
                                        <div class="w-12 h-12 bg-zinc-100 rounded-lg overflow-hidden flex-shrink-0">
                                            <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="Thumbnail" class="w-full h-full object-cover">
                                        </div>
                                    <?php endif; ?>
                                    <div class="min-w-0">
                                        <a href="../community_post_detail.php?id=<?php echo $post['id']; ?>" target="_blank" class="font-semibold text-zinc-900 hover:text-amber-600 truncate block transition">
                                            <?php echo htmlspecialchars($post['title']); ?>
                                        </a>
                                        <p class="text-sm text-zinc-500 line-clamp-1 mt-0.5">
                                            <?php echo htmlspecialchars($post['description'] ?? ''); ?>
                                        </p>
                                        <span class="text-[10px] text-zinc-400 mt-1 block"><?php echo date('M d, Y h:ia', strtotime($post['created_at'])); ?></span>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Author -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 font-bold text-xs overflow-hidden">
                                        <?php if (!empty($post['avatar_url'])): ?>
                                            <img src="../<?php echo htmlspecialchars($post['avatar_url']); ?>" alt="Avatar" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <?php echo strtoupper(substr($post['username'] ?? 'U', 0, 1)); ?>
                                        <?php endif; ?>
                                    </div>
                                    <span class="font-medium text-zinc-900 text-sm"><?php echo htmlspecialchars($post['username'] ?? 'Anonymous'); ?></span>
                                </div>
                            </td>
                            
                            <!-- Category -->
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold bg-zinc-100 text-zinc-700 tracking-wide uppercase">
                                    <?php echo htmlspecialchars($post['category']); ?>
                                </span>
                            </td>
                            
                            <!-- Stats -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-4 text-xs font-medium text-zinc-500">
                                    <span class="flex items-center gap-1" title="Upvotes">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        <?php echo $post['upvotes']; ?>
                                    </span>
                                    <span class="flex items-center gap-1" title="Downvotes">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-rose-500 transform rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        <?php echo $post['downvotes']; ?>
                                    </span>
                                    <span class="flex items-center gap-1" title="Comments">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                        <?php echo $post['comment_count']; ?>
                                    </span>
                                </div>
                            </td>
                            
                            <!-- Actions -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- View Live -->
                                    <a href="../community_post_detail.php?id=<?php echo $post['id']; ?>" target="_blank" title="View Live Post" class="p-2 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                    
                                    <!-- Moderate Comments -->
                                    <a href="community_comments.php?post_id=<?php echo $post['id']; ?>" title="Moderate Comments" class="p-2 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-100 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                        </svg>
                                    </a>
                                    
                                    <!-- Delete -->
                                    <button type="button" 
                                        onclick="showDeleteModal(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars(addslashes($post['title'])); ?>')"
                                        class="p-2 text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition" title="Delete Post">
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
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center text-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-zinc-900">Delete Post</h3>
                    <p class="text-sm text-zinc-500">This action cannot be undone</p>
                </div>
            </div>
            <p id="deleteModalMessage" class="text-zinc-600 mb-6"></p>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-zinc-600 hover:text-zinc-900 font-medium transition">
                    Cancel
                </button>
                <form id="deleteForm" action="community.php" method="POST">
                    <input type="hidden" name="post_id" id="deletePostId">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition">
                        Delete Post
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showDeleteModal(postId, postTitle) {
    document.getElementById('deletePostId').value = postId;
    document.getElementById('deleteModalMessage').textContent = `Are you sure you want to permanently delete the discussion "${postTitle}"? All associated comments and reactions will also be removed.`;
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

function searchPosts() {
    var input = document.getElementById("searchInput");
    var filter = input.value.toUpperCase();
    var table = document.getElementById("postsTable");
    var tr = table.getElementsByTagName("tr");

    for (var i = 1; i < tr.length; i++) {
        var tdTitle = tr[i].getElementsByTagName("td")[0];
        var tdAuthor = tr[i].getElementsByTagName("td")[1];
        if (tdTitle || tdAuthor) {
            var txtValueTitle = tdTitle.textContent || tdTitle.innerText;
            var txtValueAuthor = tdAuthor.textContent || tdAuthor.innerText;
            if (txtValueTitle.toUpperCase().indexOf(filter) > -1 || txtValueAuthor.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}
</script>

<?php include 'footer.php'; ?>
