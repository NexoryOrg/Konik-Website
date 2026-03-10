<?php
session_start();

if (function_exists('ob_end_clean')) {
    while (ob_get_level()) {
        ob_end_clean();
    }
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

$uploadDir = __DIR__ . '/datenbank/bilder/uploads/';
$tempDir = __DIR__ . '/datenbank/bilder/uploads/pending/';

if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

if (empty($_POST['eventDate']) || empty($_POST['eventTitle']) || empty($_FILES['eventImage'])) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Fehlende erforderliche Felder']));
}

$eventDate = trim($_POST['eventDate']);
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

$allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$fileMime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($fileMime, $allowedMimes)) {
    die(json_encode(['success' => false, 'message' => 'Nur Bilder (JPEG, PNG, GIF, WebP) erlaubt']));
}

if ($file['size'] > 10 * 1024 * 1024) {
    die(json_encode(['success' => false, 'message' => 'Dateigröße zu groß (max. 10MB)']));
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = md5(uniqid()) . '.' . $ext;
$tempPath = $tempDir . $filename;
$finalPath = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $tempPath)) {
    die(json_encode(['success' => false, 'message' => 'Fehler beim Hochladen der Datei']));
}

$metadata = [
    'filename' => $filename,
    'originalName' => basename($file['name']),
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
