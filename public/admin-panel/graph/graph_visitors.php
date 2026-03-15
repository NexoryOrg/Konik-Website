<?php
$dataFile = __DIR__ . '/../../datenbank/data/visitors.json';
if (!file_exists($dataFile)) {
    header('Content-Type: image/svg+xml; charset=UTF-8');
    echo '<svg width="600" height="140" xmlns="http://www.w3.org/2000/svg"><rect width="600" height="140" fill="#242933"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#fff" font-family="Arial" font-size="16">Visitor data file not found</text></svg>';
    exit;
}

$raw = file_get_contents($dataFile);
$data = json_decode($raw, true);
if (!is_array($data) || count($data) === 0) {
    header('Content-Type: image/svg+xml; charset=UTF-8');
    echo '<svg width="600" height="140" xmlns="http://www.w3.org/2000/svg"><rect width="600" height="140" fill="#242933"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#fff" font-family="Arial" font-size="16">No visitor data available</text></svg>';
    exit;
}

$last = array_slice($data, -7, 7, true);
$labels = array_keys($last);
$values = array_values($last);
$count = max(count($values), 1);
$maxValue = max($values) ?: 1;

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
for ($i = 0; $i <= 5; $i++) {
    $y = $h - $padding - ($i * ($plotH / 5));
    $value = round($maxValue * $i / 5);
    $grid .= '<line x1="' . $padding . '" y1="' . $y . '" x2="' . ($w - $padding) . '" y2="' . $y . '" stroke="#D7E6EB" stroke-width="1" stroke-dasharray="2,2"/>';
    $grid .= '<text x="12" y="' . ($y + 4) . '" fill="#364F5C" font-family="Arial" font-size="12">' . $value . '</text>';
}

$pointString = implode(' ', array_map(function($p){ return $p['x'].','.$p['y']; }, $points));
$line = '<polyline fill="none" stroke="#26B76D" stroke-width="3" points="' . $pointString . '" stroke-linecap="round" stroke-linejoin="round" />';

$markerDots = '';
foreach ($points as $p) {
    $markerDots .= '<circle cx="' . $p['x'] . '" cy="' . $p['y'] . '" r="4" fill="#FFFFFF" stroke="#26B76D" stroke-width="2" />';
}

$labelMarks = '';
foreach ($labels as $i => $label) {
    $x = $padding + ($count === 1 ? $plotW / 2 : $i * ($plotW / max($count - 1, 1)));
    $labelText = date('H:i', strtotime($label));
    if ($labelText === '00:00' && $label !== date('Y-m-d H:i', strtotime($label))) {
        $labelText = substr($label, 11, 5);
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
echo '</svg>';
exit;
