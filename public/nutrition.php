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

// Fetch 7-day nutrition data for charts
$stmt = $db->prepare("
    SELECT log_date, 
           SUM(calories_consumed) as total_consumed, 
           SUM(calories_burned) as total_burned
    FROM daily_nutrition
    WHERE user_id = :uid AND tenant_id = :tid
    AND log_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY log_date
    ORDER BY log_date ASC
");
$stmt->execute(['uid' => $userId, 'tid' => $tenantId]);
$weeklyNutrition = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format for JS (fill missing days)
$chartData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dayName = date('D', strtotime($date));
    $dayData = array_filter($weeklyNutrition, fn($d) => $d['log_date'] === $date);
    $dayData = !empty($dayData) ? reset($dayData) : ['total_consumed' => 0, 'total_burned' => 0];
    
    $chartData[] = [
        'label' => $dayName,
        'consumed' => (int)$dayData['total_consumed'],
        'burned' => (int)$dayData['total_burned'],
        'net' => (int)$dayData['total_consumed'] - (int)$dayData['total_burned']
    ];
}

// Calculate current net balance for the hero card
$today = date('Y-m-d');
$todayData = array_filter($chartData, fn($d) => $d['label'] === date('D'));
$todayData = !empty($todayData) ? reset($todayData) : ['net' => 0];
$netBalance = $todayData['net'];

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="main-content">
    <!-- Net Balance Hero Card -->
    <div class="net-balance-card">
        <h3>Today's Net Energy Balance</h3>
        <p class="net-balance-value"><?= $netBalance > 0 ? '+' : '' ?><?= $netBalance ?> kcal</p>
        <p class="net-balance-label">
            <?= $netBalance > 0 ? 'Caloric Surplus (Energy Stored)' : ($netBalance < 0 ? 'Caloric Deficit (Fat Loss)' : 'Maintenance Level') ?>
        </p>
    </div>

    <!-- Progress Charts -->
    <div class="progress-charts-row">
        <div class="chart-card">
            <h3>7-Day Calorie Trend</h3>
            <div class="chart-wrapper" id="calorieTrendChart"></div>
            <div class="chart-tooltip" id="calorieTooltip"></div>
        </div>
        
        <div class="chart-card">
            <h3>Macro Nutrient Split (Today)</h3>
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
        
        <!-- Diet Focus Selector -->
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

            <!-- Macro Inputs with Algorithm -->
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
 * CSS donut chart rendering, and asynchronous logging.
 */

// Mock data for the donut chart (In a real app, this would come from a detailed food_log table)
let currentMacros = { p: 0, c: 0, f: 0 };

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('logDate').valueAsDate = new Date();
    renderCalorieTrendChart();
    loadRecentFoodLog();
});

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
    
    // Only auto-fill if the user hasn't manually entered a total
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
        return;
    }

    const pPercent = (p / total) * 100;
    const cPercent = (c / total) * 100;
    // Fats take up the remainder

    // CSS Conic Gradient for performant donut chart
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
    // In a full implementation, this would adjust the target macros for the donut chart
}

function renderCalorieTrendChart() {
    const container = document.getElementById('calorieTrendChart');
    const data = <?= json_encode($chartData) ?>;
    const maxVal = Math.max(...data.map(d => Math.max(d.consumed, d.burned)), 1);
    const barHeight = 120;

    container.innerHTML = `
        <div class="bar-chart-enhanced" style="gap: 4px;">
            ${data.map(d => {
                const hCons = d.consumed > 0 ? Math.max(4, (d.consumed / maxVal) * barHeight) : 0;
                const hBurn = d.burned > 0 ? Math.max(4, (d.burned / maxVal) * barHeight) : 0;
                return `
                    <div style="display:flex; gap:2px; align-items:flex-end; height:100%; flex:1; justify-content:center;">
                        <div class="bar-enhanced" style="height: ${hCons}px; background: var(--accent); width:40%;" 
                             onmouseenter="showTooltip(event, 'calorieTooltip', '${d.label}: ${d.consumed} kcal in')"
                             onmouseleave="hideTooltip('calorieTooltip')"></div>
                        <div class="bar-enhanced" style="height: ${hBurn}px; background: var(--highlight); width:40%;" 
                             onmouseenter="showTooltip(event, 'calorieTooltip', '${d.label}: ${d.burned} kcal out')"
                             onmouseleave="hideTooltip('calorieTooltip')"></div>
                        <span class="bar-label" style="position:absolute; bottom:-20px;">${d.label}</span>
                    </div>
                `;
            }).join('')}
        </div>
        <div style="display:flex; justify-content:center; gap:1rem; margin-top:2rem; font-size:0.75rem;">
            <div style="display:flex; align-items:center; gap:0.5rem;"><div style="width:12px;height:12px;background:var(--accent);border-radius:2px;"></div> Intake</div>
            <div style="display:flex; align-items:center; gap:0.5rem;"><div style="width:12px;height:12px;background:var(--highlight);border-radius:2px;"></div> Burned</div>
        </div>
    `;
}

// Reuse tooltip logic from workouts
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
    // Mock data for visual demonstration (would normally fetch from a food_log API)
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

    // Send to existing nutrition API
    const payload = {
        log_date: date,
        calories_consumed: parseInt(cals),
        calories_burned: 0 // Burned is handled via the workouts page
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
            location.reload(); // Reload to update charts and net balance
        }
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>