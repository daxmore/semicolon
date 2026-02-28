<?php
// admin/manage_reports.php
session_start();
require_once '../includes/db.php';

// Check if admin
$stmt_role = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt_role->bind_param("i", $_SESSION['user_id']);
$stmt_role->execute();
$role_res = $stmt_role->get_result()->fetch_assoc();
if (!$role_res || $role_res['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $report_id = (int)$_POST['report_id'];
    $action = $_POST['action'];

    if ($action === 'dismiss') {
        $stmt_upd = $conn->prepare("UPDATE community_reports SET status = 'dismissed' WHERE id = ?");
        $stmt_upd->bind_param("i", $report_id);
        $stmt_upd->execute();
        
        if (!isset($_SESSION['toasts'])) $_SESSION['toasts'] = [];
        $_SESSION['toasts'][] = ['type' => 'info', 'message' => 'Report dismissed.'];
    } 
    elseif ($action === 'delete_content') {
        $stmt_get = $conn->prepare("SELECT target_type, target_id FROM community_reports WHERE id = ?");
        $stmt_get->bind_param("i", $report_id);
        $stmt_get->execute();
        $target = $stmt_get->get_result()->fetch_assoc();

        if ($target) {
            $ttype = $target['target_type'];
            $tid = $target['target_id'];
            
            if ($ttype === 'post') {
                $del = $conn->prepare("DELETE FROM community_posts WHERE id = ?");
                $del->bind_param("i", $tid);
                $del->execute();
            } else {
                $del = $conn->prepare("DELETE FROM community_comments WHERE id = ?");
                $del->bind_param("i", $tid);
                $del->execute();
            }

            $upd = $conn->prepare("UPDATE community_reports SET status = 'reviewed' WHERE target_type = ? AND target_id = ?");
            $upd->bind_param("si", $ttype, $tid);
            $upd->execute();
            
            if (!isset($_SESSION['toasts'])) $_SESSION['toasts'] = [];
            $_SESSION['toasts'][] = ['type' => 'success', 'message' => 'Reported content deleted.'];
        }
    }
    header('Location: manage_reports.php');
    exit();
}

// Fetch pending reports
$query = "SELECT cr.*, u.username as reporter_name 
          FROM community_reports cr 
          JOIN users u ON cr.user_id = u.id 
          WHERE cr.status = 'pending' 
          ORDER BY cr.created_at DESC";
$reports = $conn->query($query)->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>

<div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-8">
    <div>
        <h2 class="text-2xl font-bold text-zinc-900">Manage Reports</h2>
        <p class="text-zinc-500">Review community reports and moderate content.</p>
    </div>
</div>

<div class="bg-white rounded-[2rem] border border-zinc-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-zinc-50/50 border-b border-zinc-200">
                    <th class="px-6 py-4 text-xs font-bold text-zinc-500 uppercase tracking-wider">Report Info</th>
                    <th class="px-6 py-4 text-xs font-bold text-zinc-500 uppercase tracking-wider">Target</th>
                    <th class="px-6 py-4 text-xs font-bold text-zinc-500 uppercase tracking-wider">Reason</th>
                    <th class="px-6 py-4 text-xs font-bold text-zinc-500 uppercase tracking-wider whitespace-nowrap text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                <?php if (empty($reports)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="w-16 h-16 bg-zinc-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-zinc-500 font-medium">No pending reports.</p>
                            <p class="text-xs text-zinc-400 mt-1">Community looks safe and clean!</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reports as $r): ?>
                        <tr class="hover:bg-zinc-50/50 transition-colors">
                            <td class="px-6 py-4 border-b border-zinc-100">
                                <p class="text-sm font-bold text-zinc-900"><?php echo htmlspecialchars($r['reporter_name']); ?></p>
                                <p class="text-xs text-zinc-500"><?php echo date('M j, Y g:i a', strtotime($r['created_at'])); ?></p>
                            </td>
                            <td class="px-6 py-4 border-b border-zinc-100">
                                <span class="inline-flex px-2 py-1 bg-amber-50 text-amber-600 text-[10px] font-bold uppercase rounded-md mb-1">
                                    <?php echo htmlspecialchars($r['target_type']); ?>
                                </span>
                                <p class="text-xs text-zinc-700 font-mono">ID: <?php echo $r['target_id']; ?></p>
                                <?php
                                // Fetch target text preview
                                $preview = "Unknown";
                                if ($r['target_type'] === 'post') {
                                    $stmt = $conn->prepare("SELECT title as content FROM community_posts WHERE id = ?");
                                    $stmt->bind_param("i", $r['target_id']);
                                    $stmt->execute();
                                    $res = $stmt->get_result()->fetch_assoc();
                                    if ($res) {
                                        $preview = $res['content'];
                                        $link = "../community_post_detail.php?id=" . $r['target_id'];
                                    }
                                } else {
                                    $stmt = $conn->prepare("SELECT content, post_id FROM community_comments WHERE id = ?");
                                    $stmt->bind_param("i", $r['target_id']);
                                    $stmt->execute();
                                    $res = $stmt->get_result()->fetch_assoc();
                                    if ($res) {
                                        $preview = $res['content'];
                                        $link = "../community_post_detail.php?id=" . $res['post_id'] . "#comment-" . $r['target_id'];
                                    }
                                }
                                ?>
                                <?php if (isset($link)): ?>
                                    <a href="<?php echo $link; ?>" target="_blank" class="text-xs text-indigo-600 hover:underline line-clamp-1 mt-1 font-medium">View Content &rarr;</a>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 border-b border-zinc-100 max-w-xs xl:max-w-md">
                                <p class="text-sm text-zinc-700 line-clamp-3 bg-red-50 p-2 rounded-lg border border-red-100">"<?php echo htmlspecialchars($r['reason']); ?>"</p>
                            </td>
                            <td class="px-6 py-4 border-b border-zinc-100 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <form action="" method="POST" class="inline">
                                        <input type="hidden" name="action" value="dismiss">
                                        <input type="hidden" name="report_id" value="<?php echo $r['id']; ?>">
                                        <button type="submit" class="px-3 py-1.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-lg text-xs font-bold transition">Dismiss</button>
                                    </form>
                                    <form action="" method="POST" class="inline">
                                        <input type="hidden" name="action" value="delete_content">
                                        <input type="hidden" name="report_id" value="<?php echo $r['id']; ?>">
                                        <button type="submit" onclick="return confirm('Delete this reported content permanently?')" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-bold transition">Delete Content</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
