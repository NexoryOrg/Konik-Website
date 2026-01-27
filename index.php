<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Übersicht Seite</title>

  <link rel="stylesheet" href="stil/index.css">
  <link rel="stylesheet" href="stil/navbar.css">
  <link rel="stylesheet" href="stil/footer.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <!-- Hero Bereich mit echtem <img> -->
    <section class="hero">
        <img src="datenbank/bilder/hintergrund/pferde.jpeg" alt="Wildpferde auf einer Wiese">
        <div class="hero-overlay">
            <h1>Wildpferde</h1>
        </div>
    </section>

    <!-- Text und Button unter dem Bild -->
    <section class="text-section">
        <p>Wildpferde an der Schwarzwald-Hochstraße</p>
        <p><a class="info-button">Mehr Infos</a></p>
    </section>

    <?php include 'footer.php'; ?>

    <script src="js/index.js"></script>
</body>
</html>