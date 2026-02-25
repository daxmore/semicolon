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
    
    <form action="books.php" method="post" enctype="multipart/form-data" class="space-y-6">
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
        <div class="bg-zinc-50 rounded-xl p-4 border border-zinc-100">
            <p class="text-sm font-medium text-zinc-700 mb-1">Current Resource</p>
            <p class="text-sm text-zinc-500 break-all"><?php echo htmlspecialchars($book['private_path']); ?></p>
        </div>

        <!-- Update Resource -->
        <div class="border-t border-zinc-100 pt-6">
            <p class="text-sm font-medium text-zinc-700 mb-3">Update Resource (Optional)</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm text-zinc-600 mb-2">New PDF Upload</label>
                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-zinc-300 rounded-xl cursor-pointer bg-zinc-50 hover:bg-zinc-100 transition">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-8 h-8 mb-2 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-sm text-zinc-500"><span id="file-name-display" class="font-medium text-indigo-600">Click to upload new</span></p>
                            <p class="text-xs text-zinc-400">PDF only</p>
                        </div>
                        <input type="file" id="fileUploadInput" name="file_upload" accept=".pdf" class="hidden">
                    </label>
                </div>
                <div>
                    <label class="block text-sm text-zinc-600 mb-2">Or New External URL</label>
                    <input type="url" id="fileUrlInput" name="file_url" placeholder="https://example.com/document.pdf" class="w-full px-4 py-3 border border-zinc-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    <p class="text-xs text-zinc-400 mt-2">Will replace the current resource link</p>
                </div>
            </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('fileUploadInput');
    const urlInput = document.getElementById('fileUrlInput');
    const fileNameDisplay = document.getElementById('file-name-display');
    const uploadArea = fileInput ? fileInput.closest('label') : null;

    if (fileInput && urlInput && fileNameDisplay) {
        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const fileName = e.target.files[0].name;
                fileNameDisplay.innerHTML = `<span class="text-green-600 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                    ${fileName}
                </span>`;
                urlInput.value = '';
                urlInput.disabled = true;
                urlInput.classList.add('bg-zinc-100', 'cursor-not-allowed', 'opacity-50');
            } else {
                fileNameDisplay.textContent = 'Click to upload new';
                urlInput.disabled = false;
                urlInput.classList.remove('bg-zinc-100', 'cursor-not-allowed', 'opacity-50');
            }
        });

        urlInput.addEventListener('input', function(e) {
            if (e.target.value.trim() !== '') {
                fileInput.value = '';
                uploadArea.classList.add('opacity-40', 'cursor-not-allowed', 'pointer-events-none');
                fileNameDisplay.textContent = 'Upload disabled';
            } else {
                uploadArea.classList.remove('opacity-40', 'cursor-not-allowed', 'pointer-events-none');
                fileNameDisplay.textContent = 'Click to upload new';
            }
        });
    }
});
</script>

<?php include 'footer.php'; ?>