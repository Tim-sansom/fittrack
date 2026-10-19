<?php
/**
 * File: wellbeing.php
 * Location: /public/api/wellbeing.php
 *
 * Purpose:
 * Minimal REST endpoint for the Wellbeing SPA renderer.
 * Returns static content metadata; the actual HTML is rendered client-side
 * by router.js to avoid unnecessary network payload.
 */
declare(strict_types=1);
require_once __DIR__ . '/../../config/Session.php';
Session::start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorised']);
    exit;
}

echo json_encode([
    'success' => true,
    'data' => [
        'title' => 'Physical Wellbeing',
        'description' => 'Evidence-based guidance on physical activity for adults in the United Kingdom.'
    ]
], JSON_UNESCAPED_UNICODE);