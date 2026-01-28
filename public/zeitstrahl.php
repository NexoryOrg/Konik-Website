<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="stil/zeitstrahl.css">
    <link rel="stylesheet" href="stil/navbar.css">
    <link rel="stylesheet" href="stil/footer.css">
</head>
    <body>

        <?php include 'navbar.php'; ?>

        <div class="timeline-wrapper">
            <div class="timeline">
                <?php
                $jsonFile = 'datenbank/informationen/zeitstrahl-informationen.json';
                $jsonData = file_get_contents($jsonFile);
                $events = json_decode($jsonData, true);

                if ($events === null) {
                    echo "Fehler beim Laden der Events!";
                    exit;
                }

                foreach($events as $index => $event): ?>
                    <div class="timeline-item <?= $index % 2 == 0 ? 'left' : 'right' ?>">
                        <div class="timeline-date"><?= $event['year'] ?></div>
                        <div class="timeline-content">
                            <img class="timeline-img" src="<?= $event['img'] ?>" alt="<?= $event['title'] ?>">
                            <h3><?= $event['title'] ?></h3>
                            <p><?= $event['desc'] ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php include 'footer.php'; ?>

        <script src="funktionen/zeitstrahl.js"></script>

    </body>

</html>