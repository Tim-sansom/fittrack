<?php
/**
 * File: community.php
 * Location: /public/community.php
 *
 * Purpose:
 * Renders the Community Hub. Aggregates tenant-scoped data to display a
 * collective goal, a social feed of peer activities, and a weekly leaderboard.
 *
 * Architectural Role:
 * Demonstrates complex SQL aggregation (subqueries for scalar counts) and
 * multi-tenant data isolation. The feed is rendered server-side for SEO/initial
 * paint performance, while interactions (kudos) are handled client-side.
 *
 * Security:
 * - All database queries are scoped to the authenticated user_id and tenant_id.
 * - Output is escaped using htmlspecialchars() to prevent XSS.
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/Session.php';
require_once __DIR__ . '/../config/Database.php';
Session::start();

// Explicitly set SPA mode to MPA so the header toggle renders correctly
$_SESSION['spa_mode'] = 'mpa';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$userId = (int)$_SESSION['user_id'];
$tenantId = (int)$_SESSION['tenant_id'];
$pageTitle = 'Community';
$currentPage = 'community';

/*
 * Query 1: Weekly Leaderboard
 * Aggregates total duration for all users in the current tenant over the last 7 days.
 * Filters by visibility to ensure private workouts don't leak into public stats.
 */
$leaderboardStmt = $db->prepare("
    SELECT u.display_name, SUM(al.duration_minutes) as total_mins
    FROM activity_logs al
    JOIN users u ON u.user_id = al.user_id
    WHERE al.tenant_id = :tid
    AND al.activity_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    AND (al.visibility = 'tenant' OR al.visibility = 'public' OR al.visibility IS NULL)
    GROUP BY al.user_id
    ORDER BY total_mins DESC
    LIMIT 5
");
$leaderboardStmt->execute(['tid' => $tenantId]);
$leaderboard = $leaderboardStmt->fetchAll(PDO::FETCH_ASSOC);

/*
 * Query 2: Community Feed
 * Uses correlated subqueries to count kudos.
 * Design Decision: A subquery is used instead of a LEFT JOIN on the kudos table
 * to prevent row duplication (which would occur if multiple users kudos'd the same post),
 * thereby avoiding the need for complex GROUP BY clauses on the main feed query.
 */
$feedStmt = $db->prepare("
    SELECT al.activity_log_id, al.title, al.duration_minutes, al.distance_km, al.activity_date,
           u.display_name, at.name as type_name,
           (SELECT COUNT(*) FROM kudos k WHERE k.activity_log_id = al.activity_log_id) as kudos_count,
           (SELECT COUNT(*) FROM kudos k WHERE k.activity_log_id = al.activity_log_id AND k.user_id = :uid) as user_kudos
    FROM activity_logs al
    JOIN users u ON u.user_id = al.user_id
    JOIN activity_types at ON at.activity_type_id = al.activity_type_id AND at.tenant_id = al.tenant_id
    WHERE al.tenant_id = :tid
    AND (al.visibility = 'tenant' OR al.visibility = 'public' OR al.visibility IS NULL)
    ORDER BY al.created_at DESC
    LIMIT 20
");
$feedStmt->execute(['tid' => $tenantId, 'uid' => $userId]);
$feed = $feedStmt->fetchAll(PDO::FETCH_ASSOC);

/*
 * Query 3: Tenant Goal Progress (Dynamic)
 * Calculates total minutes for the current month.
 */
$goalStmt = $db->prepare("
    SELECT COALESCE(SUM(duration_minutes), 0) as total_mins
    FROM activity_logs
    WHERE tenant_id = :tid
    AND activity_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
    AND (visibility = 'tenant' OR visibility = 'public' OR visibility IS NULL)
");
$goalStmt->execute(['tid' => $tenantId]);
$tenantTotal = (int)$goalStmt->fetchColumn();
$tenantGoal = 10000;
$goalPct = min(100, round(($tenantTotal / $tenantGoal) * 100, 1));

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<div class="main-content">
    <div class="community-header">
        <h2>Community Hub</h2>
    </div>

    <!-- Collective Goal: Gamification element to foster tenant cohesion -->
    <div class="card community-goal-card" data-tooltip="The collective activity goal for all users in your tenant this month.">
        <div class="goal-info">
            <h3>Tenant Goal: <?= number_format($tenantGoal) ?> Active Minutes This Month</h3>
            <p>Together, we've logged <strong><?= number_format($tenantTotal) ?></strong> minutes. Keep it up!</p>
        </div>
        <div class="progress-track" style="height: 16px; border-radius: 8px;">
            <div class="progress-fill" style="width: <?= $goalPct ?>%; border-radius: 8px;"></div>
        </div>
    </div>

    <!-- CSS Grid Layout: 2fr for feed, 1fr for leaderboard sidebar -->
    <div class="community-grid">
        <div class="feed-column">
            <h3>Recent Activity</h3>
            <div id="activityFeed">
                <?php if (empty($feed)): ?>
                    <div class="card empty-state">No public activities yet. Be the first to share a workout!</div>
                <?php else: ?>
                    <?php foreach ($feed as $post):
                        // Generate initials for the avatar fallback
                        $initials = strtoupper(substr(str_replace(' ', '', $post['display_name']), 0, 2));
                        $isKudos = $post['user_kudos'] > 0;
                    ?>
                    <!-- data-id attribute used by JS to target the specific API request -->
                    <div class="feed-card" data-id="<?= $post['activity_log_id'] ?>">
                        <div class="feed-header">
                            <div class="feed-avatar"><?= $initials ?></div>
                            <div class="feed-meta">
                                <strong><?= htmlspecialchars($post['display_name']) ?></strong>
                                <span>completed a <?= htmlspecialchars($post['type_name']) ?> session</span>
                                <small><?= date('j M', strtotime($post['activity_date'])) ?></small>
                            </div>
                        </div>
                        <div class="feed-body">
                            <h4><?= htmlspecialchars($post['title']) ?></h4>
                            <div class="feed-stats">
                                <?php if ($post['duration_minutes']): ?>
                                <span>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;vertical-align:middle;margin-right:4px;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                    <?= $post['duration_minutes'] ?> mins
                                </span>
                                <?php endif; ?>
                                <?php if ($post['distance_km']): ?>
                                <span>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;vertical-align:middle;margin-right:4px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                    <?= $post['distance_km'] ?> km
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="feed-footer">
                            <!-- Kudos Button: Triggers optimistic UI update via onclick -->
                            <button class="kudos-btn <?= $isKudos ? 'active' : '' ?>" onclick="toggleKudos(<?= $post['activity_log_id'] ?>, this)" data-tooltip="<?= $isKudos ? 'Remove your kudo from this activity.' : 'Give a kudo to this activity.' ?>">
                                <svg viewBox="0 0 24 24" fill="<?= $isKudos ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path></svg>
                                <span class="kudos-count"><?= $post['kudos_count'] ?></span> Kudos
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Leaderboard Sidebar -->
        <div class="leaderboard-column">
            <div class="card">
                <h3>Weekly Leaderboard</h3>
                <div class="leaderboard-list">
                    <?php if (empty($leaderboard)): ?>
                        <p class="empty-state">No data yet.</p>
                    <?php else: ?>
                        <?php foreach ($leaderboard as $index => $user):
                            // Use styled text badges instead of emojis for ranks
                            if ($index === 0) {
                                $rankDisplay = '<span class="rank-badge rank-1" data-tooltip="Top performer this week!">1st</span>';
                            } elseif ($index === 1) {
                                $rankDisplay = '<span class="rank-badge rank-2">2nd</span>';
                            } elseif ($index === 2) {
                                $rankDisplay = '<span class="rank-badge rank-3">3rd</span>';
                            } else {
                                $rankDisplay = '<span class="rank-badge rank-other">' . ($index + 1) . 'th</span>';
                            }
                        ?>
                        <div class="leaderboard-item">
                            <span class="lb-rank"><?= $rankDisplay ?></span>
                            <span class="lb-name"><?= htmlspecialchars($user['display_name']) ?></span>
                            <span class="lb-score"><?= $user['total_mins'] ?> mins</span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FIXED: Removed leading slash so the <base> tag resolves it correctly -->
<script src="js/community.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>