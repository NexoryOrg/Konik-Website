<?php
$jsonDatei = __DIR__ . '/datenbank/json/galerie.json';


if (!file_exists($jsonDatei)) {
    die("JSON-Datei nicht gefunden!");
}

$galerie = json_decode(file_get_contents($jsonDatei), true);


krsort($galerie);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galerie</title>
    <link rel="icon" type="image/png" href="datenbank/bilder/logo/logo.png">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="stil/galerie.css">
    <link rel="stylesheet" href="!navebar/navbar.css">
    <link rel="stylesheet" href="!footer/footer.css">
</head>
<body>

<?php include '!navebar/navbar.php'; ?>

<div class="gallery-container">

    <!-- TIMELINE -->
    <div class="timeline-box">
        <div class="timeline">
            <?php foreach($galerie as $jahr => $bilder): ?>
                <div class="timeline-dot" data-year="<?= htmlspecialchars($jahr, ENT_QUOTES, 'UTF-8') ?>">
                    <span><?= htmlspecialchars($jahr, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- GALLERY -->
    <div class="gallery-box">
        <div class="gallery">
            <?php foreach($galerie as $jahr => $bilder): ?>
                <div class="year-section" id="year-<?= htmlspecialchars($jahr, ENT_QUOTES, 'UTF-8') ?>">
                    <h2><?= htmlspecialchars($jahr, ENT_QUOTES, 'UTF-8') ?></h2>
                    <div class="images">
                        <?php foreach($bilder as $bild): ?>
                            <img 
                                src="<?= htmlspecialchars($bild['src'], ENT_QUOTES, 'UTF-8') ?>" 
                                alt="<?= htmlspecialchars($bild['alt'], ENT_QUOTES, 'UTF-8') ?>" 
                                loading="lazy"
                                onerror="this.src='datenbank/bilder/error.jpg'"
                            >
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    </div>

    <!-- LIGHTBOX -->
    <div id="lightbox">
        <button class="nav prev" aria-label="Previous image">&#10094;</button>
        <figure>
            <img id="lightbox-image" alt="">
            <div class="lightbox-info">
                <figcaption id="description"></figcaption>
                <span id="image-year"></span>
            </div>
        </figure>
        <button class="nav next" aria-label="Next image">&#10095;</button>
        <span id="close" aria-label="Close">&times;</span>
    </div>

    <?php include '!footer/footer.php'; ?>

    <script src="funktionen/galerie.js"></script>
    <script src="!navebar/navbar.js"></script>

    </body>
</html>