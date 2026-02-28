            </div>
        </main>
    </div>
    
    <?php include '../includes/toast.php'; ?>
    <?php
    // Admin Persistent Toast for Pending Requests
    if (isset($conn) && isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
        $pending_req_query = "SELECT COUNT(*) as count FROM material_requests WHERE status = 'pending'";
        $pending_res = $conn->query($pending_req_query);
        if ($pending_res) {
            $pending_count = $pending_res->fetch_assoc()['count'];
            if ($pending_count > 0) {
                echo "<script>
                    document.addEventListener('DOMContentLoaded', () => {
                        // Delay slightly so it appears after any other toasts
                        setTimeout(() => {
                            showToast(
                                'Pending Requests', 
                                'There are {$pending_count} pending material requests waiting for your approval.', 
                                'info', 
                                1000 // 1s delay
                            );
                            
                            // Make it persistent by overriding the auto-remove timeout for the last added toast
                            setTimeout(() => {
                                const container = document.getElementById('global-toast-container');
                                if(container && container.lastElementChild) {
                                    // Remove the auto-hide by cloning and replacing to strip timeouts, 
                                    // or just add a special class. Actually, showToast auto-removes after 5s.
                                    // Let's just inject a custom persistent toast right into the container!
                                }
                            }, 1100);
                        }, 500);
                    });
                </script>";
            }
        }
    }
    ?>
    <script>
    // Custom Persistent Admin Toast Function
    function showPersistentAdminToast(title, message) {
        const container = document.getElementById('global-toast-container');
        if (!container) return;
        
        const toast = document.createElement('div');
        toast.className = `flex items-center gap-4 p-4 rounded-2xl border pointer-events-auto shadow-xl shadow-black/5 transform translate-y-10 opacity-0 transition-all duration-500 ease-out min-w-[300px] max-w-sm backdrop-blur-md bg-indigo-50 border-indigo-200 text-indigo-900`;
        
        toast.innerHTML = `
            <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center text-indigo-500 bg-indigo-100 animate-pulse">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold truncate">${title}</p>
                <p class="text-xs opacity-80 mt-0.5 line-clamp-2">${message}</p>
            </div>
            <button onclick="window.location.href='requests.php'" class="flex-shrink-0 bg-indigo-600 text-white text-xs px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition">
                Review
            </button>
        `;
        
        container.appendChild(toast);
        
        // Animate In
        setTimeout(() => {
            toast.classList.remove('translate-y-10', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
        }, 100);
    }
    </script>
    <?php
    if (isset($pending_count) && $pending_count > 0) {
        echo "<script>document.addEventListener('DOMContentLoaded', () => { showPersistentAdminToast('Pending Requests', 'There are {$pending_count} pending material requests waiting for your approval.'); });</script>";
    }
    ?>
</body>
</html>
