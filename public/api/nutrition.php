<?php
/**
 * File: nutrition.php
 * Location: /public/api/nutrition.php
 * 
 * Purpose:
 * RESTful endpoint for logging daily calorie intake and expenditure.
 * 
 * Architectural Role:
 * Acts as the data ingestion point for the nutrition wave chart. It uses an 
 * "upsert" strategy (INSERT ... ON DUPLICATE KEY UPDATE) to ensure a user 
 * only has one nutrition record per day, preventing duplicate chart data.
 * 
 * Security:
 * - Validates active session before processing.
 * - Scopes all writes to the authenticated tenant_id to prevent cross-tenant pollution.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/Session.php';
require_once __DIR__ . '/../../config/Database.php';

Session::start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();
$userId = (int)$_SESSION['user_id'];
$tenantId = (int)$_SESSION['tenant_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $date = $input['log_date'] ?? date('Y-m-d');
    $consumed = (int)($input['calories_consumed'] ?? 0);
    $burned = (int)($input['calories_burned'] ?? 0);

    // Upsert logic: Insert new record, or update existing record for this specific date.
    // This guarantees data consistency for the 7-day chart.
    $sql = "INSERT INTO daily_nutrition (tenant_id, user_id, log_date, calories_consumed, calories_burned) 
            VALUES (:tid, :uid, :date, :consumed, :burned)
            ON DUPLICATE KEY UPDATE 
            calories_consumed = :consumed, calories_burned = :burned";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([
        'tid' => $tenantId, 
        'uid' => $userId, 
        'date' => $date, 
        'consumed' => $consumed, 
        'burned' => $burned
    ]);

    echo json_encode(['success' => true]);
}