<?php
/**
 * File: admin.php
 * Location: /public/api/admin.php
 *
 * Purpose:
 * RESTful endpoint that returns admin data (activity types and tenant users)
 * for the SPA renderer.
 *
 * Security:
 * - Enforces Role-Based Access Control (RBAC).
 * - Scopes all queries to the authenticated tenant_id.
 */
declare(strict_types=1);
require_once __DIR__ . '/../../config/Session.php';
require_once __DIR__ . '/../../config/Database.php';
Session::start();

// RBAC Check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['administrator', 'super_administrator'], true)) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
$db = Database::getInstance()->getConnection();
$tenantId = (int)$_SESSION['tenant_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Fetch activity types
        $stmt = $db->prepare("SELECT * FROM activity_types WHERE tenant_id = :tid ORDER BY name");
        $stmt->execute(['tid' => $tenantId]);
        $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch tenant users
        $stmt = $db->prepare("
            SELECT u.user_id, u.username, u.email, u.display_name, u.account_status,
                   tm.membership_status, r.role_key
            FROM tenant_memberships tm
            JOIN users u ON tm.user_id = u.user_id
            JOIN roles r ON tm.role_id = r.role_id
            WHERE tm.tenant_id = :tid
            ORDER BY u.display_name ASC
        ");
        $stmt->execute(['tid' => $tenantId]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch available roles (excluding super_administrator)
        $roles = $db->query("SELECT role_id, role_key FROM roles WHERE role_key != 'super_administrator'")->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => [
                'activity_types' => $types,
                'users' => $users,
                'roles' => $roles
            ]
        ], JSON_UNESCAPED_UNICODE);
    } catch (PDOException $e) {
        error_log('Admin API GET failed: ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON payload']);
        exit;
    }

    $action = $input['action'] ?? '';
    $adminUserId = (int)$_SESSION['user_id'];

    try {
        if ($action === 'add_activity') {
            $name = trim($input['name'] ?? '');
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
            $stmt = $db->prepare("INSERT INTO activity_types (tenant_id, name, slug, category, is_published, created_by_user_id) VALUES (:tid, :name, :slug, 'general', 1, :uid)");
            $stmt->execute(['tid' => $tenantId, 'name' => $name, 'slug' => $slug, 'uid' => $adminUserId]);
            echo json_encode(['success' => true, 'message' => 'Activity type added.']);
        } elseif ($action === 'delete_activity') {
            $stmt = $db->prepare("DELETE FROM activity_types WHERE activity_type_id = :id AND tenant_id = :tid");
            $stmt->execute(['id' => (int)$input['activity_type_id'], 'tid' => $tenantId]);
            echo json_encode(['success' => true, 'message' => 'Activity type deleted.']);
        } elseif ($action === 'add_user') {
            $username = trim($input['username'] ?? '');
            $email = trim($input['email'] ?? '');
            $displayName = trim($input['display_name'] ?? '');
            $password = $input['password'] ?? '';
            $roleId = (int)$input['role_id'];

            if ($username && $email && $password && strlen($password) >= 12) {
                $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, display_name, account_status) VALUES (:u, :e, :h, :d, 'active')");
                $stmt->execute(['u' => $username, 'e' => $email, 'h' => $hash, 'd' => $displayName]);
                $newUserId = (int)$db->lastInsertId();

                $stmt = $db->prepare("INSERT INTO tenant_memberships (tenant_id, user_id, role_id, membership_status) VALUES (:tid, :uid, :rid, 'active')");
                $stmt->execute(['tid' => $tenantId, 'uid' => $newUserId, 'rid' => $roleId]);

                echo json_encode(['success' => true, 'message' => "User $displayName added successfully."]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid input. Password must be at least 12 characters.']);
            }
        } elseif (in_array($action, ['suspend_user', 'remove_user'])) {
            $targetUserId = (int)$input['user_id'];

            if ($targetUserId === $adminUserId) {
                echo json_encode(['success' => false, 'error' => 'You cannot modify your own account.']);
            } else {
                $newStatus = ($action === 'suspend_user') ? 'suspended' : 'removed';
                $stmt = $db->prepare("UPDATE tenant_memberships SET membership_status = :status WHERE user_id = :uid AND tenant_id = :tid");
                $stmt->execute(['status' => $newStatus, 'uid' => $targetUserId, 'tid' => $tenantId]);

                $msg = ($action === 'suspend_user') ? 'User suspended (banned).' : 'User removed from tenant.';
                echo json_encode(['success' => true, 'message' => $msg]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
        }
    } catch (PDOException $e) {
        error_log('Admin API POST failed: ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Database operation failed']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);