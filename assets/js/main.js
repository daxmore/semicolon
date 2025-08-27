// Mobile menu toggle
const mobileMenuButton = document.getElementById('mobile-menu-button');
const mobileMenu = document.getElementById('mobile-menu');

if (mobileMenuButton && mobileMenu) {
    mobileMenuButton.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
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