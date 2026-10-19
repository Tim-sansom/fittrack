<?php
/**
 * File: header.php
 * Location: /public/includes/header.php
 *
 * Purpose:
 * Renders the full-width top navigation bar with user context, live clock,
 * and quick-access action buttons. Includes interactive dropdowns for
 * Notifications (Kudos) and Upcoming Plans (Calendar), plus the SPA/MPA toggle.
 *
 * Architectural Role:
 * Sits in grid-row 1, spanning both columns of the app grid layout.
 * Performs lightweight, scoped SQL queries to populate header widgets.
 *
 * Security:
 * - Session timeout enforced server-side on every page load.
 * - All user output escaped with htmlspecialchars().
 */
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =====================================================================
// SESSION TIMEOUT CHECK (Server-Side)
// Redirects to login if inactive for 30 minutes.
// =====================================================================
$sessionTimeout = 1800; // 30 minutes in seconds
if (isset($_SESSION['user_id']) && isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $sessionTimeout) {
        session_unset();
        session_destroy();
        if (!headers_sent()) {
            header('Location: login.php?timeout=1');
            exit;
        }
    }
    // Update last activity time on every page load
    $_SESSION['last_activity'] = time();
}

$username = $_SESSION['username'] ?? 'User';
$initials = strtoupper(substr(str_replace(' ', '', $username), 0, 2));
$userId = (int)($_SESSION['user_id'] ?? 0);
$tenantId = (int)($_SESSION['tenant_id'] ?? 0);

// =====================================================================
// MULTI-TENANT SAFETY NET
// Recovers tenant_id from DB if lost from session.
// =====================================================================
if ($userId > 0 && empty($tenantId)) {
    try {
        require_once __DIR__ . '/../../config/Database.php';
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT tenant_id FROM tenant_memberships 
            WHERE user_id = :uid AND membership_status = 'active' 
            LIMIT 1
        ");
        $stmt->execute(['uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $tenantId = (int)$row['tenant_id'];
            $_SESSION['tenant_id'] = $tenantId;
        }
    } catch (Exception $e) {
        // Fail silently
    }
}

$avatarPath = null;
$notifications = [];
$upcomingEvents = [];
$unreadNotifs = 0;

if ($userId > 0) {
    try {
        require_once __DIR__ . '/../../config/Database.php';
        $db = Database::getInstance()->getConnection();
        
        // Lazy Seed Hook
        require_once __DIR__ . '/lazy_seed.php';

        $stmt = $db->prepare("SELECT avatar_path FROM users WHERE user_id = :id");
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['avatar_path'])) {
            $avatarPath = $row['avatar_path'];
        }

        /*
         * Query 1: Recent Notifications (Kudos received by the current user).
         */
        $notifStmt = $db->prepare("
            SELECT k.created_at, u.display_name AS giver_name, al.title, al.activity_log_id
            FROM kudos k
            JOIN activity_logs al ON k.activity_log_id = al.activity_log_id
            JOIN users u ON k.user_id = u.user_id
            WHERE al.user_id = :uid AND al.tenant_id = :tid
            ORDER BY k.created_at DESC
            LIMIT 5
        ");
        $notifStmt->execute(['uid' => $userId, 'tid' => $tenantId]);
        $notifications = $notifStmt->fetchAll(PDO::FETCH_ASSOC);
        $unreadNotifs = count($notifications);

        /*
         * Query 2: Upcoming Calendar Events (Planned activities for the current user).
         */
        $calStmt = $db->prepare("
            SELECT pi.planned_date, at.name AS type_name, pi.planned_duration_minutes, pi.notes
            FROM plan_items pi
            JOIN plans p ON pi.plan_id = p.plan_id AND pi.tenant_id = p.tenant_id
            JOIN activity_types at ON pi.activity_type_id = at.activity_type_id AND pi.tenant_id = at.tenant_id
            WHERE pi.user_id = :uid AND pi.tenant_id = :tid AND pi.planned_date >= CURDATE()
            ORDER BY pi.planned_date ASC
            LIMIT 5
        ");
        $calStmt->execute(['uid' => $userId, 'tid' => $tenantId]);
        $upcomingEvents = $calStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Silently fail - header widgets are non-critical
        $notifications = [];
        $upcomingEvents = [];
        $unreadNotifs = 0;
    }
}

$initialDateTime = date('D j M, H:i');
?>
<!DOCTYPE html>
<html lang="en-GB" data-theme="light">
<head>
    <!-- SILVER BULLET: Forces all relative URLs to resolve from your project folder -->
    <base href="/COM6011/public/">
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?>FitTrack</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="icon" type="image/png" href="favicon.png">
</head>
<body>
<div class="app-grid-layout">
    <!-- Top Header: Spans full width -->
    <header class="top-header">
        <div class="header-left">
            <button class="mobile-sidebar-toggle" id="mobileSidebarToggle" aria-label="Toggle sidebar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
            <div class="header-title-group">
                <h1 class="page-heading"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
                <span class="live-datetime" id="liveDateTime"><?= $initialDateTime ?></span>
            </div>
        </div>
        <div class="header-right">
            <div class="header-actions">
                <!-- SPA/MPA Architecture Toggle -->
                <div class="spa-toggle-container">
                    <span class="toggle-label <?= (($_SESSION['spa_mode'] ?? 'mpa') === 'mpa') ? 'active' : '' ?>" id="mpaLabel">MPA</span>
                    <label class="switch" for="spaModeToggle">
                        <input type="checkbox" id="spaModeToggle" <?= (($_SESSION['spa_mode'] ?? 'mpa') === 'spa') ? 'checked' : '' ?>>
                        <span class="slider round"></span>
                    </label>
                    <span class="toggle-label <?= (($_SESSION['spa_mode'] ?? 'mpa') === 'spa') ? 'active' : '' ?>" id="spaLabel">SPA</span>
                </div>

                <!-- Notifications Dropdown (Message Icon) -->
                <div class="dropdown-container">
                    <button class="icon-btn dropdown-trigger" id="notifTrigger" aria-label="Notifications" title="Notifications" data-tooltip="View recent kudos and notifications">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <?php if ($unreadNotifs > 0): ?>
                            <span class="notification-badge" title="<?= $unreadNotifs ?> new notifications"></span>
                        <?php endif; ?>
                    </button>
                    <div class="dropdown-menu" id="notifDropdown">
                        <div class="dropdown-header">Recent Kudos</div>
                        <div class="dropdown-content">
                            <?php if (empty($notifications)): ?>
                                <div class="dropdown-empty">No new notifications.</div>
                            <?php else: ?>
                                <?php foreach ($notifications as $notif): ?>
                                    <div class="dropdown-item">
                                        <div class="notif-icon">
                                            <svg viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path></svg>
                                        </div>
                                        <div class="notif-text">
                                            <strong><?= htmlspecialchars($notif['giver_name']) ?></strong> kudos'd your workout:
                                            <span>"<?= htmlspecialchars($notif['title']) ?>"</span>
                                            <small><?= date('j M', strtotime($notif['created_at'])) ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <a href="community.php" class="dropdown-footer">View Community</a>
                    </div>
                </div>

                <!-- Calendar Dropdown -->
                <div class="dropdown-container">
                    <button class="icon-btn dropdown-trigger" id="calTrigger" aria-label="Calendar" title="Upcoming Plans" data-tooltip="View upcoming planned activities">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </button>
                    <div class="dropdown-menu" id="calDropdown">
                        <div class="dropdown-header">Upcoming Plans</div>
                        <div class="dropdown-content">
                            <?php if (empty($upcomingEvents)): ?>
                                <div class="dropdown-empty">No upcoming workouts planned.</div>
                            <?php else: ?>
                                <?php foreach ($upcomingEvents as $event): ?>
                                    <div class="dropdown-item">
                                        <div class="cal-date">
                                            <span class="cal-day"><?= date('d', strtotime($event['planned_date'])) ?></span>
                                            <span class="cal-month"><?= date('M', strtotime($event['planned_date'])) ?></span>
                                        </div>
                                        <div class="cal-text">
                                            <strong><?= htmlspecialchars($event['type_name']) ?></strong>
                                            <span><?= (int)$event['planned_duration_minutes'] ?> mins</span>
                                            <?php if (!empty($event['notes'])): ?>
                                                <small><?= htmlspecialchars(substr($event['notes'], 0, 40)) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <a href="workouts.php" class="dropdown-footer">View Workouts</a>
                    </div>
                </div>

                <!-- Theme Toggle -->
                <button class="icon-btn" id="themeToggle" aria-label="Toggle dark mode" title="Toggle theme" data-tooltip="Toggle between light and dark mode">
                    <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5"/>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                    <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    </svg>
                </button>
            </div>

            <!-- User Profile Pill -->
            <a href="profile.php" class="user-profile-pill" title="View profile" data-tooltip="View and edit your profile">
                <?php 
                // Strip leading slash so the <base> tag resolves the path correctly
                $cleanAvatarPath = $avatarPath ? ltrim($avatarPath, '/') : null;
                
                // Check if the file physically exists on the server to prevent broken image icons
                $fileExists = $cleanAvatarPath && file_exists(__DIR__ . '/../' . $cleanAvatarPath);
                ?>
                
                <?php if ($fileExists): ?>
                    <img src="<?= htmlspecialchars($cleanAvatarPath) ?>" alt="Avatar" class="avatar-circle avatar-img">
                <?php else: ?>
                    <div class="avatar-circle"><?= htmlspecialchars($initials) ?></div>
                <?php endif; ?>
                
                <span class="user-display-name"><?= htmlspecialchars($username) ?></span>
            </a>
        </div>
    </header>

    <!-- SPA/MPA Toggle Logic (FIXED: Syncs labels with toggle state) -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('spaModeToggle');
        const mpaLabel = document.getElementById('mpaLabel');
        const spaLabel = document.getElementById('spaLabel');
        
        if (toggle) {
            // Get current mode from session
            const currentMode = '<?= htmlspecialchars($_SESSION['spa_mode'] ?? 'mpa') ?>';
            
            // Set toggle state to match session
            toggle.checked = currentMode === 'spa';
            
            // Sync label highlights with toggle state
            if (currentMode === 'spa') {
                mpaLabel.classList.remove('active');
                spaLabel.classList.add('active');
            } else {
                mpaLabel.classList.add('active');
                spaLabel.classList.remove('active');
            }

            toggle.addEventListener('change', function() {
                const currentPage = '<?= htmlspecialchars($currentPage ?? 'dashboard') ?>';
                
                if (this.checked) {
                    // Switching to SPA Mode: update labels and redirect
                    mpaLabel.classList.remove('active');
                    spaLabel.classList.add('active');
                    window.location.href = 'spa/index.php?page=' + currentPage;
                } else {
                    // Switching to MPA Mode: update labels and redirect
                    mpaLabel.classList.add('active');
                    spaLabel.classList.remove('active');
                    window.location.href = currentPage + '.php';
                }
            });
        }
    });
    </script>

    <!-- Client-side Session Timeout Checker -->
    <script>
    (function() {
        const sessionTimeout = 1800000; // 30 minutes in milliseconds
        const checkInterval = 60000; // Check every minute
        
        let lastActivity = Date.now();
        
        // Track user activity to reset the timer
        function updateActivity() {
            lastActivity = Date.now();
        }
        
        // Listen for user activity
        ['click', 'keypress', 'scroll', 'mousemove', 'touchstart'].forEach(function(event) {
            document.addEventListener(event, updateActivity, true);
        });
        
        // Check for session timeout periodically
        setInterval(function() {
            if (Date.now() - lastActivity > sessionTimeout) {
                window.location.href = 'login.php?timeout=1';
            }
        }, checkInterval);
    })();
    </script>