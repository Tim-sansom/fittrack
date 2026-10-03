<?php declare(strict_types=1);
/**
 * File: dashboard.php
 * Location: /public/dashboard.php
 *
 * Purpose:
 * Primary command centre. Aggregates data from all modules (workouts, nutrition,
 * progress, challenges, community, profile) into interactive visualisations
 * with time-range toggling (Week/Month/Year).
 *
 * Architectural Role:
 * Demonstrates advanced SQL aggregation with dynamic date ranges,
 * client-side SVG chart re-rendering, IntersectionObserver animations,
 * and CSS keyframe animations. Serves as the application's "wow" page.
 *
 * Security:
 * - All queries scoped to authenticated user_id and tenant_id.
 * - Output escaped with htmlspecialchars().
 * - Time-range input validated against an allowlist to prevent injection.
 */
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

$pageTitle = 'Dashboard';
$currentPage = 'dashboard';

/* Time-range validation */
$validRanges = ['week', 'month', 'year'];
$range = in_array($_GET['range'] ?? '', $validRanges, true) ? $_GET['range'] : 'week';

switch ($range) {
    case 'month': $daysBack = 30; $periodLabel = 'Last 30 Days'; break;
    case 'year': $daysBack = 365; $periodLabel = 'Last 12 Months'; break;
    default: $daysBack = 7; $periodLabel = 'This Week';
}

/* QUERY 1: Hero Stats */
$weekStmt = $db->prepare("SELECT COUNT(*) as workout_count, COALESCE(SUM(duration_minutes), 0) as total_minutes FROM activity_logs WHERE user_id = :uid AND tenant_id = :tid AND activity_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
$weekStmt->execute(['uid' => $userId, 'tid' => $tenantId]);
$weekData = $weekStmt->fetch(PDO::FETCH_ASSOC) ?: ['workout_count' => 0, 'total_minutes' => 0];

/* QUERY 2: Today's Nutrition */
$nutritionStmt = $db->prepare("SELECT COALESCE(SUM(calories_consumed), 0) as consumed, COALESCE(SUM(calories_burned), 0) as burned FROM nutrition_logs WHERE user_id = :uid AND tenant_id = :tid AND log_date = CURDATE()");
$nutritionStmt->execute(['uid' => $userId, 'tid' => $tenantId]);
$nutritionData = $nutritionStmt->fetch(PDO::FETCH_ASSOC) ?: ['consumed' => 0, 'burned' => 0];
$netCalories = (int)$nutritionData['consumed'] - (int)$nutritionData['burned'];

/* QUERY 3: Macros */
try {
    $macroStmt = $db->prepare("SELECT COALESCE(SUM(protein_g), 0) as protein, COALESCE(SUM(carbs_g), 0) as carbs, COALESCE(SUM(fat_g), 0) as fat FROM nutrition_logs WHERE user_id = :uid AND tenant_id = :tid AND log_date = CURDATE()");
    $macroStmt->execute(['uid' => $userId, 'tid' => $tenantId]);
    $macroData = $macroStmt->fetch(PDO::FETCH_ASSOC) ?: ['protein' => 0, 'carbs' => 0, 'fat' => 0];
} catch (Exception $e) { $macroData = ['protein' => 0, 'carbs' => 0, 'fat' => 0]; }

/* QUERY 4: Hydration */
try {
    $hydrationStmt = $db->prepare("SELECT COALESCE(SUM(water_litres), 0) as water FROM nutrition_logs WHERE user_id = :uid AND tenant_id = :tid AND log_date = CURDATE()");
    $hydrationStmt->execute(['uid' => $userId, 'tid' => $tenantId]);
    $waterIntake = (float)(($hydrationStmt->fetch(PDO::FETCH_ASSOC) ?: ['water' => 0])['water']);
} catch (Exception $e) { $waterIntake = 0; }

/* QUERY 5: Activity Distribution */
$distStmt = $db->prepare("SELECT at.name as type_name, COUNT(*) as count, COALESCE(SUM(al.duration_minutes), 0) as total_mins FROM activity_logs al JOIN activity_types at ON al.activity_type_id = at.activity_type_id AND al.tenant_id = at.tenant_id WHERE al.user_id = :uid AND al.tenant_id = :tid AND al.activity_date >= DATE_SUB(CURDATE(), INTERVAL :days DAY) GROUP BY at.name ORDER BY count DESC");
$distStmt->bindValue(':uid', $userId, PDO::PARAM_INT);
$distStmt->bindValue(':tid', $tenantId, PDO::PARAM_INT);
$distStmt->bindValue(':days', $daysBack, PDO::PARAM_INT);
$distStmt->execute();
$activityDist = $distStmt->fetchAll(PDO::FETCH_ASSOC);

/* QUERY 6: Volume Data */
$volumeStmt = $db->prepare("SELECT activity_date, COUNT(*) as session_count, COALESCE(SUM(duration_minutes), 0) as total_mins FROM activity_logs WHERE user_id = :uid AND tenant_id = :tid AND activity_date >= DATE_SUB(CURDATE(), INTERVAL :days DAY) GROUP BY activity_date ORDER BY activity_date ASC");
$volumeStmt->bindValue(':uid', $userId, PDO::PARAM_INT);
$volumeStmt->bindValue(':tid', $tenantId, PDO::PARAM_INT);
$volumeStmt->bindValue(':days', $daysBack, PDO::PARAM_INT);
$volumeStmt->execute();
$volumeData = $volumeStmt->fetchAll(PDO::FETCH_ASSOC);

/* QUERY 7: Streak */
$streakStmt = $db->prepare("SELECT activity_date FROM activity_logs WHERE user_id = :uid AND tenant_id = :tid AND activity_date <= CURDATE() GROUP BY activity_date ORDER BY activity_date DESC");
$streakStmt->execute(['uid' => $userId, 'tid' => $tenantId]);
$streakDates = array_column($streakStmt->fetchAll(PDO::FETCH_ASSOC), 'activity_date');
$currentStreak = 0;
$today = new DateTime(); $yesterday = (new DateTime())->modify('-1 day');
$todayStr = $today->format('Y-m-d'); $yesterdayStr = $yesterday->format('Y-m-d');
if (in_array($todayStr, $streakDates) || in_array($yesterdayStr, $streakDates)) {
    $currentStreak = 1;
    $checkDate = in_array($todayStr, $streakDates) ? $today : $yesterday;
    while (true) { $checkDate->modify('-1 day'); if (in_array($checkDate->format('Y-m-d'), $streakDates)) { $currentStreak++; } else { break; } }
}

/* QUERY 8: Mini Heatmap */
$miniHeatmapStmt = $db->prepare("SELECT activity_date, COUNT(*) as count FROM activity_logs WHERE user_id = :uid AND tenant_id = :tid AND activity_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY activity_date");
$miniHeatmapStmt->execute(['uid' => $userId, 'tid' => $tenantId]);
$miniHeatmap = $miniHeatmapStmt->fetchAll(PDO::FETCH_KEY_PAIR);

/* QUERY 9: Challenges */
$challengeStmt = $db->prepare("SELECT challenge_id, title, target_value, current_value, unit, end_date, ROUND((current_value / NULLIF(target_value, 0)) * 100, 1) as progress_pct FROM challenges WHERE user_id = :uid AND tenant_id = :tid AND status = 'active' ORDER BY end_date ASC LIMIT 3");
$challengeStmt->execute(['uid' => $userId, 'tid' => $tenantId]);
$challenges = $challengeStmt->fetchAll(PDO::FETCH_ASSOC);

/* QUERY 10: Kudos */
$kudosStmt = $db->prepare("SELECT u.display_name, al.title, k.created_at FROM kudos k JOIN activity_logs al ON k.activity_log_id = al.activity_log_id JOIN users u ON k.user_id = u.user_id WHERE al.user_id = :uid AND al.tenant_id = :tid ORDER BY k.created_at DESC LIMIT 3");
$kudosStmt->execute(['uid' => $userId, 'tid' => $tenantId]);
$recentKudos = $kudosStmt->fetchAll(PDO::FETCH_ASSOC);

/* QUERY 11: Upcoming */
$upcomingStmt = $db->prepare("SELECT pi.planned_date, at.name as type_name, pi.planned_duration_minutes FROM plan_items pi JOIN plans p ON pi.plan_id = p.plan_id AND pi.tenant_id = p.tenant_id JOIN activity_types at ON pi.activity_type_id = at.activity_type_id AND pi.tenant_id = at.tenant_id WHERE pi.user_id = :uid AND pi.tenant_id = :tid AND pi.planned_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND pi.completion_status = 'planned' ORDER BY pi.planned_date ASC LIMIT 5");
$upcomingStmt->execute(['uid' => $userId, 'tid' => $tenantId]);
$upcomingWorkouts = $upcomingStmt->fetchAll(PDO::FETCH_ASSOC);

/* QUERY 12: PRs */
$prStmt = $db->prepare("SELECT at.name as type_name, MAX(al.duration_minutes) as best_duration, MAX(al.distance_km) as best_distance FROM activity_logs al JOIN activity_types at ON al.activity_type_id = at.activity_type_id AND al.tenant_id = at.tenant_id WHERE al.user_id = :uid AND al.tenant_id = :tid GROUP BY at.name ORDER BY best_duration DESC LIMIT 3");
$prStmt->execute(['uid' => $userId, 'tid' => $tenantId]);
$personalRecords = $prStmt->fetchAll(PDO::FETCH_ASSOC);

/* QUERY 13: Latest Measure */
$measureStmt = $db->prepare("SELECT weight_kg, bmi FROM user_measurements WHERE user_id = :uid ORDER BY recorded_at DESC LIMIT 1");
$measureStmt->execute(['uid' => $userId]);
$latestMeasure = $measureStmt->fetch(PDO::FETCH_ASSOC) ?: null;

/* QUERY 14: Weight Trend */
$weightTrendStmt = $db->prepare("SELECT weight_kg, recorded_at FROM user_measurements WHERE user_id = :uid ORDER BY recorded_at DESC LIMIT 5");
$weightTrendStmt->execute(['uid' => $userId]);
$weightTrend = array_reverse($weightTrendStmt->fetchAll(PDO::FETCH_ASSOC));

/* QUERY 15: Community Goal */
$communityGoalStmt = $db->prepare("SELECT COALESCE(SUM(duration_minutes), 0) as tenant_minutes FROM activity_logs WHERE tenant_id = :tid AND activity_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND visibility IN ('tenant', 'public')");
$communityGoalStmt->execute(['tid' => $tenantId]);
$communityGoal = (int)($communityGoalStmt->fetchColumn() ?: 0);
$communityGoalTarget = 10000;
$communityGoalPct = min(100, round(($communityGoal / $communityGoalTarget) * 100, 1));

/* QUERY 16: Recent Activities */
$recentStmt = $db->prepare("SELECT al.activity_log_id, al.title, al.duration_minutes, al.activity_date, at.name as type_name FROM activity_logs al JOIN activity_types at ON al.activity_type_id = at.activity_type_id AND al.tenant_id = at.tenant_id WHERE al.user_id = :uid AND al.tenant_id = :tid ORDER BY al.activity_date DESC, al.created_at DESC LIMIT 5");
$recentStmt->execute(['uid' => $userId, 'tid' => $tenantId]);
$recentActivities = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

$chartData = [
    'range' => $range, 'periodLabel' => $periodLabel, 'daysBack' => $daysBack,
    'weekMinutes' => (int)$weekData['total_minutes'], 'weekWorkouts' => (int)$weekData['workout_count'],
    'streak' => $currentStreak, 'netCalories' => $netCalories,
    'caloriesConsumed' => (int)$nutritionData['consumed'], 'caloriesBurned' => (int)$nutritionData['burned'],
    'waterIntake' => $waterIntake,
    'macros' => ['protein' => (int)$macroData['protein'], 'carbs' => (int)$macroData['carbs'], 'fat' => (int)$macroData['fat']],
    'activityDist' => $activityDist, 'volumeData' => $volumeData,
    'miniHeatmap' => $miniHeatmap, 'weightTrend' => $weightTrend,
    'latestWeight' => $latestMeasure['weight_kg'] ?? null, 'latestBmi' => $latestMeasure['bmi'] ?? null,
    'communityGoalPct' => $communityGoalPct, 'communityGoalMinutes' => $communityGoal, 'communityGoalTarget' => $communityGoalTarget,
];

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="main-content dashboard-main">
    <div class="hero-stats-grid">
        <div class="stat-card-hero animate-on-scroll">
            <div class="stat-icon-ring">
                <svg viewBox="0 0 36 36" class="ring-chart"><path class="ring-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="var(--border)" stroke-width="3"/><path class="ring-fill" stroke-dasharray="<?= min(100, (($weekData['workout_count'] ?? 0) / 5) * 100) ?>, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="var(--accent)" stroke-width="3" stroke-linecap="round"/></svg>
                <div class="stat-value" data-target="<?= $weekData['workout_count'] ?? 0 ?>">0</div>
            </div>
            <div class="stat-label">Workouts This Week</div>
            <div class="stat-trend positive">Goal: 5 sessions</div>
        </div>
        <div class="stat-card-hero animate-on-scroll" style="animation-delay: 0.1s">
            <div class="stat-icon-ring">
                <svg viewBox="0 0 36 36" class="ring-chart"><path class="ring-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="var(--border)" stroke-width="3"/><path class="ring-fill" stroke-dasharray="<?= min(100, (($weekData['total_minutes'] ?? 0) / 150) * 100) ?>, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="var(--highlight)" stroke-width="3" stroke-linecap="round"/></svg>
                <div class="stat-value" data-target="<?= $weekData['total_minutes'] ?? 0 ?>">0</div>
            </div>
            <div class="stat-label">Minutes Active</div>
            <div class="stat-trend positive">Goal: 150 mins</div>
        </div>
        <div class="stat-card-hero animate-on-scroll" style="animation-delay: 0.2s">
            <div class="stat-icon-ring">
                <svg viewBox="0 0 36 36" class="ring-chart"><path class="ring-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="var(--border)" stroke-width="3"/><path class="ring-fill" stroke-dasharray="<?= min(100, (abs($netCalories) / 2000) * 100) ?>, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="<?= $netCalories > 0 ? 'var(--alert)' : 'var(--success)' ?>" stroke-width="3" stroke-linecap="round"/></svg>
                <div class="stat-value" data-target="<?= $netCalories ?>">0</div>
            </div>
            <div class="stat-label">Net Calories Today</div>
            <div class="stat-trend <?= $netCalories > 0 ? 'negative' : 'positive' ?>"><?= $netCalories > 0 ? 'Surplus' : ($netCalories < 0 ? 'Deficit' : 'Balanced') ?></div>
        </div>
        <div class="stat-card-hero streak-card animate-on-scroll" style="animation-delay: 0.3s">
            <div class="streak-flame"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg></div>
            <div class="stat-value" data-target="<?= $currentStreak ?>">0</div>
            <div class="stat-label">Day Streak</div>
            <div class="stat-trend positive"><?= $currentStreak > 0 ? 'Keep it going!' : 'Start today' ?></div>
        </div>
    </div>

    <div class="chart-card-wide animate-on-scroll">
        <div class="chart-header-row">
            <div><h3>Activity Progress</h3><p class="chart-subtitle"><?= htmlspecialchars($periodLabel) ?> &middot; <?= count($volumeData) ?> active days</p></div>
            <div class="time-range-toggles">
                <a href="?range=week" class="toggle-btn <?= $range === 'week' ? 'active' : '' ?>">Week</a>
                <a href="?range=month" class="toggle-btn <?= $range === 'month' ? 'active' : '' ?>">Month</a>
                <a href="?range=year" class="toggle-btn <?= $range === 'year' ? 'active' : '' ?>">Year</a>
            </div>
        </div>
        <div class="chart-container tall">
            <svg id="volumeChart" class="line-chart-svg" viewBox="0 0 800 280" preserveAspectRatio="none">
                <g id="volumeGrid"></g>
                <path id="volumeArea" class="chart-area" fill="url(#volumeGradient)"/>
                <path id="volumeLine" class="chart-line" fill="none" stroke="var(--accent)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <g id="volumePoints"></g>
                <defs><linearGradient id="volumeGradient" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="var(--accent)" stop-opacity="0.3"/><stop offset="100%" stop-color="var(--accent)" stop-opacity="0"/></linearGradient></defs>
            </svg>
            <div class="chart-tooltip" id="volumeTooltip"></div>
        </div>
        <div class="chart-summary-row">
            <div class="summary-stat"><span class="summary-label">Total Sessions</span><span class="summary-value"><?= array_sum(array_column($volumeData, 'session_count')) ?></span></div>
            <div class="summary-stat"><span class="summary-label">Total Minutes</span><span class="summary-value"><?= array_sum(array_column($volumeData, 'total_mins')) ?></span></div>
            <div class="summary-stat"><span class="summary-label">Avg per Session</span><span class="summary-value"><?php $ts = array_sum(array_column($volumeData, 'session_count')); $tm = array_sum(array_column($volumeData, 'total_mins')); echo $ts > 0 ? round($tm / $ts) : 0; ?></span></div>
            <div class="summary-stat"><span class="summary-label">Active Days</span><span class="summary-value"><?= count($volumeData) ?> / <?= $daysBack ?></span></div>
        </div>
    </div>

    <div class="triple-grid">
        <div class="chart-card animate-on-scroll">
            <h3>Activity Distribution</h3><p class="chart-subtitle"><?= htmlspecialchars($periodLabel) ?></p>
            <div class="chart-container"><div class="bar-chart" id="activityDistChart">
                <?php if (empty($activityDist)): ?><div class="empty-state">No activities in this period.</div><?php else: $maxCount = max(array_column($activityDist, 'count')); foreach ($activityDist as $item): $heightPct = ($item['count'] / $maxCount) * 100; ?>
                <div class="bar-group" data-tooltip="<?= htmlspecialchars($item['type_name']) ?>: <?= $item['count'] ?> sessions"><div class="bar-value"><?= $item['count'] ?></div><div class="bar" style="height: 0%" data-height="<?= $heightPct ?>%"></div><div class="bar-label"><?= htmlspecialchars($item['type_name']) ?></div></div>
                <?php endforeach; endif; ?>
            </div></div>
        </div>
        <div class="chart-card animate-on-scroll" style="animation-delay: 0.1s">
            <h3>Calorie Breakdown</h3><p class="chart-subtitle">Today's intake vs burn</p>
            <div class="calorie-breakdown">
                <div class="calorie-ring-container">
                    <svg viewBox="0 0 120 120" class="donut-svg"><circle class="donut-bg" cx="60" cy="60" r="50" fill="none" stroke="var(--border)" stroke-width="12"/><circle class="donut-intake" cx="60" cy="60" r="50" fill="none" stroke="var(--accent)" stroke-width="12" stroke-dasharray="314" stroke-dashoffset="314" stroke-linecap="round" data-target="<?= min(314, 314 - (min((int)($nutritionData['consumed'] ?? 0), 3000) / 3000) * 314) ?>"/><circle class="donut-burn" cx="60" cy="60" r="38" fill="none" stroke="var(--success)" stroke-width="10" stroke-dasharray="239" stroke-dashoffset="239" stroke-linecap="round" data-target="<?= min(239, 239 - (min((int)($nutritionData['burned'] ?? 0), 1500) / 1500) * 239) ?>"/></svg>
                    <div class="donut-center"><div class="donut-net <?= $netCalories > 0 ? 'surplus' : 'deficit' ?>"><?= $netCalories > 0 ? '+' : '' ?><?= $netCalories ?></div><div class="donut-center-label">kcal net</div></div>
                </div>
                <div class="calorie-legend">
                    <div class="legend-row"><span class="legend-dot" style="background: var(--accent)"></span><span class="legend-label">Consumed</span><span class="legend-value"><?= number_format((int)($nutritionData['consumed'] ?? 0)) ?> kcal</span></div>
                    <div class="legend-row"><span class="legend-dot" style="background: var(--success)"></span><span class="legend-label">Burned</span><span class="legend-value"><?= number_format((int)($nutritionData['burned'] ?? 0)) ?> kcal</span></div>
                </div>
            </div>
        </div>
        <div class="chart-card animate-on-scroll" style="animation-delay: 0.2s">
            <h3>Macros & Hydration</h3><p class="chart-subtitle">Today's breakdown</p>
            <div class="macro-donut-container">
                <?php $totalMacros = (int)($macroData['protein'] ?? 0) + (int)($macroData['carbs'] ?? 0) + (int)($macroData['fat'] ?? 0); $proteinPct = $totalMacros > 0 ? ((int)$macroData['protein'] / $totalMacros) * 100 : 33; $carbsPct = $totalMacros > 0 ? ((int)$macroData['carbs'] / $totalMacros) * 100 : 34; $fatPct = $totalMacros > 0 ? ((int)$macroData['fat'] / $totalMacros) * 100 : 33; ?>
                <svg viewBox="0 0 100 100" class="macro-svg"><circle cx="50" cy="50" r="40" fill="none" stroke="var(--border)" stroke-width="14"/><circle cx="50" cy="50" r="40" fill="none" stroke="#4CAF50" stroke-width="14" stroke-dasharray="<?= $proteinPct * 2.51 ?> 251" stroke-dashoffset="0" transform="rotate(-90 50 50)"/><circle cx="50" cy="50" r="40" fill="none" stroke="#9F5F91" stroke-width="14" stroke-dasharray="<?= $carbsPct * 2.51 ?> 251" stroke-dashoffset="<?= -$proteinPct * 2.51 ?>" transform="rotate(-90 50 50)"/><circle cx="50" cy="50" r="40" fill="none" stroke="#F6EA98" stroke-width="14" stroke-dasharray="<?= $fatPct * 2.51 ?> 251" stroke-dashoffset="<?= -($proteinPct + $carbsPct) * 2.51 ?>" transform="rotate(-90 50 50)"/></svg>
                <div class="macro-legend-inline"><div class="macro-item"><span class="macro-dot" style="background:#4CAF50"></span>Protein <?= (int)($macroData['protein'] ?? 0) ?>g</div><div class="macro-item"><span class="macro-dot" style="background:#9F5F91"></span>Carbs <?= (int)($macroData['carbs'] ?? 0) ?>g</div><div class="macro-item"><span class="macro-dot" style="background:#F6EA98"></span>Fat <?= (int)($macroData['fat'] ?? 0) ?>g</div></div>
            </div>
            <div class="hydration-section"><div class="hydration-header"><span>Hydration</span><span class="hydration-value"><?= number_format($waterIntake, 1) ?>L / 2.5L</span></div><div class="hydration-track"><div class="hydration-fill" style="width: <?= min(100, ($waterIntake / 2.5) * 100) ?>%"></div></div></div>
        </div>
    </div>

    <div class="dual-grid">
        <div class="chart-card animate-on-scroll">
            <h3>Last 7 Days</h3><p class="chart-subtitle">Daily activity intensity</p>
            <div class="mini-heatmap">
                <?php $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']; for ($i = 6; $i >= 0; $i--): $d = new DateTime(); $d->modify("-{$i} days"); $dateStr = $d->format('Y-m-d'); $count = $miniHeatmap[$dateStr] ?? 0; $level = $count === 0 ? 0 : ($count === 1 ? 1 : ($count === 2 ? 2 : 3)); $dayName = $dayNames[(int)$d->format('N') - 1]; $isToday = $i === 0; ?>
                <div class="mini-heatmap-day level-<?= $level ?> <?= $isToday ? 'today' : '' ?>" title="<?= $d->format('j M') ?>: <?= $count ?> activities"><span class="mini-day-label"><?= $dayName ?></span><span class="mini-day-count"><?= $count ?></span></div>
                <?php endfor; ?>
            </div>
        </div>
        <div class="chart-card animate-on-scroll" style="animation-delay: 0.1s">
            <h3>Weight Trend</h3><p class="chart-subtitle"><?php if ($latestMeasure): ?>Current: <?= number_format((float)$latestMeasure['weight_kg'], 1) ?> kg<?php if ($latestMeasure['bmi']): ?> &middot; BMI <?= $latestMeasure['bmi'] ?><?php endif; ?><?php else: ?>No measurements yet<?php endif; ?></p>
            <div class="chart-container short">
                <?php if (count($weightTrend) >= 2): ?><svg id="weightChart" class="sparkline-svg" viewBox="0 0 300 100" preserveAspectRatio="none"><path id="weightLine" class="sparkline" fill="none" stroke="var(--success)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><g id="weightPoints"></g></svg><?php else: ?><div class="empty-state">Log measurements in Profile to see trends.</div><?php endif; ?>
            </div>
            <?php if (count($weightTrend) >= 2): $first = (float)$weightTrend[0]['weight_kg']; $last = (float)end($weightTrend)['weight_kg']; $change = $last - $first; $changeClass = $change < 0 ? 'positive' : ($change > 0 ? 'negative' : ''); $changeSymbol = $change < 0 ? '-' : '+'; ?>
            <div class="sparkline-footer"><span class="change <?= $changeClass ?>"><?= $changeSymbol ?><?= number_format(abs($change), 1) ?> kg over period</span></div><?php endif; ?>
        </div>
    </div>

    <div class="dual-grid">
        <div class="card community-goal-card animate-on-scroll">
            <div class="goal-info"><h3>Tenant Goal: <?= number_format($communityGoalTarget) ?> Active Minutes This Month</h3><p>Together, we've logged <strong><?= number_format($communityGoal) ?></strong> minutes.</p></div>
            <div class="progress-track" style="height: 16px; border-radius: 8px;"><div class="progress-fill" style="width: <?= $communityGoalPct ?>%; border-radius: 8px;"></div></div>
            <div class="goal-footer"><span><?= $communityGoalPct ?>% complete</span><a href="/community.php" class="goal-link">View Community</a></div>
        </div>
        <div class="card animate-on-scroll" style="animation-delay: 0.1s">
            <h3>Quick Log</h3><p class="chart-subtitle">Fast access to common actions</p>
            <div class="quick-log-grid">
                <a href="/workouts.php" class="quick-log-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6.5 6.5h11M6.5 17.5h11M4 10v4M20 10v4M8 6v12M16 6v12"/></svg><span>Gym</span></a>
                <a href="/workouts.php" class="quick-log-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="5" r="2"/><path d="M9 20l3-7 3 7"/></svg><span>Run</span></a>
                <a href="/nutrition.php" class="quick-log-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/></svg><span>Food</span></a>
                <a href="/profile.php" class="quick-log-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12h4l3-9 4 18 3-9h4"/></svg><span>Weigh-in</span></a>
                <a href="/challenges.php" class="quick-log-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg><span>Challenge</span></a>
                <a href="/community.php" class="quick-log-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg><span>Community</span></a>
            </div>
        </div>
    </div>

    <div class="triple-grid">
        <div class="chart-card animate-on-scroll">
            <div class="chart-header-row compact"><h3>Recent Activities</h3><a href="/workouts.php" class="chart-link">View all</a></div>
            <?php if (empty($recentActivities)): ?><div class="empty-state">No activities logged yet.</div><?php else: ?><div class="activity-feed"><?php foreach ($recentActivities as $act): ?><div class="activity-feed-item"><div class="activity-feed-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div><div class="activity-feed-details"><strong><?= htmlspecialchars($act['title']) ?></strong><span><?= htmlspecialchars($act['type_name']) ?></span><small><?= date('j M', strtotime($act['activity_date'])) ?><?php if ($act['duration_minutes']): ?> &middot; <?= $act['duration_minutes'] ?> mins<?php endif; ?></small></div></div><?php endforeach; ?></div><?php endif; ?>
        </div>
        <div class="chart-card animate-on-scroll" style="animation-delay: 0.1s">
            <div class="chart-header-row compact"><h3>Recent Kudos</h3><a href="/community.php" class="chart-link">View community</a></div>
            <?php if (empty($recentKudos)): ?><div class="empty-state">No kudos yet. Share a workout!</div><?php else: ?><div class="kudos-list"><?php foreach ($recentKudos as $kudo): ?><div class="kudo-item"><div class="kudo-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg></div><div class="kudo-text"><strong><?= htmlspecialchars($kudo['display_name']) ?></strong><span>kudos'd "<?= htmlspecialchars($kudo['title']) ?>"</span><small><?= date('j M', strtotime($kudo['created_at'])) ?></small></div></div><?php endforeach; ?></div><?php endif; ?>
        </div>
        <div class="chart-card animate-on-scroll" style="animation-delay: 0.2s">
            <div class="chart-header-row compact"><h3>Personal Records</h3><a href="/progress.php" class="chart-link">View progress</a></div>
            <?php if (empty($personalRecords)): ?><div class="empty-state">Complete workouts to set records!</div><?php else: ?><div class="pr-list"><?php foreach ($personalRecords as $pr): ?><div class="pr-item"><div class="pr-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg></div><div class="pr-details"><strong><?= htmlspecialchars($pr['type_name']) ?></strong><?php if ($pr['best_duration']): ?><span><?= $pr['best_duration'] ?> mins</span><?php endif; ?><?php if ($pr['best_distance']): ?><span><?= $pr['best_distance'] ?> km</span><?php endif; ?></div></div><?php endforeach; ?></div><?php endif; ?>
        </div>
    </div>
</div>

<script>const dashboardData = <?= json_encode($chartData, JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<script src="/js/dashboard.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>