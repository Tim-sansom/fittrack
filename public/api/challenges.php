<?php
/**
 * File: challenges.php
 * Location: /public/api/challenges.php
 * 
 * Purpose:
 * API endpoint for managing user challenges.
 * Handles retrieval of active/completed challenges and creation of new ones.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/Session.php';
require_once __DIR__ . '/../../config/Database.php';

header('Content-Type: application/json');
Session::start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$db = Database::getInstance()->getConnection();
$userId = (int)$_SESSION['user_id'];
$tenantId = (int)$_SESSION['tenant_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $status = $_GET['status'] ?? 'active';
    if (!in_array($status, ['active', 'completed', 'failed'])) {
        $status = 'active';
    }

    $stmt = $db->prepare("
        SELECT challenge_id, title, target_value, current_value, unit, end_date, status
        FROM challenges 
        WHERE user_id = :uid AND tenant_id = :tid AND status = :status
        ORDER BY end_date ASC
    ");
    $stmt->execute(['uid' => $userId, 'tid' => $tenantId, 'status' => $status]);
    $challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $challenges]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $title = trim($input['title'] ?? '');
    $target = (float)($input['target_value'] ?? 0);
    $unit = $input['unit'] ?? 'sessions';
    $duration = (int)($input['duration'] ?? 30);

    if (empty($title) || $target <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
        exit;
    }

    $startDate = date('Y-m-d');
    $endDate = date('Y-m-d', strtotime("+$duration days"));

    $stmt = $db->prepare("
        INSERT INTO challenges (tenant_id, user_id, title, target_value, unit, start_date, end_date, status)
        VALUES (:tid, :uid, :title, :target, :unit, :start, :end, 'active')
    ");
    
    $success = $stmt->execute([
        'tid' => $tenantId,
        'uid' => $userId,
        'title' => $title,
        'target' => $target,
        'unit' => $unit,
        'start' => $startDate,
        'end' => $endDate
    ]);

    if ($success) {
        echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);