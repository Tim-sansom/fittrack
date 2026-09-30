<?php
/**
 * File: logout.php
 * Location: /public/logout.php
 * 
 * Purpose:
 * Destroys the user session and redirects to the login page.
 * Delegates the actual cookie clearing and session destruction 
 * to the Session class to ensure consistency.
 */
declare(strict_types=1);

require_once __DIR__ . '/config/Session.php';

Session::start();
Session::destroy();

header('Location: login.php');
exit;