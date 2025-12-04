<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: books.php');
    exit();
}

$sql = "SELECT * FROM books WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();

if (!$book) {
    header('Location: books.php');
    exit();
}

include 'header.php';
?>

<!-- Back Link -->
<div class="mb-6">
    <a href="books.php" class="inline-flex items-center gap-2 text-zinc-500 hover:text-zinc-700 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Books
    </a>
</div>

<!-- Edit Form -->
<div class="bg-white rounded-2xl border border-zinc-200 p-6">
    <h2 class="text-lg font-bold text-zinc-900 mb-6">Edit Book Details</h2>
    
    <form action="books.php" method="post" class="space-y-6">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="<?php echo $book['id']; ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-2">Title</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($book['title']); ?>" required
                    class="w-full px-4 py-3 border border-zinc-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-2">Author</label>
                <input type="text" name="author" value="<?php echo htmlspecialchars($book['author']); ?>" required
                    class="w-full px-4 py-3 border border-zinc-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-zinc-700 mb-2">Description</label>
            <textarea name="description" rows="4" required
                class="w-full px-4 py-3 border border-zinc-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"><?php echo htmlspecialchars($book['description']); ?></textarea>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-2">Subject</label>
                <input type="text" name="subject" value="<?php echo htmlspecialchars($book['subject']); ?>" required
                    class="w-full px-4 py-3 border border-zinc-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-2">Difficulty</label>
                <select name="difficulty" required
                    class="w-full px-4 py-3 border border-zinc-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    <option value="Easy" <?php echo $book['difficulty'] === 'Easy' ? 'selected' : ''; ?>>Easy</option>
                    <option value="Medium" <?php echo $book['difficulty'] === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="Hard" <?php echo $book['difficulty'] === 'Hard' ? 'selected' : ''; ?>>Hard</option>
                </select>
            </div>
        </div>
        
        <!-- Current File Info -->
        <div class="bg-zinc-50 rounded-xl p-4">
            <p class="text-sm font-medium text-zinc-700 mb-1">Current Resource</p>
            <p class="text-sm text-zinc-500 break-all"><?php echo htmlspecialchars($book['private_path']); ?></p>
        </div>
        
        <div class="flex items-center justify-end gap-4 pt-4 border-t border-zinc-100">
            <a href="books.php" class="px-6 py-2.5 text-zinc-600 hover:text-zinc-800 font-medium transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition">
                Update Book
            </button>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>