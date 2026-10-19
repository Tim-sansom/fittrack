<?php
/**
 * File: index.php
 * Location: /public/spa/index.php
 *
 * Purpose:
 * Dynamic entry point for the Single Page Application (SPA) shell.
 * Reads the requested page from the URL, sets the correct context,
 * and injects dynamic content into the #spa-feed container via Vanilla JS.
 */
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// 1. Dynamically determine the page to load based on GET parameter
$spaPage = $_GET['page'] ?? 'dashboard';
$currentPage = $spaPage;

// 2. Map page slugs to human-readable titles for the header
$pageTitles = [
    'dashboard' => 'Dashboard',
    'workouts' => 'Workouts',
    'nutrition' => 'Nutrition',
    'progress' => 'Progress',
    'challenges' => 'Challenges',
    'community' => 'Community',
    'settings' => 'Settings',
    'plans' => 'My Plans',
    'meal-plans' => 'Meal Plans',
    'admin' => 'Admin',
    'wellbeing' => 'Physical Wellbeing',
    'profile' => 'Profile'
];

// Safely get the page title with a fallback
$pageTitle = $pageTitles[$spaPage] ?? 'Dashboard';

// 3. CRITICAL: Set session variable so the header toggle knows we are in SPA mode
$_SESSION['spa_mode'] = 'spa';

// 4. RBAC Check for Admin Page
if ($spaPage === 'admin') {
    $userRole = $_SESSION['role'] ?? '';
    if (!in_array($userRole, ['administrator', 'super_administrator'], true)) {
        header('Location: dashboard.php');
        exit;
    }
}
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

<!-- SPA Initialisation Logic -->
<script>
// Force the router to load the correct initial page based on the URL parameter
window.addEventListener('DOMContentLoaded', () => {
    const page = '<?= htmlspecialchars($spaPage) ?>';
    if (page && page !== 'dashboard') {
        window.location.hash = page;
    }
});
</script>
<!-- SPA Router -->
<script src="spa/js/router.js"></script>