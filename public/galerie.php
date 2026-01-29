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
    $stmt = $pdo->query("SELECT jahr, alt, src FROM galerie ORDER BY jahr DESC");

    $galerie = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $jahr = $row['jahr'];
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

    <link rel="stylesheet" href="stil/galerie.css">
    <link rel="stylesheet" href="stil/navbar.css">
    <link rel="stylesheet" href="stil/footer.css">
    <title>Galerie</title>
</head>
<body>


    <?php include 'navbar.php'; ?>

    <div class="galerie-container">
        <div class="zeitstrahl-box">
            <div class="zeitstrahl">
                <?php foreach($galerie as $jahr => $bilder): ?>
                    <div class="zeitpunkt"><span><?= e($jahr) ?></span></div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="galerie-box">
            <div class="galerie">
                <?php foreach($galerie as $jahr => $bilder): ?>
                    <div class="jahr-section">
                        <h2><?= e($jahr) ?></h2>
                        <div class="bilder">
                            <?php foreach($bilder as $bild): ?>
                                <img src="<?= safe_src($bild['src']) ?>" alt="<?= e($bild['alt']) ?>" loading="lazy" onerror="this.src='datenbank/bilder/error.jpg'">
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="lightbox" id="lightbox">
        <span class="close" id="lightbox-close">&times;</span>

        <div class="lightbox-info">
            <span id="lightbox-jahr"></span>
            <span id="lightbox-alt"></span>
        </div>

        <div class="lightbox-nav">
            <div class="nav-left"></div>
            <img src="" alt="" id="lightbox-img">
            <div class="nav-right"></div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="funktionen/galerie.js"></script>
</body>
</html>