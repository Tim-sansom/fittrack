<?php
/**
 * File: challenges.php
 * Location: /public/challenges.php
 *
 * Purpose:
 * Renders the Challenges & Goals page for the Multi-Page Application (MPA).
 * Displays a 365-day activity heatmap, tabbed challenge lists, and a modal
 * for creating new user-defined fitness challenges.
 *
 * Architectural Role:
 * Serves as the primary view for the challenges module. It aggregates data
 * from the `challenges` and `activity_logs` tables to provide visual feedback
 * on user consistency and goal progression.
 *
 * Security:
 * - Enforces session authentication.
 * - Scopes all database queries to the authenticated user_id and tenant_id.
 * - Escapes output using htmlspecialchars() to prevent XSS.
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
$pageTitle = 'Challenges';
$currentPage = 'challenges';

/**
 * Fetch user challenges grouped by status.
 */
$stmt = $db->prepare("
    SELECT * FROM challenges
    WHERE user_id = :uid AND tenant_id = :tid
    ORDER BY created_at DESC
");
$stmt->execute(['uid' => $userId, 'tid' => $tenantId]);
$allChallenges = $stmt->fetchAll(PDO::FETCH_ASSOC);
$activeChallenges = array_filter($allChallenges, fn($c) => $c['status'] === 'active');
$completedChallenges = array_filter($allChallenges, fn($c) => $c['status'] === 'completed');
$expiredChallenges = array_filter($allChallenges, fn($c) => $c['status'] === 'expired');

/**
 * Fetch activity logs for the last 365 days to generate the heatmap data.
 */
$stmt = $db->prepare("
    SELECT activity_date, COUNT(*) as count
    FROM activity_logs
    WHERE user_id = :uid AND tenant_id = :tid
    AND activity_date >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)
    GROUP BY activity_date
");
$stmt->execute(['uid' => $userId, 'tid' => $tenantId]);
$activityRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$activityData = [];
foreach ($activityRows as $row) {
    $activityData[$row['activity_date']] = (int)$row['count'];
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<main class="main-content">
    <div class="page-header">
        <h1>Challenges & Goals</h1>
        <button class="btn-primary" id="openChallengeModal" data-tooltip="Create a new personal fitness challenge.">+ New Challenge</button>
    </div>

    <!-- Activity Heatmap -->
    <div class="card" style="margin-bottom: 2rem;">
        <h3>Activity Consistency (Last 365 Days)</h3>
        <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <div id="heatmapGrid" class="heatmap-grid" data-tooltip="Visualises your daily activity over the past year. Darker squares indicate more activity."></div>
        </div>
        <div class="heatmap-legend">
            <span>Less</span>
            <div class="legend-box"></div>
            <div class="legend-box level-1"></div>
            <div class="legend-box level-2"></div>
            <div class="legend-box level-3"></div>
            <div class="legend-box level-4"></div>
            <span>More</span>
        </div>
    </div>

    <!-- Challenge Tabs -->
    <div class="tabs-container">
        <div class="tabs">
            <button class="tab-btn active" data-status="active" data-tooltip="View your currently active challenges.">Active (<span id="activeGoalCount"><?= count($activeChallenges) ?></span>)</button>
            <button class="tab-btn" data-status="completed" data-tooltip="View challenges you have successfully completed.">Completed</button>
            <button class="tab-btn" data-status="expired" data-tooltip="View challenges that have passed their end date.">Expired</button>
        </div>
    </div>

    <!-- Challenge List Container -->
    <div id="challengeList" class="challenge-grid">
        <!-- Populated dynamically by challenges.js -->
    </div>
</main>

<!-- Create Challenge Modal -->
<div class="modal-overlay" id="challengeModal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Create New Challenge</h3>
            <button class="close-modal" id="closeChallengeModal" data-tooltip="Close this window.">&times;</button>
        </div>
        <form id="challengeForm">
            <div class="form-group">
                <label for="challengeTitle">Challenge Title</label>
                <input type="text" id="challengeTitle" name="title" required placeholder="e.g. Run 100km this month" data-tooltip="Give your challenge a memorable name.">
            </div>
            <div class="form-group">
                <label for="targetValue">Target Value</label>
                <input type="number" id="targetValue" name="target_value" step="0.1" required data-tooltip="The numerical goal you want to achieve.">
            </div>
            <div class="form-group">
                <label for="unit">Unit</label>
                <select id="unit" name="unit" data-tooltip="Select the unit of measurement for your target.">
                    <option value="km">Kilometres</option>
                    <option value="sessions">Sessions</option>
                    <option value="kcal">Calories</option>
                    <option value="mins">Minutes</option>
                </select>
            </div>
            <div class="form-group">
                <label for="duration">Duration (Days)</label>
                <input type="number" id="duration" name="duration" value="30" required data-tooltip="How many days this challenge will last.">
            </div>
            <button type="submit" class="btn-primary" data-tooltip="Saves the new challenge to your list.">Create Challenge</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<!-- Inject heatmap data for JS consumption -->
<script>
const activityData = <?= json_encode($activityData) ?>;
</script>
<script src="js/challenges.js"></script>