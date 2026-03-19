<?php

$configuredTimezone = trim((string)getenv('APP_TIMEZONE'));
if ($configuredTimezone === '') {
    $configuredTimezone = 'Europe/Berlin';
}
if (!@date_default_timezone_set($configuredTimezone)) {
    date_default_timezone_set('UTC');
}

$file = __DIR__ . "/datenbank/data/visitors.json";

$userAgent = strtolower((string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
$automatedSignatures = [
    'konik-uptime/',
    'curl/',
    'wget/',
    'powershell/',
    'python-requests/',
    'postmanruntime/',
    'insomnia/',
    'googlebot',
    'bingbot',
    'duckduckbot',
    'yandexbot',
    'baiduspider',
    'slurp',
    'applebot',
    'facebookexternalhit',
    'twitterbot',
    'linkedinbot',
    'semrushbot',
    'ahrefsbot',
    'mj12bot',
    'petalbot',
    'screaming frog'
];

// Skip non-human/system requests so uptime checks do not inflate visitor stats.
$isAutomatedRequest = ($userAgent === '');
if (!$isAutomatedRequest) {
    foreach ($automatedSignatures as $signature) {
        if (strpos($userAgent, $signature) !== false) {
            $isAutomatedRequest = true;
            break;
        }
    }
}

if (!$isAutomatedRequest && preg_match('/\b(bot|crawler|spider|headless|lighthouse|monitor)\b/', $userAgent) === 1) {
    $isAutomatedRequest = true;
}

if ($isAutomatedRequest) {
    return;
}

$data = [];
if (file_exists($file)) {
    $contents = file_get_contents($file);
    $parsed = json_decode($contents, true);
    if (is_array($parsed)) {
        $data = $parsed;
    }
}

if (!empty($data)) {
    ksort($data);
    $keys = array_keys($data);
    $latestKey = (string)end($keys);

    $localCurrentHour = date('Y-m-d H:00');
    $localPreviousHour = date('Y-m-d H:00', strtotime('-1 hour'));
    $utcCurrentHour = gmdate('Y-m-d H:00');
    $utcPreviousHour = gmdate('Y-m-d H:00', strtotime('-1 hour'));

    $looksLikeLegacyUtc = in_array($latestKey, [$utcCurrentHour, $utcPreviousHour], true)
        && !in_array($latestKey, [$localCurrentHour, $localPreviousHour], true);

    if ($looksLikeLegacyUtc) {
        $localTz = new DateTimeZone(date_default_timezone_get());
        $utcTz = new DateTimeZone('UTC');
        $converted = [];

        foreach ($data as $hourKey => $count) {
            $utcDate = DateTimeImmutable::createFromFormat('Y-m-d H:i', (string)$hourKey, $utcTz);
            if ($utcDate === false) {
                continue;
            }

            $localKey = $utcDate->setTimezone($localTz)->format('Y-m-d H:00');
            if (!isset($converted[$localKey])) {
                $converted[$localKey] = 0;
            }
            $converted[$localKey] += (int)$count;
        }

        if (!empty($converted)) {
            ksort($converted);
            $data = $converted;
        }
    }
}

$currentHour = date("Y-m-d H:00");
if (!isset($data[$currentHour])) {
    $data[$currentHour] = 0;
}

$data[$currentHour]++;

$last7 = [];
for ($i = 6; $i >= 0; $i--) {
    $hour = date("Y-m-d H:00", strtotime("-$i hour"));
    $last7[$hour] = $data[$hour] ?? 0;
}

$dir = dirname($file);
if (!is_dir($dir)) {
    @mkdir($dir, 0755, true);
}

file_put_contents($file, json_encode($last7, JSON_PRETTY_PRINT), LOCK_EX);
