<?php
/**
 * File: index.php
 * Location: /public/spa/index.php
 *
 * Purpose:
 * Entry point for the Single Page Application (SPA) shell.
 * Uses PHP includes to replicate the exact MPA layout, then injects 
 * dynamic content into the #spa-content container via Vanilla JS.
 */
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }

// Set variables expected by header.php
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
?>

<!-- 1. Top Bun: Header -->
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<!-- 2. Meat: Sidebar -->
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

<!-- 3. The Main Content Area (Explicitly opened here) -->
<main class="main-content" id="spa-main">
    <div id="spa-feed" style="max-width: 1200px; margin: 0 auto; padding: 1rem;">
        <div style="text-align: center; padding: 2rem; color: var(--text);">Loading application data...</div>
    </div>
</main>

<!-- 4. Bottom Bun: Footer (This file closes the </main> tag) -->
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<!-- SPA Logic -->
<script src="js/router.js"></script>