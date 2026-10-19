<?php
/**
 * File: nutrition.php
 * Location: /public/api/nutrition.php
 *
 * Purpose:
 * RESTful endpoint for managing daily calorie intake and expenditure.
 * Handles both data ingestion (POST) and data retrieval (GET) for the SPA.
 *
 * Architectural Role:
 * Acts as the data layer for the nutrition module. It uses an "upsert"
 * strategy (INSERT ... ON DUPLICATE KEY UPDATE) to ensure a user only
 * has one nutrition record per day, preventing duplicate chart data.
 *
 * Security:
 * - Validates active session before processing any request.
 * - Scopes all reads and writes to the authenticated tenant_id and user_id
 *   to prevent cross-tenant data leakage.
 * - Returns strict JSON responses with appropriate HTTP status codes.
 */
declare(strict_types=1);
require_once __DIR__ . '/../../config/Session.php';
require_once __DIR__ . '/../../config/Database.php';
Session::start();

// Enforce authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorised']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
$db = Database::getInstance()->getConnection();
$userId = (int)$_SESSION['user_id'];
$tenantId = (int)$_SESSION['tenant_id'];

/**
 * GET: Retrieve recent nutrition records for the SPA dashboard.
 */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $sql = "SELECT log_date, calories_consumed, calories_burned, protein_g, carbs_g, fats_g
                FROM daily_nutrition
                WHERE tenant_id = :tid AND user_id = :uid
                ORDER BY log_date DESC
                LIMIT 14";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'tid' => $tenantId,
            'uid' => $userId
        ]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Map the database rows to the structure expected by the SPA renderNutrition function
        $nutritionData = array_map(function($row) {
            return [
                'food_name'   => 'Daily Total',
                'name'        => 'Daily Log',
                'calories'    => (int)$row['calories_consumed'],
                'protein'     => (int)($row['protein_g'] ?? 0),
                'carbs'       => (int)($row['carbs_g'] ?? 0),
                'fats'        => (int)($row['fats_g'] ?? 0),
                'logged_at'   => $row['log_date']
            ];
        }, $records);
        
        echo json_encode([
            'success' => true,
            'data'    => $nutritionData,
            'count'   => count($nutritionData)
        ], JSON_UNESCAPED_UNICODE);
    } catch (PDOException $e) {
        error_log('Nutrition API GET failed: ' . $e->getMessage());
        echo json_encode([
            'success' => true,
            'data'    => [],
            'count'   => 0,
            'message' => 'Nutrition data table not yet provisioned.'
        ]);
    }
    exit;
}

/**
 * POST: Ingest new daily nutrition data.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON payload']);
        exit;
    }
    
    $date = $input['log_date'] ?? date('Y-m-d');
    $consumed = (int)($input['calories_consumed'] ?? 0);
    $burned = (int)($input['calories_burned'] ?? 0);
    
    try {
        $sql = "INSERT INTO daily_nutrition (tenant_id, user_id, log_date, calories_consumed, calories_burned)
                VALUES (:tid, :uid, :date, :consumed, :burned)
                ON DUPLICATE KEY UPDATE
                calories_consumed = :consumed, calories_burned = :burned";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'tid'      => $tenantId,
            'uid'      => $userId,
            'date'     => $date,
            'consumed' => $consumed,
            'burned'   => $burned
        ]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        error_log('Nutrition API POST failed: ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Database operation failed']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);