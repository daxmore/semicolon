<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}
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
} elseif ($action === 'update') {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $subject = $_POST['subject'];
    $year = $_POST['year'];
    $file_path = $_POST['file_path'];

    $sql = "UPDATE papers SET title = ?, subject = ?, year = ?, file_path = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssisi', $title, $subject, $year, $file_path, $id);
    $stmt->execute();
    header('Location: papers.php');
    exit();
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
    <link href="../assets/css/index.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/index.css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
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
                        <input type="text" name="title" id="title"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            required>
                    </div>
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                        <input type="text" name="subject" id="subject"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            required>
                    </div>
                    <div>
                        <label for="year" class="block text-sm font-medium text-gray-700">Year</label>
                        <input type="number" name="year" id="year"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            required>
                    </div>
                    <div>
                        <label for="file_path" class="block text-sm font-medium text-gray-700">File Path</label>
                        <input type="text" name="file_path" id="file_path"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            required>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit"
                        class="rounded-md bg-teal-600 px-5 py-2.5 text-sm font-medium text-white shadow">Add
                        Paper</button>
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
                            <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900 text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($papers as $paper): ?>
                            <tr>
                                <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">
                                    <?php echo htmlspecialchars($paper['title']); ?>
                                </td>
                                <td class="whitespace-nowrap px-4 py-2 text-gray-700">
                                    <?php echo htmlspecialchars($paper['subject']); ?>
                                </td>
                                <td class="whitespace-nowrap px-4 py-2 text-gray-700">
                                    <?php echo htmlspecialchars($paper['year']); ?>
                                </td>
                                <td class="gap-1 flex justify-center">
                                    <a href="edit_paper.php?id=<?php echo $paper['id']; ?>" title="Edit" class="inline-block rounded bg-indigo-600 px-4 py-2 text-xs font-medium text-white hover:bg-indigo-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form action="papers.php" method="post" class="inline-block js-confirm-delete">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $paper['id']; ?>">
                                        <button type="submit"
                                            class="inline-block rounded bg-red-600 px-4 py-2 text-xs font-medium text-white hover:bg-red-700">
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
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>