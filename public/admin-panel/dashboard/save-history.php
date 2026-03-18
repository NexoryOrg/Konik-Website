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

$file = __DIR__ . '/../../datenbank/json/history.json';
file_put_contents($file, json_encode($safeEvents, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

echo json_encode(['status' => 'saved']);
?>
