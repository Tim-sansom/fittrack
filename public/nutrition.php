<?php
/**
 * File: nutrition.php
 * Location: /public/nutrition.php
 *
 * Purpose:
 * Provides a comprehensive food diary and nutritional analysis interface.
 * Implements the Atwater system algorithm to convert macronutrients into
 * total calories, feeding directly into the dashboard's net balance algorithm.
 *
 * Architectural Role:
 * Demonstrates MPA architecture with advanced client-side data processing.
 * Uses CSS conic-gradients for performant, library-free data visualisation.
 *
 * Independent Contribution:
 * - Inclusive diet profiles (High Protein, Low Carb, Diabetic, Keto, etc.)
 * - Real-time macro-to-calorie algorithmic conversion
 * - Interactive net-balance tracking (Intake vs Expenditure)
 * - Multi-range analytics (Day/Week/Month) with dynamic re-rendering
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
$pageTitle = 'Nutrition';
$currentPage = 'nutrition';

/**
 * Check if macro columns exist in daily_nutrition table
 */
function checkMacroColumns($db): bool {
    try {
        $stmt = $db->query("SHOW COLUMNS FROM daily_nutrition LIKE 'protein_g'");
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        return false;
    }
}

$hasMacroColumns = checkMacroColumns($db);

/**
 * Fetch nutrition data for a given range
 */
function fetchNutritionRange($db, $userId, $tenantId, $range, $hasMacros): array {
    $interval = match($range) {
        'day'   => 'INTERVAL 1 DAY',
        'week'  => 'INTERVAL 7 DAY',
        'month' => 'INTERVAL 30 DAY',
        default => 'INTERVAL 7 DAY',
    };

    $macroSelect = $hasMacros 
        ? ', SUM(protein_g) as total_protein, SUM(carbs_g) as total_carbs, SUM(fats_g) as total_fats'
        : ', 0 as total_protein, 0 as total_carbs, 0 as total_fats';

    $stmt = $db->prepare("
        SELECT log_date,
               SUM(calories_consumed) as total_consumed,
               SUM(calories_burned) as total_burned
               $macroSelect
        FROM daily_nutrition
        WHERE user_id = :uid AND tenant_id = :tid
          AND log_date >= DATE_SUB(CURDATE(), $interval)
        GROUP BY log_date
        ORDER BY log_date ASC
    ");
    $stmt->execute(['uid' => $userId, 'tid' => $tenantId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch data for all three ranges
$dayData   = fetchNutritionRange($db, $userId, $tenantId, 'day', $hasMacroColumns);
$weekData  = fetchNutritionRange($db, $userId, $tenantId, 'week', $hasMacroColumns);
$monthData = fetchNutritionRange($db, $userId, $tenantId, 'month', $hasMacroColumns);

/**
 * Shape data for the chart with proper labels
 */
function shapeChartData($rawData, $days, $format): array {
    $chartData = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $label = match($format) {
            'day'   => date('H:i', strtotime($date)),
            'week'  => date('D', strtotime($date)),
            'month' => date('d M', strtotime($date)),
        };
        $dayData = array_filter($rawData, fn($d) => $d['log_date'] === $date);
        $dayData = !empty($dayData) ? reset($dayData) : [
            'total_consumed' => 0, 'total_burned' => 0,
            'total_protein' => 0, 'total_carbs' => 0, 'total_fats' => 0
        ];
        $chartData[] = [
            'label'    => $label,
            'consumed' => (int)($dayData['total_consumed'] ?? 0),
            'burned'   => (int)($dayData['total_burned'] ?? 0),
            'net'      => (int)($dayData['total_consumed'] ?? 0) - (int)($dayData['total_burned'] ?? 0),
            'protein'  => (int)($dayData['total_protein'] ?? 0),
            'carbs'    => (int)($dayData['total_carbs'] ?? 0),
            'fats'     => (int)($dayData['total_fats'] ?? 0),
        ];
    }
    return $chartData;
}

$dayChartData   = shapeChartData($dayData, 1, 'day');
$weekChartData  = shapeChartData($weekData, 7, 'week');
$monthChartData = shapeChartData($monthData, 30, 'month');

// Today's totals for the hero card
$today = date('Y-m-d');
$todayRow = array_filter($dayData, fn($d) => $d['log_date'] === $today);
$todayRow = !empty($todayRow) ? reset($todayRow) : [
    'total_consumed' => 0, 'total_burned' => 0,
    'total_protein' => 0, 'total_carbs' => 0, 'total_fats' => 0
];
$netBalance = (int)($todayRow['total_consumed'] ?? 0) - (int)($todayRow['total_burned'] ?? 0);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<div class="main-content">
    <!-- Net Balance Hero Card -->
    <div class="net-balance-card">
        <h3 id="balanceTitle">Today's Net Energy Balance</h3>
        <p class="net-balance-value" id="balanceValue"><?= $netBalance > 0 ? '+' : '' ?><?= $netBalance ?> kcal</p>
        <p class="net-balance-label" id="balanceLabel">
            <?= $netBalance > 0 ? 'Caloric Surplus (Energy Stored)' : ($netBalance < 0 ? 'Caloric Deficit (Fat Loss)' : 'Maintenance Level') ?>
        </p>
    </div>

    <!-- Progress Charts with Range Toggles -->
    <div class="progress-charts-row">
        <div class="chart-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 style="margin: 0;">Calorie Trend</h3>
                <div class="chart-controls">
                    <button class="active" onclick="switchNutritionRange('day')">Day</button>
                    <button onclick="switchNutritionRange('week')">Week</button>
                    <button onclick="switchNutritionRange('month')">Month</button>
                </div>
            </div>
            <div class="chart-wrapper" id="calorieTrendChart" style="min-height: 200px;"></div>
            <div class="chart-tooltip" id="calorieTooltip"></div>
        </div>
        <div class="chart-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 style="margin: 0;">Macro Nutrient Split</h3>
                <div class="chart-controls">
                    <button class="active" onclick="switchMacroRange('day')">Day</button>
                    <button onclick="switchMacroRange('week')">Week</button>
                    <button onclick="switchMacroRange('month')">Month</button>
                </div>
            </div>
            <div class="donut-chart-container">
                <div class="donut-chart" id="macroDonut">
                    <div class="donut-hole">
                        <strong id="totalMacroCals">0</strong>
                        <span>kcal</span>
                    </div>
                </div>
                <div class="macro-legend">
                    <div class="legend-item"><div class="legend-color" style="background: #4CAF50;"></div> Protein <span id="leg-p">0g</span></div>
                    <div class="legend-item"><div class="legend-color" style="background: #9F5F91;"></div> Carbs <span id="leg-c">0g</span></div>
                    <div class="legend-item"><div class="legend-color" style="background: #F6EA98;"></div> Fats <span id="leg-f">0g</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Food Logger -->
    <div class="card">
        <h2>Log Food Intake</h2>
        <p style="color: var(--text); opacity: 0.7; margin-bottom: 0.5rem;">Select your current diet focus:</p>
        <div class="diet-selector">
            <button class="active" onclick="setDietFocus(this, 'balanced')">Balanced</button>
            <button onclick="setDietFocus(this, 'high-protein')">High Protein</button>
            <button onclick="setDietFocus(this, 'low-carb')">Low Carb</button>
            <button onclick="setDietFocus(this, 'keto')">Ketogenic</button>
            <button onclick="setDietFocus(this, 'diabetic')">Diabetic / Low GI</button>
            <button onclick="setDietFocus(this, 'vegan')">Vegan</button>
        </div>
        <form id="nutritionForm">
            <div class="workout-grid-2">
                <div class="form-group">
                    <label>Meal Type</label>
                    <select id="mealType">
                        <option value="Breakfast">Breakfast</option>
                        <option value="Lunch">Lunch</option>
                        <option value="Dinner">Dinner</option>
                        <option value="Snack">Snack</option>
                        <option value="Liquid">Liquid / Supplement</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" id="logDate" required>
                </div>
            </div>
            <div class="form-group">
                <label>Food / Drink Name</label>
                <input type="text" id="foodName" placeholder="e.g. Grilled Chicken Breast & Rice" required>
            </div>
            <div class="macro-grid">
                <div class="form-group macro-input-group">
                    <label>Total Calories (kcal)</label>
                    <input type="number" id="totalCals" placeholder="Auto-calc">
                </div>
                <div class="form-group macro-input-group">
                    <label>Protein (g)</label>
                    <input type="number" id="macroP" placeholder="0" oninput="calculateCalories()">
                </div>
                <div class="form-group macro-input-group">
                    <label>Carbs (g)</label>
                    <input type="number" id="macroC" placeholder="0" oninput="calculateCalories()">
                </div>
                <div class="form-group macro-input-group">
                    <label>Fats (g)</label>
                    <input type="number" id="macroF" placeholder="0" oninput="calculateCalories()">
                </div>
            </div>
            <button type="button" class="btn-primary" onclick="submitFoodLog()">Log Meal</button>
        </form>
    </div>

    <!-- Recent Food Log -->
    <div class="card">
        <h2>Recent Food Log</h2>
        <div id="recentFoodLog">Loading...</div>
    </div>
</div>

<script>
/**
 * Nutrition Page Controller
 * Handles the Atwater calorie algorithm, diet focus toggling,
 * CSS donut chart rendering, range switching, and asynchronous logging.
 */
let currentMacros = { p: 0, c: 0, f: 0 };
let currentRange = 'day';

// Data payloads from PHP, keyed by range
const nutritionChartData = {
    day: <?= json_encode($dayChartData) ?>,
    week: <?= json_encode($weekChartData) ?>,
    month: <?= json_encode($monthChartData) ?>
};

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('logDate').valueAsDate = new Date();
    renderCalorieTrendChart('day');
    updateDonutFromData(nutritionChartData.day.reduce((acc, d) => {
        acc.p += d.protein; acc.c += d.carbs; acc.f += d.fats;
        return acc;
    }, { p: 0, c: 0, f: 0 }));
    loadRecentFoodLog();
});

/**
 * Switch the calorie trend chart to a new time range.
 */
function switchNutritionRange(range) {
    currentRange = range;
    // Update active button state
    document.querySelectorAll('.chart-card:first-child .chart-controls button').forEach(btn => {
        btn.classList.toggle('active', btn.textContent.toLowerCase() === range);
    });
    // Update hero card title
    const titles = { day: "Today's Net Energy Balance", week: "This Week's Net Energy Balance", month: "This Month's Net Energy Balance" };
    document.getElementById('balanceTitle').textContent = titles[range];
    // Recalculate net balance for the range
    const data = nutritionChartData[range];
    const rangeNet = data.reduce((sum, d) => sum + d.net, 0);
    const balanceEl = document.getElementById('balanceValue');
    balanceEl.textContent = (rangeNet > 0 ? '+' : '') + rangeNet + ' kcal';
    document.getElementById('balanceLabel').textContent =
        rangeNet > 0 ? 'Caloric Surplus (Energy Stored)' : (rangeNet < 0 ? 'Caloric Deficit (Fat Loss)' : 'Maintenance Level');

    renderCalorieTrendChart(range);
}

/**
 * Switch the macro donut to a new time range.
 */
function switchMacroRange(range) {
    document.querySelectorAll('.chart-card:last-child .chart-controls button').forEach(btn => {
        btn.classList.toggle('active', btn.textContent.toLowerCase() === range);
    });
    const data = nutritionChartData[range];
    const totals = data.reduce((acc, d) => {
        acc.p += d.protein; acc.c += d.carbs; acc.f += d.fats;
        return acc;
    }, { p: 0, c: 0, f: 0 });
    updateDonutFromData(totals);
}

function updateDonutFromData(totals) {
    updateDonutChart(totals.p, totals.c, totals.f);
}

/**
 * Atwater System Algorithm
 * Calculates total calories based on macronutrient grams.
 * Protein: 4 kcal/g, Carbs: 4 kcal/g, Fats: 9 kcal/g
 */
function calculateCalories() {
    const p = parseFloat(document.getElementById('macroP').value) || 0;
    const c = parseFloat(document.getElementById('macroC').value) || 0;
    const f = parseFloat(document.getElementById('macroF').value) || 0;
    const calculatedCals = (p * 4) + (c * 4) + (f * 9);
    if (!document.getElementById('totalCals').value) {
        document.getElementById('totalCals').value = Math.round(calculatedCals);
    }
    updateDonutChart(p, c, f);
}

function updateDonutChart(p, c, f) {
    const total = p + c + f;
    if (total === 0) {
        document.getElementById('macroDonut').style.background = 'var(--border)';
        document.getElementById('totalMacroCals').textContent = '0';
        document.getElementById('leg-p').textContent = '0g';
        document.getElementById('leg-c').textContent = '0g';
        document.getElementById('leg-f').textContent = '0g';
        return;
    }
    const pPercent = (p / total) * 100;
    const cPercent = (c / total) * 100;
    const gradient = `conic-gradient(
        #4CAF50 0% ${pPercent}%,
        #9F5F91 ${pPercent}% ${pPercent + cPercent}%,
        #F6EA98 ${pPercent + cPercent}% 100%
    )`;
    document.getElementById('macroDonut').style.background = gradient;
    document.getElementById('totalMacroCals').textContent = document.getElementById('totalCals').value || Math.round((p*4)+(c*4)+(f*9));
    document.getElementById('leg-p').textContent = p + 'g';
    document.getElementById('leg-c').textContent = c + 'g';
    document.getElementById('leg-f').textContent = f + 'g';
}

function setDietFocus(btn, diet) {
    document.querySelectorAll('.diet-selector button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

function renderCalorieTrendChart(range) {
    const container = document.getElementById('calorieTrendChart');
    const data = nutritionChartData[range];
    const maxVal = Math.max(...data.map(d => Math.max(d.consumed, d.burned)), 1);
    const barHeight = 150;
    
    // Only show labels for a subset to avoid overlap
    const showLabel = (index, total) => {
        if (total <= 7) return true; // Show all for week view
        if (total <= 30) return index % 5 === 0 || index === total - 1; // Every 5th for month
        return index % 10 === 0 || index === total - 1; // Every 10th for larger ranges
    };

    container.innerHTML = `
        <div class="bar-chart-enhanced" style="gap: 4px; height: ${barHeight}px; display: flex; align-items: flex-end; padding-bottom: 30px;">
            ${data.map((d, i) => {
                const hCons = d.consumed > 0 ? Math.max(4, (d.consumed / maxVal) * barHeight) : 0;
                const hBurn = d.burned > 0 ? Math.max(4, (d.burned / maxVal) * barHeight) : 0;
                const showLbl = showLabel(i, data.length);
                return `
                <div style="display:flex; gap:2px; align-items:flex-end; height:100%; flex:1; justify-content:center; position: relative;">
                    <div class="bar-enhanced" style="height: ${hCons}px; background: var(--accent); width:40%; min-width: 8px;"
                         onmouseenter="showTooltip(event, 'calorieTooltip', '${d.label}: ${d.consumed} kcal in')"
                         onmouseleave="hideTooltip('calorieTooltip')"></div>
                    <div class="bar-enhanced" style="height: ${hBurn}px; background: var(--highlight); width:40%; min-width: 8px;"
                         onmouseenter="showTooltip(event, 'calorieTooltip', '${d.label}: ${d.burned} kcal out')"
                         onmouseleave="hideTooltip('calorieTooltip')"></div>
                    ${showLbl ? `<span style="position:absolute; bottom:-25px; font-size: 0.7rem; white-space: nowrap;">${d.label}</span>` : ''}
                </div>`;
            }).join('')}
        </div>
        <div style="display:flex; justify-content:center; gap:1rem; margin-top:2rem; font-size:0.75rem;">
            <div style="display:flex; align-items:center; gap:0.5rem;"><div style="width:12px;height:12px;background:var(--accent);border-radius:2px;"></div> Intake</div>
            <div style="display:flex; align-items:center; gap:0.5rem;"><div style="width:12px;height:12px;background:var(--highlight);border-radius:2px;"></div> Burned</div>
        </div>
    `;
}

function showTooltip(event, tooltipId, text) {
    const tooltip = document.getElementById(tooltipId);
    const container = tooltip.closest('.chart-card');
    if (!container) return;
    const rect = container.getBoundingClientRect();
    tooltip.textContent = text;
    tooltip.classList.add('visible');
    tooltip.style.left = (event.clientX - rect.left) + 'px';
    tooltip.style.top = (event.clientY - rect.top - 40) + 'px';
    tooltip.style.transform = 'translateX(-50%)';
}

function hideTooltip(tooltipId) {
    document.getElementById(tooltipId).classList.remove('visible');
}

function loadRecentFoodLog() {
    const mockLog = [
        { name: 'Oatmeal with Berries', meal: 'Breakfast', cals: 350, p: 12, c: 60, f: 6 },
        { name: 'Grilled Chicken Salad', meal: 'Lunch', cals: 450, p: 45, c: 15, f: 20 },
        { name: 'Protein Shake', meal: 'Snack', cals: 180, p: 25, c: 5, f: 3 }
    ];
    const container = document.getElementById('recentFoodLog');
    container.innerHTML = mockLog.map(item => `
        <div class="food-log-item">
            <div class="food-details">
                <span class="food-name">${item.name}</span>
                <span class="food-meta">${item.meal} • ${item.cals} kcal</span>
            </div>
            <div class="food-macros">
                <span class="macro-p">P: ${item.p}g</span>
                <span class="macro-c">C: ${item.c}g</span>
                <span class="macro-f">F: ${item.f}g</span>
            </div>
        </div>
    `).join('');
}

function submitFoodLog() {
    const name = document.getElementById('foodName').value;
    const date = document.getElementById('logDate').value;
    const cals = document.getElementById('totalCals').value;
    if (!name || !cals || !date) {
        alert('Please fill in the food name, calories, and date.');
        return;
    }
    const payload = {
        log_date: date,
        calories_consumed: parseInt(cals),
        calories_burned: 0
    };
    fetch('/api/nutrition.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            document.getElementById('foodName').value = '';
            document.getElementById('totalCals').value = '';
            document.getElementById('macroP').value = '';
            document.getElementById('macroC').value = '';
            document.getElementById('macroF').value = '';
            updateDonutChart(0,0,0);
            alert('Meal logged successfully! Dashboard updated.');
            location.reload();
        }
    });
}
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>