<?php
require_once __DIR__ . '/../init.php';
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Imprint & Privacy</title>
        <link rel="icon" type="image/png" href="database/images/logo/logo.png">

        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="stil/index.css">
        <link rel="stylesheet" href="!navbar/navbar.css">
        <link rel="stylesheet" href="!footer/footer.css">
    </head>
    <body>

        <?php include '!navbar/navbar.php'; ?>

        <main style="max-width:900px;margin:120px auto 80px;padding:0 20px;font-family:'Poppins',sans-serif;line-height:1.7;">
            <h1>Imprint & Privacy Policy</h1>

            <section>
                <h2>Imprint</h2>
                <p><strong>Operator:</strong><br>
                Konik-Website<br>
                Musterstraße 1<br>
                12345 Musterstadt<br>
                Germany</p>

                <p><strong>Contact:</strong><br>
                Phone: +49 123 456789<br>
                Email: <a href="mailto:info@konik-website.example">info@konik-website.example</a></p>

                <p><strong>Responsible for content according to § 55 Abs. 2 RStV:</strong><br>
                Max Mustermann<br>
                Musterstraße 1<br>
                12345 Musterstadt</p>

                <p><strong>VAT identification number:</strong><br>
                DE123456789</p>

                <p><strong>Disclaimer:</strong><br>
                The content of this website has been created with the greatest care. However, we cannot guarantee the correctness, completeness and up-to-dateness of the content. As a service provider we are responsible for our own content on these pages in accordance with general laws. We are not obliged to monitor transmitted or stored external information and to investigate circumstances that indicate illegal activity.
                </p>

                <p><strong>External links:</strong><br>
                Our website contains links to external third-party websites over whose content we have no influence. Therefore we cannot assume any liability for this external content. The respective provider or operator of the pages is always responsible for the content of the linked pages.</p>
            </section>

            <section>
                <h2>Privacy Policy</h2>
                <p>We take your privacy seriously. Personal data that you provide via the contact form (name, email address, message) is used solely to answer your inquiry and is not shared with third parties.
                </p>

                <p>Cookies are used only to ensure that the website functions properly (e.g., for the navigation bar). No tracking cookies are set by this site.</p>

                <p>For detailed information on how we handle data, please refer to the full <a href="#">privacy policy</a> (coming soon).</p>
            </section>

            <section>
                <h2>How to reach us</h2>
                <p>If you have questions about this imprint or data protection, please contact us via <a href="contact.php">the contact form</a> or by email at <a href="mailto:info@konik-website.example">info@konik-website.example</a>.</p>
            </section>
        </main>

        <?php include '!footer/footer.php'; ?>

        <script src="!navbar/navbar.js"></script>
    </body>
</html>
