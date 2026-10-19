<?php
/**
 * File: workouts.php
 * Location: /public/workouts.php
 *
 * Purpose:
 * Provides a detailed interface for logging specific workout types with
 * time-range analytics, progress tracking, and interactive tooltips.
 * Demonstrates MPA architecture with full CRUD operations (Create via form,
 * Read via charts/table, Update/Delete via recent workouts table).
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
$pageTitle = 'Workouts';
$currentPage = 'workouts';
// Fetch user gender for exercise library
$stmt = $db->prepare("SELECT gender_identity FROM user_profiles WHERE user_id = :id");
$stmt->execute(['id' => $userId]);
$userProfile = $stmt->fetch(PDO::FETCH_ASSOC);
$userGender = $userProfile['gender_identity'] ?? 'prefer_not_to_say';
// --- CHART DATA FETCHING ---
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
// --- FETCH RECENT WORKOUTS FOR CRUD TABLE ---
$recentStmt = $db->prepare("
    SELECT al.activity_log_id, al.title, al.activity_date, al.duration_minutes, at.name as type_name
    FROM activity_logs al
    JOIN activity_types at ON al.activity_type_id = at.activity_type_id AND al.tenant_id = at.tenant_id
    WHERE al.user_id = :uid AND al.tenant_id = :tid
    ORDER BY al.activity_date DESC, al.created_at DESC
    LIMIT 5
");
$recentStmt->execute(['uid' => $userId, 'tid' => $tenantId]);
$recentWorkouts = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<div class="main-content">
    <!-- Progress Charts with Time Range Controls -->
    <div class="progress-charts-row">
        <div class="chart-card" data-tooltip="Visualises your workout frequency over time.">
            <h2>Workout Frequency</h2>
            <div class="chart-controls" role="group" aria-label="Chart time range selection">
                <button class="active" onclick="switchChart('frequency', 'week')" data-tooltip="Show data for the last 7 days.">Week</button>
                <button onclick="switchChart('frequency', 'month')" data-tooltip="Show data for the last 4 weeks.">Month</button>
                <button onclick="switchChart('frequency', 'year')" data-tooltip="Show data for the last 12 months.">Year</button>
            </div>
            <div class="chart-wrapper" id="frequencyChart" role="img" aria-label="Workout frequency chart"></div>
            <div class="chart-tooltip" id="frequencyTooltip" role="tooltip"></div>
        </div>
        <div class="chart-card" data-tooltip="Visualises your total active minutes over time.">
            <h2>Volume Trend (minutes)</h2>
            <div class="chart-controls" role="group" aria-label="Chart time range selection">
                <button class="active" onclick="switchChart('volume', 'week')" data-tooltip="Show data for the last 7 days.">Week</button>
                <button onclick="switchChart('volume', 'month')" data-tooltip="Show data for the last 4 weeks.">Month</button>
                <button onclick="switchChart('volume', 'year')" data-tooltip="Show data for the last 12 months.">Year</button>
            </div>
            <div class="chart-wrapper" id="volumeChart" role="img" aria-label="Volume trend chart"></div>
            <div class="chart-tooltip" id="volumeTooltip" role="tooltip"></div>
            <div class="progress-indicator <?= $progressDirection ?>" aria-live="polite" data-tooltip="Percentage change compared to the previous period.">
                <?php if ($progressPercent > 0): ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="18 15 12 9 6 15"></polyline></svg>
                    <span>+<?= abs($progressPercent) ?>% vs previous period</span>
                <?php elseif ($progressPercent < 0): ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
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
        <p style="color: var(--text); margin-bottom: 1.5rem;">Select your activity type to begin.</p>
        <div class="workout-type-grid" role="group" aria-label="Workout type selection">
            <div class="workout-type-card" data-type="gym" onclick="selectWorkoutType('gym')" role="button" tabindex="0" aria-label="Gym or Resistance training" data-tooltip="Log a resistance training session.">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6.5 6.5h11M6.5 17.5h11M4 10v4M20 10v4M8 6v12M16 6v12"/></svg>
                <h3>Gym / Resistance</h3>
            </div>
            <div class="workout-type-card" data-type="run" onclick="selectWorkoutType('run')" role="button" tabindex="0" aria-label="Running" data-tooltip="Log a running session.">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 4v16M17 4v16M19 4v16M9 4v16M5 4v16"/></svg>
                <h3>Running</h3>
            </div>
            <div class="workout-type-card" data-type="walk" onclick="selectWorkoutType('walk')" role="button" tabindex="0" aria-label="Walking" data-tooltip="Log a walking session.">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 4v8l4 8M9 4v8l-4 8M7 12h10"/></svg>
                <h3>Walking</h3>
            </div>
            <div class="workout-type-card" data-type="swim" onclick="selectWorkoutType('swim')" role="button" tabindex="0" aria-label="Swimming" data-tooltip="Log a swimming session.">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12c2-2 4-2 6 0s4 2 6 0 4-2 6 0M2 16c2-2 4-2 6 0s4 2 6 0 4-2 6 0"/></svg>
                <h3>Swimming</h3>
            </div>
            <div class="workout-type-card" data-type="sport" onclick="selectWorkoutType('sport')" role="button" tabindex="0" aria-label="Sport or Football" data-tooltip="Log a sport or match session.">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20M2 12h20"/></svg>
                <h3>Sport / Football</h3>
            </div>
        </div>
        <form id="workoutForm" novalidate>
            <!-- Gym Form -->
            <div id="form-gym" class="workout-form-section" role="region" aria-label="Gym workout form">
                <div class="gender-toggle" role="group" aria-label="Exercise program selection">
                    <button type="button" class="active" onclick="setGender('male')" data-tooltip="Load the male-focused exercise library.">Male Program</button>
                    <button type="button" onclick="setGender('female')" data-tooltip="Load the female-focused exercise library.">Female Program</button>
                    <button type="button" onclick="setGender('neutral')" data-tooltip="Load the gender-neutral exercise library.">Neutral</button>
                </div>
                <div class="form-group">
                    <label for="gymTitle">Session Title</label>
                    <input type="text" id="gymTitle" placeholder="e.g. Day 1 - Full Body" aria-required="true" data-tooltip="Give your session a memorable name.">
                </div>
                <div class="form-group">
                    <label id="exerciseLibraryLabel">Select Exercises (Lee's 3-Day Split)</label>
                    <div class="exercise-library" id="exerciseLibrary" role="group" aria-labelledby="exerciseLibraryLabel"></div>
                </div>
                <div class="workout-grid-2">
                    <div class="form-group">
                        <label for="gymDuration">Duration (mins)</label>
                        <input type="number" id="gymDuration" name="duration_minutes" min="1" aria-required="true" data-tooltip="Time spent exercising in minutes.">
                    </div>
                    <div class="form-group">
                        <label for="gymDate">Date</label>
                        <input type="date" id="gymDate" name="activity_date" aria-required="true" data-tooltip="Select the date of this workout.">
                    </div>
                </div>
                <button type="button" class="btn-primary" onclick="submitWorkout(4)" data-tooltip="Saves this gym session to your log.">Log Gym Session</button>
            </div>
            <!-- Run Form -->
            <div id="form-run" class="workout-form-section" role="region" aria-label="Running workout form">
                <div class="form-group">
                    <label for="runTitle">Run Title</label>
                    <input type="text" id="runTitle" placeholder="e.g. Morning 5k" aria-required="true" data-tooltip="Give your run a memorable name.">
                </div>
                <div class="workout-grid-2">
                    <div class="form-group">
                        <label for="runDistance">Distance (km)</label>
                        <input type="number" step="0.1" id="runDistance" name="distance_km" min="0" aria-required="true" data-tooltip="Total distance covered in kilometres.">
                    </div>
                    <div class="form-group">
                        <label for="runDuration">Duration (mins)</label>
                        <input type="number" id="runDuration" name="duration_minutes" min="1" aria-required="true" data-tooltip="Time spent running in minutes.">
                    </div>
                </div>
                <div class="form-group">
                    <label for="runDate">Date</label>
                    <input type="date" id="runDate" name="activity_date" aria-required="true" data-tooltip="Select the date of this run.">
                </div>
                <button type="button" class="btn-primary" onclick="submitWorkout(1)" data-tooltip="Saves this run to your log.">Log Run</button>
            </div>
            <!-- Walk Form -->
            <div id="form-walk" class="workout-form-section" role="region" aria-label="Walking workout form">
                <div class="form-group">
                    <label for="walkTitle">Walk Title</label>
                    <input type="text" id="walkTitle" placeholder="e.g. Evening stroll" aria-required="true" data-tooltip="Give your walk a memorable name.">
                </div>
                <div class="workout-grid-2">
                    <div class="form-group">
                        <label for="walkDistance">Distance (km)</label>
                        <input type="number" step="0.1" id="walkDistance" name="distance_km" min="0" aria-required="true" data-tooltip="Total distance covered in kilometres.">
                    </div>
                    <div class="form-group">
                        <label for="walkDuration">Duration (mins)</label>
                        <input type="number" id="walkDuration" name="duration_minutes" min="1" aria-required="true" data-tooltip="Time spent walking in minutes.">
                    </div>
                </div>
                <div class="form-group">
                    <label for="walkSteps">Steps (optional)</label>
                    <input type="number" id="walkSteps" placeholder="e.g. 8000" min="0" data-tooltip="Total step count for this walk.">
                </div>
                <div class="form-group">
                    <label for="walkDate">Date</label>
                    <input type="date" id="walkDate" name="activity_date" aria-required="true" data-tooltip="Select the date of this walk.">
                </div>
                <button type="button" class="btn-primary" onclick="submitWorkout(2)" data-tooltip="Saves this walk to your log.">Log Walk</button>
            </div>
            <!-- Swim Form -->
            <div id="form-swim" class="workout-form-section" role="region" aria-label="Swimming workout form">
                <div class="form-group">
                    <label for="swimTitle">Session Title</label>
                    <input type="text" id="swimTitle" placeholder="e.g. Pool Laps" aria-required="true" data-tooltip="Give your swim a memorable name.">
                </div>
                <div class="workout-grid-2">
                    <div class="form-group">
                        <label for="swimDuration">Duration (mins)</label>
                        <input type="number" id="swimDuration" name="duration_minutes" min="1" aria-required="true" data-tooltip="Time spent swimming in minutes.">
                    </div>
                    <div class="form-group">
                        <label for="swimDate">Date</label>
                        <input type="date" id="swimDate" name="activity_date" aria-required="true" data-tooltip="Select the date of this swim.">
                    </div>
                </div>
                <button type="button" class="btn-primary" onclick="submitWorkout(3)" data-tooltip="Saves this swim to your log.">Log Swim</button>
            </div>
            <!-- Sport Form -->
            <div id="form-sport" class="workout-form-section" role="region" aria-label="Sport workout form">
                <div class="form-group">
                    <label for="sportTitle">Match/Session Title</label>
                    <input type="text" id="sportTitle" placeholder="e.g. Sunday League" aria-required="true" data-tooltip="Give your sport session a memorable name.">
                </div>
                <div class="form-group">
                    <label for="sportDuration">Duration (mins)</label>
                    <input type="number" id="sportDuration" name="duration_minutes" min="1" aria-required="true" data-tooltip="Time spent playing sport in minutes.">
                </div>
                <div class="form-group">
                    <label for="sportDate">Date</label>
                    <input type="date" id="sportDate" name="activity_date" aria-required="true" data-tooltip="Select the date of this sport session.">
                </div>
                <button type="button" class="btn-primary" onclick="submitWorkout(5)" data-tooltip="Saves this sport session to your log.">Log Sport</button>
            </div>
        </form>
    </div>
    <!-- Recent Workouts CRUD Table -->
    <div class="card">
        <h2>Recent Workouts</h2>
        <?php if (empty($recentWorkouts)): ?>
            <p style="color: var(--text);">No recent workouts logged.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table aria-label="Recent workouts list">
                    <thead>
                        <tr>
                            <th scope="col">Date</th>
                            <th scope="col">Type</th>
                            <th scope="col">Title</th>
                            <th scope="col">Duration</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentWorkouts as $rw): ?>
                        <tr>
                            <td><?= htmlspecialchars($rw['activity_date']) ?></td>
                            <td><?= htmlspecialchars($rw['type_name']) ?></td>
                            <td><?= htmlspecialchars($rw['title']) ?></td>
                            <td>
                                <form method="POST" action="api/activities.php" style="display: flex; gap: 0.5rem; align-items: center;">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="activity_log_id" value="<?= $rw['activity_log_id'] ?>">
                                    <label for="duration_<?= $rw['activity_log_id'] ?>" class="sr-only">Duration in minutes</label>
                                    <input type="number" id="duration_<?= $rw['activity_log_id'] ?>" name="duration_minutes" value="<?= (int)$rw['duration_minutes'] ?>" style="width: 60px; padding: 0.25rem; font-size: 0.9rem;" required min="1" aria-label="Duration in minutes" data-tooltip="Update the duration in minutes.">
                                    <span style="font-size: 0.85rem; color: var(--text);">mins</span>
                                    <button type="submit" class="btn-primary" style="padding: 0.25rem 0.75rem; font-size: 0.8rem; margin: 0;" data-tooltip="Saves the updated duration.">Save</button>
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="api/activities.php" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="activity_log_id" value="<?= $rw['activity_log_id'] ?>">
                                    <button type="submit" class="btn-danger" style="padding: 0.25rem 0.75rem; font-size: 0.8rem; margin: 0;" onclick="return confirm('Are you sure you want to delete this workout?')" aria-label="Delete workout: <?= htmlspecialchars($rw['title']) ?>" data-tooltip="Permanently deletes this workout.">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<script>
// Pass PHP data to JS globals
window.exercisePrograms = <?= json_encode([
    'male' => [
        ['name' => 'Barbell Squat', 'sets' => 3, 'reps' => '8-12', 'rest' => '90s'],
        ['name' => 'Dumbbell Bench Press', 'sets' => 3, 'reps' => '8-12', 'rest' => '90s'],
        ['name' => 'Lat Pulldown', 'sets' => 3, 'reps' => '8-12', 'rest' => '90s'],
        ['name' => 'Romanian Deadlift', 'sets' => 3, 'reps' => '8-12', 'rest' => '90s'],
        ['name' => 'Overhead Dumbbell Press', 'sets' => 3, 'reps' => '8-12', 'rest' => '90s'],
        ['name' => 'Bodyweight Pull-up', 'sets' => 3, 'reps' => '5-8', 'rest' => '90s'],
        ['name' => 'Standing Calf Raises', 'sets' => 3, 'reps' => '12-15', 'rest' => '60s'],
        ['name' => 'Leg Press', 'sets' => 3, 'reps' => '8-12', 'rest' => '90s'],
        ['name' => 'Push-ups', 'sets' => 3, 'reps' => '8-12', 'rest' => '60s'],
        ['name' => 'Single-Arm Dumbbell Row', 'sets' => 3, 'reps' => '8-12/side', 'rest' => '90s'],
        ['name' => 'Plank', 'sets' => 3, 'reps' => '30s hold', 'rest' => '60s']
    ],
    'female' => [
        ['name' => 'Goblet Squat', 'sets' => 3, 'reps' => '8-12', 'rest' => '90s'],
        ['name' => 'Dumbbell Bench Press', 'sets' => 3, 'reps' => '8-12', 'rest' => '90s'],
        ['name' => 'Seated Cable Row', 'sets' => 3, 'reps' => '8-12', 'rest' => '90s'],
        ['name' => 'Romanian Deadlift (DB)', 'sets' => 3, 'reps' => '8-12', 'rest' => '90s'],
        ['name' => 'Overhead Dumbbell Press', 'sets' => 3, 'reps' => '8-12', 'rest' => '90s'],
        ['name' => 'Assisted Pull-up', 'sets' => 3, 'reps' => '5-8', 'rest' => '90s'],
        ['name' => 'Standing Calf Raises', 'sets' => 3, 'reps' => '12-15', 'rest' => '60s'],
        ['name' => 'Walking Lunges', 'sets' => 3, 'reps' => '8-12/leg', 'rest' => '90s'],
        ['name' => 'Machine Chest Press', 'sets' => 3, 'reps' => '8-12', 'rest' => '90s'],
        ['name' => 'Single-Arm Dumbbell Row', 'sets' => 3, 'reps' => '8-12/side', 'rest' => '90s'],
        ['name' => 'Dead Bug', 'sets' => 3, 'reps' => '10/side', 'rest' => '60s']
    ],
    'neutral' => [
        ['name' => 'Goblet Squat', 'sets' => 3, 'reps' => '8-12', 'rest' => '90s'],
        ['name' => 'Push-ups', 'sets' => 3, 'reps' => '8-12', 'rest' => '60s'],
        ['name' => 'Seated Cable Row', 'sets' => 3, 'reps' => '8-12', 'rest' => '90s'],
        ['name' => 'Romanian Deadlift (DB)', 'sets' => 3, 'reps' => '8-12', 'rest' => '90s'],
        ['name' => 'Dumbbell Shoulder Press', 'sets' => 3, 'reps' => '8-12', 'rest' => '90s'],
        ['name' => 'Bodyweight Lunge', 'sets' => 3, 'reps' => '8-12/leg', 'rest' => '90s'],
        ['name' => 'Plank', 'sets' => 3, 'reps' => '30s hold', 'rest' => '60s'],
        ['name' => 'Standing Calf Raises', 'sets' => 3, 'reps' => '12-15', 'rest' => '60s']
    ]
]) ?>;
window.currentGender = '<?= htmlspecialchars($userGender) ?>';
window.selectedExercises = [];
window.workoutChartData = {
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
</script>
<script src="js/workouts.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>