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
                $events = [
                    ['year' => '20. Mai 2020', 'title' => 'Projekt Konik-Pferde', 'desc' => 'Die ersten 3 Pferde (Helinga, Tek, Bernadin) haben den Nationalpark Schwarzwald erreicht und starten ein tolles Leben.', 'img' => 'datenbank/bilder/pferde/start-projekt.jpg'],
                    ['year' => '24. Mai 2020', 'title' => 'Erstes Fohlen geboren', 'desc' => 'Das alle erste Fohlen Ronda genannt, der Herde im Nationalpark ist auf die Welt gekommen.', 'img' => 'datenbank/bilder/pferde/ronda.jpg'],
                    ['year' => '24. Mai 2020', 'title' => '"Die Kleine" auf der Welt', 'desc' => 'Das 2 Fohlen Brundhilde durfte das Licht der Welt erblicken.', 'img' => 'datenbank/bilder/pferde/brunhilde.jpg'],
                    ['year' => '05. April 2022', 'title' => 'Nächstes Fohlen', 'desc' => '2 Jahre später durfte schon ein weiteres Fohlen ,mit dem Namen Buran, durfte der Herde beitretten.', 'img' => 'datenbank/bilder/pferde/buran.jpg']
                ];

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