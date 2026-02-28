<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Handle form submissions
$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $title = $_POST['title'];
    $subject = $_POST['subject'];
    $year = $_POST['year'];
    
    $file_url = $_POST['file_url'] ?? '';
    $file_upload = $_FILES['file_upload'] ?? null;
    $private_path = '';

    if (!empty($file_url) && !empty($file_upload['name'])) {
        die("Error: Please provide EITHER a file OR a URL, not both.");
    } elseif (empty($file_url) && empty($file_upload['name'])) {
        die("Error: Please provide a file or a URL.");
    }

    if (!empty($file_upload['name'])) {
        $target_dir = "../private/papers/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($file_upload['name'], PATHINFO_EXTENSION);
        if (strtolower($file_extension) !== 'pdf') {
             die("Error: Only PDF files are allowed.");
        }
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($file_upload['tmp_name'], $target_file)) {
            $private_path = "private/papers/" . $new_filename;
        } else {
            die("Error uploading file.");
        }
    } else {
        $private_path = $file_url;
    }

    $slug = generate_slug($title);
    $slug .= '-' . substr(md5(uniqid()), 0, 5);
    $token = generate_token();

    $sql = "INSERT INTO papers (title, subject, year, private_path, slug, token) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssisss', $title, $subject, $year, $private_path, $slug, $token);
    $stmt->execute();
    if (!isset($_SESSION['toasts'])) $_SESSION['toasts'] = [];
    $_SESSION['toasts'][] = ['type' => 'success', 'title' => 'Paper Added', 'message' => "Successfully added: {$title}"];
    header("Location: papers.php");
    exit();
} elseif ($action === 'delete') {
    $id = $_POST['id'];
    $sql = "DELETE FROM papers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    if (!isset($_SESSION['toasts'])) $_SESSION['toasts'] = [];
    $_SESSION['toasts'][] = ['type' => 'info', 'title' => 'Paper Deleted', 'message' => "The paper was removed."];
    header("Location: papers.php");
    exit();
} elseif ($action === 'update') {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $subject = $_POST['subject'];
    $year = $_POST['year'];

    $file_url = $_POST['file_url'] ?? '';
    $file_upload = $_FILES['file_upload'] ?? null;
    $private_path = null;

    if (!empty($file_url) && !empty($file_upload['name'])) {
        die("Error: Please provide EITHER a file OR a URL, not both.");
    }

    if (!empty($file_upload['name'])) {
        $target_dir = "../private/papers/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($file_upload['name'], PATHINFO_EXTENSION);
        if (strtolower($file_extension) !== 'pdf') {
             die("Error: Only PDF files are allowed.");
        }
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($file_upload['tmp_name'], $target_file)) {
            $private_path = "private/papers/" . $new_filename;
        } else {
            die("Error uploading file.");
        }
    } elseif (!empty($file_url)) {
        $private_path = $file_url;
    }

    if ($private_path) {
        $sql = "UPDATE papers SET title = ?, subject = ?, year = ?, private_path = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssisi', $title, $subject, $year, $private_path, $id);
    } else {
        $sql = "UPDATE papers SET title = ?, subject = ?, year = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssii', $title, $subject, $year, $id);
    }

    $stmt->execute();
    if (!isset($_SESSION['toasts'])) $_SESSION['toasts'] = [];
    $_SESSION['toasts'][] = ['type' => 'success', 'title' => 'Paper Updated', 'message' => "Successfully updated: {$title}"];
    header("Location: papers.php");
    exit();
}

$papers = get_papers();
$showForm = isset($_GET['add']);

include 'header.php';
?>

<!-- Header with Add Button -->
<div class="flex justify-between items-center mb-6">
    <p class="text-zinc-500">Manage your papers collection</p>
    <?php if (!$showForm): ?>
        <a href="papers.php?add=1" class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 text-white rounded-xl font-medium hover:bg-teal-700 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add New Paper
        </a>
    <?php else: ?>
        <a href="papers.php" class="inline-flex items-center gap-2 px-4 py-2 border border-zinc-200 text-zinc-700 rounded-xl font-medium hover:bg-zinc-50 transition">
            Cancel
        </a>
    <?php endif; ?>
</div>

<?php if ($showForm): ?>
<!-- Add Form -->
<div class="bg-white p-6 rounded-2xl border border-zinc-200 mb-8">
    <h2 class="text-lg font-bold text-zinc-900 mb-6">Add New Paper</h2>
    <form action="papers.php" method="post" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input type="hidden" name="action" value="add">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-zinc-700 mb-1">Title</label>
            <input type="text" name="title" required class="w-full px-4 py-2 border border-zinc-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-zinc-700 mb-1">Subject</label>
            <input type="text" name="subject" required class="w-full px-4 py-2 border border-zinc-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-zinc-700 mb-1">Year</label>
            <input type="number" name="year" required min="2000" max="2030" value="<?php echo date('Y'); ?>" class="w-full px-4 py-2 border border-zinc-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-transparent">
        </div>
        
        <div class="md:col-span-2 border-t border-zinc-100 pt-4 mt-2">
            <p class="text-sm font-medium text-zinc-700 mb-3">Resource Source (Choose One)</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-zinc-600 mb-2">Upload PDF</label>
                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-zinc-300 rounded-xl cursor-pointer bg-zinc-50 hover:bg-zinc-100 transition">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-8 h-8 mb-2 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-sm text-zinc-500"><span id="file-name-display" class="font-medium text-teal-600">Click to upload</span></p>
                            <p class="text-xs text-zinc-400">PDF only</p>
                        </div>
                        <input type="file" id="fileUploadInput" name="file_upload" accept=".pdf" class="hidden">
                    </label>
                </div>
                <div>
                    <label class="block text-sm text-zinc-600 mb-2">Or External URL</label>
                    <input type="url" id="fileUrlInput" name="file_url" placeholder="https://example.com/paper.pdf" class="w-full px-4 py-3 border border-zinc-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-transparent transition">
                    <p class="text-xs text-zinc-400 mt-2">Link to an external PDF file</p>
                </div>
            </div>
        </div>

        <div class="md:col-span-2 flex justify-end mt-4">
            <button type="submit" class="px-6 py-2 bg-teal-600 text-white rounded-xl font-medium hover:bg-teal-700 transition">
                Add Paper
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- Papers Table -->
<div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden">
    <div class="p-4 border-b border-zinc-100">
        <input type="text" id="searchInput" onkeyup="searchPapers()" placeholder="Search papers..." 
            class="w-full px-4 py-2 bg-zinc-50 border-0 rounded-xl focus:ring-2 focus:ring-teal-500">
    </div>
    <div class="overflow-x-auto">
        <table id="papersTable" class="w-full">
            <thead>
                <tr class="bg-zinc-50 border-b border-zinc-200 text-xs uppercase text-zinc-500 font-semibold">
                    <th class="px-6 py-4 text-left">Title</th>
                    <th class="px-6 py-4 text-left">Subject</th>
                    <th class="px-6 py-4 text-left">Year</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                <?php if (empty($papers)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-zinc-500">No papers found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($papers as $paper): ?>
                        <tr class="hover:bg-zinc-50 transition">
                            <td class="px-6 py-4 font-medium text-zinc-900"><?php echo htmlspecialchars($paper['title']); ?></td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-2.5 py-1 bg-teal-100 text-teal-700 rounded-full text-xs font-medium">
                                    <?php echo htmlspecialchars($paper['subject']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-zinc-600"><?php echo htmlspecialchars($paper['year']); ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="edit_paper.php?id=<?php echo $paper['id']; ?>" class="p-2 bg-teal-100 text-teal-600 rounded-lg hover:bg-teal-200 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form action="papers.php" method="post" class="inline" onsubmit="return confirm('Delete this paper?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $paper['id']; ?>">
                                        <button type="submit" class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function searchPapers() {
    var input = document.getElementById("searchInput");
    var filter = input.value.toUpperCase();
    var table = document.getElementById("papersTable");
    var tr = table.getElementsByTagName("tr");

    for (var i = 1; i < tr.length; i++) {
        var td = tr[i].getElementsByTagName("td")[0];
        if (td) {
            var txtValue = td.textContent || td.innerText;
            tr[i].style.display = txtValue.toUpperCase().indexOf(filter) > -1 ? "" : "none";
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('fileUploadInput');
    const urlInput = document.getElementById('fileUrlInput');
    const fileNameDisplay = document.getElementById('file-name-display');
    const uploadArea = fileInput ? fileInput.closest('label') : null;

    if (fileInput && urlInput && fileNameDisplay) {
        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const fileName = e.target.files[0].name;
                fileNameDisplay.innerHTML = `<span class="text-teal-600 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                    ${fileName}
                </span>`;
                urlInput.value = '';
                urlInput.disabled = true;
                urlInput.classList.add('bg-zinc-100', 'cursor-not-allowed', 'opacity-50');
            } else {
                fileNameDisplay.textContent = 'Click to upload';
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
                fileNameDisplay.textContent = 'Click to upload';
            }
        });
    }
});
</script>

<?php include 'footer.php'; ?>