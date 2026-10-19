<?php
/**
 * File: export.php
 * Location: /public/api/export.php
 *
 * Purpose:
 * Generates a JSON dump of the user's data for the "Export My Data" feature.
 * Satisfies GDPR/Data Portability requirements.
 */
declare(strict_types=1);
require_once __DIR__ . '/../../config/Session.php';
require_once __DIR__ . '/../../config/Database.php';
Session::start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorised');
}

// Set headers to force a file download
header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="fittrack_data_export_' . date('Y-m-d') . '.json"');
header('Cache-Control: no-cache, no-store, must-revalidate');

$db = Database::getInstance()->getConnection();
$userId = (int)$_SESSION['user_id'];
$tenantId = (int)$_SESSION['tenant_id'];

$exportData = [
    'export_date' => date('c'),
    'user' => [
        'username' => $_SESSION['username'] ?? null
    ]
];

// 1. Fetch Profile Data
try {
    $stmt = $db->prepare("SELECT display_name, bio, account_status, created_at FROM users WHERE user_id = :uid");
    $stmt->execute(['uid' => $userId]);
    $exportData['profile'] = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) { $exportData['profile'] = null; }

// 2. Fetch Preferences
try {
    $stmt = $db->prepare("SELECT distance_unit, weight_unit, temperature_unit, theme_preference, default_activity_visibility FROM user_preferences WHERE user_id = :uid AND tenant_id = :tid");
    $stmt->execute(['uid' => $userId, 'tid' => $tenantId]);
    $exportData['preferences'] = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) { $exportData['preferences'] = null; }

// 3. Fetch Activities (Last 100 for performance)
try {
    $stmt = $db->prepare("SELECT activity_date, title, duration_minutes, distance_km, steps, intensity, notes FROM activity_logs WHERE user_id = :uid AND tenant_id = :tid ORDER BY activity_date DESC LIMIT 100");
    $stmt->execute(['uid' => $userId, 'tid' => $tenantId]);
    $exportData['activities'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $exportData['activities'] = []; }

// 4. Fetch Nutrition Logs (Last 30 days)
try {
    $stmt = $db->prepare("SELECT log_date, calories_consumed, calories_burned, protein_g, carbs_g, fats_g FROM daily_nutrition WHERE user_id = :uid AND tenant_id = :tid ORDER BY log_date DESC LIMIT 30");
    $stmt->execute(['uid' => $userId, 'tid' => $tenantId]);
    $exportData['nutrition'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $exportData['nutrition'] = []; }

echo json_encode($exportData, JSON_PRETTY_PRINT);