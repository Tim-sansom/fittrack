<?php
/**
 * File: meal-plans.php
 * Location: /public/api/meal-plans.php
 *
 * Purpose:
 * RESTful endpoint that returns the current week's meal plan data
 * for the SPA renderer. Groups meals by date and slot (breakfast/lunch/dinner/snacks).
 *
 * Security:
 * - Enforces authentication via session check.
 * - All queries scoped to tenant_id and user_id.
 */
declare(strict_types=1);
require_once __DIR__ . '/../../config/Session.php';
require_once __DIR__ . '/../../config/Database.php';
Session::start();

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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Calculate current week (Monday to Sunday)
        $today = new DateTime();
        $dayOfWeek = (int)$today->format('N'); // 1=Mon, 7=Sun
        $monday = (clone $today)->modify('-' . ($dayOfWeek - 1) . ' days');
        $sunday = (clone $monday)->modify('+6 days');

        $weekDates = [];
        for ($i = 0; $i < 7; $i++) {
            $date = (clone $monday)->modify("+{$i} days");
            $weekDates[] = $date->format('Y-m-d');
        }

        // Fetch meals for the current week
        $stmt = $db->prepare("
            SELECT meal_plan_item_id, meal_date, meal_slot, food_name,
                   calories, protein_g, carbs_g, fats_g, notes
            FROM meal_plan_items
            WHERE tenant_id = :tid AND user_id = :uid
              AND meal_date BETWEEN :start AND :end
            ORDER BY meal_date ASC, FIELD(meal_slot, 'breakfast', 'lunch', 'dinner', 'snacks')
        ");
        $stmt->execute([
            'tid' => $tenantId,
            'uid' => $userId,
            'start' => $weekDates[0],
            'end' => $weekDates[6]
        ]);
        $meals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group meals by date and slot
        $mealsByDate = [];
        foreach ($meals as $meal) {
            $mealsByDate[$meal['meal_date']][$meal['meal_slot']] = $meal;
        }

        // Build week structure with all days and slots
        $weekData = [];
        foreach ($weekDates as $date) {
            $dateObj = new DateTime($date);
            $weekData[] = [
                'date' => $date,
                'day_name' => $dateObj->format('l'),
                'day_short' => $dateObj->format('D'),
                'is_today' => $date === $today->format('Y-m-d'),
                'meals' => [
                    'breakfast' => $mealsByDate[$date]['breakfast'] ?? null,
                    'lunch' => $mealsByDate[$date]['lunch'] ?? null,
                    'dinner' => $mealsByDate[$date]['dinner'] ?? null,
                    'snacks' => $mealsByDate[$date]['snacks'] ?? null,
                ]
            ];
        }

        echo json_encode([
            'success' => true,
            'data' => $weekData,
            'count' => count($meals)
        ], JSON_UNESCAPED_UNICODE);
    } catch (PDOException $e) {
        error_log('Meal Plans API GET failed: ' . $e->getMessage());
        echo json_encode(['success' => true, 'data' => [], 'count' => 0]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);