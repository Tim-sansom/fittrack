<?php
/**
 * File: activities.php
 * Location: /public/api/activities.php
 * 
 * Purpose:
 * Centralised CRUD controller for the activity_logs table.
 * 
 * Architectural Role:
 * Handles all HTTP methods (GET, POST, PUT, DELETE) for activity management.
 * It bridges the SPA frontend with the relational database, returning JSON.
 * 
 * Security:
 * - Strict Multi-Tenant Isolation: Every query explicitly binds `tenant_id` 
 *   and `user_id`. This prevents Insecure Direct Object Reference (IDOR) 
 *   attacks where a user might try to manipulate another user's records.
 * - Ownership Verification: The PUT method performs a preliminary check to 
 *   ensure the user owns the record before attempting an update.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/Session.php';
require_once __DIR__ . '/../../config/Database.php';

Session::start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorised']);
    exit;
}

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance()->getConnection();

// Enforce multi-tenant isolation context
$tenantId = (int)$_SESSION['tenant_id'];
$userId = (int)$_SESSION['user_id'];

try {
    if ($method === 'GET') {
        // READ: Fetch the 20 most recent activities for the current user.
        $stmt = $db->prepare("
            SELECT al.activity_log_id, al.activity_date, al.title, al.duration_minutes, 
                   al.distance_km, al.intensity, at.name as type_name, 
                   at.slug as type_slug, al.activity_type_id
            FROM activity_logs al
            JOIN activity_types at ON al.activity_type_id = at.activity_type_id
            WHERE al.tenant_id = :tid AND al.user_id = :uid
            ORDER BY al.activity_date DESC
            LIMIT 20
        ");
        $stmt->execute(['tid' => $tenantId, 'uid' => $userId]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        
    } elseif ($method === 'POST') {
        // CREATE: Insert a new activity log.
        $input = json_decode(file_get_contents('php://input'), true);
        
        $typeId = (int)($input['activity_type_id'] ?? 0);
        $date = $input['activity_date'] ?? date('Y-m-d');
        $title = trim($input['title'] ?? '');
        $duration = !empty($input['duration_minutes']) ? (int)$input['duration_minutes'] : null;
        $distance = !empty($input['distance_km']) ? (float)$input['distance_km'] : null;

        if (!$typeId || !$title) {
            throw new Exception('Missing required fields');
        }

        $stmt = $db->prepare("
            INSERT INTO activity_logs (tenant_id, user_id, activity_type_id, activity_date, title, duration_minutes, distance_km, visibility)
            VALUES (:tid, :uid, :type_id, :date, :title, :duration, :distance, 'private')
        ");
        $stmt->execute([
            'tid' => $tenantId, 'uid' => $userId, 'type_id' => $typeId,
            'date' => $date, 'title' => $title, 'duration' => $duration, 'distance' => $distance
        ]);

        echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);

    } elseif ($method === 'PUT') {
        // UPDATE: Modify an existing activity.
        $input = json_decode(file_get_contents('php://input'), true);
        $logId = (int)($input['activity_log_id'] ?? 0);
        
        if (!$logId) throw new Exception('Missing ID');

        // Ownership check: Verify the record belongs to this user and tenant before updating.
        $check = $db->prepare("SELECT activity_log_id FROM activity_logs WHERE activity_log_id = :id AND tenant_id = :tid AND user_id = :uid");
        $check->execute(['id' => $logId, 'tid' => $tenantId, 'uid' => $userId]);
        if (!$check->fetch()) throw new Exception('Not found or unauthorised');

        $typeId = (int)($input['activity_type_id'] ?? 0);
        $date = $input['activity_date'] ?? date('Y-m-d');
        $title = trim($input['title'] ?? '');
        $duration = !empty($input['duration_minutes']) ? (int)$input['duration_minutes'] : null;
        $distance = !empty($input['distance_km']) ? (float)$input['distance_km'] : null;

        $stmt = $db->prepare("
            UPDATE activity_logs 
            SET activity_type_id = :type_id, activity_date = :date, title = :title, 
                duration_minutes = :duration, distance_km = :distance 
            WHERE activity_log_id = :id AND tenant_id = :tid AND user_id = :uid
        ");
        $stmt->execute([
            'type_id' => $typeId, 'date' => $date, 'title' => $title, 
            'duration' => $duration, 'distance' => $distance, 
            'id' => $logId, 'tid' => $tenantId, 'uid' => $userId
        ]);

        echo json_encode(['success' => true]);

    } elseif ($method === 'DELETE') {
        // DELETE: Remove an activity.
        $input = json_decode(file_get_contents('php://input'), true);
        $logId = (int)($input['activity_log_id'] ?? 0);

        if (!$logId) throw new Exception('Invalid ID');

        // Scoping the DELETE statement prevents accidental deletion of other users' data.
        $stmt = $db->prepare("DELETE FROM activity_logs WHERE activity_log_id = :id AND tenant_id = :tid AND user_id = :uid");
        $stmt->execute(['id' => $logId, 'tid' => $tenantId, 'uid' => $userId]);

        echo json_encode(['success' => true, 'deleted' => $stmt->rowCount() > 0]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}