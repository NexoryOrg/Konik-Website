<?php
require_once __DIR__ . '/../init.php';
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Security-Policy" content="default-src 'self'; style-src 'self' 'unsafe-inline' https:; script-src 'self' https://unpkg.com 'unsafe-inline'; font-src 'self' data: https:; img-src 'self' data: blob: https:; connect-src 'self' https:;">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <base href="/">
        <title>Home</title>
        <link rel="icon" type="image/png" href="datenbank/bilder/logo/logo.png">

        <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link rel="stylesheet" href="../home/index.css">
        <link rel="stylesheet" href="../navebar/navbar.css">
        <link rel="stylesheet" href="../footer/footer.css">
    </head>
    <body>

        <?php 
        include __DIR__ . '/../track.php'; 

        include __DIR__ . '/../navebar/navbar.php';
        ?>

        <section class="hero">
            <img src="../datenbank/bilder/hintergrund/pferde.jpeg" alt="Wild horses in a meadow">
            <div class="hero-content">
                <h1>Wild Horses</h1>
                <p>Black Forest National Park</p>
            </div>
            <div class="scroll-arrow" id="scrollArrow">
                <span class="text">Scroll down</span>
                <svg class="arrow" width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <path d="M12 5v14M12 19l-6-6M12 19l6-6"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"/>
                </svg>
            </div>
        </section>

        <div class="section-separator"></div>

        <section>
            <h2 class="section-heading">About the Koniks</h2>
            <section class="text-section">
                <div class="info-box">
                    <p>In the Black Forest National Park, Konik horses have been used for several years to maintain the open mountain pastures known as "Grinden." These Grinden are unique habitats high above the forest that would gradually overgrow without grazing. The hardy animals come from Poland and are an original horse breed that still carries genes of the former wild horses (Tarpans) – which is why they closely resemble the wild horses that once roamed here.</p>
                </div>
                <div class="info-box">
                    <h3>Habitat</h3>
                    <p>The Koniks live on the higher, open heath and grasslands in the northern Black Forest, around Zollstock/Hilseneck and near the Schliffkopf. These Grinden are important for many rare animals and plants due to their special flora. Their grazing helps keep the areas open and diverse.</p>
                    <img class="gallery-thumb" src="../datenbank/bilder/pferde/2020/start-projekt.jpg" alt="The horses' habitat" loading="lazy">
                </div>
                <div class="info-box">
                    <h3>Behavior</h3>
                    <p>Koniks are thrifty and adaptable. They usually live in herds led by a dominant mare; they graze a lot and vary what they eat – that means they consume grasses, herbs and even bark or young shoots. Their feeding behavior contributes to the structural diversity of the vegetation, which in turn benefits other species such as insects, birds or reptiles. Their droppings also promote nutrient cycles and support dung organisms like beetles, providing additional food for birds.</p>
                    <img class="gallery-thumb" src="../datenbank/bilder/pferde/2022/buran.jpg" alt="Konik behavior" loading="lazy">
                </div>
                <div class="info-box">
                    <h3>Protection and Care</h3>
                    <p>Although the horses appear wild, they are regularly looked after: park officials monitor their health, attend to hoof care and manage the duration of grazing. The animals belong to Karlsruhe Zoo, which works closely with the national park. It is also important to interact correctly with people: visitors should keep their distance, not feed or disturb the animals so that they can live naturally.</p>
                    <img class="gallery-thumb" src="../datenbank/bilder/pferde/2020/brunhilde.jpg" alt="Protection and care of the horses" loading="lazy">
                </div>
            </section>
        </section>

        <div class="section-separator"></div>

        <section>
            <h2 class="section-heading">Getting to the National Park</h2>
            <div class="map-container">
                <div id="map-placeholder">
                    <button id="load-map">📍 Show location</button>
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

        <div class="section-separator"></div>

        <?php include __DIR__ . '/../footer/footer.php'; ?>

        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
        <script src="../home/index.js"></script>
        <script src="../navebar/navbar.js"></script>

    </body>
</html>