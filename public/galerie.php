<?php
if (!ob_start('ob_gzhandler')) {
    ob_start();
}
header('Vary: Accept-Encoding');

header('Cache-Control: max-age=3600, public');
header('Expires: ' . gmdate('D, d M Y H:i:s', time()+3600) . ' GMT');

$jsonDatei = __DIR__ . '/datenbank/json/galerie.json';

if (!file_exists($jsonDatei)) {
    die("JSON-Datei nicht gefunden!");
}

$galerie = json_decode(file_get_contents($jsonDatei), true);
krsort($galerie);

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
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galerie</title>
    <link rel="icon" type="image/png" href="datenbank/bilder/logo/logo.png">

    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="stil/galerie.css">
    <link rel="stylesheet" href="!navebar/navbar.css">
    <link rel="stylesheet" href="!footer/footer.css">
<?php
if (!empty($galerie)) {
    $firstYear = array_key_first($galerie);
    if (!empty($galerie[$firstYear])) {
        $firstImg = htmlspecialchars($galerie[$firstYear][0]['src'], ENT_QUOTES, 'UTF-8');
        echo "    <link rel=\"preload\" as=\"image\" href=\"{$firstImg}\">\n";
    }
}
?>
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
                                data-src="<?= htmlspecialchars($bild['src'], ENT_QUOTES, 'UTF-8') ?>" 
                                src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==" 
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

    <script defer src="funktionen/galerie.js"></script>
    <script defer src="!navebar/navbar.js"></script>

    </body>
</html>
