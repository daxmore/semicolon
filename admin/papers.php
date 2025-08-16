<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Handle form submissions for CRUD operations
$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $title = $_POST['title'];
    $subject = $_POST['subject'];
    $year = $_POST['year'];
    $file_path = $_POST['file_path'];

    $sql = "INSERT INTO papers (title, subject, year, file_path) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssis', $title, $subject, $year, $file_path);
    $stmt->execute();
} elseif ($action === 'delete') {
    $id = $_POST['id'];
    $sql = "DELETE FROM papers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
}

$papers = get_papers();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Papers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/index.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold">Manage Papers</h1>

        <div class="mt-8">
            <h2 class="text-2xl font-bold">Add New Paper</h2>
            <form action="papers.php" method="post" class="mt-4">
                <input type="hidden" name="action" value="add">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" id="title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    </div>
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                        <input type="text" name="subject" id="subject" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    </div>
                    <div>
                        <label for="year" class="block text-sm font-medium text-gray-700">Year</label>
                        <input type="number" name="year" id="year" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    </div>
                    <div>
                        <label for="file_path" class="block text-sm font-medium text-gray-700">File Path</label>
                        <input type="text" name="file_path" id="file_path" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="rounded-md bg-teal-600 px-5 py-2.5 text-sm font-medium text-white shadow">Add Paper</button>
                </div>
            </form>
        </div>

        <div class="mt-8">
            <h2 class="text-2xl font-bold">Existing Papers</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y-2 divide-gray-200 bg-white text-sm">
                    <thead class="ltr:text-left rtl:text-right">
                        <tr>
                            <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Title</th>
                            <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Subject</th>
                            <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Year</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($papers as $paper): ?>
                            <tr>
                                <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900"><?php echo htmlspecialchars($paper['title']); ?></td>
                                <td class="whitespace-nowrap px-4 py-2 text-gray-700"><?php echo htmlspecialchars($paper['subject']); ?></td>
                                <td class="whitespace-nowrap px-4 py-2 text-gray-700"><?php echo htmlspecialchars($paper['year']); ?></td>
                                <td class="whitespace-nowrap px-4 py-2">
                                    <form action="papers.php" method="post" class="inline-block js-confirm-delete">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $paper['id']; ?>">
                                        <button type="submit" class="inline-block rounded bg-red-600 px-4 py-2 text-xs font-medium text-white hover:bg-red-700">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>