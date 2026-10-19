<?php
/**
 * File: process-reset--request.php
 * Location: /public/process-reset--request.php
 *
 * Purpose:
 * Backend processor for password reset requests.
 * Validates the token, checks expiration, and handles the password update.
 * This file contains no UI elements, so no tooltips are required.
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/Session.php';
require_once __DIR__ . '/../config/Database.php';
Session::start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON payload']);
    exit;
}

$db = Database::getInstance()->getConnection();
$token = $input['token'] ?? '';
$newPassword = $input['new_password'] ?? '';
$confirmPassword = $input['confirm_password'] ?? '';

if (empty($token) || empty($newPassword) || empty($confirmPassword)) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

if ($newPassword !== $confirmPassword) {
    echo json_encode(['success' => false, 'error' => 'Passwords do not match']);
    exit;
}

if (strlen($newPassword) < 12) {
    echo json_encode(['success' => false, 'error' => 'Password must be at least 12 characters']);
    exit;
}

// Validate token
$stmt = $db->prepare("
    SELECT user_id, expires_at FROM password_resets WHERE token = :token LIMIT 1
");
$stmt->execute(['token' => $token]);
$resetData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resetData || strtotime($resetData['expires_at']) < time()) {
    echo json_encode(['success' => false, 'error' => 'Invalid or expired token']);
    exit;
}

try {
    $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
    
    $stmt = $db->prepare("UPDATE users SET password_hash = :hash WHERE user_id = :uid");
    $stmt->execute(['hash' => $hash, 'uid' => $resetData['user_id']]);

    $stmt = $db->prepare("DELETE FROM password_resets WHERE token = :token");
    $stmt->execute(['token' => $token]);

    echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
} catch (PDOException $e) {
    error_log('Password reset failed: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}