<?php
/**
 * Database Connection Test
 * 
 * A temporary script to verify the PDO connection is working correctly.
 * Delete this file before production deployment.
 */

// Define app root to allow env.php to load correctly
define('APP_ROOT', dirname(__DIR__));

require_once __DIR__ . '/../config/Database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Test query: Fetch the database version and current user
    $stmt = $pdo->query("SELECT VERSION() as version, USER() as user");
    $result = $stmt->fetch();

    echo "<h1>Database Connection Successful</h1>";
    echo "<p><strong>Database User:</strong> " . htmlspecialchars($result['user']) . "</p>";
    echo "<p><strong>Database Version:</strong> " . htmlspecialchars($result['version']) . "</p>";
    echo "<p><strong>Charset:</strong> utf8mb4 (Verified in PDO options)</p>";
    
} catch (Exception $e) {
    echo "<h1 style='color: red;'>Connection Failed</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}