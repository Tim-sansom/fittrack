/* global document, window, localStorage, console, setInterval */
'use strict';

/**
 * File: main.js
 * Location: /public/js/main.js
 *
 * Purpose:
 * Handles global client-side UI interactions. Manages the dark/light/system
 * theme toggle, sidebar collapse state, mobile navigation drawer, header
 * dropdowns (Notifications and Calendar), the live header clock, and the
 * SPA/MPA architecture toggle.
 *
 * Architectural Role:
 * Acts as the presentation layer's interaction controller. Keeps generic
 * UI behaviours in a single file to maintain a clean separation of concerns
 * and ensure consistent UX across all pages.
 */

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

    function applyTheme(theme) {
        if (theme === 'dark') {
            rootElement.setAttribute('data-theme', 'dark');
        } else if (theme === 'light') {
            rootElement.removeAttribute('data-theme');
        } else {
            rootElement.removeAttribute('data-theme');
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (systemPrefersDark) {
                rootElement.setAttribute('data-theme', 'dark');
            }
        }
        localStorage.setItem('theme', theme);
    }

    const savedTheme = localStorage.getItem('theme') || 'system';
    applyTheme(savedTheme);

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
        if (localStorage.getItem('theme') === 'system') {
            applyTheme('system');
        }
    });

    themeToggleBtn.addEventListener('click', function () {
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

    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
    }

    toggleBtn.addEventListener('click', function () {
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

    toggleBtn.addEventListener('click', function () {
        sidebar.classList.toggle('mobile-open');
    });

    document.addEventListener('click', function (e) {
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

    triggers.forEach(function (trigger) {
        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            const targetMenu = trigger.closest('.dropdown-container').querySelector('.dropdown-menu');
            menus.forEach(function (menu) {
                if (menu !== targetMenu) menu.classList.remove('active');
            });
            targetMenu.classList.toggle('active');
        });
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.dropdown-container')) {
            menus.forEach(function (menu) {
                menu.classList.remove('active');
            });
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            menus.forEach(function (menu) {
                menu.classList.remove('active');
            });
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

/**
 * SPA/MPA Architecture Toggle
 * Persists user preference in localStorage and routes between the
 * Multi-Page Application and the Single-Page Application shell,
 * maintaining the current page context across the switch.
 */
(function initSpaMpaToggle() {
    const toggle = document.getElementById('spaModeToggle');
    const mpaLabel = document.getElementById('mpaLabel');
    const spaLabel = document.getElementById('spaLabel');
    if (!toggle) return;

    // 1. Load saved preference on page load
    const isSpaMode = localStorage.getItem('fittrack_spa_mode') === 'true';
    toggle.checked = isSpaMode;
    if (mpaLabel) mpaLabel.classList.toggle('active', !isSpaMode);
    if (spaLabel) spaLabel.classList.toggle('active', isSpaMode);

    // 2. Handle Toggle Click
    toggle.addEventListener('change', function () {
        const enabled = toggle.checked;
        localStorage.setItem('fittrack_spa_mode', enabled);

        if (mpaLabel) mpaLabel.classList.toggle('active', !enabled);
        if (spaLabel) spaLabel.classList.toggle('active', enabled);

        // 3. Smart Routing: Maintain current page context
        let currentPage = 'dashboard';

        if (enabled) {
            // Switching MPA -> SPA
            const path = window.location.pathname;
            const spaMatch = path.match(/\/spa\/index\.php$/);
            const mpaMatch = path.match(/\/([a-z]+)\.php$/);

            if (mpaMatch && mpaMatch[1] && !spaMatch) {
                currentPage = mpaMatch[1];
            } else if (spaMatch) {
                const hash = window.location.hash.replace('#', '');
                if (hash) currentPage = hash;
            }

            const hash = window.location.hash.replace('#', '');
            if (hash && hash !== 'index') currentPage = hash;

            const savedRoute = localStorage.getItem('fittrack_current_route');
            if (savedRoute) currentPage = savedRoute;

            console.log('MPA->SPA redirecting to:', currentPage);
            window.location.href = '../spa/index.php#' + currentPage;
        } else {
            // Switching SPA -> MPA
            const hash = window.location.hash.replace('#', '');
            if (hash && hash !== 'index') {
                currentPage = hash;
            }

            const savedRoute = localStorage.getItem('fittrack_current_route');
            if (savedRoute && savedRoute !== 'index') {
                currentPage = savedRoute;
            }

            console.log('SPA->MPA redirecting to:', currentPage);
            window.location.href = '../' + currentPage + '.php';
        }
    });
})();