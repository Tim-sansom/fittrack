<?php
/**
 * File: measurements.php
 * Location: /public/api/measurements.php
 *
 * Purpose:
 * Returns the user's body measurement records and activity volume data
 * for the SPA Progress view. Provides both the latest measurement (for
 * stat cards with deltas) and historical records (for trend charts).
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

try {
    $db = Database::getInstance()->getConnection();
    $userId = (int)$_SESSION['user_id'];
    $tenantId = (int)$_SESSION['tenant_id'];

    // Fetch the two most recent measurements (latest + previous for delta calculation)
    $stmt = $db->prepare("
        SELECT weight_kg, bmi, chest_cm, waist_cm, hips_cm,
               left_arm_cm, right_arm_cm, left_leg_cm, right_leg_cm,
               height_cm, recorded_at
        FROM user_measurements
        WHERE user_id = :uid
        ORDER BY recorded_at DESC
        LIMIT 2
    ");
    $stmt->execute(['uid' => $userId]);
    $measurements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch historical weight data for the trend chart (grouped by week)
    $histStmt = $db->prepare("
        SELECT DATE_FORMAT(recorded_at, '%Y-%u') as week_key,
               MIN(recorded_at) as date,
               AVG(weight_kg) as avg_weight
        FROM user_measurements
        WHERE user_id = :uid
        GROUP BY week_key
        ORDER BY date ASC
    ");
    $histStmt->execute(['uid' => $userId]);
    $weightHistory = $histStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch activity volume for the volume chart
    $volStmt = $db->prepare("
        SELECT DATE(activity_date) as date, SUM(duration_minutes) as total_mins
        FROM activity_logs
        WHERE user_id = :uid AND tenant_id = :tid
        GROUP BY DATE(activity_date)
        ORDER BY activity_date ASC
    ");
    $volStmt->execute(['uid' => $userId, 'tid' => $tenantId]);
    $volumeHistory = $volStmt->fetchAll(PDO::FETCH_ASSOC);

    // Shape data for different time ranges (Week/Month/Year)
    $weightData = [
        'week'  => array_map(fn($d) => ['label' => date('D', strtotime($d['date'])), 'value' => round((float)$d['avg_weight'], 1)], array_slice($weightHistory, -7)),
        'month' => array_map(fn($d) => ['label' => 'W' . date('W', strtotime($d['date'])), 'value' => round((float)$d['avg_weight'], 1)], array_slice($weightHistory, -4)),
        'year'  => array_map(fn($d) => ['label' => date('M', strtotime($d['date'])), 'value' => round((float)$d['avg_weight'], 1)], array_slice($weightHistory, -12))
    ];

    $volumeData = [
        'week'  => array_map(fn($d) => ['label' => date('D', strtotime($d['date'])), 'value' => (int)$d['total_mins']], array_slice($volumeHistory, -7)),
        'month' => array_map(fn($d) => ['label' => 'W' . date('W', strtotime($d['date'])), 'value' => (int)$d['total_mins']], array_slice($volumeHistory, -4)),
        'year'  => array_map(fn($d) => ['label' => date('M', strtotime($d['date'])), 'value' => (int)$d['total_mins']], array_slice($volumeHistory, -12))
    ];

    // Calculate deltas between latest and previous measurement
    $latest = $measurements[0] ?? null;
    $previous = $measurements[1] ?? null;

    $calcDelta = function($curr, $prev) {
        if (!$prev || $prev <= 0) return 0;
        return round((($curr - $prev) / $prev) * 100, 1);
    };

    $weightDelta = $latest && $previous 
        ? $calcDelta((float)$latest['weight_kg'], (float)$previous['weight_kg']) 
        : 0;
    $bmiDelta = $latest && $previous 
        ? $calcDelta((float)$latest['bmi'], (float)$previous['bmi']) 
        : 0;

    echo json_encode([
        'success' => true,
        'data' => [
            'latest' => $latest,
            'previous' => $previous,
            'weight_delta' => $weightDelta,
            'bmi_delta' => $bmiDelta,
            'weight_history' => $weightData,
            'volume_history' => $volumeData
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
exit;