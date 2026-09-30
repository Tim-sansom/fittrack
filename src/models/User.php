<?php
declare(strict_types=1);

// Fixed: Changed '../config' to '../../config' to correctly navigate out of src/models/
require_once __DIR__ . '/../../config/Database.php';

/**
 * User Model
 * Handles authentication and user data retrieval using the multi-tenant schema.
 */
class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Authenticate a user by email and password.
     * Joins users, tenant_memberships, and roles to establish context.
     */
    public function authenticate(string $email, string $password): ?array {
        $sql = "SELECT u.user_id, u.username, u.email, u.password_hash, u.account_status, 
                       tm.tenant_id, r.role_key
                FROM users u
                JOIN tenant_memberships tm ON u.user_id = tm.user_id
                JOIN roles r ON tm.role_id = r.role_id
                WHERE u.email = :email 
                  AND tm.membership_status = 'active'
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password and check account status
        if ($user && password_verify($password, $user['password_hash']) && $user['account_status'] === 'active') {
            unset($user['password_hash']); // Never return the hash
            return $user;
        }

        return null;
    }
}