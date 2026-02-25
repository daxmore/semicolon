<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

require_once 'includes/db.php';
require_once 'includes/functions.php';

$user_id = $_SESSION['user_id'];
$message = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $post_id = (int)($_POST['post_id'] ?? 0);

    // Verify ownership
    $stmt = $conn->prepare("SELECT user_id FROM community_posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $post = $stmt->get_result()->fetch_assoc();

    if ($post && $post['user_id'] == $user_id) {
        if ($action === 'delete') {
            // Delete associated comments and reactions (handled by foreign keys if ON DELETE CASCADE, but manual to be safe)
            $conn->query("DELETE FROM community_comment_reactions WHERE comment_id IN (SELECT id FROM community_comments WHERE post_id = $post_id)");
            $conn->query("DELETE FROM community_comments WHERE post_id = $post_id");
            $conn->query("DELETE FROM community_post_reactions WHERE post_id = $post_id");
            
            $del_stmt = $conn->prepare("DELETE FROM community_posts WHERE id = ?");
            $del_stmt->bind_param("i", $post_id);
            if ($del_stmt->execute()) {
                $message = "Post deleted successfully.";
            }
        } elseif ($action === 'edit') {
            $new_title = trim($_POST['title'] ?? '');
            $new_description = trim($_POST['description'] ?? '');
            $new_category = trim($_POST['category'] ?? '');
            
            if (!empty($new_title) && !empty($new_description) && !empty($new_category)) {
                $upd_stmt = $conn->prepare("UPDATE community_posts SET title = ?, description = ?, category = ? WHERE id = ?");
                $upd_stmt->bind_param("sssi", $new_title, $new_description, $new_category, $post_id);
                if ($upd_stmt->execute()) {
                    $message = "Post updated successfully.";
                }
            } else {
                $message = "All fields are required for editing.";
            }
        }
    } else {
        $message = "Unauthorized action.";
    }
}

// Fetch user's posts
// We also want to fetch the count of comments for each post
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Total count
$count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM community_posts WHERE user_id = ?");
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$total_posts = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_posts / $limit);

// Fetch posts
$sql = "SELECT cp.*, (SELECT COUNT(*) FROM community_comments WHERE post_id = cp.id) as comment_count 
        FROM community_posts cp 
        WHERE cp.user_id = ? 
        ORDER BY cp.created_at DESC 
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $limit, $offset);
$stmt->execute();
$posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$categories = [
    'Frontend', 'Backend', 'Full Stack', 'App Dev', 'Game Dev', 
    'UI/UX Design', 'Graphic Design', 'Video Editing', 'Motion Graphics', 
    'Cloud Computing', 'General Tech'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Posts - Semicolon</title>
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
    <style>
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }
    </style>
</head>
<body class="antialiased bg-[#FAFAFA]">
    <?php include 'includes/header.php'; ?>

    <!-- Header Area -->
    <div class="bg-white border-b border-zinc-100 py-6 mb-10">
        <div class="container mx-auto px-6 max-w-5xl">
            <a href="profile.php" class="text-sm font-medium text-amber-600 hover:text-amber-700 mb-2 inline-block">&larr; Back to Profile</a>
            <h1 class="text-3xl font-bold text-zinc-900">Manage My Posts</h1>
            <p class="text-zinc-500 mt-2">Edit or delete the discussions you've started in the community.</p>
        </div>
    </div>

    <!-- Main Content -->
    <section class="pb-24">
        <div class="container mx-auto px-6 max-w-5xl">
            
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-xl border <?php echo strpos($message, 'successfully') !== false ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'; ?> flex flex-row items-center gap-3">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span class="font-medium"><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm overflow-hidden">
                <?php if (empty($posts)): ?>
                    <div class="p-12 text-center">
                        <div class="w-16 h-16 bg-zinc-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-zinc-900 mb-2">No Posts Yet</h2>
                        <p class="text-zinc-500 mb-6 max-w-md mx-auto">You haven't created any community posts. Start a discussion to engage with other members!</p>
                        <a href="community_create.php" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded-xl transition shadow-sm inline-block">Create Post</a>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-zinc-50 border-b border-zinc-100">
                                    <th class="px-6 py-4 text-xs font-bold text-zinc-500 uppercase tracking-wider">Post Details</th>
                                    <th class="px-6 py-4 text-xs font-bold text-zinc-500 uppercase tracking-wider w-32">Stats</th>
                                    <th class="px-6 py-4 text-xs font-bold text-zinc-500 uppercase tracking-wider w-40">Date</th>
                                    <th class="px-6 py-4 text-xs font-bold text-zinc-500 uppercase tracking-wider text-right w-40">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100">
                                <?php foreach ($posts as $post): ?>
                                    <tr class="hover:bg-zinc-50/50 transition">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-600">
                                                    <?php echo htmlspecialchars($post['category']); ?>
                                                </span>
                                            </div>
                                            <a href="community_post_detail.php?id=<?php echo $post['id']; ?>" class="font-bold text-zinc-900 hover:text-amber-600 transition block break-words">
                                                <?php echo htmlspecialchars($post['title']); ?>
                                            </a>
                                            <p class="text-sm text-zinc-500 mt-1 line-clamp-1 block truncate max-w-md"><?php echo htmlspecialchars($post['description']); ?></p>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 font-medium">
                                            <div class="flex flex-col gap-1">
                                                <span class="flex items-center gap-1.5"><svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg> <?php echo $post['upvotes']; ?> votes</span>
                                                <span class="flex items-center gap-1.5"><svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg> <?php echo $post['comment_count']; ?> msgs</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500">
                                            <?php echo date('M d, Y', strtotime($post['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            <button 
                                                onclick='openEditModal(<?php echo json_encode([
                                                    "id" => $post['id'],
                                                    "title" => $post['title'],
                                                    "category" => $post['category'],
                                                    "description" => $post['description']
                                                ]); ?>)'
                                                class="text-amber-600 hover:text-amber-900 hover:bg-amber-50 px-2 py-1 rounded transition font-medium mr-2">Edit</button>
                                            
                                            <form action="" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this post? This cannot be undone.');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-900 hover:bg-red-50 px-2 py-1 rounded transition font-medium cursor-pointer">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="px-6 py-4 border-t border-zinc-100 flex items-center justify-between">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" class="px-4 py-2 text-sm font-medium text-zinc-700 bg-white border border-zinc-200 rounded-lg hover:bg-zinc-50 transition">Previous</a>
                        <?php else: ?>
                            <span class="px-4 py-2 text-sm font-medium text-zinc-400 bg-zinc-50 border border-zinc-200 rounded-lg cursor-not-allowed">Previous</span>
                        <?php endif; ?>
                        
                        <span class="text-sm text-zinc-500 font-medium">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="px-4 py-2 text-sm font-medium text-zinc-700 bg-white border border-zinc-200 rounded-lg hover:bg-zinc-50 transition">Next</a>
                        <?php else: ?>
                            <span class="px-4 py-2 text-sm font-medium text-zinc-400 bg-zinc-50 border border-zinc-200 rounded-lg cursor-not-allowed">Next</span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Edit Post Modal -->
    <div id="editModal" class="fixed inset-0 z-50 hidden">
        <div class="modal-backdrop absolute inset-0" onclick="closeEditModal()"></div>
        <div class="relative flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl relative z-10 overflow-hidden flex flex-col max-h-[90vh]">
                <!-- Modal Header -->
                <div class="bg-zinc-50 border-b border-zinc-100 px-6 py-5 flex items-center justify-between flex-shrink-0">
                    <h2 class="text-xl font-bold text-zinc-900">Edit Post</h2>
                    <button onclick="closeEditModal()" class="text-zinc-400 hover:text-zinc-600 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <!-- Modal Body -->
                <div class="overflow-y-auto flex-1 p-6">
                    <form action="" method="POST" id="editForm" class="space-y-5">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="post_id" id="edit_post_id" value="">
                        
                        <div>
                            <label for="edit_title" class="block text-sm font-bold text-zinc-900 mb-2">Title</label>
                            <input 
                                type="text" 
                                id="edit_title" 
                                name="title" 
                                required 
                                class="w-full px-4 py-3 bg-white border border-zinc-200 rounded-xl text-zinc-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                            >
                        </div>

                        <div>
                            <label for="edit_category" class="block text-sm font-bold text-zinc-900 mb-2">Topic Category</label>
                            <select id="edit_category" name="category" required class="w-full px-4 py-3 bg-white border border-zinc-200 rounded-xl text-zinc-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>">
                                        <?php echo htmlspecialchars($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label for="edit_description" class="block text-sm font-bold text-zinc-900 mb-2">Description</label>
                            <textarea 
                                id="edit_description" 
                                name="description" 
                                rows="6" 
                                required 
                                class="w-full px-4 py-3 bg-white border border-zinc-200 rounded-xl text-zinc-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition resize-y"
                            ></textarea>
                        </div>
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="bg-zinc-50 border-t border-zinc-100 px-6 py-4 flex gap-3 justify-end flex-shrink-0">
                    <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 border border-zinc-200 text-zinc-700 font-medium rounded-xl hover:bg-white transition bg-transparent">Cancel</button>
                    <button type="button" onclick="document.getElementById('editForm').submit();" class="px-6 py-2.5 bg-amber-500 text-white font-medium rounded-xl hover:bg-amber-600 transition shadow-sm">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        function openEditModal(post) {
            document.getElementById('edit_post_id').value = post.id;
            document.getElementById('edit_title').value = post.title;
            document.getElementById('edit_category').value = post.category;
            document.getElementById('edit_description').value = post.description;
            
            document.getElementById('editModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.body.style.overflow = '';
        }
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditModal();
            }
        });
    </script>
</body>
</html>
