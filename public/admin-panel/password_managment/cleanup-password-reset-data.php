<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script can only be executed via CLI.' . PHP_EOL);
}

$resetDataFile = __DIR__ . '/../../database/data/password_resets.json';
$rateLimitFile = __DIR__ . '/../../database/data/password_reset_rate_limit.json';

function loadJsonArray(string $path): array
{
    if (!file_exists($path)) {
        return [];
    }

    $raw = file_get_contents($path);
    if ($raw === false) {
        return [];
    }

    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        return [];
    }

    return $data;
}

function saveJsonArray(string $path, array $payload): bool
{
    $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($encoded === false) {
        return false;
    }

    return file_put_contents($path, $encoded, LOCK_EX) !== false;
}

$now = time();
$removedTokens = 0;
$removedRateBuckets = 0;

$resetData = loadJsonArray($resetDataFile);
if (!isset($resetData['tokens']) || !is_array($resetData['tokens'])) {
    $resetData['tokens'] = [];
}

$cleanTokens = [];
foreach ($resetData['tokens'] as $row) {
    if (!is_array($row)) {
        $removedTokens++;
        continue;
    }

    $expiresAt = (int)($row['expires_at'] ?? 0);
    $used = (bool)($row['used'] ?? false);

    if ($expiresAt <= 0) {
        $removedTokens++;
        continue;
    }

    if ($used && ($now - $expiresAt) > (12 * 60 * 60)) {
        $removedTokens++;
        continue;
    }

    if (!$used && $expiresAt < ($now - (2 * 60 * 60))) {
        $removedTokens++;
        continue;
    }

    $cleanTokens[] = $row;
}

$resetData['tokens'] = $cleanTokens;
saveJsonArray($resetDataFile, $resetData);

$rateData = loadJsonArray($rateLimitFile);
if (!isset($rateData['entries']) || !is_array($rateData['entries'])) {
    $rateData['entries'] = [];
}

$windowSeconds = 15 * 60;
$cleanEntries = [];
foreach ($rateData['entries'] as $key => $entries) {
    if (!is_array($entries)) {
        $removedRateBuckets++;
        continue;
    }

    $valid = [];
    foreach ($entries as $ts) {
        $ts = (int)$ts;
        if ($ts > 0 && ($now - $ts) <= $windowSeconds) {
            $valid[] = $ts;
        }
    }

    if (count($valid) > 0) {
        $cleanEntries[$key] = $valid;
    } else {
        $removedRateBuckets++;
    }
}

$rateData['entries'] = $cleanEntries;
saveJsonArray($rateLimitFile, $rateData);

fwrite(STDOUT, sprintf(
    "Cleanup complete. Removed %d token rows and %d stale rate-limit buckets.%s",
    $removedTokens,
    $removedRateBuckets,
    PHP_EOL
));
