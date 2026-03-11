<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$message = '';
$message_type = '';

// Handle actions (Reset progress, adjust XP)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $target_user_id = intval($_POST['target_user_id']);
    
    if ($_POST['action'] === 'add_xp') {
        $xp_amount = intval($_POST['xp_amount']);
        if ($xp_amount != 0) {
            add_user_xp($target_user_id, $xp_amount, true);
            $message = "Added {$xp_amount} XP successfully.";
            $message_type = "success";
        }
    } elseif ($_POST['action'] === 'reset_progress') {
        $conn->query("DELETE FROM user_skill_progress WHERE user_id = {$target_user_id}");
        $conn->query("DELETE FROM user_attempts WHERE user_id = {$target_user_id}");
        $conn->query("UPDATE users SET xp_total = 0, xp_weekly = 0, daily_xp_earned = 0, level = 1 WHERE id = {$target_user_id}");
        $message = "Progress reset successfully for user ID {$target_user_id}.";
        $message_type = "success";
    } elseif ($_POST['action'] === 'edit_user') {
        $edit_username = trim($_POST['edit_username']);
        $edit_email = trim($_POST['edit_email']);
        
        $stmt_upd = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt_upd->bind_param("ssi", $edit_username, $edit_email, $target_user_id);
        if ($stmt_upd->execute()) {
            $message = "User updated successfully.";
            $message_type = "success";
        } else {
            $message = "Failed to update user.";
            $message_type = "error";
        }
    }
}

// Fetch all users with basic stats
$query = "
    SELECT u.id, u.username, u.email, u.xp_total as xp, u.level,
           (SELECT COUNT(*) FROM user_skill_progress WHERE user_id = u.id) as active_skills
    FROM users u
    ORDER BY u.id DESC
";
$users = $conn->query($query)->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>

<div class="max-w-6xl mx-auto px-4">
            
            <header class="mb-10">
                <h1 class="text-3xl font-black text-zinc-900 dark:text-white tracking-tight">Manage Users</h1>
                <p class="text-zinc-500 dark:text-zinc-400 mt-2">Adjust user XP balances and monitor their progress in the Academy.</p>
            </header>

            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-2xl animate-in fade-in slide-in-from-top-4 duration-300 <?php echo $message_type == 'success' ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50' : 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800/50'; ?>">
                    <div class="flex items-center gap-3 font-bold">
                        <?php if($message_type == 'success'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                        <?php else: ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="bg-white dark:bg-zinc-900 rounded-3xl shadow-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/50 border-b border-zinc-200 dark:border-zinc-800 text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest">
                        <tr>
                            <th class="px-6 py-5">User</th>
                            <th class="px-6 py-5 font-center">Level</th>
                            <th class="px-6 py-5">XP Balance</th>
                            <th class="px-6 py-5">Active Skills</th>
                            <th class="px-6 py-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 text-sm">
                        <?php foreach($users as $user): ?>
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 transition-colors">
                            <td class="px-6 py-6 min-w-[280px]">
                                <div class="font-bold text-zinc-900 dark:text-white"><?php echo htmlspecialchars($user['username']); ?></div>
                                <div class="text-zinc-500 dark:text-zinc-500 text-xs"><?php echo htmlspecialchars($user['email']); ?></div>
                                <button onclick="toggleEditForm('<?php echo $user['id']; ?>')" class="text-[10px] font-black uppercase tracking-tighter text-indigo-500 hover:text-indigo-400 mt-2 flex items-center gap-1 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    Edit Details
                                </button>
                                
                                <form id="edit-form-<?php echo $user['id']; ?>" method="POST" action="manage_users.php" class="hidden mt-4 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl border border-zinc-200 dark:border-zinc-700 animate-in fade-in zoom-in-95 duration-200 max-w-sm">
                                    <input type="hidden" name="action" value="edit_user">
                                    <input type="hidden" name="target_user_id" value="<?php echo $user['id']; ?>">
                                    <div class="mb-3">
                                        <label class="block text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase mb-1">Username</label>
                                        <input type="text" name="edit_username" value="<?php echo htmlspecialchars($user['username']); ?>" class="w-full px-3 py-2 text-sm bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition shadow-sm" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="block text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase mb-1">Email</label>
                                        <input type="email" name="edit_email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full px-3 py-2 text-sm bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition shadow-sm" required>
                                    </div>
                                    <div class="flex justify-end gap-2 mt-4">
                                        <button type="button" onclick="toggleEditForm('<?php echo $user['id']; ?>')" class="px-3 py-2 text-xs font-bold text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition">Cancel</button>
                                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-600/20 transition duration-300">Save Changes</button>
                                    </div>
                                </form>
                            </td>
                            <td class="px-6 py-6 text-center">
                                <span class="px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 font-bold text-xs uppercase shadow-sm border border-indigo-100 dark:border-indigo-900/30">
                                    Lvl <?php echo (int)($user['level'] ?: 1); ?>
                                </span>
                            </td>
                            <td class="px-6 py-6 font-mono font-bold text-zinc-600 dark:text-zinc-400">
                                <?php echo number_format((int)$user['xp']); ?> <span class="text-[10px] text-zinc-400 uppercase">XP</span>
                            </td>
                            <td class="px-6 py-6">
                                <span class="px-2.5 py-1 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 font-bold text-xs">
                                    <?php echo (int)$user['active_skills']; ?> Skills
                                </span>
                            </td>
                            <td class="px-6 py-6 text-right">
                                <div class="flex flex-col sm:flex-row items-center justify-end gap-2">
                                    <form method="POST" action="manage_users.php" class="flex items-center">
                                        <input type="hidden" name="action" value="add_xp">
                                        <input type="hidden" name="target_user_id" value="<?php echo $user['id']; ?>">
                                        <div class="relative group">
                                            <input type="number" name="xp_amount" value="50" step="10" class="w-20 px-3 py-2 text-xs bg-zinc-50 dark:bg-zinc-800 border-2 border-zinc-100 dark:border-zinc-700 rounded-xl text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none transition">
                                        </div>
                                        <button type="submit" class="ml-2 px-4 py-2 text-xs font-black uppercase text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl hover:bg-emerald-100 dark:hover:bg-emerald-900/40 border-2 border-emerald-100 dark:border-emerald-900/20 transition-all shadow-sm">
                                            Add
                                        </button>
                                    </form>

                                    <form method="POST" action="manage_users.php" onsubmit="return confirm('Are you sure you want to completely wipe progress for this user?');" class="ml-2">
                                        <input type="hidden" name="action" value="reset_progress">
                                        <input type="hidden" name="target_user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="px-4 py-2 text-xs font-black uppercase text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20 rounded-xl hover:bg-rose-100 dark:hover:bg-rose-900/40 border-2 border-rose-100 dark:border-rose-900/20 transition-all shadow-sm">
                                            Reset
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($users)): ?>
                        <tr><td colspan="5" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400 font-medium">No users found in the system.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

</div>

<script>
function toggleEditForm(userId) {
    const form = document.getElementById('edit-form-' + userId);
    if (form.classList.contains('hidden')) {
        form.classList.remove('hidden');
    } else {
        form.classList.add('hidden');
    }
}
</script>

<?php include 'footer.php'; ?>
