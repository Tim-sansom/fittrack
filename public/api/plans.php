<?php
/**
 * File: plans.php
 * Location: /public/api/plans.php
 *
 * Purpose:
 * RESTful JSON endpoint for the Plans module.
 * - GET: Returns all user plans with their items, activity types, and completion stats.
 * - POST: Handles create_plan, add_item, toggle_status, and delete_plan actions.
 *
 * Architectural Role:
 * Serves as the data layer for the SPA Plans view. It decouples the database logic
 * from the presentation layer, allowing the JavaScript router to fetch and render
 * data dynamically without full page reloads.
 *
 * Security:
 * - Enforces strict session authentication.
 * - All queries are scoped to the authenticated user_id and tenant_id.
 * - Ownership verification is performed before any mutation (UPDATE/DELETE).
 * - Input is validated and sanitized before database interaction.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/Session.php';
require_once __DIR__ . '/../../config/Database.php';

header('Content-Type: application/json; charset=utf-8');
Session::start();

// 1. Authentication Check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorised']);
    exit;
}

$db = Database::getInstance()->getConnection();
$userId = (int)$_SESSION['user_id'];
$tenantId = (int)$_SESSION['tenant_id'];

/**
 * GET: Return all user plans with items and stats.
 */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Fetch all plans for the current user
        $plansStmt = $db->prepare("
            SELECT plan_id, title, start_date, end_date, description, status, created_at
            FROM plans
            WHERE user_id = :uid AND tenant_id = :tid
            ORDER BY start_date DESC
        ");
        $plansStmt->execute(['uid' => $userId, 'tid' => $tenantId]);
        $plans = $plansStmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch available activity types for the dropdown
        $typesStmt = $db->prepare("
            SELECT activity_type_id, name 
            FROM activity_types 
            WHERE tenant_id = :tid AND is_published = 1 
            ORDER BY name
        ");
        $typesStmt->execute(['tid' => $tenantId]);
        $activityTypes = $typesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Enrich each plan with its items and calculated stats
        foreach ($plans as &$plan) {
            $itemsStmt = $db->prepare("
                SELECT pi.plan_item_id, pi.planned_date, pi.planned_duration_minutes,
                       pi.notes, pi.completion_status, at.name as type_name
                FROM plan_items pi
                JOIN activity_types at ON pi.activity_type_id = at.activity_type_id AND pi.tenant_id = at.tenant_id
                WHERE pi.plan_id = :pid AND pi.tenant_id = :tid
                ORDER BY pi.planned_date ASC
            ");
            $itemsStmt->execute(['pid' => $plan['plan_id'], 'tid' => $tenantId]);
            $plan['items'] = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate progress stats
            $total = count($plan['items']);
            $completed = count(array_filter($plan['items'], fn($i) => $i['completion_status'] === 'completed'));
            $plan['stats'] = [
                'total' => $total,
                'completed' => $completed,
                'progress' => $total > 0 ? round(($completed / $total) * 100, 1) : 0
            ];
        }
        unset($plan); // Break reference

        echo json_encode([
            'success' => true,
            'data' => [
                'plans' => $plans,
                'activity_types' => $activityTypes
            ]
        ], JSON_UNESCAPED_UNICODE);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

/**
 * POST: Handle plan mutations.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON payload']);
        exit;
    }

    $action = $input['action'] ?? '';

    try {
        if ($action === 'create_plan') {
            $title = trim($input['title'] ?? '');
            $startDate = $input['start_date'] ?? '';
            $endDate = $input['end_date'] ?? '';
            $description = trim($input['description'] ?? '');

            if (!$title || !$startDate || !$endDate || strtotime($endDate) < strtotime($startDate)) {
                echo json_encode(['success' => false, 'error' => 'Invalid plan details. Ensure end date is after start date.']);
                exit;
            }

            $stmt = $db->prepare("
                INSERT INTO plans (tenant_id, user_id, title, start_date, end_date, description, status)
                VALUES (:tid, :uid, :title, :start, :end, :desc, 'active')
            ");
            $stmt->execute([
                'tid' => $tenantId, 'uid' => $userId,
                'title' => $title, 'start' => $startDate, 'end' => $endDate, 'desc' => $description
            ]);
            echo json_encode(['success' => true, 'plan_id' => $db->lastInsertId()]);

        } elseif ($action === 'add_item') {
            $planId = (int)($input['plan_id'] ?? 0);
            $activityTypeId = (int)($input['activity_type_id'] ?? 0);
            $plannedDate = $input['planned_date'] ?? '';
            $duration = (int)($input['duration'] ?? 0);
            $notes = trim($input['notes'] ?? '');

            // Verify plan ownership
            $checkStmt = $db->prepare("SELECT plan_id FROM plans WHERE plan_id = :pid AND user_id = :uid AND tenant_id = :tid");
            $checkStmt->execute(['pid' => $planId, 'uid' => $userId, 'tid' => $tenantId]);
            if (!$checkStmt->fetch()) {
                echo json_encode(['success' => false, 'error' => 'Plan not found or access denied']);
                exit;
            }

            $stmt = $db->prepare("
                INSERT INTO plan_items (tenant_id, plan_id, activity_type_id, user_id, planned_date, planned_duration_minutes, notes, completion_status)
                VALUES (:tid, :pid, :aid, :uid, :date, :dur, :notes, 'planned')
            ");
            $stmt->execute([
                'tid' => $tenantId, 'pid' => $planId, 'aid' => $activityTypeId, 'uid' => $userId,
                'date' => $plannedDate, 'dur' => $duration, 'notes' => $notes
            ]);
            echo json_encode(['success' => true]);

        } elseif ($action === 'toggle_status') {
            $itemId = (int)($input['item_id'] ?? 0);
            $newStatus = $input['status'] ?? 'planned';

            if (!in_array($newStatus, ['planned', 'completed', 'skipped'])) {
                echo json_encode(['success' => false, 'error' => 'Invalid status value']);
                exit;
            }

            // Verify item ownership
            $checkStmt = $db->prepare("SELECT plan_item_id FROM plan_items WHERE plan_item_id = :iid AND user_id = :uid AND tenant_id = :tid");
            $checkStmt->execute(['iid' => $itemId, 'uid' => $userId, 'tid' => $tenantId]);
            if (!$checkStmt->fetch()) {
                echo json_encode(['success' => false, 'error' => 'Item not found or access denied']);
                exit;
            }

            $stmt = $db->prepare("UPDATE plan_items SET completion_status = :status WHERE plan_item_id = :iid");
            $stmt->execute(['status' => $newStatus, 'iid' => $itemId]);
            echo json_encode(['success' => true]);

        } elseif ($action === 'delete_plan') {
            $planId = (int)($input['plan_id'] ?? 0);

            // Verify plan ownership before deleting (cascades to items via DB schema)
            $stmt = $db->prepare("DELETE FROM plans WHERE plan_id = :pid AND user_id = :uid AND tenant_id = :tid");
            $stmt->execute(['pid' => $planId, 'uid' => $userId, 'tid' => $tenantId]);
            echo json_encode(['success' => true]);

        } else {
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);