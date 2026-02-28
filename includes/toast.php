<?php
// Initialize toasts from session if they exist
$session_toasts = [];
if (isset($_SESSION['toasts'])) {
    $session_toasts = $_SESSION['toasts'];
    unset($_SESSION['toasts']); // clear it so they don't show again
} elseif (isset($_SESSION['toast'])) { // Backwards compatibility for single toast
    $session_toasts[] = $_SESSION['toast'];
    unset($_SESSION['toast']);
}
?>

<!-- Toast Container -->
<div id="global-toast-container" class="fixed bottom-5 right-5 z-[9999] flex flex-col gap-3 pointer-events-none"></div>

<script>
/**
 * Advanced Toast Notification System
 * @param {string} title 
 * @param {string} message 
 * @param {string} type - 'success', 'error', 'info', 'xp', 'badge', 'certificate'
 */
function showToast(title, message, type = 'info', delay = 0) {
    const container = document.getElementById('global-toast-container');
    if (!container) return;

    // Define icons and colors based on type
    const styles = {
        success: { icon: 'M5 13l4 4L19 7', color: 'bg-emerald-50 border-emerald-200 text-emerald-800', iconColor: 'text-emerald-500 bg-emerald-100' },
        error: { icon: 'M6 18L18 6M6 6l12 12', color: 'bg-red-50 border-red-200 text-red-800', iconColor: 'text-red-500 bg-red-100' },
        info: { icon: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', color: 'bg-blue-50 border-blue-200 text-blue-800', iconColor: 'text-blue-500 bg-blue-100' },
        xp: { icon: 'M13 10V3L4 14h7v7l9-11h-7z', color: 'bg-indigo-50 border-indigo-200 text-indigo-900', iconColor: 'text-indigo-500 bg-indigo-100' },
        badge: { icon: 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z', color: 'bg-amber-50 border-amber-200 text-amber-900', iconColor: 'text-amber-500 bg-amber-100' },
        certificate: { icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', color: 'bg-purple-50 border-purple-200 text-purple-900', iconColor: 'text-purple-500 bg-purple-100' }
    };

    const currentStyle = styles[type] || styles['info'];

    setTimeout(() => {
        // Create Toast Element
        const toast = document.createElement('div');
        toast.className = `flex items-center gap-4 p-4 rounded-2xl border pointer-events-auto shadow-xl shadow-black/5 transform translate-y-10 opacity-0 transition-all duration-500 ease-out min-w-[300px] max-w-sm backdrop-blur-md ${currentStyle.color}`;
        
        // Icon HTML
        const iconHtml = currentStyle.icon.startsWith('<') ? currentStyle.icon : `
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="${currentStyle.icon}" />
            </svg>
        `;

        toast.innerHTML = `
            <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center ${currentStyle.iconColor}">
                ${iconHtml}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold truncate">${title}</p>
                <p class="text-xs opacity-80 mt-0.5 line-clamp-2">${message}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="flex-shrink-0 p-2 opacity-50 hover:opacity-100 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        `;

        container.appendChild(toast);

        // Animate In
        setTimeout(() => {
            toast.classList.remove('translate-y-10', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
        }, 10);

        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('translate-y-10', 'opacity-0');
            setTimeout(() => toast.remove(), 500); // Wait for transition
        }, 5000);
    }, delay);
}

// Check for PHP session toasts
document.addEventListener('DOMContentLoaded', () => {
<?php if (!empty($session_toasts)): ?>
    <?php foreach ($session_toasts as $index => $toast): ?>
        showToast(
            <?php echo json_encode($toast['title'] ?? 'Notification'); ?>,
            <?php echo json_encode($toast['message'] ?? ''); ?>,
            <?php echo json_encode($toast['type'] ?? 'info'); ?>,
            <?php echo $index * 500; ?> // Stagger multiple toasts by 500ms
        );
    <?php endforeach; ?>
<?php endif; ?>
});
</script>
