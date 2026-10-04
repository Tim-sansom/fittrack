<?php
/**
 * File: reset-password.php
 * Purpose: Validates the reset token and allows the user to set a new password.
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/Database.php';

$token = $_GET['token'] ?? '';
$message = '';
$messageType = '';
$validToken = false;

if (strlen($token) === 64) {
    $db = Database::getInstance()->getConnection();
    $tokenHash = hash('sha256', $token);

    // Find valid, unused token
    $stmt = $db->prepare("
        SELECT prt.user_id, u.username 
        FROM password_reset_tokens prt
        JOIN users u ON prt.user_id = u.user_id
        WHERE prt.token_hash = :hash 
        AND prt.expires_at > NOW() 
        AND prt.used_at IS NULL
        LIMIT 1
    ");
    $stmt->execute(['hash' => $tokenHash]);
    $resetData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resetData) {
        $validToken = true;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (strlen($newPassword) < 8) {
                $message = "Password must be at least 8 characters long.";
                $messageType = 'error';
            } elseif ($newPassword !== $confirmPassword) {
                $message = "Passwords do not match.";
                $messageType = 'error';
            } else {
                // Hash new password and update user
                $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
                $updateStmt = $db->prepare("UPDATE users SET password_hash = :hash WHERE user_id = :uid");
                $updateStmt->execute(['hash' => $newHash, 'uid' => $resetData['user_id']]);

                // Mark token as used
                $markStmt = $db->prepare("UPDATE password_reset_tokens SET used_at = NOW() WHERE token_hash = :hash");
                $markStmt->execute(['hash' => $tokenHash]);

                $message = "Password updated successfully. You can now log in.";
                $messageType = 'success';
                $validToken = false; // Hide the form
            }
        }
    } else {
        $message = "This reset link is invalid or has expired.";
        $messageType = 'error';
    }
} else {
    $message = "Invalid reset link.";
    $messageType = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - FitTrack</title>
    <link rel="stylesheet" href="/css/dashboard.css">
    <link rel="icon" type="image/svg+xml" href="/favicon.png">
</head>
    <style>
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: var(--bg); }
        .auth-card { background: var(--card); padding: 2.5rem; border-radius: 16px; box-shadow: 0 4px 24px var(--shadow); width: 100%; max-width: 400px; }
        .auth-card h2 { margin-bottom: 1rem; color: var(--text); text-align: center; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem; }
        .form-group input { width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box; }
        .btn-primary { width: 100%; padding: 0.875rem; background: var(--accent); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 1rem; }
        .btn-primary:hover { background: var(--text); }
        .alert { padding: 0.75rem; border-radius: 8px; font-size: 0.875rem; margin-bottom: 1rem; text-align: center; }
        .alert.success { background: rgba(76, 175, 80, 0.1); color: var(--success); }
        .alert.error { background: rgba(226, 105, 114, 0.1); color: var(--alert); }
        .back-link { display: block; text-align: center; margin-top: 1rem; color: var(--accent); text-decoration: none; font-size: 0.875rem; }
    </style>
</head>
<body>
    <div class="auth-card">
        <h2>Set New Password</h2>
        
        <?php if ($message): ?>
            <div class="alert <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($validToken): ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                </div>
                <button type="submit" class="btn-primary">Update Password</button>
            </form>
        <?php else: ?>
            <a href="/login.php" class="back-link">Return to Login</a>
        <?php endif; ?>
    </div>
</body>
</html>