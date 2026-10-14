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
        <button class="btn-primary" id="openChallengeModal">+ New Challenge</button>
    </div>

    <!-- Activity Heatmap -->
    <div class="card" style="margin-bottom: 2rem;">
        <h3>Activity Consistency (Last 365 Days)</h3>
        <div id="heatmapGrid" class="heatmap-grid"></div>
    </div>

    <!-- Challenge Tabs -->
    <div class="tabs-container">
        <div class="tabs">
            <button class="tab-btn active" data-status="active">Active (<span id="activeGoalCount"><?= count($activeChallenges) ?></span>)</button>
            <button class="tab-btn" data-status="completed">Completed</button>
            <button class="tab-btn" data-status="expired">Expired</button>
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
            <button class="close-modal" id="closeChallengeModal">&times;</button>
        </div>
        <form id="challengeForm">
            <div class="form-group">
                <label for="challengeTitle">Challenge Title</label>
                <input type="text" id="challengeTitle" name="title" required placeholder="e.g. Run 100km this month">
            </div>
            <div class="form-group">
                <label for="targetValue">Target Value</label>
                <input type="number" id="targetValue" name="target_value" step="0.1" required>
            </div>
            <div class="form-group">
                <label for="unit">Unit</label>
                <select id="unit" name="unit">
                    <option value="km">Kilometres</option>
                    <option value="sessions">Sessions</option>
                    <option value="kcal">Calories</option>
                    <option value="mins">Minutes</option>
                </select>
            </div>
            <div class="form-group">
                <label for="duration">Duration (Days)</label>
                <input type="number" id="duration" name="duration" value="30" required>
            </div>
            <button type="submit" class="btn-primary">Create Challenge</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<!-- Inject heatmap data for JS consumption -->
<script>
    const activityData = <?= json_encode($activityData) ?>;
</script>
<script src="js/challenges.js"></script>