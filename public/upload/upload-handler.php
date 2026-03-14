<?php
session_start();

if (function_exists('ob_end_clean')) {
    while (ob_get_level()) {
        ob_end_clean();
    }
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Error: ' . $errstr]));
});

set_exception_handler(function($exception) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Error: ' . $exception->getMessage()]));
});

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

$uploadDir = __DIR__ . '/../datenbank/bilder/uploads/';
$tempDir = __DIR__ . '/../datenbank/bilder/uploads/pending/';

if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

if (empty($_POST['eventDate']) || empty($_POST['eventTitle']) || empty($_FILES['eventImage'])) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Fehlende erforderliche Felder']));
}

$eventDate = trim($_POST['eventDate']);
$eventEmail = trim($_POST['uploaderEmail']);
$eventTitle = trim($_POST['eventTitle']);
$eventDes = trim($_POST['eventDes'] ?? '');
$file = $_FILES['eventImage'];

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $eventDate)) {
    die(json_encode(['success' => false, 'message' => 'Ungültiges Datumformat']));
}

$dateObj = DateTime::createFromFormat('Y-m-d', $eventDate);
if (!$dateObj) {
    die(json_encode(['success' => false, 'message' => 'Ungültiges Datum']));
}
$formattedDate = $dateObj->format('d.m.Y');

$allowedExtensions = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExtensions)) {
    die(json_encode(['success' => false, 'message' => 'Nur Bilder (JPEG, PNG, GIF, WebP) erlaubt']));
}

if ($file['size'] > 10 * 1024 * 1024) {
    die(json_encode(['success' => false, 'message' => 'Dateigröße zu groß (max. 10MB)']));
}

$filename = md5(uniqid()) . '.' . $ext;
$tempPath = $tempDir . $filename;
$finalPath = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $tempPath)) {
    die(json_encode(['success' => false, 'message' => 'Fehler beim Hochladen der Datei']));
}

$metadata = [
    'filename' => $filename,
    'originalName' => basename($file['name']),
    'email' => $eventEmail,
    'date' => $formattedDate,
    'title' => $eventTitle,
    'description' => $eventDes,
    'timestamp' => time(),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
];

file_put_contents($tempDir . $filename . '.json', json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode([
    'success' => true,
    'message' => 'Bild erfolgreich hochgeladen und wird überprüft. Es wird bald der Galerie hinzugefügt!'
]);
exit;