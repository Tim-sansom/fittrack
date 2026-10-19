<?php
declare(strict_types=1);

/**
 * File: User.php
 * Location: /src/models/User.php
 *
 * Purpose:
 * Authentication model. Verifies credentials and returns the tenant
 * membership context required to seed the session.
 *
 * Architectural Role:
 * Sits outside the public webroot so it cannot be requested directly
 * over HTTP. Consumed by controllers in /public/ via require_once.
 *
 * Security:
 * - Prepared statements throughout; no string concatenation.
 * - password_verify() for constant-time hash comparison.
 * - Returns null on any failure to prevent user enumeration.
 */

require_once __DIR__ . '/../../config/Database.php';

class UserModel
{
    /**
     * Verify credentials and return session context, or null on failure.
     *
     * @return array{user_id:int,username:string,tenant_id:int,role_key:string}|null
     */
    public function authenticate(string $email, string $password): ?array
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare(
            "SELECT u.user_id,
                    u.username,
                    u.password_hash,
                    tm.tenant_id,
                    r.role_key
               FROM users u
         INNER JOIN tenant_memberships tm
                 ON tm.user_id = u.user_id
                AND tm.membership_status = 'active'
         INNER JOIN roles r
                 ON r.role_id = tm.role_id
              WHERE u.email = :email
                AND u.account_status = 'active'
           ORDER BY tm.tenant_id ASC
              LIMIT 1"
        );

        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || empty($user['password_hash'])) {
            // Burn equivalent time to a real hash check to avoid timing leaks
            password_verify($password, '$2y$12$usesomesillystringfor.u7hZ1uqK5ZT6X5o5F5o5o5o5o5o5o');
            return null;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }

        $update = $db->prepare(
            "UPDATE users SET last_login_at = NOW() WHERE user_id = :uid"
        );
        $update->execute(['uid' => (int) $user['user_id']]);

        return [
            'user_id'   => (int) $user['user_id'],
            'username'  => (string) $user['username'],
            'tenant_id' => (int) $user['tenant_id'],
            'role_key'  => (string) $user['role_key'],
        ];
    }
}