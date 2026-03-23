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

function normalizehistoryImageSrc($src) {
	$src = trim((string)$src);
	if ($src === '') {
		return '';
	}

	if (!preg_match('#^/database/images/history/[A-Za-z0-9._-]+$#', $src)) {
		return '';
	}

	return $src;
}

function historyImageSrcToFile($src) {
	$normalizedSrc = normalizehistoryImageSrc($src);
	if ($normalizedSrc === '') {
		return '';
	}

	$filename = basename($normalizedSrc);
	if ($filename === '' || $filename === '.' || $filename === '..') {
		return '';
	}

	return __DIR__ . '/../../database/images/history/' . $filename;
}

$file = __DIR__ . '/../../database/json/history.json';
$existingEvents = [];
if (file_exists($file)) {
	$existingDecoded = json_decode((string)file_get_contents($file), true);
	if (is_array($existingDecoded)) {
		$existingEvents = $existingDecoded;
	}
}

$safeEvents = [];
$sourceLang = current_lang();
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

	$titleI18n = build_i18n_text_map($title, $sourceLang, 120);
	$descriptionI18n = build_i18n_text_map($description, $sourceLang, 5000);

	$safeEvents[] = [
		'id' => $id,
		'title' => (string)($titleI18n['de'] ?? $title),
		'date' => $date,
		'description' => (string)($descriptionI18n['de'] ?? $description),
		'src' => $src,
		'alt' => (string)($titleI18n['de'] ?? $title),
		'title_i18n' => $titleI18n,
		'description_i18n' => $descriptionI18n,
		'alt_i18n' => $titleI18n
	];
}

$existingSrcSet = [];
foreach ($existingEvents as $event) {
	if (!is_array($event)) {
		continue;
	}
	$src = normalizehistoryImageSrc($event['src'] ?? '');
	if ($src !== '') {
		$existingSrcSet[$src] = true;
	}
}

$newSrcSet = [];
foreach ($safeEvents as $event) {
	$src = normalizehistoryImageSrc($event['src'] ?? '');
	if ($src !== '') {
		$newSrcSet[$src] = true;
	}
}

$removedSrc = array_diff_key($existingSrcSet, $newSrcSet);
foreach (array_keys($removedSrc) as $src) {
	$path = historyImageSrcToFile($src);
	if ($path !== '' && is_file($path)) {
		@unlink($path);
	}
}

file_put_contents($file, json_encode($safeEvents, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

echo json_encode(['status' => 'saved']);
?>
