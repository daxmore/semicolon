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
            add_user_xp($target_user_id, $xp_amount);
            $message = "Added {$xp_amount} XP successfully.";
            $message_type = "success";
        }
    } elseif ($_POST['action'] === 'reset_progress') {
        $conn->query("DELETE FROM user_skill_progress WHERE user_id = {$target_user_id}");
        $conn->query("DELETE FROM user_attempts WHERE user_id = {$target_user_id}");
        $conn->query("UPDATE users SET xp_total = 0, level = 1 WHERE id = {$target_user_id}");
        $message = "Progress reset successfully for user ID {$target_user_id}.";
        $message_type = "success";
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

<div class="max-w-6xl mx-auto">
            
            <header class="mb-10">
                <h1 class="text-3xl font-bold text-gray-900">Manage Users</h1>
                <p class="text-gray-500 mt-2">Adjust user XP balances and monitor their progress in the Academy.</p>
            </header>

            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $message_type == 'success' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-red-50 text-red-600 border border-red-200'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-200 text-sm font-medium text-gray-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">User</th>
                            <th class="px-6 py-4">Level</th>
                            <th class="px-6 py-4">XP Balance</th>
                            <th class="px-6 py-4">Active Skills</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-sm">
                        <?php foreach($users as $user): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($user['username']); ?></div>
                                <div class="text-gray-500 text-xs"><?php echo htmlspecialchars($user['email']); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full bg-indigo-100 text-indigo-700 font-bold text-xs uppercase">
                                    Lvl <?php echo (int)($user['level'] ?: 1); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 font-mono font-medium text-gray-600">
                                <?php echo number_format((int)$user['xp']); ?> XP
                            </td>
                            <td class="px-6 py-4">
                                <?php echo (int)$user['active_skills']; ?>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2 flex justify-end items-center">
                                
                                <form method="POST" action="manage_users.php" class="inline-flex items-center">
                                    <input type="hidden" name="action" value="add_xp">
                                    <input type="hidden" name="target_user_id" value="<?php echo $user['id']; ?>">
                                    <input type="number" name="xp_amount" value="50" step="10" class="w-20 px-2 py-1 text-xs border-gray-300 rounded shadow-sm focus:ring-indigo-500 mr-2">
                                    <button type="submit" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded transition">
                                        Add XP
                                    </button>
                                </form>

                                <form method="POST" action="manage_users.php" onsubmit="return confirm('Are you sure you want to completely wipe progress for this user?');" class="inline-block">
                                    <input type="hidden" name="action" value="reset_progress">
                                    <input type="hidden" name="target_user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded transition">
                                        Reset Progress
                                    </button>
                                </form>

                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($users)): ?>
                        <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No users found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

</div>

<?php include 'footer.php'; ?>
