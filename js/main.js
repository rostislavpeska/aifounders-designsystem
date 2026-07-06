/**
 * Design System — Core
 * 
 * Main entry point for the showcase.
 * Only handles theme switching and global showcase logic.
 * Component-specific logic resides in js/components/.
 */

document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.theme-btn');
    const root = document.documentElement;
    
    // Theme Switcher
    const savedTheme = localStorage.getItem('ds-theme') || 'aiguild';
    root.dataset.theme = savedTheme;
    updateActiveButton(savedTheme);
    
    buttons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const theme = this.dataset.theme;
            root.dataset.theme = theme;
            localStorage.setItem('ds-theme', theme);
            updateActiveButton(theme);
        });
    });
    
    function updateActiveButton(theme) {
        buttons.forEach(function(btn) {
            btn.classList.toggle('active', btn.dataset.theme === theme);
        });
    }
});
