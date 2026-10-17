<?php
/**
 * File: settings.php
 * Location: /public/settings.php
 *
 * Purpose:
 * Allows users to manage their account preferences, security settings, 
 * and profile information. Demonstrates secure password handling and 
 * user preference persistence.
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/Session.php';
require_once __DIR__ . '/../config/Database.php';
Session::start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$userId = (int)$_SESSION['user_id'];
$tenantId = (int)$_SESSION['tenant_id'];

$message = '';
$messageType = '';

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token.');
    }

    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'update_preferences') {
            $distanceUnit = $_POST['distance_unit'] ?? 'km';
            $weightUnit = $_POST['weight_unit'] ?? 'kg';
            $temperatureUnit = $_POST['temperature_unit'] ?? 'celsius';
            $visibility = $_POST['default_activity_visibility'] ?? 'private';
            $theme = $_POST['theme'] ?? 'system';

            $stmt = $db->prepare("
                UPDATE user_preferences 
                SET distance_unit = :dist, weight_unit = :weight, temperature_unit = :temp, 
                    default_activity_visibility = :vis, theme = :theme
                WHERE user_id = :uid AND tenant_id = :tid
            ");
            $stmt->execute([
                'dist' => $distanceUnit, 'weight' => $weightUnit, 'temp' => $temperatureUnit,
                'vis' => $visibility, 'theme' => $theme, 'uid' => $userId, 'tid' => $tenantId
            ]);
            $message = 'Preferences updated successfully.';
            $messageType = 'success';
            
        } elseif ($action === 'change_password') {
            $currentPwd = $_POST['current_password'] ?? '';
            $newPwd = $_POST['new_password'] ?? '';
            $confirmPwd = $_POST['confirm_password'] ?? '';

            if ($newPwd !== $confirmPwd) {
                throw new Exception('New passwords do not match.');
            }
            if (strlen($newPwd) < 8) {
                throw new Exception('Password must be at least 8 characters.');
            }

            // Verify current password
            $stmt = $db->prepare("SELECT password_hash FROM users WHERE user_id = :uid AND tenant_id = :tid");
            $stmt->execute(['uid' => $userId, 'tid' => $tenantId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($currentPwd, $user['password_hash'])) {
                throw new Exception('Current password is incorrect.');
            }

            $newHash = password_hash($newPwd, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password_hash = :hash WHERE user_id = :uid AND tenant_id = :tid");
            $stmt->execute(['hash' => $newHash, 'uid' => $userId, 'tid' => $tenantId]);
            
            $message = 'Password changed successfully.';
            $messageType = 'success';
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

// Fetch current preferences
$stmt = $db->prepare("SELECT * FROM user_preferences WHERE user_id = :uid AND tenant_id = :tid");
$stmt->execute(['uid' => $userId, 'tid' => $tenantId]);
$prefs = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
    'distance_unit' => 'km', 'weight_unit' => 'kg', 'temperature_unit' => 'celsius',
    'default_activity_visibility' => 'private', 'theme' => 'system'
];

$currentTheme = $prefs['theme'] ?? 'system';

$pageTitle = 'Settings';
$currentPage = 'settings';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<div class="main-content">
    <?php if ($message): ?>
    <div class="alert alert-<?= htmlspecialchars($messageType) ?>" role="alert" style="padding: 1rem; background: <?= $messageType === 'success' ? '#d4edda' : '#f8d7da' ?>; color: <?= $messageType === 'success' ? '#155724' : '#721c24' ?>; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid <?= $messageType === 'success' ? '#c3e6cb' : '#f5c6cb' ?>;">
        <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <div class="card">
        <h2>Account Settings</h2>
        
        <!-- Settings Tabs -->
        <div class="settings-tabs" style="display: flex; gap: 1rem; border-bottom: 1px solid var(--border); margin-bottom: 2rem;">
            <!-- FIX: Changed active tab text color to #5D2E5D to fix WAVE contrast error -->
            <button class="tab-btn active" data-tab="preferences" style="opacity: 1; color: #5D2E5D; background-color: rgb(245, 245, 245); padding: 0.75rem 1.5rem; border: none; border-bottom: 3px solid var(--accent); cursor: pointer; font-weight: 600; font-size: 1rem;">Preferences</button>
            <button class="tab-btn" data-tab="security" style="opacity: 0.7; color: var(--text); background: none; border: none; border-bottom: 3px solid transparent; cursor: pointer; font-weight: 600; font-size: 1rem;">Security</button>
            <button class="tab-btn" data-tab="account" style="opacity: 0.7; color: var(--text); background: none; border: none; border-bottom: 3px solid transparent; cursor: pointer; font-weight: 600; font-size: 1rem;">Account</button>
        </div>

        <!-- Tab Content: Preferences -->
        <div id="tab-preferences" class="tab-content active">
            <h3>Appearance</h3>
            <p style="margin-bottom: 1.5rem; color: var(--text);">Choose how FitTrack looks. This syncs with the header theme toggle.</p>
            
            <!-- FIX: Wrapped radio buttons in a fieldset with a legend to fix WAVE "Missing fieldset" and "Orphaned label" errors -->
            <fieldset class="theme-options" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; border: none; padding: 0; margin: 0 0 2rem 0;">
                <legend class="sr-only">Select Theme</legend>
                <div class="theme-card">
                    <input type="radio" name="theme" id="theme_system" value="system" <?= $currentTheme === 'system' ? 'checked' : '' ?>>
                    <label for="theme_system" style="display: flex; flex-direction: column; align-items: center; padding: 1.5rem; border: 2px solid var(--border); border-radius: 12px; cursor: pointer; transition: all 0.2s;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 32px; height: 32px; margin-bottom: 0.5rem;"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                        System
                    </label>
                </div>
                <div class="theme-card">
                    <input type="radio" name="theme" id="theme_light" value="light" <?= $currentTheme === 'light' ? 'checked' : '' ?>>
                    <label for="theme_light" style="display: flex; flex-direction: column; align-items: center; padding: 1.5rem; border: 2px solid var(--border); border-radius: 12px; cursor: pointer; transition: all 0.2s;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 32px; height: 32px; margin-bottom: 0.5rem;"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                        Light
                    </label>
                </div>
                <div class="theme-card">
                    <input type="radio" name="theme" id="theme_dark" value="dark" <?= $currentTheme === 'dark' ? 'checked' : '' ?>>
                    <label for="theme_dark" style="display: flex; flex-direction: column; align-items: center; padding: 1.5rem; border: 2px solid var(--border); border-radius: 12px; cursor: pointer; transition: all 0.2s;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 32px; height: 32px; margin-bottom: 0.5rem;"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                        Dark
                    </label>
                </div>
            </fieldset>

            <h3>Units & Privacy</h3>
            <form method="POST" action="settings.php" id="prefForm" style="display: grid; gap: 1.5rem; max-width: 600px;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="action" value="update_preferences">
                
                <div class="form-group">
                    <label for="distance_unit" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #222;">Distance Unit</label>
                    <select name="distance_unit" id="distance_unit" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; color: #222; background: #fff;">
                        <option value="km" <?= $prefs['distance_unit'] === 'km' ? 'selected' : '' ?>>Kilometers (km)</option>
                        <option value="mi" <?= $prefs['distance_unit'] === 'mi' ? 'selected' : '' ?>>Miles (mi)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="weight_unit" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #222;">Weight Unit</label>
                    <select name="weight_unit" id="weight_unit" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; color: #222; background: #fff;">
                        <option value="kg" <?= $prefs['weight_unit'] === 'kg' ? 'selected' : '' ?>>Kilograms (kg)</option>
                        <option value="lbs" <?= $prefs['weight_unit'] === 'lbs' ? 'selected' : '' ?>>Pounds (lbs)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="temperature_unit" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #222;">Temperature Unit</label>
                    <select name="temperature_unit" id="temperature_unit" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; color: #222; background: #fff;">
                        <option value="celsius" <?= $prefs['temperature_unit'] === 'celsius' ? 'selected' : '' ?>>Celsius (°C)</option>
                        <option value="fahrenheit" <?= $prefs['temperature_unit'] === 'fahrenheit' ? 'selected' : '' ?>>Fahrenheit (°F)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="default_activity_visibility" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #222;">Default Activity Visibility</label>
                    <select name="default_activity_visibility" id="default_activity_visibility" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; color: #222; background: #fff;">
                        <option value="public" <?= $prefs['default_activity_visibility'] === 'public' ? 'selected' : '' ?>>Public (Visible to all tenants)</option>
                        <option value="private" <?= $prefs['default_activity_visibility'] === 'private' ? 'selected' : '' ?>>Private (Only visible to you)</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary" style="padding: 0.75rem 1.5rem; justify-self: start;">Save Preferences</button>
            </form>
        </div>

        <!-- Tab Content: Security -->
        <div id="tab-security" class="tab-content" style="display: none;">
            <h3>Change Password</h3>
            <form method="POST" action="settings.php" id="pwdForm" style="display: grid; gap: 1.5rem; max-width: 600px;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="action" value="change_password">
                
                <div class="form-group">
                    <label for="current_password" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #222;">Current Password</label>
                    <input type="password" name="current_password" id="current_password" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; color: #222;">
                </div>

                <div class="form-group">
                    <label for="new_password" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #222;">New Password</label>
                    <input type="password" name="new_password" id="new_password" required minlength="8" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; color: #222;">
                    <small style="color: #555; margin-top: 0.25rem; display: block;">Minimum 8 characters.</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #222;">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" required minlength="8" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; color: #222;">
                </div>

                <button type="submit" class="btn-primary" style="padding: 0.75rem 1.5rem; justify-self: start;">Update Password</button>
            </form>
        </div>

        <!-- Tab Content: Account -->
        <div id="tab-account" class="tab-content" style="display: none;">
            <h3>Account Management</h3>
            <p style="color: var(--text); margin-bottom: 1.5rem;">Manage your account details and data.</p>
            <div style="padding: 1.5rem; background: var(--bg); border-radius: 8px; border: 1px solid var(--border);">
                <h4 style="margin-top: 0; color: #222;">Data Export</h4>
                <p style="color: #555; margin-bottom: 1rem;">Download a copy of all your fitness data in JSON format.</p>
                <a href="/api/export.php" class="btn-primary" style="display: inline-block; text-decoration: none; padding: 0.75rem 1.5rem;">Export My Data</a>
            </div>
        </div>
    </div>
</div>

<script>
// Simple Tab Switching Logic
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        // Remove active class from all buttons and contents
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('active');
            b.style.opacity = '0.7';
            b.style.borderBottomColor = 'transparent';
            b.style.backgroundColor = 'transparent';
            // FIX: Ensure inactive tabs use dark text for contrast
            b.style.color = '#222'; 
        });
        document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');

        // Add active class to clicked button and show content
        btn.classList.add('active');
        btn.style.opacity = '1';
        btn.style.borderBottomColor = 'var(--accent)';
        btn.style.backgroundColor = 'rgb(245, 245, 245)';
        // FIX: Ensure active tab uses dark text for contrast
        btn.style.color = '#5D2E5D'; 
        
        const tabId = 'tab-' + btn.dataset.tab;
        document.getElementById(tabId).style.display = 'block';
    });
});

// Theme selection visual feedback
document.querySelectorAll('input[name="theme"]').forEach(radio => {
    radio.addEventListener('change', (e) => {
        document.querySelectorAll('.theme-card label').forEach(label => {
            label.style.borderColor = 'var(--border)';
        });
        e.target.nextElementSibling.style.borderColor = 'var(--accent)';
    });
});

// Initialize theme border on load
const checkedTheme = document.querySelector('input[name="theme"]:checked');
if (checkedTheme) {
    checkedTheme.nextElementSibling.style.borderColor = 'var(--accent)';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>