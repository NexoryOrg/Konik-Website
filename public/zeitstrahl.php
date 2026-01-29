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

$stmt = $pdo->query("SELECT datum, titel, beschreibung, bild FROM zeitstrahl ORDER BY datum ASC");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Zeitstrahl</title>
    <link rel="stylesheet" href="stil/zeitstrahl.css">
    <link rel="stylesheet" href="stil/navbar.css">
    <link rel="stylesheet" href="stil/footer.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="timeline-wrapper">
    <div class="timeline">
        <?php if(empty($events)): ?>
            <p>Keine Einträge im Zeitstrahl vorhanden.</p>
        <?php else: ?>
            <?php foreach($events as $index => $event): ?>
                <div class="timeline-item <?= $index % 2 == 0 ? 'left' : 'right' ?>">
                    <div class="timeline-date"><?= date('d M Y', strtotime($event['datum'])) ?></div>
                    <div class="timeline-content">
                        <img class="timeline-img" src="<?= htmlspecialchars($event['bild']) ?>" alt="<?= htmlspecialchars($event['titel']) ?>">
                        <h3><?= htmlspecialchars($event['titel']) ?></h3>
                        <p><?= htmlspecialchars($event['beschreibung']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>

<script src="funktionen/zeitstrahl.js"></script>
</body>
</html>