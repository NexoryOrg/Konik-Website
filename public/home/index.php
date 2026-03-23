<?php
require_once __DIR__ . '/../init.php';
?>

<!doctype html>
<html lang="<?= e(current_lang()) ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <base href="/">
        <title><?= e(t('home.title')) ?></title>
        <link rel="icon" type="image/png" href="database/images/logo/logo.png">

        <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link rel="stylesheet" href="../home/index.css">
        <link rel="stylesheet" href="../navbar/navbar.css">
        <link rel="stylesheet" href="../footer/footer.css">
    </head>
    <body>

        <?php 
        include __DIR__ . '/../track.php'; 

        include __DIR__ . '/../navbar/navbar.php';
        ?>

        <section class="hero">
            <img src="../database/images/background/pferde.jpeg" alt="<?= e(t('home.hero.image_alt')) ?>">
            <div class="hero-content">
                <h1><?= e(t('home.hero.title')) ?></h1>
                <p><?= e(t('home.hero.subtitle')) ?></p>
            </div>
            <div class="scroll-arrow" id="scrollArrow">
                <span class="text"><?= e(t('home.scroll')) ?></span>
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
            <h2 class="section-heading"><?= e(t('home.about.heading')) ?></h2>
            <section class="text-section">
                <div class="info-box">
                    <p><?= e(t('home.about.intro')) ?></p>
                </div>
                <div class="info-box">
                    <h3><?= e(t('home.about.habitat.title')) ?></h3>
                    <p><?= e(t('home.about.habitat.text')) ?></p>
                    <img class="gallery-thumb" src="../database/images/horses/2020/start-projekt.jpg" alt="<?= e(t('home.about.habitat.image_alt')) ?>" loading="lazy">
                </div>
                <div class="info-box">
                    <h3><?= e(t('home.about.behavior.title')) ?></h3>
                    <p><?= e(t('home.about.behavior.text')) ?></p>
                    <img class="gallery-thumb" src="../database/images/uploads/b99d7f5e1c51e03c863226846247b1fc.jpg" alt="<?= e(t('home.about.behavior.image_alt')) ?>" loading="lazy">
                </div>
                <div class="info-box">
                    <h3><?= e(t('home.about.care.title')) ?></h3>
                    <p><?= e(t('home.about.care.text')) ?></p>
                    <img class="gallery-thumb" src="../database/images/uploads/8011e2783419c035edd40faa24f74c5f.jpg" alt="<?= e(t('home.about.care.image_alt')) ?>" loading="lazy">
                </div>
            </section>
        </section>

        <div class="section-separator"></div>

        <section>
            <h2 class="section-heading"><?= e(t('home.map.heading')) ?></h2>
            <div class="map-container">
                <div id="map-placeholder">
                    <button
                        id="load-map"
                        data-popup-title="<?= e(t('home.map.popup_title')) ?>"
                        data-popup-subtitle="<?= e(t('home.map.popup_subtitle')) ?>"
                        data-marker-title="<?= e(t('home.map.marker_title')) ?>"
                    ><?= e(t('home.map.button')) ?></button>
                </div>
                <div id="map" style="display:none;"></div>
            </div>
        </section>

        <div id="lightbox">
            <button class="nav prev" aria-label="<?= e(t('home.lightbox.prev')) ?>">&#10094;</button>
            <figure>
                <img id="lightbox-image" alt="">
                <figcaption id="description"></figcaption>
            </figure>
            <button class="nav next" aria-label="<?= e(t('home.lightbox.next')) ?>">&#10095;</button>
            <span id="close" aria-label="<?= e(t('home.lightbox.close')) ?>">&times;</span>
        </div>

        <div class="section-separator"></div>

        <?php include __DIR__ . '/../footer/footer.php'; ?>

        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
        <script src="../home/index.js"></script>
        <script src="../navbar/navbar.js"></script>

    </body>
</html>
