<?php
require_once __DIR__ . '/init.php';

$file = __DIR__ . '/datenbank/data/uptime.json';

$defaultScheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$defaultHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$defaultUrl = $defaultScheme . '://' . $defaultHost . '/home/index.php';

$checkUrl = null;
if (isset($_GET['url'])) {
    $checkUrl = filter_var(trim($_GET['url']), FILTER_SANITIZE_URL);
}
if (empty($checkUrl)) {
    $checkUrl = $defaultUrl;
}
if (!filter_var($checkUrl, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['error' => 'Invalid URL for uptime check']);
    exit;
}

$checkHost = strtolower((string)parse_url($checkUrl, PHP_URL_HOST));
$defaultHostOnly = strtolower((string)parse_url($defaultUrl, PHP_URL_HOST));
$checkScheme = strtolower((string)parse_url($checkUrl, PHP_URL_SCHEME));
if ($checkHost === '' || $defaultHostOnly === '' || !in_array($checkScheme, ['http', 'https'], true) || $checkHost !== $defaultHostOnly) {
    http_response_code(403);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['error' => 'Host not allowed for uptime check']);
    exit;
}

function checkServer(string $url): int
{
    if (function_exists('curl_version')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_NOBODY => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $content = curl_exec($ch);
        $err = curl_error($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($content === false || $err) {
            return 0;
        }

        return ($http >= 200 && $http < 400) ? 1 : 0;
    }

    $ctx = stream_context_create(['http' => ['method' => 'HEAD', 'timeout' => 15]]);
    $headers = @get_headers($url, 1, $ctx);
    if (!$headers || !isset($headers[0])) {
        return 0;
    }

    preg_match('/\s(\d{3})\s/', $headers[0], $m);
    $code = isset($m[1]) ? (int)$m[1] : 0;
    return ($code >= 200 && $code < 400) ? 1 : 0;
}

$data = [];
if (file_exists($file)) {
    $json = @file_get_contents($file);
    $parsed = json_decode($json, true);
    if (is_array($parsed)) {
        $data = $parsed;
    }
}

$now = new DateTimeImmutable('now', new DateTimeZone(date_default_timezone_get()));
$currentHour = (int)$now->format('U') - ((int)$now->format('i') * 60 + (int)$now->format('s'));
$status = checkServer($checkUrl);

$lastHour = null;
if (!empty($data)) {
    end($data);
    $lastKey = key($data);
    $lastHour = strtotime($lastKey);
}

if ($lastHour !== null && $lastHour < $currentHour) {
    $nextHour = $lastHour + 3600;
    while ($nextHour < $currentHour) {
        $data[date('Y-m-d H:00', $nextHour)] = 0;
        $nextHour += 3600;
    }
}

$key = date('Y-m-d H:00', $currentHour);
$data[$key] = $status;
$data = array_slice($data, -24, 24, true);

$dir = dirname($file);
if (!is_dir($dir)) {
    @mkdir($dir, 0755, true);
}

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);

header('Content-Type: application/json; charset=UTF-8');
echo json_encode([
    'ok' => true,
    'url' => $checkUrl,
    'timestamp' => $key,
    'status' => $status,
], JSON_UNESCAPED_SLASHES);
