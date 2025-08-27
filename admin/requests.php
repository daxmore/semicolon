<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
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

        $stmt = $conn->prepare("UPDATE material_requests SET status = ? WHERE id = ?");
        if ($stmt && $stmt->bind_param('si', $new_status, $request_id) && $stmt->execute()) {
            $message = 'Request status updated successfully!';
            $message_type = 'success';
        } else {
            $message = 'Failed to update request status.';
            $message_type = 'error';
        }
    }
}


// Fetch all material requests
$stmt = $conn->prepare("SELECT mr.*, u.username FROM material_requests mr LEFT JOIN users u ON mr.user_id = u.id ORDER BY mr.requested_at DESC");
$stmt->execute();
$requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Material Requests</title>
    <link href="../assets/css/index.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/index.css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-gray-100">
    <?php include 'header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Manage Material Requests</h1>

        <?php if ($message): ?>
            <div class="<?php echo $message_type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'; ?> border px-4 py-3 rounded relative mb-6"
                role="alert">
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
                        <th class="py-3 px-6 text-center">Update Status</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="10" class="py-3 px-6 text-center text-gray-500">No material requests found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $request): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($request['id']); ?></td>
                                <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($request['username'] ?? 'Guest'); ?>
                                </td>
                                <td class="py-3 px-6 text-left">
                                    <?php echo htmlspecialchars(ucfirst($request['material_type'])); ?>
                                </td>
                                <td class="py-3 px-6 text-left font-medium"><?php echo htmlspecialchars($request['title']); ?>
                                </td>
                                <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($request['author_publisher']); ?>
                                </td>
                                <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($request['details']); ?></td>
                                <td class="py-3 px-6 text-center">
                                    <span
                                        class="py-1 px-3 rounded-full text-xs <?php
                                        if ($request['status'] == 'approved')
                                            echo 'bg-green-200 text-green-600';
                                        else if ($request['status'] == 'rejected')
                                            echo 'bg-red-200 text-red-600';
                                        else
                                            echo 'bg-yellow-200 text-yellow-600';
                                        ?>">
                                        <?php echo htmlspecialchars(ucfirst($request['status'])); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($request['requested_at']))); ?>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <div class="flex item-center justify-center space-x-2">
                                        <?php if ($request['status'] == 'pending'): ?>
                                            <form method="POST" action="requests.php" class="inline-block">
                                                <input type="hidden" name="request_id"
                                                    value="<?php echo $request['id']; ?>">
                                                <input type="hidden" name="new_status" value="approved">
                                                <button type="submit" title="Approve"
                                                    class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </button>
                                            </form>
                                            <form method="POST" action="requests.php" class="inline-block">
                                                <input type="hidden" name="request_id"
                                                    value="<?php echo $request['id']; ?>">
                                                <input type="hidden" name="new_status" value="rejected">
                                                <button type="submit" title="Reject"
                                                    class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <form action="requests.php" method="post" class="inline-block"
                                        onsubmit="return confirm('Are you sure you want to delete this request?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $request['id']; ?>">
                                        <button type="submit" title="Delete"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>