<?php
/**
 * File: plans.php
 * Location: /public/plans.php
 *
 * Purpose:
 * Provides the interface for users to build, manage, and track training plans.
 * Demonstrates complex relational data handling: creating a parent 'plan' record
 * and associating multiple child 'plan_item' records with specific activity types.
 *
 * Architectural Role:
 * Serves as the primary MPA controller for the planning module. It handles
 * both the rendering of the diary view and the processing of form submissions
 * for creating plans and logging scheduled activities.
 *
 * Security & Multi-Tenancy:
 * - Enforces strict ownership checks: users can only view/modify their own plans.
 * - All database writes and reads are strictly scoped to the authenticated 
 *   tenant_id to prevent cross-tenant data leakage.
 * - Validates CSRF tokens on all state-changing POST requests to prevent 
 *   Cross-Site Request Forgery attacks.
 * - Uses prepared statements for all SQL queries to prevent SQL injection.
 *
 * Assignment Context (COM6011):
 * Demonstrates LO3 by combining server-side PHP/SQL with client-side HTML forms.
 * Implements the Post/Redirect/Get (PRG) pattern to prevent form resubmission.
 */
declare(strict_types=1);

// =============================================================================
// 1. AUTHENTICATION & SESSION SETUP
// =============================================================================
require_once __DIR__ . '/../config/Session.php';
require_once __DIR__ . '/../config/Database.php';
Session::start();

// Redirect unauthenticated users to the login page immediately.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$userId = (int)$_SESSION['user_id'];
$tenantId = (int)($_SESSION['tenant_id'] ?? 0);

// =============================================================================
// RESILIENCE: Multi-Tenant Session Safety Net
// =============================================================================
// In a production environment, session data can occasionally be lost. 
// This safety net ensures that if the tenant_id is missing from the session, 
// we fetch it from the database before any queries run. This prevents the 
// "empty dropdown" issue where queries run with tenant_id = 0.
if ($userId > 0 && $tenantId === 0) {
    $tenantStmt = $db->prepare("
        SELECT tenant_id FROM tenant_memberships 
        WHERE user_id = :uid AND membership_status = 'active' 
        LIMIT 1
    ");
    $tenantStmt->execute(['uid' => $userId]);
    $tenantRow = $tenantStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($tenantRow) {
        $tenantId = (int)$tenantRow['tenant_id'];
        $_SESSION['tenant_id'] = $tenantId; // Restore to session
    }
}

$pageTitle = 'My Plans';
$currentPage = 'plans';
$message = '';
$messageType = '';

// =============================================================================
// 2. HANDLE FORM SUBMISSIONS (POST REQUESTS)
// =============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Security: CSRF Validation using timing-safe comparison to prevent token guessing.
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token. Request aborted.');
    }

    $action = $_POST['action'] ?? '';

    try {
        // --- ACTION: Create a new training plan ---
        if ($action === 'create_plan') {
            $title = trim($_POST['title'] ?? '');
            $startDate = $_POST['start_date'] ?? '';
            $endDate = $_POST['end_date'] ?? '';
            $description = trim($_POST['description'] ?? '');

            // Basic server-side validation
            if ($title && $startDate && $endDate && strtotime($endDate) >= strtotime($startDate)) {
                $stmt = $db->prepare("
                    INSERT INTO plans (tenant_id, user_id, title, start_date, end_date, description, status)
                    VALUES (:tid, :uid, :title, :start, :end, :desc, 'active')
                ");
                $stmt->execute([
                    'tid' => $tenantId, 'uid' => $userId,
                    'title' => $title, 'start' => $startDate, 'end' => $endDate, 'desc' => $description
                ]);
                $message = 'Plan created successfully.';
                $messageType = 'success';
            } else {
                $message = 'Invalid plan details. Ensure end date is after start date.';
                $messageType = 'error';
            }
        }
        
        // --- ACTION: Add an activity item to an existing plan ---
        elseif ($action === 'add_item') {
            $planId = (int)$_POST['plan_id'] ?? 0;
            $activityTypeId = (int)$_POST['activity_type_id'] ?? 0;
            $plannedDate = $_POST['planned_date'] ?? '';
            $duration = (int)$_POST['duration'] ?? 0;
            $notes = trim($_POST['notes'] ?? '');

            // Security: Verify plan ownership before allowing items to be added.
            // This prevents a user from adding items to another user's plan.
            $checkStmt = $db->prepare("SELECT plan_id FROM plans WHERE plan_id = :pid AND user_id = :uid AND tenant_id = :tid");
            $checkStmt->execute(['pid' => $planId, 'uid' => $userId, 'tid' => $tenantId]);
            
            if ($checkStmt->fetch() && $activityTypeId > 0 && $plannedDate) {
                $stmt = $db->prepare("
                    INSERT INTO plan_items (tenant_id, plan_id, activity_type_id, user_id, planned_date, planned_duration_minutes, notes, completion_status)
                    VALUES (:tid, :pid, :aid, :uid, :date, :dur, :notes, 'planned')
                ");
                $stmt->execute([
                    'tid' => $tenantId, 'pid' => $planId, 'aid' => $activityTypeId, 'uid' => $userId,
                    'date' => $plannedDate, 'dur' => $duration, 'notes' => $notes
                ]);
                $message = 'Activity added to plan.';
                $messageType = 'success';
            } else {
                $message = 'Error adding activity. Check plan ownership and input.';
                $messageType = 'error';
            }
        }
        
        // --- ACTION: Update the completion status of a plan item ---
        elseif ($action === 'update_status') {
            $itemId = (int)$_POST['item_id'] ?? 0;
            $newStatus = $_POST['status'] ?? 'planned';

            // Security: Verify item ownership before updating.
            $checkStmt = $db->prepare("SELECT plan_item_id FROM plan_items WHERE plan_item_id = :iid AND user_id = :uid AND tenant_id = :tid");
            $checkStmt->execute(['iid' => $itemId, 'uid' => $userId, 'tid' => $tenantId]);

            if ($checkStmt->fetch() && in_array($newStatus, ['planned', 'completed', 'skipped'])) {
                $stmt = $db->prepare("UPDATE plan_items SET completion_status = :status WHERE plan_item_id = :iid");
                $stmt->execute(['status' => $newStatus, 'iid' => $itemId]);
                $message = 'Status updated.';
                $messageType = 'success';
            }
        }
        
        // --- ACTION: Delete a plan (Cascades to items via DB schema) ---
        elseif ($action === 'delete_plan') {
            $planId = (int)$_POST['plan_id'] ?? 0;
            
            // Security: Verify ownership before deletion.
            $stmt = $db->prepare("DELETE FROM plans WHERE plan_id = :pid AND user_id = :uid AND tenant_id = :tid");
            $stmt->execute(['pid' => $planId, 'uid' => $userId, 'tid' => $tenantId]);
            $message = 'Plan deleted.';
            $messageType = 'success';
        }
    } catch (PDOException $e) {
        $message = 'Database error: ' . $e->getMessage();
        $messageType = 'error';
    }

    // Post/Redirect/Get (PRG) Pattern: Redirect to prevent form resubmission on refresh.
    header('Location: plans.php?msg=' . urlencode($message) . '&type=' . $messageType);
    exit;
}

// Handle GET messages passed from the PRG redirect.
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $messageType = $_GET['type'] ?? 'success';
}

// =============================================================================
// 3. FETCH DATA FOR RENDERING (GET REQUEST)
// =============================================================================

// Fetch all plans for the current user, strictly scoped by tenant_id.
$plansStmt = $db->prepare("
    SELECT * FROM plans
    WHERE user_id = :uid AND tenant_id = :tid
    ORDER BY start_date DESC
");
$plansStmt->execute(['uid' => $userId, 'tid' => $tenantId]);
$plans = $plansStmt->fetchAll(PDO::FETCH_ASSOC);

// =============================================================================
// CRITICAL FIX: Use a UNIQUE variable name ($planActivityTypes) 
// to prevent scope collision with header.php or sidebar.php.
// =============================================================================
$planActivityTypesStmt = $db->prepare("SELECT activity_type_id, name FROM activity_types WHERE tenant_id = :tid AND is_published = 1 ORDER BY name");
$planActivityTypesStmt->execute(['tid' => $tenantId]);
$planActivityTypes = $planActivityTypesStmt->fetchAll(PDO::FETCH_ASSOC);

// Include the global layout components.
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<!-- Main Content Area -->
<div class="main-content">
    
    <!-- Alert Messages (Success/Error feedback) -->
    <?php if ($message): ?>
    <!-- Accessibility: Added role="alert" and high-contrast hex colors to pass WAVE -->
    <div class="alert alert-<?= htmlspecialchars($messageType) ?>" role="alert" style="padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; background: <?= $messageType === 'success' ? '#d4edda' : '#f8d7da' ?>; color: <?= $messageType === 'success' ? '#155724' : '#721c24' ?>; border: 1px solid <?= $messageType === 'success' ? '#c3e6cb' : '#f5c6cb' ?>;">
        <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <!-- Section 1: Create New Plan Form -->
    <div class="card">
        <h2>Create New Training Plan</h2>
        <form method="POST" action="plans.php" style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1rem; align-items: end;">
            <!-- CSRF Token for security -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="action" value="create_plan">
            
            <!-- Accessibility: All labels explicitly linked to inputs via 'for' attribute -->
            <div class="form-group" style="margin-bottom: 0;">
                <label for="plan_title" style="color: #222;">Plan Title</label>
                <input type="text" id="plan_title" name="title" required placeholder="e.g. 12-Week Marathon Prep" style="color: #222;">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="start_date" style="color: #222;">Start Date</label>
                <input type="date" id="start_date" name="start_date" required style="color: #222;">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="end_date" style="color: #222;">End Date</label>
                <input type="date" id="end_date" name="end_date" required style="color: #222;">
            </div>
            <div style="grid-column: 1 / -1; margin-top: 1rem;">
                <button type="submit" class="btn-primary">Create Plan</button>
            </div>
        </form>
    </div>

    <!-- Section 2: Existing Plans List -->
    <?php if (empty($plans)): ?>
        <div class="card" style="text-align: center; padding: 3rem; opacity: 0.7;">
            <p style="color: #222;">You have no active training plans. Create one above to get started.</p>
        </div>
    <?php else: ?>
        <?php foreach ($plans as $plan): ?>
        <div class="card" style="margin-bottom: 2rem; border-left: 4px solid var(--accent);">
            
            <!-- Plan Header and Delete Action -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                <div>
                    <h3 style="margin: 0 0 0.5rem 0; color: #222;"><?= htmlspecialchars($plan['title']) ?></h3>
                    <p style="margin: 0; font-size: 0.9rem; color: #555;">
                        <?= date('d M Y', strtotime($plan['start_date'])) ?> to <?= date('d M Y', strtotime($plan['end_date'])) ?>
                        <span style="margin-left: 1rem; padding: 0.2rem 0.6rem; background: var(--bg); border-radius: 12px; font-size: 0.75rem; text-transform: uppercase;">
                            <?= htmlspecialchars($plan['status']) ?>
                        </span>
                    </p>
                </div>
                <form method="POST" action="plans.php" style="margin: 0;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <input type="hidden" name="action" value="delete_plan">
                    <input type="hidden" name="plan_id" value="<?= $plan['plan_id'] ?>">
                    <button type="submit" class="btn-danger" onclick="return confirm('Delete this plan and all its scheduled activities?')" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Delete Plan</button>
                </form>
            </div>

            <!-- Add Activity to this Plan Form -->
            <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <h4 style="margin: 0 0 1rem 0; font-size: 1rem; color: #222;">Schedule Activity</h4>
                <form method="POST" action="plans.php" style="display: grid; grid-template-columns: 2fr 1fr 1fr 2fr auto; gap: 1rem; align-items: end;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <input type="hidden" name="action" value="add_item">
                    <input type="hidden" name="plan_id" value="<?= $plan['plan_id'] ?>">
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <!-- Accessibility: Label explicitly linked to select via 'for' attribute -->
                        <label for="activity_type_<?= $plan['plan_id'] ?>" style="color: #222;">Activity Type</label>
                        <select name="activity_type_id" id="activity_type_<?= $plan['plan_id'] ?>" required style="color: #222; background: #fff;">
                            <option value="">Select...</option>
                            <?php foreach ($planActivityTypes as $type): ?>
                                <option value="<?= $type['activity_type_id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="planned_date_<?= $plan['plan_id'] ?>" style="color: #222;">Date</label>
                        <input type="date" id="planned_date_<?= $plan['plan_id'] ?>" name="planned_date" required min="<?= $plan['start_date'] ?>" max="<?= $plan['end_date'] ?>" style="color: #222;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="duration_<?= $plan['plan_id'] ?>" style="color: #222;">Mins</label>
                        <input type="number" id="duration_<?= $plan['plan_id'] ?>" name="duration" min="1" required placeholder="60" style="color: #222;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="notes_<?= $plan['plan_id'] ?>" style="color: #222;">Notes (Optional)</label>
                        <input type="text" id="notes_<?= $plan['plan_id'] ?>" name="notes" placeholder="e.g. Easy pace" style="color: #222;">
                    </div>
                    <button type="submit" class="btn-primary" style="padding: 0.85rem 1rem;">Add</button>
                </form>
            </div>

            <!-- Plan Items List (Scheduled Activities) -->
            <?php
            // Fetch items for this specific plan, joining with activity_types to get the name.
            $itemsStmt = $db->prepare("
                SELECT pi.*, at.name as type_name
                FROM plan_items pi
                JOIN activity_types at ON pi.activity_type_id = at.activity_type_id AND pi.tenant_id = at.tenant_id
                WHERE pi.plan_id = :pid AND pi.tenant_id = :tid
                ORDER BY pi.planned_date ASC
            ");
            $itemsStmt->execute(['pid' => $plan['plan_id'], 'tid' => $tenantId]);
            $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <?php if (empty($items)): ?>
                <p style="opacity: 0.6; font-style: italic; color: #444;">No activities scheduled yet.</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <?php foreach ($items as $item):
                        // Determine border color based on completion status for visual feedback.
                        // FIX: Used high-contrast hex colors to pass WAVE accessibility tests.
                        $statusColor = match($item['completion_status']) {
                            'completed' => '#1b5e20', // Dark Green
                            'skipped' => '#b71c1c',   // Dark Red
                            default => '#222'         // Dark Grey
                        };
                        $statusBg = match($item['completion_status']) {
                            'completed' => '#e8f5e9', // Light Green bg
                            'skipped' => '#ffebee',   // Light Red bg
                            default => '#f5f5f5'      // Light Grey bg
                        };
                    ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #fff; border: 1px solid #ddd; border-radius: 8px; border-left: 5px solid <?= $statusColor ?>;">
                        <div style="flex-grow: 1;">
                            <div style="font-weight: 600; color: #222;"><?= htmlspecialchars($item['type_name']) ?></div>
                            <div style="font-size: 0.85rem; color: #555;">
                                <?= date('D, d M Y', strtotime($item['planned_date'])) ?>
                                • <?= $item['planned_duration_minutes'] ?> mins
                                <?php if (!empty($item['notes'])): ?> • <?= htmlspecialchars($item['notes']) ?><?php endif; ?>
                            </div>
                        </div>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <!-- Status Badge with High Contrast -->
                            <span style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.85rem; font-weight: bold; color: <?= $statusColor ?>; background: <?= $statusBg ?>; text-transform: uppercase;">
                                <?= htmlspecialchars($item['completion_status']) ?>
                            </span>
                            
                            <!-- Status Update Form -->
                            <!-- Accessibility: Uses a proper submit button instead of onchange="this.form.submit()" 
                                 to comply with WCAG guidelines and avoid "JavaScript jump menu" errors. -->
                            <form method="POST" action="plans.php" style="display: inline-flex; gap: 0.5rem; align-items: center; margin: 0;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="item_id" value="<?= $item['plan_item_id'] ?>">
                                
                                <!-- sr-only class hides the label visually but keeps it accessible to screen readers. -->
                                <label for="status_<?= $item['plan_item_id'] ?>" class="sr-only">Change status for <?= htmlspecialchars($item['type_name']) ?></label>
                                <select name="status" id="status_<?= $item['plan_item_id'] ?>" style="padding: 0.4rem; font-size: 0.85rem; border-radius: 4px; border: 1px solid var(--border); color: #222; background: #fff;">
                                    <option value="planned" <?= $item['completion_status'] === 'planned' ? 'selected' : '' ?>>Planned</option>
                                    <option value="completed" <?= $item['completion_status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="skipped" <?= $item['completion_status'] === 'skipped' ? 'selected' : '' ?>>Skipped</option>
                                </select>
                                <button type="submit" class="btn-primary" style="padding: 0.4rem 0.75rem; font-size: 0.8rem;">Update</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>