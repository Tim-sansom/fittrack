<?php
/**
 * File: meal-plans.php
 * Location: /public/meal-plans.php
 *
 * Purpose:
 * Weekly meal planner with CRUD operations.
 * Displays the current week (Monday–Sunday) with four meal slots per day.
 * Users can add, edit, and delete meals, and optionally apply meals to
 * their daily nutrition tracking.
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
$pageTitle = 'Meal Plans';
$currentPage = 'meal-plans';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Calculate current week (Monday to Sunday)
$today = new DateTime();
$dayOfWeek = (int)$today->format('N'); // 1 (Mon) to 7 (Sun)
$monday = (clone $today)->modify('-' . ($dayOfWeek - 1) . ' days');
$weekDates = [];
for ($i = 0; $i < 7; $i++) {
    $date = (clone $monday)->modify("+{$i} days");
    $weekDates[] = $date->format('Y-m-d');
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token.');
    }
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'add' || $action === 'edit') {
            $mealDate = $_POST['meal_date'] ?? '';
            $mealSlot = $_POST['meal_slot'] ?? '';
            $foodName = trim($_POST['food_name'] ?? '');
            $calories = !empty($_POST['calories']) ? (int)$_POST['calories'] : null;
            $protein = !empty($_POST['protein_g']) ? (float)$_POST['protein_g'] : null;
            $carbs = !empty($_POST['carbs_g']) ? (float)$_POST['carbs_g'] : null;
            $fats = !empty($_POST['fats_g']) ? (float)$_POST['fats_g'] : null;
            $notes = trim($_POST['notes'] ?? '');
            
            if (!$mealDate || !$mealSlot || !$foodName) {
                throw new Exception('Missing required fields.');
            }
            if (!in_array($mealSlot, ['breakfast', 'lunch', 'dinner', 'snacks'])) {
                throw new Exception('Invalid meal slot.');
            }
            
            if ($action === 'add') {
                $stmt = $db->prepare("
                    INSERT INTO meal_plan_items
                    (tenant_id, user_id, meal_date, meal_slot, food_name, calories, protein_g, carbs_g, fats_g, notes)
                    VALUES (:tid, :uid, :date, :slot, :name, :cal, :prot, :carb, :fat, :notes)
                    ON DUPLICATE KEY UPDATE
                    food_name = VALUES(food_name), calories = VALUES(calories),
                    protein_g = VALUES(protein_g), carbs_g = VALUES(carbs_g),
                    fats_g = VALUES(fats_g), notes = VALUES(notes)
                ");
                $stmt->execute([
                    'tid' => $tenantId, 'uid' => $userId, 'date' => $mealDate,
                    'slot' => $mealSlot, 'name' => $foodName, 'cal' => $calories,
                    'prot' => $protein, 'carb' => $carbs, 'fat' => $fats, 'notes' => $notes
                ]);
            } else {
                $itemId = (int)$_POST['meal_plan_item_id'];
                $check = $db->prepare("SELECT meal_plan_item_id FROM meal_plan_items WHERE meal_plan_item_id = :id AND tenant_id = :tid AND user_id = :uid");
                $check->execute(['id' => $itemId, 'tid' => $tenantId, 'uid' => $userId]);
                if (!$check->fetch()) {
                    throw new Exception('Meal not found or access denied.');
                }
                $stmt = $db->prepare("
                    UPDATE meal_plan_items
                    SET food_name = :name, calories = :cal, protein_g = :prot, carbs_g = :carb, fats_g = :fat, notes = :notes
                    WHERE meal_plan_item_id = :id AND tenant_id = :tid AND user_id = :uid
                ");
                $stmt->execute([
                    'name' => $foodName, 'cal' => $calories, 'prot' => $protein,
                    'carb' => $carbs, 'fat' => $fats, 'notes' => $notes,
                    'id' => $itemId, 'tid' => $tenantId, 'uid' => $userId
                ]);
            }
        } elseif ($action === 'delete') {
            $itemId = (int)$_POST['meal_plan_item_id'];
            $stmt = $db->prepare("DELETE FROM meal_plan_items WHERE meal_plan_item_id = :id AND tenant_id = :tid AND user_id = :uid");
            $stmt->execute(['id' => $itemId, 'tid' => $tenantId, 'uid' => $userId]);
        } elseif ($action === 'apply_to_nutrition') {
            $itemId = (int)$_POST['meal_plan_item_id'];
            $targetDate = $_POST['target_date'] ?? date('Y-m-d');
            $stmt = $db->prepare("SELECT * FROM meal_plan_items WHERE meal_plan_item_id = :id AND tenant_id = :tid AND user_id = :uid");
            $stmt->execute(['id' => $itemId, 'tid' => $tenantId, 'uid' => $userId]);
            $meal = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($meal && $meal['calories']) {
                $nutStmt = $db->prepare("
                    INSERT INTO daily_nutrition
                    (tenant_id, user_id, log_date, calories_consumed, protein_g, carbs_g, fats_g)
                    VALUES (:tid, :uid, :date, :cal, :prot, :carb, :fat)
                    ON DUPLICATE KEY UPDATE
                    calories_consumed = calories_consumed + VALUES(calories_consumed),
                    protein_g = protein_g + VALUES(protein_g),
                    carbs_g = carbs_g + VALUES(carbs_g),
                    fats_g = fats_g + VALUES(fats_g)
                ");
                $nutStmt->execute([
                    'tid' => $tenantId, 'uid' => $userId, 'date' => $targetDate,
                    'cal' => $meal['calories'], 'prot' => $meal['protein_g'],
                    'carb' => $meal['carbs_g'], 'fat' => $meal['fats_g']
                ]);
            }
        }
    } catch (Exception $e) {
        error_log('Meal plan error: ' . $e->getMessage());
    }
    header('Location: meal-plans.php');
    exit;
}

// Fetch meals for the current week
$mealsStmt = $db->prepare("
    SELECT * FROM meal_plan_items
    WHERE tenant_id = :tid AND user_id = :uid
    AND meal_date BETWEEN :start AND :end
    ORDER BY meal_date ASC, FIELD(meal_slot, 'breakfast', 'lunch', 'dinner', 'snacks')
");
$mealsStmt->execute(['tid' => $tenantId, 'uid' => $userId, 'start' => $weekDates[0], 'end' => $weekDates[6]]);
$allMeals = $mealsStmt->fetchAll(PDO::FETCH_ASSOC);

$mealsByDate = [];
foreach ($allMeals as $meal) {
    $mealsByDate[$meal['meal_date']][$meal['meal_slot']] = $meal;
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<main class="main-content meal-plans-main">
    <header class="meal-plans-header">
        <h1>Weekly Meal Planner</h1>
        <p class="meal-plans-lede">
            Plan your meals for the week. Add, edit, or delete meals, and apply them to your daily nutrition tracking.
        </p>
    </header>
    <section class="meal-week-grid" aria-label="Weekly meal plan">
        <?php foreach ($weekDates as $index => $date):
            $dateObj = new DateTime($date);
            $dayName = $dateObj->format('l');
            $isToday = $date === $today->format('Y-m-d');
            $slots = ['breakfast', 'lunch', 'dinner', 'snacks'];
        ?>
        <article class="meal-day-card <?= $isToday ? 'today' : '' ?>" aria-labelledby="day-<?= $index ?>">
            <header class="meal-day-header">
                <h2 id="day-<?= $index ?>"><?= $dayName ?></h2>
                <time datetime="<?= $date ?>"><?= $dateObj->format('j M') ?></time>
            </header>
            <?php foreach ($slots as $slot):
                $meal = $mealsByDate[$date][$slot] ?? null;
            ?>
            <div class="meal-slot" data-date="<?= $date ?>" data-slot="<?= $slot ?>">
                <h3 class="meal-slot-label"><?= ucfirst($slot) ?></h3>
                <?php if ($meal): ?>
                    <div class="meal-item">
                        <div class="meal-info">
                            <strong><?= htmlspecialchars($meal['food_name']) ?></strong>
                            <?php if ($meal['calories']): ?>
                                <span class="meal-cals"><?= $meal['calories'] ?> kcal</span>
                            <?php endif; ?>
                            <?php if ($meal['notes']): ?>
                                <p class="meal-notes"><?= htmlspecialchars($meal['notes']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="meal-actions">
                            <button type="button" class="btn-edit-meal" data-meal='<?= htmlspecialchars(json_encode($meal), ENT_QUOTES, 'UTF-8') ?>' aria-label="Edit <?= htmlspecialchars($meal['food_name']) ?>" data-tooltip="Edit this meal's details.">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                            <form method="POST" action="meal-plans.php" class="inline-form">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="meal_plan_item_id" value="<?= $meal['meal_plan_item_id'] ?>">
                                <button type="submit" class="btn-delete-meal" aria-label="Delete <?= htmlspecialchars($meal['food_name']) ?>" onclick="return confirm('Delete this meal?')" data-tooltip="Permanently delete this meal.">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </form>
                            <form method="POST" action="meal-plans.php" class="inline-form">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="action" value="apply_to_nutrition">
                                <input type="hidden" name="meal_plan_item_id" value="<?= $meal['meal_plan_item_id'] ?>">
                                <input type="hidden" name="target_date" value="<?= $date ?>">
                                <button type="submit" class="btn-apply-meal" aria-label="Apply <?= htmlspecialchars($meal['food_name']) ?> to nutrition log" data-tooltip="Add this meal's macros to today's nutrition log.">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <button type="button" class="btn-add-meal" data-date="<?= $date ?>" data-slot="<?= $slot ?>" aria-label="Add <?= $slot ?> meal for <?= $dayName ?>" data-tooltip="Click to add a new meal for this slot.">
                        + Add meal
                    </button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </article>
        <?php endforeach; ?>
    </section>
</main>

<!-- Modal for adding/editing meals -->
<div class="modal-overlay" id="mealModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle" style="display: none;">
    <div class="modal-content meal-modal">
        <header class="modal-header">
            <h2 id="modalTitle">Add Meal</h2>
            <button type="button" class="close-modal" id="closeMealModal" aria-label="Close modal" data-tooltip="Close this window.">&times;</button>
        </header>
        <form method="POST" action="meal-plans.php" id="mealForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="action" id="mealAction" value="add">
            <input type="hidden" name="meal_plan_item_id" id="mealItemId">
            <input type="hidden" name="meal_date" id="mealDate">
            <input type="hidden" name="meal_slot" id="mealSlot">
            
            <div class="form-group">
                <label for="foodName">Food Name</label>
                <input type="text" id="foodName" name="food_name" required maxlength="100" data-tooltip="Enter the name of the food or meal.">
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label for="calories">Calories (kcal)</label>
                    <input type="number" id="calories" name="calories" min="0" step="1" data-tooltip="Total energy content in kilocalories.">
                </div>
                <div class="form-group">
                    <label for="protein">Protein (g)</label>
                    <input type="number" id="protein" name="protein_g" min="0" step="0.1" data-tooltip="Protein content in grams.">
                </div>
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label for="carbs">Carbs (g)</label>
                    <input type="number" id="carbs" name="carbs_g" min="0" step="0.1" data-tooltip="Carbohydrate content in grams.">
                </div>
                <div class="form-group">
                    <label for="fats">Fats (g)</label>
                    <input type="number" id="fats" name="fats_g" min="0" step="0.1" data-tooltip="Fat content in grams.">
                </div>
            </div>
            <div class="form-group">
                <label for="notes">Notes (optional)</label>
                <textarea id="notes" name="notes" rows="2" maxlength="250" data-tooltip="Add any extra details or preparation notes."></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" id="cancelMeal" data-tooltip="Discard changes and close.">Cancel</button>
                <button type="submit" class="btn-primary" data-tooltip="Save this meal to your plan.">Save Meal</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('mealModal');
    const form = document.getElementById('mealForm');
    const closeBtn = document.getElementById('closeMealModal');
    const cancelBtn = document.getElementById('cancelMeal');
    
    // Helper to open modal
    function openModal() {
        modal.style.display = 'flex';
        modal.classList.add('active');
    }
    
    // Helper to close modal
    function closeModal() {
        modal.style.display = 'none';
        modal.classList.remove('active');
    }
    
    // Add meal buttons
    document.querySelectorAll('.btn-add-meal').forEach(btn => {
        btn.addEventListener('click', () => {
            // FIX: Call reset() FIRST, otherwise it wipes the hidden fields we set below!
            form.reset(); 
            document.getElementById('mealAction').value = 'add';
            document.getElementById('mealItemId').value = '';
            document.getElementById('mealDate').value = btn.dataset.date;
            document.getElementById('mealSlot').value = btn.dataset.slot;
            document.getElementById('modalTitle').textContent = `Add ${btn.dataset.slot} meal`;
            openModal();
        });
    });
    
    // Edit meal buttons
    document.querySelectorAll('.btn-edit-meal').forEach(btn => {
        btn.addEventListener('click', () => {
            const meal = JSON.parse(btn.dataset.meal);
            document.getElementById('mealAction').value = 'edit';
            document.getElementById('mealItemId').value = meal.meal_plan_item_id;
            document.getElementById('mealDate').value = meal.meal_date;
            document.getElementById('mealSlot').value = meal.meal_slot;
            document.getElementById('modalTitle').textContent = `Edit ${meal.meal_slot} meal`;
            document.getElementById('foodName').value = meal.food_name;
            document.getElementById('calories').value = meal.calories || '';
            document.getElementById('protein').value = meal.protein_g || '';
            document.getElementById('carbs').value = meal.carbs_g || '';
            document.getElementById('fats').value = meal.fats_g || '';
            document.getElementById('notes').value = meal.notes || '';
            openModal();
        });
    });
    
    // Close modal events
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeModal();
        }
    });
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>