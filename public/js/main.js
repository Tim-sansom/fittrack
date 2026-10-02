/**
 * File: main.js
 * Location: /public/js/main.js
 * 
 * Purpose:
 * Handles global client-side UI interactions. Manages the dark/light theme 
 * toggle and the sidebar collapse state.
 */

'use strict';

/**
 * Theme Management
 * Persists user theme preference in localStorage.
 */
(function initThemeToggle() {
    const themeToggleBtn = document.getElementById('theme-toggle');
    if (!themeToggleBtn) return;

    const rootElement = document.documentElement;
    const savedTheme = localStorage.getItem('theme');
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
        rootElement.setAttribute('data-theme', 'dark');
    }

    themeToggleBtn.addEventListener('click', () => {
        const currentTheme = rootElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        rootElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
    });
})();

/**
 * Sidebar Toggle Logic (Desktop)
 * Persists the expanded/collapsed state in localStorage.
 */
(function initSidebarToggle() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    
    if (!sidebar || !toggleBtn) return;

    // Restore saved state
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
    }

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        const newState = sidebar.classList.contains('collapsed');
        localStorage.setItem('sidebarCollapsed', newState);
    });
})();

/**
 * Mobile Sidebar Toggle
 * Handles the slide-in drawer behaviour for viewports under 768px.
 */
(function initMobileSidebar() {
    // Look for a mobile toggle button if one exists in the header
    const mobileToggleBtn = document.getElementById('mobileSidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (!mobileToggleBtn || !sidebar) return;

    mobileToggleBtn.addEventListener('click', (e) => {
        e.stopPropagation(); // Prevent immediate closing
        sidebar.classList.toggle('mobile-open');
    });

    // Close sidebar when clicking outside of it on mobile
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768 && 
            !sidebar.contains(e.target) && 
            !mobileToggleBtn.contains(e.target) && 
            sidebar.classList.contains('mobile-open')) {
            sidebar.classList.remove('mobile-open');
        }
    });
})();