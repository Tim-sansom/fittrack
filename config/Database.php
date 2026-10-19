<?php
/**
 * File: Database.php
 * Location: /config/Database.php
 * 
 * Purpose:
 * Manages the single, persistent connection to the MySQL database using the 
 * Singleton design pattern. This prevents connection exhaustion and ensures 
 * consistent configuration across the application lifecycle.
 * 
 * Security & Architecture:
 * - Uses true prepared statements (not emulated) to prevent SQL injection at the 
 *   protocol level and ensure strict type handling.
 * - Enforces utf8mb4 charset to prevent encoding-based attacks and support full 
 *   Unicode (including emojis in user bios).
 * - Errors are logged internally rather than exposed to the user to prevent 
 *   information leakage about the database schema.
 */
declare(strict_types=1);

//require_once __DIR__ . '/env.php';

class Database {
    private static ?Database $instance = null;
    private PDO $connection;

    /**
     * Private constructor prevents direct instantiation.
     * Enforces the Singleton pattern.
     */
    private function __construct() {
    // Live Arden Server Credentials
    $host = 'localhost';
    $port = '3306';
    $dbname = 'k66x4ro_fittrack';
    $user = 'k66x4ro_uni_tim';
    $pass = 'T3chn0-2026!';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        // Strict PDO options for security and reliability
        $options = [
            // Throw exceptions on errors for easier debugging and control flow
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Return associative arrays by default for cleaner data handling
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // CRITICAL: Disable emulated prepares. This forces MySQL to handle 
            // parameter binding natively, closing specific SQL injection vectors.
            PDO::ATTR_EMULATE_PREPARES => false,
            // Disable persistent connections to avoid pool exhaustion in PHP-FPM
            PDO::ATTR_PERSISTENT => false,
        ];

        try {
            $this->connection = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Log the raw error for the developer, but show a generic message to the user
            error_log("Database Connection Failed: " . $e->getMessage());
            die("A critical database error occurred. Please contact the administrator.");
        }
    }

    /**
     * Prevent cloning of the instance.
     */
    private function __clone() {}

    /**
     * Prevent unserializing of the instance.
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * Returns the single instance of the Database class.
     *
     * @return Database The active database connection wrapper.
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Returns the active PDO connection for executing queries.
     *
     * @return PDO The underlying PHP Data Objects connection.
     */
    public function getConnection(): PDO {
        return $this->connection;
    }
}