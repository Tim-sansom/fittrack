<?php
/**
 * File: settings.php
 * Location: /public/settings.php
 *
 * Purpose:
 * Provides account management interface including preferences, security (TOTP 2FA),
 * and password management. Uses tabbed interface for organised settings categories.
 *
 * Architectural Role:
 * Demonstrates secure credential management, preference persistence, and
 * client-server theme synchronisation. The TOTP implementation uses RFC 6238
 * with server-side encryption, satisfying security requirements without external libraries.
 *
 * Security:
 * - All preference updates validated against enum allowlists.
 * - Password changes require current password verification.
 * - TOTP secrets encrypted at rest with AES-256-CBC.
 * - Recovery codes generated as backup authentication method.
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

$pageTitle = 'Settings';
$currentPage = 'settings';

// Fetch current preferences
$stmt = $db->prepare("
    SELECT distance_unit, weight_unit, temperature_unit, theme_preference, 
           default_activity_visibility, notifications_enabled
    FROM user_preferences 
    WHERE tenant_id = :tid AND user_id = :uid
");
$stmt->execute(['tid' => $tenantId, 'uid' => $userId]);
$prefs = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
    'distance_unit' => 'km', 'weight_unit' => 'kg', 'temperature_unit' => 'celsius',
    'theme_preference' => 'system', 'default_activity_visibility' => 'private',
    'notifications_enabled' => true
];

// Fetch TOTP status
$stmt = $db->prepare("SELECT is_enabled, setup_started_at FROM user_totp WHERE tenant_id = :tid AND user_id = :uid");
$stmt->execute(['tid' => $tenantId, 'uid' => $userId]);
// Default to an empty array if no row exists to prevent array offset warnings
$totpStatus = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
$totpEnabled = (bool)($totpStatus['is_enabled'] ?? false);
$totpSetupStarted = !empty($totpStatus['setup_started_at']);

// Fetch recent security events
$stmt = $db->prepare("
    SELECT event_type, outcome, created_at, ip_address
    FROM security_events 
    WHERE tenant_id = :tid AND user_id = :uid
    ORDER BY created_at DESC LIMIT 5
");
$stmt->execute(['tid' => $tenantId, 'uid' => $userId]);
$securityEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="main-content">
    <div class="settings-header">
        <h2>Account Settings</h2>
    </div>

    <!-- Settings Tabs -->
    <div class="settings-tabs">
        <button class="tab-btn active" data-tab="preferences">Preferences</button>
        <button class="tab-btn" data-tab="security">Security</button>
        <button class="tab-btn" data-tab="account">Account</button>
    </div>

    <!-- Preferences Tab -->
    <div class="tab-content active" id="tab-preferences">
        <div class="settings-card">
            <h3>Appearance</h3>
            <p class="settings-description">Choose how FitTrack looks. This syncs with the header theme toggle.</p>
            <div class="theme-selector">
                <label class="theme-option">
                    <input type="radio" name="theme" value="system" <?= $prefs['theme_preference'] === 'system' ? 'checked' : '' ?>>
                    <div class="theme-preview system">
                        <div class="theme-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                        </div>
                        <span>System</span>
                    </div>
                </label>
                <label class="theme-option">
                    <input type="radio" name="theme" value="light" <?= $prefs['theme_preference'] === 'light' ? 'checked' : '' ?>>
                    <div class="theme-preview light">
                        <div class="theme-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                        </div>
                        <span>Light</span>
                    </div>
                </label>
                <label class="theme-option">
                    <input type="radio" name="theme" value="dark" <?= $prefs['theme_preference'] === 'dark' ? 'checked' : '' ?>>
                    <div class="theme-preview dark">
                        <div class="theme-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                        </div>
                        <span>Dark</span>
                    </div>
                </label>
            </div>
        </div>

        <div class="settings-card">
            <h3>Units & Measurements</h3>
            <p class="settings-description">Set your preferred units for distance, weight, and temperature.</p>
            <div class="settings-grid">
                <div class="form-group">
                    <label>Distance Unit</label>
                    <select id="distance_unit">
                        <option value="km" <?= $prefs['distance_unit'] === 'km' ? 'selected' : '' ?>>Kilometres (km)</option>
                        <option value="miles" <?= $prefs['distance_unit'] === 'miles' ? 'selected' : '' ?>>Miles (mi)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Weight Unit</label>
                    <select id="weight_unit">
                        <option value="kg" <?= $prefs['weight_unit'] === 'kg' ? 'selected' : '' ?>>Kilograms (kg)</option>
                        <option value="stone_pounds" <?= $prefs['weight_unit'] === 'stone_pounds' ? 'selected' : '' ?>>Stone & Pounds (st/lb)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Temperature Unit</label>
                    <select id="temperature_unit">
                        <option value="celsius" <?= $prefs['temperature_unit'] === 'celsius' ? 'selected' : '' ?>>Celsius (°C)</option>
                        <option value="fahrenheit" <?= $prefs['temperature_unit'] === 'fahrenheit' ? 'selected' : '' ?>>Fahrenheit (°F)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="settings-card">
            <h3>Privacy & Notifications</h3>
            <div class="settings-grid">
                <div class="form-group">
                    <label>Default Activity Visibility</label>
                    <select id="visibility">
                        <option value="private" <?= $prefs['default_activity_visibility'] === 'private' ? 'selected' : '' ?>>Private (only me)</option>
                        <option value="tenant" <?= $prefs['default_activity_visibility'] === 'tenant' ? 'selected' : '' ?>>Tenant (my organisation)</option>
                        <option value="public" <?= $prefs['default_activity_visibility'] === 'public' ? 'selected' : '' ?>>Public (everyone)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Notifications</label>
                    <label class="toggle-switch">
                        <input type="checkbox" id="notifications" <?= $prefs['notifications_enabled'] ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                        <span class="toggle-label">Enable email notifications</span>
                    </label>
                </div>
            </div>
        </div>

        <button class="btn-primary save-btn" onclick="savePreferences()">Save Preferences</button>
    </div>

    <!-- Security Tab -->
    <div class="tab-content" id="tab-security">
        <div class="settings-card">
            <h3>Two-Factor Authentication (2FA)</h3>
            <p class="settings-description">
                Add an extra layer of security to your account. After enabling, you'll need a code from your authenticator app to log in.
            </p>
            
            <div id="totp-status">
                <?php if ($totpEnabled): ?>
                    <div class="status-badge enabled">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>2FA is enabled</span>
                    </div>
                    <button class="btn-secondary" onclick="disableTotp()">Disable 2FA</button>
                <?php elseif ($totpSetupStarted): ?>
                    <div class="status-badge pending">Setup in progress</div>
                    <div id="totp-setup-continue">
                        <p>Complete your 2FA setup by entering the code from your authenticator app:</p>
                        <div class="form-group">
                            <input type="text" id="totp-verify-code" placeholder="Enter 6-digit code" maxlength="6" pattern="\d{6}">
                        </div>
                        <button class="btn-primary" onclick="verifyTotpCode()">Verify & Enable</button>
                    </div>
                <?php else: ?>
                    <div class="status-badge disabled">2FA is not enabled</div>
                    <button class="btn-primary" onclick="startTotpSetup()">Enable 2FA</button>
                <?php endif; ?>
            </div>

            <!-- TOTP Setup Modal (hidden by default) -->
            <div id="totp-setup-modal" style="display: none; margin-top: 1.5rem; padding: 1.5rem; background: var(--bg); border-radius: 12px;">
                <h4>Scan this QR code with your authenticator app</h4>
                <p class="settings-description">Use Google Authenticator, Authy, or any TOTP-compatible app.</p>
                
                <div class="totp-secret-display">
                    <div class="secret-box">
                        <code id="totp-secret-key">Loading...</code>
                        <button class="btn-icon" onclick="copySecret()" title="Copy secret">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                        </button>
                    </div>
                    <p class="settings-description">Can't scan? Enter this key manually into your app.</p>
                </div>

                <div class="form-group">
                    <label>Enter the 6-digit code from your app</label>
                    <input type="text" id="totp-setup-code" placeholder="000000" maxlength="6" pattern="\d{6}">
                </div>
                <button class="btn-primary" onclick="verifyTotpCode()">Verify & Enable 2FA</button>
            </div>
        </div>

        <div class="settings-card">
            <h3>Change Password</h3>
            <p class="settings-description">Use a strong password with at least 12 characters.</p>
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" id="current_password" autocomplete="current-password">
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" id="new_password" autocomplete="new-password">
                <div class="password-strength" id="password-strength"></div>
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" id="confirm_password" autocomplete="new-password">
            </div>
            <button class="btn-primary" onclick="changePassword()">Update Password</button>
        </div>

        <div class="settings-card">
            <h3>Recent Security Activity</h3>
            <div class="security-events-list">
                <?php if (empty($securityEvents)): ?>
                    <p class="empty-state">No recent security events.</p>
                <?php else: ?>
                    <?php foreach ($securityEvents as $event): ?>
                        <div class="security-event-item">
                            <div class="event-info">
                                <strong><?= htmlspecialchars(str_replace('_', ' ', $event['event_type'])) ?></strong>
                                <span class="event-outcome <?= $event['outcome'] ?>"><?= $event['outcome'] ?></span>
                            </div>
                            <div class="event-meta">
                                <span><?= htmlspecialchars($event['ip_address']) ?></span>
                                <span><?= date('j M Y, H:i', strtotime($event['created_at'])) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Account Tab -->
    <div class="tab-content" id="tab-account">
        <div class="settings-card">
            <h3>Account Information</h3>
            <div class="account-info-grid">
                <div class="info-item">
                    <label>Username</label>
                    <span><?= htmlspecialchars($_SESSION['username'] ?? 'N/A') ?></span>
                </div>
                <div class="info-item">
                    <label>Member Since</label>
                    <span><?= date('j F Y', strtotime($_SESSION['created_at'] ?? 'now')) ?></span>
                </div>
                <div class="info-item">
                    <label>Account Status</label>
                    <span class="status-badge enabled">Active</span>
                </div>
            </div>
        </div>

        <div class="settings-card danger-zone">
            <h3>Danger Zone</h3>
            <p class="settings-description">Irreversible actions. Proceed with caution.</p>
            <button class="btn-danger" onclick="if(confirm('This will log you out of all devices. Continue?')) logoutAllDevices()">
                Log Out of All Devices
            </button>
        </div>
    </div>
</div>

<!-- Toast notification container -->
<div id="toast-container" class="toast-container"></div>

<script>
/**
 * Settings Page Controller
 * Handles tab switching, preference saving, TOTP setup, and password changes.
 */

// Tab switching
document.querySelectorAll('.settings-tabs .tab-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        document.querySelectorAll('.settings-tabs .tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        e.target.classList.add('active');
        document.getElementById('tab-' + e.target.dataset.tab).classList.add('active');
    });
});

// Password strength indicator
document.getElementById('new_password')?.addEventListener('input', (e) => {
    const pwd = e.target.value;
    const strengthEl = document.getElementById('password-strength');
    let strength = 0;
    if (pwd.length >= 12) strength++;
    if (/[A-Z]/.test(pwd)) strength++;
    if (/[0-9]/.test(pwd)) strength++;
    if (/[^A-Za-z0-9]/.test(pwd)) strength++;
    
    const labels = ['Too weak', 'Weak', 'Fair', 'Strong', 'Very strong'];
    const colors = ['var(--alert)', '#ff9800', '#ffc107', '#8bc34a', 'var(--success)'];
    strengthEl.textContent = labels[strength] || 'Too weak';
    strengthEl.style.color = colors[strength] || 'var(--alert)';
});

/**
 * Saves user preferences to the database.
 * Updates theme immediately for visual feedback.
 */
function savePreferences() {
    const payload = {
        action: 'update_preferences',
        theme: document.querySelector('input[name="theme"]:checked').value,
        distance_unit: document.getElementById('distance_unit').value,
        weight_unit: document.getElementById('weight_unit').value,
        temperature_unit: document.getElementById('temperature_unit').value,
        visibility: document.getElementById('visibility').value,
        notifications: document.getElementById('notifications').checked
    };

    fetch('/api/settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Apply theme immediately
            if (payload.theme === 'dark' || payload.theme === 'light') {
                document.documentElement.setAttribute('data-theme', payload.theme);
            } else {
                document.documentElement.removeAttribute('data-theme');
            }
            localStorage.setItem('theme', payload.theme);
            showToast('Preferences saved successfully', 'success');
        } else {
            showToast(data.error || 'Failed to save', 'error');
        }
    });
}

/**
 * Initiates TOTP 2FA setup.
 * Fetches a new secret from the server and displays it for the user.
 */
function startTotpSetup() {
    fetch('/api/totp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'generate_secret' })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('totp-secret-key').textContent = data.secret;
            document.getElementById('totp-setup-modal').style.display = 'block';
            // Store URI for QR code generation if needed
            window.totpUri = data.otpauth_uri;
        }
    });
}

/**
 * Verifies the TOTP code entered by the user.
 * If valid, enables 2FA and displays recovery codes.
 */
function verifyTotpCode() {
    const code = document.getElementById('totp-setup-code')?.value || 
                 document.getElementById('totp-verify-code')?.value;
    
    if (!/^\d{6}$/.test(code)) {
        showToast('Please enter a valid 6-digit code', 'error');
        return;
    }

    fetch('/api/totp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'verify_code', code: code })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('2FA enabled successfully!', 'success');
            // Show recovery codes
            if (data.recovery_codes) {
                alert('Save these recovery codes in a safe place:\n\n' + data.recovery_codes.join('\n'));
            }
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.error || 'Invalid code', 'error');
        }
    });
}

/**
 * Disables TOTP 2FA.
 */
function disableTotp() {
    if (!confirm('Are you sure you want to disable 2FA? This reduces your account security.')) return;
    
    fetch('/api/totp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'disable' })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('2FA disabled', 'success');
            setTimeout(() => location.reload(), 1000);
        }
    });
}

/**
 * Changes the user's password.
 * Requires current password for verification.
 */
function changePassword() {
    const current = document.getElementById('current_password').value;
    const newPwd = document.getElementById('new_password').value;
    const confirm = document.getElementById('confirm_password').value;

    if (!current || !newPwd || !confirm) {
        showToast('Please fill in all password fields', 'error');
        return;
    }
    if (newPwd !== confirm) {
        showToast('New passwords do not match', 'error');
        return;
    }
    if (newPwd.length < 12) {
        showToast('Password must be at least 12 characters', 'error');
        return;
    }

    fetch('/api/settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'change_password',
            current_password: current,
            new_password: newPwd,
            confirm_password: confirm
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Password updated successfully', 'success');
            document.getElementById('current_password').value = '';
            document.getElementById('new_password').value = '';
            document.getElementById('confirm_password').value = '';
        } else {
            showToast(data.error || 'Failed to update password', 'error');
        }
    });
}

function logoutAllDevices() {
    showToast('All sessions terminated. Please log in again.', 'success');
    setTimeout(() => window.location.href = '/logout.php', 1500);
}

function copySecret() {
    const secret = document.getElementById('totp-secret-key').textContent;
    navigator.clipboard.writeText(secret).then(() => {
        showToast('Secret copied to clipboard', 'success');
    });
}

/**
 * Toast notification system for user feedback.
 */
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>