<?php
$dataFile = __DIR__ . '/../../datenbank/data/uptime.json';
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

$hours = [];
foreach ($data as $timestamp => $value) {
    $hour = date('Y-m-d H:00', strtotime($timestamp));
    if (!isset($hours[$hour])) {
        $hours[$hour] = ['total' => 0, 'up' => 0];
    }
    $hours[$hour]['total']++;
    $hours[$hour]['up'] += ($value ? 1 : 0);
}

$hours = array_slice($hours, -24, 24, true);
if (empty($hours)) {
    header('Content-Type: image/svg+xml; charset=UTF-8');
    echo '<svg width="600" height="140" xmlns="http://www.w3.org/2000/svg"><rect width="600" height="140" fill="#242933"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#fff" font-family="Arial" font-size="16">No uptime checks in last 24 hours</text></svg>';
    exit;
}

$labels = array_keys($hours);
$values = [];
foreach ($hours as $hour => $stats) {
    // Wenn mindestens ein check online, betrachten wir die Stunde als online.
    $online = ($stats['up'] / max($stats['total'], 1)) >= 0.5 ? 100 : 0;
    $values[] = $online;
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
$yOnline = $h - $padding - $plotH;
$yOffline = $h - $padding;
$grid .= '<line x1="' . $padding . '" y1="' . $yOnline . '" x2="' . ($w - $padding) . '" y2="' . $yOnline . '" stroke="#D7E6EB" stroke-width="1" stroke-dasharray="2,2"/>';
$grid .= '<text x="12" y="' . ($yOnline + 4) . '" fill="#364F5C" font-family="Arial" font-size="12">Online</text>';
$grid .= '<line x1="' . $padding . '" y1="' . $yOffline . '" x2="' . ($w - $padding) . '" y2="' . $yOffline . '" stroke="#D7E6EB" stroke-width="1" stroke-dasharray="2,2"/>';
$grid .= '<text x="12" y="' . ($yOffline + 4) . '" fill="#364F5C" font-family="Arial" font-size="12">Offline</text>';


$pointString = implode(' ', array_map(function($p){ return $p['x'].','.$p['y']; }, $points));
$line = '<polyline fill="none" stroke="#26B76D" stroke-width="3" points="' . $pointString . '" stroke-linecap="round" stroke-linejoin="round" />';

$markerDots = '';
foreach ($points as $p) {
    $markerDots .= '<circle cx="' . $p['x'] . '" cy="' . $p['y'] . '" r="4" fill="#FFFFFF" stroke="#26B76D" stroke-width="2" />';
}

$labelMarks = '';
$maxLabels = 8;
$step = max(1, (int)ceil($count / $maxLabels));
foreach ($labels as $i => $label) {
    if ($i % $step !== 0 && $i !== $count - 1) {
        continue;
    }
    $x = $padding + ($count === 1 ? $plotW / 2 : $i * ($plotW / max($count - 1, 1)));
    $labelText = date('H:i', strtotime($label));
    if ($count > 12) {
        $labelText = date('H', strtotime($label));
    }
    $labelMarks .= '<text x="' . $x . '" y="' . ($h - $padding + 24) . '" fill="#364F5C" font-family="Arial" font-size="12" text-anchor="middle">' . htmlspecialchars($labelText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</text>';
}

header('Content-Type: image/svg+xml; charset=UTF-8');

echo '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '" viewBox="0 0 ' . $w . ' ' . $h . '">';
echo '<rect width="100%" height="100%" fill="#F8FFFB" />';
echo $grid;
echo $axis;
echo $line;
echo $markerDots;
echo $labelMarks;
echo '<text x="' . ($w / 2) . '" y="25" fill="#0D4C3B" font-family="Arial" font-size="18" text-anchor="middle">Uptime status last ' . $count . ' hours</text>';
echo '</svg>';
exit;