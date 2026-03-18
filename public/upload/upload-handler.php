<?php
require_once __DIR__ . '/../init.php';

if (function_exists('ob_end_clean')) {
    while (ob_get_level()) {
        ob_end_clean();
    }
}

header('Content-Type: application/json; charset=utf-8');

set_error_handler(function() {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Interner Serverfehler']);
    exit;
});

set_exception_handler(function() {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Interner Serverfehler']);
    exit;
});

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
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

if (!csrf_validate($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Ungültige Anfrage']);
    exit;
}

$uploadDir = __DIR__ . '/../datenbank/bilder/uploads/';
$tempDir = __DIR__ . '/../datenbank/bilder/uploads/pending/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0755, true);
}

if (empty($_POST['eventDate']) || empty($_POST['eventTitle']) || empty($_FILES['eventImage'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Fehlende erforderliche Felder']);
    exit;
}

$eventDate = trim((string)$_POST['eventDate']);
$eventEmail = trim((string)($_POST['uploaderEmail'] ?? ''));
$eventTitle = trim((string)$_POST['eventTitle']);
$eventDes = trim((string)($_POST['eventDes'] ?? ''));
$file = $_FILES['eventImage'];

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $eventDate)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ungültiges Datumformat']);
    exit;
}

$dateObj = DateTime::createFromFormat('Y-m-d', $eventDate);
if (!$dateObj) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ungültiges Datum']);
    exit;
}

if ($eventEmail !== '' && !filter_var($eventEmail, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ungültige E-Mail-Adresse']);
    exit;
}

if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK || !isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Fehler beim Hochladen der Datei']);
    exit;
}

if (($file['size'] ?? 0) > 10 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dateigröße zu groß (max. 10MB)']);
    exit;
}

$allowedMimeToExt = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
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
if (!isset($allowedMimeToExt[$mime]) || @getimagesize($file['tmp_name']) === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nur gültige Bilddateien sind erlaubt']);
    exit;
}

try {
    $filename = bin2hex(random_bytes(16)) . '.' . $allowedMimeToExt[$mime];
} catch (Exception $e) {
    $filename = sha1(uniqid((string)mt_rand(), true)) . '.' . $allowedMimeToExt[$mime];
}

$tempPath = $tempDir . $filename;
if (!move_uploaded_file($file['tmp_name'], $tempPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Fehler beim Hochladen der Datei']);
    exit;
}

$formattedDate = $dateObj->format('d.m.Y');
$metadata = [
    'filename' => $filename,
    'originalName' => basename((string)($file['name'] ?? '')),
    'email' => $eventEmail,
    'date' => $formattedDate,
    'title' => function_exists('mb_substr') ? mb_substr($eventTitle, 0, 150) : substr($eventTitle, 0, 150),
    'description' => function_exists('mb_substr') ? mb_substr($eventDes, 0, 3000) : substr($eventDes, 0, 3000),
    'timestamp' => time(),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
];

file_put_contents($tempDir . $filename . '.json', json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

echo json_encode([
    'success' => true,
    'message' => 'Bild erfolgreich hochgeladen und wird überprüft. Es wird bald der Galerie hinzugefügt!'
]);
exit;
