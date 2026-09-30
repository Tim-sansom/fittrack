<?php
/**
 * File: header.php
 * Location: /public/includes/header.php
 * 
 * Purpose:
 * Outputs the standardised HTML head, site navigation, and the opening <main> tag.
 * Acts as the presentation layer entry point for all authenticated pages.
 * 
 * Architectural Role:
 * Centralises the UI shell to enforce DRY (Don't Repeat Yourself) principles.
 * Handles Role-Based Access Control (RBAC) for the navigation menu by checking 
 * the session role before rendering the Admin link.
 */
declare(strict_types=1);

// Ensure the secure session is initialised before rendering user-specific content.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Dynamic page title with fallback to application name -->
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?>FitTrack</title>
    <link rel="stylesheet" href="/css/dashboard.css">
</head>
<body>
    <header class="site-header">
        <div class="logo">FitTrack</div>
        
        <!-- Hamburger menu trigger for mobile/tablet viewports -->
        <button class="mobile-menu-toggle" aria-label="Toggle navigation" aria-expanded="false">
            <span class="hamburger"></span>
        </button>
        
        <nav class="main-nav">
            <ul>
                <li><a href="/dashboard.php">Dashboard</a></li>
                <li><a href="/profile.php">Profile</a></li>
                <!-- RBAC: Only render Admin link for privileged roles -->
                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['administrator', 'super_administrator'], true)): ?>
                    <li><a href="/admin.php">Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <div class="header-actions">
            <!-- Theme toggle button (Sun/Moon icons handled via CSS/JS) -->
            <button id="theme-toggle" class="theme-toggle-btn" aria-label="Toggle dark mode">
                <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
            </button>
            
            <!-- Logout form uses POST to prevent CSRF via GET requests -->
            <form action="/logout.php" method="POST" class="logout-form">
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>
    </header>
    
    <!-- Main content area. Child pages inject their content here. -->
    <main class="site-main">