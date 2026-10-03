<?php
/**
 * File: forgot-password.php
 * Purpose: Handles the initial password reset request.
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/Database.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    
    if ($email) {
        $db = Database::getInstance()->getConnection();
        
        // 1. Check if user exists (but don't reveal if they do or not in the UI)
        $stmt = $db->prepare("SELECT user_id FROM users WHERE email = :email AND account_status = 'active'");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // 2. Generate secure token (64 chars) and hash it for storage
            $rawToken = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $rawToken);
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry
            $ipHash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');

            // 3. Insert token into database
            $insertStmt = $db->prepare("
                INSERT INTO password_reset_tokens (user_id, token_hash, expires_at, request_ip_hash)
                VALUES (:uid, :hash, :exp, :ip)
            ");
            $insertStmt->execute([
                'uid' => $user['user_id'],
                'hash' => $tokenHash,
                'exp' => $expiresAt,
                'ip' => $ipHash
            ]);

            // 4. Send Email using native PHP mail()
            // CRITICAL: Change this to your actual Arden hosting email address!
            $fromEmail = 'noreply@stu99999.webhosting.arden.ac.uk'; 
            $subject = 'FitTrack Password Reset Request';
            
            // Build the reset link. Change the URL to your actual Arden URL.
            $resetLink = "https://stu99999.webhosting.arden.ac.uk/COM6011/reset-password.php?token=" . $rawToken;
            
            $body = "Hello,\n\n";
            $body .= "You recently requested to reset your password for your FitTrack account.\n";
            $body .= "Click the link below to reset it. This link will expire in 1 hour.\n\n";
            $body .= $resetLink . "\n\n";
            $body .= "If you did not request a password reset, please ignore this email.\n\n";
            $body .= "Regards,\nFitTrack Team";

            $headers = "From: FitTrack <" . $fromEmail . ">\r\n";
            $headers .= "Reply-To: " . $fromEmail . "\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            // Attempt to send. We don't show errors to the user for security.
            @mail($email, $subject, $body, $headers);
        }

        // Always show this message to prevent email enumeration attacks
        $message = "If an account exists with that email address, a password reset link has been sent.";
        $messageType = 'success';
    } else {
        $message = "Please enter a valid email address.";
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - FitTrack</title>
    <link rel="stylesheet" href="/css/dashboard.css">
    <style>
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: var(--bg); }
        .auth-card { background: var(--card); padding: 2.5rem; border-radius: 16px; box-shadow: 0 4px 24px var(--shadow); width: 100%; max-width: 400px; }
        .auth-card h2 { margin-bottom: 1rem; color: var(--text); text-align: center; }
        .auth-card p { font-size: 0.9rem; opacity: 0.7; text-align: center; margin-bottom: 1.5rem; }
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
        <h2>Reset Password</h2>
        <p>Enter your email address and we'll send you a link to reset your password.</p>
        
        <?php if ($message): ?>
            <div class="alert <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="you@example.com">
            </div>
            <button type="submit" class="btn-primary">Send Reset Link</button>
        </form>
        
        <a href="/login.php" class="back-link">Back to Login</a>
    </div>
</body>
</html>