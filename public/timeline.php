<?php
if (!ob_start('ob_gzhandler')) {
    ob_start();
}
header('Vary: Accept-Encoding');

header('Cache-Control: max-age=3600, public');
header('Expires: ' . gmdate('D, d M Y H:i:s', time()+3600) . ' GMT');

$jsonFile = __DIR__ . '/datenbank/json/timeline.json';
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
        foreach ($eventsData as $date => $entries) {
            foreach ($entries as $entry) {
                $events[] = [
                    'date' => $date,
                    'title' => $entry['alt'] ?? '',
                    'des' => $entry['des'] ?? '',
                    'image' => $entry['src'] ?? ''
                ];
            }
        }

        usort($events, function($a, $b) {
            $da = DateTime::createFromFormat('d.m.Y', $a['date']);
            $db = DateTime::createFromFormat('d.m.Y', $b['date']);
            return $da <=> $db;
        });
    }
}
?>

<!doctype html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="Content-Security-Policy" content="default-src 'self'; style-src 'self' https://cdnjs.cloudflare.com; script-src 'self';">
        <title>Timeline</title>
        <link rel="icon" type="image/png" href="datenbank/bilder/logo/logo.png">
        <link rel="preconnect" href="https://cdnjs.cloudflare.com">
        <link rel="preconnect" href="https://cdnjs.cloudflare.com">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link rel="stylesheet" href="stil/timeline.css">
        <link rel="stylesheet" href="!navebar/navbar.css">
        <link rel="stylesheet" href="!footer/footer.css">
<?php
if (!empty($events) && !empty($events[0]['image'])) {
    $img = htmlspecialchars($events[0]['image'], ENT_QUOTES, 'UTF-8');
    echo "        <link rel=\"preload\" as=\"image\" href=\"{$img}\">\n";
}
?>
    </head>
    <body>

        <?php include '!navebar/navbar.php'; ?>

        <div class="timeline-wrapper">
            <div class="timeline">
                <?php if(empty($events)): ?>
                    <p>Keine Einträge im Zeitstrahl vorhanden.</p>
                <?php else: ?>
                    <?php foreach($events as $index => $event): ?>
                        <div class="timeline-item <?= $index % 2 == 0 ? 'left' : 'right' ?>">
                            <div class="timeline-date"><?= htmlspecialchars($event['date']) ?></div>
                            <div class="timeline-content">
                                <img class="timeline-img" data-src="<?= htmlspecialchars($event['image']) ?>" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==" alt="<?= htmlspecialchars($event['title']) ?>">
                                <h3 class="timeline-title"><?= htmlspecialchars($event['title']) ?></h3>
                                <p><?= htmlspecialchars($event['des']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php include '!footer/footer.php'; ?>

        <script defer src="funktionen/timeline.js"></script>
        <script defer src="!navebar/navbar.js"></script>

    </body>
</html>
