<?php
if (!ob_start('ob_gzhandler')) {
    ob_start();
}
header('Vary: Accept-Encoding');

header('Cache-Control: max-age=3600, public');
header('Expires: ' . gmdate('D, d M Y H:i:s', time()+3600) . ' GMT');

$jsonFile = __DIR__ . '/datenbank/json/gallery.json';

if (!file_exists($jsonFile)) {
    die("JSON file not found!");
}

$gallery = json_decode(file_get_contents($jsonFile), true);
krsort($gallery);

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery</title>
    <link rel="icon" type="image/png" href="datenbank/bilder/logo/logo.png">

    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="stil/gallery.css">
    <link rel="stylesheet" href="!navebar/navbar.css">
    <link rel="stylesheet" href="!footer/footer.css">
<?php
if (!empty($gallery)) {
    $firstYear = array_key_first($gallery);
    if (!empty($gallery[$firstYear])) {
        $firstImg = htmlspecialchars($gallery[$firstYear][0]['src'], ENT_QUOTES, 'UTF-8');
        echo "    <link rel=\"preload\" as=\"image\" href=\"{$firstImg}\">\n";
    }
}
?>
</head>
<body>

<?php include '!navebar/navbar.php'; ?>

<div class="gallery-container">

    <!-- HISTORY -->
    <div class="history-box">
        <div class="history">
            <?php foreach($gallery as $year => $images): ?>
                <div class="history-dot" data-year="<?= htmlspecialchars($year, ENT_QUOTES, 'UTF-8') ?>">
                    <span><?= htmlspecialchars($year, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- GALLERY -->
    <div class="gallery-box">
        <div class="gallery">
            <?php foreach($gallery as $year => $images): ?>
                <div class="year-section" id="year-<?= htmlspecialchars($year, ENT_QUOTES, 'UTF-8') ?>">
                    <h2><?= htmlspecialchars($year, ENT_QUOTES, 'UTF-8') ?></h2>
                    <div class="images">
                        <?php foreach($images as $image): ?>
                            <img 
                                data-src="<?= htmlspecialchars($image['src'], ENT_QUOTES, 'UTF-8') ?>" 
                                src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==" 
                                alt="<?= htmlspecialchars($image['alt'], ENT_QUOTES, 'UTF-8') ?>" 
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

    <script defer src="funktionen/gallery.js"></script>
    <script defer src="!navebar/navbar.js"></script>

    </body>
</html>