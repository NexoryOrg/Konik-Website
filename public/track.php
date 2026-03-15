<?php
$file = __DIR__ . "/../datenbank/data/visitors.json";

$data = [];
if (file_exists($file)) {
    $contents = file_get_contents($file);
    $parsed = json_decode($contents, true);
    if (is_array($parsed)) {
        $data = $parsed;
    }
}

$hour = date("Y-m-d H:00");

if (!isset($data[$hour]) || !is_numeric($data[$hour])) {
    $data[$hour] = 0;
}

$data[$hour]++;

// Nur die letzten 24 Stunden behalten
$data = array_slice($data, -24, 24, true);

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);

header('Content-Type: application/json; charset=UTF-8');
echo json_encode(['status' => 'ok', 'hour' => $hour, 'count' => $data[$hour]]);
