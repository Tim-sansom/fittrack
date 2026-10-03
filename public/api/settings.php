<?php
/**
 * File: settings.php
 * Location: /public/api/settings.php
 * 
 * Purpose:
 * API endpoint for user preferences and account management.
 * Handles theme, units, visibility defaults, notifications, and password changes.
 * 
 * Security:
 * - Password changes require current password verification.
 * - New passwords are hashed with bcrypt (cost factor 12).
 * - All updates are scoped to tenant_id + user_id composite key.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/Session.php';
require_once __DIR__ . '/../../config/Database.php';

header('Content-Type: application/json');
Session::start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

$db = Database::getInstance()->getConnection();
$userId = (int)$_SESSION['user_id'];
$tenantId = (int)$_SESSION['tenant_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['success' => false, 'error' => 'Method not allowed']));
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'update_preferences':
        // Validate enum values to prevent injection
        $theme = in_array($input['theme'] ?? '', ['system', 'light', 'dark']) ? $input['theme'] : 'system';
        $distance = in_array($input['distance_unit'] ?? '', ['km', 'miles']) ? $input['distance_unit'] : 'km';
        $weight = in_array($input['weight_unit'] ?? '', ['kg', 'stone_pounds']) ? $input['weight_unit'] : 'kg';
        $temp = in_array($input['temperature_unit'] ?? '', ['celsius', 'fahrenheit']) ? $input['temperature_unit'] : 'celsius';
        $visibility = in_array($input['visibility'] ?? '', ['private', 'tenant', 'public']) ? $input['visibility'] : 'private';
        $notifications = (bool)($input['notifications'] ?? true);
        
        $stmt = $db->prepare("
            INSERT INTO user_preferences 
            (tenant_id, user_id, distance_unit, weight_unit, temperature_unit, theme_preference, default_activity_visibility, notifications_enabled)
            VALUES (:tid, :uid, :dist, :weight, :temp, :theme, :vis, :notif)
            ON DUPLICATE KEY UPDATE
            distance_unit = :dist2, weight_unit = :weight2, temperature_unit = :temp2,
            theme_preference = :theme2, default_activity_visibility = :vis2, notifications_enabled = :notif2
        ");
        $stmt->execute([
            'tid' => $tenantId, 'uid' => $userId,
            'dist' => $distance, 'dist2' => $distance,
            'weight' => $weight, 'weight2' => $weight,
            'temp' => $temp, 'temp2' => $temp,
            'theme' => $theme, 'theme2' => $theme,
            'vis' => $visibility, 'vis2' => $visibility,
            'notif' => $notifications, 'notif2' => $notifications
        ]);
        
        // Update session theme for immediate UI sync
        $_SESSION['theme_preference'] = $theme;
        
        echo json_encode(['success' => true]);
        exit;
        
    case 'change_password':
        $current_password = $input['current_password'] ?? '';
        $new_password = $input['new_password'] ?? '';
        $confirm_password = $input['confirm_password'] ?? '';
        
        if ($new_password !== $confirm_password) {
            exit(json_encode(['success' => false, 'error' => 'New passwords do not match']));
        }
        
        if (strlen($new_password) < 12) {
            exit(json_encode(['success' => false, 'error' => 'Password must be at least 12 characters']));
        }
        
        // Verify current password
        $stmt = $db->prepare("SELECT password_hash FROM users WHERE user_id = :uid");
        $stmt->execute(['uid' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($current_password, $user['password_hash'])) {
            exit(json_encode(['success' => false, 'error' => 'Current password is incorrect']));
        }
        
        // Hash new password with bcrypt (cost 12)
        $new_hash = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $stmt = $db->prepare("UPDATE users SET password_hash = :hash, updated_at = NOW() WHERE user_id = :uid");
        $stmt->execute(['hash' => $new_hash, 'uid' => $userId]);
        
        // Log security event
        $db->prepare("
            INSERT INTO security_events (tenant_id, user_id, event_type, outcome, ip_address, user_agent)
            VALUES (:tid, :uid, 'password_changed', 'success', :ip, :ua)
        ")->execute([
            'tid' => $tenantId, 'uid' => $userId,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)
        ]);
        
        echo json_encode(['success' => true]);
        exit;
        
    default:
        exit(json_encode(['success' => false, 'error' => 'Unknown action']));
}