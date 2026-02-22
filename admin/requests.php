<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db.php';
require_once '../includes/functions.php';

$message = '';
$message_type = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $request_id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM material_requests WHERE id = ?");
        if ($stmt && $stmt->bind_param('i', $request_id) && $stmt->execute()) {
            $message = 'Request deleted successfully!';
            $message_type = 'success';
        } else {
            $message = 'Failed to delete request.';
            $message_type = 'error';
        }
    } elseif (isset($_POST['request_id']) && isset($_POST['new_status'])) {
        $request_id = $_POST['request_id'];
        $new_status = $_POST['new_status'];

        // Get request details before updating
        $req_stmt = $conn->prepare("SELECT user_id, title, material_type FROM material_requests WHERE id = ?");
        $req_stmt->bind_param('i', $request_id);
        $req_stmt->execute();
        $request_data = $req_stmt->get_result()->fetch_assoc();

        $stmt = $conn->prepare("UPDATE material_requests SET status = ? WHERE id = ?");
        if ($stmt && $stmt->bind_param('si', $new_status, $request_id) && $stmt->execute()) {
            $message = 'Request status updated successfully!';
            $message_type = 'success';

            // Send notification to user
            if ($request_data && $request_data['user_id']) {
                $user_id = $request_data['user_id'];
                $material_title = $request_data['title'];
                $material_type = ucfirst($request_data['material_type']);

                if ($new_status === 'approved') {
                    $notif_title = "Request Approved!";
                    $notif_message = "Great news! Your request for \"{$material_title}\" ({$material_type}) has been approved. We'll add it to our library within 24-48 hours. Thank you for your patience!";
                } else {
                    $notif_title = "Request Update";
                    $notif_message = "Unfortunately, we couldn't fulfill your request for \"{$material_title}\" ({$material_type}) at this time. This could be due to availability or licensing restrictions. Please try requesting something else.";
                }

                create_notification($user_id, $notif_title, $notif_message);
            }
        } else {
            $message = 'Failed to update request status.';
            $message_type = 'error';
        }
    }
}

// Fetch all material requests
$requests = [];
$result = $conn->query("SELECT mr.*, u.username FROM material_requests mr LEFT JOIN users u ON mr.user_id = u.id ORDER BY mr.requested_at DESC");
if ($result) {
    $requests = $result->fetch_all(MYSQLI_ASSOC);
}

include 'header.php';
?>

<?php if ($message): ?>
    <div class="rounded-2xl p-4 mb-6 <?php echo $message_type === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
        <div class="flex items-center gap-3">
            <?php if ($message_type === 'success'): ?>
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <?php else: ?>
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            <?php endif; ?>
            <p class="font-medium"><?php echo htmlspecialchars($message); ?></p>
        </div>
    </div>
<?php endif; ?>

<!-- Header -->
<div class="flex justify-between items-center mb-6">
    <p class="text-zinc-500">Manage material requests from users</p>
    <div class="text-sm text-zinc-500">
        Total Requests: <span class="font-bold text-zinc-900"><?php echo count($requests); ?></span>
    </div>
</div>

<!-- Requests Table -->
<div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-zinc-50 border-b border-zinc-200 text-xs uppercase text-zinc-500 font-semibold">
                    <th class="px-6 py-4 text-left">ID</th>
                    <th class="px-6 py-4 text-left">User</th>
                    <th class="px-6 py-4 text-left">Type</th>
                    <th class="px-6 py-4 text-left">Title</th>
                    <th class="px-6 py-4 text-left">Details</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-center">Date</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-zinc-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 text-zinc-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p>No material requests found</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($requests as $request): ?>
                        <tr class="hover:bg-zinc-50 transition">
                            <td class="px-6 py-4 text-sm text-zinc-500">#<?php echo $request['id']; ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center text-amber-600 font-bold text-xs">
                                        <?php echo strtoupper(substr($request['username'] ?? 'G', 0, 1)); ?>
                                    </div>
                                    <span class="text-sm font-medium text-zinc-900"><?php echo htmlspecialchars($request['username'] ?? 'Guest'); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $type_colors = [
                                    'book' => 'bg-indigo-100 text-indigo-700',
                                    'paper' => 'bg-teal-100 text-teal-700',
                                    'video' => 'bg-rose-100 text-rose-700'
                                ];
                                $color = $type_colors[$request['material_type']] ?? 'bg-zinc-100 text-zinc-700';
                                ?>
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium <?php echo $color; ?>">
                                    <?php echo htmlspecialchars(ucfirst($request['material_type'])); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-zinc-900"><?php echo htmlspecialchars($request['title']); ?></td>
                            <td class="px-6 py-4 text-sm text-zinc-500 max-w-xs truncate" title="<?php echo htmlspecialchars($request['details']); ?>">
                                <?php echo htmlspecialchars($request['details'] ?: '-'); ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php
                                $status_colors = [
                                    'approved' => 'bg-green-100 text-green-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                    'pending' => 'bg-amber-100 text-amber-700'
                                ];
                                $status_color = $status_colors[$request['status']] ?? 'bg-zinc-100 text-zinc-700';
                                ?>
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium <?php echo $status_color; ?>">
                                    <?php echo htmlspecialchars(ucfirst($request['status'])); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center text-sm text-zinc-500">
                                <?php echo date('M d', strtotime($request['requested_at'])); ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <?php if ($request['status'] == 'pending'): ?>
                                        <form method="POST" action="requests.php" class="inline">
                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                            <input type="hidden" name="new_status" value="approved">
                                            <button type="submit" title="Approve" class="p-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                            </button>
                                        </form>
                                        <form method="POST" action="requests.php" class="inline">
                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                            <input type="hidden" name="new_status" value="rejected">
                                            <button type="submit" title="Reject" class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <form action="requests.php" method="post" class="inline" onsubmit="return confirm('Delete this request?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $request['id']; ?>">
                                        <button type="submit" title="Delete" class="p-2 bg-zinc-100 text-zinc-500 rounded-lg hover:bg-red-100 hover:text-red-600 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
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