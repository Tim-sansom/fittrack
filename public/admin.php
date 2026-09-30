<?php
/**
 * File: admin.php
 * Location: /public/admin.php
 * 
 * Purpose:
 * Administrative interface for managing the activity_types catalogue.
 * 
 * Security:
 * - Enforces Role-Based Access Control (RBAC). Only 'administrator' 
 *   or 'super_administrator' roles can access this page.
 * - Validates CSRF tokens on all POST requests.
 * - Scopes all database writes to the authenticated tenant_id.
 */
declare(strict_types=1);

require_once __DIR__ . '/config/Session.php';
require_once __DIR__ . '/config/Database.php';

Session::start();

// RBAC Check: Redirect non-admins to the dashboard
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['administrator', 'super_administrator'], true)) {
    header('Location: dashboard.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$tenantId = (int)$_SESSION['tenant_id'];
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Validation
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $name = trim($_POST['name'] ?? '');
            // Generate URL-friendly slug from name
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
            $category = $_POST['category'] ?? 'general';
            $measurement_model = $_POST['measurement_model'] ?? 'general';
            $default_unit = $_POST['default_unit'] ?? 'none';
            $description = trim($_POST['description'] ?? '');
            $safety_notes = trim($_POST['safety_notes'] ?? '');

            if ($name && $slug) {
                try {
                    $stmt = $db->prepare("
                        INSERT INTO activity_types 
                        (tenant_id, name, slug, category, measurement_model, default_unit, description, safety_notes, is_published, created_by_user_id)
                        VALUES (:tid, :name, :slug, :cat, :model, :unit, :desc, :safety, 1, :uid)
                    ");
                    $stmt->execute([
                        'tid' => $tenantId, 'name' => $name, 'slug' => $slug,
                        'cat' => $category, 'model' => $measurement_model, 'unit' => $default_unit,
                        'desc' => $description, 'safety' => $safety_notes, 'uid' => $_SESSION['user_id']
                    ]);
                    $message = 'Activity type added successfully.';
                    $messageType = 'success';
                } catch (PDOException $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'error';
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            $typeId = (int)($_POST['activity_type_id'] ?? 0);
            if ($typeId) {
                // Scope deletion to tenant to prevent cross-tenant data removal
                $stmt = $db->prepare("DELETE FROM activity_types WHERE activity_type_id = :id AND tenant_id = :tid");
                $stmt->execute(['id' => $typeId, 'tid' => $tenantId]);
                $message = 'Activity type deleted.';
                $messageType = 'success';
            }
        }
    }
}

// Fetch tenant-specific activity types
$stmt = $db->prepare("SELECT * FROM activity_types WHERE tenant_id = :tid ORDER BY name");
$stmt->execute(['tid' => $tenantId]);
$activityTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | FitTrack</title>
    <link rel="stylesheet" href="/css/dashboard.css">
    <style>
        /* Admin-specific styles */
        .nav-links { display: flex; gap: 1rem; }
        .nav-links a { text-decoration: none; color: var(--accent); font-weight: 500; }
        .container { max-width: 1000px; margin: 0 auto; }
        .message { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .message.success { background: rgba(76, 175, 80, 0.1); color: var(--success); }
        .message.error { background: rgba(226, 105, 114, 0.1); color: var(--alert); }
        textarea { resize: vertical; min-height: 80px; }
        .btn-danger { background: var(--alert); color: #fff; padding: 0.5rem 1rem; border: none; border-radius: 6px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--border); }
        .category-tag { display: inline-block; padding: 0.25rem 0.5rem; background: var(--highlight); border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
    </style>
</head>
<body>
    <?php $pageTitle = 'Admin'; require_once __DIR__ . '/includes/header.php'; ?>

    <div class="container">
        <?php if ($message): ?>
            <div class="message <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>Add Activity Type</h2>
            <form method="POST" action="admin.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="action" value="add">
                <div class="form-group"><label for="name">Activity Name</label><input type="text" id="name" name="name" required></div>
                <div class="form-group"><label for="category">Category</label><select id="category" name="category" required><option value="strength">Strength</option><option value="cardio">Cardio</option><option value="aquatic">Aquatic</option><option value="sport">Sport</option><option value="general">General</option></select></div>
                <div class="form-group"><label for="measurement_model">Measurement Model</label><select id="measurement_model" name="measurement_model" required><option value="gym">Gym</option><option value="swimming">Swimming</option><option value="distance">Distance</option><option value="sport">Sport</option><option value="duration">Duration</option><option value="general">General</option></select></div>
                <div class="form-group"><label for="default_unit">Default Unit</label><select id="default_unit" name="default_unit" required><option value="kg">Kilograms (kg)</option><option value="km">Kilometres (km)</option><option value="metres">Metres (m)</option><option value="minutes">Minutes</option><option value="none">None</option></select></div>
                <div class="form-group"><label for="description">Description</label><textarea id="description" name="description" required></textarea></div>
                <div class="form-group"><label for="safety_notes">Safety Notes</label><textarea id="safety_notes" name="safety_notes"></textarea></div>
                <button type="submit" class="btn-primary" style="width: auto;">Add Activity Type</button>
            </form>
        </div>

        <div class="card">
            <h2>Manage Activity Types</h2>
            <table>
                <thead><tr><th>Name</th><th>Category</th><th>Unit</th><th>Published</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($activityTypes as $type): ?>
                    <tr>
                        <td><?= htmlspecialchars($type['name']) ?></td>
                        <td><span class="category-tag"><?= htmlspecialchars($type['category']) ?></span></td>
                        <td><?= htmlspecialchars($type['default_unit']) ?></td>
                        <td><?= $type['is_published'] ? 'Yes' : 'No' ?></td>
                        <td>
                            <form method="POST" action="admin.php" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="activity_type_id" value="<?= $type['activity_type_id'] ?>">
                                <button type="submit" class="btn-danger" onclick="return confirm('Delete this activity type?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>