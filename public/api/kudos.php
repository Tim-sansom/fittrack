<?php
/**
 * File: kudos.php
 * Location: /public/api/kudos.php
 *
 * Purpose:
 * RESTful endpoint for the Community module.
 * - GET: Returns the community activity feed, weekly leaderboard, and tenant goal progress.
 * - POST: Records or removes a kudos (like) on a specific activity log.
 *
 * Architectural Role:
 * Serves as the data layer for the Community Hub. The GET handler aggregates
 * public activity logs, kudos counts, and user totals for the leaderboard.
 * The POST handler implements a toggle mechanism with optimistic UI support.
 *
 * Security:
 * - All queries scoped to the authenticated tenant_id and user_id.
 * - Kudos writes validated against session ownership.
 * - Input strictly cast to integers to prevent SQL injection via type juggling.
 * - Visibility filters ensure private activities don't leak into public feeds.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/Session.php';
require_once __DIR__ . '/../../config/Database.php';

// Force JSON response header for the SPA-like fetch request
header('Content-Type: application/json; charset=utf-8');

Session::start();

// Reject unauthenticated requests immediately
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit(json_encode(['success' => false, 'error' => 'Unauthorised']));
}

$db = Database::getInstance()->getConnection();
$userId = (int)$_SESSION['user_id']; // Cast to int for strict type safety
$tenantId = (int)$_SESSION['tenant_id'];

/**
 * GET: Return community activity feed + weekly leaderboard + tenant goal.
 * 
 * Returns a structured JSON response containing:
 * - feed: Recent public/tenant activities with kudos counts
 * - leaderboard: Top 5 users by total minutes this week
 * - tenant_total_minutes: Current month's aggregate minutes
 * - tenant_goal_minutes: Target goal (10,000 minutes)
 */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // 1. Recent public/tenant activity logs with kudos counts
        // Uses LEFT JOIN to include activities with zero kudos
        // Filters by visibility to prevent private activity leakage
        $feedStmt = $db->prepare("
            SELECT 
                al.activity_log_id,
                al.title,
                al.activity_date,
                al.duration_minutes,
                al.distance_km,
                al.visibility,
                u.display_name,
                u.user_id AS author_user_id,
                at.name AS activity_type_name,
                COUNT(k.kudos_id) AS kudos_count
            FROM activity_logs al
            JOIN users u ON u.user_id = al.user_id
            JOIN activity_types at 
                ON at.tenant_id = al.tenant_id 
                AND at.activity_type_id = al.activity_type_id
            LEFT JOIN kudos k ON k.activity_log_id = al.activity_log_id
            WHERE al.tenant_id = :tid
              AND al.visibility IN ('tenant', 'public')
            GROUP BY al.activity_log_id
            ORDER BY al.activity_date DESC, al.created_at DESC
            LIMIT 20
        ");
        $feedStmt->execute(['tid' => $tenantId]);
        $feed = $feedStmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Weekly leaderboard: top 5 users by total minutes this week
        // Aggregates duration for all visible activities in the last 7 days
        $leaderStmt = $db->prepare("
            SELECT 
                u.display_name,
                u.user_id,
                SUM(al.duration_minutes) AS total_minutes
            FROM activity_logs al
            JOIN users u ON u.user_id = al.user_id
            WHERE al.tenant_id = :tid
              AND al.activity_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
              AND al.visibility IN ('tenant', 'public')
            GROUP BY u.user_id
            ORDER BY total_minutes DESC
            LIMIT 5
        ");
        $leaderStmt->execute(['tid' => $tenantId]);
        $leaderboard = $leaderStmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Tenant-wide total minutes this month (for the goal banner)
        // Calculates aggregate from the first day of the current month
        $goalStmt = $db->prepare("
            SELECT COALESCE(SUM(duration_minutes), 0) AS total_mins
            FROM activity_logs
            WHERE tenant_id = :tid
              AND activity_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
              AND visibility IN ('tenant', 'public')
        ");
        $goalStmt->execute(['tid' => $tenantId]);
        $tenantTotal = (int)$goalStmt->fetchColumn();

        // Return structured response matching SPA expectations
        echo json_encode([
            'success' => true,
            'data' => [
                'feed' => $feed,
                'leaderboard' => $leaderboard,
                'tenant_total_minutes' => $tenantTotal,
                'tenant_goal_minutes' => 10000
            ]
        ], JSON_UNESCAPED_UNICODE);

    } catch (PDOException $e) {
        http_response_code(500);
        error_log('Kudos API GET error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Database query failed']);
    }
    exit;
}

/**
 * POST: Record or remove a kudos on an activity log.
 * 
 * Implements a toggle mechanism:
 * - If the user has already kudos'd the activity, remove it (unlike)
 * - If the user hasn't kudos'd the activity, add it (like)
 * 
 * Returns the new kudos count for optimistic UI updates.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!is_array($input)) {
        http_response_code(400);
        exit(json_encode(['success' => false, 'error' => 'Invalid JSON payload']));
    }

    $activityId = (int)($input['activity_log_id'] ?? 0);
    
    if ($activityId <= 0) {
        http_response_code(400);
        exit(json_encode(['success' => false, 'error' => 'Invalid activity ID']));
    }

    try {
        // Verify the activity exists and belongs to this tenant
        // Prevents users from kudos-ing activities from other tenants
        $checkStmt = $db->prepare("
            SELECT activity_log_id 
            FROM activity_logs 
            WHERE activity_log_id = :id AND tenant_id = :tid
        ");
        $checkStmt->execute(['id' => $activityId, 'tid' => $tenantId]);
        
        if (!$checkStmt->fetch()) {
            http_response_code(404);
            exit(json_encode(['success' => false, 'error' => 'Activity not found']));
        }

        // Toggle Logic:
        // Check if the relationship exists. If it does, DELETE it (unlike).
        // If it doesn't, INSERT it (like). This is cleaner than INSERT IGNORE
        // because it allows us to easily track the action taken for the response.
        $stmt = $db->prepare("
            SELECT kudos_id 
            FROM kudos 
            WHERE user_id = :uid AND activity_log_id = :aid
        ");
        $stmt->execute(['uid' => $userId, 'aid' => $activityId]);
        $exists = $stmt->fetch();
        
        if ($exists) {
            // Remove kudos (unlike)
            $db->prepare("DELETE FROM kudos WHERE kudos_id = :id")
               ->execute(['id' => $exists['kudos_id']]);
            $action = 'removed';
        } else {
            // Add kudos (like)
            $db->prepare("INSERT INTO kudos (user_id, activity_log_id) VALUES (:uid, :aid)")
               ->execute(['uid' => $userId, 'aid' => $activityId]);
            $action = 'added';
        }

        // Fetch the new total count to send back to the client
        // This enables optimistic UI updates without a full page reload
        $countStmt = $db->prepare("
            SELECT COUNT(*) 
            FROM kudos 
            WHERE activity_log_id = :aid
        ");
        $countStmt->execute(['aid' => $activityId]);
        $newCount = (int)$countStmt->fetchColumn();

        echo json_encode([
            'success' => true, 
            'action' => $action, 
            'count' => $newCount
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        error_log('Kudos API POST error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Database operation failed']);
    }
    exit;
}

// Neither GET nor POST
http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);