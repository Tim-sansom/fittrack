<?php
/**
 * File: dashboard.php
 * Location: /public/dashboard.php
 * 
 * Purpose:
 * Primary user interface. Acts as a Single Page Application (SPA) shell 
 * that loads data dynamically via the API endpoints in /public/api/.
 * 
 * Architectural Role:
 * Separates the presentation layer (HTML/CSS) from the data layer (API).
 * The PHP portion only handles session validation and layout inclusion.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/Session.php';
Session::start();

// Redirect unauthenticated users to login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="dashboard-grid">
    <!-- Left Column: Visualizations -->
    <div>
        <div class="stats-row">
            <div class="stat-card">
                <div class="ring-container">
                    <svg viewBox="0 0 100 100" width="100" height="100">
                        <circle class="ring-bg" cx="50" cy="50" r="42"></circle>
                        <circle class="ring-progress" id="workoutRing" cx="50" cy="50" r="42" stroke-dasharray="264" stroke-dashoffset="264"></circle>
                    </svg>
                    <div class="ring-text" id="workoutCount">0</div>
                </div>
                <div>Workouts This Week</div>
            </div>
            <div class="stat-card">
                <div class="ring-container">
                    <svg viewBox="0 0 100 100" width="100" height="100">
                        <circle class="ring-bg" cx="50" cy="50" r="42"></circle>
                        <circle class="ring-progress" id="durationRing" cx="50" cy="50" r="42" stroke-dasharray="264" stroke-dashoffset="264" style="stroke: var(--highlight)"></circle>
                    </svg>
                    <div class="ring-text" id="durationCount">0</div>
                </div>
                <div>Minutes Active</div>
            </div>
            <div class="stat-card">
                <div class="ring-container">
                    <svg viewBox="0 0 100 100" width="100" height="100">
                        <circle class="ring-bg" cx="50" cy="50" r="42"></circle>
                        <circle class="ring-progress" id="calorieRing" cx="50" cy="50" r="42" stroke-dasharray="264" stroke-dashoffset="264" style="stroke: var(--success)"></circle>
                    </svg>
                    <div class="ring-text" id="calorieBalance">0</div>
                </div>
                <div>Net Calories Today</div>
            </div>
        </div>

        <div class="card">
            <h2>Activity Distribution</h2>
            <div class="chart-container"><div class="bar-chart" id="barChart"></div></div>
        </div>

        <div class="card">
            <h2>7-Day Calorie Balance</h2>
            <div class="chart-container">
                <svg class="wave-chart" id="waveChart" viewBox="0 0 600 200" preserveAspectRatio="none"></svg>
            </div>
        </div>
    </div>

    <!-- Right Column: Actions & Plans -->
    <div>
        <div class="card">
            <h2>3-Day Full Body Split</h2>
            <div class="plan-day"><div class="plan-day-header"><span class="day-name">Monday</span></div><div class="muscle-tags"><span class="tag">Chest</span><span class="tag">Back</span><span class="tag">Legs</span></div></div>
            <div class="plan-day"><div class="plan-day-header"><span class="day-name">Tuesday</span></div><div class="rest-day">Rest & Recovery</div></div>
            <div class="plan-day"><div class="plan-day-header"><span class="day-name">Wednesday</span></div><div class="muscle-tags"><span class="tag">Shoulders</span><span class="tag">Arms</span><span class="tag">Core</span></div></div>
            <div class="plan-day"><div class="plan-day-header"><span class="day-name">Thursday</span></div><div class="rest-day">Rest & Recovery</div></div>
            <div class="plan-day"><div class="plan-day-header"><span class="day-name">Friday</span></div><div class="muscle-tags"><span class="tag">Full Body</span><span class="tag">Cardio</span></div></div>
        </div>

        <div class="card">
            <h2>Log Daily Nutrition</h2>
            <form id="nutritionForm">
                <div class="form-group"><label for="logDate">Date</label><input type="date" id="logDate" required></div>
                <div class="form-group"><label for="calConsumed">Calories Consumed</label><input type="number" id="calConsumed" placeholder="e.g. 2000" required></div>
                <div class="form-group"><label for="calBurned">Calories Burned (Exercise)</label><input type="number" id="calBurned" placeholder="e.g. 400"></div>
                <button type="submit" class="btn-primary">Save Nutrition</button>
            </form>
        </div>

        <div class="card">
            <h2 id="formTitle">Log Activity</h2>
            <form id="activityForm">
                <input type="hidden" id="editId" name="activity_log_id" value="">
                <div class="form-group"><label for="type">Activity Type</label><select id="type" name="activity_type_id" required><option value="1">Running</option><option value="2">Walking</option><option value="3">Swimming</option><option value="4">Gym</option><option value="5">Football</option></select></div>
                <div class="form-group"><label for="title">Title</label><input type="text" id="title" name="title" placeholder="e.g. Morning Run" required></div>
                <div class="form-group"><label for="date">Date</label><input type="date" id="date" name="activity_date" required></div>
                <div class="form-group"><label for="duration">Duration (mins)</label><input type="number" id="duration" name="duration_minutes"></div>
                <button type="submit" class="btn-primary" id="submitBtn">Save Activity</button>
                <button type="button" class="btn-secondary" id="cancelBtn" style="display: none;">Cancel Edit</button>
            </form>
        </div>

        <div class="card">
            <h2>Recent Activities</h2>
            <div id="activityList">Loading...</div>
        </div>
    </div>
</div>

<script>
/**
 * Dashboard Controller
 * Handles data fetching, chart rendering, and form submissions for the dashboard.
 */
document.addEventListener('DOMContentLoaded', () => {
    const listContainer = document.getElementById('activityList');
    const form = document.getElementById('activityForm');
    const editIdInput = document.getElementById('editId');
    const formTitle = document.getElementById('formTitle');
    const submitBtn = document.getElementById('submitBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const nutritionForm = document.getElementById('nutritionForm');
    
    // Set default dates to today
    document.querySelectorAll('input[type="date"]').forEach(input => {
        if (!input.value) input.valueAsDate = new Date();
    });

    /**
     * Fetches stats and activity list from the API.
     */
    function loadDashboard() {
        fetch('/api/dashboard_data.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updateRings(data.data.weekly, data.data.nutrition_chart);
                    renderBarChart(data.data.distribution);
                    renderWaveChart(data.data.nutrition_chart);
                }
            });

        fetch('/api/activities.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.data.length === 0) {
                        listContainer.innerHTML = '<p class="empty-state">No activities logged yet.</p>';
                        return;
                    }
                    listContainer.innerHTML = data.data.map(act => `
                        <div class="activity-item">
                            <div><strong>${act.title}</strong> (${act.type_name})<br><small>${act.activity_date} • ${act.duration_minutes || 0} mins</small></div>
                            <div class="action-btns">
                                <button class="btn-edit" onclick="editActivity(${act.activity_log_id}, '${act.title}', '${act.activity_type_id}', '${act.activity_date}', '${act.duration_minutes}')">Edit</button>
                                <button class="btn-delete" onclick="deleteActivity(${act.activity_log_id})">Delete</button>
                            </div>
                        </div>
                    `).join('');
                }
            });
    }

    function updateRings(weekly, nutritionData) {
        const workoutPercent = Math.min((weekly.total_workouts / 4) * 100, 100);
        animateRing('workoutRing', workoutPercent);
        document.getElementById('workoutCount').textContent = weekly.total_workouts;

        const durationPercent = Math.min((weekly.total_duration / 150) * 100, 100);
        animateRing('durationRing', durationPercent);
        document.getElementById('durationCount').textContent = weekly.total_duration;

        let netCals = 0;
        if (nutritionData && nutritionData.length > 0) {
             const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
             const todayName = days[new Date().getDay()];
             const todayEntry = nutritionData.find(d => d.date === todayName);
             if (todayEntry) netCals = todayEntry.balance;
        }
        const calPercent = Math.min((Math.abs(netCals) / 2000) * 100, 100);
        animateRing('calorieRing', calPercent);
        document.getElementById('calorieBalance').textContent = netCals;
    }

    function animateRing(id, percent) {
        const circle = document.getElementById(id);
        if (!circle) return;
        const circumference = 2 * Math.PI * 42;
        const offset = circumference - (percent / 100) * circumference;
        setTimeout(() => { circle.style.strokeDashoffset = offset; }, 100);
    }

    function renderBarChart(distribution) {
        const container = document.getElementById('barChart');
        if (!container) return;
        if (!distribution || !distribution.length) { container.innerHTML = '<p class="empty-state">No data yet.</p>'; return; }
        const max = Math.max(...distribution.map(d => d.count));
        container.innerHTML = distribution.map(d => {
            const heightPx = max > 0 ? (d.count / max) * 120 : 0;
            return `<div class="bar-group"><div class="bar-value">${d.count}</div><div class="bar" style="height: ${heightPx}px;"></div><div class="bar-label">${d.name}</div></div>`;
        }).join('');
    }

    function renderWaveChart(data) {
        const svg = document.getElementById('waveChart');
        if (!svg) return;
        const width = 600, height = 200, padding = 20;
        if (!data || data.length === 0 || data.every(d => d.balance === 0)) {
            svg.innerHTML = `<line x1="${padding}" y1="${height/2}" x2="${width-padding}" y2="${height/2}" stroke="#eee" stroke-width="2" stroke-dasharray="4"/><text x="${width/2}" y="${height/2 - 10}" text-anchor="middle" fill="#999">Log nutrition to see balance</text>`;
            return;
        }
        const maxVal = Math.max(...data.map(d => Math.abs(d.balance)), 1000);
        const points = data.map((d, i) => {
            const x = padding + (i / (data.length - 1 || 1)) * (width - 2 * padding);
            const y = height / 2 - (d.balance / maxVal) * (height / 2 - padding);
            return { x, y, ...d };
        });
        let pathD = `M ${points[0].x} ${points[0].y}`;
        for (let i = 1; i < points.length; i++) pathD += ` L ${points[i].x} ${points[i].y}`;
        const areaD = `${pathD} L ${points[points.length-1].x} ${height/2} L ${points[0].x} ${height/2} Z`;
        svg.innerHTML = `<defs><linearGradient id="waveGradient" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="var(--accent)" stop-opacity="0.5"/><stop offset="100%" stop-color="var(--accent)" stop-opacity="0"/></linearGradient></defs><line x1="${padding}" y1="${height/2}" x2="${width-padding}" y2="${height/2}" stroke="#eee" stroke-width="2" stroke-dasharray="4"/><path class="wave-area" d="${areaD}"></path><path class="wave-line" d="${pathD}"></path>${points.map(p => `<circle class="wave-point" cx="${p.x}" cy="${p.y}" r="4"><title>${p.date}: ${p.balance} kcal</title></circle>`).join('')}${points.map((p, i) => `<text x="${p.x}" y="${height-5}" text-anchor="middle" font-size="10" fill="#666">${p.date}</text>`).join('')}`;
    }

    // Expose edit function to global scope for inline onclick handlers
    window.editActivity = (id, title, typeId, date, duration) => {
        editIdInput.value = id;
        document.getElementById('title').value = title;
        document.getElementById('type').value = typeId;
        document.getElementById('date').value = date;
        document.getElementById('duration').value = duration;
        formTitle.textContent = 'Edit Activity';
        submitBtn.textContent = 'Update Activity';
        cancelBtn.style.display = 'block';
    };

    cancelBtn.addEventListener('click', () => {
        form.reset();
        editIdInput.value = '';
        document.getElementById('date').valueAsDate = new Date();
        formTitle.textContent = 'Log Activity';
        submitBtn.textContent = 'Save Activity';
        cancelBtn.style.display = 'none';
    });

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(form).entries());
        const isEdit = editIdInput.value !== '';
        fetch('/api/activities.php', {
            method: isEdit ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        }).then(res => res.json()).then(res => {
            if (res.success) { cancelBtn.click(); loadDashboard(); }
        });
    });

    nutritionForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const data = {
            log_date: document.getElementById('logDate').value,
            calories_consumed: document.getElementById('calConsumed').value,
            calories_burned: document.getElementById('calBurned').value || 0
        };
        fetch('/api/nutrition.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data)
        }).then(res => res.json()).then(res => {
            if (res.success) { nutritionForm.reset(); document.getElementById('logDate').valueAsDate = new Date(); loadDashboard(); }
        });
    });

    window.deleteActivity = (id) => {
        if (!confirm('Delete this activity?')) return;
        fetch('/api/activities.php', {
            method: 'DELETE', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ activity_log_id: id })
        }).then(res => res.json()).then(res => { if (res.success) loadDashboard(); });
    };

    loadDashboard();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>