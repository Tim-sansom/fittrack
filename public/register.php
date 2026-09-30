<?php
/**
 * File: register.php
 * Location: /public/register.php
 * 
 * Purpose:
 * Handles new user registration. Creates a user account, hashes the password,
 * and assigns the user to the first available active tenant with the 'user' role.
 * 
 * Security:
 * - Validates CSRF tokens to prevent cross-site request forgery.
 * - Uses password_hash() with the default bcrypt algorithm.
 * - Wraps the user creation and membership assignment in a database transaction 
 *   to prevent orphaned records if one query fails.
 * - Returns generic error messages to prevent user enumeration (e.g., does not 
 *   specify if the email already exists).
 * 
 * Architectural Role:
 * Acts as a standalone controller and view. It bypasses the standard header/footer 
 * includes to maintain a focused, distraction-free authentication interface.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/Session.php';
require_once __DIR__ . '/../config/Database.php';

Session::start();

// Redirect authenticated users away from the registration page.
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Generate CSRF token for the form if one does not exist.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$db = Database::getInstance()->getConnection();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token using timing-safe comparison.
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die('Invalid request. CSRF token mismatch.');
    }

    // 2. Sanitize and collect input.
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $displayName = trim($_POST['display_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    // 3. Server-side validation.
    if (empty($username) || empty($email) || empty($displayName) || empty($password)) {
        $error = 'All fields are required.';
    } elseif ($password !== $passwordConfirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        try {
            // 4. Begin transaction to ensure atomic user creation.
            $db->beginTransaction();

            // Check if email is already registered.
            $stmt = $db->prepare("SELECT user_id FROM users WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()) {
                throw new Exception('Email already registered.');
            }

            // Hash password and insert user.
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("
                INSERT INTO users (username, email, password_hash, display_name, account_status) 
                VALUES (:username, :email, :hash, :display_name, 'active')
            ");
            $stmt->execute([
                'username' => $username, 
                'email' => $email, 
                'hash' => $hash, 
                'display_name' => $displayName
            ]);
            $newUserId = (int)$db->lastInsertId();

            // 5. Assign to the first active tenant with the standard 'user' role.
            // In a production environment, this logic would be handled by an onboarding wizard.
            $tenantStmt = $db->prepare("SELECT tenant_id FROM tenants WHERE tenant_status = 'active' LIMIT 1");
            $tenantStmt->execute();
            $tenantId = (int)$tenantStmt->fetchColumn();

            $roleStmt = $db->prepare("SELECT role_id FROM roles WHERE role_key = 'user' LIMIT 1");
            $roleStmt->execute();
            $roleId = (int)$roleStmt->fetchColumn();

            if ($tenantId && $roleId) {
                $memStmt = $db->prepare("
                    INSERT INTO tenant_memberships (tenant_id, user_id, role_id, membership_status) 
                    VALUES (:tid, :uid, :rid, 'active')
                ");
                $memStmt->execute(['tid' => $tenantId, 'uid' => $newUserId, 'rid' => $roleId]);
            }

            $db->commit();
            $success = 'Registration successful. Please log in.';
        } catch (Exception $e) {
            // Rollback transaction on failure to maintain database integrity.
            $db->rollBack();
            $error = 'Registration failed. Please try again.';
            // Log the specific error internally for debugging without exposing it to the user.
            error_log("Registration Error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | FitTrack</title>
    <link rel="stylesheet" href="/css/dashboard.css">
    <style>
        /* Auth-specific layout overrides. Keeps the form centred and isolated. */
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .auth-container { background: var(--card); padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 20px var(--shadow); width: 100%; max-width: 400px; }
        h1 { text-align: center; margin-bottom: 1.5rem; color: var(--text); }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 500; font-size: 0.875rem; }
        input { width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box; }
        .btn-primary { width: 100%; padding: 0.875rem; background: var(--text); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 1rem; }
        .btn-primary:hover { background: var(--accent); }
        .message { padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; text-align: center; font-size: 0.875rem; }
        .message.error { background: rgba(226, 105, 114, 0.1); color: var(--alert); }
        .message.success { background: rgba(76, 175, 80, 0.1); color: var(--success); }
        .auth-link { text-align: center; margin-top: 1rem; font-size: 0.875rem; }
        .auth-link a { color: var(--accent); text-decoration: none; }
    </style>
</head>
<body>
    <div class="auth-container">
        <h1>Create Account</h1>
        
        <?php if ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="register.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="display_name">Display Name</label>
                <input type="text" id="display_name" name="display_name" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="8">
            </div>
            
            <div class="form-group">
                <label for="password_confirm">Confirm Password</label>
                <input type="password" id="password_confirm" name="password_confirm" required minlength="8">
            </div>
            
            <button type="submit" class="btn-primary">Register</button>
        </form>
        
        <div class="auth-link">
            Already have an account? <a href="login.php">Sign In</a>
        </div>
    </div>
</body>
</html>