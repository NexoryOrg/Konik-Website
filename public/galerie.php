<?php
require_once __DIR__ . '/init.php';

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
    error_log('DB error: ' . $e->getMessage());
    echo "❌ DB Verbindung fehlgeschlagen.";
    $pdo = null;
}

if ($pdo) {
    $stmt = $pdo->query("SELECT year, alt, src FROM galerie ORDER BY year DESC");

    $galerie = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $jahr = $row['year'];
        if (!isset($galerie[$jahr])) {
            $galerie[$jahr] = [];
        }
        $galerie[$jahr][] = [
            'src' => $row['src'],
            'alt' => $row['alt']
        ];
    }
} else {
    $galerie = [];
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self';">
    <link rel="stylesheet" href="stil/galerie.css">
    <link rel="stylesheet" href="stil/navbar.css">
    <link rel="stylesheet" href="stil/footer.css">
    <title>Galerie</title>
</head>
<body>


    <?php include 'navbar.php'; ?>

    <div class="gallery-container">
        <div class="timeline-box">
            <div class="timeline">
                <?php foreach($galerie as $jahr => $bilder): ?>
                    <div class="timeline-dot"><span><?= e($jahr) ?></span></div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="gallery-box">
            <div class="gallery">
                <?php foreach($galerie as $jahr => $bilder): ?>
                    <div class="year-section">
                        <h2><?= e($jahr) ?></h2>
                        <div class="images">
                            <?php foreach($bilder as $bild): ?>
                                <img src="<?= safe_src($bild['src']) ?>" alt="<?= e($bild['alt']) ?>" loading="lazy" onerror="this.src='datenbank/bilder/error.jpg'">
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

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

    <?php include 'footer.php'; ?>

    <script src="funktionen/galerie.js"></script>
</body>
</html>