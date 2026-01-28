<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="stil/galerie.css">
    <link rel="stylesheet" href="stil/navbar.css">
    <link rel="stylesheet" href="stil/footer.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="galerie-container">
        <div class="galerie"></div>
    </div>

    <div class="lightbox" id="lightbox">
        <span class="close" id="lightbox-close">&times;</span>
        <img src="" alt="Lightbox Bild" id="lightbox-img">
    </div>

    <?php include 'footer.php'; ?>

    <script src="funktionen/galerie.js"></script>
</body>
</html>