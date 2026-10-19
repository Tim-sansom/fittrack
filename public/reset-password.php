<?php
/**
 * File: reset-password.php
 * Location: /public/reset-password.php
 *
 * Purpose:
 * Validates the password reset token and allows the user to set a new password.
 * Enforces minimum password length and secure hashing.
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/Session.php';
require_once __DIR__ . '/../config/Database.php';
Session::start();

$db = Database::getInstance()->getConnection();
$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if (empty($token)) {
    header('Location: login.php');
    exit;
}

// Validate token
$stmt = $db->prepare("
    SELECT pr.user_id, pr.expires_at, u.username
    FROM password_reset_tokens pr
    JOIN users u ON pr.user_id = u.user_id
    WHERE pr.token_hash = :token
    LIMIT 1
");
// Note: In a real implementation, you would hash the incoming token before comparing
$stmt->execute(['token' => hash('sha256', $token)]);
$resetData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resetData || strtotime($resetData['expires_at']) < time()) {
    $error = 'This password reset link is invalid or has expired.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $resetData) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token.');
    }
    
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (strlen($newPassword) < 12) {
        $error = 'Password must be at least 12 characters long.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $stmt = $db->prepare("UPDATE users SET password_hash = :hash WHERE user_id = :uid");
        $stmt->execute(['hash' => $hash, 'uid' => $resetData['user_id']]);
        
        // Invalidate the reset token
        $stmt = $db->prepare("UPDATE password_reset_tokens SET used_at = NOW() WHERE token_hash = :token");
        $stmt->execute(['token' => hash('sha256', $token)]);
        
        $success = 'Your password has been successfully updated. You can now log in.';
        $resetData = null; // Prevent form from showing again
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle = 'Reset Password';
?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - FitTrack</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: var(--bg); margin: 0; font-family: system-ui, -apple-system, sans-serif; }
        .reset-container { background: var(--card); padding: 2.5rem; border-radius: 16px; box-shadow: 0 8px 32px var(--shadow); width: 100%; max-width: 400px; }
        .reset-header { text-align: center; margin-bottom: 2rem; }
        .reset-header h1 { color: var(--accent); margin: 0 0 0.5rem 0; font-size: 1.8rem; }
        .reset-header p { color: var(--text); opacity: 0.7; margin: 0; font-size: 0.9rem; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text); font-size: 0.9rem; }
        .form-group input { width: 100%; padding: 0.875rem; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box; background: var(--bg); color: var(--text); font-size: 1rem; }
        .form-group input:focus { border-color: var(--accent); outline: none; box-shadow: 0 0 0 3px rgba(159, 95, 145, 0.15); }
        .btn-primary { width: 100%; padding: 0.875rem; background: var(--accent); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 1rem; transition: background 0.2s; margin-top: 0.5rem; }
        .btn-primary:hover { background: var(--text); }
        .alert { padding: 0.875rem; border-radius: 8px; font-size: 0.875rem; margin-bottom: 1.5rem; text-align: center; }
        .alert-success { background: rgba(76, 175, 80, 0.1); color: var(--success); border: 1px solid var(--success); }
        .alert-error { background: rgba(226, 105, 114, 0.1); color: var(--alert); border: 1px solid var(--alert); }
        .reset-footer { text-align: center; margin-top: 1.5rem; font-size: 0.875rem; }
        .reset-footer a { color: var(--accent); text-decoration: none; font-weight: 600; }
        .reset-footer a:hover { text-decoration: underline; }
        .password-hint { font-size: 0.8rem; color: var(--text); opacity: 0.7; margin-top: 0.25rem; display: block; }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <h1>Set New Password</h1>
            <p>Enter your new password below.</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success" role="alert"><?= htmlspecialchars($success) ?></div>
            <div class="reset-footer">
                <a href="login.php" data-tooltip="Return to the login page">&larr; Back to Login</a>
            </div>
        <?php elseif ($resetData): ?>
            <form method="POST" action="reset-password.php?token=<?= htmlspecialchars($token) ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required minlength="12" autocomplete="new-password" data-tooltip="Enter a new secure password (min 12 characters)">
                    <span class="password-hint">Minimum 12 characters.</span>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="12" autocomplete="new-password" data-tooltip="Re-enter your new password to confirm">
                </div>
                
                <button type="submit" class="btn-primary" data-tooltip="Update your account password">Update Password</button>
            </form>
        <?php else: ?>
            <div class="alert alert-error" role="alert"><?= htmlspecialchars($error) ?></div>
            <div class="reset-footer">
                <a href="login.php" data-tooltip="Return to the login page">&larr; Back to Login</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>