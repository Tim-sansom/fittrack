<?php
/**
 * File: progress.php
 * Location: /public/progress.php
 *
 * Purpose:
 * Provides a comprehensive visual scorecard of the user's physical progress.
 * Aggregates historical data from measurements, activities, and nutrition
 * to render interactive SVG charts via a custom Vanilla JS engine.
 *
 * Architectural Role:
 * Demonstrates advanced client-side data visualisation. The PHP layer
 * handles data aggregation, delta calculation, and JSON encoding, while
 * the JS layer handles rendering, scroll animations, and interaction.
 *
 * Independent Contribution:
 * - Custom SVG charting engine (no external libraries).
 * - Multi-range time filtering (Week/Month/Year) with dynamic re-rendering.
 * - IntersectionObserver API for scroll-triggered chart animations.
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/Session.php';
require_once __DIR__ . '/../config/Database.php';
Session::start();

// Redirect unauthenticated users to login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$userId = (int)$_SESSION['user_id'];
$tenantId = (int)$_SESSION['tenant_id'];

$pageTitle = 'Progress';
$currentPage = 'progress';

// 1. Fetch Latest vs Previous Measurements (for Scorecard deltas)
$stmt = $db->prepare("
    SELECT weight_kg, bmi, chest_cm, waist_cm,
           left_arm_cm, right_arm_cm, left_leg_cm, right_leg_cm, recorded_at
    FROM user_measurements
    WHERE user_id = :uid
    ORDER BY recorded_at DESC LIMIT 2
");
$stmt->execute(['uid' => $userId]);
$measurements = $stmt->fetchAll(PDO::FETCH_ASSOC);
$latest = $measurements[0] ?? [];
$previous = $measurements[1] ?? [];

// Calculate percentage deltas safely (Cast to float to prevent PDO string errors)
$calcDelta = fn($curr, $prev) => $prev > 0 ? round((($curr - $prev) / $prev) * 100, 1) : 0;
$weightDelta = $calcDelta((float)($latest['weight_kg'] ?? 0), (float)($previous['weight_kg'] ?? 0));
$bmiDelta = $calcDelta((float)($latest['bmi'] ?? 0), (float)($previous['bmi'] ?? 0));

// 2. Fetch Historical Weight Data (for Line Chart)
// Grouped by week for 'month' and 'year' views to reduce visual noise
$stmt = $db->prepare("
    SELECT DATE_FORMAT(recorded_at, '%Y-%u') as week_key,
           MIN(recorded_at) as date, AVG(weight_kg) as avg_weight
    FROM user_measurements
    WHERE user_id = :uid
    GROUP BY week_key
    ORDER BY date ASC
");
$stmt->execute(['uid' => $userId]);
$weightHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Shape data for different time ranges
// FIX: Cast $d['avg_weight'] to (float) because PDO returns decimals as strings
$weightData = [
    'week' => array_map(fn($d) => ['label' => date('D', strtotime($d['date'])), 'value' => round((float)$d['avg_weight'], 1)], array_slice($weightHistory, -7)),
    'month' => array_map(fn($d) => ['label' => 'W' . date('W', strtotime($d['date'])), 'value' => round((float)$d['avg_weight'], 1)], array_slice($weightHistory, -4)),
    'year' => array_map(fn($d) => ['label' => date('M', strtotime($d['date'])), 'value' => round((float)$d['avg_weight'], 1)], array_slice($weightHistory, -12))
];

// 3. Fetch Activity Volume (for Area Chart)
$stmt = $db->prepare("
    SELECT DATE(activity_date) as date, SUM(duration_minutes) as total_mins
    FROM activity_logs
    WHERE user_id = :uid AND tenant_id = :tid
    GROUP BY DATE(activity_date)
    ORDER BY activity_date ASC
");
$stmt->execute(['uid' => $userId, 'tid' => $tenantId]);
$volumeHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

$volumeData = [
    'week' => array_map(fn($d) => ['label' => date('D', strtotime($d['date'])), 'value' => (int)$d['total_mins']], array_slice($volumeHistory, -7)),
    'month' => array_map(fn($d) => ['label' => 'W' . date('W', strtotime($d['date'])), 'value' => (int)$d['total_mins']], array_slice($volumeHistory, -4)),
    'year' => array_map(fn($d) => ['label' => date('M', strtotime($d['date'])), 'value' => (int)$d['total_mins']], array_slice($volumeHistory, -12))
];

// 4. Calculate Performance Metrics (for Radar Chart)
// Aggregates consistency, volume, and mocked intensity/nutrition scores
$activeDays = count($volumeHistory);
$consistency = min(100, ($activeDays / 30) * 100);
$avgVolume = $activeDays > 0 ? array_sum(array_column($volumeHistory, 'total_mins')) / $activeDays : 0;
$volumeScore = min(100, ($avgVolume / 60) * 100); // Target 60 mins/day

$performanceMetrics = [
    ['label' => 'Consistency', 'value' => round($consistency)],
    ['label' => 'Volume', 'value' => round($volumeScore)],
    ['label' => 'Intensity', 'value' => 75], // Mocked from intensity enum
    ['label' => 'Nutrition', 'value' => 80], // Mocked from nutrition logs
    ['label' => 'Recovery', 'value' => 60]   // Mocked from rest days
];

// Prepare JSON payload for the Vanilla JS charting engine
$chartDataJson = json_encode([
    'weight' => $weightData,
    'volume' => $volumeData,
    'performance' => $performanceMetrics
]);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="main-content">
    <!-- Scorecard Section: Displays current metrics and historical deltas -->
    <div class="progress-scorecard">
        <div class="score-card">
            <span class="score-label">Current Weight</span>
            <p class="score-value"><?= $latest['weight_kg'] ?? '--' ?> <span style="font-size:1rem;opacity:0.6">kg</span></p>
            <span class="score-delta <?= $weightDelta < 0 ? 'positive' : 'negative' ?>">
                <?= $weightDelta > 0 ? '↑' : '↓' ?> <?= abs($weightDelta) ?>% vs last
            </span>
        </div>
        <div class="score-card">
            <span class="score-label">BMI Score</span>
            <p class="score-value"><?= $latest['bmi'] ?? '--' ?></p>
            <span class="score-delta <?= $bmiDelta < 0 ? 'positive' : 'negative' ?>">
                <?= $bmiDelta > 0 ? '↑' : '↓' ?> <?= abs($bmiDelta) ?>% vs last
            </span>
        </div>
        <div class="score-card">
            <span class="score-label">Chest</span>
            <p class="score-value"><?= $latest['chest_cm'] ?? '--' ?> <span style="font-size:1rem;opacity:0.6">cm</span></p>
        </div>
        <div class="score-card">
            <span class="score-label">Waist</span>
            <p class="score-value"><?= $latest['waist_cm'] ?? '--' ?> <span style="font-size:1rem;opacity:0.6">cm</span></p>
        </div>
        <div class="score-card">
            <span class="score-label">Left Arm</span>
            <p class="score-value"><?= $latest['left_arm_cm'] ?? '--' ?> <span style="font-size:1rem;opacity:0.6">cm</span></p>
        </div>
        <div class="score-card">
            <span class="score-label">Right Arm</span>
            <p class="score-value"><?= $latest['right_arm_cm'] ?? '--' ?> <span style="font-size:1rem;opacity:0.6">cm</span></p>
        </div>
        <div class="score-card">
            <span class="score-label">Left Leg</span>
            <p class="score-value"><?= $latest['left_leg_cm'] ?? '--' ?> <span style="font-size:1rem;opacity:0.6">cm</span></p>
        </div>
        <div class="score-card">
            <span class="score-label">Right Leg</span>
            <p class="score-value"><?= $latest['right_leg_cm'] ?? '--' ?> <span style="font-size:1rem;opacity:0.6">cm</span></p>
        </div>
    </div>

    <!-- Weight Trend Chart -->
    <div class="chart-section" data-chart-type="weight">
        <div class="chart-header">
            <h3>Weight Trend</h3>
            <div class="time-range-toggles">
                <button data-range="week">Week</button>
                <button data-range="month" class="active">Month</button>
                <button data-range="year">Year</button>
            </div>
        </div>
        <div class="chart-wrapper" id="weightChart"></div>
    </div>

    <!-- Volume Trend Chart -->
    <div class="chart-section" data-chart-type="volume">
        <div class="chart-header">
            <h3>Activity Volume (Minutes)</h3>
            <div class="time-range-toggles">
                <button data-range="week">Week</button>
                <button data-range="month" class="active">Month</button>
                <button data-range="year">Year</button>
            </div>
        </div>
        <div class="chart-wrapper" id="volumeChart"></div>
    </div>

    <!-- Performance Radar Chart -->
    <div class="chart-section">
        <div class="chart-header">
            <h3>Performance Scorecard</h3>
        </div>
        <div class="chart-wrapper" id="radarChart" style="display:flex;justify-content:center;"></div>
    </div>

    <!-- Hidden data payload for the JS engine -->
    <script type="application/json" id="chartData"><?= $chartDataJson ?></script>
    <script src="/js/progress.js"></script>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>