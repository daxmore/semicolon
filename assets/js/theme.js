// theme.js - Handle Dark Mode locally
function initTheme() {
    const theme = localStorage.getItem('theme');
    
    // Check if user has explicitly opted for dark theme, or if they haven't set anything and the system prefers dark mode
    if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
}

// Run immediately to prevent FOUC
initTheme();

document.addEventListener('DOMContentLoaded', () => {
    const themeToggles = document.querySelectorAll('.theme-toggle');
    
    themeToggles.forEach(toggle => {
        toggle.addEventListener('click', () => {
            const isDark = document.documentElement.classList.contains('dark');
            
            if (isDark) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        });
    });
});
