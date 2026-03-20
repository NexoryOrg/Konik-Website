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

$rawData = file_get_contents('php://input');
$payload = json_decode((string)$rawData, true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid payload']);
    exit;
}

$action = trim((string)($payload['action'] ?? ''));
$filename = basename((string)($payload['filename'] ?? ''));

if (!preg_match('/^[A-Za-z0-9._-]+$/', $filename)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid filename']);
    exit;
}

$galleryFile = __DIR__ . '/../../datenbank/json/gallery.json';
$statsFile = __DIR__ . '/../../datenbank/data/stats.json';
$uploadImageFile = __DIR__ . '/../../datenbank/bilder/uploads/' . $filename;

$galleryData = [];
if (file_exists($galleryFile)) {
    $decoded = json_decode((string)file_get_contents($galleryFile), true);
    if (is_array($decoded)) {
        $galleryData = $decoded;
    }
}

$stats = [
    'approved' => 0,
    'rejected' => 0,
    'approved_items' => [],
    'rejected_items' => []
];
if (file_exists($statsFile)) {
    $decodedStats = json_decode((string)file_get_contents($statsFile), true);
    if (is_array($decodedStats)) {
        $stats = array_merge($stats, array_intersect_key($decodedStats, $stats));
        if (!is_array($stats['approved_items'])) {
            $stats['approved_items'] = [];
        }
        if (!is_array($stats['rejected_items'])) {
            $stats['rejected_items'] = [];
        }
    }
}

$foundYear = null;
$foundIndex = null;
foreach ($galleryData as $year => $entries) {
    if (!is_array($entries)) {
        continue;
    }

    foreach ($entries as $index => $entry) {
        $entryFilename = basename((string)($entry['src'] ?? ''));
        if ($entryFilename === $filename) {
            $foundYear = (string)$year;
            $foundIndex = (int)$index;
            break 2;
        }
    }
}

if ($foundYear === null || $foundIndex === null) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Image not found']);
    exit;
}

if ($action === 'delete') {
    unset($galleryData[$foundYear][$foundIndex]);
    $galleryData[$foundYear] = array_values($galleryData[$foundYear]);
    if (count($galleryData[$foundYear]) === 0) {
        unset($galleryData[$foundYear]);
    }

    $stats['approved_items'] = array_values(array_filter($stats['approved_items'], function ($item) use ($filename) {
        if (!is_array($item)) {
            return false;
        }
        $itemFilename = basename((string)($item['filename'] ?? ''));
        if ($itemFilename === '') {
            $itemFilename = basename((string)($item['img'] ?? ''));
        }
        return $itemFilename !== $filename;
    }));
    $stats['approved'] = count($stats['approved_items']);

    file_put_contents($galleryFile, json_encode($galleryData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    file_put_contents($statsFile, json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

    if (is_file($uploadImageFile)) {
        @unlink($uploadImageFile);
    }

    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'edit') {
    $title = trim((string)($payload['title'] ?? ''));
    $description = trim((string)($payload['description'] ?? ''));
    $date = trim((string)($payload['date'] ?? ''));

    if ($title === '' || $description === '' || !preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $date)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid edit data']);
        exit;
    }

    $targetYear = substr($date, -4);
    if (!preg_match('/^\d{4}$/', $targetYear)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid date year']);
        exit;
    }

    $entry = $galleryData[$foundYear][$foundIndex] ?? [];
    if (!is_array($entry)) {
        $entry = [];
    }

    $entry['alt'] = function_exists('mb_substr') ? mb_substr($title, 0, 150) : substr($title, 0, 150);
    $entry['des'] = function_exists('mb_substr') ? mb_substr($description, 0, 3000) : substr($description, 0, 3000);

    if ($targetYear !== $foundYear) {
        unset($galleryData[$foundYear][$foundIndex]);
        $galleryData[$foundYear] = array_values($galleryData[$foundYear]);
        if (count($galleryData[$foundYear]) === 0) {
            unset($galleryData[$foundYear]);
        }

        if (!isset($galleryData[$targetYear]) || !is_array($galleryData[$targetYear])) {
            $galleryData[$targetYear] = [];
        }
        $galleryData[$targetYear][] = $entry;
    } else {
        $galleryData[$foundYear][$foundIndex] = $entry;
    }

    foreach ($stats['approved_items'] as $idx => $item) {
        if (!is_array($item)) {
            continue;
        }

        $itemFilename = basename((string)($item['filename'] ?? ''));
        if ($itemFilename === '') {
            $itemFilename = basename((string)($item['img'] ?? ''));
        }

        if ($itemFilename === $filename) {
            $stats['approved_items'][$idx]['title'] = $entry['alt'];
            $stats['approved_items'][$idx]['description'] = $entry['des'];
            $stats['approved_items'][$idx]['date'] = $date;
            break;
        }
    }

    file_put_contents($galleryFile, json_encode($galleryData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    file_put_contents($statsFile, json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

    echo json_encode(['success' => true, 'year' => $targetYear]);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Unknown action']);
exit;
