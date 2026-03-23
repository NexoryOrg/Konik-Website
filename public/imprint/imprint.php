<?php
require_once __DIR__ . '/../init.php';
?>

<!doctype html>
<html lang="<?= e(current_lang()) ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <base href="/">

        <title><?= e(t('imprint.title')) ?></title>
        <link rel="icon" type="image/png" href="/database/images/logo/logo.png">

        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="/navbar/navbar.css">
        <link rel="stylesheet" href="/footer/footer.css">
    </head>
    <body>

        <?php include __DIR__ . '/../navbar/navbar.php'; ?>

        <main style="max-width:900px;margin:120px auto 80px;padding:0 20px;font-family:'Poppins',sans-serif;line-height:1.7;">
            <h1><?= e(t('imprint.heading')) ?></h1>

            <section>
                <h2><?= e(t('imprint.section.imprint')) ?></h2>
                <p><strong><?= e(t('imprint.operator')) ?></strong><br>
                Konik-Website<br>
                Musterstraße 1<br>
                12345 Musterstadt<br>
                Germany</p>

                <p><strong><?= e(t('imprint.contact')) ?></strong><br>
                Phone: +49 123 456789<br>
                Email: <a href="mailto:info@konik-website.example">info@konik-website.example</a></p>

                <p><strong><?= e(t('imprint.responsible')) ?></strong><br>
                Max Mustermann<br>
                Musterstraße 1<br>
                12345 Musterstadt</p>

                <p><strong><?= e(t('imprint.vat')) ?></strong><br>
                DE123456789</p>

                <p><strong><?= e(t('imprint.disclaimer')) ?></strong><br>
                <?= e(t('imprint.disclaimer.text')) ?>
                </p>

                <p><strong><?= e(t('imprint.external')) ?></strong><br>
                <?= e(t('imprint.external.text')) ?></p>
            </section>

            <section>
                <h2><?= e(t('imprint.section.privacy')) ?></h2>
                <p><?= e(t('imprint.privacy.text1')) ?>
                </p>

                <p><?= e(t('imprint.privacy.text2')) ?></p>

                <p><?= e(t('imprint.privacy.text3')) ?></p>
            </section>

            <section>
                <h2><?= e(t('imprint.section.reach')) ?></h2>
                <p><?= e(t('imprint.reach.text')) ?> <a href="<?= e(with_lang('/contact/contact.php')) ?>"><?= e(t('nav.contact')) ?></a> - <a href="mailto:info@konik-website.example">info@konik-website.example</a>.</p>
            </section>
        </main>

        <?php include __DIR__ . '/../footer/footer.php'; ?>

        <script src="/navbar/navbar.js"></script>
    </body>
</html>
