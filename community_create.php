<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

require_once 'includes/db.php';
require_once 'includes/functions.php';

$categories = [
    'Frontend', 'Backend', 'Full Stack', 'App Dev', 'Game Dev', 
    'UI/UX Design', 'Graphic Design', 'Video Editing', 'Motion Graphics', 
    'Cloud Computing', 'General Tech'
];

$pre_selected_category = $_GET['category'] ?? '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category = $_POST['category'] ?? '';
    $description = trim($_POST['description'] ?? '');
    
    // We expect the JS to have squashed the image and placed it into a hidden field
    $compressed_b64 = trim($_POST['compressed_image_b64'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');

    $final_image_url = '';

    if (empty($title) || empty($category) || empty($description)) {
        $error = "Title, Category, and Description are required.";
    } elseif (!in_array($category, $categories)) {
        $error = "Invalid category selected.";
    } else {
        
        // Priority 1: The compressed B64 string from the frontend upload
        if (!empty($compressed_b64)) {
            // Verify it looks like a valid B64 data URI
            if (strpos($compressed_b64, 'data:image') === 0) {
                $final_image_url = $compressed_b64;
            } else {
                $error = "Invalid image upload format.";
            }
        } 
        // Priority 2: Standard URL fallback
        elseif (!empty($image_url)) {
            $url = filter_var($image_url, FILTER_SANITIZE_URL);
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $final_image_url = $url;
            } else {
                $error = "Invalid image URL provided.";
            }
        }

        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO community_posts (user_id, title, description, image_url, category) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('issss', $_SESSION['user_id'], $title, $description, $final_image_url, $category);
            
            if ($stmt->execute()) {
                $new_post_id = $conn->insert_id;
                
                // Award 10 XP for creating a post
                add_user_xp($_SESSION['user_id'], 10);
                
                echo "<script>window.location.href = 'community_post_detail.php?id=" . $new_post_id . "';</script>";
                exit();
            } else {
                $error = "Something went wrong saving to the database. " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post - Semicolon Community</title>
    <link href="assets/css/index.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
    <script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>
</head>
<body class="antialiased bg-[#FAFAFA]">
    <?php include 'includes/header.php'; ?>

    <!-- Header Area -->
    <div class="bg-white border-b border-zinc-100 py-6 mb-10">
        <div class="container mx-auto px-6 max-w-3xl">
            <a href="community.php" class="text-sm font-medium text-amber-600 hover:text-amber-700 mb-2 inline-block">&larr; Back to Community Feed</a>
            <h1 class="text-3xl font-bold text-zinc-900">Start a new discussion</h1>
        </div>
    </div>

    <!-- Form Section -->
    <section class="pb-24">
        <div class="container mx-auto px-6 max-w-3xl">
            
            <?php if ($error): ?>
                <div class="mb-6 bg-red-50 text-red-700 p-4 rounded-xl border border-red-100 flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" id="postForm" class="bg-white rounded-2xl border border-zinc-100 p-8 shadow-sm">
                
                <!-- Hidden input to hold the compressed Base64 string -->
                <input type="hidden" id="compressed_image_b64" name="compressed_image_b64" value="">
                    <div>
                        <label for="title" class="block text-sm font-bold text-zinc-900 mb-2">Title</label>
                        <input type="text" id="title" name="title" required placeholder="What's on your mind?" 
                               class="w-full px-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl text-zinc-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" 
                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                    </div>

                    <div>
                        <label for="category" class="block text-sm font-bold text-zinc-900 mt-4 mb-2">Topic Category</label>
                        <select id="category" name="category" required 
                                class="w-full px-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl text-zinc-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition">
                            <option value="">Select a specific topic...</option>
                            <?php foreach ($categories as $cat): ?>
                                <?php $selected = ($pre_selected_category === $cat || ($_POST['category'] ?? '') === $cat) ? 'selected' : ''; ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="bg-zinc-50 p-6 mb-2 mt-4 rounded-xl border border-zinc-100">
                        <label class="block text-sm font-bold text-zinc-900 mb-1">Attach Media <span class="text-zinc-400 font-normal">(Optional)</span></label>
                        <p class="text-xs text-zinc-500 mb-4">Upload an image from your device, or paste a public URL.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="image_upload" class="block text-xs font-semibold text-zinc-600 mb-2 uppercase tracking-wide">Upload File (Max 2MB)</label>
                                <input type="file" id="image_upload" accept="image/*"
                                       class="w-full px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm text-zinc-700 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                            </div>
                            
                             <div>
                                <label for="image_url" class="block text-xs font-semibold text-zinc-600 mb-2 uppercase tracking-wide">Or Image URL</label>
                                <input type="url" id="image_url" name="image_url" placeholder="https://example.com/image.jpg" 
                                       oninput="handleUrlPreview(this.value)"
                                       class="w-full px-4 py-3 bg-white border border-zinc-200 rounded-xl text-zinc-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" 
                                       value="<?php echo htmlspecialchars($_POST['image_url'] ?? ''); ?>">
                            </div>
                        </div>

                        <!-- Image Preview Area -->
                        <div id="preview-container" class="hidden mt-6 pt-6 border-t border-zinc-200">
                            <label class="block text-xs font-bold text-zinc-500 uppercase mb-2">Attachment Preview</label>
                            <div class="relative rounded-xl overflow-hidden bg-white border border-zinc-200 max-h-[300px] flex items-center justify-center group">
                                <img id="image-preview" src="#" alt="Preview" class="max-w-full max-h-[300px] object-contain">
                                <button type="button" onclick="clearPreview()" class="absolute top-2 right-2 p-1.5 bg-zinc-900/50 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity backdrop-blur-sm hover:bg-zinc-900">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-bold text-zinc-900 mb-2">Description</label>
                        <textarea id="description" name="description" rows="8" required placeholder="Add more details to your discussion..." 
                                  class="w-full px-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl text-zinc-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition resize-y"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="mt-8 pt-6 border-t border-zinc-100 flex justify-end gap-4 pb-4">
                        <a href="community.php" class="px-6 py-3 border border-zinc-200 text-zinc-600 font-medium rounded-xl hover:bg-zinc-50 transition-all hover:scale-[1.02] active:scale-[0.98]">Cancel</a>
                        <button type="button" id="submitBtn" class="px-8 py-3 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-bold rounded-xl transition-all shadow-md hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]">Post Discussion</button>
                    </div>
                </div>
            </form>

        </div>
    </section>

    <!-- Client Side Image Compression & Markdown Script -->
    <script>
    const easyMDE = new EasyMDE({ 
        element: document.getElementById('description'),
        spellChecker: false,
        placeholder: "Add more details to your discussion (Markdown supported)...",
        styleSelectedText: false,
        toolbar: ["bold", "italic", "heading", "|", "quote", "unordered-list", "ordered-list", "|", "link", "image", "|", "preview", "guide"]
    });

    document.getElementById('image_upload').addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                showPreview(e.target.result);
                document.getElementById('image_url').value = ''; // Clear URL if file uploaded
                document.getElementById('compressed_image_b64').value = '';
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    function handleUrlPreview(url) {
        if (url && (url.startsWith('http://') || url.startsWith('https://'))) {
            showPreview(url);
            document.getElementById('image_upload').value = ''; // Clear file if URL entered
            document.getElementById('compressed_image_b64').value = '';
        } else {
            document.getElementById('preview-container').classList.add('hidden');
        }
    }

    function showPreview(src) {
        const preview = document.getElementById('image-preview');
        const container = document.getElementById('preview-container');
        preview.src = src;
        container.classList.remove('hidden');
        
        preview.onerror = function() {
            container.classList.add('hidden');
            console.log('Preview failed to load for:', src);
        };
    }

    function clearPreview() {
        document.getElementById('image_upload').value = '';
        document.getElementById('image_url').value = '';
        document.getElementById('preview-container').classList.add('hidden');
        document.getElementById('compressed_image_b64').value = '';
    }

    document.getElementById('submitBtn').addEventListener('click', function(e) {
        const fileInput = document.getElementById('image_upload');
        const form = document.getElementById('postForm');
        const btn = document.getElementById('submitBtn');
        
        // Ensure Markdown content is synced
        document.getElementById('description').value = easyMDE.value();

        // If no file, just submit the form normally
        if (!fileInput.files || fileInput.files.length === 0) {
            form.submit();
            return;
        }

        const file = fileInput.files[0];
        // Validate roughly 2MB constraint before processing
        if (file.size > 2 * 1024 * 1024) {
            alert("Image must be under 2MB.");
            return;
        }

        btn.disabled = true;
        btn.innerText = "Processing...";

        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = function(event) {
            const img = new Image();
            img.src = event.target.result;
            img.onload = function() {
                // Determine new dimensions while preserving aspect ratio (max 1200px wide)
                const MAX_WIDTH = 1200;
                let width = img.width;
                let height = img.height;

                if (width > MAX_WIDTH) {
                    height = Math.round((height * MAX_WIDTH) / width);
                    width = MAX_WIDTH;
                }

                // Create canvas and compress
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                // Export as compressed WebP (or JPEG) to ensure it's easily digestible by MySQL
                const compressedDataUrl = canvas.toDataURL('image/webp', 0.8);
                
                // Stick it in the hidden input and submit
                document.getElementById('compressed_image_b64').value = compressedDataUrl;
                
                btn.innerText = "Uploading...";
                form.submit();
            };
        };
        reader.onerror = function() {
            alert("Failed to read file.");
            btn.disabled = false;
            btn.innerText = "Post Discussion";
        };
    });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
