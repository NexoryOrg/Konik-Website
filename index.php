<?php
session_start();
?>

<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="stil/index.css">
  <link rel="stylesheet" href="stil/navbar.css">
  <link rel="stylesheet" href="stil/footer.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <section class="hero">
        <img src="datenbank/bilder/hintergrund/pferde.jpeg" alt="Wildpferde auf einer Wiese">
        <div class="hero-overlay">
            <h1>Wildpferde</h1>
            <p>Nationalpark Schwarzwald</p>
        </div>
        <div class="scroll-hint" id="scrollHint">
            <span class="text">Scroll</span>

            <svg class="arrow" width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path d="M12 5v14M12 19l-6-6M12 19l6-6"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"/>
            </svg>
        </div>
    </section>

    <section>
        <h2 class="überschrift-h2">Informationen zu den Koniks</h2>
        <section class="text-section">
            <p>Wildpferde an der Schwarzwald-Hochstraße</p>
            <p><a href="zeitstrahl.php" class="info-button">Mehr Infos</a></p>
        </section>
    </section>

    <section>
        <h2 class="überschrift-h2">Anfahrt zum Nationalpark</h2>
        <div class="map-container">
            <div id="map-placeholder">
                <button id="load-map">📍 Standort anzeigen</button>
            </div>
            <div id="map" style="display:none;"></div>
        </div>
    </section>


    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <?php include 'footer.php'; ?>

    <script src="funktionen/index.js"></script>
</body>
</html>