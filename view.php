<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$token = $_GET['token'] ?? '';
$is_raw = isset($_GET['raw']) && $_GET['raw'] === 'true';

if (empty($token)) {
    die("Invalid request.");
}

// Find resource by token
$resource = null;
$type = '';

// Check books
$stmt = $conn->prepare("SELECT * FROM books WHERE token = ?");
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $resource = $row;
    $type = 'book';
}

// Check papers
if (!$resource) {
    $stmt = $conn->prepare("SELECT * FROM papers WHERE token = ?");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $resource = $row;
        $type = 'paper';
    }
}

// Check videos
if (!$resource) {
    $stmt = $conn->prepare("SELECT * FROM videos WHERE token = ?");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $resource = $row;
        $type = 'video';
    }
}

if (!$resource) {
    die("Resource not found or invalid token.");
}

// Handle RAW file serving (for iframe)
if ($is_raw && $type !== 'video') {
    $file_path = $resource['private_path'];
    
    if (filter_var($file_path, FILTER_VALIDATE_URL)) {
        header("Location: " . $file_path);
        exit();
    } else {
        $real_path = realpath($file_path);
        if ($real_path && file_exists($real_path)) {
            $mime_type = mime_content_type($real_path);
            header('Content-Type: ' . $mime_type);
            header('Content-Disposition: inline; filename="' . basename($real_path) . '"');
            header('Content-Length: ' . filesize($real_path));
            readfile($real_path);
            exit();
        } else {
            die("File not found on server.");
        }
    }
}

// Record History (only on main view)
if (!$is_raw) {
    if (record_view($_SESSION['user_id'], $type, $resource['id'])) {
        // Award XP for viewing a resource (5 XP)
        // We do this if record_view is successful (which now also means it was potentially a new daily view if we added that, or just an engagement action).
        add_user_xp($_SESSION['user_id'], 5);
    }
}

// Fetch Reaction Stats
$stats = get_reaction_stats($type, $resource['id']);
$user_id = $_SESSION['user_id'];

// Check user's reaction
$stmt = $conn->prepare("SELECT is_helpful FROM reactions WHERE user_id = ? AND resource_type = ? AND resource_id = ?");
$stmt->bind_param('isi', $user_id, $type, $resource['id']);
$stmt->execute();
$user_reaction = $stmt->get_result()->fetch_assoc();
$is_helpful = $user_reaction ? $user_reaction['is_helpful'] : null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viewing <?php echo htmlspecialchars($resource['title']); ?> - Semicolon</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <style>
        body, html { margin: 0; padding: 0; height: 100%; overflow: hidden; background: #0f172a; font-family: 'Inter', sans-serif; }
        .viewer-grid { display: grid; grid-template-rows: auto 1fr; height: 100%; }
        .pdf-viewer-container {
            height: 100%;
            overflow-y: auto;
            background: #1a1a1a;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }
        .pdf-page-wrapper {
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            margin-bottom: 20px;
            background: white;
            position: relative;
        }
        canvas {
            display: block;
            max-width: 100%;
        }
        #loading-overlay {
            position: absolute;
            inset: 0;
            top: 56px; /* Below toolbar */
            background: rgba(0,0,0,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            z-index: 50;
            backdrop-filter: blur(4px);
        }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #1a1a1a; }
        ::-webkit-scrollbar-thumb { background: #333; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #444; }
    </style>
</head>
<body>
    <div class="viewer-grid">
        <!-- Toolbar -->
        <div class="bg-gray-900 text-white px-4 py-3 flex items-center justify-between shadow-md z-10">
            <div class="flex items-center gap-4">
                <a href="javascript:history.back()" class="text-gray-400 hover:text-white transition flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Back
                </a>
                <h1 class="font-semibold text-lg truncate max-w-md"><?php echo htmlspecialchars($resource['title']); ?></h1>
                <span class="text-xs bg-gray-700 px-2 py-1 rounded text-gray-300 uppercase"><?php echo $type; ?></span>
            </div>

            <div class="flex items-center gap-4">
                <?php 
                $is_pro = is_pro_user($_SESSION['user_id']);
                ?>

                <?php if ($type !== 'video'): ?>
                <!-- PDF Controls -->
                <div class="flex items-center gap-3 bg-gray-800 rounded-lg p-1 mr-2 px-3">
                    <div class="flex items-center gap-2">
                        <button onclick="prevPage()" class="p-1 hover:bg-gray-700 rounded transition text-gray-400 hover:text-white disabled:opacity-30 disabled:hover:bg-transparent" id="prev-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                        </button>
                        <span class="text-xs font-mono text-gray-300 min-w-[60px] text-center">
                            <span id="page-num">1</span> / <span id="page-count">?</span>
                        </span>
                        <button onclick="nextPage()" class="p-1 hover:bg-gray-700 rounded transition text-gray-400 hover:text-white disabled:opacity-30 disabled:hover:bg-transparent" id="next-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                        </button>
                    </div>
                    <div class="w-px h-4 bg-gray-700"></div>
                    <div class="flex items-center gap-1">
                        <button onclick="zoomOut()" class="p-1 hover:bg-gray-700 rounded transition text-gray-400 hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" /></svg>
                        </button>
                        <span id="zoom-percent" class="text-[10px] font-bold text-gray-400 w-10 text-center">100%</span>
                        <button onclick="zoomIn()" class="p-1 hover:bg-gray-700 rounded transition text-gray-400 hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" /></svg>
                        </button>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($is_pro): ?>
                <a href="download.php?token=<?php echo $token; ?>" class="flex items-center gap-1.5 px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition text-xs font-bold shadow-lg shadow-indigo-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download PDF
                </a>
                <?php else: ?>
                <a href="pricing.php" class="flex items-center gap-1.5 px-4 py-1.5 bg-white/10 hover:bg-white/20 text-white rounded-lg transition text-xs font-bold border border-white/10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Unlock Download
                </a>
                <?php endif; ?>

                <div class="flex items-center gap-2 bg-gray-800 rounded-lg p-1">
                    <button onclick="toggleReaction(1)" id="btn-helpful" 
                        class="flex items-center gap-1 px-3 py-1.5 rounded transition <?php echo ($is_helpful === 1) ? 'bg-green-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-700'; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z" />
                        </svg>
                        <span class="text-xs font-bold" id="count-helpful"><?php echo $stats['helpful']; ?></span>
                    </button>
                    <button onclick="toggleReaction(0)" id="btn-not-helpful"
                        class="flex items-center gap-1 px-3 py-1.5 rounded transition <?php echo ($is_helpful === 0) ? 'bg-red-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-700'; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M18 9.5a1.5 1.5 0 11-3 0v-6a1.5 1.5 0 013 0v6zM14 9.667v-5.43a2 2 0 00-1.105-1.79l-.05-.025A4 4 0 0011.055 2H5.64a2 2 0 00-1.962 1.608l-1.2 6A2 2 0 004.44 12H8v4a2 2 0 002 2 1 1 0 001-1v-.667a4 4 0 01.8-2.4l1.4-1.866a4 4 0 00.8-2.4z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
 
        <!-- Content -->
        <div class="bg-black relative w-full h-full overflow-hidden">
            <?php if ($type === 'video'): 
                $video_url = $resource['youtube_url'];
                $is_iframe = (strpos(trim($video_url), '<iframe') === 0);
                
                if ($is_iframe):
                    echo $video_url;
                else:
                    $video_id = get_youtube_id($video_url);
            ?>
                <iframe src="https://www.youtube.com/embed/<?php echo $video_id; ?>?autoplay=1" allowfullscreen class="w-full h-full border-0"></iframe>
            <?php endif; ?>
            <?php else: ?>
                <div id="loading-overlay">
                    <div class="flex flex-col items-center gap-4">
                        <div class="w-12 h-12 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                        <p class="text-sm font-bold tracking-widest uppercase opacity-50">Initializing Secure Viewer</p>
                    </div>
                </div>
                <div id="pdf-container" class="pdf-viewer-container">
                    <!-- Page rendered here -->
                    <div class="pdf-page-wrapper">
                        <canvas id="pdf-canvas"></canvas>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const resourceType = '<?php echo $type; ?>';
        const resourceId = <?php echo $resource['id']; ?>;

        <?php if ($type !== 'video'): ?>
        // PDF.js Implementation
        const url = 'view.php?token=<?php echo $token; ?>&raw=true';
        const pdfjsLib = window['pdfjs-dist/build/pdf'];
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        let pdfDoc = null,
            pageNum = 1,
            pageRendering = false,
            pageNumPending = null,
            scale = 1.25,
            canvas = document.getElementById('pdf-canvas'),
            ctx = canvas.getContext('2d');

        /**
         * Get page info from document, resize canvas accordingly, and render page.
         * @param num Page number.
         */
        function renderPage(num) {
            pageRendering = true;
            document.getElementById('loading-overlay').style.display = 'flex';
            
            // Using promise to fetch the page
            pdfDoc.getPage(num).then(function(page) {
                var viewport = page.getViewport({scale: scale});
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                // Render PDF page into canvas context
                var renderContext = {
                    canvasContext: ctx,
                    viewport: viewport
                };
                var renderTask = page.render(renderContext);

                // Wait for rendering to finish
                renderTask.promise.then(function() {
                    pageRendering = false;
                    document.getElementById('loading-overlay').style.display = 'none';
                    if (pageNumPending !== null) {
                        // New page rendering is pending
                        renderPage(pageNumPending);
                        pageNumPending = null;
                    }
                });
            });

            // Update page counters
            document.getElementById('page-num').textContent = num;
            updateButtons();
        }

        function updateButtons() {
            document.getElementById('prev-btn').disabled = (pageNum <= 1);
            document.getElementById('next-btn').disabled = (pageNum >= pdfDoc.numPages);
        }

        /**
         * If another page rendering in progress, waits until the rendering is
         * finised. Otherwise, executes rendering immediately.
         */
        function queueRenderPage(num) {
            if (pageRendering) {
                pageNumPending = num;
            } else {
                renderPage(num);
            }
        }

        /**
         * Displays previous page.
         */
        function prevPage() {
            if (pageNum <= 1) return;
            pageNum--;
            queueRenderPage(pageNum);
        }

        /**
         * Displays next page.
         */
        function nextPage() {
            if (pageNum >= pdfDoc.numPages) return;
            pageNum++;
            queueRenderPage(pageNum);
        }

        function zoomIn() {
            if (scale >= 3) return;
            scale += 0.25;
            document.getElementById('zoom-percent').textContent = Math.round(scale * 100) + '%';
            queueRenderPage(pageNum);
        }

        function zoomOut() {
            if (scale <= 0.5) return;
            scale -= 0.25;
            document.getElementById('zoom-percent').textContent = Math.round(scale * 100) + '%';
            queueRenderPage(pageNum);
        }

        /**
         * Asynchronously downloads PDF.
         */
        pdfjsLib.getDocument(url).promise.then(function(pdfDoc_) {
            pdfDoc = pdfDoc_;
            document.getElementById('page-count').textContent = pdfDoc.numPages;

            // Initial/first page rendering
            renderPage(pageNum);
        }).catch(err => {
            console.error('Error loading PDF:', err);
            document.getElementById('loading-overlay').innerHTML = `
                <div class="text-center p-8">
                    <svg class="w-12 h-12 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <p class="text-white font-bold mb-2">Failed to Load Content</p>
                    <p class="text-gray-400 text-xs">Please refresh the page or contact support.</p>
                </div>
            `;
        });
        <?php endif; ?>

        function toggleReaction(isHelpful) {
            fetch('api/reaction.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    resource_type: resourceType,
                    resource_id: resourceId,
                    is_helpful: isHelpful
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update UI
                    document.getElementById('count-helpful').innerText = data.stats.helpful;
                    
                    const btnHelpful = document.getElementById('btn-helpful');
                    const btnNotHelpful = document.getElementById('btn-not-helpful');

                    // Reset both to inactive state first
                    btnHelpful.className = 'flex items-center gap-1 px-3 py-1.5 rounded transition text-gray-400 hover:text-white hover:bg-gray-700';
                    btnNotHelpful.className = 'flex items-center gap-1 px-3 py-1.5 rounded transition text-gray-400 hover:text-white hover:bg-gray-700';

                    // Apply active state if there is a reaction
                    if (data.user_reaction === 1) {
                        btnHelpful.className = 'flex items-center gap-1 px-3 py-1.5 rounded transition bg-green-600 text-white';
                    } else if (data.user_reaction === 0 || data.user_reaction === '0') {
                        btnNotHelpful.className = 'flex items-center gap-1 px-3 py-1.5 rounded transition bg-red-600 text-white';
                    }
                }
            })
            .catch(err => console.error('Error:', err));
        }
    </script>
</body>
</html>
