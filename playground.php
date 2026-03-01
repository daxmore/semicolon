<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// If user is accessing a specific snippet, load it
$snippet_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$snippet = null;
$is_owner = false;

if ($snippet_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM code_snippets WHERE id = ?");
    $stmt->bind_param("i", $snippet_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $snippet = $result->fetch_assoc();
        // Check visibility
        if (!$snippet['is_public'] && (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $snippet['user_id'])) {
            // Not public and not the owner
            $snippet = null; // Deny access
        } else {
            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $snippet['user_id']) {
                $is_owner = true;
            }
        }
    }
}

// Initial Code values
$title = $snippet ? $snippet['title'] : 'Untitled Snippet';
$html_code = $snippet ? $snippet['html_code'] : "<h1>Hello Semicolon!</h1>\n<p>Start coding below!</p>";
$css_code = $snippet ? $snippet['css_code'] : "body {\n  font-family: sans-serif;\n  background-color: #f3f4f6;\n  color: #1f2937;\n  padding: 2rem;\n  text-align: center;\n}";
$js_code = $snippet ? $snippet['js_code'] : "console.log('Code Playground initialized');";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - Code Playground | Semicolon</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['Fira Code', 'Monaco', 'monospace']
                    }
                }
            }
        }
    </script>
    <link href="assets/css/index.css" rel="stylesheet">
    <!-- CodeMirror CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/theme/material-darker.min.css">
    <!-- CodeMirror JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/javascript/javascript.min.js"></script>
    <script src="assets/js/theme.js"></script>
    
    <style>
        .CodeMirror {
            height: 100%;
            font-family: 'Fira Code', 'Monaco', monospace;
            font-size: 14px;
            border-radius: 0.5rem;
            z-index: 0;
        }
        .cm-s-material-darker.CodeMirror {
            background-color: #18181b; /* zinc-900 */
        }
        /* Hidden layout classes for tabs */
        .editor-tab-content.hidden {
            display: none;
        }
    </style>
</head>
<body class="bg-[#FAFAFA] dark:bg-zinc-950 font-sans antialiased text-zinc-900 dark:text-zinc-200 h-screen flex flex-col overflow-hidden">

    <!-- Header (shrinked version for playground) -->
    <header class="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border-b border-zinc-200/80 dark:border-zinc-800/80 sticky top-0 z-50 flex-none h-16 flex items-center justify-between px-6 transition-colors duration-300">
        <div class="flex items-center gap-6">
            <a href="index.php" class="flex items-center gap-3 group relative">
                <div class="h-8 flex items-center justify-center transform group-hover:-rotate-6 transition-transform">
                    <?php echo file_get_contents('assets/images/logo.svg'); ?>
                </div>
                <span class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-zinc-900 to-zinc-600 dark:from-white dark:to-zinc-400">
                    Playground
                </span>
            </a>
            
            <div class="h-6 w-px bg-zinc-200 dark:bg-zinc-800 hidden sm:block"></div>
            
            <div class="hidden sm:flex items-center gap-2">
                <input type="text" id="snippet-title" value="<?php echo htmlspecialchars($title); ?>" placeholder="Untitled Snippet" class="bg-transparent border-none text-lg font-semibold text-zinc-800 dark:text-zinc-200 focus:ring-0 w-64 placeholder-zinc-400">
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button id="theme-toggle" class="p-2.5 text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white rounded-xl hover:bg-zinc-100 dark:hover:bg-zinc-800 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 transition-all">
                <svg id="theme-toggle-dark-icon" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                <svg id="theme-toggle-light-icon" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
            </button>
            <button onclick="runCode()" class="flex items-center gap-2 px-4 py-2 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 font-bold rounded-xl transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" /></svg>
                Run
            </button>
            <?php if (isset($_SESSION['user_id'])): ?>
                <button onclick="saveSnippet()" class="flex items-center gap-2 px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/30 transition transform hover:-translate-y-0.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h5v5.586l-1.293-1.293z" /></svg>
                    Save
                </button>
            <?php else: ?>
                <a href="auth/login.php" class="flex items-center gap-2 px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-white font-medium rounded-xl transition">
                    Login to Save
                </a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Main Workspace -->
    <main class="flex-1 flex flex-col lg:flex-row overflow-hidden">
        
        <!-- Editors Column -->
        <div class="flex-1 flex flex-col bg-zinc-50 dark:bg-zinc-950 border-r border-zinc-200 dark:border-zinc-800 w-full lg:w-1/2">
            <!-- Tabs -->
            <div class="flex items-center px-2 pt-2 bg-zinc-100 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800 gap-2">
                <button onclick="switchTab('html')" id="tab-html" class="px-5 py-2.5 rounded-t-xl font-mono text-sm font-semibold transition bg-white dark:bg-zinc-950 text-indigo-600 border-t-2 border-indigo-600 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                    index.html
                </button>
                <button onclick="switchTab('css')" id="tab-css" class="px-5 py-2.5 rounded-t-xl font-mono text-sm font-semibold transition text-zinc-500 hover:bg-white/50 dark:hover:bg-zinc-800 border-t-2 border-transparent">
                    style.css
                </button>
                <button onclick="switchTab('js')" id="tab-js" class="px-5 py-2.5 rounded-t-xl font-mono text-sm font-semibold transition text-zinc-500 hover:bg-white/50 dark:hover:bg-zinc-800 border-t-2 border-transparent">
                    script.js
                </button>
            </div>
            
            <!-- Editor Textareas -->
            <div class="flex-1 relative bg-white dark:bg-zinc-950">
                <div id="editor-html" class="editor-tab-content absolute inset-0 p-2">
                    <textarea id="code-html"><?php echo htmlspecialchars($html_code); ?></textarea>
                </div>
                <div id="editor-css" class="editor-tab-content absolute inset-0 hidden p-2">
                    <textarea id="code-css"><?php echo htmlspecialchars($css_code); ?></textarea>
                </div>
                <div id="editor-js" class="editor-tab-content absolute inset-0 hidden p-2">
                    <textarea id="code-js"><?php echo htmlspecialchars($js_code); ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- Preview Column -->
        <div class="flex-1 flex flex-col bg-white dark:bg-zinc-900 w-full lg:w-1/2 relative">
            <div class="h-10 border-b border-zinc-200 dark:border-zinc-800 flex items-center px-4 bg-zinc-50 dark:bg-zinc-900">
                <div class="flex gap-1.5">
                    <div class="w-3 h-3 rounded-full bg-red-400"></div>
                    <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                    <div class="w-3 h-3 rounded-full bg-green-400"></div>
                </div>
                <span class="ml-4 text-xs font-mono text-zinc-400">Preview Layout</span>
            </div>
            <div class="flex-1 w-full bg-white relative">
                <iframe id="preview-frame" class="absolute inset-0 w-full h-full border-0" sandbox="allow-scripts allow-modals"></iframe>
            </div>
            
            <!-- Toast Notification Area -->
            <div id="toast-container" class="absolute bottom-4 right-4 z-50 flex flex-col gap-2"></div>
        </div>
        
    </main>

    <script>
        // Init CodeMirror
        const cmOptions = {
            theme: document.documentElement.classList.contains('dark') ? 'material-darker' : 'default',
            lineNumbers: true,
            indentUnit: 4,
            matchBrackets: true,
            autoCloseBrackets: true,
            lineWrapping: true
        };

        const editorHtml = CodeMirror.fromTextArea(document.getElementById('code-html'), { ...cmOptions, mode: 'xml' });
        const editorCss = CodeMirror.fromTextArea(document.getElementById('code-css'), { ...cmOptions, mode: 'css' });
        const editorJs = CodeMirror.fromTextArea(document.getElementById('code-js'), { ...cmOptions, mode: 'javascript' });

        // Update theme on toggle click (needs to poll html class)
        const observer = new MutationObserver(function() {
            const isDark = document.documentElement.classList.contains('dark');
            const themeStr = isDark ? 'material-darker' : 'default';
            editorHtml.setOption("theme", themeStr);
            editorCss.setOption("theme", themeStr);
            editorJs.setOption("theme", themeStr);
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

        // Tab Switching Logic
        const tabs = ['html', 'css', 'js'];
        function switchTab(activeTab) {
            tabs.forEach(tab => {
                const btn = document.getElementById(`tab-${tab}`);
                const content = document.getElementById(`editor-${tab}`);
                if (tab === activeTab) {
                    btn.className = "px-5 py-2.5 rounded-t-xl font-mono text-sm font-semibold transition bg-white dark:bg-zinc-950 text-indigo-600 border-t-2 border-indigo-600 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]";
                    content.classList.remove('hidden');
                    // Refresh CodeMirror when tab becomes visible
                    if (tab === 'html') editorHtml.refresh();
                    if (tab === 'css') editorCss.refresh();
                    if (tab === 'js') editorJs.refresh();
                } else {
                    btn.className = "px-5 py-2.5 rounded-t-xl font-mono text-sm font-semibold transition text-zinc-500 hover:bg-white/50 dark:hover:bg-zinc-800 border-t-2 border-transparent";
                    content.classList.add('hidden');
                }
            });
        }

        // Run Code Logic
        function runCode() {
            const html = editorHtml.getValue();
            const css = `<style>${editorCss.getValue()}<\/style>`;
            const js = `<script>${editorJs.getValue()}<\/script>`;
            
            const previewFrame = document.getElementById('preview-frame');
            const documentContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Preview</title>
                    ${css}
                </head>
                <body>
                    ${html}
                    ${js}
                </body>
                </html>
            `;
            
            const iframeDoc = previewFrame.contentDocument || previewFrame.contentWindow.document;
            iframeDoc.open();
            iframeDoc.write(documentContent);
            iframeDoc.close();
            
            showToast('Code compiled & executed! ✓', 'success');
        }

        // Auto-run on load
        setTimeout(runCode, 500);

        // Save Snippet Logic via AJAX
        let currentSnippetId = <?php echo $snippet_id > 0 ? $snippet_id : 'null'; ?>;
        
        function saveSnippet() {
            const title = document.getElementById('snippet-title').value || 'Untitled Snippet';
            const html_code = editorHtml.getValue();
            const css_code = editorCss.getValue();
            const js_code = editorJs.getValue();
            
            const formData = new FormData();
            formData.append('title', title);
            formData.append('html_code', html_code);
            formData.append('css_code', css_code);
            formData.append('js_code', js_code);
            
            if (currentSnippetId) {
                formData.append('id', currentSnippetId);
                formData.append('action', 'update');
            } else {
                formData.append('action', 'create');
            }
            
            fetch('api_playground.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Snippet saved successfully!', 'success');
                    if (data.id && !currentSnippetId) {
                        currentSnippetId = data.id;
                        // Update URL without refreshing
                        window.history.pushState({}, '', '?id=' + currentSnippetId);
                    }
                } else {
                    showToast(data.message || 'Error saving snippet', 'error');
                }
            })
            .catch(err => {
                showToast('Network error while saving.', 'error');
            });
        }

        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            
            const bgClass = type === 'success' ? 'bg-zinc-900 border-zinc-800' : 'bg-red-900 border-red-800';
            const textClass = type === 'success' ? 'text-zinc-100' : 'text-white';
            const icon = type === 'success' ? 
                '<svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>' 
                : '<svg class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';

            toast.className = `${bgClass} flex items-center gap-3 px-4 py-3 rounded-xl shadow-2xl border transform transition-all translate-y-full opacity-0 duration-300`;
            toast.innerHTML = `${icon} <span class="font-medium text-sm ${textClass}">${message}</span>`;
            
            container.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-y-full', 'opacity-0');
            }, 10);
            
            // Animate out
            setTimeout(() => {
                toast.classList.add('translate-y-full', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>
