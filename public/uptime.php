<?php
$configuredTimezone = trim((string)getenv('APP_TIMEZONE'));
if ($configuredTimezone === '') {
    $configuredTimezone = 'Europe/Berlin';
}
if (!@date_default_timezone_set($configuredTimezone)) {
    date_default_timezone_set('UTC');
}

$isCli = PHP_SAPI === 'cli';
if (!$isCli) {
    require_once __DIR__ . '/init.php';
}

$file = __DIR__ . '/datenbank/data/uptime.json';

function respondJson(array $payload, int $statusCode, bool $isCli): void
{
    if (!$isCli) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
    }

    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    if ($isCli) {
        echo PHP_EOL;
    }
    exit;
}

function getCliOption(array $argv, string $name): ?string
{
    $prefix = '--' . $name . '=';
    foreach ($argv as $arg) {
        if (strpos($arg, $prefix) === 0) {
            $value = trim((string)substr($arg, strlen($prefix)));
            return $value === '' ? null : $value;
        }
    }

    return null;
}

function checkServer(string $url): array
{
    if (function_exists('curl_version')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => [
                'Range: bytes=0-0',
                'Accept: */*',
            ],
            CURLOPT_USERAGENT => 'Konik-Uptime/1.0',
        ]);

        $content = curl_exec($ch);
        $err = curl_error($ch);
        $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $responseMs = (int)round((float)curl_getinfo($ch, CURLINFO_TOTAL_TIME) * 1000);
        curl_close($ch);

        if ($content === false || $err) {
            return [
                'status' => 0,
                'http_code' => $http,
                'response_ms' => $responseMs,
                'error' => $err !== '' ? $err : 'request_failed',
            ];
        }

        return [
            'status' => ($http >= 200 && $http < 400) ? 1 : 0,
            'http_code' => $http,
            'response_ms' => $responseMs,
            'error' => null,
        ];
    }

    $start = microtime(true);
    $ctx = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 15,
            'ignore_errors' => true,
            'header' => "Range: bytes=0-0\r\nUser-Agent: Konik-Uptime/1.0\r\n",
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ]);

    $stream = @fopen($url, 'rb', false, $ctx);
    if ($stream !== false) {
        @fread($stream, 1);
        @fclose($stream);
    }

    $headers = $http_response_header ?? [];
    $http = 0;
    if (isset($headers[0]) && preg_match('/\s(\d{3})\s/', $headers[0], $m)) {
        $http = (int)$m[1];
    }

    $responseMs = (int)round((microtime(true) - $start) * 1000);
    return [
        'status' => ($http >= 200 && $http < 400) ? 1 : 0,
        'http_code' => $http,
        'response_ms' => $responseMs,
        'error' => $stream === false ? 'request_failed' : null,
    ];
}

$defaultScheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$defaultHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$envTarget = trim((string)getenv('UPTIME_TARGET_URL'));
$defaultUrl = $envTarget !== '' ? $envTarget : ($defaultScheme . '://' . $defaultHost . '/home/index.php');

$checkUrl = null;
if ($isCli) {
    $checkUrl = getCliOption($argv ?? [], 'url');
    if ($checkUrl === null && isset($argv[1]) && strpos((string)$argv[1], '--') !== 0) {
        $checkUrl = trim((string)$argv[1]);
    }
} elseif (isset($_GET['url'])) {
    $checkUrl = trim((string)$_GET['url']);
}

if (empty($checkUrl)) {
    $checkUrl = $defaultUrl;
}

$checkUrl = filter_var((string)$checkUrl, FILTER_SANITIZE_URL);
if (!filter_var($checkUrl, FILTER_VALIDATE_URL)) {
    respondJson(['error' => 'Invalid URL for uptime check'], 400, $isCli);
}

$requiredToken = trim((string)getenv('UPTIME_CHECK_TOKEN'));
if (!$isCli && $requiredToken !== '') {
    $providedToken = (string)($_GET['token'] ?? '');
    if ($providedToken === '' || !hash_equals($requiredToken, $providedToken)) {
        respondJson(['error' => 'Unauthorized uptime check'], 403, false);
    }
}

if (!$isCli) {
    $checkHost = strtolower((string)parse_url($checkUrl, PHP_URL_HOST));
    $defaultHostOnly = strtolower((string)parse_url($defaultUrl, PHP_URL_HOST));
    $checkScheme = strtolower((string)parse_url($checkUrl, PHP_URL_SCHEME));
    if ($checkHost === '' || $defaultHostOnly === '' || !in_array($checkScheme, ['http', 'https'], true) || $checkHost !== $defaultHostOnly) {
        respondJson(['error' => 'Host not allowed for uptime check'], 403, false);
    }
}

$data = [];
if (file_exists($file)) {
    $json = @file_get_contents($file);
    $parsed = json_decode((string)$json, true);
    if (is_array($parsed)) {
        $data = $parsed;
    }
}

$now = new DateTimeImmutable('now', new DateTimeZone(date_default_timezone_get()));
$sampleKey = $now->format('Y-m-d H:i');
$hourKey = $now->format('Y-m-d H:00');

$result = checkServer($checkUrl);
$status = (int)$result['status'];
$data[$sampleKey] = $status;

$retentionHours = 24;
$cutoffTs = $now->modify('-' . $retentionHours . ' hours')->getTimestamp();
foreach ($data as $timestamp => $value) {
    $ts = strtotime((string)$timestamp);
    if ($ts === false || $ts < $cutoffTs) {
        unset($data[$timestamp]);
    }
}
ksort($data);

$dir = dirname($file);
if (!is_dir($dir)) {
    @mkdir($dir, 0755, true);
}

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);

$hourStats = ['up' => 0, 'total' => 0];
foreach ($data as $timestamp => $value) {
    if (strpos((string)$timestamp, $hourKey) === 0) {
        $hourStats['total']++;
        $hourStats['up'] += ((int)$value === 1) ? 1 : 0;
    }
}

$hourAvailability = $hourStats['total'] > 0
    ? round(($hourStats['up'] / $hourStats['total']) * 100, 2)
    : null;

respondJson([
    'ok' => true,
    'url' => $checkUrl,
    'timestamp' => $sampleKey,
    'status' => $status,
    'http_code' => (int)$result['http_code'],
    'response_ms' => (int)$result['response_ms'],
    'error' => $result['error'],
    'hour_availability' => $hourAvailability,
], 200, $isCli);
