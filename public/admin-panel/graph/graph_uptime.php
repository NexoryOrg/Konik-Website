<?php
$configuredTimezone = trim((string)getenv('APP_TIMEZONE'));
if ($configuredTimezone === '') {
    $configuredTimezone = 'Europe/Berlin';
}
if (!@date_default_timezone_set($configuredTimezone)) {
    date_default_timezone_set('UTC');
}

$tz = new DateTimeZone(date_default_timezone_get());
$sampleIntervalMinutes = (int)getenv('UPTIME_SAMPLE_INTERVAL_MINUTES');
if ($sampleIntervalMinutes <= 0) {
    $sampleIntervalMinutes = 5;
}

$maxTrustedGapSeconds = $sampleIntervalMinutes * 60;

function addDurationToHours(int $startTs, int $endTs, int $status, DateTimeZone $tz, array &$hours): void
{
    while ($startTs < $endTs) {
        $cursor = (new DateTimeImmutable('@' . $startTs))->setTimezone($tz);
        $hourStart = $cursor->setTime((int)$cursor->format('H'), 0, 0);
        $hourEndTs = $hourStart->modify('+1 hour')->getTimestamp();
        $segmentEndTs = min($endTs, $hourEndTs);
        $duration = max(0, $segmentEndTs - $startTs);

        if ($duration > 0) {
            $hourKey = $hourStart->format('Y-m-d H:00');
            if (!isset($hours[$hourKey])) {
                $hours[$hourKey] = ['total_seconds' => 0, 'up_seconds' => 0];
            }

            $hours[$hourKey]['total_seconds'] += $duration;
            if ($status === 1) {
                $hours[$hourKey]['up_seconds'] += $duration;
            }
        }

        $startTs = $segmentEndTs;
    }
}

$dataFile = __DIR__ . '/../../database/data/uptime.json';
if (!file_exists($dataFile)) {
    header('Content-Type: image/svg+xml; charset=UTF-8');
    echo '<svg width="600" height="140" xmlns="http://www.w3.org/2000/svg"><rect width="600" height="140" fill="#242933"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#fff" font-family="Arial" font-size="16">Uptime data file not found</text></svg>';
    exit;
}

$raw = file_get_contents($dataFile);
$data = json_decode($raw, true);
if (!is_array($data) || count($data) === 0) {
    header('Content-Type: image/svg+xml; charset=UTF-8');
    echo '<svg width="600" height="140" xmlns="http://www.w3.org/2000/svg"><rect width="600" height="140" fill="#242933"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#fff" font-family="Arial" font-size="16">No uptime data available</text></svg>';
    exit;
}

$samples = [];
foreach ($data as $timestamp => $value) {
    $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i', (string)$timestamp, $tz);
    if ($dt === false) {
        $parsedTs = strtotime((string)$timestamp);
        if ($parsedTs === false) {
            continue;
        }
        $dt = (new DateTimeImmutable('@' . $parsedTs))->setTimezone($tz);
    }

    $samples[] = [
        'ts' => $dt->getTimestamp(),
        'status' => ((int)$value === 1) ? 1 : 0,
    ];
}

if (count($samples) === 0) {
    header('Content-Type: image/svg+xml; charset=UTF-8');
    echo '<svg width="600" height="140" xmlns="http://www.w3.org/2000/svg"><rect width="600" height="140" fill="#242933"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#fff" font-family="Arial" font-size="16">No valid uptime data available</text></svg>';
    exit;
}

usort($samples, function(array $a, array $b): int {
    return $a['ts'] <=> $b['ts'];
});

$now = new DateTimeImmutable('now', $tz);
$nowTs = $now->getTimestamp();
$currentHourStart = $now->setTime((int)$now->format('H'), 0, 0);
$windowStartHour = $currentHourStart->modify('-23 hours');
$windowStartTs = $windowStartHour->getTimestamp();

$statusAtWindowStart = 0;
$lastSampleBeforeWindowStartTs = null;
foreach ($samples as $sample) {
    if ($sample['ts'] <= $windowStartTs) {
        $statusAtWindowStart = $sample['status'];
        $lastSampleBeforeWindowStartTs = $sample['ts'];
        continue;
    }
    break;
}

if ($lastSampleBeforeWindowStartTs === null || ($windowStartTs - $lastSampleBeforeWindowStartTs) > $maxTrustedGapSeconds) {
    $statusAtWindowStart = 0;
}

$timeline = [
    ['ts' => $windowStartTs, 'status' => $statusAtWindowStart],
];

foreach ($samples as $sample) {
    if ($sample['ts'] > $windowStartTs && $sample['ts'] <= $nowTs) {
        $timeline[] = $sample;
    }
}

$dedupedTimeline = [];
foreach ($timeline as $sample) {
    $lastIndex = count($dedupedTimeline) - 1;
    if ($lastIndex >= 0 && $dedupedTimeline[$lastIndex]['ts'] === $sample['ts']) {
        $dedupedTimeline[$lastIndex]['status'] = $sample['status'];
        continue;
    }
    $dedupedTimeline[] = $sample;
}

$timeline = $dedupedTimeline;
if (empty($timeline)) {
    $timeline[] = ['ts' => $windowStartTs, 'status' => 0];
}

$boundedTimeline = [];
for ($i = 0; $i < count($timeline); $i++) {
    $currentSample = $timeline[$i];
    $boundedTimeline[] = $currentSample;

    if ($i === count($timeline) - 1) {
        continue;
    }

    $nextSample = $timeline[$i + 1];
    $trustedUntilTs = min($currentSample['ts'] + $maxTrustedGapSeconds, $nextSample['ts']);
    if ($trustedUntilTs < $nextSample['ts']) {
        $boundedTimeline[] = ['ts' => $trustedUntilTs, 'status' => 0];
    }
}

$timeline = $boundedTimeline;
$lastSample = $timeline[count($timeline) - 1];
$trustedNowTs = min($lastSample['ts'] + $maxTrustedGapSeconds, $nowTs);
if ($lastSample['ts'] < $trustedNowTs) {
    $timeline[] = ['ts' => $trustedNowTs, 'status' => $lastSample['status']];
}
if ($trustedNowTs < $nowTs) {
    $timeline[] = ['ts' => $trustedNowTs, 'status' => 0];
    $timeline[] = ['ts' => $nowTs, 'status' => 0];
}

$hours = [];
for ($i = 0; $i < count($timeline) - 1; $i++) {
    $startTs = (int)$timeline[$i]['ts'];
    $endTs = (int)$timeline[$i + 1]['ts'];
    $status = (int)$timeline[$i]['status'];

    if ($endTs <= $startTs) {
        continue;
    }

    addDurationToHours($startTs, $endTs, $status, $tz, $hours);
}

$normalizedHours = [];
for ($i = 0; $i < 24; $i++) {
    $bucket = $windowStartHour->modify('+' . $i . ' hours');
    $bucketKey = $bucket->format('Y-m-d H:00');
    $normalizedHours[$bucketKey] = $hours[$bucketKey] ?? ['total_seconds' => 0, 'up_seconds' => 0];
}
$hours = $normalizedHours;

if (empty($hours)) {
    header('Content-Type: image/svg+xml; charset=UTF-8');
    echo '<svg width="600" height="140" xmlns="http://www.w3.org/2000/svg"><rect width="600" height="140" fill="#242933"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#fff" font-family="Arial" font-size="16">No uptime checks in last 24 hours</text></svg>';
    exit;
}

$labels = array_keys($hours);
$values = [];
foreach ($hours as $hour => $stats) {
    if ((int)$stats['total_seconds'] > 0) {
        $availability = ($stats['up_seconds'] / $stats['total_seconds']) * 100;
    } else {
        $availability = 0.0;
    }
    $values[] = $availability;
}
$count = max(count($values), 1);
$maxValue = 100;

$w = 700;
$h = 340;
$padding = 50;
$plotW = $w - 2 * $padding;
$plotH = $h - 2 * $padding;

$points = [];
for ($i = 0; $i < $count; $i++) {
    $x = $padding + ($count === 1 ? $plotW / 2 : $i * ($plotW / max($count - 1, 1)));
    $y = $h - $padding - ($values[$i] / $maxValue) * $plotH;
    $points[] = ['x' => $x, 'y' => $y];
}

$axis = '<line x1="' . $padding . '" y1="' . ($padding) . '" x2="' . $padding . '" y2="' . ($h - $padding) . '" stroke="#B9C9E6" stroke-width="1"/>';
$axis .= '<line x1="' . $padding . '" y1="' . ($h - $padding) . '" x2="' . ($w - $padding) . '" y2="' . ($h - $padding) . '" stroke="#B9C9E6" stroke-width="1"/>';

$grid = '';
$gridLevels = [100 => '100%', 75 => '75%', 50 => '50%', 25 => '25%', 0 => '0%'];
foreach ($gridLevels as $levelValue => $levelLabel) {
    $y = $h - $padding - ($levelValue / $maxValue) * $plotH;
    $grid .= '<line x1="' . $padding . '" y1="' . $y . '" x2="' . ($w - $padding) . '" y2="' . $y . '" stroke="#D7E6EB" stroke-width="1" stroke-dasharray="2,2"/>';
    $grid .= '<text x="12" y="' . ($y + 4) . '" fill="#364F5C" font-family="Arial" font-size="12">' . $levelLabel . '</text>';
}


$pointString = implode(' ', array_map(function($p){ return $p['x'].','.$p['y']; }, $points));
$line = '<polyline fill="none" stroke="#26B76D" stroke-width="3" points="' . $pointString . '" stroke-linecap="round" stroke-linejoin="round" />';

$markerDots = '';
foreach ($points as $p) {
    $markerDots .= '<circle cx="' . $p['x'] . '" cy="' . $p['y'] . '" r="4" fill="#FFFFFF" stroke="#26B76D" stroke-width="2" />';
}

$labelMarks = '';
$targetTicks = 7;
$tickStep = max(1, (int)ceil(max($count - 1, 1) / ($targetTicks - 1)));
foreach ($labels as $i => $label) {
    if ($i % $tickStep !== 0 && $i !== $count - 1) {
        continue;
    }

    $x = $padding + ($count === 1 ? $plotW / 2 : $i * ($plotW / max($count - 1, 1)));
    $labelDate = DateTimeImmutable::createFromFormat('Y-m-d H:i', (string)$label, $tz);
    if ($labelDate === false) {
        $labelTs = strtotime((string)$label);
        if ($labelTs !== false) {
            $labelDate = (new DateTimeImmutable('@' . $labelTs))->setTimezone($tz);
        }
    }

    $labelText = $labelDate instanceof DateTimeImmutable
        ? $labelDate->format('H:i')
        : substr((string)$label, 11, 5);
    $labelMarks .= '<text x="' . $x . '" y="' . ($h - $padding + 24) . '" fill="#364F5C" font-family="Arial" font-size="11" text-anchor="middle">' . htmlspecialchars($labelText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</text>';
}

header('Content-Type: image/svg+xml; charset=UTF-8');

echo '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '" viewBox="0 0 ' . $w . ' ' . $h . '">';
echo '<rect width="100%" height="100%" fill="#F8FFFB" />';
echo $grid;
echo $axis;
echo $line;
echo $markerDots;
echo $labelMarks;
echo '</svg>';
exit;
