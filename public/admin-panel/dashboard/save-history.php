<?php
require_once __DIR__ . '/../../init.php';

header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['admin'])) {
	http_response_code(403);
	echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
	exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
	exit;
}

$csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!csrf_validate($csrfHeader)) {
	http_response_code(403);
	echo json_encode(['status' => 'error', 'message' => 'Invalid request token']);
	exit;
}

$rawData = file_get_contents('php://input');
$decoded = json_decode($rawData, true);
if (!is_array($decoded)) {
	http_response_code(400);
	echo json_encode(['status' => 'error', 'message' => 'Invalid payload']);
	exit;
}

function normalizeTimelineImageSrc($src) {
	$src = trim((string)$src);
	if ($src === '') {
		return '';
	}

	if (!preg_match('#^/datenbank/bilder/history/[A-Za-z0-9._-]+$#', $src)) {
		return '';
	}

	return $src;
}

function timelineImageSrcToFile($src) {
	$normalizedSrc = normalizeTimelineImageSrc($src);
	if ($normalizedSrc === '') {
		return '';
	}

	$filename = basename($normalizedSrc);
	if ($filename === '' || $filename === '.' || $filename === '..') {
		return '';
	}

	return __DIR__ . '/../../datenbank/bilder/history/' . $filename;
}

$file = __DIR__ . '/../../datenbank/json/history.json';
$existingEvents = [];
if (file_exists($file)) {
	$existingDecoded = json_decode((string)file_get_contents($file), true);
	if (is_array($existingDecoded)) {
		$existingEvents = $existingDecoded;
	}
}

$safeEvents = [];
foreach ($decoded as $item) {
	if (!is_array($item)) {
		continue;
	}

	$id = isset($item['id']) ? (int)$item['id'] : time();
	$title = trim((string)($item['title'] ?? ''));
	$date = trim((string)($item['date'] ?? ''));
	$description = trim((string)($item['description'] ?? ''));
	$src = trim((string)($item['src'] ?? ''));

	if ($title === '') {
		$title = 'New Event';
	}
	if ($date !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
		$date = '';
	}
	if ($src !== '' && !preg_match('#^(?:/|\.\./|data:image/)#', $src)) {
		$src = '';
	}

	$safeEvents[] = [
		'id' => $id,
		'title' => function_exists('mb_substr') ? mb_substr($title, 0, 120) : substr($title, 0, 120),
		'date' => $date,
		'description' => function_exists('mb_substr') ? mb_substr($description, 0, 5000) : substr($description, 0, 5000),
		'src' => $src,
		'alt' => function_exists('mb_substr') ? mb_substr($title, 0, 120) : substr($title, 0, 120)
	];
}

$existingSrcSet = [];
foreach ($existingEvents as $event) {
	if (!is_array($event)) {
		continue;
	}
	$src = normalizeTimelineImageSrc($event['src'] ?? '');
	if ($src !== '') {
		$existingSrcSet[$src] = true;
	}
}

$newSrcSet = [];
foreach ($safeEvents as $event) {
	$src = normalizeTimelineImageSrc($event['src'] ?? '');
	if ($src !== '') {
		$newSrcSet[$src] = true;
	}
}

$removedSrc = array_diff_key($existingSrcSet, $newSrcSet);
foreach (array_keys($removedSrc) as $src) {
	$path = timelineImageSrcToFile($src);
	if ($path !== '' && is_file($path)) {
		@unlink($path);
	}
}

file_put_contents($file, json_encode($safeEvents, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

echo json_encode(['status' => 'saved']);
?>
