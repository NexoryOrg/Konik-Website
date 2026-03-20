<?php
require_once __DIR__ . '/../../init.php';

header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!csrf_validate($csrfHeader)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid request token']);
    exit;
}

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '') {
    $originHost = strtolower((string)parse_url($origin, PHP_URL_HOST));
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
    $requestHost = strtolower((string)parse_url(($isHttps ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST));
    if ($originHost === '' || $requestHost === '' || $originHost !== $requestHost) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Origin not allowed']);
        exit;
    }
}

if (empty($_FILES['image']) || !isset($_FILES['image']['error']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No image uploaded']);
    exit;
}

$file = $_FILES['image'];
if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid upload']);
    exit;
}

if (($file['size'] ?? 0) > 10 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File size too large (max 10MB)']);
    exit;
}

$allowedMimeToExt = [
    'image/jpeg' => 'jpg',
    'image/jpg' => 'jpg',
    'image/pjpeg' => 'jpg',
    'image/png' => 'png',
    'image/x-png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp',
];

$mime = '';
if (class_exists('finfo')) {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string)$finfo->file($file['tmp_name']);
} elseif (function_exists('mime_content_type')) {
    $mime = (string)mime_content_type($file['tmp_name']);
}

$imageInfo = @getimagesize($file['tmp_name']);
if ($imageInfo === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Only valid image files are allowed']);
    exit;
}

$detectedType = (int)($imageInfo[2] ?? 0);
$typeToExt = [
    IMAGETYPE_JPEG => 'jpg',
    IMAGETYPE_PNG => 'png',
    IMAGETYPE_GIF => 'gif',
    IMAGETYPE_WEBP => 'webp',
];

$extension = $allowedMimeToExt[$mime] ?? ($typeToExt[$detectedType] ?? '');
if ($extension === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Only valid image files are allowed']);
    exit;
}

$uploadDir = __DIR__ . '/../../database/images/history/';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not prepare upload directory']);
    exit;
}

try {
    $filename = bin2hex(random_bytes(16)) . '.' . $extension;
} catch (Exception $e) {
    $filename = sha1(uniqid((string)mt_rand(), true)) . '.' . $extension;
}

$targetFile = $uploadDir . $filename;
if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not save image']);
    exit;
}

$publicSrc = '/database/images/history/' . $filename;

echo json_encode([
    'success' => true,
    'src' => $publicSrc
]);
