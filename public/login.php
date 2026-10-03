<?php
/**
 * File: login.php
 * Location: /public/login.php
 *
 * Purpose:
 * Authenticates users via email and password. 
 * Sets session variables and redirects to the dashboard on success.
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
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $db->prepare("SELECT user_id, username, password_hash, account_status, tenant_id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['account_status'] !== 'active') {
                $error = 'Your account is not active. Please contact an administrator.';
            } else {
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = (int)$user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['tenant_id'] = (int)$user['tenant_id'];
                $_SESSION['last_activity'] = time();
                
                header('Location: dashboard.php');
                exit;
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - FitTrack</title>
    <link rel="stylesheet" href="/css/dashboard.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: var(--bg);
            margin: 0;
        }
        .auth-card {
            background: var(--card);
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 24px var(--shadow);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
        }
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .auth-header h1 {
            color: var(--accent);
            font-size: 1.75rem;
            margin: 0 0 0.5rem 0;
        }
        .auth-header p {
            color: var(--text);
            opacity: 0.7;
            margin: 0;
            font-size: 0.9rem;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text);
        }
        .form-group input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
            background: var(--card);
            color: var(--text);
            box-sizing: border-box;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-group input:focus {
            border-color: var(--accent);
            outline: none;
            box-shadow: 0 0 0 3px rgba(159, 95, 145, 0.15);
        }
        .btn-primary {
            width: 100%;
            padding: 0.875rem;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 0.5rem;
        }
        .btn-primary:hover {
            background: var(--text);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px var(--shadow);
        }
        .alert {
            background: rgba(226, 105, 114, 0.1);
            color: var(--alert);
            padding: 0.875rem;
            border-radius: 8px;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .auth-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.875rem;
            color: var(--text);
            opacity: 0.8;
        }
        .auth-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }
        .auth-link a:hover {
            text-decoration: underline;
        }
        .forgot-link {
            text-align: right;
            margin-top: 0.5rem;
            margin-bottom: 1.25rem;
            font-size: 0.85rem;
        }
        .forgot-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
        }
        .forgot-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-header">
            <h1>FitTrack</h1>
            <p>Sign in to your account</p>
        </div>

        <?php if ($error): ?>
            <div class="alert"><?= htmlspecialchars($error) ?></div>
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

            <div class="forgot-link">
                <a href="/forgot-password.php">Forgot your password?</a>
            </div>

            <button type="submit" class="btn-primary">Sign In</button>
        </form>

        <div class="auth-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</body>
</html>