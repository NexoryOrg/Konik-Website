<?php
$jsonFile = __DIR__ . '/datenbank/json/timeline.json';
$events = [];

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
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link rel="stylesheet" href="stil/timeline.css">
        <link rel="stylesheet" href="!navebar/navbar.css">
        <link rel="stylesheet" href="!footer/footer.css">
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
                                <img class="timeline-img" src="<?= htmlspecialchars($event['image']) ?>" alt="<?= htmlspecialchars($event['title']) ?>">
                                <h3 class="timeline-title"><?= htmlspecialchars($event['title']) ?></h3>
                                <p><?= htmlspecialchars($event['des']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php include '!footer/footer.php'; ?>

        <script src="funktionen/timeline.js"></script>
        <script src="!navebar/navbar.js"></script>

    </body>
</html>