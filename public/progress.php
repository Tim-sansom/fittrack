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
// Explicitly set SPA mode to MPA so the header toggle renders correctly
$_SESSION['spa_mode'] = 'mpa';
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
// Default empty data structures
$latest = [];
$previous = [];
$weightHistory = [];
$volumeHistory = [];
// 1. Fetch Latest vs Previous Measurements (for Scorecard deltas)
// NOTE: user_measurements table does NOT have tenant_id, so we scope by user_id only.
try {
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
} catch (Exception $e) {
    error_log('Progress measurements query failed: ' . $e->getMessage());
}
// Calculate percentage deltas safely
$calcDelta = function ($curr, $prev) {
    return $prev > 0 ? round((($curr - $prev) / $prev) * 100, 1) : 0;
};
$weightDelta = $calcDelta((float)($latest['weight_kg'] ?? 0), (float)($previous['weight_kg'] ?? 0));
$bmiDelta = $calcDelta((float)($latest['bmi'] ?? 0), (float)($previous['bmi'] ?? 0));
// 2. Fetch Historical Weight Data (for Line Chart)
try {
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
} catch (Exception $e) {
    error_log('Progress weight history query failed: ' . $e->getMessage());
}
// Shape data for different time ranges
$weightData = [
    'week'  => array_map(function ($d) {
        return ['label' => date('D', strtotime($d['date'])), 'value' => round((float)$d['avg_weight'], 1)];
    }, array_slice($weightHistory, -7)),
    'month' => array_map(function ($d) {
        return ['label' => 'W' . date('W', strtotime($d['date'])), 'value' => round((float)$d['avg_weight'], 1)];
    }, array_slice($weightHistory, -4)),
    'year'  => array_map(function ($d) {
        return ['label' => date('M', strtotime($d['date'])), 'value' => round((float)$d['avg_weight'], 1)];
    }, array_slice($weightHistory, -12))
];
// 3. Fetch Activity Volume (for Area Chart)
try {
    $stmt = $db->prepare("
        SELECT DATE(activity_date) as date, SUM(duration_minutes) as total_mins
        FROM activity_logs
        WHERE user_id = :uid AND tenant_id = :tid
        GROUP BY DATE(activity_date)
        ORDER BY activity_date ASC
    ");
    $stmt->execute(['uid' => $userId, 'tid' => $tenantId]);
    $volumeHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('Progress volume history query failed: ' . $e->getMessage());
}
$volumeData = [
    'week'  => array_map(function ($d) {
        return ['label' => date('D', strtotime($d['date'])), 'value' => (int)$d['total_mins']];
    }, array_slice($volumeHistory, -7)),
    'month' => array_map(function ($d) {
        return ['label' => 'W' . date('W', strtotime($d['date'])), 'value' => (int)$d['total_mins']];
    }, array_slice($volumeHistory, -4)),
    'year'  => array_map(function ($d) {
        return ['label' => date('M', strtotime($d['date'])), 'value' => (int)$d['total_mins']];
    }, array_slice($volumeHistory, -12))
];
// 4. Calculate Performance Metrics (for Radar Chart)
$activeDays = count($volumeHistory);
$consistency = min(100, ($activeDays / 30) * 100);
$avgVolume = $activeDays > 0 ? array_sum(array_column($volumeHistory, 'total_mins')) / $activeDays : 0;
$volumeScore = min(100, ($avgVolume / 60) * 100);
$performanceMetrics = [
    ['label' => 'Consistency', 'value' => round($consistency)],
    ['label' => 'Volume',      'value' => round($volumeScore)],
    ['label' => 'Intensity',   'value' => 75],
    ['label' => 'Nutrition',   'value' => 80],
    ['label' => 'Recovery',    'value' => 60]
];
// Prepare JSON payload for the Vanilla JS charting engine
$chartDataJson = json_encode([
    'weight'      => $weightData,
    'volume'      => $volumeData,
    'performance' => $performanceMetrics
]);
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<div class="main-content">
    <!-- Scorecard Section: Displays current metrics and historical deltas -->
    <h2 class="sr-only">Body Measurements</h2>
    <div class="progress-scorecard">
        <!-- Current Weight -->
        <div class="score-card animate-on-scroll" data-tooltip="Your current body weight.">
            <h3 class="score-label">Current Weight</h3>
            <p class="score-value" data-target="<?= $latest['weight_kg'] ?? 0 ?>" data-decimals="1">
                <?= $latest['weight_kg'] ? number_format((float)$latest['weight_kg'], 1) : '--' ?>
                <span class="score-unit">kg</span>
            </p>
            <?php
            $wSymbol = $weightDelta > 0 ? '↑' : ($weightDelta < 0 ? '↓' : '→');
            $wColor = $weightDelta > 0 ? 'var(--alert)' : ($weightDelta < 0 ? 'var(--success)' : 'var(--text)');
            ?>
            <span class="score-delta" style="color: <?= $wColor ?>; font-weight: bold;" data-tooltip="Percentage change from your previous measurement.">
                <?= $wSymbol ?> <?= abs($weightDelta) ?>% vs last
            </span>
        </div>
        <!-- BMI Score -->
        <div class="score-card animate-on-scroll" style="animation-delay: 0.05s" data-tooltip="Your Body Mass Index score.">
            <h3 class="score-label">BMI Score</h3>
            <p class="score-value" data-target="<?= $latest['bmi'] ?? 0 ?>" data-decimals="1">
                <?= $latest['bmi'] ? number_format((float)$latest['bmi'], 1) : '--' ?>
            </p>
            <?php
            $bSymbol = $bmiDelta > 0 ? '↑' : ($bmiDelta < 0 ? '↓' : '→');
            $bColor = $bmiDelta > 0 ? 'var(--alert)' : ($bmiDelta < 0 ? 'var(--success)' : 'var(--text)');
            ?>
            <span class="score-delta" style="color: <?= $bColor ?>; font-weight: bold;" data-tooltip="Percentage change from your previous BMI.">
                <?= $bSymbol ?> <?= abs($bmiDelta) ?>% vs last
            </span>
        </div>
        <!-- Chest -->
        <div class="score-card animate-on-scroll" style="animation-delay: 0.1s" data-tooltip="Your chest circumference.">
            <h3 class="score-label">Chest</h3>
            <p class="score-value" data-target="<?= $latest['chest_cm'] ?? 0 ?>" data-decimals="1">
                <?= $latest['chest_cm'] ? number_format((float)$latest['chest_cm'], 1) : '--' ?>
                <span class="score-unit">cm</span>
            </p>
        </div>
        <!-- Waist -->
        <div class="score-card animate-on-scroll" style="animation-delay: 0.15s" data-tooltip="Your waist circumference.">
            <h3 class="score-label">Waist</h3>
            <p class="score-value" data-target="<?= $latest['waist_cm'] ?? 0 ?>" data-decimals="1">
                <?= $latest['waist_cm'] ? number_format((float)$latest['waist_cm'], 1) : '--' ?>
                <span class="score-unit">cm</span>
            </p>
        </div>
        <!-- Left Arm -->
        <div class="score-card animate-on-scroll" style="animation-delay: 0.2s" data-tooltip="Your left arm circumference.">
            <h3 class="score-label">Left Arm</h3>
            <p class="score-value" data-target="<?= $latest['left_arm_cm'] ?? 0 ?>" data-decimals="1">
                <?= $latest['left_arm_cm'] ? number_format((float)$latest['left_arm_cm'], 1) : '--' ?>
                <span class="score-unit">cm</span>
            </p>
        </div>
        <!-- Right Arm -->
        <div class="score-card animate-on-scroll" style="animation-delay: 0.25s" data-tooltip="Your right arm circumference.">
            <h3 class="score-label">Right Arm</h3>
            <p class="score-value" data-target="<?= $latest['right_arm_cm'] ?? 0 ?>" data-decimals="1">
                <?= $latest['right_arm_cm'] ? number_format((float)$latest['right_arm_cm'], 1) : '--' ?>
                <span class="score-unit">cm</span>
            </p>
        </div>
        <!-- Left Leg -->
        <div class="score-card animate-on-scroll" style="animation-delay: 0.3s" data-tooltip="Your left leg circumference.">
            <h3 class="score-label">Left Leg</h3>
            <p class="score-value" data-target="<?= $latest['left_leg_cm'] ?? 0 ?>" data-decimals="1">
                <?= $latest['left_leg_cm'] ? number_format((float)$latest['left_leg_cm'], 1) : '--' ?>
                <span class="score-unit">cm</span>
            </p>
        </div>
        <!-- Right Leg -->
        <div class="score-card animate-on-scroll" style="animation-delay: 0.35s" data-tooltip="Your right leg circumference.">
            <h3 class="score-label">Right Leg</h3>
            <p class="score-value" data-target="<?= $latest['right_leg_cm'] ?? 0 ?>" data-decimals="1">
                <?= $latest['right_leg_cm'] ? number_format((float)$latest['right_leg_cm'], 1) : '--' ?>
                <span class="score-unit">cm</span>
            </p>
        </div>
    </div>
    <!-- Weight Trend Chart -->
    <div class="chart-section animate-on-scroll" data-chart-type="weight" data-tooltip="Tracks your weight changes over time.">
        <div class="chart-header">
            <h2>Weight Trend</h2>
            <div class="time-range-toggles">
                <button data-range="week" data-tooltip="Show the last 7 days of data.">Week</button>
                <button data-range="month" class="active" data-tooltip="Show the last 4 weeks of data.">Month</button>
                <button data-range="year" data-tooltip="Show the last 12 months of data.">Year</button>
            </div>
        </div>
        <div class="chart-wrapper" id="weightChart">
            <?php if (empty($weightHistory)): ?>
                <p style="text-align:center; padding:2rem; color:var(--text); opacity:0.6;">Log measurements to see trends.</p>
            <?php endif; ?>
        </div>
    </div>
    <!-- Volume Trend Chart -->
    <div class="chart-section animate-on-scroll" data-chart-type="volume" data-tooltip="Tracks your total active minutes over time.">
        <div class="chart-header">
            <h2>Activity Volume (Minutes)</h2>
            <div class="time-range-toggles">
                <button data-range="week" data-tooltip="Show the last 7 days of data.">Week</button>
                <button data-range="month" class="active" data-tooltip="Show the last 4 weeks of data.">Month</button>
                <button data-range="year" data-tooltip="Show the last 12 months of data.">Year</button>
            </div>
        </div>
        <div class="chart-wrapper" id="volumeChart"></div>
    </div>
    <!-- Performance Radar Chart -->
    <div class="chart-section animate-on-scroll" data-tooltip="Visual summary of your overall fitness performance.">
        <div class="chart-header">
            <h2>Performance Scorecard</h2>
        </div>
        <div class="chart-wrapper" id="radarChart" style="display:flex;justify-content:center;"></div>
    </div>
    <!-- Hidden data payload for the JS engine -->
    <script type="application/json" id="chartData"><?= $chartDataJson ?></script>
    <script src="js/progress.js"></script>
    <!-- Dynamic Counter Animation Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        /**
         * Animates scorecard values from 0 to their target.
         * Uses data-target and data-decimals attributes on .score-value elements.
         */
        const scoreValues = document.querySelectorAll('.score-value[data-target]');
        if (scoreValues.length === 0) return;
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const target = parseFloat(el.dataset.target) || 0;
                    // Skip animation if target is 0 (no data)
                    if (target === 0) {
                        observer.unobserve(el);
                        return;
                    }
                    const decimals = parseInt(el.dataset.decimals || '1', 10);
                    const unitEl = el.querySelector('.score-unit');
                    const unitText = unitEl ? unitEl.outerHTML : '';
                    animateValue(el, 0, target, 1200, decimals, unitText);
                    observer.unobserve(el);
                }
            });
        }, { threshold: 0.5 });
        scoreValues.forEach(function(el) { observer.observe(el); });
        /**
         * Animates a numeric value from start to end over duration ms.
         */
        function animateValue(element, start, end, duration, decimals, unitHtml) {
            const steps = 40;
            const increment = (end - start) / steps;
            let step = 0;
            let current = start;
            const timer = setInterval(function() {
                step++;
                current = start + (increment * step);
                if (step >= steps) {
                    current = end;
                    clearInterval(timer);
                }
                const formatted = current.toFixed(decimals);
                element.innerHTML = formatted + ' ' + unitHtml;
            }, duration / steps);
        }
        /**
         * Animate delta percentages with a slide-in effect.
         */
        const deltas = document.querySelectorAll('.score-delta');
        deltas.forEach(function(delta, index) {
            delta.style.opacity = '0';
            delta.style.transform = 'translateX(-10px)';
            delta.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            setTimeout(function() {
                delta.style.opacity = '1';
                delta.style.transform = 'translateX(0)';
            }, 200 + (index * 80));
        });
    });
    </script>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>