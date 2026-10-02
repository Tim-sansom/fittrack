<?php
/**
 * File: workouts.php
 * Location: /public/workouts.php
 * 
 * Purpose:
 * Provides a detailed interface for logging specific workout types with
 * time-range analytics, progress tracking, and interactive tooltips.
 * 
 * Architectural Role:
 * Demonstrates MPA architecture with SPA-like UX through dynamic chart
 * switching, time range filtering, and hover-based data exploration.
 * 
 * Independent Contribution:
 * - Multi-range analytics (week/month/year) with varied visualizations
 * - Progress trend analysis showing workout improvement over time
 * - Interactive tooltips for data exploration without cluttering the UI
 * - Evidence-based 3-day split program with gender-aware variants
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

$pageTitle = 'Workouts';
$currentPage = 'workouts';

// Fetch user gender for exercise library
$stmt = $db->prepare("SELECT gender_identity FROM user_profiles WHERE user_id = :id");
$stmt->execute(['id' => $userId]);
$userProfile = $stmt->fetch(PDO::FETCH_ASSOC);
$userGender = $userProfile['gender_identity'] ?? 'prefer_not_to_say';

// Fetch weekly workout frequency
$stmt = $db->prepare("
    SELECT DAYNAME(activity_date) as day_name, COUNT(*) as count, SUM(duration_minutes) as total_mins
    FROM activity_logs
    WHERE user_id = :uid AND tenant_id = :tid
    AND activity_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DAYNAME(activity_date)
    ORDER BY FIELD(day_name, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')
");
$stmt->execute(['uid' => $userId, 'tid' => $tenantId]);
$weeklyData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build complete week array
$weekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
$weekMap = [];
foreach ($weeklyData as $row) {
    $shortDay = substr($row['day_name'], 0, 3);
    $weekMap[$shortDay] = [
        'count' => (int)$row['count'],
        'minutes' => (int)($row['total_mins'] ?? 0)
    ];
}
$weekChartData = [];
foreach ($weekDays as $day) {
    $weekChartData[] = [
        'label' => $day,
        'count' => $weekMap[$day]['count'] ?? 0,
        'minutes' => $weekMap[$day]['minutes'] ?? 0
    ];
}

// Fetch monthly data (4 weeks)
$stmt = $db->prepare("
    SELECT YEARWEEK(activity_date, 1) as week_num, 
           COUNT(*) as count,
           SUM(duration_minutes) as total_mins
    FROM activity_logs
    WHERE user_id = :uid AND tenant_id = :tid
    AND activity_date >= DATE_SUB(CURDATE(), INTERVAL 4 WEEK)
    GROUP BY YEARWEEK(activity_date, 1)
    ORDER BY week_num ASC
");
$stmt->execute(['uid' => $userId, 'tid' => $tenantId]);
$monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);

$monthChartData = [];
foreach ($monthlyData as $i => $week) {
    $monthChartData[] = [
        'label' => 'W' . ($i + 1),
        'count' => (int)$week['count'],
        'minutes' => (int)$week['total_mins']
    ];
}

// Fetch yearly data (12 months)
$stmt = $db->prepare("
    SELECT DATE_FORMAT(activity_date, '%b') as month_name,
           COUNT(*) as count,
           SUM(duration_minutes) as total_mins
    FROM activity_logs
    WHERE user_id = :uid AND tenant_id = :tid
    AND activity_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(activity_date, '%Y-%m')
    ORDER BY activity_date ASC
");
$stmt->execute(['uid' => $userId, 'tid' => $tenantId]);
$yearlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);

$yearChartData = [];
foreach ($yearlyData as $month) {
    $yearChartData[] = [
        'label' => $month['month_name'],
        'count' => (int)$month['count'],
        'minutes' => (int)$month['total_mins']
    ];
}

// Calculate progress trend (compare last 4 weeks to previous 4 weeks)
$stmt = $db->prepare("
    SELECT 
        SUM(CASE WHEN activity_date >= DATE_SUB(CURDATE(), INTERVAL 4 WEEK) THEN duration_minutes ELSE 0 END) as recent_mins,
        SUM(CASE WHEN activity_date >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK) 
                 AND activity_date < DATE_SUB(CURDATE(), INTERVAL 4 WEEK) THEN duration_minutes ELSE 0 END) as previous_mins
    FROM activity_logs
    WHERE user_id = :uid AND tenant_id = :tid
    AND activity_date >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK)
");
$stmt->execute(['uid' => $userId, 'tid' => $tenantId]);
$progressData = $stmt->fetch(PDO::FETCH_ASSOC);

$recentMins = (int)($progressData['recent_mins'] ?? 0);
$previousMins = (int)($progressData['previous_mins'] ?? 0);
$progressPercent = $previousMins > 0 ? round((($recentMins - $previousMins) / $previousMins) * 100, 1) : 0;
$progressDirection = $progressPercent >= 0 ? 'positive' : 'negative';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="main-content">
    <!-- Progress Charts with Time Range Controls -->
    <div class="progress-charts-row">
        <div class="chart-card">
            <h3>Workout Frequency</h3>
            <div class="chart-controls">
                <button class="active" onclick="switchChart('frequency', 'week')">Week</button>
                <button onclick="switchChart('frequency', 'month')">Month</button>
                <button onclick="switchChart('frequency', 'year')">Year</button>
            </div>
            <div class="chart-wrapper" id="frequencyChart">
                <!-- Rendered by JavaScript -->
            </div>
            <div class="chart-tooltip" id="frequencyTooltip"></div>
        </div>
        
        <div class="chart-card">
            <h3>Volume Trend (minutes)</h3>
            <div class="chart-controls">
                <button class="active" onclick="switchChart('volume', 'week')">Week</button>
                <button onclick="switchChart('volume', 'month')">Month</button>
                <button onclick="switchChart('volume', 'year')">Year</button>
            </div>
            <div class="chart-wrapper" id="volumeChart">
                <!-- Rendered by JavaScript -->
            </div>
            <div class="chart-tooltip" id="volumeTooltip"></div>
            
            <div class="progress-indicator <?= $progressDirection ?>">
                <?php if ($progressPercent > 0): ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"></polyline></svg>
                    <span>+<?= abs($progressPercent) ?>% vs previous period</span>
                <?php elseif ($progressPercent < 0): ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    <span><?= $progressPercent ?>% vs previous period</span>
                <?php else: ?>
                    <span>No change from previous period</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Workout Logger -->
    <div class="card">
        <h2>Log a New Workout</h2>
        <p style="color: var(--text); opacity: 0.7; margin-bottom: 1.5rem;">Select your activity type to begin.</p>
        
        <div class="workout-type-grid">
            <div class="workout-type-card" data-type="gym" onclick="selectWorkoutType('gym')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6.5 6.5h11M6.5 17.5h11M4 10v4M20 10v4M8 6v12M16 6v12"/></svg>
                <h3>Gym / Resistance</h3>
            </div>
            <div class="workout-type-card" data-type="run" onclick="selectWorkoutType('run')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 4v16M17 4v16M19 4v16M9 4v16M5 4v16"/></svg>
                <h3>Running</h3>
            </div>
            <div class="workout-type-card" data-type="walk" onclick="selectWorkoutType('walk')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 4v8l4 8M9 4v8l-4 8M7 12h10"/></svg>
                <h3>Walking</h3>
            </div>
            <div class="workout-type-card" data-type="swim" onclick="selectWorkoutType('swim')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12c2-2 4-2 6 0s4 2 6 0 4-2 6 0M2 16c2-2 4-2 6 0s4 2 6 0 4-2 6 0"/></svg>
                <h3>Swimming</h3>
            </div>
            <div class="workout-type-card" data-type="sport" onclick="selectWorkoutType('sport')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20M2 12h20"/></svg>
                <h3>Sport / Football</h3>
            </div>
        </div>

        <form id="workoutForm">
            <!-- Gym Form -->
            <div id="form-gym" class="workout-form-section">
                <div class="gender-toggle">
                    <button type="button" class="active" onclick="setGender('male')">Male Program</button>
                    <button type="button" onclick="setGender('female')">Female Program</button>
                    <button type="button" onclick="setGender('neutral')">Neutral</button>
                </div>
                <div class="form-group">
                    <label>Session Title</label>
                    <input type="text" id="gymTitle" placeholder="e.g. Day 1 - Full Body">
                </div>
                <div class="form-group">
                    <label>Select Exercises (Lee's 3-Day Split)</label>
                    <div class="exercise-library" id="exerciseLibrary"></div>
                </div>
                <div class="workout-grid-2">
                    <div class="form-group">
                        <label>Duration (mins)</label>
                        <input type="number" id="gymDuration" name="duration_minutes">
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" id="gymDate" name="activity_date">
                    </div>
                </div>
                <button type="button" class="btn-primary" onclick="submitWorkout(4)">Log Gym Session</button>
            </div>

            <!-- Run Form -->
            <div id="form-run" class="workout-form-section">
                <div class="form-group">
                    <label>Run Title</label>
                    <input type="text" id="runTitle" placeholder="e.g. Morning 5k">
                </div>
                <div class="workout-grid-2">
                    <div class="form-group">
                        <label>Distance (km)</label>
                        <input type="number" step="0.1" id="runDistance" name="distance_km">
                    </div>
                    <div class="form-group">
                        <label>Duration (mins)</label>
                        <input type="number" id="runDuration" name="duration_minutes">
                    </div>
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" id="runDate" name="activity_date">
                </div>
                <button type="button" class="btn-primary" onclick="submitWorkout(1)">Log Run</button>
            </div>

            <!-- Walk Form -->
            <div id="form-walk" class="workout-form-section">
                <div class="form-group">
                    <label>Walk Title</label>
                    <input type="text" id="walkTitle" placeholder="e.g. Evening stroll">
                </div>
                <div class="workout-grid-2">
                    <div class="form-group">
                        <label>Distance (km)</label>
                        <input type="number" step="0.1" id="walkDistance" name="distance_km">
                    </div>
                    <div class="form-group">
                        <label>Duration (mins)</label>
                        <input type="number" id="walkDuration" name="duration_minutes">
                    </div>
                </div>
                <div class="form-group">
                    <label>Steps (optional)</label>
                    <input type="number" id="walkSteps" placeholder="e.g. 8000">
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" id="walkDate" name="activity_date">
                </div>
                <button type="button" class="btn-primary" onclick="submitWorkout(2)">Log Walk</button>
            </div>

            <!-- Swim Form -->
            <div id="form-swim" class="workout-form-section">
                <div class="form-group">
                    <label>Session Title</label>
                    <input type="text" id="swimTitle" placeholder="e.g. Pool Laps">
                </div>
                <div class="workout-grid-2">
                    <div class="form-group">
                        <label>Duration (mins)</label>
                        <input type="number" id="swimDuration" name="duration_minutes">
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" id="swimDate" name="activity_date">
                    </div>
                </div>
                <button type="button" class="btn-primary" onclick="submitWorkout(3)">Log Swim</button>
            </div>

            <!-- Sport Form -->
            <div id="form-sport" class="workout-form-section">
                <div class="form-group">
                    <label>Match/Session Title</label>
                    <input type="text" id="sportTitle" placeholder="e.g. Sunday League">
                </div>
                <div class="form-group">
                    <label>Duration (mins)</label>
                    <input type="number" id="sportDuration" name="duration_minutes">
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" id="sportDate" name="activity_date">
                </div>
                <button type="button" class="btn-primary" onclick="submitWorkout(5)">Log Sport</button>
            </div>
        </form>
    </div>

    <!-- Recent Workouts List -->
    <div class="card">
        <h2>Recent Workouts</h2>
        <div id="recentWorkoutsList">Loading...</div>
    </div>
</div>

<script>
/**
 * Workout Page Controller
 * Handles dynamic form switching, exercise library, time-range chart switching,
 * tooltip interactions, and asynchronous submission.
 */

// Lee's 3-Day Full Body Program
const exercisePrograms = {
    male: [
        { name: 'Barbell Squat', sets: 3, reps: '8-12', rest: '90s' },
        { name: 'Dumbbell Bench Press', sets: 3, reps: '8-12', rest: '90s' },
        { name: 'Lat Pulldown', sets: 3, reps: '8-12', rest: '90s' },
        { name: 'Romanian Deadlift', sets: 3, reps: '8-12', rest: '90s' },
        { name: 'Overhead Dumbbell Press', sets: 3, reps: '8-12', rest: '90s' },
        { name: 'Bodyweight Pull-up', sets: 3, reps: '5-8', rest: '90s' },
        { name: 'Standing Calf Raises', sets: 3, reps: '12-15', rest: '60s' },
        { name: 'Leg Press', sets: 3, reps: '8-12', rest: '90s' },
        { name: 'Push-ups', sets: 3, reps: '8-12', rest: '60s' },
        { name: 'Single-Arm Dumbbell Row', sets: 3, reps: '8-12/side', rest: '90s' },
        { name: 'Plank', sets: 3, reps: '30s hold', rest: '60s' }
    ],
    female: [
        { name: 'Goblet Squat', sets: 3, reps: '8-12', rest: '90s' },
        { name: 'Dumbbell Bench Press', sets: 3, reps: '8-12', rest: '90s' },
        { name: 'Seated Cable Row', sets: 3, reps: '8-12', rest: '90s' },
        { name: 'Romanian Deadlift (DB)', sets: 3, reps: '8-12', rest: '90s' },
        { name: 'Overhead Dumbbell Press', sets: 3, reps: '8-12', rest: '90s' },
        { name: 'Assisted Pull-up', sets: 3, reps: '5-8', rest: '90s' },
        { name: 'Standing Calf Raises', sets: 3, reps: '12-15', rest: '60s' },
        { name: 'Walking Lunges', sets: 3, reps: '8-12/leg', rest: '90s' },
        { name: 'Machine Chest Press', sets: 3, reps: '8-12', rest: '90s' },
        { name: 'Single-Arm Dumbbell Row', sets: 3, reps: '8-12/side', rest: '90s' },
        { name: 'Dead Bug', sets: 3, reps: '10/side', rest: '60s' }
    ],
    neutral: [
        { name: 'Goblet Squat', sets: 3, reps: '8-12', rest: '90s' },
        { name: 'Push-ups', sets: 3, reps: '8-12', rest: '60s' },
        { name: 'Seated Cable Row', sets: 3, reps: '8-12', rest: '90s' },
        { name: 'Romanian Deadlift (DB)', sets: 3, reps: '8-12', rest: '90s' },
        { name: 'Dumbbell Shoulder Press', sets: 3, reps: '8-12', rest: '90s' },
        { name: 'Bodyweight Lunge', sets: 3, reps: '8-12/leg', rest: '90s' },
        { name: 'Plank', sets: 3, reps: '30s hold', rest: '60s' },
        { name: 'Standing Calf Raises', sets: 3, reps: '12-15', rest: '60s' }
    ]
};

// Chart data from PHP
const chartData = {
    frequency: {
        week: <?= json_encode($weekChartData) ?>,
        month: <?= json_encode($monthChartData) ?>,
        year: <?= json_encode($yearChartData) ?>
    },
    volume: {
        week: <?= json_encode($weekChartData) ?>,
        month: <?= json_encode($monthChartData) ?>,
        year: <?= json_encode($yearChartData) ?>
    }
};

let selectedExercises = [];
let currentGender = '<?= htmlspecialchars($userGender) ?>';
let currentRange = { frequency: 'week', volume: 'week' };

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[type="date"]').forEach(input => {
        if (!input.value) input.valueAsDate = new Date();
    });
    
    populateExerciseLibrary(currentGender);
    loadRecentWorkouts();
    
    // Initial chart render
    renderFrequencyChart('week');
    renderVolumeChart('week');
});

function switchChart(chartType, range) {
    currentRange[chartType] = range;
    
    // Update active button state
    const controls = document.querySelectorAll(`#${chartType}Chart`).length > 0 
        ? document.querySelector(`#${chartType}Chart`).previousElementSibling
        : null;
    
    if (controls) {
        controls.querySelectorAll('button').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
    }
    
    if (chartType === 'frequency') {
        renderFrequencyChart(range);
    } else {
        renderVolumeChart(range);
    }
}

function renderFrequencyChart(range) {
    const container = document.getElementById('frequencyChart');
    const data = chartData.frequency[range];
    const maxCount = Math.max(...data.map(d => d.count), 1);
    
    // Week: Bar chart, Month: Bar chart, Year: Line chart
    if (range === 'year') {
        renderLineChart(container, data, 'count', 'frequencyTooltip');
    } else {
        renderBarChart(container, data, 'count', 'workouts', 'frequencyTooltip');
    }
}

function renderVolumeChart(range) {
    const container = document.getElementById('volumeChart');
    const data = chartData.volume[range];
    
    // Week: Bar chart, Month: Line chart, Year: Area chart
    if (range === 'month' || range === 'year') {
        renderLineChart(container, data, 'minutes', 'volumeTooltip', range === 'year');
    } else {
        renderBarChart(container, data, 'minutes', 'mins', 'volumeTooltip');
    }
}

function renderBarChart(container, data, valueKey, unit, tooltipId) {
    const maxVal = Math.max(...data.map(d => d[valueKey]), 1);
    const barHeight = 120;
    
    container.innerHTML = `
        <div class="bar-chart-enhanced">
            ${data.map(d => {
                const height = d[valueKey] > 0 ? Math.max(4, (d[valueKey] / maxVal) * barHeight) : 0;
                return `
                    <div class="bar-enhanced" style="height: ${height}px;" 
                         onmouseenter="showTooltip(event, '${tooltipId}', '${d.label}: ${d[valueKey]} ${unit}')"
                         onmouseleave="hideTooltip('${tooltipId}')">
                        <span class="bar-label">${d.label}</span>
                    </div>
                `;
            }).join('')}
        </div>
    `;
}

function renderLineChart(container, data, valueKey, tooltipId, showArea = false) {
    const width = 400;
    const height = 140;
    const padding = 20;
    const maxVal = Math.max(...data.map(d => d[valueKey]), 1);
    
    const points = data.map((d, i) => ({
        x: padding + (i / Math.max(data.length - 1, 1)) * (width - 2 * padding),
        y: height - padding - (d[valueKey] / maxVal) * (height - 2 * padding),
        ...d
    }));
    
    let pathD = `M ${points[0].x} ${points[0].y}`;
    for (let i = 1; i < points.length; i++) {
        pathD += ` L ${points[i].x} ${points[i].y}`;
    }
    
    const areaD = showArea 
        ? `${pathD} L ${points[points.length-1].x} ${height-padding} L ${points[0].x} ${height-padding} Z`
        : '';
    
    container.innerHTML = `
        <svg class="line-chart" viewBox="0 0 ${width} ${height}" preserveAspectRatio="none">
            ${showArea ? `<defs><linearGradient id="lineGradient" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="var(--accent)" stop-opacity="0.3"/><stop offset="100%" stop-color="var(--accent)" stop-opacity="0"/></linearGradient></defs><path class="line-area" d="${areaD}"></path>` : ''}
            <path class="line-path" d="${pathD}"></path>
            ${points.map(p => `
                <circle class="line-point" cx="${p.x}" cy="${p.y}" r="4"
                        onmouseenter="showTooltip(event, '${tooltipId}', '${p.label}: ${p[valueKey]} mins')"
                        onmouseleave="hideTooltip('${tooltipId}')"></circle>
            `).join('')}
        </svg>
    `;
}

function showTooltip(event, tooltipId, text) {
    const tooltip = document.getElementById(tooltipId);
    const container = tooltip.parentElement;
    const rect = container.getBoundingClientRect();
    
    tooltip.textContent = text;
    tooltip.classList.add('visible');
    
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top - 40;
    
    tooltip.style.left = x + 'px';
    tooltip.style.top = y + 'px';
    tooltip.style.transform = 'translateX(-50%)';
}

function hideTooltip(tooltipId) {
    document.getElementById(tooltipId).classList.remove('visible');
}

function selectWorkoutType(type) {
    document.querySelectorAll('.workout-type-card').forEach(c => c.classList.remove('active'));
    document.querySelectorAll('.workout-form-section').forEach(f => f.classList.remove('active'));
    
    document.querySelector(`.workout-type-card[data-type="${type}"]`).classList.add('active');
    document.getElementById(`form-${type}`).classList.add('active');
}

function setGender(gender) {
    currentGender = gender;
    document.querySelectorAll('.gender-toggle button').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
    populateExerciseLibrary(gender);
}

function populateExerciseLibrary(gender) {
    const library = document.getElementById('exerciseLibrary');
    const exercises = exercisePrograms[gender] || exercisePrograms.neutral;
    selectedExercises = [];
    
    library.innerHTML = exercises.map((ex) => `
        <label class="exercise-item" onclick="toggleExercise(this, '${ex.name}')">
            <input type="checkbox" value="${ex.name}">
            <div>
                <strong>${ex.name}</strong><br>
                <small>${ex.sets} sets × ${ex.reps} (rest ${ex.rest})</small>
            </div>
        </label>
    `).join('');
}

function toggleExercise(element, name) {
    const checkbox = element.querySelector('input');
    checkbox.checked = !checkbox.checked;
    element.classList.toggle('selected', checkbox.checked);
    
    if (checkbox.checked) {
        selectedExercises.push(name);
    } else {
        selectedExercises = selectedExercises.filter(e => e !== name);
    }
}

function loadRecentWorkouts() {
    fetch('/api/activities.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const container = document.getElementById('recentWorkoutsList');
                if (data.data.length === 0) {
                    container.innerHTML = '<p class="empty-state">No workouts logged yet.</p>';
                    return;
                }
                container.innerHTML = data.data.map(act => `
                    <div class="activity-item">
                        <div>
                            <strong>${act.title}</strong> (${act.type_name})<br>
                            <small>${act.activity_date} • ${act.duration_minutes || 0} mins ${act.distance_km ? '• ' + act.distance_km + ' km' : ''}</small>
                        </div>
                    </div>
                `).join('');
            }
        });
}

function submitWorkout(typeId) {
    const activeForm = document.querySelector('.workout-form-section.active');
    if (!activeForm) return;

    const titleInput = activeForm.querySelector('input[id$="Title"]');
    const title = titleInput ? titleInput.value : '';
    const date = activeForm.querySelector('input[id$="Date"]')?.value || new Date().toISOString().split('T')[0];
    const duration = activeForm.querySelector('input[id$="Duration"]')?.value || null;
    const distance = activeForm.querySelector('input[id$="Distance"]')?.value || null;

    if (!title) {
        alert('Please enter a session title.');
        return;
    }

    let finalTitle = title;
    if (typeId === 4 && selectedExercises.length > 0) {
        finalTitle = `${title} [${selectedExercises.join(', ')}]`;
    }

    const payload = {
        activity_type_id: typeId,
        title: finalTitle,
        activity_date: date,
        duration_minutes: duration,
        distance_km: distance
    };

    fetch('/api/activities.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            activeForm.querySelectorAll('input').forEach(i => i.value = '');
            activeForm.querySelector('input[type="date"]').valueAsDate = new Date();
            document.querySelectorAll('.workout-type-card').forEach(c => c.classList.remove('active'));
            activeForm.classList.remove('active');
            selectedExercises = [];
            loadRecentWorkouts();
            setTimeout(() => location.reload(), 500);
        } else {
            alert('Error logging workout: ' + (res.error || 'Unknown error'));
        }
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>