<?php
require_once __DIR__ . '/init.php';
?>

<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self';">
  <link rel="stylesheet" href="stil/index.css">
  <link rel="stylesheet" href="stil/navbar.css">
  <link rel="stylesheet" href="stil/footer.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <section class="hero">
        <img src="datenbank/bilder/hintergrund/pferde.jpeg" alt="Wildpferde auf einer Wiese">
        <div class="hero-content">
            <h1>Wildpferde</h1>
            <p>Nationalpark Schwarzwald</p>
        </div>
        <div class="scroll-arrow" id="scrollArrow">
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
        <h2 class="section-heading">Informationen zu den Koniks</h2>
        <section class="text-section">
            <div class="info-box">
                <p>Im Nationalpark Schwarzwald werden seit einigen Jahren Konik-Pferde eingesetzt, um die offenen Bergweiden, die sogenannten Grinden, zu pflegen. Diese Grinden sind einzigartige Lebensräume hoch über dem Wald, die ohne Beweidung allmählich zuwachsen würden. Die robusten Tiere stammen aus Polen und sind eine ursprüngliche Pferderasse, in die noch Gene der ehemaligen Wildpferde (Tarpane) eingekreuzt wurden – daher ähneln sie stark den früher auch hier lebenden Wildpferden.</p>
            </div>
            <div class="info-box">
                <h3>Lebensraum</h3>
                <p>Die Koniks leben auf den höheren, offenen Heide- und Grasflächen im Nordschwarzwald, etwa rund um Zollstock/Hilseneck und in der Nähe des Schliffkopfs. Diese Grinden sind aufgrund ihrer speziellen Pflanzenwelt wichtig für viele seltene Tiere und Pflanzen. Ihre Beweidung hilft, die Flächen offen und vielfältig zu halten.</p>
                <img class="gallery-thumb" src="datenbank/bilder/pferde/start-projekt.jpg" alt="Der Lebensraum der Pferde" loading="lazy">
            </div>
            <div class="info-box">
                <h3>Verhalten</h3>
                <p>Koniks sind genügsam und anpassungsfähig. Sie leben meist in Herden mit einer Leitstute, sie grasen viel und verschieden – das heißt, sie fressen Gräser, Kräuter und auch Rinden oder junge Triebe. Durch ihr Fressverhalten tragen sie zur Strukturvielfalt der Vegetation bei, was wiederum anderen Arten zugutekommt, zum Beispiel Insekten, Vögeln oder Reptilien. Auch ihr Kot fördert den Nährstoffkreislauf und unterstützt Dung-organismen wie Käfer, was zusätzliche Nahrung für Vögel schafft.</p>
                <img class="gallery-thumb" src="datenbank/bilder/pferde/buran.jpg" alt="Verhalten der Koniks" loading="lazy">
            </div>
            <div class="info-box">
                <h3>Schutz und Pflege</h3>
                <p>Obwohl die Pferde wild wirken, werden sie regelmäßig betreut: Verantwortliche im Nationalpark überwachen ihre Gesundheit, kümmern sich um Hufpflege und steuern die Beweidungsdauer. Die Tiere gehören dem Zoo Karlsruhe, der eng mit dem Nationalpark zusammenarbeitet. Wichtig ist auch das richtige Zusammenleben mit Menschen: Besucherinnen und Besucher sollen Abstand halten, die Tiere nicht füttern oder stören, damit sie natürlich leben können</p>
                <img class="gallery-thumb" src="datenbank/bilder/pferde/brunhilde.jpg" alt="Schutz und Pflege der Pferde" loading="lazy">
            </div>
        </section>
    </section>

    <section>
        <h2 class="section-heading">Anfahrt zum Nationalpark</h2>
        <div class="map-container">
            <div id="map-placeholder">
                <button id="load-map">📍 Standort anzeigen</button>
            </div>
            <div id="map" style="display:none;"></div>
        </div>
    </section>

    <div id="lightbox">
        <button class="nav prev" aria-label="Previous image">&#10094;</button>
        <figure>
            <img id="lightbox-image" alt="">
            <figcaption id="description"></figcaption>
        </figure>
        <button class="nav next" aria-label="Next image">&#10095;</button>
        <span id="close" aria-label="Close">&times;</span>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <?php include 'footer.php'; ?>

    <script src="funktionen/index.js"></script>
</body>
</html>