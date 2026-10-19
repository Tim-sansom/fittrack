<?php
/**
 * File: nutrition.php
 * Location: /public/nutrition.php
 *
 * Purpose:
 * Provides a comprehensive food diary and nutritional analysis interface.
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/Session.php';
require_once __DIR__ . '/../config/Database.php';
Session::start();
// Explicitly set SPA mode to MPA so the header toggle renders correctly
$_SESSION['spa_mode'] = 'mpa';
// 1. Authentication Check
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$db = Database::getInstance()->getConnection();
$userId = (int)$_SESSION['user_id'];
$tenantId = (int)$_SESSION['tenant_id'];
$pageTitle = 'Nutrition';
$currentPage = 'nutrition';
$success = '';
$error = '';
// 2. Handle Form Submissions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token. Request aborted.');
    }
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'add') {
            $logDate = $_POST['log_date'] ?? date('Y-m-d');
            $caloriesConsumed = (int)($_POST['calories_consumed'] ?? 0);
            $caloriesBurned = (int)($_POST['calories_burned'] ?? 0);
            if ($caloriesConsumed <= 0 && $caloriesBurned <= 0) {
                $error = 'Please enter at least one calorie value.';
            } else {
                $stmt = $db->prepare("
                    INSERT INTO daily_nutrition (tenant_id, user_id, log_date, calories_consumed, calories_burned)
                    VALUES (:tid, :uid, :date, :cc, :cb)
                    ON DUPLICATE KEY UPDATE
                    calories_consumed = VALUES(calories_consumed),
                    calories_burned = VALUES(calories_burned)
                ");
                $stmt->execute([
                    'tid' => $tenantId, 'uid' => $userId, 'date' => $logDate,
                    'cc' => $caloriesConsumed, 'cb' => $caloriesBurned
                ]);
                $success = 'Food log saved successfully.';
            }
        } elseif ($action === 'update') {
            $logDate = $_POST['log_date'] ?? '';
            $caloriesConsumed = (int)($_POST['calories_consumed'] ?? 0);
            $stmt = $db->prepare("UPDATE daily_nutrition SET calories_consumed = :cc WHERE log_date = :date AND user_id = :uid AND tenant_id = :tid");
            $stmt->execute(['cc' => $caloriesConsumed, 'date' => $logDate, 'uid' => $userId, 'tid' => $tenantId]);
            $success = 'Log updated.';
        } elseif ($action === 'delete') {
            $logDate = $_POST['log_date'] ?? '';
            $stmt = $db->prepare("DELETE FROM daily_nutrition WHERE log_date = :date AND user_id = :uid AND tenant_id = :tid");
            $stmt->execute(['date' => $logDate, 'uid' => $userId, 'tid' => $tenantId]);
            $success = 'Log deleted.';
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
    header('Location: nutrition.php?msg=' . urlencode($success ?: $error) . '&type=' . ($success ? 'success' : 'error'));
    exit;
}
if (isset($_GET['msg'])) {
    if ($_GET['type'] === 'success') {
        $success = $_GET['msg'];
    } else {
        $error = $_GET['msg'];
    }
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// 3. Fetch Data for Rendering
$todayStmt = $db->prepare("SELECT * FROM daily_nutrition WHERE user_id = :uid AND tenant_id = :tid AND log_date = CURDATE()");
$todayStmt->execute(['uid' => $userId, 'tid' => $tenantId]);
$todayNutrition = $todayStmt->fetch(PDO::FETCH_ASSOC) ?: [
    'calories_consumed' => 0, 'calories_burned' => 0
];
$foodStmt = $db->prepare("
    SELECT log_date, calories_consumed, calories_burned
    FROM daily_nutrition
    WHERE user_id = :uid AND tenant_id = :tid
    ORDER BY log_date DESC LIMIT 5
");
$foodStmt->execute(['uid' => $userId, 'tid' => $tenantId]);
$recentFood = $foodStmt->fetchAll(PDO::FETCH_ASSOC);
// Pre-calculate net balance for the animated counter
$netBalance = (int)$todayNutrition['calories_consumed'] - (int)$todayNutrition['calories_burned'];
$netSign = $netBalance > 0 ? '+' : ($netBalance < 0 ? '−' : '');
$netAbsolute = abs($netBalance);
$netLabel = $netBalance > 0 ? 'Caloric Surplus (Energy Stored)' : ($netBalance < 0 ? 'Caloric Deficit (Fat Loss)' : 'Maintenance Level');
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<div class="main-content">
    <?php if ($success): ?>
        <div class="alert alert-success" role="alert" style="padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; background: rgba(76,175,80,0.1); color: var(--success); border: 1px solid var(--success);">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error" role="alert" style="padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; background: rgba(226,105,114,0.1); color: var(--alert); border: 1px solid var(--alert);">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    <!-- Net Balance Hero Card -->
    <div class="net-balance-card" data-tooltip="Your net energy balance for today (Consumed minus Burned).">
        <h2>Today's Net Energy Balance</h2>
        <p class="net-balance-value">
            <span class="net-sign"><?= $netSign ?></span><span class="net-counter" data-target="<?= $netAbsolute ?>">0</span> kcal
        </p>
        <p class="net-balance-label"><?= $netLabel ?></p>
    </div>
    <!-- Food Logger Form -->
    <div class="card">
        <h2>Log Food Intake</h2>
        <p style="color: var(--text); margin-bottom: 1.5rem;">Record your daily nutrition to track your progress.</p>
        <form method="POST" action="nutrition.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="logDate">Date</label>
                <input type="date" id="logDate" name="log_date" value="<?= date('Y-m-d') ?>" required data-tooltip="Select the date for this log entry.">
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label for="caloriesConsumed">Calories Consumed (kcal)</label>
                    <input type="number" id="caloriesConsumed" name="calories_consumed" value="<?= (int)$todayNutrition['calories_consumed'] ?>" min="0" required data-tooltip="Total energy intake for the day.">
                </div>
                <div class="form-group">
                    <label for="caloriesBurned">Calories Burned (kcal)</label>
                    <input type="number" id="caloriesBurned" name="calories_burned" value="<?= (int)$todayNutrition['calories_burned'] ?>" min="0" data-tooltip="Total energy expended through activity.">
                </div>
            </div>
            <button type="submit" class="btn-primary" data-tooltip="Records your daily nutrition.">Save Food Log</button>
        </form>
    </div>
    <!-- Recent Food Log Table with Edit/Delete -->
    <div class="card">
        <h2>Recent Food Log</h2>
        <?php if (empty($recentFood)): ?>
            <p style="color: var(--text); opacity: 0.7;">No recent food logged.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;" aria-label="Recent food log entries">
                    <thead>
                        <tr style="text-align: left; border-bottom: 2px solid var(--border);">
                            <th scope="col" style="padding: 0.75rem 0.5rem;">Date</th>
                            <th scope="col" style="padding: 0.75rem 0.5rem;">Consumed</th>
                            <th scope="col" style="padding: 0.75rem 0.5rem;">Burned</th>
                            <th scope="col" style="padding: 0.75rem 0.5rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentFood as $rf): ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 0.75rem 0.5rem;"><?= htmlspecialchars($rf['log_date']) ?></td>
                            <td style="padding: 0.75rem 0.5rem;"><?= (int)$rf['calories_consumed'] ?> kcal</td>
                            <td style="padding: 0.75rem 0.5rem;"><?= (int)$rf['calories_burned'] ?> kcal</td>
                            <td style="padding: 0.75rem 0.5rem; display: flex; gap: 0.5rem;">
                                <form method="POST" action="nutrition.php" style="display: flex; gap: 0.25rem; align-items: center;">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="log_date" value="<?= $rf['log_date'] ?>">
                                    <label for="calories_<?= $rf['log_date'] ?>" class="sr-only">Calories consumed on <?= htmlspecialchars($rf['log_date']) ?></label>
                                    <input type="number" id="calories_<?= $rf['log_date'] ?>" name="calories_consumed" value="<?= (int)$rf['calories_consumed'] ?>" style="width: 70px; padding: 0.25rem;" aria-label="Calories consumed on <?= htmlspecialchars($rf['log_date']) ?>" min="0" data-tooltip="Update calories for this date.">
                                    <button type="submit" class="btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" aria-label="Save calories for <?= htmlspecialchars($rf['log_date']) ?>" data-tooltip="Saves the updated value.">Save</button>
                                </form>
                                <form method="POST" action="nutrition.php" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="log_date" value="<?= $rf['log_date'] ?>">
                                    <button type="submit" class="btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="return confirm('Delete this log?')" aria-label="Delete log for <?= htmlspecialchars($rf['log_date']) ?>" data-tooltip="Permanently deletes this entry.">Delete</button>
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
<!-- Animated Net Balance Counter -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const counter = document.querySelector('.net-counter');
    if (!counter) return;
    const target = parseInt(counter.dataset.target, 10) || 0;
    if (target === 0) {
        counter.textContent = '0';
        return;
    }
    const duration = 1200;
    const steps = 60;
    const increment = target / steps;
    let step = 0;
    const timer = setInterval(function () {
        step++;
        const current = Math.min(Math.round(increment * step), target);
        counter.textContent = current.toLocaleString('en-GB');
        if (step >= steps) {
            counter.textContent = target.toLocaleString('en-GB');
            clearInterval(timer);
        }
    }, duration / steps);
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>