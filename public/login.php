<?php
/**
 * File: login.php
 * Location: /public/login.php
 * 
 * Purpose:
 * Authentication controller. Handles user credential verification and 
 * session initialisation.
 * 
 * Security:
 * - Uses timing-safe comparison (hash_equals) for CSRF tokens.
 * - Regenerates session ID upon successful login to prevent session fixation.
 * - Returns generic error messages to prevent user enumeration attacks.
 */
declare(strict_types=1);

// Fixed: Added '/../' to navigate up to the root config and src directories
require_once __DIR__ . '/../config/Session.php';
require_once __DIR__ . '/../src/models/User.php';

Session::start();

// Redirect authenticated users away from the login page.
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Generate CSRF token for the form if one does not exist.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

// Check for session timeout redirection flag.
if (isset($_GET['timeout']) && $_GET['timeout'] === '1') {
    $error = 'Your session has expired due to inactivity. Please log in again.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token using timing-safe comparison.
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('Invalid request. CSRF token mismatch.');
    }

    // 2. Sanitise and collect input.
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // 3. Server-side validation.
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        // 4. Attempt Authentication via Model.
        $userModel = new User();
        $user = $userModel->authenticate($email, $password);

        if ($user) {
            // 5. Regenerate session ID to prevent session fixation attacks.
            Session::regenerate();
            
            // 6. Store user context in session for subsequent requests.
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['tenant_id'] = $user['tenant_id'];
            $_SESSION['role'] = $user['role_key'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['last_activity'] = time(); // Initialise timeout tracker

            // 7. Redirect to the protected dashboard.
            header('Location: dashboard.php');
            exit;
        } else {
            // Generic error prevents attackers from knowing if the email exists.
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | FitTrack</title>
    <link rel="stylesheet" href="/css/dashboard.css">
    <style>
        /* Auth-specific layout overrides. Keeps the form centred and isolated. */
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .auth-container { background: var(--card); padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 20px var(--shadow); width: 100%; max-width: 360px; }
        h1 { text-align: center; margin-bottom: 1.5rem; color: var(--text); }
        .form-group { margin-bottom: 1.25rem; }
        label { display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem; }
        input { width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box; }
        input:focus { outline: none; border-color: var(--accent); }
        .btn-primary { width: 100%; padding: 0.875rem; background: var(--text); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 1rem; }
        .btn-primary:hover { background: var(--accent); }
        .message { padding: 0.75rem; border-radius: 8px; margin-bottom: 1.25rem; text-align: center; font-size: 0.875rem; background: rgba(226, 105, 114, 0.1); color: var(--alert); }
        .auth-link { text-align: center; margin-top: 1rem; font-size: 0.875rem; }
        .auth-link a { color: var(--accent); text-decoration: none; }
    </style>
</head>
<body>
    <div class="auth-container">
        <h1>FitTrack Login</h1>
        
        <?php if ($error): ?>
            <div class="message"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required autocomplete="email" autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            
            <button type="submit" class="btn-primary">Sign In</button>
        </form>
        
        <div class="auth-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</body>
</html>