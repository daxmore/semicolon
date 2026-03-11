<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Handle Actions
if (isset($_POST['action'])) {
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];

    if ($action === 'toggle_status') {
        $current_status = $_POST['current_status'];
        $new_status = ($current_status === 'active') ? 'banned' : 'active';
        
        $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $user_id);
        $stmt->execute();
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }
    
    header('Location: users.php');
    exit();
}

// Fetch Users (exclude admins)
$sql = "SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC";
$result = $conn->query($sql);
$users = $result->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>

<!-- Header with count -->
<div class="flex justify-between items-center mb-6">
    <div>
        <p class="text-zinc-500 dark:text-zinc-400">Manage registered users</p>
    </div>
    <div class="text-sm text-zinc-500 dark:text-zinc-400">
        Total Users: <span class="font-bold text-zinc-900 dark:text-white"><?php echo count($users); ?></span>
    </div>
</div>

<!-- Users Table -->
<div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-zinc-50 dark:bg-zinc-800/50 border-b border-zinc-200 dark:border-zinc-700 text-xs uppercase text-zinc-500 dark:text-zinc-400 font-semibold">
                    <th class="px-6 py-4 text-left">ID</th>
                    <th class="px-6 py-4 text-left">User</th>
                    <th class="px-6 py-4 text-left">Status</th>
                    <th class="px-6 py-4 text-left">Joined</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">No users found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition">
                            <td class="px-6 py-4 text-sm text-zinc-500 dark:text-zinc-500">#<?php echo $user['id']; ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-sm">
                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <span class="font-medium text-zinc-900 dark:text-white"><?php echo htmlspecialchars($user['username']); ?></span>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400"><?php echo ucfirst($user['role']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium <?php echo $user['status'] === 'active' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Toggle Ban/Unban -->
                                    <button type="button" 
                                        onclick="showBanModal(<?php echo $user['id']; ?>, '<?php echo $user['status']; ?>', '<?php echo htmlspecialchars($user['username']); ?>')"
                                        class="px-3 py-1.5 text-sm font-medium rounded-lg transition <?php echo $user['status'] === 'active' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 hover:bg-amber-200 dark:hover:bg-amber-900/50' : 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 hover:bg-green-200 dark:hover:bg-green-900/50'; ?>">
                                        <?php echo $user['status'] === 'active' ? 'Ban' : 'Unban'; ?>
                                    </button>
                                    
                                    <!-- Delete -->
                                    <button type="button" 
                                        onclick="showDeleteModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')"
                                        class="px-3 py-1.5 text-sm font-medium rounded-lg bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 transition">
                                        Delete
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

<!-- Ban/Unban Modal -->
<div id="banModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-zinc-950/60 backdrop-blur-sm" onclick="closeBanModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-4">
        <div class="bg-white dark:bg-zinc-900 rounded-3xl shadow-2xl border border-zinc-200 dark:border-zinc-800 p-8">
            <div class="flex items-center gap-4 mb-6">
                <div id="banIconContainer" class="w-14 h-14 rounded-2xl flex items-center justify-center border-2 shadow-sm">
                    <!-- Icon will be set by JS -->
                </div>
                <div>
                    <h3 id="banModalTitle" class="font-black text-xl text-zinc-900 dark:text-white"></h3>
                    <p id="banModalDesc" class="text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest"></p>
                </div>
            </div>
            <p id="banModalMessage" class="text-zinc-600 dark:text-zinc-400 mb-8 leading-relaxed"></p>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeBanModal()" class="px-6 py-2.5 text-sm font-bold text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition">
                    Cancel
                </button>
                <form id="banForm" action="users.php" method="POST">
                    <input type="hidden" name="user_id" id="banUserId">
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="current_status" id="banCurrentStatus">
                    <button type="submit" id="banSubmitBtn" class="px-8 py-2.5 rounded-xl font-bold text-sm shadow-lg transition duration-300">
                        Confirm
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-zinc-950/60 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-4">
        <div class="bg-white dark:bg-zinc-900 rounded-3xl shadow-2xl border border-zinc-200 dark:border-zinc-800 p-8">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 bg-red-50 dark:bg-red-900/20 border-2 border-red-100 dark:border-red-900/30 rounded-2xl flex items-center justify-center text-red-600 dark:text-red-400 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-black text-xl text-zinc-900 dark:text-white">Delete User</h3>
                    <p class="text-xs font-bold text-red-500 uppercase tracking-widest">Permanent Action</p>
                </div>
            </div>
            <p id="deleteModalMessage" class="text-zinc-600 dark:text-zinc-400 mb-8 leading-relaxed"></p>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeDeleteModal()" class="px-6 py-2.5 text-sm font-bold text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition">
                    Cancel
                </button>
                <form id="deleteForm" action="users.php" method="POST">
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="px-8 py-2.5 bg-red-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-red-600/20 hover:bg-red-700 transition duration-300">
                        Delete User
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showBanModal(userId, currentStatus, username) {
    const modal = document.getElementById('banModal');
    const iconContainer = document.getElementById('banIconContainer');
    const title = document.getElementById('banModalTitle');
    const desc = document.getElementById('banModalDesc');
    const message = document.getElementById('banModalMessage');
    const submitBtn = document.getElementById('banSubmitBtn');
    
    document.getElementById('banUserId').value = userId;
    document.getElementById('banCurrentStatus').value = currentStatus;
    
    if (currentStatus === 'active') {
        // Banning user
        iconContainer.className = 'w-14 h-14 rounded-2xl flex items-center justify-center bg-amber-50 dark:bg-amber-900/20 border-2 border-amber-100 dark:border-amber-900/30 text-amber-600 dark:text-amber-400';
        iconContainer.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>';
        title.textContent = 'Ban User';
        desc.textContent = 'Suspend account';
        message.textContent = `Are you sure you want to ban "${username}"? They will not be able to log in until unbanned.`;
        submitBtn.className = 'px-8 py-2.5 rounded-xl font-bold text-sm bg-amber-600 text-white hover:bg-amber-700 shadow-lg shadow-amber-600/20';
        submitBtn.textContent = 'Ban User';
    } else {
        // Unbanning user
        iconContainer.className = 'w-14 h-14 rounded-2xl flex items-center justify-center bg-green-50 dark:bg-green-900/20 border-2 border-green-100 dark:border-green-900/30 text-green-600 dark:text-green-400';
        iconContainer.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
        title.textContent = 'Unban User';
        desc.textContent = 'Restore account';
        message.textContent = `Are you sure you want to unban "${username}"? They will be able to log in again.`;
        submitBtn.className = 'px-8 py-2.5 rounded-xl font-bold text-sm bg-green-600 text-white hover:bg-green-700 shadow-lg shadow-green-600/20';
        submitBtn.textContent = 'Unban User';
    }
    
    modal.classList.remove('hidden');
}

function closeBanModal() {
    document.getElementById('banModal').classList.add('hidden');
}

function showDeleteModal(userId, username) {
    document.getElementById('deleteUserId').value = userId;
    document.getElementById('deleteModalMessage').textContent = `Are you sure you want to permanently delete "${username}"? All their data will be removed.`;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeBanModal();
        closeDeleteModal();
    }
});
</script>

<?php include 'footer.php'; ?>
