/**
 * File: main.js
 * Location: /public/js/main.js
 *
 * Purpose:
 * Handles global client-side UI interactions. Manages the dark/light/system
 * theme toggle, sidebar collapse state, mobile navigation drawer, header
 * dropdowns (Notifications and Calendar), and the live header clock.
 *
 * Architectural Role:
 * Acts as the presentation layer's interaction controller. Keeps generic
 * UI behaviours in a single file to maintain a clean separation of concerns
 * and ensure consistent UX across all pages.
 */
'use strict';

/**
 * Theme Management
 * Persists user theme preference in localStorage. Supports explicit
 * 'light'/'dark' modes and 'system' mode which respects OS preferences
 * and listens for live OS theme changes.
 */
(function initThemeToggle() {
    const themeToggleBtn = document.getElementById('themeToggle');
    if (!themeToggleBtn) return;

    const rootElement = document.documentElement;

    /**
     * Applies the given theme to the document root.
     * @param {string} theme - 'light', 'dark', or 'system'
     */
    function applyTheme(theme) {
        if (theme === 'dark') {
            rootElement.setAttribute('data-theme', 'dark');
        } else if (theme === 'light') {
            rootElement.removeAttribute('data-theme');
        } else {
            // System mode: remove explicit attribute, then check OS preference
            rootElement.removeAttribute('data-theme');
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (systemPrefersDark) {
                rootElement.setAttribute('data-theme', 'dark');
            }
        }
        localStorage.setItem('theme', theme);
    }

    // Apply initial theme on load
    const savedTheme = localStorage.getItem('theme') || 'system';
    applyTheme(savedTheme);

    // Listen for OS-level theme changes if user is in 'system' mode
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (localStorage.getItem('theme') === 'system') {
            applyTheme('system');
        }
    });

    // Header toggle button: overrides system preference with explicit light/dark
    themeToggleBtn.addEventListener('click', () => {
        const isCurrentlyDark = rootElement.getAttribute('data-theme') === 'dark';
        const newTheme = isCurrentlyDark ? 'light' : 'dark';
        applyTheme(newTheme);
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
    const toggleBtn = document.getElementById('mobileSidebarToggle');
    const sidebar = document.querySelector('.sidebar');

    if (!toggleBtn || !sidebar) return;

    toggleBtn.addEventListener('click', (e) => {
        e.stopPropagation(); // Prevent immediate closing
        sidebar.classList.toggle('mobile-open');
    });

    // Close sidebar when clicking outside of it on mobile
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768 &&
            !sidebar.contains(e.target) &&
            !toggleBtn.contains(e.target) &&
            sidebar.classList.contains('mobile-open')) {
            sidebar.classList.remove('mobile-open');
        }
    });
})();

/**
 * Header Dropdown Logic
 * Handles the toggling of Notifications and Calendar dropdowns.
 * Ensures dropdowns close when clicking outside or pressing Escape.
 */
(function initHeaderDropdowns() {
    const triggers = document.querySelectorAll('.dropdown-trigger');
    const menus = document.querySelectorAll('.dropdown-menu');

    if (!triggers.length || !menus.length) return;

    // Toggle dropdown on button click
    triggers.forEach(trigger => {
        trigger.addEventListener('click', (e) => {
            e.stopPropagation(); // Prevent document click from immediately closing it
            const targetMenu = trigger.closest('.dropdown-container').querySelector('.dropdown-menu');

            // Close all other menus first
            menus.forEach(menu => {
                if (menu !== targetMenu) menu.classList.remove('active');
            });

            // Toggle current menu
            targetMenu.classList.toggle('active');
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.dropdown-container')) {
            menus.forEach(menu => menu.classList.remove('active'));
        }
    });

    // Close dropdowns on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            menus.forEach(menu => menu.classList.remove('active'));
        }
    });
})();

/**
 * Live Date/Time Clock
 * Updates the header clock every second to provide real-time context.
 */
(function initLiveClock() {
    const clockElement = document.getElementById('liveDateTime');
    if (!clockElement) return;

    function updateClock() {
        const now = new Date();
        // Format: "Sat, 3 Oct, 14:30"
        const options = {
            weekday: 'short',
            day: 'numeric',
            month: 'short',
            hour: '2-digit',
            minute: '2-digit'
        };
        clockElement.textContent = now.toLocaleDateString('en-GB', options);
    }

    updateClock();
    setInterval(updateClock, 1000);
})();