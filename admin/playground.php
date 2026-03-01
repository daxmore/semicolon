<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Handle Actions (Delete Snippet)
if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'delete') {
        $snippet_id = (int)$_POST['snippet_id'];
        $stmt = $conn->prepare("DELETE FROM code_snippets WHERE id = ?");
        $stmt->bind_param("i", $snippet_id);
        $stmt->execute();
    }
    
    header('Location: playground.php');
    exit();
}

// Fetch Snippets
$sql = "SELECT s.*, u.username FROM code_snippets s JOIN users u ON s.user_id = u.id ORDER BY s.created_at DESC";
$result = $conn->query($sql);
$snippets = $result->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>

<!-- Header with count -->
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-xl font-bold text-zinc-900 dark:text-white">Code Snippets</h2>
        <p class="text-zinc-500 dark:text-zinc-400">Manage user playground creations</p>
    </div>
    <div class="text-sm text-zinc-500 dark:text-zinc-400">
        Total Snippets: <span class="font-bold text-zinc-900 dark:text-white"><?php echo count($snippets); ?></span>
    </div>
</div>

<!-- Snippets Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (empty($snippets)): ?>
        <div class="col-span-full py-12 text-center text-zinc-500 dark:text-zinc-400 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800">
            No snippets have been created by users yet.
        </div>
    <?php else: ?>
        <?php foreach ($snippets as $snippet): ?>
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm">
                            <?php echo strtoupper(substr($snippet['username'], 0, 1)); ?>
                        </div>
                        <div>
                            <span class="block font-medium text-zinc-900 dark:text-white"><?php echo htmlspecialchars($snippet['username']); ?></span>
                            <span class="block text-xs text-zinc-500"><?php echo date('M d, Y', strtotime($snippet['created_at'])); ?></span>
                        </div>
                    </div>
                    
                    <h3 class="font-bold text-lg text-zinc-900 dark:text-white mb-2 truncate">
                        <?php echo htmlspecialchars($snippet['title']); ?>
                    </h3>
                    
                    <div class="flex items-center gap-2 mb-4">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-orange-100 text-orange-700">HTML</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-700">CSS</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-yellow-100 text-yellow-700">JS</span>
                    </div>
                </div>
                
                <div class="flex justify-end gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <a href="../playground.php?id=<?php echo $snippet['id']; ?>" target="_blank" class="px-3 py-1.5 text-sm font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">View Code</a>
                    <button onclick="showDeleteModal(<?php echo $snippet['id']; ?>, '<?php echo htmlspecialchars(addslashes($snippet['title'])); ?>')" class="px-3 py-1.5 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition">Delete</button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-xl p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center text-red-600 flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-zinc-900 dark:text-white">Delete Snippet</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">This action cannot be undone.</p>
                </div>
            </div>
            <p id="deleteModalMessage" class="text-zinc-600 dark:text-zinc-300 mb-6"></p>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-zinc-600 dark:text-zinc-400 font-medium hover:bg-zinc-50 dark:hover:bg-zinc-800 rounded-xl transition">Cancel</button>
                <form action="playground.php" method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="snippet_id" id="deleteSnippetId">
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-xl transition">Yes, Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showDeleteModal(id, title) {
    document.getElementById('deleteSnippetId').value = id;
    document.getElementById('deleteModalMessage').innerText = `Are you sure you want to permanently delete the snippet "${title}"?`;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
});
</script>

<?php include 'footer.php'; ?>
