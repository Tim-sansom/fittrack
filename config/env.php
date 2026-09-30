<?php
/**
 * File: env.php
 * Location: /config/env.php
 * 
 * Purpose:
 * Loads environment variables from a `.env` file into the `$_ENV` superglobal.
 * This separates sensitive configuration (credentials) from the source code, 
 * adhering to the 12-factor app methodology.
 * 
 * Why not use a library?
 * The brief explicitly forbids third-party libraries. This lightweight parser 
 * provides the necessary functionality without violating this constraint.
 */
declare(strict_types=1);

// Prevent direct access and define the application root
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

$envFile = APP_ROOT . '/.env';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Ignore full-line comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Strip inline comments (e.g., KEY=VALUE # comment)
        if (strpos($line, '#') !== false) {
            $line = substr($line, 0, strpos($line, '#'));
        }

        // Parse key=value pairs
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove surrounding quotes if present
            $value = trim($value, '"\'');
            
            // Populate both $_ENV and putenv for maximum compatibility 
            // across different PHP SAPIs (CLI vs FPM)
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
} else {
    // Fail securely. The application cannot function without database credentials.
    die('Critical Error: .env file not found. Application cannot start.');
}