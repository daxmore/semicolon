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

<div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-10 px-4 sm:px-0">
    <div>
        <h2 class="text-3xl font-black text-zinc-900 dark:text-white tracking-tight">Community Reports</h2>
        <p class="text-zinc-500 dark:text-zinc-400 mt-2">Monitor and moderate reported content from the community.</p>
    </div>
</div>

<div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 overflow-hidden shadow-xl mx-4 sm:mx-0">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-zinc-50 dark:bg-zinc-800/50 border-b border-zinc-200 dark:border-zinc-800">
                    <th class="px-6 py-5 text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest">Reporter Info</th>
                    <th class="px-6 py-5 text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest">Target Content</th>
                    <th class="px-6 py-5 text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest">Reason / Flag</th>
                    <th class="px-6 py-5 text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest text-right">Moderation</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                <?php if (empty($reports)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="w-16 h-16 bg-zinc-50 dark:bg-zinc-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-zinc-500 dark:text-zinc-400 font-medium">No pending reports.</p>
                            <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">Community looks safe and clean!</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reports as $r): ?>
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition-colors group">
                            <td class="px-6 py-6 border-b border-zinc-100 dark:border-zinc-800">
                                <p class="font-bold text-zinc-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors uppercase text-xs tracking-tight"><?php echo htmlspecialchars($r['reporter_name']); ?></p>
                                <p class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 mt-0.5"><?php echo date('M j, Y g:i a', strtotime($r['created_at'])); ?></p>
                            </td>
                            <td class="px-6 py-6 border-b border-zinc-100 dark:border-zinc-800">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="inline-flex px-2 py-0.5 bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 text-[9px] font-black uppercase rounded border border-amber-100 dark:border-amber-900/30">
                                        <?php echo htmlspecialchars($r['target_type']); ?>
                                    </span>
                                    <span class="text-[9px] font-bold text-zinc-400 font-mono">#<?php echo $r['target_id']; ?></span>
                                </div>
                                <?php
                                $preview = "Unknown Content";
                                $link = null;
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
                                <?php if ($link): ?>
                                    <a href="<?php echo $link; ?>" target="_blank" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 underline flex items-center gap-1 group/link">
                                        Launch Content 
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 transform group-hover/link:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                    </a>
                                <?php else: ?>
                                    <span class="text-[10px] font-bold text-rose-500 uppercase flex items-center gap-1 italic">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                        Resource Deleted
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-6 border-b border-zinc-100 dark:border-zinc-800 max-w-xs xl:max-w-md">
                                <div class="p-4 bg-red-50 dark:bg-rose-950/20 rounded-2xl border border-red-100 dark:border-rose-900/30">
                                    <p class="text-xs font-black text-rose-500 dark:text-rose-400 uppercase tracking-widest mb-1 opacity-60">Violation Reason</p>
                                    <p class="text-sm font-bold text-zinc-700 dark:text-zinc-300 line-clamp-3 leading-relaxed">"<?php echo htmlspecialchars($r['reason']); ?>"</p>
                                </div>
                            </td>
                            <td class="px-6 py-6 border-b border-zinc-100 dark:border-zinc-800 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <form action="" method="POST" class="inline">
                                        <input type="hidden" name="action" value="dismiss">
                                        <input type="hidden" name="report_id" value="<?php echo $r['id']; ?>">
                                        <button type="submit" class="px-4 py-2 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 rounded-xl text-[11px] font-black uppercase transition shadow-sm">Dismiss</button>
                                    </form>
                                    <form action="" method="POST" class="inline">
                                        <input type="hidden" name="action" value="delete_content">
                                        <input type="hidden" name="report_id" value="<?php echo $r['id']; ?>">
                                        <button type="submit" onclick="return confirm('Delete this reported content permanently?')" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-[11px] font-black uppercase transition shadow-lg shadow-rose-600/20">Purge</button>
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
