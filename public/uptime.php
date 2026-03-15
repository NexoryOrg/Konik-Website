<?php
$file = __DIR__ . "/../datenbank/data/uptime.json";

$data = [];
if (file_exists($file)) {
    $contents = file_get_contents($file);
    $parsed = json_decode($contents, true);
    if (is_array($parsed)) {
        $data = $parsed;
    }
}

// status query: 1 = online, 0 = offline
$status = 1;
if (isset($_GET['status'])) {
    $status = ($_GET['status'] === '0') ? 0 : 1;
}

$time = date("Y-m-d H:i");
$data[$time] = $status;

// Keep last 48 hours for history
$data = array_slice($data, -48 * 60, 48 * 60, true);

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);

header('Content-Type: application/json; charset=UTF-8');
echo json_encode(['status' => 'ok', 'time' => $time, 'value' => $status]);
