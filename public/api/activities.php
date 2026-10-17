<?php
/**
 * File: activities.php
 * Location: /public/api/activities.php
 *
 * Purpose:
 * Centralised CRUD controller for the activity_logs table.
 * Handles JSON API requests (SPA) and standard Form submissions (MPA).
 */
declare(strict_types=1);
require_once __DIR__ . '/../../config/Session.php';
require_once __DIR__ . '/../../config/Database.php';
Session::start();

if (!isset($_SESSION['user_id'])) {
    // If it's a form submission, redirect to login
    if (isset($_POST['action'])) {
        header('Location: ../login.php');
        exit;
    }
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorised']);
    exit;
}

$db = Database::getInstance()->getConnection();
$tenantId = (int)$_SESSION['tenant_id'];
$userId = (int)$_SESSION['user_id'];

// =====================================================================
// MPA FORM HANDLER (For Edit/Delete buttons on workouts.php)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'delete') {
        $logId = (int)$_POST['activity_log_id'];
        $stmt = $db->prepare("DELETE FROM activity_logs WHERE activity_log_id = :id AND tenant_id = :tid AND user_id = :uid");
        $stmt->execute(['id' => $logId, 'tid' => $tenantId, 'uid' => $userId]);
        header('Location: ../workouts.php?msg=deleted');
        exit;
    } 
    elseif ($action === 'update') {
        $logId = (int)$_POST['activity_log_id'];
        $newDuration = (int)$_POST['duration_minutes'];
        
        // Ownership check
        $check = $db->prepare("SELECT activity_log_id FROM activity_logs WHERE activity_log_id = :id AND tenant_id = :tid AND user_id = :uid");
        $check->execute(['id' => $logId, 'tid' => $tenantId, 'uid' => $userId]);
        
        if ($check->fetch()) {
            $stmt = $db->prepare("UPDATE activity_logs SET duration_minutes = :dur WHERE activity_log_id = :id");
            $stmt->execute(['dur' => $newDuration, 'id' => $logId]);
        }
        header('Location: ../workouts.php?msg=updated');
        exit;
    }
}

// =====================================================================
// SPA JSON API HANDLER
// =====================================================================
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

// For JSON requests, decode the body. For form requests, use $_POST.
$input = json_decode(file_get_contents('php://input'), true);
if (!$input && $method !== 'GET') {
    $input = $_POST;
}

try {
    if ($method === 'GET') {
        $stmt = $db->prepare("
            SELECT al.activity_log_id, al.activity_date, al.title, al.duration_minutes,
            al.distance_km, al.intensity, at.name as type_name
            FROM activity_logs al
            JOIN activity_types at ON al.activity_type_id = at.activity_type_id
            WHERE al.tenant_id = :tid AND al.user_id = :uid
            ORDER BY al.activity_date DESC LIMIT 20
        ");
        $stmt->execute(['tid' => $tenantId, 'uid' => $userId]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        
    } elseif ($method === 'POST' && !isset($input['action'])) {
        // Create new activity (SPA)
        $typeId = (int)($input['activity_type_id'] ?? 0);
        $date = $input['activity_date'] ?? date('Y-m-d');
        $title = trim($input['title'] ?? '');
        $duration = !empty($input['duration_minutes']) ? (int)$input['duration_minutes'] : null;
        
        if (!$typeId || !$title) throw new Exception('Missing required fields');
        
        $stmt = $db->prepare("INSERT INTO activity_logs (tenant_id, user_id, activity_type_id, activity_date, title, duration_minutes) VALUES (:tid, :uid, :type_id, :date, :title, :duration)");
        $stmt->execute(['tid' => $tenantId, 'uid' => $userId, 'type_id' => $typeId, 'date' => $date, 'title' => $title, 'duration' => $duration]);
        echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
        
    } elseif ($method === 'PUT') {
        // Update activity (SPA)
        $logId = (int)($input['activity_log_id'] ?? 0);
        if (!$logId) throw new Exception('Missing ID');
        
        $check = $db->prepare("SELECT activity_log_id FROM activity_logs WHERE activity_log_id = :id AND tenant_id = :tid AND user_id = :uid");
        $check->execute(['id' => $logId, 'tid' => $tenantId, 'uid' => $userId]);
        if (!$check->fetch()) throw new Exception('Not found');
        
        $duration = !empty($input['duration_minutes']) ? (int)$input['duration_minutes'] : null;
        $stmt = $db->prepare("UPDATE activity_logs SET duration_minutes = :duration WHERE activity_log_id = :id");
        $stmt->execute(['duration' => $duration, 'id' => $logId]);
        echo json_encode(['success' => true]);
        
    } elseif ($method === 'DELETE') {
        // Delete activity (SPA)
        $logId = (int)($input['activity_log_id'] ?? 0);
        $stmt = $db->prepare("DELETE FROM activity_logs WHERE activity_log_id = :id AND tenant_id = :tid AND user_id = :uid");
        $stmt->execute(['id' => $logId, 'tid' => $tenantId, 'uid' => $userId]);
        echo json_encode(['success' => true]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}