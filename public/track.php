<?php

$file = __DIR__ . "/datenbank/data/visitors.json";

$data = [];
if (file_exists($file)) {
    $contents = file_get_contents($file);
    $parsed = json_decode($contents, true);
    if (is_array($parsed)) {
        $data = $parsed;
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
