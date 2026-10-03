<?php
/**
 * File: kudos.php
 * Location: /public/api/kudos.php
 * 
 * Purpose:
 * API endpoint handling asynchronous state changes for the Kudos system.
 * Implements a toggle mechanism (add/remove) and returns the updated count.
 * 
 * Security & Architecture:
 * - Strict multi-tenant isolation is enforced via the session user_id.
 * - Input is strictly cast to integers to prevent SQL injection via type juggling.
 * - Returns JSON for consumption by the Vanilla JS optimistic UI updater.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/Session.php';
require_once __DIR__ . '/../../config/Database.php';

// Force JSON response header for the SPA-like fetch request
header('Content-Type: application/json');
Session::start();

// Reject unauthenticated requests immediately
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

$db = Database::getInstance()->getConnection();
$userId = (int)$_SESSION['user_id']; // Cast to int for strict type safety

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $activityId = (int)($input['activity_log_id'] ?? 0);

    if ($activityId <= 0) {
        exit(json_encode(['success' => false, 'error' => 'Invalid activity ID']));
    }

    /* 
     * Toggle Logic:
     * We first check if the relationship exists. If it does, we DELETE it (unlike).
     * If it doesn't, we INSERT it (like). This is cleaner than INSERT IGNORE 
     * because it allows us to easily track the action taken for the response.
     */
    $stmt = $db->prepare("SELECT kudos_id FROM kudos WHERE user_id = :uid AND activity_log_id = :aid");
    $stmt->execute(['uid' => $userId, 'aid' => $activityId]);
    $exists = $stmt->fetch();

    if ($exists) {
        $db->prepare("DELETE FROM kudos WHERE kudos_id = :id")->execute(['id' => $exists['kudos_id']]);
        $action = 'removed';
    } else {
        $db->prepare("INSERT INTO kudos (user_id, activity_log_id) VALUES (:uid, :aid)")
           ->execute(['uid' => $userId, 'aid' => $activityId]);
        $action = 'added';
    }

    // Fetch the new total count to send back to the client
    $countStmt = $db->prepare("SELECT COUNT(*) FROM kudos WHERE activity_log_id = :aid");
    $countStmt->execute(['aid' => $activityId]);
    $newCount = (int)$countStmt->fetchColumn();

    echo json_encode(['success' => true, 'action' => $action, 'count' => $newCount]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);