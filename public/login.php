<?php
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
declare(strict_types=1);

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
        // Log silently or just redirect to prevent bot enumeration
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
    <link rel="icon" type="image/png" href="/favicon.png">
    <style>
        /* Split-Screen Login Layout */
        .login-split-container {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }
        .login-visual {
            flex: 1;
            background: url('/assets/images/login-bg.webp') no-repeat center center;
            background-size: cover;
            position: relative;
            display: none; /* Hidden on mobile */
        }
        .login-visual::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(128, 90, 128, 0.85) 0%, rgba(40, 20, 40, 0.9) 100%);
        }
        .login-visual-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 4rem;
            color: white;
        }
        .login-visual-content h1 {
            font-size: 3.5rem;
            margin: 0 0 1rem 0;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .login-visual-content p {
            font-size: 1.25rem;
            opacity: 0.9;
            max-width: 500px;
            line-height: 1.6;
        }
        .login-form-side {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: var(--bg, #f8f9fa);
        }
        .login-form-card {
            width: 100%;
            max-width: 420px;
            background: white;
            padding: 3rem;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        }
        .login-form-card h2 {
            margin: 0 0 0.5rem 0;
            color: var(--text, #333);
            font-size: 1.75rem;
        }
        .login-subtitle {
            color: #666;
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text, #333);
            font-size: 0.9rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.85rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--accent, #805a80);
            box-shadow: 0 0 0 3px rgba(128, 90, 128, 0.15);
        }
        .btn-primary {
            width: 100%;
            padding: 0.9rem;
            background: var(--accent, #805a80);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 1rem;
        }
        .btn-primary:hover {
            background: #6b4a6b;
        }
        .login-footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.9rem;
            color: #666;
        }
        .login-footer a {
            color: var(--accent, #805a80);
            text-decoration: none;
            font-weight: 600;
        }
        .alert {
            background: #fee2e2;
            color: #991b1b;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            border: 1px solid #fecaca;
        }
        /* Honeypot: Hidden from humans, visible to bots */
        .hp-field {
            position: absolute;
            left: -9999px;
            opacity: 0;
            height: 0;
            width: 0;
            overflow: hidden;
        }
        @media (min-width: 900px) {
            .login-visual { display: block; }
        }
    </style>
</head>
<body class="auth-page">
    <div class="login-split-container">
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
                    <div class="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
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

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; font-size: 0.85rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: #666;">
                            <input type="checkbox" name="remember" style="accent-color: var(--accent, #805a80);"> Remember me
                        </label>
                        <a href="/forgot-password.php" style="color: var(--accent, #805a80); text-decoration: none; font-weight: 600;">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn-primary">Sign In</button>
                </form>

                <div class="login-footer">
                    Don't have an account? <a href="register.php">Register here</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>