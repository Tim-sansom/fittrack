<?php
/**
 * File: admin.php
 * Location: /public/admin.php
 *
 * Purpose:
 * Administrative interface for managing tenant-specific content and users.
 *
 * Security:
 * - Enforces Role-Based Access Control (RBAC).
 * - Prevents admins from modifying their own accounts (anti-lockout).
 * - Scopes all database writes to the authenticated tenant_id.
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/Session.php';
require_once __DIR__ . '/../config/Database.php';
Session::start();
// RBAC Check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['administrator', 'super_administrator'], true)) {
    header('Location: dashboard.php');
    exit;
}
$db = Database::getInstance()->getConnection();
$tenantId = (int)$_SESSION['tenant_id'];
$adminUserId = (int)$_SESSION['user_id'];
$pageTitle = 'Admin';
$currentPage = 'admin';
$message = '';
$messageType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
    $action = $_POST['action'] ?? '';
    // --- ACTIVITY TYPE MANAGEMENT ---
    if ($action === 'add_activity') {
        $name = trim($_POST['name'] ?? '');
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
        $stmt = $db->prepare("INSERT INTO activity_types (tenant_id, name, slug, category, is_published, created_by_user_id) VALUES (:tid, :name, :slug, 'general', 1, :uid)");
        $stmt->execute(['tid' => $tenantId, 'name' => $name, 'slug' => $slug, 'uid' => $adminUserId]);
        $message = 'Activity type added.'; $messageType = 'success';
    } elseif ($action === 'delete_activity') {
        $stmt = $db->prepare("DELETE FROM activity_types WHERE activity_type_id = :id AND tenant_id = :tid");
        $stmt->execute(['id' => (int)$_POST['activity_type_id'], 'tid' => $tenantId]);
        $message = 'Activity type deleted.'; $messageType = 'success';
    }
    // --- USER MANAGEMENT ---
    elseif ($action === 'add_user') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $displayName = trim($_POST['display_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $roleId = (int)$_POST['role_id'];
        if ($username && $email && $password && strlen($password) >= 8) {
            try {
                $db->beginTransaction();
                // 1. Create User
                $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, display_name, account_status) VALUES (:u, :e, :h, :d, 'active')");
                $stmt->execute(['u' => $username, 'e' => $email, 'h' => $hash, 'd' => $displayName]);
                $newUserId = (int)$db->lastInsertId();
                // 2. Assign to Tenant
                $stmt = $db->prepare("INSERT INTO tenant_memberships (tenant_id, user_id, role_id, membership_status) VALUES (:tid, :uid, :rid, 'active')");
                $stmt->execute(['tid' => $tenantId, 'uid' => $newUserId, 'rid' => $roleId]);
                $db->commit();
                $message = "User $displayName added successfully."; $messageType = 'success';
            } catch (PDOException $e) {
                $db->rollBack();
                $message = 'Error: Username or email may already exist.'; $messageType = 'error';
            }
        } else {
            $message = 'Invalid input. Password must be at least 8 characters.'; $messageType = 'error';
        }
    }
    elseif (in_array($action, ['suspend_user', 'remove_user'])) {
        $targetUserId = (int)$_POST['user_id'];
        // Security: Prevent self-modification
        if ($targetUserId === $adminUserId) {
            $message = 'You cannot modify your own account.'; $messageType = 'error';
        } else {
            $newStatus = ($action === 'suspend_user') ? 'suspended' : 'removed';
            $stmt = $db->prepare("UPDATE tenant_memberships SET membership_status = :status WHERE user_id = :uid AND tenant_id = :tid");
            $stmt->execute(['status' => $newStatus, 'uid' => $targetUserId, 'tid' => $tenantId]);
            $msg = ($action === 'suspend_user') ? 'User suspended (banned).' : 'User removed from tenant.';
            $message = $msg; $messageType = 'success';
        }
    }
}
// Fetch Data
$activityTypes = $db->prepare("SELECT * FROM activity_types WHERE tenant_id = :tid ORDER BY name");
$activityTypes->execute(['tid' => $tenantId]);
$types = $activityTypes->fetchAll(PDO::FETCH_ASSOC);
$roles = $db->query("SELECT role_id, role_key FROM roles WHERE role_key != 'super_administrator'")->fetchAll(PDO::FETCH_ASSOC);
$users = $db->prepare("
    SELECT u.user_id, u.username, u.email, u.display_name, u.account_status,
    tm.membership_status, r.role_key
    FROM tenant_memberships tm
    JOIN users u ON tm.user_id = u.user_id
    JOIN roles r ON tm.role_id = r.role_id
    WHERE tm.tenant_id = :tid
    ORDER BY u.display_name ASC
");
$users->execute(['tid' => $tenantId]);
$userList = $users->fetchAll(PDO::FETCH_ASSOC);
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<div class="main-content">
    <?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>" style="padding: 1rem; background: <?= $messageType === 'success' ? '#d4edda' : '#f8d7da' ?>; color: <?= $messageType === 'success' ? '#155724' : '#721c24' ?>; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid <?= $messageType === 'success' ? '#c3e6cb' : '#f5c6cb' ?>;"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <!-- 1. Activity Types Management -->
    <div class="card">
        <h2>Manage Activity Types</h2>
        <form method="POST" action="admin.php" style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="action" value="add_activity">
            <!-- FIX: Added for/id pairing for label -->
            <label for="new_activity_name" class="sr-only">New Activity Type Name</label>
            <input type="text" id="new_activity_name" name="name" placeholder="New Activity Type" required style="flex-grow: 1; color: #222;">
            <button type="submit" class="btn-primary">Add</button>
        </form>
        <table class="data-table">
            <thead><tr><th>Name</th><th>Category</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($types as $type): ?>
            <tr>
                <!-- FIX: Changed to high-contrast hex color -->
                <td style="color: #222;"><?= htmlspecialchars($type['name']) ?></td>
                <td style="color: #222;"><?= htmlspecialchars($type['category']) ?></td>
                <td>
                    <form method="POST" action="admin.php" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden" name="action" value="delete_activity">
                        <input type="hidden" name="activity_type_id" value="<?= $type['activity_type_id'] ?>">
                        <button type="submit" class="btn-danger" onclick="return confirm('Delete?')">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- 2. Tenant User Management -->
    <div class="card">
        <h2>Tenant User Management</h2>
        <!-- Add User Form -->
        <form method="POST" action="admin.php" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="action" value="add_user">
            <!-- FIX: Added for/id pairing for all labels -->
            <div class="form-group"><label for="admin_username">Username</label><input type="text" id="admin_username" name="username" required style="color: #222;"></div>
            <div class="form-group"><label for="admin_email">Email</label><input type="email" id="admin_email" name="email" required style="color: #222;"></div>
            <div class="form-group"><label for="admin_display_name">Display Name</label><input type="text" id="admin_display_name" name="display_name" required style="color: #222;"></div>
            <div class="form-group"><label for="admin_password">Password (Min 8)</label><input type="password" id="admin_password" name="password" required minlength="8" style="color: #222;"></div>
            <div class="form-group">
                <!-- FIX: Added for/id pairing for select label -->
                <label for="admin_role_id">Role</label>
                <select id="admin_role_id" name="role_id" required style="color: #222; background: #fff;">
                    <?php foreach ($roles as $role): ?>
                    <option value="<?= $role['role_id'] ?>"><?= ucfirst(str_replace('_', ' ', $role['role_key'])) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="display: flex; align-items: flex-end;">
                <button type="submit" class="btn-primary">Create User</button>
            </div>
        </form>
        <!-- User List -->
        <table class="data-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Username</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($userList as $user):
                $isSelf = ($user['user_id'] === $adminUserId);
                // FIX: Changed to high-contrast hex colors
                $statusColor = $user['membership_status'] === 'active' ? '#1b5e20' : '#b71c1c';
                $statusBg = $user['membership_status'] === 'active' ? '#e8f5e9' : '#ffebee';
            ?>
            <tr>
                <!-- FIX: Changed to high-contrast hex colors -->
                <td style="color: #222;"><?= htmlspecialchars($user['display_name']) ?></td>
                <td style="color: #222;"><?= htmlspecialchars($user['username']) ?></td>
                <td style="color: #222;"><?= ucfirst(str_replace('_', ' ', $user['role_key'])) ?></td>
                <td style="color: <?= $statusColor ?>; background: <?= $statusBg ?>; padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: bold; text-transform: uppercase; font-size: 0.85rem;"><?= ucfirst($user['membership_status']) ?></td>
                <td>
                    <?php if (!$isSelf): ?>
                        <?php if ($user['membership_status'] === 'active'): ?>
                            <form method="POST" action="admin.php" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="action" value="suspend_user">
                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                <button type="submit" class="btn-danger" style="background: #f5e6a3; color: #333;" onclick="return confirm('Suspend (Ban) this user?')">Suspend</button>
                            </form>
                            <form method="POST" action="admin.php" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="action" value="remove_user">
                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                <button type="submit" class="btn-danger" onclick="return confirm('Remove user from tenant?')">Remove</button>
                            </form>
                        <?php else: ?>
                            <span style="font-size: 0.8rem; color: #555;">Inactive</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span style="font-size: 0.8rem; color: #555;">(You)</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>