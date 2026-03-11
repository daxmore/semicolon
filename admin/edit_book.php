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
<div class="mb-8 px-4 sm:px-0">
    <a href="books.php" class="inline-flex items-center gap-2 text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 font-medium transition group">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Books
    </a>
</div>

<!-- Edit Form -->
<div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 p-8 shadow-xl max-w-4xl mx-auto lg:mx-0">
    <h2 class="text-xl font-black text-zinc-900 dark:text-white mb-8 tracking-tight">Edit Book Details</h2>
    
    <form action="books.php" method="post" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="<?php echo $book['id']; ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest mb-2 px-1">Title</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($book['title']); ?>" required
                    class="w-full px-5 py-3.5 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition shadow-sm">
            </div>
            <div>
                <label class="block text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest mb-2 px-1">Author</label>
                <input type="text" name="author" value="<?php echo htmlspecialchars($book['author']); ?>" required
                    class="w-full px-5 py-3.5 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition shadow-sm">
            </div>
        </div>
        
        <div>
            <label class="block text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest mb-2 px-1">Description</label>
            <textarea name="description" rows="4" required
                class="w-full px-5 py-3.5 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition shadow-sm"><?php echo htmlspecialchars($book['description']); ?></textarea>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest mb-2 px-1">Subject</label>
                <input type="text" name="subject" value="<?php echo htmlspecialchars($book['subject']); ?>" required
                    class="w-full px-5 py-3.5 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition shadow-sm">
            </div>
            <div>
                <label class="block text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest mb-2 px-1">Difficulty</label>
                <select name="difficulty" required
                    class="w-full px-5 py-3.5 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition shadow-sm appearance-none">
                    <option value="Easy" <?php echo $book['difficulty'] === 'Easy' ? 'selected' : ''; ?>>Easy</option>
                    <option value="Medium" <?php echo $book['difficulty'] === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="Hard" <?php echo $book['difficulty'] === 'Hard' ? 'selected' : ''; ?>>Hard</option>
                </select>
            </div>
        </div>
        
        <!-- Current File Info -->
        <div class="p-6 bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl border border-zinc-100 dark:border-zinc-800 shadow-inner">
            <p class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-tight mb-2">Current Active Resource</p>
            <div class="flex items-center gap-3">
                <div class="p-2 bg-white dark:bg-zinc-900 rounded-lg shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
                <p class="text-sm text-zinc-600 dark:text-zinc-300 font-mono text-xs break-all"><?php echo htmlspecialchars($book['private_path']); ?></p>
            </div>
        </div>

        <!-- Update Resource -->
        <div class="border-t border-zinc-100 dark:border-zinc-800 pt-8 mt-4">
            <div class="flex justify-between items-center mb-6 px-1">
                <p class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-wider">Update Resource</p>
                <button type="button" id="clearSelection" class="text-[10px] font-black uppercase text-rose-500 hover:text-rose-600 transition tracking-tighter hidden">
                    Clear Selection
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <label class="block text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest mb-3 px-1">New PDF Upload</label>
                    <label class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-zinc-300 dark:border-zinc-700 rounded-3xl cursor-pointer bg-zinc-50 dark:bg-zinc-800/30 hover:bg-zinc-100 dark:hover:bg-zinc-800/50 transition shadow-inner group">
                        <div class="flex flex-col items-center justify-center py-5">
                            <div class="p-3 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm mb-3 group-hover:scale-110 transition-transform">
                                <svg class="w-8 h-8 text-zinc-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                            </div>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 px-4 text-center"><span id="file-name-display" class="font-bold text-indigo-600 dark:text-indigo-400">Choose new PDF</span></p>
                        </div>
                        <input type="file" id="fileUploadInput" name="file_upload" accept=".pdf" class="hidden">
                    </label>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest mb-3 px-1">Or External URL</label>
                    <div class="space-y-4">
                        <input type="url" id="fileUrlInput" name="file_url" placeholder="https://example.com/document.pdf" 
                            class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl text-zinc-900 dark:text-white font-mono text-xs focus:ring-2 focus:ring-indigo-500 outline-none transition shadow-sm">
                        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/10 rounded-2xl border border-emerald-100 dark:border-emerald-900/20">
                            <p class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium leading-relaxed">
                                URL take precedence. If you provide a link, any uploaded file will be ignored.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="flex items-center justify-end gap-10 pt-10 mt-6 border-t border-zinc-100 dark:border-zinc-800">
            <a href="books.php" class="text-xs font-black uppercase text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition group flex items-center gap-2">
                Discard Changes
            </a>
            <button type="submit" class="px-10 py-4 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 transition shadow-xl shadow-indigo-600/20 active:scale-[0.98]">
                Update Book Resource
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
    const clearBtn = document.getElementById('clearSelection');

    function updateClearButton() {
        if ((fileInput && fileInput.files.length > 0) || (urlInput && urlInput.value.trim() !== '')) {
            clearBtn.classList.remove('hidden');
        } else {
            clearBtn.classList.add('hidden');
        }
    }

    if (fileInput && urlInput && fileNameDisplay) {
        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const fileName = e.target.files[0].name;
                fileNameDisplay.innerHTML = `<span class="text-emerald-600 dark:text-emerald-400 flex items-center justify-center gap-2 font-bold break-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                    ${fileName}
                </span>`;
                urlInput.value = '';
                urlInput.disabled = true;
                urlInput.classList.add('bg-zinc-100', 'dark:bg-zinc-800', 'cursor-not-allowed', 'opacity-50');
            } else {
                fileNameDisplay.textContent = 'Choose new PDF';
                urlInput.disabled = false;
                urlInput.classList.remove('bg-zinc-100', 'dark:bg-zinc-800', 'cursor-not-allowed', 'opacity-50');
            }
            updateClearButton();
        });

        urlInput.addEventListener('input', function(e) {
            if (e.target.value.trim() !== '') {
                fileInput.value = '';
                uploadArea.classList.add('opacity-40', 'cursor-not-allowed', 'pointer-events-none');
                fileNameDisplay.textContent = 'Upload disabled';
            } else {
                uploadArea.classList.remove('opacity-40', 'cursor-not-allowed', 'pointer-events-none');
                fileNameDisplay.textContent = 'Choose new PDF';
            }
            updateClearButton();
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                fileInput.value = '';
                urlInput.value = '';
                urlInput.disabled = false;
                urlInput.classList.remove('bg-zinc-100', 'dark:bg-zinc-800', 'cursor-not-allowed', 'opacity-50');
                uploadArea.classList.remove('opacity-40', 'cursor-not-allowed', 'pointer-events-none');
                fileNameDisplay.textContent = 'Choose new PDF';
                this.classList.add('hidden');
            });
        }
    }
});
</script>

<?php include 'footer.php'; ?>