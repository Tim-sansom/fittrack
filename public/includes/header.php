<?php
/**
 * File: header.php
 * Location: /public/includes/header.php
 *
 * Purpose:
 * Renders the full-width top navigation bar with user context, live clock,
 * and quick-access action buttons. Now includes interactive dropdowns for
 * Notifications (Kudos) and Upcoming Plans (Calendar).
 *
 * Architectural Role:
 * Sits in grid-row 1, spanning both columns of the app grid layout.
 * Performs lightweight, scoped SQL queries to populate header widgets.
 */
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$username = $_SESSION['username'] ?? 'User';
$initials = strtoupper(substr(str_replace(' ', '', $username), 0, 2));
$userId = (int)($_SESSION['user_id'] ?? 0);
$tenantId = (int)($_SESSION['tenant_id'] ?? 0);

// Fetch avatar path if available
$avatarPath = null;
if ($userId > 0) {
    try {
        require_once __DIR__ . '/../../config/Database.php';
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("SELECT avatar_path FROM users WHERE user_id = :id");
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['avatar_path'])) {
            $avatarPath = $row['avatar_path'];
        }

        /*
         * Query 1: Recent Notifications (Kudos received by the current user).
         * Joins kudos -> activity_logs -> users (giver) to show who gave kudos and for what workout.
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
         * Joins plan_items -> plans -> activity_types to show what is scheduled next.
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
} else {
    $notifications = [];
    $upcomingEvents = [];
    $unreadNotifs = 0;
}
?>
<!DOCTYPE html>
<html lang="en-GB" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?>FitTrack</title>
    <link rel="stylesheet" href="/css/dashboard.css">
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
                <span class="live-datetime" id="liveDateTime">Loading...</span>
            </div>
        </div>
        <div class="header-right">
            <div class="header-actions">
                
                <!-- Notifications Dropdown (Message Icon) -->
                <div class="dropdown-container">
                    <button class="icon-btn dropdown-trigger" id="notifTrigger" aria-label="Notifications" title="Notifications">
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
                        <a href="/community.php" class="dropdown-footer">View Community</a>
                    </div>
                </div>

                <!-- Calendar Dropdown -->
                <div class="dropdown-container">
                    <button class="icon-btn dropdown-trigger" id="calTrigger" aria-label="Calendar" title="Upcoming Plans">
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
                        <a href="/workouts.php" class="dropdown-footer">View Workouts</a>
                    </div>
                </div>

                <!-- Theme Toggle -->
                <button class="icon-btn" id="themeToggle" aria-label="Toggle dark mode" title="Toggle theme">
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
            <a href="/profile.php" class="user-profile-pill" title="View profile">
                <?php if ($avatarPath): ?>
                    <img src="<?= htmlspecialchars($avatarPath) ?>" alt="Avatar" class="avatar-circle avatar-img">
                <?php else: ?>
                    <div class="avatar-circle"><?= htmlspecialchars($initials) ?></div>
                <?php endif; ?>
                <span class="user-display-name"><?= htmlspecialchars($username) ?></span>
            </a>
        </div>
    </header>