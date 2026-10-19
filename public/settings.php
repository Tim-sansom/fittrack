<?php
/**
 * File: settings.php
 * Location: /public/settings.php
 *
 * Purpose:
 * Allows users to manage their account preferences, security settings,
 * and profile information. Demonstrates secure password handling,
 * user preference persistence, and Two-Factor Authentication (TOTP).
 *
 * Architectural Role:
 * Serves as the primary MPA controller for user configuration. It handles
 * form submissions for preferences, password changes, and 2FA toggling.
 *
 * Security:
 * - CSRF tokens validated on all state-changing POST requests.
 * - Password verification uses timing-safe comparison.
 * - TOTP secrets are encrypted at rest using AES-256-CBC.
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/Session.php';
require_once __DIR__ . '/../config/Database.php';
Session::start();
// Explicitly set SPA mode to MPA so the header toggle renders correctly
$_SESSION['spa_mode'] = 'mpa';
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
                default_activity_visibility = :vis, theme_preference = :theme
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
            if ($newPwd !== $confirmPwd) throw new Exception('New passwords do not match.');
            if (strlen($newPwd) < 12) throw new Exception('Password must be at least 12 characters.');
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
    'default_activity_visibility' => 'private', 'theme_preference' => 'system'
];
$currentTheme = $prefs['theme_preference'] ?? 'system';
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
            <button class="tab-btn active" data-tab="preferences" style="opacity: 1; color: #5D2E5D; background-color: rgb(245, 245, 245); padding: 0.75rem 1.5rem; border: none; border-bottom: 3px solid var(--accent); cursor: pointer; font-weight: 600; font-size: 1rem;" data-tooltip="Manage your display and unit preferences.">Preferences</button>
            <button class="tab-btn" data-tab="security" style="opacity: 0.7; color: var(--text); background: none; border: none; border-bottom: 3px solid transparent; cursor: pointer; font-weight: 600; font-size: 1rem;" data-tooltip="Manage your password and 2FA settings.">Security</button>
            <button class="tab-btn" data-tab="account" style="opacity: 0.7; color: var(--text); background: none; border: none; border-bottom: 3px solid transparent; cursor: pointer; font-weight: 600; font-size: 1rem;" data-tooltip="Manage your account data and exports.">Account</button>
        </div>
        <!-- Tab Content: Preferences -->
        <div id="tab-preferences" class="tab-content active">
            <h3>Appearance</h3>
            <p style="margin-bottom: 1.5rem; color: var(--text);">Choose how FitTrack looks. This syncs with the header theme toggle.</p>
            <fieldset class="theme-options" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; border: none; padding: 0; margin: 0 0 2rem 0;">
                <legend class="sr-only">Select Theme</legend>
                <div class="theme-card">
                    <input type="radio" name="theme" id="theme_system" value="system" <?= $currentTheme === 'system' ? 'checked' : '' ?> data-tooltip="Matches your device's system theme.">
                    <label for="theme_system" style="display: flex; flex-direction: column; align-items: center; padding: 1.5rem; border: 2px solid var(--border); border-radius: 12px; cursor: pointer; transition: all 0.2s;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 32px; height: 32px; margin-bottom: 0.5rem;"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                        System
                    </label>
                </div>
                <div class="theme-card">
                    <input type="radio" name="theme" id="theme_light" value="light" <?= $currentTheme === 'light' ? 'checked' : '' ?> data-tooltip="Forces a light colour scheme.">
                    <label for="theme_light" style="display: flex; flex-direction: column; align-items: center; padding: 1.5rem; border: 2px solid var(--border); border-radius: 12px; cursor: pointer; transition: all 0.2s;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 32px; height: 32px; margin-bottom: 0.5rem;"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                        Light
                    </label>
                </div>
                <div class="theme-card">
                    <input type="radio" name="theme" id="theme_dark" value="dark" <?= $currentTheme === 'dark' ? 'checked' : '' ?> data-tooltip="Forces a dark colour scheme.">
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
                    <select name="distance_unit" id="distance_unit" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; color: #222; background: #fff;" data-tooltip="Preferred unit for tracking distance.">
                        <option value="km" <?= $prefs['distance_unit'] === 'km' ? 'selected' : '' ?>>Kilometers (km)</option>
                        <option value="mi" <?= $prefs['distance_unit'] === 'mi' ? 'selected' : '' ?>>Miles (mi)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="weight_unit" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #222;">Weight Unit</label>
                    <select name="weight_unit" id="weight_unit" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; color: #222; background: #fff;" data-tooltip="Preferred unit for body weight.">
                        <option value="kg" <?= $prefs['weight_unit'] === 'kg' ? 'selected' : '' ?>>Kilograms (kg)</option>
                        <option value="lbs" <?= $prefs['weight_unit'] === 'lbs' ? 'selected' : '' ?>>Pounds (lbs)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="temperature_unit" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #222;">Temperature Unit</label>
                    <select name="temperature_unit" id="temperature_unit" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; color: #222; background: #fff;" data-tooltip="Preferred unit for weather/temperature.">
                        <option value="celsius" <?= $prefs['temperature_unit'] === 'celsius' ? 'selected' : '' ?>>Celsius (°C)</option>
                        <option value="fahrenheit" <?= $prefs['temperature_unit'] === 'fahrenheit' ? 'selected' : '' ?>>Fahrenheit (°F)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="default_activity_visibility" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #222;">Default Activity Visibility</label>
                    <select name="default_activity_visibility" id="default_activity_visibility" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; color: #222; background: #fff;" data-tooltip="Default visibility for new activities.">
                        <option value="public" <?= $prefs['default_activity_visibility'] === 'public' ? 'selected' : '' ?>>Public (Visible to all tenants)</option>
                        <option value="private" <?= $prefs['default_activity_visibility'] === 'private' ? 'selected' : '' ?>>Private (Only visible to you)</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary" style="padding: 0.75rem 1.5rem; justify-self: start;" data-tooltip="Saves your preference changes.">Save Preferences</button>
            </form>
        </div>
        <!-- Tab Content: Security -->
        <div id="tab-security" class="tab-content" style="display: none;">
            <h3>Change Password</h3>
            <form method="POST" action="settings.php" id="pwdForm" style="display: grid; gap: 1.5rem; max-width: 600px; margin-bottom: 3rem;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="action" value="change_password">
                <div class="form-group">
                    <label for="current_password" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #222;">Current Password</label>
                    <input type="password" name="current_password" id="current_password" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; color: #222;" data-tooltip="Enter your existing password.">
                </div>
                <div class="form-group">
                    <label for="new_password" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #222;">New Password</label>
                    <input type="password" name="new_password" id="new_password" required minlength="12" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; color: #222;" data-tooltip="Enter a new password (min 12 characters).">
                    <small style="color: #555; margin-top: 0.25rem; display: block;">Minimum 12 characters.</small>
                </div>
                <div class="form-group">
                    <label for="confirm_password" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #222;">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" required minlength="12" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; color: #222;" data-tooltip="Re-enter your new password to confirm.">
                </div>
                <button type="submit" class="btn-primary" style="padding: 0.75rem 1.5rem; justify-self: start;" data-tooltip="Updates your account password.">Update Password</button>
            </form>
            <hr style="border: 0; border-top: 1px solid var(--border); margin: 2rem 0;">
            <h3>Two-Factor Authentication (2FA)</h3>
            <p style="color: var(--text); margin-bottom: 1.5rem;">Add an extra layer of security to your account using an authenticator app.</p>
            <div id="totp-container" style="max-width: 600px; background: var(--bg); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border);">
                <div id="totp-status-loading">Checking 2FA status...</div>
                <div id="totp-disabled-view" style="display: none;">
                    <p style="margin-bottom: 1rem;">2FA is currently <strong>disabled</strong>.</p>
                    <button id="btn-enable-totp" class="btn-primary" style="padding: 0.5rem 1rem;" data-tooltip="Starts the 2FA setup process.">Enable 2FA</button>
                </div>
                <div id="totp-setup-view" style="display: none;">
                    <p style="margin-bottom: 1rem;">Scan the QR code below with your authenticator app (e.g., Google Authenticator, Authy), or enter the secret key manually.</p>
                    <div style="background: white; padding: 1rem; border-radius: 8px; display: inline-block; margin-bottom: 1rem;">
                        <img id="totp-qr-code" src="" alt="QR Code" style="width: 150px; height: 150px;">
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.25rem;">Secret Key:</label>
                        <div id="totp-secret-key" style="font-family: monospace; font-size: 1.1rem; background: white; padding: 0.5rem; border-radius: 4px; border: 1px solid var(--border); word-break: break-all;"></div>
                    </div>
                    <div class="form-group">
                        <label for="totp-verify-code" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Enter 6-digit code to verify:</label>
                        <input type="text" id="totp-verify-code" maxlength="6" pattern="\d{6}" style="width: 150px; padding: 0.5rem; font-size: 1.2rem; text-align: center; letter-spacing: 2px;" data-tooltip="Enter the 6-digit code from your authenticator app.">
                    </div>
                    <button id="btn-verify-totp" class="btn-primary" style="padding: 0.5rem 1rem;" data-tooltip="Verifies the code and enables 2FA.">Verify & Enable</button>
                    <button id="btn-cancel-totp" class="btn-secondary" style="padding: 0.5rem 1rem; margin-left: 0.5rem;" data-tooltip="Cancels the 2FA setup process.">Cancel</button>
                    <div id="totp-setup-error" style="color: var(--alert); margin-top: 0.5rem; display: none;"></div>
                </div>
                <div id="totp-enabled-view" style="display: none;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                        <span style="background: rgba(76, 175, 80, 0.1); color: var(--success); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">Enabled</span>
                    </div>
                    <p style="margin-bottom: 1rem;">Your account is protected with 2FA.</p>
                    <button id="btn-disable-totp" class="btn-danger" style="padding: 0.5rem 1rem;" data-tooltip="Disables 2FA protection on your account.">Disable 2FA</button>
                </div>
            </div>
        </div>
        <!-- Tab Content: Account -->
        <div id="tab-account" class="tab-content" style="display: none;">
            <h3>Account Management</h3>
            <p style="color: var(--text); margin-bottom: 1.5rem;">Manage your account details and data.</p>
            <div style="padding: 1.5rem; background: var(--bg); border-radius: 8px; border: 1px solid var(--border);">
                <h4 style="margin-top: 0; color: #222;">Data Export</h4>
                <p style="color: #555; margin-bottom: 1rem;">Download a copy of all your fitness data in JSON format.</p>
                <a href="api/export.php" class="btn-primary" style="display: inline-block; text-decoration: none; padding: 0.75rem 1.5rem;" data-tooltip="Downloads your fitness data as a JSON file.">Export My Data</a>
            </div>
        </div>
    </div>
</div>
<script>
// Tab Switching Logic
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('active');
            b.style.opacity = '0.7';
            b.style.borderBottomColor = 'transparent';
            b.style.backgroundColor = 'transparent';
            b.style.color = '#222';
        });
        document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
        btn.classList.add('active');
        btn.style.opacity = '1';
        btn.style.borderBottomColor = 'var(--accent)';
        btn.style.backgroundColor = 'rgb(245, 245, 245)';
        btn.style.color = '#5D2E5D';
        document.getElementById('tab-' + btn.dataset.tab).style.display = 'block';
    });
});
// Theme selection visual feedback
document.querySelectorAll('input[name="theme"]').forEach(radio => {
    radio.addEventListener('change', (e) => {
        document.querySelectorAll('.theme-card label').forEach(label => label.style.borderColor = 'var(--border)');
        e.target.nextElementSibling.style.borderColor = 'var(--accent)';
    });
});
const checkedTheme = document.querySelector('input[name="theme"]:checked');
if (checkedTheme) checkedTheme.nextElementSibling.style.borderColor = 'var(--accent)';
// =====================================================================
// TOTP / 2FA Logic
// =====================================================================
const totpContainer = document.getElementById('totp-container');
if (totpContainer) {
    const statusView = document.getElementById('totp-status-loading');
    const disabledView = document.getElementById('totp-disabled-view');
    const setupView = document.getElementById('totp-setup-view');
    const enabledView = document.getElementById('totp-enabled-view');
    const btnEnable = document.getElementById('btn-enable-totp');
    const btnVerify = document.getElementById('btn-verify-totp');
    const btnCancel = document.getElementById('btn-cancel-totp');
    const btnDisable = document.getElementById('btn-disable-totp');
    const qrImg = document.getElementById('totp-qr-code');
    const secretText = document.getElementById('totp-secret-key');
    const codeInput = document.getElementById('totp-verify-code');
    const errorMsg = document.getElementById('totp-setup-error');
    // 1. Check Status on Load
    fetch('api/totp.php')
        .then(res => res.json())
        .then(data => {
            statusView.style.display = 'none';
            if (data.success) {
                if (data.data.is_enabled) {
                    enabledView.style.display = 'block';
                } else {
                    disabledView.style.display = 'block';
                }
            }
        })
        .catch(err => {
            statusView.textContent = 'Error loading 2FA status.';
            console.error(err);
        });
    // 2. Enable Flow: Generate Secret
    btnEnable.addEventListener('click', () => {
        disabledView.style.display = 'none';
        setupView.style.display = 'block';
        errorMsg.style.display = 'none';
        fetch('api/totp.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'generate_secret' })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                secretText.textContent = data.secret;
                qrImg.src = `https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=${encodeURIComponent(data.otpauth_uri)}`;
            } else {
                errorMsg.textContent = data.error || 'Failed to generate secret.';
                errorMsg.style.display = 'block';
            }
        });
    });
    // 3. Verify Flow
    btnVerify.addEventListener('click', () => {
        const code = codeInput.value.trim();
        if (code.length !== 6) {
            errorMsg.textContent = 'Please enter a valid 6-digit code.';
            errorMsg.style.display = 'block';
            return;
        }
        fetch('api/totp.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'verify_code', code: code })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('2FA Enabled Successfully! Please save your recovery codes:\n\n' + data.recovery_codes.join('\n'));
                setupView.style.display = 'none';
                enabledView.style.display = 'block';
            } else {
                errorMsg.textContent = data.error || 'Invalid code.';
                errorMsg.style.display = 'block';
            }
        });
    });
    // 4. Cancel Setup
    btnCancel.addEventListener('click', () => {
        setupView.style.display = 'none';
        disabledView.style.display = 'block';
        codeInput.value = '';
        errorMsg.style.display = 'none';
    });
    // 5. Disable Flow
    btnDisable.addEventListener('click', () => {
        if (confirm('Are you sure you want to disable 2FA? This reduces your account security.')) {
            fetch('api/totp.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'disable' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    enabledView.style.display = 'none';
                    disabledView.style.display = 'block';
                } else {
                    alert('Failed to disable 2FA.');
                }
            });
        }
    });
}
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>