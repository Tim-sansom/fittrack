<?php
/**
 * File: Session.php
 * Location: /config/Session.php
 * 
 * Purpose:
 * Hardens PHP's default session configuration to mitigate common web vulnerabilities,
 * specifically session hijacking, XSS, and CSRF. It also enforces an inactivity timeout
 * to further reduce the risk of unauthorised access on shared or unattended devices.
 * This class must be instantiated before any HTTP output is sent to the browser.
 * 
 * Architectural Role:
 * Acts as the security gateway for state management. By centralising session 
 * configuration here, we ensure consistent security headers and timeout logic 
 * across all application entry points (login, dashboard, API endpoints).
 */
declare(strict_types=1);

class Session {
    /**
     * @var int Duration in minutes before an inactive session is automatically terminated.
     */
    private static int $timeoutMinutes = 30;

    /**
     * Initialise a secure session with hardened cookie parameters and enforce timeout.
     * 
     * Why this matters:
     * - cookie_httponly: Prevents client-side scripts (XSS) from stealing the session ID.
     * - cookie_samesite: Strict mode prevents the browser from sending this cookie 
     *   with cross-site requests, effectively mitigating CSRF attacks.
     * - use_strict_mode: Forces PHP to reject uninitialised session IDs, preventing 
     *   session fixation attacks where an attacker forces a known ID onto a victim.
     * - timeout: Automatically logs out idle users, reducing the window of opportunity 
     *   for unauthorised access on compromised or shared devices.
     */
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', '1');
            
            // CRITICAL: Set to '1' when deploying to the university server (HTTPS).
            // Currently '0' to allow local development over HTTP.
            ini_set('session.cookie_secure', '0'); 
            
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.use_strict_mode', '1');
            
            session_start();
        }
        
        // Enforce session timeout for authenticated users
        if (isset($_SESSION['user_id'])) {
            self::checkTimeout();
        }
    }

    /**
     * Check if the session has exceeded the allowed inactivity period.
     * 
     * Usage: Called automatically within start() for authenticated users.
     * Action: Destroys the session and redirects to login if expired.
     */
    private static function checkTimeout(): void {
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
        } elseif (time() - $_SESSION['last_activity'] > (self::$timeoutMinutes * 60)) {
            self::destroy();
            // Redirect to login with a timeout flag to display a user-friendly message
            header('Location: /login.php?timeout=1');
            exit;
        }
        // Update last activity timestamp for the current request
        $_SESSION['last_activity'] = time();
    }

    /**
     * Regenerate the session ID securely.
     * 
     * Usage: Must be called immediately after a successful login or privilege change.
     * Why: Invalidates the old session ID to prevent session fixation attacks.
     */
    public static function regenerate(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    /**
     * Destroy the session completely and clear all associated cookies.
     * 
     * Usage: Called during logout or session timeout to ensure no residual 
     * state remains on the client or server.
     */
    public static function destroy(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }
            session_destroy();
        }
    }
}