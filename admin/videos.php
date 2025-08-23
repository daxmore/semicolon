<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// session_start();
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header('Location: login.php');
//     exit();
// }

// Handle Create and Delete operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $youtube_url = $_POST['youtube_url'];

        $stmt = $conn->prepare("INSERT INTO videos (title, description, youtube_url) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $description, $youtube_url);
        $stmt->execute();
    } elseif ($action === 'delete') {
        $id = $_POST['id'];

        $stmt = $conn->prepare("DELETE FROM videos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    header("Location: videos.php");
    exit();
}

// Fetch all videos
$result = $conn->query("SELECT * FROM videos ORDER BY created_at DESC");
$videos = $result->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Videos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/index.css">
</head>

<body class="bg-gray-100">

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8 text-gray-800">Manage Videos</h1>

        <!-- Add Video Form -->
        <div class="bg-white p-8 rounded-lg shadow-md mb-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-700">Add New Video</h2>
            <form method="POST" action="videos.php">
                <input type="hidden" name="action" value="add">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-1">
                        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" id="title" name="title"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            required>
                    </div>
                    <div class="col-span-1">
                        <label for="youtube_url" class="block text-sm font-medium text-gray-700">YouTube URL</label>
                        <input type="url" id="youtube_url" name="youtube_url"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            required>
                    </div>
                    <div class="col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="description" name="description" rows="4"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                    </div>
                </div>
                <div class="text-right mt-6">
                    <button type="submit"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Add Video
                    </button>
                </div>
            </form>
        </div>

        <!-- Video Table -->
        <div class="bg-white p-8 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-6 text-gray-700">Existing Videos</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y-2 divide-gray-200 bg-white text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="whitespace-nowrap px-4 py-3 font-medium text-gray-900 text-left">Title</th>
                            <th class="px-4 py-3 font-medium text-gray-900 text-left">Description</th>
                            <th class="whitespace-nowrap px-4 py-3 font-medium text-gray-900 text-left">Link</th>
                            <th class="whitespace-nowrap px-4 py-3 font-medium text-gray-900 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($videos)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-gray-500">No videos found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($videos as $video): ?>
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">
                                        <?php echo htmlspecialchars($video['title']); ?></td>
                                    <td class="px-4 py-2 text-gray-700 max-w-xs truncate">
                                        <?php echo htmlspecialchars($video['description']); ?></td>
                                    <td class="whitespace-nowrap px-4 py-2 text-gray-700">
                                        <a href="<?php echo htmlspecialchars($video['youtube_url']); ?>" target="_blank"
                                            class="text-indigo-600 hover:text-indigo-900 hover:underline">
                                            Watch Video
                                        </a>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-2 text-center flex items-center justify-center gap-1">
                                        <a href="edit_video.php?id=<?php echo $video['id']; ?>" title="Edit" class="inline-block p-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <form action="videos.php" method="post" class="inline-block m-0"
                                            onsubmit="return confirm('Are you sure you want to delete this video?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $video['id']; ?>">
                                            <button type="submit"
                                                class="inline-block rounded bg-red-600 p-2 text-white hover:bg-red-700">
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
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>