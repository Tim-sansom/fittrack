<?php
/**
 * File: dashboard_data.php
 * Location: /public/api/dashboard_data.php
 * 
 * Purpose:
 * Aggregates data from multiple tables to populate the dashboard visualisations.
 * 
 * Architectural Role:
 * Reduces frontend complexity by performing server-side aggregation. Instead of 
 * the browser fetching raw rows and calculating totals, the database engine 
 * handles the math, returning a lightweight JSON payload.
 * 
 * Security:
 * - All aggregation queries are strictly scoped to the authenticated `tenant_id` 
 *   and `user_id`.
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

$db = Database::getInstance()->getConnection();
$userId = (int)$_SESSION['user_id'];
$tenantId = (int)$_SESSION['tenant_id'];

try {
    // 1. Weekly Activity Stats (For the Rings)
    // Uses COALESCE to return 0 instead of NULL when no records exist, preventing frontend errors.
    $stmt = $db->prepare("
        SELECT COUNT(*) as total_workouts, 
               COALESCE(SUM(duration_minutes), 0) as total_duration
        FROM activity_logs 
        WHERE user_id = :uid AND tenant_id = :tid 
        AND activity_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ");
    $stmt->execute(['uid' => $userId, 'tid' => $tenantId]);
    $weeklyStats = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Activity Distribution (For the Bar Chart)
    $stmt = $db->prepare("
        SELECT at.name, COUNT(al.activity_log_id) as count
        FROM activity_logs al
        JOIN activity_types at ON al.activity_type_id = at.activity_type_id
        WHERE al.user_id = :uid AND al.tenant_id = :tid
        GROUP BY at.name
        ORDER BY count DESC
        LIMIT 5
    ");
    $stmt->execute(['uid' => $userId, 'tid' => $tenantId]);
    $distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. 7-Day Nutrition Data (For the Wave Chart)
    $stmt = $db->prepare("
        SELECT log_date, 
               COALESCE(SUM(calories_consumed), 0) as consumed, 
               COALESCE(SUM(calories_burned), 0) as burned
        FROM daily_nutrition
        WHERE user_id = :uid AND tenant_id = :tid
        AND log_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY log_date
        ORDER BY log_date ASC
    ");
    $stmt->execute(['uid' => $userId, 'tid' => $tenantId]);
    $nutritionData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Data shaping: Fill in missing days to ensure the chart always renders 7 points.
    $chartData = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $dayData = array_filter($nutritionData, fn($d) => $d['log_date'] === $date);
        $dayData = !empty($dayData) ? reset($dayData) : ['consumed' => 0, 'burned' => 0];
        $chartData[] = [
            'date' => date('D', strtotime($date)),
            'balance' => (int)$dayData['consumed'] - (int)$dayData['burned']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'weekly' => $weeklyStats,
            'distribution' => $distribution,
            'nutrition_chart' => $chartData
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}