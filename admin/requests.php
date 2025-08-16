<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if admin is logged in, otherwise redirect
// session_start();
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header('Location: login.php');
//     exit();
// }

$message = '';
$message_type = '';

// Handle request status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];
    $new_status = $_POST['new_status'];

    $stmt = $conn->prepare("UPDATE material_requests SET status = ? WHERE id = ?");
    if ($stmt->bind_param('si', $new_status, $request_id) && $stmt->execute()) {
        $message = 'Request status updated successfully!';
        $message_type = 'success';
    } else {
        $message = 'Failed to update request status.';
        $message_type = 'error';
    }
}

// Fetch all material requests
$stmt = $conn->prepare("SELECT mr.*, u.username FROM material_requests mr LEFT JOIN users u ON mr.user_id = u.id ORDER BY mr.requested_at ASC");
$stmt->execute();
$requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Material Requests</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/index.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Manage Material Requests</h1>

        <?php if ($message): ?>
            <div class="<?php echo $message_type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'; ?> border px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-white shadow-md rounded my-6 overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">ID</th>
                        <th class="py-3 px-6 text-left">User</th>
                        <th class="py-3 px-6 text-left">Type</th>
                        <th class="py-3 px-6 text-left">Title</th>
                        <th class="py-3 px-6 text-left">Author/Publisher</th>
                        <th class="py-3 px-6 text-left">Details</th>
                        <th class="py-3 px-6 text-center">Status</th>
                        <th class="py-3 px-6 text-center">Requested At</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    <?php foreach ($requests as $request) : ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($request['id']); ?></td>
                            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($request['username'] ?? 'Guest'); ?></td>
                            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars(ucfirst($request['material_type'])); ?></td>
                            <td class="py-3 px-6 text-left font-medium"><?php echo htmlspecialchars($request['title']); ?></td>
                            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($request['author_publisher']); ?></td>
                            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($request['details']); ?></td>
                            <td class="py-3 px-6 text-center">
                                <span class="<?php
                                    if ($request['status'] == 'approved') echo 'bg-green-200 text-green-600';
                                    else if ($request['status'] == 'rejected') echo 'bg-red-200 text-red-600';
                                    else echo 'bg-yellow-200 text-yellow-600';
                                ?> py-1 px-3 rounded-full text-xs">
                                    <?php echo htmlspecialchars(ucfirst($request['status'])); ?>
                                </span>
                            </td>
                            <td class="py-3 px-6 text-center"><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($request['requested_at']))); ?></td>
                            <td class="py-3 px-6 text-center">
                                <div class="flex item-center justify-center space-x-2">
                                    <?php if ($request['status'] == 'pending') : ?>
                                        <form method="POST" action="requests.php" class="inline-block">
                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                            <input type="hidden" name="new_status" value="approved">
                                            <button type="submit" title="Approve" class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Approve
                                            </button>
                                        </form>
                                        <form method="POST" action="requests.php" class="inline-block">
                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                            <input type="hidden" name="new_status" value="rejected">
                                            <button type="submit" title="Reject" class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Reject
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="9" class="py-3 px-6 text-center">No material requests found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>