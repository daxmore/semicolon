<?php
include 'header.php';
require_once '../includes/db.php';
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
<body>

// Handle Create, Update, Delete operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_video'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $youtube_url = $_POST['youtube_url'];

        $stmt = $conn->prepare("INSERT INTO videos (title, description, youtube_url) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $description, $youtube_url);
        $stmt->execute();
    } elseif (isset($_POST['update_video'])) {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $youtube_url = $_POST['youtube_url'];

        $stmt = $conn->prepare("UPDATE videos SET title = ?, description = ?, youtube_url = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $description, $youtube_url, $id);
        $stmt->execute();
    } elseif (isset($_POST['delete_video'])) {
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
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Manage Videos</h1>

    <!-- Add Video Form -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <h2 class="text-2xl font-bold mb-4">Add New Video</h2>
        <form method="POST" action="videos.php">
            <div class="mb-4">
                <label for="title" class="block text-gray-700 font-bold mb-2">Title</label>
                <input type="text" id="title" name="title" class="w-full px-3 py-2 border rounded-lg" required>
            </div>
            <div class="mb-4">
                <label for="description" class="block text-gray-700 font-bold mb-2">Description</label>
                <textarea id="description" name="description" rows="3" class="w-full px-3 py-2 border rounded-lg"></textarea>
            </div>
            <div class="mb-4">
                <label for="youtube_url" class="block text-gray-700 font-bold mb-2">YouTube URL</label>
                <input type="url" id="youtube_url" name="youtube_url" class="w-full px-3 py-2 border rounded-lg" required>
            </div>
            <button type="submit" name="add_video" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                Add Video
            </button>
        </form>
    </div>

    <!-- Video List -->
    <div>
        <h2 class="text-2xl font-bold mb-4">Existing Videos</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($videos as $video) : ?>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($video['title']); ?></h3>
                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($video['description']); ?></p>
                    <div class="flex justify-end space-x-2">
                        <a href="#" class="text-blue-500 hover:underline">Edit</a>
                        <form method="POST" action="videos.php" class="js-confirm-delete">
                            <input type="hidden" name="id" value="<?php echo $video['id']; ?>">
                            <button type="submit" name="delete_video" class="text-red-500 hover:underline">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
