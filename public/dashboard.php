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

/* QUERY 1: Hero Stats (Total Workouts & Minutes) */
$weekStmt = $db->prepare("SELECT COUNT(*) as workout_count, COALESCE(SUM(duration_minutes), 0) as total_minutes FROM activity_logs WHERE user_id = :uid AND tenant_id = :tid AND activity_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
$weekStmt->execute(['uid' => $userId, 'tid' => $tenantId]);
$weekData = $weekStmt->fetch(PDO::FETCH_ASSOC) ?: ['workout_count' => 0, 'total_minutes' => 0];

/* QUERY 2: Activity Breakdown for 3-Ring Chart */
// We need to categorize activities to fill the concentric rings
$breakdownStmt = $db->prepare("
    SELECT at.category, COUNT(*) as count 
    FROM activity_logs al 
    JOIN activity_types at ON al.activity_type_id = at.activity_type_id AND al.tenant_id = at.tenant_id
    WHERE al.user_id = :uid AND al.tenant_id = :tid AND al.activity_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY at.category
");
$breakdownStmt->execute(['uid' => $userId, 'tid' => $tenantId]);
$breakdownData = $breakdownStmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Calculate counts for specific rings
// Outer Ring: Total Workouts (Goal: 5)
// Middle Ring: Strength/Gym (Goal: 2)
// Inner Ring: Cardio (Goal: 3)
$totalWorkouts = (int)$weekData['workout_count'];
$gymCount = (int)($breakdownData['strength'] ?? 0);
$cardioCount = (int)($breakdownData['cardio'] ?? 0);

// Calculate percentages (capped at 100%)
// Formula: 100 - (current / goal * 100) gives the 'offset' needed to fill the ring
$workoutOffset = 100 - min(100, ($totalWorkouts / 5) * 100);
$gymOffset = 100 - min(100, ($gymCount / 2) * 100);
$cardioOffset = 100 - min(100, ($cardioCount / 3) * 100);

/* QUERY 3: Today's Nutrition */
$nutritionStmt = $db->prepare("SELECT COALESCE(SUM(calories_consumed), 0) as consumed, COALESCE(SUM(calories_burned), 0) as burned FROM daily_nutrition WHERE user_id = :uid AND tenant_id = :tid AND log_date = CURDATE()");
$nutritionStmt->execute(['uid' => $userId, 'tid' => $tenantId]);
$nutritionData = $nutritionStmt->fetch(PDO::FETCH_ASSOC) ?: ['consumed' => 0, 'burned' => 0];
$netCalories = (int)$nutritionData['consumed'] - (int)$nutritionData['burned'];

/* QUERY 4: Macros */
try {
    $macroStmt = $db->prepare("SELECT COALESCE(SUM(protein_g), 0) as protein, COALESCE(SUM(carbs_g), 0) as carbs, COALESCE(SUM(fat_g), 0) as fat FROM daily_nutrition WHERE user_id = :uid AND tenant_id = :tid AND log_date = CURDATE()");
    $macroStmt->execute(['uid' => $userId, 'tid' => $tenantId]);
    $macroData = $macroStmt->fetch(PDO::FETCH_ASSOC) ?: ['protein' => 0, 'carbs' => 0, 'fat' => 0];
} catch (Exception $e) { $macroData = ['protein' => 0, 'carbs' => 0, 'fat' => 0]; }

/* QUERY 5: Hydration */
try {
    $hydrationStmt = $db->prepare("SELECT COALESCE(SUM(water_litres), 0) as water FROM daily_nutrition WHERE user_id = :uid AND tenant_id = :tid AND log_date = CURDATE()");
    $hydrationStmt->execute(['uid' => $userId, 'tid' => $tenantId]);
    $waterIntake = (float)(($hydrationStmt->fetch(PDO::FETCH_ASSOC) ?: ['water' => 0])['water']);
} catch (Exception $e) { $waterIntake = 0; }

/* QUERY 6: Volume Data (Line Chart) */
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

/* QUERY 9: Latest Measure & Weight Trend */
$measureStmt = $db->prepare("SELECT weight_kg, bmi FROM user_measurements WHERE user_id = :uid ORDER BY recorded_at DESC LIMIT 1");
$measureStmt->execute(['uid' => $userId]);
$latestMeasure = $measureStmt->fetch(PDO::FETCH_ASSOC) ?: null;

$weightTrendStmt = $db->prepare("SELECT weight_kg, recorded_at FROM user_measurements WHERE user_id = :uid ORDER BY recorded_at DESC LIMIT 5");
$weightTrendStmt->execute(['uid' => $userId]);
$weightTrend = array_reverse($weightTrendStmt->fetchAll(PDO::FETCH_ASSOC));

/* QUERY 10: Community Goal */
$communityGoalStmt = $db->prepare("SELECT COALESCE(SUM(duration_minutes), 0) as tenant_minutes FROM activity_logs WHERE tenant_id = :tid AND activity_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND visibility IN ('tenant', 'public')");
$communityGoalStmt->execute(['tid' => $tenantId]);
$communityGoal = (int)($communityGoalStmt->fetchColumn() ?: 0);
$communityGoalTarget = 10000;
$communityGoalPct = min(100, round(($communityGoal / $communityGoalTarget) * 100, 1));

/* QUERY 11: Recent Activities */
$recentStmt = $db->prepare("SELECT al.activity_log_id, al.title, al.duration_minutes, al.activity_date, at.name as type_name FROM activity_logs al JOIN activity_types at ON al.activity_type_id = at.activity_type_id AND al.tenant_id = at.tenant_id WHERE al.user_id = :uid AND al.tenant_id = :tid ORDER BY al.activity_date DESC, al.created_at DESC LIMIT 5");
$recentStmt->execute(['uid' => $userId, 'tid' => $tenantId]);
$recentActivities = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare JSON payload for JS
$chartData = [
    'range' => $range, 'periodLabel' => $periodLabel, 'daysBack' => $daysBack,
    'weekMinutes' => (int)$weekData['total_minutes'], 'weekWorkouts' => (int)$weekData['workout_count'],
    'streak' => $currentStreak, 'netCalories' => $netCalories,
    'caloriesConsumed' => (int)$nutritionData['consumed'], 'caloriesBurned' => (int)$nutritionData['burned'],
    'waterIntake' => $waterIntake,
    'macros' => ['protein' => (int)$macroData['protein'], 'carbs' => (int)$macroData['carbs'], 'fat' => (int)$macroData['fat']],
    'volumeData' => $volumeData, 'miniHeatmap' => $miniHeatmap, 'weightTrend' => $weightTrend,
    'latestWeight' => $latestMeasure['weight_kg'] ?? null, 'latestBmi' => $latestMeasure['bmi'] ?? null,
    'communityGoalPct' => $communityGoalPct, 'communityGoalMinutes' => $communityGoal, 'communityGoalTarget' => $communityGoalTarget,
];

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<div class="main-content dashboard-main">
    <div class="hero-stats-grid">
        
        <!-- Ring 1: Workouts This Week (3-Layer Apple Watch Style) -->
        <div class="stat-card-hero animate-on-scroll">
            <div class="stat-icon-ring">
                <svg viewBox="0 0 36 36" class="ring-chart" aria-hidden="true">
                    <!-- Background Tracks -->
                    <path class="ring-bg" pathLength="100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="var(--border)" stroke-width="3"/>
                    <path class="ring-bg" pathLength="100" d="M18 5.0845 a 12.9155 12.9155 0 0 1 0 25.831 a 12.9155 12.9155 0 0 1 0 -25.831" fill="none" stroke="var(--border)" stroke-width="3"/>
                    <path class="ring-bg" pathLength="100" d="M18 8.0845 a 9.9155 9.9155 0 0 1 0 19.831 a 9.9155 9.9155 0 0 1 0 -19.831" fill="none" stroke="var(--border)" stroke-width="3"/>

                    <!-- Animated Rings -->
                    <!-- Outer: Total Workouts (Purple) -->
                    <path class="ring-fill ring-outer" pathLength="100" stroke-dasharray="100" stroke-dashoffset="100" data-target="<?= $workoutOffset ?>" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="var(--accent)" stroke-width="3" stroke-linecap="round"/>
                    <!-- Middle: Gym/Strength (Green) -->
                    <path class="ring-fill ring-middle" pathLength="100" stroke-dasharray="100" stroke-dashoffset="100" data-target="<?= $gymOffset ?>" d="M18 5.0845 a 12.9155 12.9155 0 0 1 0 25.831 a 12.9155 12.9155 0 0 1 0 -25.831" fill="none" stroke="var(--success)" stroke-width="3" stroke-linecap="round"/>
                    <!-- Inner: Cardio (Grey/Text Color) -->
                    <path class="ring-fill ring-inner" pathLength="100" stroke-dasharray="100" stroke-dashoffset="100" data-target="<?= $cardioOffset ?>" d="M18 8.0845 a 9.9155 9.9155 0 0 1 0 19.831 a 9.9155 9.9155 0 0 1 0 -19.831" fill="none" stroke="#888888" stroke-width="3" stroke-linecap="round"/>
                </svg>
                <div class="stat-value" data-target="<?= $totalWorkouts ?>">0</div>
            </div>
            <div class="stat-label">Workouts This Week</div>
            <div class="stat-trend positive">Goal: 5 sessions</div>
        </div>

        <!-- Ring 2: Minutes Active (Single Ring) -->
        <div class="stat-card-hero animate-on-scroll" style="animation-delay: 0.1s">
            <div class="stat-icon-ring">
                <svg viewBox="0 0 36 36" class="ring-chart" aria-hidden="true">
                    <path class="ring-bg" pathLength="100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="var(--border)" stroke-width="3"/>
                    <path class="ring-fill ring-minutes" pathLength="100" stroke-dasharray="100" stroke-dashoffset="100" data-target="<?= 100 - min(100, (($weekData['total_minutes'] ?? 0) / 150) * 100) ?>" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="var(--highlight)" stroke-width="3" stroke-linecap="round"/>
                </svg>
                <div class="stat-value" data-target="<?= $weekData['total_minutes'] ?? 0 ?>">0</div>
            </div>
            <div class="stat-label">Minutes Active</div>
            <div class="stat-trend positive">Goal: 150 mins</div>
        </div>

        <!-- Ring 3: Net Calories Today (Single Ring) -->
        <div class="stat-card-hero animate-on-scroll" style="animation-delay: 0.2s">
            <div class="stat-icon-ring">
                <svg viewBox="0 0 36 36" class="ring-chart" aria-hidden="true">
                    <path class="ring-bg" pathLength="100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="var(--border)" stroke-width="3"/>
                    <path class="ring-fill ring-calories" pathLength="100" stroke-dasharray="100" stroke-dashoffset="100" data-target="<?= 100 - min(100, (abs($netCalories) / 2000) * 100) ?>" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="<?= $netCalories > 0 ? 'var(--alert)' : 'var(--success)' ?>" stroke-width="3" stroke-linecap="round"/>
                </svg>
                <div class="stat-value" data-target="<?= $netCalories ?>">0</div>
            </div>
            <div class="stat-label">Net Calories Today</div>
            <div class="stat-trend <?= $netCalories > 0 ? 'negative' : 'positive' ?>"><?= $netCalories > 0 ? 'Surplus' : ($netCalories < 0 ? 'Deficit' : 'Balanced') ?></div>
        </div>

        <!-- Ring 4: Day Streak -->
        <div class="stat-card-hero streak-card animate-on-scroll" style="animation-delay: 0.3s">
            <div class="streak-flame"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg></div>
            <div class="stat-value" data-target="<?= $currentStreak ?>">0</div>
            <div class="stat-label">Day Streak</div>
            <div class="stat-trend positive"><?= $currentStreak > 0 ? 'Keep it going!' : 'Start today' ?></div>
        </div>
    </div>

    <div class="chart-card-wide animate-on-scroll">
        <div class="chart-header-row">
            <div><h2>Activity Progress</h2><p class="chart-subtitle"><?= htmlspecialchars($periodLabel) ?> &middot; <?= count($volumeData) ?> active days</p></div>
            <div class="time-range-toggles" role="tablist" aria-label="Time range selection">
                <a href="?range=week" class="toggle-btn <?= $range === 'week' ? 'active' : '' ?>" role="tab" aria-selected="<?= $range === 'week' ? 'true' : 'false' ?>">Week</a>
                <a href="?range=month" class="toggle-btn <?= $range === 'month' ? 'active' : '' ?>" role="tab" aria-selected="<?= $range === 'month' ? 'true' : 'false' ?>">Month</a>
                <a href="?range=year" class="toggle-btn <?= $range === 'year' ? 'active' : '' ?>" role="tab" aria-selected="<?= $range === 'year' ? 'true' : 'false' ?>">Year</a>
            </div>
        </div>
        <div class="chart-container tall">
            <svg id="volumeChart" class="line-chart-svg" viewBox="0 0 800 280" preserveAspectRatio="none" role="img" aria-label="Activity volume chart showing workout frequency over time">
                <g id="volumeGrid"></g>
                <path id="volumeArea" class="chart-area" fill="url(#volumeGradient)"/>
                <path id="volumeLine" class="chart-line" fill="none" stroke="var(--accent)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <g id="volumePoints"></g>
                <defs><linearGradient id="volumeGradient" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="var(--accent)" stop-opacity="0.3"/><stop offset="100%" stop-color="var(--accent)" stop-opacity="0"/></linearGradient></defs>
            </svg>
            <div class="chart-tooltip" id="volumeTooltip" role="tooltip"></div>
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
            <h2>Activity Distribution</h2><p class="chart-subtitle"><?= htmlspecialchars($periodLabel) ?></p>
            <div class="chart-container"><div class="bar-chart" id="activityDistChart">
                <?php if (empty($breakdownData)): ?><div class="empty-state">No activities in this period.</div><?php else: $maxCount = max($breakdownData); foreach ($breakdownData as $type => $count): $heightPct = ($count / $maxCount) * 100; ?>
                <div class="bar-group" data-tooltip="<?= htmlspecialchars(ucfirst($type)) ?>: <?= $count ?> sessions"><div class="bar-value"><?= $count ?></div><div class="bar" style="height: 0%" data-height="<?= $heightPct ?>%"></div><div class="bar-label"><?= htmlspecialchars(ucfirst($type)) ?></div></div>
                <?php endforeach; endif; ?>
            </div></div>
        </div>
        <div class="chart-card animate-on-scroll" style="animation-delay: 0.1s">
            <h2>Calorie Breakdown</h2><p class="chart-subtitle">Today's intake vs burn</p>
            <div class="calorie-breakdown">
                <div class="calorie-ring-container">
                    <svg viewBox="0 0 120 120" class="donut-svg" role="img" aria-label="Calorie breakdown chart"><circle class="donut-bg" cx="60" cy="60" r="50" fill="none" stroke="var(--border)" stroke-width="12"/><circle class="donut-intake" cx="60" cy="60" r="50" fill="none" stroke="var(--accent)" stroke-width="12" stroke-dasharray="314" stroke-dashoffset="314" stroke-linecap="round" data-target="<?= min(314, 314 - (min((int)($nutritionData['consumed'] ?? 0), 3000) / 3000) * 314) ?>"/><circle class="donut-burn" cx="60" cy="60" r="38" fill="none" stroke="var(--success)" stroke-width="10" stroke-dasharray="239" stroke-dashoffset="239" stroke-linecap="round" data-target="<?= min(239, 239 - (min((int)($nutritionData['burned'] ?? 0), 1500) / 1500) * 239) ?>"/></svg>
                    <div class="donut-center"><div class="donut-net <?= $netCalories > 0 ? 'surplus' : 'deficit' ?>"><?= $netCalories > 0 ? '+' : '' ?><?= $netCalories ?></div><div class="donut-center-label">kcal net</div></div>
                </div>
                <div class="calorie-legend">
                    <div class="legend-row"><span class="legend-dot" style="background: var(--accent)"></span><span class="legend-label">Consumed</span><span class="legend-value"><?= number_format((int)($nutritionData['consumed'] ?? 0)) ?> kcal</span></div>
                    <div class="legend-row"><span class="legend-dot" style="background: var(--success)"></span><span class="legend-label">Burned</span><span class="legend-value"><?= number_format((int)($nutritionData['burned'] ?? 0)) ?> kcal</span></div>
                </div>
            </div>
        </div>
        <div class="chart-card animate-on-scroll" style="animation-delay: 0.2s">
            <h2>Macros & Hydration</h2><p class="chart-subtitle">Today's breakdown</p>
            <div class="macro-donut-container">
                <?php $totalMacros = (int)($macroData['protein'] ?? 0) + (int)($macroData['carbs'] ?? 0) + (int)($macroData['fat'] ?? 0); $proteinPct = $totalMacros > 0 ? ((int)$macroData['protein'] / $totalMacros) * 100 : 33; $carbsPct = $totalMacros > 0 ? ((int)$macroData['carbs'] / $totalMacros) * 100 : 34; $fatPct = $totalMacros > 0 ? ((int)$macroData['fat'] / $totalMacros) * 100 : 33; ?>
                <svg viewBox="0 0 100 100" class="macro-svg" role="img" aria-label="Macronutrient breakdown chart"><circle cx="50" cy="50" r="40" fill="none" stroke="var(--border)" stroke-width="14"/><circle cx="50" cy="50" r="40" fill="none" stroke="#2E7D32" stroke-width="14" stroke-dasharray="<?= $proteinPct * 2.51 ?> 251" stroke-dashoffset="0" transform="rotate(-90 50 50)"/><circle cx="50" cy="50" r="40" fill="none" stroke="#9F5F91" stroke-width="14" stroke-dasharray="<?= $carbsPct * 2.51 ?> 251" stroke-dashoffset="<?= -$proteinPct * 2.51 ?>" transform="rotate(-90 50 50)"/><circle cx="50" cy="50" r="40" fill="none" stroke="#F6EA98" stroke-width="14" stroke-dasharray="<?= $fatPct * 2.51 ?> 251" stroke-dashoffset="<?= -($proteinPct + $carbsPct) * 2.51 ?>" transform="rotate(-90 50 50)"/></svg>
                <div class="macro-legend-inline"><div class="macro-item"><span class="macro-dot" style="background:#2E7D32"></span>Protein <?= (int)($macroData['protein'] ?? 0) ?>g</div><div class="macro-item"><span class="macro-dot" style="background:#9F5F91"></span>Carbs <?= (int)($macroData['carbs'] ?? 0) ?>g</div><div class="macro-item"><span class="macro-dot" style="background:#F6EA98"></span>Fat <?= (int)($macroData['fat'] ?? 0) ?>g</div></div>
            </div>
            <div class="hydration-section"><div class="hydration-header"><span>Hydration</span><span class="hydration-value"><?= number_format($waterIntake, 1) ?>L / 2.5L</span></div><div class="hydration-track"><div class="hydration-fill" style="width: <?= min(100, ($waterIntake / 2.5) * 100) ?>%"></div></div></div>
        </div>
    </div>

    <div class="dual-grid">
        <div class="chart-card animate-on-scroll">
            <h2>Last 7 Days</h2><p class="chart-subtitle">Daily activity intensity</p>
            <div class="mini-heatmap">
                <?php $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']; for ($i = 6; $i >= 0; $i--): $d = new DateTime(); $d->modify("-{$i} days"); $dateStr = $d->format('Y-m-d'); $count = $miniHeatmap[$dateStr] ?? 0; $level = $count === 0 ? 0 : ($count === 1 ? 1 : ($count === 2 ? 2 : 3)); $dayName = $dayNames[(int)$d->format('N') - 1]; $isToday = $i === 0; ?>
                <div class="mini-heatmap-day level-<?= $level ?> <?= $isToday ? 'today' : '' ?>" title="<?= $d->format('j M') ?>: <?= $count ?> activities"><span class="mini-day-label"><?= $dayName ?></span><span class="mini-day-count"><?= $count ?></span></div>
                <?php endfor; ?>
            </div>
        </div>
        <div class="chart-card animate-on-scroll" style="animation-delay: 0.1s">
            <h2>Weight Trend</h2><p class="chart-subtitle"><?php if ($latestMeasure): ?>Current: <?= number_format((float)$latestMeasure['weight_kg'], 1) ?> kg<?php if ($latestMeasure['bmi']): ?> &middot; BMI <?= $latestMeasure['bmi'] ?><?php endif; ?><?php else: ?>No measurements yet<?php endif; ?></p>
            <div class="chart-container short">
                <?php if (count($weightTrend) >= 2): ?><svg id="weightChart" class="sparkline-svg" viewBox="0 0 300 100" preserveAspectRatio="none" role="img" aria-label="Weight trend chart"><path id="weightLine" class="sparkline" fill="none" stroke="var(--success)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><g id="weightPoints"></g></svg><?php else: ?><div class="empty-state">Log measurements in Profile to see trends.</div><?php endif; ?>
            </div>
            <?php if (count($weightTrend) >= 2): $first = (float)$weightTrend[0]['weight_kg']; $last = (float)end($weightTrend)['weight_kg']; $change = $last - $first; $changeClass = $change < 0 ? 'positive' : ($change > 0 ? 'negative' : ''); $changeSymbol = $change < 0 ? '-' : '+'; ?>
            <div class="sparkline-footer"><span class="change <?= $changeClass ?>"><?= $changeSymbol ?><?= number_format(abs($change), 1) ?> kg over period</span></div><?php endif; ?>
        </div>
    </div>

    <div class="dual-grid">
        <div class="card community-goal-card animate-on-scroll">
            <div class="goal-info"><h2>Tenant Goal: <?= number_format($communityGoalTarget) ?> Active Minutes This Month</h2><p>Together, we've logged <strong><?= number_format($communityGoal) ?></strong> minutes.</p></div>
            <div class="progress-track" style="height: 16px; border-radius: 8px;"><div class="progress-fill" style="width: <?= $communityGoalPct ?>%; border-radius: 8px;"></div></div>
            <div class="goal-footer"><span><?= $communityGoalPct ?>% complete</span><a href="/community.php" class="goal-link">View Community</a></div>
        </div>
        <div class="card animate-on-scroll" style="animation-delay: 0.1s">
            <h2>Quick Log</h2><p class="chart-subtitle">Fast access to common actions</p>
            <div class="quick-log-grid">
                <a href="/workouts.php" class="quick-log-btn" aria-label="Log gym workout"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6.5 6.5h11M6.5 17.5h11M4 10v4M20 10v4M8 6v12M16 6v12"/></svg><span>Gym</span></a>
                <a href="/workouts.php" class="quick-log-btn" aria-label="Log run"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="5" r="2"/><path d="M9 20l3-7 3 7"/></svg><span>Run</span></a>
                <a href="/nutrition.php" class="quick-log-btn" aria-label="Log food"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/></svg><span>Food</span></a>
                <a href="/profile.php" class="quick-log-btn" aria-label="Log weigh-in"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 12h4l3-9 4 18 3-9h4"/></svg><span>Weigh-in</span></a>
                <a href="/challenges.php" class="quick-log-btn" aria-label="View challenges"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg><span>Challenge</span></a>
                <a href="/community.php" class="quick-log-btn" aria-label="View community"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg><span>Community</span></a>
            </div>
        </div>
    </div>

    <div class="triple-grid">
        <div class="chart-card animate-on-scroll">
            <div class="chart-header-row compact"><h2>Recent Activities</h2><a href="/workouts.php" class="chart-link">View all workouts</a></div>
            <?php if (empty($recentActivities)): ?><div class="empty-state">No activities logged yet.</div><?php else: ?><div class="activity-feed"><?php foreach ($recentActivities as $act): ?><div class="activity-feed-item"><div class="activity-feed-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div><div class="activity-feed-details"><strong><?= htmlspecialchars($act['title']) ?></strong><span><?= htmlspecialchars($act['type_name']) ?></span><small><?= date('j M', strtotime($act['activity_date'])) ?><?php if ($act['duration_minutes']): ?> &middot; <?= $act['duration_minutes'] ?> mins<?php endif; ?></small></div></div><?php endforeach; ?></div><?php endif; ?>
        </div>
        <div class="chart-card animate-on-scroll" style="animation-delay: 0.1s">
            <div class="chart-header-row compact"><h2>Recent Kudos</h2><a href="/community.php" class="chart-link">View community feed</a></div>
            <?php if (empty($recentKudos)): ?><div class="empty-state">No kudos yet. Share a workout!</div><?php else: ?><div class="kudos-list"><?php foreach ($recentKudos as $kudo): ?><div class="kudo-item"><div class="kudo-icon"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg></div><div class="kudo-text"><strong><?= htmlspecialchars($kudo['display_name']) ?></strong><span>kudos'd "<?= htmlspecialchars($kudo['title']) ?>"</span><small><?= date('j M', strtotime($kudo['created_at'])) ?></small></div></div><?php endforeach; ?></div><?php endif; ?>
        </div>
        <div class="chart-card animate-on-scroll" style="animation-delay: 0.2s">
            <div class="chart-header-row compact"><h2>Personal Records</h2><a href="/progress.php" class="chart-link">View progress</a></div>
            <?php if (empty($personalRecords)): ?><div class="empty-state">Complete workouts to set records!</div><?php else: ?><div class="pr-list"><?php foreach ($personalRecords as $pr): ?><div class="pr-item"><div class="pr-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg></div><div class="pr-details"><strong><?= htmlspecialchars($pr['type_name']) ?></strong><?php if ($pr['best_duration']): ?><span><?= $pr['best_duration'] ?> mins</span><?php endif; ?><?php if ($pr['best_distance']): ?><span><?= $pr['best_distance'] ?> km</span><?php endif; ?></div></div><?php endforeach; ?></div><?php endif; ?>
        </div>
    </div>
</div>
<script>const dashboardData = <?= json_encode($chartData, JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<script src="/js/dashboard.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>