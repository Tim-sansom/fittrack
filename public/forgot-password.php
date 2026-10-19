<?php
/**
 * File: forgot-password.php
 * Location: /public/forgot-password.php
 *
 * Purpose:
 * Collects the user's email address to initiate the password reset flow.
 * Does not reveal whether an email exists in the database to prevent
 * user enumeration attacks.
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/Session.php';
require_once __DIR__ . '/../config/Database.php';
Session::start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token.');
    }

    $email = trim($_POST['email'] ?? '');

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Check if user exists
        $stmt = $db->prepare("SELECT user_id FROM users WHERE email = :email AND account_status = 'active'");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $db->prepare("
                INSERT INTO password_resets (user_id, token, expires_at)
                VALUES (:uid, :token, :expires)
                ON DUPLICATE KEY UPDATE token = :token2, expires_at = :expires2
            ");
            $stmt->execute(['uid' => $user['user_id'], 'token' => $token, 'expires' => $expires, 'token2' => $token, 'expires2' => $expires]);

            // In a real app, send email here. For this project, we log it or show a mock message.
            error_log("Password reset token for {$email}: {$token}");
        }

        // Always show success message to prevent enumeration
        $message = 'If an account with that email exists, a password reset link has been sent.';
        $messageType = 'success';
    } else {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle = 'Forgot Password';
?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - FitTrack</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: var(--bg); margin: 0; font-family: system-ui, -apple-system, sans-serif; }
        .reset-container { background: var(--card); padding: 2.5rem; border-radius: 16px; box-shadow: 0 8px 32px var(--shadow); width: 100%; max-width: 400px; }
        .reset-header { text-align: center; margin-bottom: 2rem; }
        .reset-header h1 { color: var(--accent); margin: 0 0 0.5rem 0; font-size: 1.8rem; }
        .reset-header p { color: var(--text); opacity: 0.7; margin: 0; font-size: 0.9rem; line-height: 1.5; }
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
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <h1>Reset Password</h1>
            <p>Enter your email address and we will send you a link to reset your password.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>" role="alert"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" action="forgot-password.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required autocomplete="email" data-tooltip="Enter the email address linked to your account.">
            </div>

            <button type="submit" class="btn-primary" data-tooltip="Send a password reset link.">Send Reset Link</button>
        </form>

        <div class="reset-footer">
            <a href="login.php" data-tooltip="Return to the login page.">&larr; Back to Login</a>
        </div>
    </div>
</body>
</html>