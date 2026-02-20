<?php
$host = getenv('DB_HOST') ?: '';
$port = getenv('DB_PORT') ?: 3306;
$db   = getenv('DB_NAME') ?: '';
$user = getenv('DB_USER') ?: '';
$pass = getenv('DB_PASS') ?: '';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 10
        ]
    );

} catch (PDOException $e) {
    echo "❌ DB Verbindung fehlgeschlagen: " . $e->getMessage() . "<br>";
}

$stmt = $pdo->query("SELECT date, title, des, image FROM timeline ORDER BY date ASC");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                            <div class="timeline-date"><?= date('d M Y', strtotime($event['date'])) ?></div>
                            <div class="timeline-content">
                                <img class="timeline-img" src="<?= htmlspecialchars($event['image']) ?>" alt="<?= htmlspecialchars($event['title']) ?>">
                                <h3><?= htmlspecialchars($event['title']) ?></h3>
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