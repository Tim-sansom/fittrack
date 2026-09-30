/**
 * File: main.js
 * Location: /public/js/main.js
 * 
 * Purpose:
 * Handles global client-side UI interactions that do not require 
 * page-specific logic. Currently manages the dark/light theme toggle 
 * and the responsive mobile navigation menu.
 * 
 * Architectural Role:
 * Acts as the presentation layer's interaction controller. By keeping 
 * these generic UI behaviours in a single file, we maintain a clean 
 * separation of concerns and ensure consistent UX across all pages 
 * that include the standard header and footer.
 */

'use strict';

/**
 * Theme Management
 * 
 * Persists user theme preference in localStorage and respects the 
 * operating system's default colour scheme on first visit.
 * Uses a data-theme attribute on the <html> element to trigger 
 * CSS variable overrides defined in dashboard.css.
 */
(function initThemeToggle() {
    const themeToggleBtn = document.getElementById('theme-toggle');
    if (!themeToggleBtn) return; // Exit if button doesn't exist on the page

    const rootElement = document.documentElement;
    const savedTheme = localStorage.getItem('theme');
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    // Apply initial theme: explicit user choice takes precedence over OS setting.
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
 * Mobile Navigation Menu
 * 
 * Toggles the visibility of the main navigation on smaller viewports.
 * Crucially, it updates the aria-expanded attribute to maintain 
 * accessibility compliance for screen reader users.
 */
(function initMobileMenu() {
    const menuToggleBtn = document.querySelector('.mobile-menu-toggle');
    const mainNav = document.querySelector('.main-nav');
    
    if (!menuToggleBtn || !mainNav) return; // Exit if elements don't exist

    menuToggleBtn.addEventListener('click', () => {
        const isExpanded = menuToggleBtn.getAttribute('aria-expanded') === 'true';
        
        // Toggle ARIA state for accessibility
        menuToggleBtn.setAttribute('aria-expanded', !isExpanded);
        
        // Toggle CSS class to trigger the slide-down animation
        mainNav.classList.toggle('is-open');
    });
})();