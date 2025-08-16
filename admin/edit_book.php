<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

$id = $_GET['id'];
$sql = "SELECT * FROM books WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Edit Book</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/index.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold">Edit Book</h1>

        <div class="mt-8">
            <form action="books.php" method="post" class="mt-4">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?php echo $book['id']; ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($book['title']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    </div>
                    <div>
                        <label for="author" class="block text-sm font-medium text-gray-700">Author</label>
                        <input type="text" name="author" id="author" value="<?php echo htmlspecialchars($book['author']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"><?php echo htmlspecialchars($book['description']); ?></textarea>
                    </div>
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                        <input type="text" name="subject" id="subject" value="<?php echo htmlspecialchars($book['subject']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="semester" class="block text-sm font-medium text-gray-700">Semester</label>
                        <input type="text" name="semester" id="semester" value="<?php echo htmlspecialchars($book['semester']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="difficulty" class="block text-sm font-medium text-gray-700">Difficulty</label>
                        <input type="text" name="difficulty" id="difficulty" value="<?php echo htmlspecialchars($book['difficulty']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="file_path" class="block text-sm font-medium text-gray-700">File Path</label>
                        <input type="text" name="file_path" id="file_path" value="<?php echo htmlspecialchars($book['file_path']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    </div>
                    <div>
                        <label for="cover_image" class="block text-sm font-medium text-gray-700">Cover Image</label>
                        <input type="text" name="cover_image" id="cover_image" value="<?php echo htmlspecialchars($book['cover_image']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="rounded-md bg-teal-600 px-5 py-2.5 text-sm font-medium text-white shadow">Update Book</button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
