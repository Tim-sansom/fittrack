<?php
/**
 * File: index.php
 * Location: /public/index.php
 * 
 * Purpose:
 * Application entry point. Routes the user to the appropriate interface 
 * based on their authentication state.
 */
declare(strict_types=1);

require_once __DIR__ . '/config/Session.php';
Session::start();

// Route authenticated users to the dashboard, others to login.
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;