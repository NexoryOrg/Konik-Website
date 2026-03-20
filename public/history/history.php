<?php
require_once __DIR__ . '/../init.php';

if (!ob_start('ob_gzhandler')) { ob_start(); }
header('Vary: Accept-Encoding');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$jsonFile = __DIR__ . '/../database/json/history.json';
$events = [];

function minify_output($buffer) {
    $buffer = preg_replace('/<!--([\s\S]*?)-->/', '', $buffer);
    $buffer = preg_replace('/\s{2,}/', ' ', $buffer);
    $buffer = preg_replace('/>\s+</', '><', $buffer);
    return $buffer;
}
register_shutdown_function(function() {
    if (ob_get_length()) {
        $contents = ob_get_contents();
        ob_clean();
        echo minify_output($contents);
    }
});

if (file_exists($jsonFile)) {
    $data = file_get_contents($jsonFile);
    $eventsData = json_decode($data, true);

    if ($eventsData) {
        foreach ($eventsData as $entry) {
            $imgPath = '/' . ltrim(str_replace('../', '', $entry['src'] ?? ''), '/');
            $events[] = [
                'date' => $entry['date'] ?? '',
                'title' => $entry['title'] ?? '',
                'des' => $entry['description'] ?? '',
                'image' => $imgPath
            ];
        }

        usort($events, function($a, $b) {
            $da = new DateTime($a['date']);
            $db = new DateTime($b['date']);
            return $da <=> $db;
        });
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>History</title>
<base href="/">
<link rel="icon" type="image/png" href="/database/images/logo/logo.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="/history/history.css">
<link rel="stylesheet" href="/navbar/navbar.css">
<link rel="stylesheet" href="/footer/footer.css">
<?php
if (!empty($events) && !empty($events[0]['image'])) {
    $img = htmlspecialchars($events[0]['image'], ENT_QUOTES, 'UTF-8');
    echo "<link rel=\"preload\" as=\"image\" href=\"{$img}\">\n";
}
?>
</head>
<body>
<?php include __DIR__. '/../navbar/navbar.php'; ?>
<div class="history-wrapper">
    <div class="history">
        <?php if(empty($events)): ?>
            <p>No entries available in the history.</p>
        <?php else: ?>
            <?php foreach($events as $index => $event): ?>
                <div class="history-item <?= $index % 2 == 0 ? 'left' : 'right' ?>">
                    <div class="history-date"><?= htmlspecialchars($event['date']) ?></div>
                    <div class="history-content">
                        <img class="history-img" data-src="<?= htmlspecialchars($event['image']) ?>" alt="<?= htmlspecialchars($event['title']) ?>">
                        <h3 class="history-title"><?= htmlspecialchars($event['title']) ?></h3>
                        <p><?= htmlspecialchars($event['des']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__. '/../footer/footer.php'; ?>
<script defer src="/history/history.js"></script>
<script defer src="/navbar/navbar.js"></script>
</body>
</html>
