// Mobile menu toggle
const mobileMenuButton = document.querySelector('.block.md\:hidden button');
const globalNav = document.querySelector('header nav[aria-label="Global"]');

if (mobileMenuButton && globalNav) {
    mobileMenuButton.addEventListener('click', () => {
        globalNav.classList.toggle('hidden');
        // For mobile, ensure it's displayed as block when not hidden
        if (!globalNav.classList.contains('hidden')) {
            globalNav.style.display = 'block';
        } else {
            globalNav.style.display = ''; // Reset to default (which will be hidden by Tailwind's md:block)
        }
    });
}

// Confirmation dialog for delete actions
// Forms that require confirmation should have the class 'js-confirm-delete'
const deleteForms = document.querySelectorAll('form.js-confirm-delete');

deleteForms.forEach(form => {
    form.addEventListener('submit', (e) => {
        if (!confirm('Are you sure you want to delete this item?')) {
            e.preventDefault();
        }
    });
});