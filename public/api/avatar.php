<?php
/**
 * File: avatar.php
 * Location: /public/api/avatar.php
 *
 * Purpose:
 * Handles avatar image upload with client-provided crop coordinates.
 * The client sends a base64-encoded cropped image along with positioning data.
 * Server validates, decodes, resizes to 256x256, and stores as a file.
 *
 * Security:
 * - Validates MIME type against whitelist (JPEG, PNG, WebP).
 * - Enforces 5MB upload limit.
 * - Stores files outside webroot when possible; here we use /uploads/avatars/
 *   with sanitised filenames.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/Session.php';
require_once __DIR__ . '/../../config/Database.php';

header('Content-Type: application/json');
Session::start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

$db = Database::getInstance()->getConnection();
$userId = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['success' => false, 'error' => 'Method not allowed']));
}

$input = json_decode(file_get_contents('php://input'), true);
$imageData = $input['image'] ?? '';

// Validate base64 data URI
if (!preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,/', $imageData, $matches)) {
    exit(json_encode(['success' => false, 'error' => 'Invalid image format']));
}

$mime = $matches[1] === 'jpg' ? 'jpeg' : $matches[1];
$base64 = substr($imageData, strpos($imageData, ',') + 1);
$imageBinary = base64_decode($base64, true);

if ($imageBinary === false || strlen($imageBinary) > 5 * 1024 * 1024) {
    exit(json_encode(['success' => false, 'error' => 'Invalid or oversized image (max 5MB)']));
}

// Create GD image from binary
$src = imagecreatefromstring($imageBinary);
if (!$src) {
    exit(json_encode(['success' => false, 'error' => 'Failed to decode image']));
}

$srcW = imagesx($src);
$srcH = imagesy($src);

// Apply crop coordinates from client (x, y, width, height as percentages 0-1)
$cropX = (float)($input['cropX'] ?? 0);
$cropY = (float)($input['cropY'] ?? 0);
$cropW = (float)($input['cropW'] ?? 1);
$cropH = (float)($input['cropH'] ?? 1);

// Clamp values
$cropX = max(0, min(1, $cropX));
$cropY = max(0, min(1, $cropY));
$cropW = max(0.1, min(1 - $cropX, $cropW));
$cropH = max(0.1, min(1 - $cropY, $cropH));

$sx = (int)($cropX * $srcW);
$sy = (int)($cropY * $srcH);
$sw = (int)($cropW * $srcW);
$sh = (int)($cropH * $srcH);

// Crop to square aspect ratio (use smaller dimension)
$size = min($sw, $sh);
$sx += (int)(($sw - $size) / 2);
$sy += (int)(($sh - $size) / 2);

// Resize to 256x256 for consistent storage
$targetSize = 256;
$dst = imagecreatetruecolor($targetSize, $targetSize);

// Preserve transparency for PNG/WebP
if ($mime === 'png' || $mime === 'webp') {
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
    imagefilledrectangle($dst, 0, 0, $targetSize, $targetSize, $transparent);
}

imagecopyresampled($dst, $src, 0, 0, $sx, $sy, $targetSize, $targetSize, $size, $size);

// Save to disk
$uploadDir = __DIR__ . '/../uploads/avatars/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$ext = $mime === 'jpeg' ? 'jpg' : $mime;
$filename = 'user_' . $userId . '_' . time() . '.' . $ext;
$filepath = $uploadDir . $filename;

$saveOk = false;
if ($mime === 'jpeg') {
    $saveOk = imagejpeg($dst, $filepath, 90);
} elseif ($mime === 'png') {
    $saveOk = imagepng($dst, $filepath, 6);
} elseif ($mime === 'webp') {
    $saveOk = imagewebp($dst, $filepath, 85);
}

imagedestroy($src);
imagedestroy($dst);

if (!$saveOk) {
    exit(json_encode(['success' => false, 'error' => 'Failed to save image']));
}

// Delete old avatar file if it exists
$stmt = $db->prepare("SELECT avatar_path FROM users WHERE user_id = :id");
$stmt->execute(['id' => $userId]);
$old = $stmt->fetchColumn();

// ltrim ensures we don't get double slashes if the old path had a leading slash
if ($old && file_exists(__DIR__ . '/../' . ltrim($old, '/'))) {
    @unlink(__DIR__ . '/../' . ltrim($old, '/'));
}

// FIXED: Save path WITHOUT leading slash so the <base> tag in header.php resolves it correctly
$relativePath = 'uploads/avatars/' . $filename;

$stmt = $db->prepare("UPDATE users SET avatar_path = :path, updated_at = NOW() WHERE user_id = :id");
$stmt->execute(['path' => $relativePath, 'id' => $userId]);

$_SESSION['avatar_path'] = $relativePath;

echo json_encode([
    'success' => true,
    'path' => $relativePath
]);