<?php
declare(strict_types=1);

/**
 * File: login.php
 * Location: /public/login.php
 *
 * Purpose:
 * Authentication controller and split-screen entry point.
 * Handles user credential verification, session initialisation,
 * and bot mitigation via a CSS-hidden honeypot field.
 *
 * Security:
 * - Uses timing-safe comparison (hash_equals) for CSRF tokens.
 * - Regenerates session ID upon successful login to prevent session fixation.
 * - Returns generic error messages to prevent user enumeration attacks.
 * - Honeypot field traps automated bots without affecting human UX.
 */

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
    // 0. Honeypot Check: If the hidden field is filled, it's a bot.
    if (!empty($_POST['website_url'])) {
        header('Location: login.php?error=1');
        exit;
    }

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
            $_SESSION['last_activity'] = time();
            
            // 7. Redirect to the protected dashboard.
            header('Location: dashboard.php');
            exit;
        } else {
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
    <link rel="icon" type="image/png" href="/favicon.png">
</head>
<body class="auth-page">
    <!-- Added <main> landmark for accessibility -->
    <main role="main" class="login-split-container">
        <!-- Left Side: Visual/Branding -->
        <div class="login-visual">
            <div class="login-visual-content">
                <h1>FitTrack</h1>
                <p>Track your physical wellbeing, set ambitious goals, and connect with your community. Your journey to a healthier you starts here.</p>
            </div>
        </div>
        
        <!-- Right Side: Login Form -->
        <div class="login-form-side">
            <div class="login-form-card">
                <h2>Welcome back</h2>
                <p class="login-subtitle">Please enter your details to sign in.</p>
                
                <?php if ($error): ?>
                    <div class="alert alert-error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                
                <form method="POST" action="login.php">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                    
                    <!-- Honeypot Field -->
                    <div class="hp-field" aria-hidden="true">
                        <label for="website_url">Website</label>
                        <input type="text" id="website_url" name="website_url" tabindex="-1" autocomplete="off">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required autocomplete="email" autofocus placeholder="name@example.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                    </div>
                    
                    <div class="login-options">
                        <label class="remember-me" style="color: #2D1B2E !important; font-weight: 700;">
                            <input type="checkbox" name="remember"> Remember me
                        </label>
                        <a href="/forgot-password.php" class="forgot-link" style="color: #2D1B2E !important; font-weight: 700; text-decoration: none;">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="btn-primary" style="width: 100%; background-color: #5D2E5D !important;">Sign In</button>
                </form>
                
                <div class="login-footer">
                    Don't have an account? <a href="register.php">Register here</a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>