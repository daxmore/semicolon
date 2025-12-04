<?php
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
        <p class="text-zinc-500">Manage registered users</p>
    </div>
    <div class="text-sm text-zinc-500">
        Total Users: <span class="font-bold text-zinc-900"><?php echo count($users); ?></span>
    </div>
</div>

<!-- Users Table -->
<div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-zinc-50 border-b border-zinc-200 text-xs uppercase text-zinc-500 font-semibold">
                    <th class="px-6 py-4 text-left">ID</th>
                    <th class="px-6 py-4 text-left">User</th>
                    <th class="px-6 py-4 text-left">Status</th>
                    <th class="px-6 py-4 text-left">Joined</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-zinc-500">No users found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-zinc-50 transition">
                            <td class="px-6 py-4 text-sm text-zinc-500">#<?php echo $user['id']; ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm">
                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <span class="font-medium text-zinc-900"><?php echo htmlspecialchars($user['username']); ?></span>
                                        <p class="text-xs text-zinc-500"><?php echo ucfirst($user['role']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium <?php echo $user['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-zinc-500">
                                <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Toggle Ban/Unban -->
                                    <button type="button" 
                                        onclick="showBanModal(<?php echo $user['id']; ?>, '<?php echo $user['status']; ?>', '<?php echo htmlspecialchars($user['username']); ?>')"
                                        class="px-3 py-1.5 text-sm font-medium rounded-lg transition <?php echo $user['status'] === 'active' ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' : 'bg-green-100 text-green-700 hover:bg-green-200'; ?>">
                                        <?php echo $user['status'] === 'active' ? 'Ban' : 'Unban'; ?>
                                    </button>
                                    
                                    <!-- Delete -->
                                    <button type="button" 
                                        onclick="showDeleteModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')"
                                        class="px-3 py-1.5 text-sm font-medium rounded-lg bg-red-100 text-red-700 hover:bg-red-200 transition">
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
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeBanModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <div class="flex items-center gap-4 mb-4">
                <div id="banIconContainer" class="w-12 h-12 rounded-full flex items-center justify-center">
                    <!-- Icon will be set by JS -->
                </div>
                <div>
                    <h3 id="banModalTitle" class="font-bold text-lg text-zinc-900"></h3>
                    <p id="banModalDesc" class="text-sm text-zinc-500"></p>
                </div>
            </div>
            <p id="banModalMessage" class="text-zinc-600 mb-6"></p>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeBanModal()" class="px-4 py-2 text-zinc-600 hover:text-zinc-900 font-medium transition">
                    Cancel
                </button>
                <form id="banForm" action="users.php" method="POST">
                    <input type="hidden" name="user_id" id="banUserId">
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="current_status" id="banCurrentStatus">
                    <button type="submit" id="banSubmitBtn" class="px-4 py-2 rounded-lg font-medium transition">
                        Confirm
                    </button>
                </form>
            </div>
        </div>
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
                    <h3 class="font-bold text-lg text-zinc-900">Delete User</h3>
                    <p class="text-sm text-zinc-500">This action cannot be undone</p>
                </div>
            </div>
            <p id="deleteModalMessage" class="text-zinc-600 mb-6"></p>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-zinc-600 hover:text-zinc-900 font-medium transition">
                    Cancel
                </button>
                <form id="deleteForm" action="users.php" method="POST">
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition">
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
        iconContainer.className = 'w-12 h-12 rounded-full flex items-center justify-center bg-amber-100 text-amber-600';
        iconContainer.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>';
        title.textContent = 'Ban User';
        desc.textContent = 'Suspend this user\'s account';
        message.textContent = `Are you sure you want to ban "${username}"? They will not be able to log in until unbanned.`;
        submitBtn.className = 'px-4 py-2 rounded-lg font-medium transition bg-amber-600 text-white hover:bg-amber-700';
        submitBtn.textContent = 'Ban User';
    } else {
        // Unbanning user
        iconContainer.className = 'w-12 h-12 rounded-full flex items-center justify-center bg-green-100 text-green-600';
        iconContainer.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
        title.textContent = 'Unban User';
        desc.textContent = 'Restore this user\'s account';
        message.textContent = `Are you sure you want to unban "${username}"? They will be able to log in again.`;
        submitBtn.className = 'px-4 py-2 rounded-lg font-medium transition bg-green-600 text-white hover:bg-green-700';
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
